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

$tables = '';

$articleinstances = uniljournal_get_article_instances(array('id' => $articleinstanceids), true);

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

    $table = new html_table();
    // Table has two cols: one for content, one for the attachements and other stuffs
    $table->data = array();

    $content = "";
    $attachments = "";

    // Get the existing article elements for display
    $actualversion = 0;
    foreach ($articleelements as $ae) {
        $property_name = 'element_' . $ae->id;
        $property_edit = $property_name . '_editor';
        $property_format = $property_name . 'format';

        $sqlargs = array('instanceid' => $articleinstance->id, 'elementid' => $ae->id);
        $sql = '
        SELECT * FROM {uniljournal_aeinstances}
          WHERE instanceid = :instanceid
            AND elementid  = :elementid ';
        /*
        if ($version != 0) {
            $sql .= 'AND version <= :version';
            $sqlargs['version'] = $version;
        }
        */
        $sql .= 'ORDER BY version DESC LIMIT 1';
        $aeinstance = $DB->get_record_sql($sql, $sqlargs);
        if ($aeinstance !== false) {
            switch ($ae->element_type) {
                case "subtitle":
                    $content .= html_writer::tag('h4', $aeinstance->value);
                    break;
                case "text":
                case "textonly":
                    $aeinstance->value = file_rewrite_pluginfile_urls($aeinstance->value, 'pluginfile.php', $context->id, 'mod_uniljournal', 'elementinstance', $aeinstance->id);
                    $content .= html_writer::tag('div', $aeinstance->value);
                    break;
            }

            if (uniljournal_startswith($ae->element_type, 'attachment_')) {
                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'mod_uniljournal', 'elementinstance', $aeinstance->id);
                if (count($files) > 0) {
                    $file = array_pop($files);
                    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                    if ($file->is_valid_image()) {
                        $attachments .= html_writer::tag('div', html_writer::img($url, $file->get_filename()));
                    } else {
                        $attachments .= html_writer::tag('div', html_writer::link($url, $file->get_filename()));
                    }
                }
            }

            $actualversion = max($actualversion, $aeinstance->version);
        }
    }

    // Build article title if it doesn't exist
    $articletitle = uniljournal_articletitle($articleinstance);

    $titlecell = new html_table_cell(html_writer::tag('h3', $articletitle));
    $titlecell->colspan = 2;
    $table->data[] = new html_table_row(array($titlecell));
    $table->data[] = new html_table_row(
        array(
            new html_table_cell($content),
            new html_table_cell($attachments),
        ));
}
$tables .= html_writer::table($table);

$PAGE->set_url('/mod/uniljournal/view_article.php', array('id' => $articleinstance->id, 'cmid' => $cm->id));
$PAGE->set_title(format_string($articletitle));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$uniljournal_renderer = $PAGE->get_renderer('mod_uniljournal');

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('uniljournal-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

// Replace the following lines with you own code.
echo $OUTPUT->heading(format_string($articletitle)." - Preview (version: ".$actualversion.")"); // TODO

echo $tables;

// Finish the page.
echo $OUTPUT->footer();
