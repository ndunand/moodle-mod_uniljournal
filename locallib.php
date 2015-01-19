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
 * Internal library of functions for module uniljournal
 *
 * All the uniljournal specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_uniljournal
 * @copyright  2015 Liip AG {@link http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function uniljournal_set_logo($data) {
    global $DB;
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->logo;

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_uniljournal', 'logo', 0, array('subdirs'=>false));
    }
}

function uniljournal_get_logo($context) {
    global $DB;
    $fs = get_file_storage();
    
    $logos = $fs->get_area_files($context->id, 'mod_uniljournal', 'logo', false, $sort = "itemid, filepath, filename", false);
    foreach($logos as $logo) {
      if ($logo->is_valid_image()) {
        return $logo;
      }
    }
    return false;
}

function uniljournal_get_elements_array() {
  $options = array();
  foreach(array('subtitle', 'textonly', 'text', 'image', 'attachment') as $elem) {
    $options[$elem] = get_string('element_'.$elem, 'uniljournal');
  }
  return $options;
}