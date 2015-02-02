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

$cmid    = optional_param('cmid', 0, PARAM_INT);  // Course_module ID
$articleinstanceids = $_POST['articles'];

if ($cmid) {
    $cm              = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course          = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $uniljournal     = $DB->get_record('uniljournal', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module');

}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/uniljournal/export_articles.php', array('id' => $cm->id));
$PAGE->set_title(get_string('exportarticles', 'mod_uniljournal'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$uniljournal_renderer = $PAGE->get_renderer('mod_uniljournal');

$articleinstances = uniljournal_get_article_instances(array('id' => $articleinstanceids), true);

$articles = '';

foreach($articleinstances as $articleinstance) {

    if ($articleinstance->userid == $USER->id) {
        require_capability('mod/uniljournal:view', $context);
    }

    // Get all elements of the model
    $articleelements = $DB->get_records_select('uniljournal_articleelements', "articlemodelid = $articleinstance->amid ORDER BY sortorder ASC");

    // Log the article read action
    $event = \mod_uniljournal\event\article_read::create(array(
        'other' => array(
            'userid' => $USER->id,
            'articleid' => $articleinstance->id
        ),
        'courseid' => $course->id,
        'objectid' => $articleinstance->id,
        'context' => $context,
    ));
    $event->trigger();

    $articletitle = uniljournal_articletitle($articleinstance);
    $articleinstance->title = $articletitle;

    $article_html = $uniljournal_renderer->display_article($articleinstance, $articleelements, $context);

    $articles .= $article_html;
}

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('uniljournal-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

echo $articles;

// Finish the page.
echo $OUTPUT->footer();
