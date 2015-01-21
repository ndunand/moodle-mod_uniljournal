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
    $articleinstance = $DB->get_record('uniljournal_articleinstances', array('id' => $id), '*', MUST_EXIST);
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
$articleelements = $DB->get_records_select('uniljournal_articleelements', "articlemodelid = $articleinstance->articlemodelid ORDER BY sortorder ASC");

$table = new html_table();
// Table has two cols: one for content, one for the attachements and other stuffs
$table->data = array();

$content = "";
$attachments = "";

// Get the existing article elements for display
$actualversion = 0;
foreach($articleelements as $ae) {
  $property_name   = 'element_'.$ae->id;
  $property_edit   = $property_name.'_editor';
  $property_format = $property_name.'format';
  
  $sqlargs = array('instanceid' => $articleinstance->id, 'elementid' => $ae->id);
  $sql = '
    SELECT * FROM {uniljournal_aeinstances} 
      WHERE instanceid = :instanceid
        AND elementid  = :elementid ';
  if ($version != 0) {
    $sql .= 'AND version <= :version';
    $sqlargs['version'] = $version;
  }
  $sql .= 'ORDER BY version DESC LIMIT 1';
  $aeinstance = $DB->get_record_sql($sql, $sqlargs);
  if($aeinstance !== false) {
    switch($ae->element_type) {
      case "subtitle":
        $content .= html_writer::tag('h4', $aeinstance->value);
        break;
      case "text":
      case "textonly":
        $aeinstance->value = file_rewrite_pluginfile_urls($aeinstance->value, 'pluginfile.php', $context->id, 'mod_uniljournal', 'elementinstance', $aeinstance->id);
        $content .= html_writer::tag('div', $aeinstance->value);
        break;
    }
    
    if (substr_compare($ae->element_type, 'attachment_', 0, 11) === 0 ) { // begins with
      $fs = get_file_storage();
      $files = $fs->get_area_files($context->id, 'mod_uniljournal', 'elementinstance', $aeinstance->id);
      if(count($files) > 0) {
        $file = array_pop($files);
        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        if($file->is_valid_image()) {
          $attachments .= html_writer::tag('div', html_writer::img($url, $file->get_filename()));
        } else {
          $attachments .= html_writer::tag('div', html_writer::link($url, $file->get_filename()));
        }
      }
    }
    
    $actualversion = max($actualversion, $aeinstance->version);
  }
}

$maxversionsql = $DB->get_record_sql('SELECT max(version) as maxversion FROM {uniljournal_aeinstances} WHERE instanceid = :instanceid', array('instanceid' => $articleinstance->id));

// Build article title if it doesn't exist
$articletitle = !empty($articleinstance->title) ? $articleinstance->title : 'TODO: Theme title';

$titlecell = new html_table_cell(html_writer::tag('h3', $articletitle));
$titlecell->colspan = 2;
$table->data[] = new html_table_row(array($titlecell));
$table->data[] = new html_table_row(
  array(
    new html_table_cell($content),
    new html_table_cell($attachments),
    ));

$PAGE->set_url('/mod/uniljournal/view_article.php', array('id' => $articleinstance->id, 'cmid' => $cm->id));
$PAGE->set_title(format_string($articletitle));
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
echo $OUTPUT->heading(format_string($articletitle)." - Preview (version: ".$actualversion.")"); // TODO

if($actualversion > 1) {
  echo html_writer::tag('span', html_writer::link(new moodle_url('/mod/uniljournal/view_article.php', array('id' => $articleinstance->id, 'cmid' => $cm->id, 'version'=> $actualversion-1)), '←'));
}

echo html_writer::tag('span', 'Version '.$actualversion." / ".$maxversionsql->maxversion);

if($actualversion < $maxversionsql->maxversion) {
  echo html_writer::tag('span', html_writer::link(new moodle_url('/mod/uniljournal/view_article.php', array('id' => $articleinstance->id, 'cmid' => $cm->id, 'version'=> $actualversion+1)), '→'));
}

echo html_writer::table($table);

// Finish the page.
echo $OUTPUT->footer();
