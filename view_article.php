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
 * @copyright  2015 Liip AG {@link http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$cmid    = optional_param('cmid', 0, PARAM_INT);  // Course_module ID, or
$id      = optional_param('id', 0, PARAM_INT);    // Article instance ID
$version = optional_param('version', 0, PARAM_INT);    // Article instance ID

if ($cmid and $id) {
    $cm              = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course          = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $uniljournal     = $DB->get_record('uniljournal', array('id' => $cm->instance), '*', MUST_EXIST);
    $articleinstances = uniljournal_get_article_instances(array('id' => $id), true);
    $articleinstance = array_pop($articleinstances);
} else {
    error('You must specify a course_module and an article instance ID');

}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
if($articleinstance->userid == $USER->id) {
  require_capability('mod/uniljournal:view', $context);
} else {
  require_capability('mod/uniljournal:viewallarticles', $context);
}

// Get all elements of the model
$articleelements = $DB->get_records_select('uniljournal_articleelements', "articlemodelid = $articleinstance->amid ORDER BY sortorder ASC");

// Log the article read action
$event = \mod_uniljournal\event\article_read::create(array(
    'other' => array(
        'userid' => $USER->id,
        'articleid' => $articleinstance->id
    ),
    'courseid' => $course->id,
    'objectid' => $articleinstance->id,
    'context' => $context,
));
$event->trigger();

$articletitle = uniljournal_articletitle($articleinstance);
$articleinstance->title = $articletitle;

$PAGE->set_url('/mod/uniljournal/view_article.php', array('id' => $articleinstance->id, 'cmid' => $cm->id));
$PAGE->set_title(format_string($articletitle));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$uniljournal_renderer = $PAGE->get_renderer('mod_uniljournal');
$actualversion = 0;


$article_html = $uniljournal_renderer->display_article($articleinstance, $articleelements, false, $context, $version, $actualversion);

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('uniljournal-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

// Replace the following lines with you own code.
echo $OUTPUT->heading(format_string($articletitle)." - Preview (version: ".$actualversion.")"); // TODO

echo '<div class="article">';

echo '<div class="article-version-toggle">';
if($actualversion > 1) {
  echo link_arrow_left(get_string('version_previous', 'uniljournal'), new moodle_url('/mod/uniljournal/view_article.php', array('id' => $articleinstance->id, 'cmid' => $cm->id, 'version'=> $actualversion-1)), true);
}

echo html_writer::tag('span', get_string('version').' '.$actualversion." / ".$articleinstance->maxversion);

if($actualversion < $articleinstance->maxversion) {
  echo link_arrow_right(get_string('version_next', 'uniljournal'), new moodle_url('/mod/uniljournal/view_article.php', array('id' => $articleinstance->id, 'cmid' => $cm->id, 'version'=> $actualversion+1)), true);
}

echo '</div>';
echo '<div class="article-edit">';

echo $article_html;

echo '</div><div class="article-comments">';
echo $uniljournal_renderer->display_comments($cmid, $id, $actualversion, $USER->id, $articleinstance->maxversion);
echo '</div>';

// Finish the page.
echo $OUTPUT->footer();
