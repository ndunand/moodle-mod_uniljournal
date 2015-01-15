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

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... uniljournal instance ID - it should be named as the first character of the module.

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

$event = \mod_uniljournal\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
// In the next line you can use $PAGE->activityrecord if you have set it, or skip this line if you don't have a record.
// $event->add_record_snapshot($PAGE->cm->modname, $PAGE->activityrecord);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/uniljournal/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($uniljournal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('uniljournal-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

// Replace the following lines with you own code.
echo $OUTPUT->heading(get_string('myarticles','uniljournal'));

require_once('locallib.php');

// List existing articles: TODO
// Get available article templates
$articlemodels = $DB->get_records_select('uniljournal_articlemodels', "uniljournalid = $uniljournal->id AND hidden != '\x31' ORDER BY sortorder ASC");
$articleelements = $DB->get_records_sql("
    SELECT ae.id as aeid, am.id as id, am.title, am.sortorder as sortorder, ae.element_type, ae.sortorder as aesortorder
         FROM {uniljournal_articlemodels} am
    INNER JOIN {uniljournal_articleelements} ae ON ae.articlemodelid = am.id
    WHERE am.hidden != '\x31' AND am.uniljournalid = :uniljournalid
    ORDER BY am.sortorder ASC, ae.sortorder ASC", array('uniljournalid' => $uniljournal->id));

$articleelementsgroups = array();
foreach($articleelements as $aeid => $aehybrid) {
  if(!array_key_exists($aehybrid->id, $articleelementsgroups)) {
    $articleelementsgroups[$aehybrid->id] = array();
  }
  if(!array_key_exists($aehybrid->element_type, $articleelementsgroups[$aehybrid->id])) {
    $articleelementsgroups[$aehybrid->id][$aehybrid->element_type] = 0;
  }
  $articleelementsgroups[$aehybrid->id][$aehybrid->element_type]++;
}

$templatesoptions = array();
$templatesoptions[-1] = get_string('addarticle', 'mod_uniljournal');
function translate_templatedesc(&$item, $key) {
  $item = get_string('element_'.$key.'_desc', 'mod_uniljournal', $item);
}
foreach($articlemodels as $amid => $am)  {

  $templatesoptions[$amid] = $am->title;
  if(array_key_exists($amid, $articleelementsgroups)) {
    array_walk($articleelementsgroups[$amid], 'translate_templatedesc');
    
    $templatesoptions[$amid] .= " (".implode($articleelementsgroups[$amid],", ").")";
  }
}



if(false) {
  $table = new html_table();
  $table->head = array('Key', 'Value');
  $table->data = array();

  $table->data[] = array('id', $uniljournal->id);
  $table->data[] = array('name', $uniljournal->name);
  $table->data[] = array('subtitle', $uniljournal->subtitle);
  $table->data[] = array('intro', $uniljournal->intro);
  if($logo = uniljournal_get_logo($context)) {
    $url = moodle_url::make_pluginfile_url($logo->get_contextid(), $logo->get_component(), $logo->get_filearea(), $logo->get_itemid(), $logo->get_filepath(), $logo->get_filename());
    $logoimg = html_writer::img($url, 'Logo');
    $table->data[] = array('logo', $logoimg);
  }
  $table->data[] = array('comments_allowed', $uniljournal->comments_allowed?"Yes":"No");

  echo html_writer::table($table);
}

// Display table of articles
$articleinstances = $DB->get_records_sql('SELECT ai.id, ai.timemodified, ai.title, am.id as amid, am.title as amtitle
       FROM {uniljournal_articleinstances} ai
  LEFT JOIN {uniljournal_articlemodels} am ON am.id = ai.articlemodelid
  WHERE uniljournalid = :ujid AND userid = :uid
  ORDER BY ai.timemodified DESC', array('ujid' => $uniljournal->id, 'uid' => $USER->id));

// TODO: Check rights
if(count($articleinstances) > 0) {
  $table = new html_table();
  $table->head = array(
      get_string('myarticles', 'uniljournal'),
      get_string('lastmodified'),
      get_string('template', 'uniljournal'),
      get_string('actions'),
  );

  $aiter = 0;
  foreach($articleinstances as $ai) {
    $aiter++;
    $row = new html_table_row();
    $script = 'edit.php';
    $row->cells[] = html_writer::link(
                      new moodle_url('/mod/uniljournal/view_article.php', array('id' => $ai->id, 'cmid' => $cm->id)),
                      $ai->title);
    $row->cells[] = strftime('%c', $ai->timemodified);
    $row->cells[] = $ai->amtitle;
    
    $actionarray = array();
    $actionarray[] = 'edit';
//     $actionarray[] = 'delete';
    
    $actions = "";
    foreach($actionarray as $actcode) {
      $script = 'view.php';
      $args = array('id'=> $cm->id, 'aid' => $ai->id, 'action' => $actcode);
      
      if($actcode == 'edit') {
        $script = 'edit_article.php';
        $args = array('cmid'=> $cm->id, 'id' => $ai->id, 'amid' => $ai->amid);
      }

      $url = new moodle_url('/mod/uniljournal/' . $script, $args);
      $img = html_writer::img($OUTPUT->pix_url('t/'. $actcode), get_string($actcode));
      $actions .= html_writer::link($url, $img)."\t";
    }
    $row->cells[] = $actions;
    $table->data[] = $row;
  }
  echo html_writer::table($table);
}

if(count($articlemodels) > 1) {
  require_once('view_choose_template_form.php');
  $customdata = array();
  $customdata['options'] = $templatesoptions;

  $mform = new choose_template_form(new moodle_url('/mod/uniljournal/edit_article.php', array('cmid' => $cm->id)), $customdata);
  //displays the form, with an auto-submitter and no change checker
  $mform->display();
  $PAGE->requires->yui_module('moodle-mod_uniljournal-viewsubmitonchange', 'M.mod_uniljournal.viewsubmitonchange.init');
} else {
  $am = array_pop($articlemodels);
  echo html_writer::link(new moodle_url('/mod/uniljournal/edit_article.php', array('cmid' => $cm->id, 'amid' => $am->id)), get_string('addarticletempl', 'mod_uniljournal', $templatesoptions[$am->id]));
}

// Finish the page.
echo $OUTPUT->footer();
