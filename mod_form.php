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

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_uniljournal_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('ujname', 'uniljournal'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'ujname', 'uniljournal');
        
        $mform->addElement('text', 'subtitle', get_string('ujsubtitle', 'uniljournal'), array('size' => '64'));
        $mform->setType('subtitle', PARAM_TEXT);
        $mform->addRule('subtitle', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('subtitle', 'ujsubtitle', 'uniljournal');

        $this->add_intro_editor(false);
        
        $filemanager_options = array();
        $filemanager_options['accepted_types'] = array('.jpg', '.jpeg', '.png');
        $filemanager_options['subdirs'] = false;
        $filemanager_options['maxfiles'] = 1;
        $mform->addElement('filemanager', 'logo', get_string('ujlogo', 'uniljournal'), null, $filemanager_options);
        $mform->addHelpButton('logo', 'ujlogo', 'uniljournal');
        
        $mform->addElement('selectyesno', 'comments_allowed', get_string('ujcomments_allowed', 'uniljournal'));
        $mform->addHelpButton('comments_allowed', 'ujcomments_allowed', 'uniljournal');
        
        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Enforce defaults here
     *
     * @param array $default_values Form defaults
     * @return void
     **/
    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            $filemanager_options = array();
            $filemanager_options['accepted_types'] = array('.jpg', '.jpeg', '.png');
            $filemanager_options['subdirs'] = false;
            $filemanager_options['maxfiles'] = 1;
            $draftitemid = file_get_submitted_draft_itemid('logo');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_uniljournal', 'logo', 0, $filemanager_options);
            $default_values['logo'] = $draftitemid;
        }
    }
}
