<?php

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
$articleinstances = uniljournal_get_article_instances(array('uniljournalid' => $uniljournal->id, 'userid' => $USER->id));

$uniljournal_statuses = uniljournal_article_status();
// Status modifier forms
$smforms = array();
foreach($articleinstances as $ai) {
    require_once('view_choose_template_form.php');
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
}
echo '<a href="#" id="selectall" data-select="none">' . get_string('selectallornone', 'form') . '</a>';
echo '<form action="' . new moodle_url('/mod/uniljournal/articles_html.php', array('cmid' => $cm->id)) . '" method="post">';

if(count($articleinstances) > 0) {
    $table = new html_table();
    $table->head = array(
        get_string('select'),
        get_string('myarticles', 'uniljournal'),
        get_string('lastmodified'),
        get_string('template', 'uniljournal'),
        get_string('articlestate', 'uniljournal'),
        get_string('actions'),
    );

    $aiter = 0;
    foreach($articleinstances as $ai) {
        $aiter++;
        $row = new html_table_row();
        $script = 'edit.php';
        require_once('locallib.php');
        $title = uniljournal_articletitle($ai);
        $row->cells[] = html_writer::start_tag('input', array('type' => 'checkbox', 'value' => $ai->id, 'name' => 'articles[]'));
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
echo '</form><button id="showArticles" type="submit" disabled="true">Display articles</button>';

echo "<script>
    $('#selectall').on('click', function (e) {
        if ($('#selectall').data('select') == 'none') {
            $('input[name=\"articles[]\"]').prop('checked', true);
            $('#selectall').data('select', 'all');
            $('#showArticles').prop('disabled', false);
        } else {
            $('input[name=\"articles[]\"]').prop('checked', false);
            $('#selectall').data('select', 'none');
            $('#showArticles').prop('disabled', true);
        }
    });

    $('input[name=\"articles[]\"]').on('change', function (e) {
        if ($('input[name=\"articles[]\"]:checked').length > 0) {
            $('#showArticles').prop('disabled', false);
                $('#selectall').data('select', 'none');
            if ($('input[name=\"articles[]\"]:checked').length == $('input[name=\"articles[]\"]').length) {
                $('#selectall').data('select', 'all');
            }
        } else {
            $('#showArticles').prop('disabled', true);
            $('#selectall').data('select', 'none');
        }
    });
</script>";


// Finish the page.
echo $OUTPUT->footer();