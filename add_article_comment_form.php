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
 * The main uniljournal send comment form
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

class add_article_comment_form extends moodleform {
    public function definition() {
        global $CFG;

        $cmid = $this->_customdata['cmid'];
        $articleinstanceid = $this->_customdata['articleinstanceid'];
        $articleinstanceversion = $this->_customdata['articleinstanceversion'];
        $user = $this->_customdata['user'];

        $mform = $this->_form;

        $mform->addElement('textarea', 'text', '');
        $mform->setType('text', PARAM_TEXT);

        $mform->addElement('hidden', 'articleinstanceid');
        $mform->setType('articleinstanceid', PARAM_INT);
        $mform->setConstant('articleinstanceid', $articleinstanceid);
        $mform->addElement('hidden', 'articleinstanceversion');
        $mform->setType('articleinstanceversion', PARAM_INT);
        $mform->setConstant('articleinstanceversion', $articleinstanceversion);
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setConstant('userid', $user->id);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->setConstant('cmid', $cmid);

        $mform->setAttributes(['action' => new moodle_url('/mod/uniljournal/add_article_comment.php'),
                               'method' => 'post', 'class' => 'mform']);

        $this->add_action_buttons(false, get_string('sendcomment', 'mod_uniljournal'));
    }
}