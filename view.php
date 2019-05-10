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
$action = optional_param('action', 0, PARAM_TEXT);
$aid = optional_param('aid', 0, PARAM_INT);
$changegroup = optional_param('group', -1, PARAM_INT);   // choose the current group

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

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/uniljournal:view', $context);

$event = \mod_uniljournal\event\course_module_viewed::create(['objectid' => $PAGE->cm->instance,
                                                              'context'  => $PAGE->context,]);
$event->add_record_snapshot('course', $PAGE->course);
// In the next line you can use $PAGE->activityrecord if you have set it, or skip this line if you don't have a record.
// $event->add_record_snapshot($PAGE->cm->modname, $PAGE->activityrecord);
$event->trigger();

require_once('locallib.php');

// Get available article templates
$articlemodels = $DB->get_records_select('uniljournal_articlemodels',
        "uniljournalid = $uniljournal->id AND hidden != '\x31' ORDER BY sortorder ASC");

// Creating new article is only allowed for students
if (has_capability('mod/uniljournal:createarticle', $context)) {
    $templatesoptions = [];
    $templatesoptions[-1] = get_string('addarticle', 'mod_uniljournal');

    $templdescs = uniljournal_get_template_descriptions($uniljournal->id);

    foreach ($articlemodels as $amid => $am) {
        $templatesoptions[$amid] = $am->title;
        if (array_key_exists($amid, $templdescs)) {
            $templatesoptions[$amid] .= " (" . implode($templdescs[$amid], ',') . ")";
        }
    }
}

// Display table of my articles
require_once('locallib.php');

if (groups_get_activity_groupmode($cm) == NOGROUPS) {
    $articleinstances = uniljournal_get_article_instances([
            'uniljournalid' => $uniljournal->id,
            'userid'        => $USER->id
    ], true);
}
else {
    $articleinstances = uniljournal_get_article_instances([
            'uniljournalid' => $uniljournal->id,
            'groupid'        => uniljournal_get_activegroup()
    ], true);
}

// Status modifier forms
$smforms = [];
foreach ($articleinstances as $ai) {
    require_once('view_choose_template_form.php');
    $uniljournal_statuses =
            uniljournal_article_status(has_capability('mod/uniljournal:viewallarticles', $context), $ai->status);
    $currententry = new stdClass();
    $currententry->aid = $ai->id;
    $statuskey = 'status_' . $ai->id;
    $currententry->statuskey = $statuskey;
    $currententry->$statuskey = $ai->status;
    $smforms[$ai->id] = new status_change_form(new moodle_url('/mod/uniljournal/view.php',
            ['id' => $cm->id, 'aid' => $ai->id, 'action' => 'change_state']),
            ['options' => $uniljournal_statuses, 'currententry' => $currententry,], null, null, null,
            !in_array($ai->status, uniljournal_immutable_statuses()));
}

if ($action && $aid) {
    if (!$ai = $articleinstances[$aid]) {
        print_error('mustexist', 'mod_uniljournal');
    }
    if ($action == "delete" && has_capability('mod/uniljournal:deletearticle', $context) && array_key_exists($aid,
                    $articleinstances)
    ) {
        require_once('edit_article_form.php');
        $customdata = [];
        $customdata['course'] = $course;
        $customdata['cm'] = $cm;
        $customdata['current'] = $ai;

        $deleteform = new article_delete_form(new moodle_url('/mod/uniljournal/view.php',
                ['id' => $cm->id, 'aid' => $aid, 'action' => 'delete']), $customdata);

        if ($deleteform->is_cancelled()) {
            unset($deleteform);
        }
        elseif (($entry = $deleteform->get_data()) && $entry->confirm == 1) {
            // Delete the record in question
            $DB->delete_records('uniljournal_article_comments', ['articleinstanceid' => $ai->id]);
            $DB->delete_records('uniljournal_aeinstances', ['instanceid' => $ai->id]);
            $DB->delete_records('uniljournal_articleinstances', ['id' => $ai->id]);
            unset($articleinstances[$aid]);
            unset($deleteform);

            // Log the article deletion
            $event = \mod_uniljournal\event\article_deleted::create(['other' => ['userid'    => $USER->id,
                                                                                 'articleid' => $ai->id],
                                                                     'courseid' => $course->id, 'objectid' => $ai->id,
                                                                     'context' => $context,]);
            $event->trigger();
        }
    }
    else if ($action == "change_state" && has_capability('mod/uniljournal:createarticle',
                    $context) && array_key_exists($aid, $articleinstances)
    ) {
        $ai = $articleinstances[$aid];
        $smform = $smforms[$aid];
        $smid = 'status_' . $aid; // Don't take it from the form, out of 'security'
        if ($smform->is_cancelled()) {
            // Should not happen!
        }
        elseif ($entry = $smform->get_data()) {
            $ai->status = $entry->$smid;
            $DB->update_record('uniljournal_articleinstances', $ai);
            redirect(new moodle_url('/mod/uniljournal/view.php', ['id' => $cm->id]));
        }
    }
}

