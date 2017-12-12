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
 * @package    mod_uniljournal
 * @copyright  2014-2017  Université de Lausanne
 * @author     Nicolas Dunand <nicolas.dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('AJAX_SCRIPT', true);

include '../../config.php';
include_once './locallib.php';

$articleinstanceid = required_param('articleinstanceid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$ackrequest = optional_param('ackrequest', 0, PARAM_INT);
$retry = optional_param('retry', 0, PARAM_INT);

$cm = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

$result             = new stdClass();
$result->error      = true;
$result->message    = get_string('noresult', 'mod_uniljournal');
$result->messagetype = 'alert';

$PAGE->set_context(null);

// check that the user is logged in
if (!isloggedin()) {
    $result->message = get_string('sessiontimedout', 'mod_uniljournal');
    send_result();
}

require_sesskey();
$lock = uniljournal_get_article_lock($articleinstanceid);

if (!$lock) {
    $result->unlocked = true;
}

// check that I am the sole holder of the lock
if (uniljournal_is_article_locked($articleinstanceid, $USER->id)) {
    // check if a request of mine has reached its limit
    if (uniljournal_can_i_force_editing($articleinstanceid)) {
        uniljournal_unset_article_lock($articleinstanceid, $lock['userid']);
        $result->unlocked = true;
        send_result();
    }
    if ($retry && !uniljournal_was_article_requested($articleinstanceid)) {
        // I'm retrying after having made a request, but my request has been removed -> means denied
        $result->denied = 1;
        send_result();
    }
    // article is locked to me - I shouldn't be editing
    $otheruser = $DB->get_record('user', ['id' => $lock['userid']]);
    $a = new stdClass();
    $a->who = fullname($otheruser, has_capability('moodle/site:viewfullnames', context_course::instance($course->id)));
    $result->error = true;
    $result->message = get_string('error_lockstolen', 'mod_uniljournal', $a);
    send_result();
}

// check if anybody requested this article from me
if ($lock['userid'] == $USER->id) {
    // lock is mine, good...
    if ($requesterid = uniljournal_was_article_requested($articleinstanceid)) {
        if ($ackrequest) {
            $lock['ack'] = 1;
            $articleinstance = $DB->get_record('uniljournal_articleinstances', ['id' => $articleinstanceid]);
            $articleinstance->editlock = serialize($lock);
            $DB->update_record('uniljournal_articleinstances', $articleinstance);
            $result->error = false;
            send_result();
        }
        if (isset($lock['ack']) && $lock['ack'] == 1) {
            $result->error = false;
            send_result();
        }
        $otheruser = $DB->get_record('user', ['id' => $requesterid]);
        $a = new stdClass();
        $a->who = fullname($otheruser, has_capability('moodle/site:viewfullnames', context_course::instance($course->id)));
        $result->error = true;
        $result->message = get_string('error_lockrequested', 'mod_uniljournal', $a);
        $result->lockrelease = get_string('lockrelease', 'mod_uniljournal');
        $result->lockkeep = get_string('lockkeep', 'mod_uniljournal');
        $b = new stdClass();
        $locktimeremaining = (int)(max(0, $lock['requestedtimestamp'] + UNILJOURNAL_LOCKREQUEST_TTL - time()) / 10) * 10;
        $b->locktimeremaining = '' . (int)($locktimeremaining / 60) . ':' . ($locktimeremaining % 60);
        $result->lockwait = get_string('lockwait', 'mod_uniljournal', $b);
        $result->messagetype = 'confirm';
        send_result();
    }

}

$result->error = false;
send_result();

function send_result() {
    global $result;
    header('Content-type: application/json');
    echo json_encode($result);
    exit;
}


