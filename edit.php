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
 * @copyright  2015 Liip AG {@link http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$cmid  = optional_param('cmid', 0, PARAM_INT); // Course_module ID, or
$amid = optional_param('amid', 0, PARAM_INT);  // template ID

if ($cmid) {
    $cm         = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $uniljournal  = $DB->get_record('uniljournal', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    error('You must specify a course_module ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
// require_capability('mod/uniljournal:', $context);

if (!$articlemodel = $DB->get_record_select('uniljournal_articlemodels', "id = $amid AND hidden != '\x31'")) {
      print_error('invalidentry');
}
$articleelements = $DB->get_records_select('uniljournal_articleelements', "articlemodelid = $amid ORDER BY sortorder ASC");

$url = new moodle_url('/mod/uniljournal/edit.php', array('cmid'=>$cm->id, 'articlemodelid' => $amid));

$PAGE->set_url($url);
$PAGE->set_title(format_string(get_string('writearticletempl', 'mod_uniljournal', $articlemodel->title)));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('writearticletempl', 'mod_uniljournal', $articlemodel->title));

if(true) {
  echo "<p>About to create an article for model $articlemodel->title.</p>";
  echo "<p>It has the following elements:</p><ul>";
  foreach($articleelements as $articleelement) {
    echo "<li>".get_string('element_'.$articleelement->element_type, 'mod_uniljournal')."</li>";
  }
  echo "</ul>";
}
echo $OUTPUT->footer();
