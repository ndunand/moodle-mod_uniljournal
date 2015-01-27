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

        if($articlemodel->themebankid and array_key_exists('themes', $this->_customdata)) {
          $themes = $this->_customdata['themes'];
          $instructions_visibility = ' style="display: none;"';
          if(property_exists($currententry, 'themeid') && array_key_exists($currententry->themeid, $themes) ) {
            $selectedthemeid = $currententry->themeid;
            // Display a static theme title and instructions
            $mform->addElement('html', "\n\t".'<div class="fitem"><div class="fitemtitle"><div class="fstaticlabel"><label>'.get_string('article_theme', 'uniljournal').'</label></div></div>');
            $mform->addElement('html', "\n\t\t".'<div class="felement fstatic">'.$themes[$currententry->themeid]->title.'</div>');
            $mform->addElement('html', "\n\t".'</div>');
            $mform->addElement('html', "\n\t".'<div class="fitem"><div class="fitemtitle"><div class="fstaticlabel"><label>'.get_string('article_instructions', 'uniljournal').'</label></div></div>');
            $mform->addElement('html', "\n\t\t".'<div class="felement fstatic">'.$themes[$currententry->themeid]->instructions.'</div>');
            $mform->addElement('html', "\n\t".'</div>');
          } else {
            $themeselect = array();
            if($articlemodel->freetitle == 1) { $themeselect[-1] = get_string('article_theme_unpicked', 'uniljournal'); }
            foreach($themes as $tid => $themedata) {
              $themeselect[$tid] = $themedata->title;
            }

            $mform->addElement('select', 'themeid', get_string('article_theme', 'uniljournal'), $themeselect);
            $mform->setType('themeid', PARAM_INT);

            $mform->addElement('html', "\n\t".'<div class="fitem" id="instructions_block" style="display: none;">');
            $mform->addElement('html', "\n\t\t".'<div class="fitemtitle">');
            $mform->addElement('html', "\n\t\t\t".'<div class="fstaticlabel"><label>'.get_string('article_instructions', 'uniljournal').'</label></div>');
            $mform->addElement('html', "\n\t\t".'</div>');
            $mform->addElement('html', "\n\t\t".'<div class="felement fstatic">');
            foreach($themes as $tid => $themedata) {
              $instructions_visibility = null; //' style="display: none;"';
              if(isset($selectedthemeid) && $selectedthemeid == $tid) $instructions_visibility = null;
              $mform->addElement('html', "\n\t\t\t".'<div id="instructions_'.$tid.'"'.$instructions_visibility.'>'.$themedata->instructions.'</div>');
            }
            $mform->addElement('html', "\n\t".'</div></div>');
            $mform->addElement('html', '<script>
              $("#id_themeid").on("change", function() {
                newid = $(this).val();
                if( newid == -1 ) {
                  $("#instructions_block").hide();
                } else {
                  $("#instructions_block").show()
                  id = "instructions_" + newid;
                  $("#" + id).show().siblings().hide();
                }
              })
              </script>');
            }
        }
        if($articlemodel->freetitle == 1) {
          $mform->addElement('text', 'title', get_string('article_title', 'uniljournal'), array('size' => '64'));
          $mform->setType('title', PARAM_TEXT);
          $mform->addRule('title', null, 'required', null, 'client');
          $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        }

        foreach($articleelements as $ae) {
          $id = 'element_'.$ae->id;
          $desc = get_string('element_'.$ae->element_type, 'uniljournal');
          
          if(uniljournal_startswith($ae->element_type, 'attachment_')) {
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
        $a->type = get_string('articlelower', 'mod_uniljournal');
        require_once('locallib.php');
        $a->name = uniljournal_articletitle($currententry);
        
        $mform->addElement('html', '<div>'.get_string('deletechecktypename', 'core', $a).'</div>');

        $mform->addElement('hidden', 'confirm');
        $mform->setType('confirm', PARAM_BOOL);
        
        // Add standard buttons, common to all modules.
        $this->add_action_buttons(true, get_string('delete', 'core'));
        
        $this->set_data(array('confirm' => true));
        
    }
}