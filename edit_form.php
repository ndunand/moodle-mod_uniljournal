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

class edit_form extends moodleform {
    protected $course;

    public function definition() {

        global $CFG;
        $course          = $this->_customdata['course'];
        $cm              = $this->_customdata['cm'];
        $currententry    = $this->_customdata['current'];
        $articlemodel    = $this->_customdata['articlemodel'];
        $articleelements = $this->_customdata['articleelements'];
        $context         = context_module::instance($cm->id);

        $this->course  = $course;
    
        $attachment_options = array();
//         $attachment_options['accepted_types'] = 'web_image'; // TODO: Inherit from the global admin
        $attachment_options['subdirs'] = false;
        $attachment_options['maxfiles'] = 1;
        
        $image_options = array();
        $image_options['accepted_types'] = 'web_image';
        $image_options['subdirs'] = false;
        $image_options['maxfiles'] = 1;
    
        $mform = $this->_form;
        
        $mform->addElement('html', '<div class="instructions">'.$articlemodel->instructions.'</div>');
        
        $mform->addElement('text', 'title', get_string('element_title', 'uniljournal'), array('size' => '64'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        
        foreach($articleelements as $ae) {
          $id = 'element_'.$ae->id;
          $desc = get_string('element_'.$ae->element_type, 'uniljournal');
          
          switch($ae->element_type) {
            case "attachment":
              $mform->addElement('filemanager', $id, $desc, null, $attachment_options);
              break;
            case "image":
              $mform->addElement('filemanager', $id, $desc, null, $image_options);
              break;
            case "text":
              $mform->addElement('editor', $id, $desc);
              $mform->setType($id, PARAM_RAW);
              break;
            case "title":
              $mform->addElement('text', $id, $desc, array('size' => '64'));
              $mform->setType($id, PARAM_TEXT);
              $mform->addRule($id, get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
              break;
          }
        }
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'amid');
        $mform->setType('amid', PARAM_INT);

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

        $this->set_data($currententry);
    }
}