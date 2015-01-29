<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of uniljournal
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_uniljournal
 * @copyright  2014 Liip AG {@link http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace uniljournal with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n       = optional_param('n', 0, PARAM_INT);  // ... uniljournal instance ID - it should be named as the first character of the module.
$uid     = optional_param('uid', 0, PARAM_INT);

if ($id) {
    $cm         = get_coursemodule_from_id('uniljournal', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $uniljournal  = $DB->get_record('uniljournal', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $uniljournal  = $DB->get_record('uniljournal', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $uniljournal->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('uniljournal', $uniljournal->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/uniljournal:viewallarticles', $context);

if($uid) {
  $foreign_user = $DB->get_record('user', array('id' => $uid), '*', MUST_EXIST);
} else {
  error('That user doesn\'t exist');
}

$event = \mod_uniljournal\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
// In the next line you can use $PAGE->activityrecord if you have set it, or skip this line if you don't have a record.
// $event->add_record_snapshot($PAGE->cm->modname, $PAGE->activityrecord);
$event->trigger();

// Display table of articles for that user
require_once('locallib.php');
$userarticles = uniljournal_get_article_instances(array('uniljournalid' => $uniljournal->id, 'userid' => $foreign_user->id), true);

// Print the page header.
$PAGE->set_url('/mod/uniljournal/view_articles.php', array('id' => $cm->id));
$PAGE->set_title(format_string($uniljournal->name.' - '.fullname($foreign_user, has_capability('moodle/site:viewfullnames', $context)))); // TODO
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($uniljournal->name));

echo html_writer::tag('h3', fullname($foreign_user, has_capability('moodle/site:viewfullnames', $context)));

$table = new html_table();
$head = array(
    get_string('name'),
    get_string('lastmodified'),
    get_string('revisions', 'uniljournal'),
    get_string('corrected_status', 'uniljournal'),
    get_string('template', 'uniljournal'),
);

if(has_capability('mod/uniljournal:createarticle', $context)) {
    $head[] = get_string('actions');
}

$table->head = $head;

foreach($userarticles as $ua) {
  $row = new html_table_row();
  $title = uniljournal_articletitle($ua);
  $row->cells[] = html_writer::link(
                    new moodle_url('/mod/uniljournal/view_article.php', array('id' => $ua->id, 'cmid' => $cm->id)),
                    $title);
  $row->cells[] = strftime('%c', $ua->timemodified);
  $row->cells[] = $ua->maxversion; // No check needed, no article should be available without element instances
  // Determine the 'corrected' status: true if:
  // a) was edited last by a foreign user OR
  // b) last version was commented by a foreign user
  $corrected = !in_array($ua->edituserid, array($ua->userid, 0)) || !in_array($ua->commentuserid, array($ua->userid, 0));
  
  $row->cells[] = $corrected?html_writer::img($OUTPUT->pix_url('t/check'), get_string('yes')):'';
  $row->cells[] = $ua->amtitle;

  
  // Add actions
  $actionarray = array();
  if(has_capability('mod/uniljournal:createarticle', $context)) {
    $actionarray[] = 'edit';
  }
  
  $actions = "";
  foreach($actionarray as $actcode) {
    if($actcode == 'edit') {
      $script = 'edit_article.php';
      $args = array('cmid'=> $cm->id, 'id' => $ua->id, 'amid' => $ua->amid);
    }

    $url = new moodle_url('/mod/uniljournal/' . $script, $args);
    $img = html_writer::img($OUTPUT->pix_url('t/'. $actcode), get_string($actcode));
    $actions .= html_writer::link($url, $img)."\t";
  }
  if(!empty($actions)) {
    $row->cells[] = $actions;
  }

  $table->data[] = $row;
}

echo html_writer::table($table);

// Finish the page.
echo $OUTPUT->footer();
