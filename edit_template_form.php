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
 * @copyright  2015 Liip AG {@link http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class template_edit_form extends moodleform {
    protected $course;

    public function definition() {

        global $CFG;
        $course              = $this->_customdata['course'];
        $cm                  = $this->_customdata['cm'];
        $instructionsoptions = $this->_customdata['instructionsoptions'];
        $currententry        = $this->_customdata['current'];
        $elements            = $this->_customdata['elements'];
        $elementsoptions     = $this->_customdata['elementsoptions'];
        $context  = context_module::instance($cm->id);

        $this->course  = $course;
    
        $mform = $this->_form;

        $mform->addElement('text', 'title', get_string('template_title', 'uniljournal'), array('size' => '64'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('title', 'template_title', 'uniljournal');
        
        // Let's prepare the maxbytes popup.
        $choices = get_max_upload_sizes($CFG->maxbytes, $this->course->maxbytes, 0, 0);
        $mform->addElement('select', 'maxbytes', get_string('maximumupload'), $choices);
        $mform->addHelpButton('maxbytes', 'maximumupload');
        
        $mform->addElement('editor', 'instructions_editor', get_string('template_instructions', 'uniljournal'), null, $instructionsoptions);
        $mform->setType('instructions_editor', PARAM_RAW);
        $mform->addHelpButton('instructions_editor', 'template_instructions', 'uniljournal');

        foreach($elements as $element) {
          $select = $mform->addElement('select', 'articleelements['.$element->id.']', get_string('template_element', 'uniljournal'), $elementsoptions);
          $mform->setType('articleelements['.$element->id.']', PARAM_TEXT);
          $select->setSelected($element->element_type);
        }
        
        $newelementsid = -1;
        
        $mform->addElement('select', 'articleelements['.$newelementsid.']', get_string('template_element', 'uniljournal'), $elementsoptions);
        $mform->setType('articleelements['.$newelementsid.']', PARAM_TEXT);
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

//-------------------------------------------------------------------------------
        $this->set_data($currententry);
    }
}