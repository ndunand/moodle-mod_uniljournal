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
global $USER;

$cmid  = optional_param('cmid', 0, PARAM_INT); // Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);  // template ID

if ($cmid) {
    $cm         = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $uniljournal  = $DB->get_record('uniljournal', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/uniljournal:managethemes', $context);
$module_context      = context_module::instance($cm->id);
$course_context      = context_course::instance($course->id);
$category_context    = context_coursecat::instance($course->category);
$system_context      = context_system::instance();
$user_context        = context_user::instance($USER->id);
$contexts = array(
    $module_context->id => get_string('module_context', 'uniljournal'),
    $course_context->id => get_string('course_context', 'uniljournal'),
    $category_context->id => get_string('category_context', 'uniljournal'),
    $system_context->id => get_string('system_context', 'uniljournal')
);

if ($id) { // if entry is specified
    if ((!$entry = $DB->get_record('uniljournal_themebanks', array('id' => $id))) || !array_key_exists($entry->contextid, $contexts)) {
        print_error('invalidentry');
    }
} else { // new entry
    $entry = new stdClass();
    $entry->id = null;
}

$entry->cmid = $cm->id;

require_once('edit_themebank_form.php');
$customdata = array();
$customdata['current'] = $entry;
$customdata['course'] = $course;
$customdata['cm'] = $cm;
$customdata['contexts'] = $contexts;

$mform = new themebank_edit_form(null, $customdata);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/uniljournal/manage_themebanks.php', array('id' => $cm->id)));
} else if ($entry = $mform->get_data()) {
    $isnewentry = empty($entry->id);

    if ($isnewentry) {
        // Add new entry.
        $entry->id = $DB->insert_record('uniljournal_themebanks', $entry);
    } else {
        // Update existing entry.
        $DB->update_record('uniljournal_themebanks', $entry);
    }

    redirect(new moodle_url('/mod/uniljournal/manage_themebanks.php', array('id' => $cm->id)));
}

$url = new moodle_url('/mod/uniljournal/edit_themebank.php', array('cmid'=>$cm->id));
if (!empty($id)) {
    $url->param('id', $id);
}
$PAGE->set_url($url);
$PAGE->set_title(format_string($uniljournal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managethemebanks', 'mod_uniljournal'));

//displays the form
$mform->display();

echo $OUTPUT->footer();
