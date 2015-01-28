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
 * Defines the delete article event.
 *
 * @package    mod_uniljournal
 * @copyright  2014  Liip AG {@url http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_uniljournal\event;
defined('MOODLE_INTERNAL') || die();

class article_deleted extends \core\event\base {
    public function init() {
        $this->data['crud'] = 'd';
        $this->data['objecttable'] = 'uniljournal_articleinstances';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    public static function get_name() {
        return get_string('article_deleted_name', 'mod_uniljournal');
    }

    public static function get_explanation() {
        return get_string('article_deleted_explanation', 'mod_uniljournal');
    }

    public function get_description() {
        return get_string('article_deleted_desc', 'mod_uniljournal', $this->data['other']);
    }
}
