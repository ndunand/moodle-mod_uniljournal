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
 * Edits a particular instance of a uniljournal instance
 *
 *
 * @package    mod_uniljournal
 * @copyright  2015 Liip AG {@link http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$cmid = optional_param('cmid', 0, PARAM_INT);  // Course_module ID, or
$amid = optional_param('amid', 0, PARAM_INT);  // template ID
$id   = optional_param('id', 0, PARAM_INT);    // Article instance ID

if ($cmid) {
    $cm         = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $uniljournal  = $DB->get_record('uniljournal', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
// TODO: require_capability('mod/uniljournal:', $context);

if (!$articlemodel = $DB->get_record_select('uniljournal_articlemodels', "id = $amid AND hidden != '\x31'")) {
       print_error('invalidentry');
}
$articleelements = $DB->get_records_select('uniljournal_articleelements', "articlemodelid = $amid ORDER BY sortorder ASC");

// $instructionsoptions = array('trusttext'=> true, 'maxfiles'=> 0, 'context'=> $context, 'subdirs'=>0);

if ($id) { // if entry is specified
  if (!$articleinstance = $DB->get_record('uniljournal_articleinstances', array('id' => $id))) {
    print_error('invalidentry');
  }
  
  // Get the existing article elements for edition
  $version = 0;
  foreach($articleelements as $ae) {
    $property_name = 'element_'.$ae->id;
    $aeinstance = $DB->get_record_sql('
      SELECT * FROM {uniljournal_aeinstances} 
        WHERE instanceid = :instanceid
          AND elementid  = :elementid 
     ORDER BY version DESC LIMIT 1', array('instanceid' => $articleinstance->id, 'elementid' => $ae->id));
     switch($ae->element_type) {
            case "attachment":
              // TODO
              break;
            case "image":
              // TODO
              break;
            case "text":
              // TODO
              break;
            case "title":
              $p = $aeinstance->text;
              break;
    }
    $articleinstance->$property_name = $p;
    $version = max($version, $aeinstance->version);
  }
  
} else { // new entry
  $articleinstance = new stdClass();
  $articleinstance->id = null;
  $version = 0;
}

// $articleinstance = file_prepare_standard_editor($articleinstance, 'instructions', $instructionsoptions, $context, 'mod_uniljournal', 'articletemplates', $articleinstance->id);
$articleinstance->cmid = $cmid;
$articleinstance->amid = $amid;

require_once('edit_form.php');
$customdata = array();
$customdata['current'] = $articleinstance;
$customdata['course'] = $course;
$customdata['articlemodel'] = $articlemodel;
$customdata['articleelements'] = $articleelements;
$customdata['cm'] = $cm;

$mform = new edit_form(null, $customdata);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/uniljournal/view.php', array('id' => $cm->id)));
} else if ($articleinstance = $mform->get_data()) {
    $isnewentry = empty($articleinstance->id);
    
    $articleinstance->userid = $USER->id;// TODO: What happens when a teacher creates or edits a content for a student ?
    $articleinstance->articlemodelid = $amid;
    $articleinstance->timemodified = time();

    if($isnewentry) {
      $articleinstance->id = $DB->insert_record('uniljournal_articleinstances', $articleinstance);
    } else {
      $DB->update_record('uniljournal_articleinstances', $articleinstance);
    }
    
    foreach($articleelements as $ae) {
      $property_name = 'element_'.$ae->id;
      if(isset($articleinstance->$property_name)) {
        $element = new stdClass();
        $element->instanceid = $articleinstance->id;
        $element->elementid = $ae->id;
        $element->version = $version + 1;
        $element->timemodified = time();
        switch($ae->element_type) {
            case "attachment":
              // TODO
              break;
            case "image":
              // TODO
              break;
            case "text":
              // TODO
              break;
            case "title":
              $element->text = $articleinstance->$property_name;
              break;
        }
        $DB->insert_record('uniljournal_aeinstances', $element);
      }
    }
    redirect(new moodle_url('/mod/uniljournal/view.php', array('id' => $cm->id)));
}


$url = new moodle_url('/mod/uniljournal/edit.php', array('cmid'=>$cm->id, 'articlemodelid' => $amid));
$PAGE->set_url($url);
$PAGE->set_title(format_string(get_string('writearticletempl', 'mod_uniljournal', $articlemodel->title)));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('writearticletempl', 'mod_uniljournal', $articlemodel->title));

if(true) {
  echo "<p>About to create an article for model $articlemodel->title.</p>";
  echo "<p>It has the following elements:</p><ul>";
  foreach($articleelements as $articleelement) {
    echo "<li>".get_string('element_'.$articleelement->element_type, 'mod_uniljournal')."</li>";
  }
  echo "</ul>";
}

//displays the form
$mform->display();

echo $OUTPUT->footer();
