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

class choose_template_form extends moodleform {
    protected $course;

    public function definition() {

        global $CFG;
        $options = $this->_customdata['options'];

        $mform = $this->_form;
        $mform->addElement('select', 'amid', '', $options, ['class' => 'autosubmit']);
        $mform->disable_form_change_checker();
    }
}

class status_change_form extends moodleform {
    protected $course;

    public function definition() {

        global $USER, $DB, $course;
        $options = $this->_customdata['options'];
        $currententry = $this->_customdata['currententry'];
        $status = optional_param($currententry->statuskey, 0, PARAM_INT);
        $cmid = optional_param('id', 0, PARAM_INT);

        $mform = $this->_form;
        $mform->addElement('select', $currententry->statuskey, '', $options, ['class' => 'autosubmit']);

        $mform->disable_form_change_checker();

        $context = context_course::instance($course->id);
        $articleinstance = $DB->get_record('uniljournal_articleinstances', ['id' => $currententry->aid], '*', MUST_EXIST);

        $teachernotificationssent = 0;
        $authornotificationssent = 0;

        if ($status == UNILJOURNAL_STATUS_TOCORRECT) {
            // sent notification to teacher if status is UNILJOURNAL_STATUS_TOCORRECT
            $teachers = get_users_by_capability($context, 'mod/uniljournal:getstudentnotifications');
            foreach ($teachers as $teacher) {
                if (!has_capability('mod/uniljournal:getstudentnotifications', $context, $teacher, false)) {
                    continue;
                    // see https://tracker.moodle.org/browse/MDL-57027
                }
                $articlelink = new moodle_url('/mod/uniljournal/view_article.php',
                        ['cmid' => $cmid, 'id' => $articleinstance->id]);
                sendtocorrectmessage($USER, $teacher, $articleinstance, $articlelink);
                $teachernotificationssent++;
            }
        }
        else if ($status == UNILJOURNAL_STATUS_CORRECTED) {
            // sent notification to student if status is UNILJOURNAL_STATUS_CORRECTED
            if (!has_capability('mod/uniljournal:viewallarticles', $context)) {
                print_error('mustbeteacher', 'mod_uniljournal');
            }
            $articlelink =
                    new moodle_url('/mod/uniljournal/view_article.php', ['cmid' => $cmid, 'id' => $articleinstance->id]);
            sendcorrectionmessage($USER, $articleinstance, $articlelink);
            $authornotificationssent++;
        }
        else if ($status == UNILJOURNAL_STATUS_ACCEPTED) {
            // sent notification to student if status is UNILJOURNAL_STATUS_ACCEPTED
            if (!has_capability('mod/uniljournal:viewallarticles', $context)) {
                print_error('mustbeteacher', 'mod_uniljournal');
            }
            $articlelink =
                    new moodle_url('/mod/uniljournal/view_article.php', ['cmid' => $cmid, 'id' => $articleinstance->id]);
            sendacceptedmessage($USER, $articleinstance, $articlelink);
            $authornotificationssent++;
        }
        else if ($status == UNILJOURNAL_STATUS_REJECTED) {
            // send notification to student if status is UNILJOURNAL_STATUS_REJECTED
            if (!has_capability('mod/uniljournal:viewallarticles', $context)) {
                print_error('mustbeteacher', 'mod_uniljournal');
            }
            $articlelink =
                    new moodle_url('/mod/uniljournal/view_article.php', ['cmid' => $cmid, 'id' => $articleinstance->id]);
            sendrejectedmessage($USER, $articleinstance, $articlelink);
            $authornotificationssent++;
        }

        if ($status >= UNILJOURNAL_STATUS_TOCORRECT) {
            $a = (object)[
                    'articletitle' => $articleinstance->title,
                    'status'       => get_string('status' . $status, 'mod_uniljournal')
            ];
            if ($teachernotificationssent) {
                \core\notification::add(get_string('newstatusemailsenttoteacher', 'mod_uniljournal', $a), \core\notification::SUCCESS);
            }
            if ($authornotificationssent) {
                \core\notification::add(get_string('newstatusemailsenttoauthor', 'mod_uniljournal', $a), \core\notification::SUCCESS);
            }
        }

        $this->set_data($currententry);
    }
}