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

$amodels = $DB->get_records_sql('
       SELECT am.*, COUNT(ai.id) as articleinstancescount
         FROM {uniljournal_articlemodels} am
    LEFT JOIN {uniljournal_articleinstances} ai ON ai.articlemodelid = am.id
        WHERE am.uniljournalid = :uniljournalid
     GROUP BY am.id
     ORDER BY am.sortorder, am.id', array('uniljournalid' => $uniljournal->id));

if ($action && $tid) {
   if (!$model = $amodels[$tid]) {
     error('Must exist!');
   }
   
   if($action == "delete" and $model->articleinstancescount == 0 ) {
    require_once('edit_template_form.php');
    $customdata = array();
    $customdata['course'] = $course;
    $customdata['cm'] = $cm;
    $customdata['template'] = $amodels[$tid];

    $deleteform = new template_delete_form(new moodle_url('/mod/uniljournal/manage_templates.php', array('id'=> $cm->id, 'tid' => $tid, 'action' => 'delete')), $customdata);

    if ($deleteform->is_cancelled()) {
      unset($deleteform);
    } elseif ( ($entry = $deleteform->get_data()) && $entry->confirm == 1) {
      // Delete the record in question
      $DB->delete_records('uniljournal_articlemodels', array('id' => $tid));
      $DB->delete_records('uniljournal_articleelements', array('articlemodelid' => $tid));
      unset($amodels[$tid]);
      unset($deleteform);

        // Log the template deletion
        $event = \mod_uniljournal\event\template_deleted::create(array(
            'other' => array(
                'userid' => $USER->id,
                'templateid' => $tid
            ),
            'courseid' => $course->id,
            'objectid' => $tid,
            'context' => $context,
        ));
        $event->trigger();
    }
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

echo $OUTPUT->heading(get_string('articletemplates', 'mod_uniljournal'));

if (isset($deleteform)) {
  //displays the form
  $deleteform->display();

} else {
  if (count($amodels) > 0) {
    $table = new html_table();
    $table->head = array(
        get_string('template', 'mod_uniljournal'),
        '',
        get_string('actions'),
    );
    require_once('locallib.php');
    $templdescs = uniljournal_get_template_descriptions($uniljournal->id, false);

    $aiter = 0;
    foreach($amodels as $amodel) {
      $aiter++;
      $row = new html_table_row();
      if($amodel->hidden) {
        $row->attributes['class'] = 'dimmed_text';
      }
      $script = 'edit_template.php';
      $args = array('cmid'=> $cm->id, 'id' => $amodel->id);
      
      if($amodel->articleinstancescount == 0) {
        $row->cells[0] = html_writer::link(new moodle_url('/mod/uniljournal/' . $script, $args), $amodel->title);
      } else {
        $row->cells[0] = $amodel->title.' ('.get_string('n_articleinstances', 'uniljournal', $amodel->articleinstancescount).')';
      }

      if(array_key_exists($amodel->id, $templdescs)) {
        $row->cells[1] = '<ul><li>'.implode($templdescs[$amodel->id],'</li><li>').'</li></ul>';
      } else {
        $row->cells[1] = '';
      }

      $actionarray = array();
      $actionarray[] = $amodel->hidden ? 'show' : 'hide';
      if($aiter != 1) $actionarray[] = 'up';
      if($aiter != count($amodels)) $actionarray[] = 'down';
      if($amodel->articleinstancescount == 0) {
        $actionarray[] = 'edit';
        $actionarray[] = 'delete';
      }
      
      $actions = "";
      foreach($actionarray as $actcode) {
        $script = 'manage_templates.php';
        $args = array('id'=> $cm->id, 'tid' => $amodel->id, 'action' => $actcode);
        
        switch($actcode) {
          case "edit":
            $script = 'edit_template.php';
            $args = array('cmid'=> $cm->id, 'id' => $amodel->id);
            break;
        }

        $url = new moodle_url('/mod/uniljournal/' . $script, $args);
        $img = html_writer::img($OUTPUT->pix_url('t/'. $actcode), get_string($actcode));
        $actions .= html_writer::link($url, $img)."\t";
      }
      $row->cells[2] = $actions;
      $table->data[] = $row;
    }
    echo html_writer::table($table);
  }

  $url = new moodle_url('/mod/uniljournal/edit_template.php', array('cmid'=> $cm->id));
  echo html_writer::link($url, get_string('addtemplate', 'mod_uniljournal'));
}

echo $OUTPUT->footer();
