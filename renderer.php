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
 * Moodle renderer used to display special elements of the lesson module
 *
 * @package mod_lesson
 * @copyright  2009 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

defined('MOODLE_INTERNAL') || die();
require('../../user/lib.php');
require('./add_article_comment_form.php');

class mod_uniljournal_renderer extends plugin_renderer_base {
    function display_comments($cmid, $articleinstanceid, $articleinstanceversion, $userid, $editable=false) {
        global $DB, $USER;

        $comments = $DB->get_records_sql('
          SELECT c.*, u.firstname AS firstname, u.lastname AS lastname FROM {uniljournal_article_comments} c
          JOIN {user} u ON c.userid = u.id
          WHERE articleinstanceid = :articleinstanceid
          ORDER BY timecreated ASC', array(
            'articleinstanceid' => $articleinstanceid
        ));
        $output = '';
        if (count($comments) > 0) {
            foreach($comments as $comment) {
                $userClass = '';
                $versionClass = '';
                $disabled = '';
                if ($userid == $comment->userid) {
                    $userClass = ' me';
                } else {
                    $userClass = ' other';
                }
                if ($articleinstanceversion == $comment->articleinstanceversion) {
                    $versionClass = ' current';
                }
                $output .= '<div class="article-comments-item'. $userClass . $versionClass . '">';
                $output .= '<h5 for="comment' . $comment->id . '">' . $comment->firstname . ' ' . $comment->lastname . '</h5>';
                $output .= '<p id ="comment' . $comment->id . '">' . $comment->text . '</p></div>';
            }
        }
        if ($editable) {
            $customdata = array();
            $customdata['cmid'] = $cmid;
            $customdata['articleinstanceid'] = $articleinstanceid;
            $customdata['articleinstanceversion'] = $articleinstanceversion;
            $customdata['currententry'] = new stdClass();
            $customdata['user'] = $USER;
            $mform = new add_article_comment_form(null, $customdata);
            $output .= '<div class="article-comments-item">';
            $output .= $mform->render();
            $output .= '</div>';
        } else if (count($comments) < 1) {
            $output .= get_string('no_comment', 'mod_uniljournal');
        }

        return $output;
    }
}
