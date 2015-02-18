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
 * @copyright  2014-2015  Université de Lausanne
 * @author     Liip AG eLearning Team <elearning@liip.ch>
 * @author     Didier Raboud <didier.raboud@liip.ch>
 * @author     Claude Bossy <claude.bossy@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id      = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n       = optional_param('n', 0, PARAM_INT);  // ... uniljournal instance ID - it should be named as the first character of the module.
$action  = optional_param('action', 0, PARAM_TEXT);
$tbid     = optional_param('tbid', 0, PARAM_INT);

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

$module_context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/uniljournal:managethemes', $module_context);
require_once('locallib.php');
$themebanks = uniljournal_get_theme_banks($cm, $course);

if ($action && $tbid) {
    if (!$model = $themebanks[$tbid]) {
        error('Must exist!');
    }

    if($action == "delete" and $model->themescount == 0 ) {
        require_once('edit_themebank_form.php');
        $customdata = array();
        $customdata['course'] = $course;
        $customdata['cm'] = $cm;
        $customdata['themebank'] = $themebanks[$tbid];

        $deleteform = new themebank_delete_form(new moodle_url('/mod/uniljournal/manage_themebanks.php', array('id'=> $cm->id, 'tbid' => $tbid, 'action' => 'delete')), $customdata);

        if ($deleteform->is_cancelled()) {
            unset($deleteform);
        } elseif ( ($entry = $deleteform->get_data()) && $entry->confirm == 1) {
            // Delete the record in question
            $DB->delete_records('uniljournal_themebanks', array('id' => $tbid));
            $DB->delete_records('uniljournal_themes', array('themebankid' => $tbid));
            unset($themebanks[$tbid]);
            unset($deleteform);

            // Log the theme bank deletion
            $event = \mod_uniljournal\event\themebank_deleted::create(array(
                'other' => array(
                    'userid' => $USER->id,
                    'themebankid' => $tbid
                ),
                'courseid' => $course->id,
                'objectid' => $tbid,
                'context' => $module_context,
            ));
            $event->trigger();
        }
    }

}

// Print the page header.

$PAGE->set_url('/mod/uniljournal/manage_themebanks.php', array('id' => $cm->id));
$PAGE->set_title(format_string($uniljournal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($module_context);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('managethemebanks', 'mod_uniljournal'));

if (isset($deleteform)) {
    //displays the form
    $deleteform->display();

} else {
    if (count($themebanks) > 0) {
        $table = new html_table();
        $table->head = array(
            get_string('themebank', 'mod_uniljournal'),
            get_string('module_context', 'uniljournal'),
            get_string('course_context', 'uniljournal'),
            get_string('category_context', 'uniljournal'),
            get_string('system_context', 'uniljournal'),
            get_string('actions'),
        );

        $aiter = 0;
        foreach($themebanks as $themebank) {
            $aiter++;
            $row = new html_table_row();
            $script = 'manage_themes.php';
            $args = array('cmid'=> $cm->id, 'tbid' => $themebank->id);
            $row->cells[0] = html_writer::link(new moodle_url('/mod/uniljournal/' . $script, $args), $themebank->title);

            $context = context::instance_by_id($themebank->contextid);
            
            $row->cells[1] = ($context->contextlevel <= CONTEXT_MODULE) ? '×': '';
            $row->cells[2] = ($context->contextlevel <= CONTEXT_COURSE) ? '×': '';
            $row->cells[3] = ($context->contextlevel <= CONTEXT_COURSECAT) ? '×': '';
            $row->cells[4] = ($context->contextlevel <= CONTEXT_SYSTEM) ? '×': '';
            
            $actionarray = array();
            $actionarray[] = 'edit';
            if($themebank->themescount == 0) $actionarray[] = 'delete';

            $actions = "";
            foreach($actionarray as $actcode) {
                $script = 'manage_themebanks.php';
                $args = array('id'=> $cm->id, 'tbid' => $themebank->id, 'action' => $actcode);

                switch($actcode) {
                    case "edit":
                        $script = 'edit_themebank.php';
                        $args = array('cmid'=> $cm->id, 'id' => $themebank->id);
                        break;
                }

                $url = new moodle_url('/mod/uniljournal/' . $script, $args);
                $img = html_writer::img($OUTPUT->pix_url('t/'. $actcode), get_string($actcode));
                $actions .= html_writer::link($url, $img)."\t";
            }
            $row->cells[5] = $actions;
            $table->data[] = $row;
        }
        echo html_writer::table($table);
    }

    $url = new moodle_url('/mod/uniljournal/edit_themebank.php', array('cmid'=> $cm->id));
    echo html_writer::link($url, get_string('addthemebank', 'mod_uniljournal'));
}

echo $OUTPUT->footer();
