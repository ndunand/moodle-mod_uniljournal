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
require_once(dirname(__FILE__) . '/locallib.php');

$cmid = optional_param('cmid', 0, PARAM_INT);  // Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);    // Article instance ID
$version = optional_param('version', 0, PARAM_INT);    // Article instance ID
$retry = optional_param('retry', 0, PARAM_INT);
$denied = optional_param('denied', 0, PARAM_INT);

if ($denied) {
    redirect(new moodle_url('/mod/uniljournal/view_article.php', [
            'id'   => $id,
            'cmid' => $cmid
    ]), get_string('error_lockrequest_denied', 'mod_uniljournal'), null, \core\output\notification::NOTIFY_ERROR);
}

if ($retry) {
    $PAGE->requires->js('/mod/uniljournal/retry_edit_article.js');
}

if ($cmid and $id) {
    $cm = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $uniljournal = $DB->get_record('uniljournal', ['id' => $cm->instance], '*', MUST_EXIST);
    $articleinstances = uniljournal_get_article_instances(['id' => $id], true);
    $articleinstance = array_pop($articleinstances);
}
else {
    print_error('id_missing', 'mod_uniljournal');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
if (uniljournal_is_my_articleinstance($articleinstance, $USER->id)) {
    require_capability('mod/uniljournal:view', $context);
}
else {
    require_capability('mod/uniljournal:viewallarticles', $context);
}

// Get all elements of the model
$articleelements = $DB->get_records_select('uniljournal_articleelements',
        "articlemodelid = $articleinstance->amid ORDER BY sortorder ASC");

// Log the article read action
$event = \mod_uniljournal\event\article_read::create(['other' => ['userid'    => $USER->id,
                                                                  'articleid' => $articleinstance->id],
                                                      'courseid' => $course->id, 'objectid' => $articleinstance->id,
                                                      'context' => $context,]);
$event->trigger();

$articletitle = uniljournal_articletitle($articleinstance);
$articleinstance->title = $articletitle;

$PAGE->set_url('/mod/uniljournal/view_article.php', ['id' => $articleinstance->id, 'cmid' => $cm->id]);
$PAGE->set_title(format_string($articletitle));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->jquery();
$uniljournal_renderer = $PAGE->get_renderer('mod_uniljournal');
$actualversion = 0;

$article_html = $uniljournal_renderer->display_article($articleinstance, $articleelements, $context, false, $version,
        $actualversion)[0];

// Output starts here.
echo $OUTPUT->header();

if ($articleinstance->userid !== $USER->id) {
    $author = $DB->get_record('user', ['id' => $articleinstance->userid]);
    echo $OUTPUT->heading(fullname($author, has_capability('moodle/site:viewfullnames', $context)));
}
echo $OUTPUT->heading(format_string($articletitle) . " - " . get_string('preview',
                'mod_uniljournal') . " (" . get_string('version', 'mod_uniljournal') . ": " . $actualversion . ")");

echo '<div class="article clearfix">';

require_once('locallib.php');
echo uniljournal_versiontoggle($articleinstance, $cm, $actualversion);

echo '<div class="article-compare">';
if ($articleinstance->maxversion > 1) {
    $versionA = ($articleinstance->maxversion == 1) ? 2 : ($articleinstance->maxversion - 1);

    echo html_writer::link(new moodle_url('/mod/uniljournal/compare.php',
            ['id' => $id, 'cmid' => $cmid, 'versionA' => $versionA, 'versionB' => $actualversion]),
            get_string('compare', 'uniljournal'));
}
echo '</div>';

echo '<div class="article-edit ' . ($uniljournal->comments_allowed ? '' : 'nocomments') . '">';

if (has_capability('mod/uniljournal:createarticle', $context) || has_capability('mod/uniljournal:editallarticles',
                $context)) {
    $editarticle_label =
            (has_capability('mod/uniljournal:editallarticles', $context)) ? ('editarticle_teacher') : ('editarticle');
    if (uniljournal_is_my_articleinstance($articleinstance, $USER->id) && in_array($articleinstance->status, uniljournal_noneditable_statuses())) {
        $a = (object)['status' => get_string('status' . $articleinstance->status, 'mod_uniljournal')];
        if (has_capability('mod/uniljournal:editallarticles', $context)) {
            \core\notification::add(get_string('readonlyforstudentbecausestatus', 'mod_uniljournal', $a),
                    \core\notification::WARNING);
            echo html_writer::link(new moodle_url('/mod/uniljournal/edit_article.php', [
                    'cmid' => $cmid,
                    'id'   => $id,
                    'amid' => $articleinstance->amid
            ]), $OUTPUT->pix_icon('t/edit', get_string('edit')) . get_string($editarticle_label, 'uniljournal'));
        }
        else {
            \core\notification::add(get_string('readonlybecausestatus', 'mod_uniljournal', $a),
                    \core\notification::WARNING);
        }
    }
    else {
        echo html_writer::link(new moodle_url('/mod/uniljournal/edit_article.php', [
                'cmid' => $cmid,
                'id'   => $id,
                'amid' => $articleinstance->amid
        ]), $OUTPUT->pix_icon('t/edit', get_string('edit')) . get_string($editarticle_label, 'uniljournal'));
    }
}

echo format_text($article_html);

echo '</div>';

if ($uniljournal->comments_allowed) {
    echo '<div class="article-comments">';
    echo html_writer::tag('h3', get_string('comments'));
    echo $uniljournal_renderer->display_comments($cmid, $id, $actualversion, $USER->id, $articleinstance->maxversion);
    echo '</div>';

    echo '<script>
    $(\'.delete-comment\').on(\'click\', function (e) {
        e.preventDefault();
        var confirmation = confirm(\'' . get_string('comment_deletion_confirmation', 'mod_uniljournal') . '\');
        if (confirmation) {
            window.location.href = $(this).prop(\'href\');
        }
    });
</script>';
}

echo '</div>';

// Finish the page.
echo $OUTPUT->footer();
