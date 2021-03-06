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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
global $USER;

$tbid = optional_param('tbid', 0, PARAM_INT); // Theme bank ID
$cmid = optional_param('cmid', 0, PARAM_INT); // Course_module ID
$tid = optional_param('tid', 0, PARAM_INT); // Theme ID
$action = optional_param('action', 0, PARAM_TEXT);

if ($tbid && $cmid) {
    $cm = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $uniljournal = $DB->get_record('uniljournal', ['id' => $cm->instance], '*', MUST_EXIST);
}
else {
    print_error('id_missing', 'mod_uniljournal');
}

require_login($course, true, $cm);
$module_context = context_module::instance($cm->id);
$course_context = context_course::instance($course->id);
$category_context = context_coursecat::instance($course->category);
$system_context = context_system::instance();
$user_context = context_user::instance($USER->id);
$contexts = ['module_context'   => $module_context->id, 'course_context' => $course_context->id,
             'category_context' => $category_context->id, 'system_context' => $system_context->id,
             'user_context'     => $user_context->id];
require_capability('mod/uniljournal:managethemes', $module_context);

$themebank = $DB->get_record('uniljournal_themebanks', ['id' => $tbid]);

$themes = $DB->get_records_sql('
        SELECT t.*, tc.count
          FROM {uniljournal_themes} t
     LEFT JOIN (
                SELECT themeid, COUNT(id) as count
                  FROM {uniljournal_articleinstances}
              GROUP BY themeid
               ) tc ON tc.themeid = t.id
         WHERE t.themebankid = :themebankid
         ORDER BY t.sortorder ASC', ['themebankid' => $tbid]);

if ($action && $tid) {
    if (!$theme = $themes[$tid]) {
        print_error('mustexist', 'mod_uniljournal');
    }

    if ($action == "delete" && (is_null($theme->count) || $theme->count == 0)) {
        require_once('edit_theme_form.php');
        $customdata = [];
        $customdata['course'] = $course;
        $customdata['themebank'] = $DB->get_record('uniljournal_themebanks', ['id' => $tbid]);
        $customdata['current'] = $themes[$tid];

        $deleteform = new theme_delete_form(new moodle_url('/mod/uniljournal/manage_themes.php',
                ['cmid' => $cm->id, 'tbid' => $tbid, 'tid' => $tid, 'action' => 'delete']), $customdata);

        if ($deleteform->is_cancelled()) {
            unset($deleteform);
        }
        elseif (($entry = $deleteform->get_data()) && $entry->confirm == 1) {
            // Delete the record in question
            $DB->delete_records('uniljournal_themes', ['id' => $tid]);
            unset($themes[$tid]);
            unset($deleteform);

            // Log the theme deletion
            $event = \mod_uniljournal\event\theme_deleted::create(['other' => ['userid'      => $USER->id,
                                                                               'themeid'     => $tid,
                                                                               'themebankid' => $tbid],
                                                                   'courseid' => $course->id, 'objectid' => $tid,
                                                                   'context' => $module_context,]);
            $event->trigger();
        }
    }
    elseif (in_array($action, ['hide', 'show'])) {
        // Manage hide/show status
        switch ($action) {
            case "hide":
                $themes[$tid]->hidden = true;
                break;
            case "show":
                $themes[$tid]->hidden = false;
                break;
        }
        $DB->update_record('uniljournal_themes', $themes[$tid]);
    }
    elseif (in_array($action, ['up', 'down'])) {
        // Manage the re-ordering of templates
        // Create three arrays, one for the actual keys (origs), one for the next ones (nexts), one for the previous ones (prevs)
        $origs = array_keys($themes);
        $nexts = array_keys($themes);
        array_shift($nexts);
        array_push($nexts, false);
        $prevs = array_keys($themes);
        array_unshift($prevs, false);

        $ordering = array_flip($origs);
        if ($action == "down" && array_key_exists($ordering[$tid], $nexts) && $nexts[$ordering[$tid]] !== false) {
            $ordering[$nexts[$ordering[$tid]]]--;
            $ordering[$tid]++;
        }
        elseif ($action == "up" && array_key_exists($ordering[$tid], $prevs) && $prevs[$ordering[$tid]] !== false) {
            $ordering[$prevs[$ordering[$tid]]]++;
            $ordering[$tid]--;
        }

        // On-purpose refresh them all, to set the initial sortorder.
        foreach ($ordering as $themeid => $newsortorder) {
            $themes[$themeid]->sortorder = $newsortorder;
            $DB->update_record('uniljournal_themes', $themes[$themeid]);
        }
        redirect(new moodle_url('/mod/uniljournal/manage_themes.php', ['cmid' => $cmid, 'tbid' => $tbid]));
    }
}

// Print the page header.

$PAGE->set_url('/mod/uniljournal/manage_themes.php', ['cmid' => $cmid, 'tbid' => $tbid]);
$PAGE->set_title(format_string($uniljournal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($module_context);

echo $OUTPUT->header();

$a = new stdClass();
$a->themebankname = $themebank->title;
echo $OUTPUT->heading(get_string('managethemes', 'mod_uniljournal', $a));

if (isset($deleteform)) {
    //displays the form
    $deleteform->display();
}
else {
    if (count($themes) > 0) {
        $table = new html_table();
        $table->head = [get_string('theme', 'mod_uniljournal'), get_string('actions'),];

        $aiter = 0;
        foreach ($themes as $theme) {
            $aiter++;
            $used =
                    (is_null($theme->count) || $theme->count == 0) ? ('') : (' (' . strtolower(get_string('used')) . ')');
            $row = new html_table_row();
            if ($theme->hidden) {
                $row->attributes['class'] = 'dimmed_text';
            }
            $script = 'edit_theme.php';
            $args = ['cmid' => $cm->id, 'tbid' => $theme->themebankid, 'id' => $theme->id];
            $row->cells[0] =
                    html_writer::link(new moodle_url('/mod/uniljournal/' . $script, $args), $theme->title) . $used;

            $actionarray = [];
            $actionarray[] = $theme->hidden ? 'show' : 'hide';
            if ($aiter != 1) {
                $actionarray[] = 'up';
            }
            if ($aiter != count($themes)) {
                $actionarray[] = 'down';
            }
            $actionarray[] = 'edit';
            if (is_null($theme->count) || $theme->count == 0) {
                $actionarray[] = 'delete';
            }

            $actions = "";
            foreach ($actionarray as $actcode) {
                $script = 'manage_themes.php';
                $args = ['cmid' => $cm->id, 'tbid' => $theme->themebankid, 'tid' => $theme->id, 'action' => $actcode];

                switch ($actcode) {
                    case "edit":
                        $script = 'edit_theme.php';
                        $args = ['cmid' => $cm->id, 'tbid' => $theme->themebankid, 'id' => $theme->id];
                        break;
                }

                $url = new moodle_url('/mod/uniljournal/' . $script, $args);
                $img = $OUTPUT->pix_icon('t/' . $actcode, get_string($actcode));
                $actions .= html_writer::link($url, $img) . "\t";
            }
            $row->cells[1] = $actions;
            $table->data[] = $row;
        }
        echo html_writer::table($table);
    }

    $url = new moodle_url('/mod/uniljournal/edit_theme.php', ['cmid' => $cm->id, 'tbid' => $tbid]);
    echo html_writer::link($url, get_string('addtheme', 'mod_uniljournal'));
}

echo $OUTPUT->footer();
