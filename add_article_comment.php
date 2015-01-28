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
 * Edits a particular instance of a uniljournal instance
 *
 *
 * @package    mod_uniljournal
 * @copyright  2015 Liip AG {@link http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/add_article_comment_form.php');

require_login();

$cmid  = optional_param('cmid', 0, PARAM_INT); // Course_module ID, or
$articleinstanceid  = optional_param('articleinstanceid', 0, PARAM_INT); // article instance ID, or
$articleinstanceversion = optional_param('articleinstanceversion', 0, PARAM_INT);  // article instance version
$cid = optional_param('cid', 0, PARAM_INT);  // comment ID
$action = optional_param('action', '', PARAM_TEXT);  // action

$context = context_module::instance($cmid);
$PAGE->set_context($context);

$cm         = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$uniljournal  = $DB->get_record('uniljournal', array('id' => $cm->instance), '*', MUST_EXIST);

if ($cid && $action == 'delete') {
    require_capability('mod/uniljournal:deletecomment', $context);
    $DB->delete_records('uniljournal_article_comments', array('id' => $cid));

    // Log the comment deletion
    $event = \mod_uniljournal\event\comment_deleted::create(array(
        'other' => array(
            'userid' => $USER->id,
            'commentid' => $cid,
            'articleid' => $articleinstanceid
        ),
        'courseid' => $course->id,
        'objectid' => $cid,
        'context' => $context,
    ));
    $event->trigger();

    redirect(new moodle_url('/mod/uniljournal/view_article.php', array('cmid' => $cmid, 'id' => $articleinstanceid)));
} else {
    require_capability('mod/uniljournal:addcomment', $context);
    $customdata = array();
    $customdata['cmid'] = $cmid;
    $customdata['articleinstanceid'] = $articleinstanceid;
    $customdata['articleinstanceversion'] = $articleinstanceversion;
    $customdata['currententry'] = new stdClass();
    $customdata['user'] = $USER;
    $mform = new add_article_comment_form(null, $customdata);

    $form_data = $mform->get_data();

    $comment = new stdClass();
    $comment->articleinstanceid = $articleinstanceid;
    $comment->articleinstanceversion = $articleinstanceversion;
    $comment->userid = $USER->id;
    $comment->text = $form_data->text;
    $comment->timecreated = time();

    $cid = $DB->insert_record('uniljournal_article_comments', $comment);

    // Log the comment creation
    $event = \mod_uniljournal\event\comment_created::create(array(
        'other' => array(
            'userid' => $USER->id,
            'commentid' => $cid,
            'articleid' => $articleinstanceid
        ),
        'courseid' => $course->id,
        'objectid' => $cid,
        'context' => $context,
    ));
    $event->trigger();

    redirect(new moodle_url('/mod/uniljournal/view_article.php', array('cmid' => $cmid, 'id' => $articleinstanceid)));
}