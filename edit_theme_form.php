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
 * The main uniljournal configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_uniljournal
 * @copyright  2014-2015  Université de Lausanne
 * @author     Liip AG eLearning Team <elearning@liip.ch>
 * @author     Didier Raboud <didier.raboud@liip.ch>
 * @author     Claude Bossy <claude.bossy@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class edit_theme_form extends moodleform {
    protected $course;

    public function definition() {

        global $CFG;
        $course = $this->_customdata['course'];
        $currententry = $this->_customdata['current'];
        $instructionsoptions = $this->_customdata['instructionsoptions'];

        $this->course = $course;

        $mform = $this->_form;

        $mform->addElement('text', 'title', get_string('theme_title', 'uniljournal'), ['size' => '64']);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('title', 'theme_title', 'uniljournal');

        $mform->addElement('editor', 'instructions_editor', get_string('theme_instructions', 'uniljournal'), null,
                $instructionsoptions);
        $mform->setType('instructions_editor', PARAM_RAW);
        $mform->addHelpButton('instructions_editor', 'theme_instructions', 'uniljournal');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'tbid');
        $mform->setType('tbid', PARAM_INT);

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

        $this->set_data($currententry);
    }
}

class theme_delete_form extends moodleform {
    protected $course;

    public function definition() {

        global $CFG;
        $course = $this->_customdata['course'];
        $currententry = $this->_customdata['current'];
        $themebank = $this->_customdata['themebank'];

        $context = context::instance_by_id($themebank->contextid);

        $this->course = $course;

        $mform = $this->_form;

        $a = new stdClass();
        $a->type = get_string('themelower', 'mod_uniljournal');
        $a->name = $currententry->title;

        $mform->addElement('html', '<div>' . get_string('deletechecktypename', 'core', $a) . '</div>');

        $mform->addElement('hidden', 'confirm');
        $mform->setType('confirm', PARAM_BOOL);

        // Add standard buttons, common to all modules.
        $this->add_action_buttons(true, get_string('delete', 'core'));

        $this->set_data(['confirm' => true]);
    }
}