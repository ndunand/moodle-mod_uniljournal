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
 * Edits a particular instance of a uniljournal instance
 *
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
require_once(dirname(__FILE__).'/locallib.php');

$cmid = optional_param('cmid', 0, PARAM_INT);  // Course_module ID, or
$tbid = optional_param('tbid', 0, PARAM_INT);  // theme bank ID
$id   = optional_param('id', 0, PARAM_INT);    // theme instance ID

if ($cmid && $tbid) {
    $cm         = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $themebank    = $DB->get_record('uniljournal_themebanks', array('id' => $tbid), '*', MUST_EXIST);
    $course       = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $uniljournal  = $DB->get_record('uniljournal', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID and a theme bank ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/uniljournal:managethemes', $context);

if ($id) { // if entry is specified
    if (!$theme = $DB->get_record('uniljournal_themes', array('id' => $id))) {
        print_error('invalidentry');
    }
} else { // new entry
    $theme = new stdClass();
    $theme->id = null;
}

$instructionsoptions = array(
    'subdirs'  => false,
    'maxfiles' => '12',
    'maxbytes' => 0,
    'context'  => $context
);
$theme->cmid = $cmid;
$theme->tbid = $tbid;
$theme = file_prepare_standard_editor($theme, 'instructions', $instructionsoptions, $context, 'mod_uniljournal', 'theme', $theme->id);

require_once('edit_theme_form.php');
$customdata = array();
$customdata['current'] = $theme;
$customdata['cm'] = $cm;
$customdata['course'] = $course;
$customdata['themebank'] = $themebank;
$customdata['instructionsoptions'] = $instructionsoptions;

$mform = new edit_theme_form(null, $customdata);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/uniljournal/manage_themes.php', array('cmid' => $cm->id, 'tbid' => $tbid)));
} else if ($theme = $mform->get_data()) {
    $isnewentry = empty($theme->id);

    $theme->themebankid = $tbid;
    $theme->hidden = false;
    $theme->sortorder = 0;
    $instructions = file_postupdate_standard_editor($theme, 'instructions', $instructionsoptions, $context, 'mod_uniljournal', 'theme', $theme->id);

    if ($isnewentry) {
        // Add new entry.
        $theme->id = $DB->insert_record('uniljournal_themes', $theme);

        // Log the theme creation
        $event = \mod_uniljournal\event\theme_created::create(array(
            'other' => array(
                'userid' => $USER->id,
                'themeid' => $theme->id,
                'themebankid' => $tbid
            ),
            'courseid' => $course->id,
            'objectid' => $theme->id,
            'context' => $context,
        ));
        $event->trigger();
    } else {
        // Update existing entry.
        $DB->update_record('uniljournal_themes', $theme);

        // Log the theme update
        $event = \mod_uniljournal\event\theme_updated::create(array(
            'other' => array(
                'userid' => $USER->id,
                'themeid' => $theme->id,
                'themebankid' => $tbid
            ),
            'courseid' => $course->id,
            'objectid' => $theme->id,
            'context' => $context,
        ));
        $event->trigger();
    }
    redirect(new moodle_url('/mod/uniljournal/manage_themes.php', array('cmid' => $cm->id, 'tbid' => $tbid)));
}


$url = new moodle_url('/mod/uniljournal/edit_theme.php', array('cmid'=>$cm->id, 'tbid' => $tbid));
if (!empty($id)) {
    $url->param('id', $id);
}
$PAGE->set_url($url);
$PAGE->set_title(format_string($uniljournal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managethemes', 'mod_uniljournal'));

$mform->display();

echo $OUTPUT->footer();
