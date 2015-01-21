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
 * uniljournal module admin settings and defaults
 *
 * @package    mod_uniljournal
 * @copyright  2015 Liip AG {@link http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    // Get available mimetype groups from the mimetype arrays.
    $groups = array();
    foreach(get_mimetypes_array() as $mimearray) {
        if (array_key_exists('groups', $mimearray) && count($mimearray['groups']) > 0) {
            $groups = array_merge($groups, $mimearray['groups']);
        }
    }
    $keyvalgroups = array();
    foreach($groups as $gid => $group) {
        $keyvalgroups[$group] = $group; //' ('.get_string('element_attachment_'.$group, 'uniljournal').')';
    }
    ksort($keyvalgroups);
    
    $settings->add(new admin_setting_configmultiselect('uniljournal/allowedmimegroups',
        get_string('allowedmimegroups', 'uniljournal'), get_string('allowedmimegroupsdescription', 'uniljournal'),
        $keyvalgroups, $keyvalgroups));
}
