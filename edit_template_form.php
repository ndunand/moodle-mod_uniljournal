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

class template_edit_form extends moodleform {
    protected $course;

    public function definition() {

        global $CFG;
        $course = $this->_customdata['course'];
        $cm = $this->_customdata['cm'];
        $instructionsoptions = $this->_customdata['instructionsoptions'];
        $currententry = $this->_customdata['current'];
        $elements = $this->_customdata['elements'];
        $elementsoptions = $this->_customdata['elementsoptions'];
        $themebanks = $this->_customdata['themebanks'];
        $context = context_module::instance($cm->id);

        $this->course = $course;

        $mform = $this->_form;

        $mform->addElement('text', 'title', get_string('template_title', 'uniljournal'), ['size' => '64']);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('title', 'template_title', 'uniljournal');

        // Let's prepare the maxbytes popup.
        $choices = get_max_upload_sizes($CFG->maxbytes, $this->course->maxbytes, 0, 0);
        $mform->addElement('select', 'maxbytes', get_string('maximumupload'), $choices);
        $mform->addHelpButton('maxbytes', 'maximumupload');

        $mform->addElement('editor', 'instructions_editor', get_string('template_instructions', 'uniljournal'), null,
                $instructionsoptions);
        $mform->setType('instructions_editor', PARAM_RAW);
        $mform->addHelpButton('instructions_editor', 'template_instructions', 'uniljournal');

        $mform->addElement('select', 'themebankid', get_string('template_themebank', 'uniljournal'), $themebanks);
        $mform->setType('themebankid', PARAM_INT);
        $mform->addHelpButton('themebankid', 'template_instructions', 'uniljournal');

        $mform->addElement('checkbox', 'freetitle', get_string('template_freetitle', 'uniljournal'));
        $mform->setType('freetitle', PARAM_BOOL);
        $mform->addHelpButton('freetitle', 'template_freetitle', 'uniljournal');
        $mform->setDefault('freetitle', true);
        $mform->disabledIf('freetitle', 'themebankid', 'eq', -1);

        $mform->addElement('html', '<div class="fitem fitem_dragdrop">');
        $mform->addElement('html',
                '<div class="fitemtitle"><label>' . get_string('template_element', 'uniljournal') . '</label></div>');
        $mform->addElement('html', '<div class="felement">');
        $mform->addElement('html',
                '<span id="error_elementsAdded" class="error" style="display:none;" tabindex="0">' . get_string('template_element_required',
                        'uniljournal') . '.</span>');
        $mform->addElement('html', '<div><ul id="elementsAdded" class="elementsAdded">');
        foreach ($elements as $element) {
            $mform->addElement('html', html_writer::tag('li',
                    get_string('element_' . $element->element_type, 'uniljournal') . html_writer::tag('input', '',
                            ['style' => "display: none;", 'type' => "text",
                             "name"  => "articleelements[" . $element->id . "]", "value" => $element->element_type])));
        }
        $mform->addElement('html', '</ul>');
        $mform->addElement('html', '<ul id="elementsToAdd" class="elementsToAdd">');
        $count = 1;
        foreach ($elementsoptions as $key => $element) {
            $mform->addElement('html', html_writer::tag('li',
                    get_string('element_' . $key, 'uniljournal') . html_writer::tag('input', '',
                            ['style' => "display: none;", 'type' => "text", "value" => $key]),
                    ['id' => 'element' . $count]));
            $count += 1;
        }
        $mform->addElement('html', '</ul></div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

        //-------------------------------------------------------------------------------
        $this->set_data($currententry);
    }

    public function getArticleElements() {
        if (array_key_exists('articleelements', $this->_form->_submitValues)) {
            return $this->_form->_submitValues['articleelements'];
        }
        else {
            return [];
        }
    }
}

class template_delete_form extends moodleform {
    protected $course;

    public function definition() {

        global $CFG;
        $course = $this->_customdata['course'];
        $cm = $this->_customdata['cm'];
        $template = $this->_customdata['template'];
        $context = context_module::instance($cm->id);

        $this->course = $course;

        $mform = $this->_form;

        $a = new stdClass();
        $a->type = get_string('templatelower', 'mod_uniljournal');
        $a->name = $template->title;

        $mform->addElement('html', '<div>' . get_string('deletechecktypename', 'core', $a) . '</div>');

        $mform->addElement('hidden', 'confirm');
        $mform->setType('confirm', PARAM_BOOL);

        // Add standard buttons, common to all modules.
        $this->add_action_buttons(true, get_string('delete', 'core'));

        $this->set_data(['confirm' => true]);
    }
}