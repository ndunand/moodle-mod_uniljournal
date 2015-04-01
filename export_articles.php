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
require_capability('mod/uniljournal:view', $context);

// Print the page header.
$PAGE->set_url('/mod/uniljournal/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($uniljournal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->jquery();

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($uniljournal->name));

// Display table of my articles
require_once('locallib.php');
if (has_capability('mod/uniljournal:viewallarticles', $context)) {
    $articleinstances = uniljournal_get_article_instances(array('uniljournalid' => $uniljournal->id), true);
} else {
    $articleinstances = uniljournal_get_article_instances(array('uniljournalid' => $uniljournal->id, 'userid' => $USER->id), true);
}

// Status modifier forms
$smforms = array();
$users = array();
foreach($articleinstances as $ai) {
    require_once('view_choose_template_form.php');
    $uniljournal_statuses = uniljournal_article_status(has_capability('mod/uniljournal:viewallarticles', $context), $ai->status);
    $currententry = new stdClass();
    $currententry->aid = $ai->id;
    $statuskey = 'status_'.$ai->id;
    $currententry->statuskey = $statuskey;
    $currententry->$statuskey = $ai->status;
    $smforms[$ai->id] = new status_change_form(new moodle_url('/mod/uniljournal/view.php', array('id' => $cm->id, 'aid' => $ai->id, 'action' => 'change_state')),
        array(
            'options' => $uniljournal_statuses,
            'currententry' => $currententry,
        ));

    if (has_capability('mod/uniljournal:viewallarticles', $context)) {
        $ai->user = $DB->get_record('user', array('id' => $ai->userid));
        if (!array_key_exists($ai->userid, $users)) {
            $users[$ai->userid] = $ai->user;
        }
    }
}

if (has_capability('mod/uniljournal:viewallarticles', $context)) {
    echo '<select id="selectStudent">';
    echo '<option value="0">' . get_string('all_students', 'mod_uniljournal') . '</option>';
    foreach($users as $user) {
        echo '<option value="' . $user->id . '">' . $user->firstname . ' ' . $user->lastname . '</option>';
    }
    echo '</select><br>';
}

echo '<a href="#" id="selectall" data-select="none">' . get_string('selectallornone', 'form') . '</a>';

echo '<form id="exportForm" method="post">';

if(count($articleinstances) > 0) {
    $table = new html_table();
    $table->head = array(
        get_string('select'),
        get_string('myarticles', 'uniljournal'),
        get_string('lastmodified'),
        get_string('corrected_status', 'uniljournal'),
        get_string('template', 'uniljournal'),
        get_string('articlestate', 'uniljournal'),
        get_string('actions'),
    );
    if (has_capability('mod/uniljournal:viewallarticles', $context)) {
        array_splice($table->head, 1, 0, get_string('author', 'uniljournal'));
    }

    $aiter = 0;
    foreach($articleinstances as $ai) {
        $aiter++;
        $row = new html_table_row();
        $row->attributes['class'] = 'student' . $ai->userid;
        $script = 'edit.php';
        require_once('locallib.php');
        $title = uniljournal_articletitle($ai);
        $row->cells[] = html_writer::start_tag('input', array('type' => 'checkbox', 'value' => $ai->id, 'name' => 'articles[]'));

        if (has_capability('mod/uniljournal:viewallarticles', $context)) {
            $ualink = new moodle_url('/mod/uniljournal/view_articles.php', array('id' => $cm->id, 'uid' => $ai->userid));
            $row->cells[] = html_writer::link($ualink, fullname($ai->user, has_capability('moodle/site:viewfullnames', $context)));
        }

        $row->cells[] = html_writer::link(
            new moodle_url('/mod/uniljournal/view_article.php', array('id' => $ai->id, 'cmid' => $cm->id)),
            $title);
        $row->cells[] = strftime('%c', $ai->timemodified);
        $row->cells[] = $ai->amtitle;

        $PAGE->requires->yui_module('moodle-core-formautosubmit',
            'M.core.init_formautosubmit',
            array(array('selectid' => 'id_status_'.$ai->id, 'nothing' => false))
        );
        // Add class to the form, to hint CSS for label hiding
        $statecell = new html_table_cell($smforms[$ai->id]->render());
        $statecell->attributes['class'] = 'state_form';
        $row->cells[] = $statecell;

        $actionarray = array();
        $actionarray[] = 'edit';
        if (has_capability('mod/uniljournal:deletearticle', $context)) $actionarray[] = 'delete';

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
echo '</form><button class="showArticles" id="showHTMLArticles" type="submit" disabled="true" onclick="actionForm(\'' . new moodle_url('/mod/uniljournal/articles_to_html_or_pdf.php', array('cmid' => $cm->id, 'format' => 'html')) . '\')">Display articles</button>
<button class="showArticles" id="showPDFArticles" type="submit" disabled="true" onclick="actionForm(\'' . new moodle_url('/mod/uniljournal/articles_to_html_or_pdf.php', array('cmid' => $cm->id, 'format' => 'pdf')) . '\')">Export articles to PDF</button>';

echo "<script>
    function actionForm(action) {
        $('#exportForm').prop('action', action);
    }
    $('#selectall').on('click', function (e) {
        if ($('#selectall').data('select') == 'none') {
            $('input[name=\"articles[]\"]:not(:disabled)').prop('checked', true);
            $('#selectall').data('select', 'all');
            if ($('input[name=\"articles[]\"]:not(:disabled)').length) {
                $('.showArticles').prop('disabled', false);
            }
        } else {
            $('input[name=\"articles[]\"]:not(:disabled)').prop('checked', false);
            $('#selectall').data('select', 'none');
            $('.showArticles').prop('disabled', true);
        }
    });

    $('input[name=\"articles[]\"]').on('change', function (e) {
        if ($('input[name=\"articles[]\"]:checked').length > 0) {
            $('.showArticles').prop('disabled', false);
                $('#selectall').data('select', 'none');
            if ($('input[name=\"articles[]\"]:checked').length == $('input[name=\"articles[]\"]').length) {
                $('#selectall').data('select', 'all');
            }
        } else {
            $('.showArticles').prop('disabled', true);
            $('#selectall').data('select', 'none');
        }
    });

    $('#selectStudent').on('change', function(e) {
        if ($(this).val() == 0) {
            $('input[name=\"articles[]\"]').prop('checked', false);
            $('input[name=\"articles[]\"]').prop('disabled', false);
            $('#selectall').data('select', 'none');
            $('.showArticles').prop('disabled', true);
            $('tr[class^=\"student\"]').show();
        } else {
            $('input[name=\"articles[]\"]').prop('checked', false);
            $('input[name=\"articles[]\"]').prop('disabled', true);
            $('#selectall').data('select', 'none');
            $('.showArticles').prop('disabled', true);
            $('tr[class^=\"student\"]').hide();
            $('tr[class^=\"student' + $(this).val() + '\"]').show();
            $('tr[class^=\"student' + $(this).val() + '\"] input[name=\"articles[]\"]').prop('disabled', false);
        }
    });
</script>";


// Finish the page.
echo $OUTPUT->footer();
