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
    function display_comments($cmid, $articleinstanceid, $articleinstanceversion, $userid, $maxversion = -1) {
        global $DB, $USER, $OUTPUT;

        $context = context_module::instance($cmid);
        $canDelete = has_capability('mod/uniljournal:deletecomment', $context);

        $userattrssql = get_all_user_name_fields(true, 'u');
        $comments = $DB->get_records_sql('
          SELECT c.*, '.$userattrssql.' FROM {uniljournal_article_comments} c
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
                if (($articleinstanceversion == $comment->articleinstanceversion) && $canDelete) {
                    $deleteURL = new moodle_url('/mod/uniljournal/add_article_comment.php',
                        array(
                            'action' => 'delete',
                            'cmid' => $cmid,
                            'cid' => $comment->id,
                            'articleinstanceid' => $articleinstanceid
                        ));
                    $output .= '<a href="' . $deleteURL . '">' . html_writer::img($OUTPUT->pix_url('t/delete'), get_string('delete')) . '</a>';
                }
                $output .= '<h5 for="comment' . $comment->id . '">' . fullname($comment, has_capability('moodle/site:viewfullnames', $context)). '</h5>';
                $output .= '<p id ="comment' . $comment->id . '">' . $comment->text . '</p></div>';
            }
        }
        if ($articleinstanceversion == $maxversion) {
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

    function display_article($article, $articleelements, $context, $pdf, $version=0, &$actualversion=0) {
        global $DB, $OUTPUT;

        $output = '';

        $output .= html_writer::start_div('article-view');

        $output .= html_writer::start_div('article-view-template');
        $output .= html_writer::start_div('article-view-template-title');
        $output .= $article->amtitle . ' (' . date_format_string($article->timemodified, '%d %B %Y') . ')';
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('article-view-template-instructions');
        $output .= $article->instructions;
        $output .= html_writer::end_div();

        $output .= html_writer::end_div();

        $output .= html_writer::start_div('article-view-title');
        $output .= $article->title;
        $output .= html_writer::end_div();

        $contents = '';
        $attachments = '';

        $file_extensions = get_mimetypes_array();

        foreach($articleelements as $ae) {
            //hack to make the image readable by TCPDF
            $filearea = 'elementinstance';
            if ($pdf) {
                $filearea = 'elementinstance_pdf';
            }
            $property_name = 'element_' . $ae->id;
            $property_edit = $property_name . '_editor';
            $property_format = $property_name . 'format';

            $sqlargs = array('instanceid' => $article->id, 'elementid' => $ae->id);
            $sql = '
              SELECT * FROM {uniljournal_aeinstances}
              WHERE instanceid = :instanceid
              AND elementid  = :elementid ';
            if ($version != 0) {
                $sql .= 'AND version <= :version';
                $sqlargs['version'] = $version;
            }
            $sql .= 'ORDER BY version DESC LIMIT 1';
            $aeinstance = $DB->get_record_sql($sql, $sqlargs);
            if ($aeinstance !== false) {
                switch ($ae->element_type) {
                    case "subtitle":
                        $contents .= html_writer::start_div('article-view-content-subtitle');
                        $contents .= $aeinstance->value;
                        $contents .= html_writer::end_div();
                        break;
                    case "text":
                        $aeinstance->value = file_rewrite_pluginfile_urls($aeinstance->value, 'pluginfile.php', $context->id, 'mod_uniljournal', $filearea, $aeinstance->id);
                        $contents .= html_writer::start_div('article-view-content-text');
                        $contents .= format_text($aeinstance->value, FORMAT_HTML);
                        $contents .= html_writer::end_div();
                        break;
                    case "textonly":
                        $contents .= html_writer::start_div('article-view-content-text-only');
                        $contents .= $aeinstance->value;
                        $contents .= html_writer::end_div();
                        break;
                }

                if (uniljournal_startswith($ae->element_type, 'attachment_')) {
                    $fs = get_file_storage();
                    $files = $fs->get_area_files($context->id, 'mod_uniljournal', 'elementinstance', $aeinstance->id);
                    if (count($files) > 0) {
                        $file = array_pop($files);
                        //hack to make the image readable by TCPDF
                        if ($file->get_filearea() == 'elementinstance') {
                            $filearea = 'elementinstance_pdf';
                        } else {
                            $filearea = $file->get_filearea();
                        }
                        $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $filearea, $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                        if ($file->is_valid_image()) {
                            $attachments .= html_writer::start_div('article-view-attachment-image');
                            $attachments .= html_writer::img($url, $file->get_filename());
                            $attachments .= html_writer::end_div();
                        } else {
                            $attachments .= html_writer::start_div('article-view-attachment-doc');
                            $attachments .= html_writer::start_div('article-view-attachment-doc-icon');
                            $attachments .= $OUTPUT->pix_icon('f/' . mimeinfo('icon128', $file->get_filename()), $file->get_filename());
                            $attachments .= html_writer::end_div();
                            $attachments .= html_writer::start_div('article-view-attachment-doc-text');
                            $attachments .= html_writer::link($url, $file->get_filename());
                            $attachments .= html_writer::end_div();
                            $attachments .= html_writer::end_div();
                        }
                    }
                }

                $actualversion = max($actualversion, $aeinstance->version);
            }
        }

        $output .= html_writer::start_div('article-view-content');
        $output .= $contents;
        $output .= html_writer::end_div();

        $output .= html_writer::start_div('article-view-attachment');
        if ($attachments != '') {
            $output .= '<br pagebreak="true"/>';
            $output .= html_writer::start_div('article-view-attachment-title');
            $output .= get_string('attachments', 'mod_uniljournal');
            $output .= html_writer::end_div();
        }
        $output .= $attachments;
        $output .= html_writer::end_div();

        $output .= html_writer::end_div();

        return $output;
    }
}
