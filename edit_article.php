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
if(! has_capability('mod/uniljournal:createarticle', $context) && ! has_capability('mod/uniljournal:editallarticles', $context) ) {
  error('You can\'t edit this article');
}

// Get the model we're editing
if (!$articlemodel = $DB->get_record_select('uniljournal_articlemodels', "id = $amid AND hidden != '\x31'")) {
       print_error('invalidentry');
}
// Get all elements of the model
$articleelements = $DB->get_records_select('uniljournal_articleelements', "articlemodelid = $amid ORDER BY sortorder ASC");

$textfieldoptions = array(
  'subdirs'  => false,
  'maxfiles' => '12',
  'maxbytes' => $articlemodel->maxbytes,
  'context'  => $context
);

$textonlyoptions = array(
  'subdirs'  => false,
  'maxfiles' => 0,
  'context'  => $context
);

$attachmentoptions = array(
  'subdirs'  => false,
  'maxfiles' => '1',
  'maxbytes' => $articlemodel->maxbytes,
  'context'  => $context
);

if ($id) { // if entry is specified
  if (!$articleinstance = $DB->get_record('uniljournal_articleinstances', array('id' => $id, 'articlemodelid' => $amid))) {
    print_error('invalidentry');
  }
  
  // Get the existing article elements for edition
  $version = 0;
  foreach($articleelements as $ae) {
    $property_name   = 'element_'.$ae->id;
    $property_edit   = $property_name.'_editor';
    $property_format = $property_name.'format';
    $aeinstance = $DB->get_record_sql('
      SELECT * FROM {uniljournal_aeinstances} 
        WHERE instanceid = :instanceid
          AND elementid  = :elementid 
     ORDER BY version DESC LIMIT 1', array('instanceid' => $articleinstance->id, 'elementid' => $ae->id));
    if($aeinstance !== false) {
      $articleinstance->$property_name = $aeinstance->value;
      $articleinstance->$property_format = $aeinstance->valueformat;
      $version = max($version, $aeinstance->version);
      
      if($ae->element_type == 'text' || $ae->element_type == 'textonly') {
        $articleinstance = file_prepare_standard_editor($articleinstance, $property_name, $textfieldoptions, $context, 'mod_uniljournal', 'elementinstance', $aeinstance->id);
      } elseif (uniljournal_startswith($ae->element_type, 'attachment_')) { // begins with
        $attoptions = $attachmentoptions;
        $attoptions['accepted_types'] = substr($ae->element_type, 11);
        $articleinstance = file_prepare_standard_filemanager($articleinstance, $property_name, $attoptions, $context, 'mod_uniljournal', 'elementinstance', $aeinstance->id);
      }
    }
  }
} else { // new entry
  $articleinstance = new stdClass();
  $articleinstance->id = null;
  $version = 0;
}

$articleinstance->cmid = $cmid;
$articleinstance->amid = $amid;

$customdata = array();
$customdata['current'] = $articleinstance;
$customdata['course'] = $course;
$customdata['articlemodel'] = $articlemodel;
$customdata['articleelements'] = $articleelements;
$customdata['attachmentoptions'] = $attachmentoptions;
$customdata['textfieldoptions'] = $textfieldoptions;
$customdata['textonlyoptions'] = $textonlyoptions;
$customdata['cm'] = $cm;

if($articlemodel->themebankid) {
  $customdata['themes'] = $DB->get_records_select('uniljournal_themes', "themebankid = ".$articlemodel->themebankid." AND hidden != '\x31' ORDER BY sortorder ASC");
}

require_once('edit_article_form.php');
$mform = new edit_article_form(null, $customdata);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/uniljournal/view.php', array('id' => $cm->id)));
} else if ($articleinstance = $mform->get_data()) {
    $isnewentry = empty($articleinstance->id);
    $articleinstance->articlemodelid = $amid;
    $articleinstance->timemodified = time();
    
    if(!isset($articlemodel->freetitle) || $articlemodel->freetitle == 0) {
      $articleinstance->title = ""; // Makes sure the article title is deleted if it exists.
    }

    if($isnewentry) {
      $articleinstance->userid = $USER->id; // A new article is always owned by its creator
      $articleinstance->id = $DB->insert_record('uniljournal_articleinstances', $articleinstance);
        // Log the article creation
        $event = \mod_uniljournal\event\article_created::create(array(
            'other' => array(
                'userid' => $USER->id,
                'articleid' => $articleinstance->id
            ),
            'courseid' => $course->id,
            'objectid' => $articleinstance->id,
            'context' => $context,
        ));
        $event->trigger();
    } else {
      unset($articleinstance->userid); // Don't let a teacher take over an article
      $DB->update_record('uniljournal_articleinstances', $articleinstance);
        // Log the article update
        $event = \mod_uniljournal\event\article_updated::create(array(
            'other' => array(
                'userid' => $USER->id,
                'articleid' => $articleinstance->id
            ),
            'courseid' => $course->id,
            'objectid' => $articleinstance->id,
            'context' => $context,
        ));
        $event->trigger();
    }
    
    foreach($articleelements as $ae) {
      $property_name   = 'element_'.$ae->id;
      $property_edit   = $property_name.'_editor';
      $property_format = $property_name.'format';
      if(isset($articleinstance->$property_name) or isset($articleinstance->$property_edit)) {
        $element = new stdClass();
        $element->instanceid = $articleinstance->id;
        $element->elementid = $ae->id;
        $element->version = $version + 1;
        $element->timemodified = time();
        $element->userid = $USER->id;
        if(isset($articleinstance->$property_name)) {
          $element->value = $articleinstance->$property_name;
        }
        // TODO: Avoid re-writing records that haven't changed !
        $element->id = $DB->insert_record('uniljournal_aeinstances', $element);

        if($ae->element_type == 'text' or $ae->element_type == 'textonly') {
          $articleinstance = file_postupdate_standard_editor($articleinstance, $property_name, $textfieldoptions, $context, 'mod_uniljournal', 'elementinstance', $element->id);
          $element->value       = $articleinstance->$property_name;
          $element->valueformat = $articleinstance->$property_format;
          $DB->update_record('uniljournal_aeinstances', $element);

        } elseif (uniljournal_startswith($ae->element_type, 'attachment_')) {
          $draftitemid = $articleinstance->$property_name;
          $context = context_module::instance($cmid);
          if ($draftitemid) {
            file_save_draft_area_files($draftitemid, $context->id, 'mod_uniljournal', 'elementinstance', $element->id, $attachmentoptions);
          }
        }
      }
    }
    redirect(new moodle_url('/mod/uniljournal/view.php', array('id' => $cm->id)));
}


$url = new moodle_url('/mod/uniljournal/edit_article.php', array('cmid'=>$cm->id, 'articlemodelid' => $amid));
$uniljournal_renderer = $PAGE->get_renderer('mod_uniljournal');
$PAGE->set_url($url);
$PAGE->set_title(format_string(get_string('writearticletempl', 'mod_uniljournal', $articlemodel->title)));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->jquery();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('writearticletempl', 'mod_uniljournal', $articlemodel->title));

echo '<div class="article"><div class="article-edit">';
$mform->display();
echo '</div><div class="article-comments">';
echo $uniljournal_renderer->display_comments($cmid, $id, $version, $USER->id, -1);
echo '</div>';

echo $OUTPUT->footer();
