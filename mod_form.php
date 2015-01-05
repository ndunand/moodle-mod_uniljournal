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
 * @copyright  2014 Liip AG {@link http://www.liip.ch/}
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
        
        $mform->addElement('textarea', 'description', get_string('ujdescription', 'uniljournal'), '');
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('description', 'ujdescription', 'uniljournal');
        
        $mform->addElement('filepicker', 'logo', get_string('ujlogo', 'uniljournal'), null,
                   array('maxbytes' => 100, 'accepted_types' => 'image'));
        $mform->addHelpButton('logo', 'ujlogo', 'uniljournal');
        
        $mform->addElement('selectyesno', 'comments_allowed', get_string('ujcomments_allowed', 'uniljournal'));
        $mform->addHelpButton('comments_allowed', 'ujcomments_allowed', 'uniljournal');
        
        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
