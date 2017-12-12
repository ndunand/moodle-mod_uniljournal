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
 * @package    mod_uniljournal
 * @copyright  2014-2015  Université de Lausanne
 * @author     Liip AG eLearning Team <elearning@liip.ch>
 * @author     Didier Raboud <didier.raboud@liip.ch>
 * @author     Claude Bossy <claude.bossy@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../locallib.php');

/**
 * Structure step to restore one uniljournal activity
 */
class restore_uniljournal_activity_structure_step extends restore_activity_structure_step {

    protected $course;

    protected function define_structure() {

        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('uniljournal', '/activity/uniljournal');
        $paths[] = new restore_path_element('uniljournal_themebank', '/activity/uniljournal/themebanks/themebank');
        $paths[] = new restore_path_element('uniljournal_theme', '/activity/uniljournal/themes/theme');
        $paths[] = new restore_path_element('uniljournal_articlemodel',
                '/activity/uniljournal/articlemodels/articlemodel');
        $paths[] = new restore_path_element('uniljournal_articleelement',
                '/activity/uniljournal/articleelements/articleelement');
        if ($userinfo) {
            $paths[] = new restore_path_element('uniljournal_articleinstance',
                    '/activity/uniljournal/articleinstances/articleinstance');
            $paths[] =
                    new restore_path_element('uniljournal_aeinstance', '/activity/uniljournal/aeinstances/aeinstance');
            $paths[] = new restore_path_element('uniljournal_article_comment',
                    '/activity/uniljournal/article_comments/article_comment');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_uniljournal($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $this->course = get_course($this->get_courseid());

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the uniljournal record
        $newitemid = $DB->insert_record('uniljournal', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_uniljournal_themebank($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        if ($data->contextid != CONTEXT_MODULE) {
            $data->title = $data->title . get_string('restoredon', 'mod_uniljournal', usergetdate(time()));
        }

        switch ($data->contextid) {
            case CONTEXT_SYSTEM:
                $data->contextid = context_system::instance()->id;
                if (!canmanagethemebank($data)) {
                    $mods = get_course_mods($this->course->id);
                    foreach ($mods as $mod) {
                        if ($mod->modname == 'uniljournal' && $mod->instance == $this->get_new_parentid('uniljournal')) {
                            $data->contextid = context_module::instance($mod->id)->id;
                            break;
                        }
                    }
                }
                break;
            case CONTEXT_COURSECAT:
                $data->contextid = context_coursecat::instance($this->course->category)->id;
                if (!canmanagethemebank($data)) {
                    $mods = get_course_mods($this->course->id);
                    foreach ($mods as $mod) {
                        if ($mod->modname == 'uniljournal' && $mod->instance == $this->get_new_parentid('uniljournal')) {
                            $data->contextid = context_module::instance($mod->id)->id;
                            break;
                        }
                    }
                }
                break;
            case CONTEXT_COURSE:
                $data->contextid = context_course::instance($this->course->id)->id;
                break;
            case CONTEXT_MODULE:
                $mods = get_course_mods($this->course->id);
                foreach ($mods as $mod) {
                    if ($mod->modname == 'uniljournal' && $mod->instance == $this->get_new_parentid('uniljournal')) {
                        break;
                    }
                }
                $data->contextid = context_module::instance($mod->id)->id;
                break;
            default:
                $data->contextid = context_system::instance()->id;
                break;
        }

        $newitemid = $DB->insert_record('uniljournal_themebanks', $data);
        $this->set_mapping('uniljournal_themebank', $oldid, $newitemid);
    }

    protected function process_uniljournal_theme($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->themebankid = $this->get_mappingid('uniljournal_themebank', $data->themebankid);
        $this->add_related_files('mod_uniljournal', 'theme', 'uniljournal_theme', null, $oldid);

        $newitemid = $DB->insert_record('uniljournal_themes', $data);
        $this->set_mapping('uniljournal_theme', $oldid, $newitemid, true);
    }

    protected function process_uniljournal_articlemodel($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->uniljournalid = $this->get_new_parentid('uniljournal');
        $data->themebankid = $this->get_mappingid('uniljournal_themebank', $data->themebankid);

        $newitemid = $DB->insert_record('uniljournal_articlemodels', $data);
        $this->set_mapping('uniljournal_articlemodel', $oldid, $newitemid);
    }

    protected function process_uniljournal_articleelement($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->articlemodelid = $this->get_mappingid('uniljournal_articlemodel', $data->articlemodelid);

        $newitemid = $DB->insert_record('uniljournal_articleelements', $data);
        $this->set_mapping('uniljournal_articleelement', $oldid, $newitemid);
    }

    protected function process_uniljournal_articleinstance($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->articlemodelid = $this->get_mappingid('uniljournal_articlemodel', $data->articlemodelid);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('groups', $data->groupid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('uniljournal_articleinstances', $data);
        $this->set_mapping('uniljournal_articleinstance', $oldid, $newitemid);
    }

    protected function process_uniljournal_aeinstance($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->instanceid = $this->get_mappingid('uniljournal_articleinstance', $data->instanceid);
        $data->elementid = $this->get_mappingid('uniljournal_articleelement', $data->elementid);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('uniljournal_aeinstances', $data);
        $this->set_mapping('uniljournal_aeinstance', $oldid, $newitemid, true);
    }

    protected function process_uniljournal_article_comment($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->articleinstanceid = $this->get_mappingid('uniljournal_articleinstance', $data->articleinstanceid);
        $data->userid = $this->get_mappingid('user', $data->userid);

        $newitemid = $DB->insert_record('uniljournal_article_comments', $data);
        $this->set_mapping('uniljournal_article_comment', $oldid, $newitemid);
    }

    protected function after_execute() {
        $this->add_related_files('mod_uniljournal', 'intro', null);
        $this->add_related_files('mod_uniljournal', 'logo', null);
        $this->add_related_files('mod_uniljournal', 'elementinstance', 'uniljournal_aeinstance');
        $this->add_related_files('mod_uniljournal', 'theme', 'uniljournal_theme');
    }
}