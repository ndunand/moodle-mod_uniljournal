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

class edit_article_form extends moodleform {
    protected $course;

    public function definition() {

        global $CFG;
        $course            = $this->_customdata['course'];
        $cm                = $this->_customdata['cm'];
        $currententry      = $this->_customdata['current'];
        $articlemodel      = $this->_customdata['articlemodel'];
        $articleelements   = $this->_customdata['articleelements'];
        $textfieldoptions  = $this->_customdata['textfieldoptions'];
        $textonlyoptions   = $this->_customdata['textonlyoptions'];
        $attachmentoptions = $this->_customdata['attachmentoptions'];
        
        $imageoptions['accepted_types'] = array('web_image');
        
        $context         = context_module::instance($cm->id);

        $this->course  = $course;
    
        $mform = $this->_form;
        
        $mform->addElement('html', '<div class="instructions">'.$articlemodel->instructions.'</div>');

        if($articlemodel->freetitle == 1) {
          $mform->addElement('text', 'title', get_string('article_title', 'uniljournal'), array('size' => '64'));
          $mform->setType('title', PARAM_TEXT);
          $mform->addRule('title', null, 'required', null, 'client');
          $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        } else {
          $mform->addElement('static', 'statictitle', get_string('article_title', 'uniljournal'), 'TODO: The article title is forced to be the one from the theme');
        }

        foreach($articleelements as $ae) {
          $id = 'element_'.$ae->id;
          $desc = get_string('element_'.$ae->element_type, 'uniljournal');
          
          if(substr_compare($ae->element_type, 'attachment_', 0, 11) === 0) {
            $attoptions = $attachmentoptions;
            $attoptions['accepted_types'] = substr($ae->element_type, 11);
            $mform->addElement('filemanager', $id, $desc, null, $attoptions);
          }
          
          switch($ae->element_type) {
            case "text":
              $id .= '_editor';
              $mform->addElement('editor', $id, $desc, null, $textfieldoptions);
              $mform->setType($id, PARAM_RAW);
              break;
            case "textonly":
              $id .= '_editor';
              $mform->addElement('editor', $id, $desc, null, $textonlyoptions);
              $mform->setType($id, PARAM_RAW);
              break;
            case "subtitle":
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

class article_delete_form extends moodleform {
    protected $course;

    public function definition() {

        global $CFG;
        $course            = $this->_customdata['course'];
        $cm                = $this->_customdata['cm'];
        $currententry      = $this->_customdata['current'];
        $context           = context_module::instance($cm->id);

        $this->course  = $course;
    
        $mform = $this->_form;
        
        $a = new stdClass();
        $a->type = get_string('templatelower', 'mod_uniljournal');
        $a->name = $currententry->freetitle == 1 ? $currententry->title : 'TODO: Theme title';
        
        $mform->addElement('html', '<div>'.get_string('deletechecktypename', 'core', $a).'</div>');

        $mform->addElement('hidden', 'confirm');
        $mform->setType('confirm', PARAM_BOOL);
        
        // Add standard buttons, common to all modules.
        $this->add_action_buttons(true, get_string('delete', 'core'));
        
        $this->set_data(array('confirm' => true));
        
    }
}