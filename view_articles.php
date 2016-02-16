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

// Replace uniljournal with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n = optional_param('n', 0,
        PARAM_INT);  // ... uniljournal instance ID - it should be named as the first character of the module.
$uid = optional_param('uid', 0, PARAM_INT);
$action = optional_param('action', 0, PARAM_TEXT);
$aid = optional_param('aid', 0, PARAM_TEXT);

if ($id) {
    $cm = get_coursemodule_from_id('uniljournal', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $uniljournal = $DB->get_record('uniljournal', ['id' => $cm->instance], '*', MUST_EXIST);
}
else if ($n) {
    $uniljournal = $DB->get_record('uniljournal', ['id' => $n], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $uniljournal->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('uniljournal', $uniljournal->id, $course->id, false, MUST_EXIST);
}
else {
    print_error('id_missing', 'mod_uniljournal');
}

global $DB;

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/uniljournal:viewallarticles', $context);

if ($uid) {
    $foreign_user = $DB->get_record('user', ['id' => $uid], '*', MUST_EXIST);
}
else {
    print_error('userdoesnotexist', 'mod_uniljournal');
}

$event = \mod_uniljournal\event\course_module_viewed::create(['objectid' => $PAGE->cm->instance,
                                                              'context'  => $PAGE->context,]);
$event->add_record_snapshot('course', $PAGE->course);
// In the next line you can use $PAGE->activityrecord if you have set it, or skip this line if you don't have a record.
// $event->add_record_snapshot($PAGE->cm->modname, $PAGE->activityrecord);
$event->trigger();

// Display table of articles for that user
require_once('locallib.php');
$userarticles =
        uniljournal_get_article_instances(['uniljournalid' => $uniljournal->id, 'userid' => $foreign_user->id], true);

$table = new html_table();
$head = [get_string('name'), get_string('lastmodified'), get_string('revisions', 'uniljournal'),
        get_string('template', 'uniljournal'), get_string('theme', 'uniljournal'),
        get_string('articlestate', 'uniljournal'),];

// Add actions
$actionarray = [];

if (has_capability('mod/uniljournal:createarticle', $context) || has_capability('mod/uniljournal:editallarticles',
                $context)
) {
    $head[] = get_string('actions');
    $actionarray[] = 'edit';
}

$table->head = $head;

// Pile the number of uncorrected articles
$sumuncorrected = 0;
$smforms = [];
foreach ($userarticles as $ai) {
    require_once('view_choose_template_form.php');
    $uniljournal_statuses =
            uniljournal_article_status(has_capability('mod/uniljournal:viewallarticles', $context), $ai->status);
    $currententry = new stdClass();
    $currententry->aid = $ai->id;
    $statuskey = 'status_' . $ai->id;
    $currententry->statuskey = $statuskey;
    $currententry->$statuskey = $ai->status;
    $smforms[$ai->id] = new status_change_form(new moodle_url('/mod/uniljournal/view_articles.php',
            ['id' => $id, 'uid' => $uid, 'n' => $n, 'aid' => $ai->id, 'action' => 'change_state']),
            ['options' => $uniljournal_statuses, 'currententry' => $currententry,], null, null, null,
            $ai->status == 40);
}

if ($action && $aid) {
    if (!$ai = $userarticles[$aid]) {
        print_error('mustexist', 'mod_uniljournal');
    }
    if ($action == "change_state" && has_capability('mod/uniljournal:editallarticles',
                    $context) && array_key_exists($aid, $userarticles)
    ) {
        $ai = $userarticles[$aid];
        $smform = $smforms[$aid];
        $smid = 'status_' . $aid; // Don't take it from the form, out of 'security'
        if ($smform->is_cancelled()) {
            // Should not happen!
        }
        elseif ($entry = $smform->get_data()) {
            $ai->status = $entry->$smid;
            $DB->update_record('uniljournal_articleinstances', $ai);
        }
    }
}

foreach ($userarticles as $ua) {
    $row = new html_table_row();
    $title = uniljournal_articletitle($ua);
    $row->cells[] =
            html_writer::link(new moodle_url('/mod/uniljournal/view_article.php', ['id' => $ua->id, 'cmid' => $cm->id]),
                    $title);
    $row->cells[] = userdate($ua->timemodified,
            get_string('strftimedaydatetime', 'langconfig')); //strftime('%c', $ua->timemodified);
    $maxversion = $DB->get_field_select('uniljournal_aeinstances', 'MAX(version) AS maxversion', 'instanceid = :instanceid', array('instanceid' => $ua->id), MUST_EXIST);
    $row->cells[] = $maxversion;
    $corrected = $ua->status == 50;

    $PAGE->requires->yui_module('moodle-core-formautosubmit', 'M.core.init_formautosubmit',
            [['selectid' => 'id_status_' . $ua->id, 'nothing' => false]]);
    // Add class to the form, to hint CSS for label hiding
    $statecell = new html_table_cell($smforms[$ua->id]->render());
    $statecell->attributes['class'] = 'state_form';
    $row->cells[] = $ua->amtitle;
    $row->cells[] = $ua->themetitle;
    $row->cells[] = $statecell;

    $actions = "";
    foreach ($actionarray as $actcode) {
        if ($actcode == 'edit') {
            $script = 'edit_article.php';
            $args = ['cmid' => $cm->id, 'id' => $ua->id, 'amid' => $ua->amid];
        }

        $url = new moodle_url('/mod/uniljournal/' . $script, $args);
        $img = html_writer::img($OUTPUT->pix_url('t/' . $actcode), get_string($actcode));
        $actions .= html_writer::link($url, $img) . "\t";
    }
    if (!empty($actions)) {
        $row->cells[] = $actions;
    }

    $table->data[] = $row;
}

// Print the page header.
$PAGE->set_url('/mod/uniljournal/view_articles.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($uniljournal->name . ' - ' . fullname($foreign_user,
                has_capability('moodle/site:viewfullnames', $context))));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($uniljournal->name));

echo html_writer::tag('h3', fullname($foreign_user, has_capability('moodle/site:viewfullnames', $context)));
if ($sumuncorrected > 0) {
    $a = new stdClass();
    $a->uncorrected = $sumuncorrected;
    echo html_writer::tag('div', get_string('uncorrected_articles', 'uniljournal', $a));
}

echo html_writer::table($table);

// Finish the page.
echo $OUTPUT->footer();
