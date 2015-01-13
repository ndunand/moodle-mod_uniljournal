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

$id      = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n       = optional_param('n', 0, PARAM_INT);  // ... uniljournal instance ID - it should be named as the first character of the module.
$action  = optional_param('action', 0, PARAM_TEXT);
$tid     = optional_param('tid', 0, PARAM_INT);

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
require_capability('mod/uniljournal:managetemplates', $context);

$amodels = $DB->get_records('uniljournal_articlemodels', array('uniljournalid' => $uniljournal->id), 'sortorder, id');

if ($action && $tid) {
   if (!$model  = $amodels[$tid]) {
     error('Must exist!');
   }
   
   if($action == "delete") {
     // Delete the record in question
     // TODO: Check if the record is used in places before deleting it
     $DB->delete_records('uniljournal_articlemodels', array('id' => $tid));
     unset($amodels[$tid]);
   } elseif(in_array($action, array('hide', 'show'))) {
    // Manage hide/show status
    switch($action) {
      case "hide":  $amodels[$tid]->hidden = true; break;
      case "show":  $amodels[$tid]->hidden = false; break;
    }
    $DB->update_record('uniljournal_articlemodels', $amodels[$tid]);
  } elseif(in_array($action, array('up', 'down'))) {
    // Manage the re-ordering of templates
    // Create three arrays, one for the actual keys (origs), one for the next ones (nexts), one for the previous ones (prevs)
    $origs = array_keys($amodels);
    $nexts = array_keys($amodels);
    array_shift($nexts);
    array_push($nexts, false);
    $prevs = array_keys($amodels);
    array_unshift($prevs, false);
  
    $ordering = array_flip($origs);
    if($action == "down" && array_key_exists($ordering[$tid], $nexts) && $nexts[$ordering[$tid]] !== false) {
      $ordering[$nexts[$ordering[$tid]]]--;
      $ordering[$tid]++;
    } elseif($action == "up" && array_key_exists($ordering[$tid], $prevs) && $prevs[$ordering[$tid]] !== false) {
      $ordering[$prevs[$ordering[$tid]]]++;
      $ordering[$tid]--;
    }
    
    // On-purpose refresh them all, to set the initial sortorder.
    foreach($ordering as $amodelid => $newsortorder) {
      $amodels[$amodelid]->sortorder = $newsortorder;
      $DB->update_record('uniljournal_articlemodels', $amodels[$amodelid]);
    }
    redirect(new moodle_url('/mod/uniljournal/manage_templates.php', array('id' => $cm->id)));
  }
  
}

// Print the page header.

$PAGE->set_url('/mod/uniljournal/manage_templates.php', array('id' => $cm->id));
$PAGE->set_title(format_string($uniljournal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('managetemplates', 'mod_uniljournal'));


if (count($amodels) > 0) {
  $table = new html_table();
  $table->head = array(
      get_string('template', 'mod_uniljournal'),
      get_string('actions'),
  );

  $aiter = 0;
  foreach($amodels as $amodel) {
    $aiter++;
    $row = new html_table_row();
    if($amodel->hidden) {
      $row->attributes['class'] = 'dimmed_text';
    }
    $script = 'edit_template.php';
    $args = array('cmid'=> $cm->id, 'id' => $amodel->id);
    $row->cells[0] = html_writer::link(new moodle_url('/mod/uniljournal/' . $script, $args), $amodel->title);
    
    $actionarray = array();
    $actionarray[] = $amodel->hidden ? 'show' : 'hide';
    if($aiter != 1) $actionarray[] = 'up';
    if($aiter != count($amodels)) $actionarray[] = 'down';
    $actionarray[] = 'edit';
    $actionarray[] = 'delete';
    
    $actions = "";
    foreach($actionarray as $actcode) {
      $script = 'manage_templates.php';
      $args = array('id'=> $cm->id, 'tid' => $amodel->id, 'action' => $actcode);
      
      if($actcode == 'edit') {
        $script = 'edit_template.php';
        $args = array('cmid'=> $cm->id, 'id' => $amodel->id);
      }

      $url = new moodle_url('/mod/uniljournal/' . $script, $args);
      $img = html_writer::img($OUTPUT->pix_url('t/'. $actcode), get_string($actcode));
      $actions .= html_writer::link($url, $img)."\t";
    }
    $row->cells[1] = $actions;
    $table->data[] = $row;
  }
  echo html_writer::table($table);
}

$url = new moodle_url('/mod/uniljournal/edit_template.php', array('cmid'=> $cm->id));
echo html_writer::link($url, get_string('addtemplate', 'mod_uniljournal'));

echo $OUTPUT->footer();