// Print the page header.
$PAGE->set_url('/mod/uniljournal/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($uniljournal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($uniljournal->name));

// find out current groups mode
if (!has_capability('moodle/site:accessallgroups', $context)) {
    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/uniljournal/view.php?id=' . $cm->id);
}

if (isset($deleteform)) {
    $deleteform->display();
}
else {
    if (isset($uniljournal->subtitle)) {
        echo html_writer::tag('h3', $uniljournal->subtitle);
    }

    //  if($logo = uniljournal_get_logo($context)) {
    //    $url = moodle_url::make_pluginfile_url($logo->get_contextid(), $logo->get_component(), $logo->get_filearea(), $logo->get_itemid(), $logo->get_filepath(), $logo->get_filename());
    //    $logoimg = html_writer::img($url, 'Logo');
    //    echo html_writer::tag('div', $logoimg, array('class' => 'logo'));
    //  }

    // View for teachers and non-editing teachers: all submitted articles
    if (has_capability('mod/uniljournal:viewallarticles', $context)) {

        // Display table of my articles
        require_once('locallib.php');
        $allarticles = uniljournal_get_article_instances(['uniljournalid' => $uniljournal->id], true);
        if (count($allarticles) > 0) {
            $userarticles = [];
            $sumuncorrected = 0;
            foreach ($allarticles as &$article) {
                if (groups_get_activity_groupmode($cm) != NOGROUPS) {
                   $article->userid = $article->groupid;
                }
                if (!array_key_exists($article->userid, $userarticles)) {
                    $a = new stdClass();
                    $a->userid = $article->userid;
                    $a->timemodified = $article->timemodified;
                    $a->narticles = 0;
                    $a->ncorrected = 0;
                    if (groups_get_activity_groupmode($cm) != NOGROUPS) {
                        $a->user = $DB->get_record('groups', ['id' => $article->userid]);
                        if (!$a->user) {
//                            continue;
                            // user not in a group, drop this article
                        }
                    }
                    else {
                        $a->user = $DB->get_record('user', ['id' => $article->userid]);
                    }
                    $userarticles[$article->userid] = $a;
                }

                $userarticles[$article->userid]->narticles++;
                $userarticles[$article->userid]->timemodified =
                        max($userarticles[$article->userid]->timemodified, $article->timemodified);

                if ($article->status == UNILJOURNAL_STATUS_TOCORRECT) {
                    $userarticles[$article->userid]->ncorrected++;
                    $sumuncorrected++;
                }
            }

            if ($sumuncorrected > 0) {
                $a = new stdClass();
                $a->students = count($userarticles);
                $a->uncorrected = $sumuncorrected;
                echo html_writer::tag('div', get_string('student_uncorrected_articles', 'uniljournal', $a));
            }

            $table = new html_table();
            $table->head = [get_string('name'), get_string('articles_num', 'uniljournal'),
                    get_string('articles_uncorrected', 'uniljournal'), get_string('lastmodified'),];

            foreach ($userarticles as $ua) {
                $row = new html_table_row();
                if (groups_get_activity_groupmode($cm) != NOGROUPS) {
                    if (!$ua->user) {
                        $ua->user = new stdClass();
                        $ua->user->name = '(' . get_string('nogroup', 'group') . ')';
                        $ua->userid = 0;
                    }
                    $ualink = new moodle_url('/mod/uniljournal/view_articles.php', ['id' => $cm->id, 'gid' => $ua->userid]);
                    $row->cells[] = html_writer::link($ualink, $ua->user->name);
                }
                else {
                    $ualink = new moodle_url('/mod/uniljournal/view_articles.php', ['id' => $cm->id, 'uid' => $ua->userid]);
                    $row->cells[] = html_writer::link($ualink,
                            fullname($ua->user, has_capability('moodle/site:viewfullnames', $context)));
                }
                $row->cells[] = html_writer::tag('span', $ua->narticles);
                $row->cells[] = html_writer::tag('span', $ua->ncorrected);
                $row->cells[] = userdate($ua->timemodified,
                        get_string('strftimedaydatetime', 'langconfig')); //strftime('%c', $ua->timemodified);

                $table->data[] = $row;
            }

            echo html_writer::table($table);
        }
    }

    if (!has_capability('moodle/site:accessallgroups', $context) && count($articleinstances) > 0) {
        $table = new html_table();
        $table->head = [get_string('myarticles', 'uniljournal'), get_string('lastmodified'),
                get_string('template', 'uniljournal'), get_string('theme', 'uniljournal'),
                get_string('articlestate', 'uniljournal'), get_string('actions'),];

        $aiter = 0;
        foreach ($articleinstances as $ai) {
            $aiter++;
            $row = new html_table_row();
            $script = 'edit.php';
            require_once('locallib.php');
            $title = uniljournal_articletitle($ai);

            $row->cells[] = html_writer::link(new moodle_url('/mod/uniljournal/view_article.php',
                    ['id' => $ai->id, 'cmid' => $cm->id]), $title);
            $editlocked = (uniljournal_is_article_locked($ai->id, $USER->id)) ? (' (<strong>' . get_string('editlocked', 'mod_uniljournal') . '</strong>)') : ('');
            $row->cells[] = userdate($ai->timemodified,
                    get_string('strftimedaydatetime', 'langconfig')) . $editlocked; //strftime('%c', $ai->timemodified);
            $row->cells[] = $ai->amtitle;
            $row->cells[] = $ai->themetitle;

            $PAGE->requires->js('/mod/uniljournal/javascript.js');
            // Add class to the form, to hint CSS for label hiding
            $statecell = new html_table_cell($smforms[$ai->id]->render());
            $statecell->attributes['class'] = 'state_form';
            $row->cells[] = $statecell;

            $actionarray = [];
            if (!(uniljournal_is_my_articleinstance($ai, $USER->id) && in_array($ai->status, uniljournal_noneditable_statuses()))) {
                $actionarray[] = 'edit';
            }
            if (has_capability('mod/uniljournal:deletearticle', $context)) {
                $actionarray[] = 'delete';
            }

            $actions = "";
            foreach ($actionarray as $actcode) {
                $script = 'view.php';
                $args = ['id' => $cm->id, 'aid' => $ai->id, 'action' => $actcode];

                if ($actcode == 'edit') {
                    $script = 'edit_article.php';
                    $args = ['cmid' => $cm->id, 'id' => $ai->id, 'amid' => $ai->amid];
                }

                $url = new moodle_url('/mod/uniljournal/' . $script, $args);
                $img = $OUTPUT->pix_icon('t/' . $actcode, get_string($actcode));
                $actions .= html_writer::link($url, $img) . "\t";
            }
            $row->cells[] = $actions;
            $table->data[] = $row;
        }
        echo html_writer::table($table);
    }

    // Creating new article is only allowed for students
    if (has_capability('mod/uniljournal:createarticle', $context)) {
        if (count($articlemodels) > 1) {
            require_once('view_choose_template_form.php');
            $customdata = [];
            $customdata['options'] = $templatesoptions;

            $mform = new choose_template_form(new moodle_url('/mod/uniljournal/edit_article.php', ['cmid' => $cm->id]),
                    $customdata);
            //displays the form, with an auto-submitter and no change checker
            $mform->display();

            $PAGE->requires->js('/mod/uniljournal/javascript.js');
        }
        else {
            $am = array_pop($articlemodels);
            if ($am) {
                echo html_writer::link(new moodle_url('/mod/uniljournal/edit_article.php',
                        ['cmid' => $cm->id, 'amid' => $am->id]),
                        get_string('addarticletempl', 'mod_uniljournal', $templatesoptions[$am->id]));
            }
            else {
                echo $OUTPUT->error_text(get_string('notemplates', 'mod_uniljournal'));
            }
        }
    }

    if (has_capability('mod/uniljournal:managetemplates', $context) && count($articlemodels) == 0) {
        echo html_writer::link(new moodle_url('/mod/uniljournal/manage_templates.php', ['id' => $PAGE->cm->id]),
                ' ' . get_string("managetemplates", "mod_uniljournal"));
    }
}

// Finish the page.
echo $OUTPUT->footer();
