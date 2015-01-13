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
require_capability('mod/uniljournal:managetemplates', $context);

$instructionsoptions = array('trusttext'=> true, 'maxfiles'=> 0, 'context'=> $context, 'subdirs'=>0);

if ($id) { // if entry is specified
    if (!$entry = $DB->get_record('uniljournal_articlemodels', array('id' => $id))) {
        print_error('invalidentry');
    }
    $elements = $DB->get_records('uniljournal_articleelements', array('articlemodelid' => $id), 'sortorder');
} else { // new entry
    $entry = new stdClass();
    $entry->id = null;
    $elements = array();
}

$entry = file_prepare_standard_editor($entry, 'instructions', $instructionsoptions, $context, 'mod_uniljournal', 'articletemplates', $entry->id);
$entry->cmid = $cm->id;

require_once('edit_template_form.php');
$customdata = array();
$customdata['current'] = $entry;
$customdata['course'] = $course;
$customdata['instructionsoptions'] = $instructionsoptions;
$customdata['cm'] = $cm;
$customdata['elements'] = $elements;
$customdata['elementsoptions'] = uniljournal_get_elements_array();

$mform = new template_edit_form(null, $customdata);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/uniljournal/manage_templates.php', array('cmid' => $cm->id)));
} else if ($entry = $mform->get_data()) {
    $isnewentry = empty($entry->id);

    $entry->instructions       = '';          // updated later
    $entry->instructionsformat = FORMAT_HTML; // updated later
    $entry->sortorder = 0; // TODO: See if it's needed to put it up last.
    $entry->hidden = false;
    $entry->uniljournalid = $uniljournal->id;

    if ($isnewentry) {
        // Add new entry.
        $entry->id = $DB->insert_record('uniljournal_articlemodels', $entry);
    } else {
        // Update existing entry.
        $DB->update_record('uniljournal_articlemodels', $entry);
    }

    $articleelementorder = 0;
    foreach($entry->articleelements as $articleelementid => $articleelement) {
      $articleelementobject = new stdClass();
      $articleelementobject->articlemodelid = $entry->id;
      $articleelementobject->sortorder = $articleelementorder++;
      if(array_key_exists($articleelement, $customdata['elementsoptions'])) {
        $articleelementobject->element_type = $articleelement;
        if ($articleelement !== "0") {
          if(!array_key_exists($articleelementid, $elements)) { // -1 should never be in there
            // Add new entry
            $articleelementobject->id = $DB->insert_record('uniljournal_articleelements', $articleelementobject);
          } else {
            // Old element, update!
            $articleelementobject->id = $articleelementid;
            $DB->update_record('uniljournal_articleelements', $articleelementobject);
            // Don't delete it later on
            unset($elements[$articleelementid]);
          }
        }
      }
    }

    // Delete the elements that were there before and that aren't here anymore (see "unset(" above)
    foreach($elements as $articleelementid => $articleelement) {
      // TODO: Check if we can really delete it (it is not used anywhere)
      $DB->delete_records('uniljournal_articleelements', array('id' => $articleelementid));
    }

    // save and relink embedded images and save attachments
    $entry = file_postupdate_standard_editor($entry, 'instructions', $instructionsoptions, $context, 'mod_uniljournal', 'articletemplates', $entry->id);
    // store the updated value values
    $DB->update_record('uniljournal_articlemodels', $entry);

    redirect(new moodle_url('/mod/uniljournal/manage_templates.php', array('id' => $cm->id)));
}

$url = new moodle_url('/mod/uniljournal/edit_template.php', array('cmid'=>$cm->id));
if (!empty($id)) {
    $url->param('id', $id);
}
$PAGE->set_url($url);
$PAGE->set_title(format_string($uniljournal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managetemplates', 'mod_uniljournal'));

//displays the form
$mform->display();

echo $OUTPUT->footer();
