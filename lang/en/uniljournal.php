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
 * English strings for uniljournal
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_uniljournal
 * @copyright  2014-2015  Université de Lausanne
 * @author     Liip AG eLearning Team <elearning@liip.ch>
 * @author     Didier Raboud <didier.raboud@liip.ch>
 * @author     Claude Bossy <claude.bossy@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'UNIL Learning Journal';
$string['modulenameplural'] = 'UNIL Learning Journals';
$string['modulename_help'] =
        'The UNIL journal module allows students to create learning journals structured in a structure of articles defined in advance.';
$string['ujname'] = 'Journal name';
$string['ujname_help'] = 'This is the name of the journal, as it will appear in the course page.';
$string['ujsubtitle'] = 'Subtitle';
$string['ujsubtitle_help'] = 'This is the secondary title of the journal, which will appeat in exported PDF documents.';
$string['ujdescription'] = 'Description';
$string['ujdescription_help'] = 'This is the description of the journal.';
$string['ujlogo'] = 'Coverpage logo';
$string['ujlogo_help'] =
        'The logo will be displayed in its nominal size on the first page of the PDF version.';
$string['ujcomments_allowed'] = 'Activate comments';
$string['ujcomments_allowed_help'] =
        'Activates the availability for teacher and student to associate general comments to each article of the journal.';
$string['allowedmimegroups'] = 'Allowed file types within Unil journal module';
$string['allowedmimegroupsdescription'] =
        'Restricts the list of allowed file MIME type groups in the UNIL journal module instances.';
$string['mimegroup_any'] = 'Any file';
$string['mimegroup_audio'] = 'Audio file';
$string['mimegroup_image'] = 'Image file';
$string['pluginadministration'] = 'UNIL Journal administration';
$string['pluginname'] = 'UNIL Learning Journal';
$string['managetemplates'] = 'Manage templates';
$string['articletemplates'] = 'Article templates';
$string['addtemplate'] = 'Add template';
$string['template'] = 'Template';
$string['templatelower'] = 'template';
$string['template_title'] = 'Template title';
$string['template_title_help'] = 'This is the title of the template, as it will be displayed to users';
$string['template_freetitle'] = 'Students can freely pick a title';
$string['template_freetitle_help'] =
        'Whether students can freely set a title, or is the title fixed by the chosen theme';
$string['template_instructions'] = 'Instructions';
$string['template_instructions_help'] = 'This setting allows to link a theme bank, allowing the student to chose a theme for the article being written';
$string['template_themebank'] = 'Pick a theme bank for that template';
$string['template_element'] = 'Template element';
$string['template_nothemebank'] = 'Don\'t select a theme bank';
$string['template_element_required'] = 'You must select at least one element';
$string['element_subtitle'] = 'Subtitle';
$string['article_theme'] = 'Pick a theme';
$string['article_theme_mandatory'] = 'You must pick a theme!';
$string['article_instructions'] = 'Writing instructions';
$string['article_theme_unpicked'] = '-';
$string['article_title'] = 'Article title';
$string['element_text'] = 'Text';
$string['element_image'] = 'Image';
$string['element_subtitle_desc'] = '{$a} subtitle(s)';
$string['element_text_desc'] = '{$a} text(s)';
$string['element_textonly'] = 'Text';
$string['element_textonly_desc'] = '{$a} text(s)';
$string['element_attachment_any'] = 'Any file';
$string['element_attachment_any_desc'] = '{$a} file(s)';
$string['element_attachment_archive'] = 'Archive';
$string['element_attachment_archive_desc'] = '{$a} archive(s)';
$string['element_attachment_audio'] = 'Audio file';
$string['element_attachment_audio_desc'] = '{$a} audio file(s)';
$string['element_attachment_document'] = 'Document';
$string['element_attachment_document_desc'] = '{$a} writer document(s)';
$string['element_attachment_image'] = 'Image';
$string['element_attachment_image_desc'] = '{$a} image(s)';
$string['element_attachment_presentation'] = 'Presentation';
$string['element_attachment_presentation_desc'] = '{$a} presentation(s)';
$string['element_attachment_spreadsheet'] = 'Spreadsheet';
$string['element_attachment_spreadsheet_desc'] = '{$a} spreadsheet(s)';
$string['element_attachment_video'] = 'Video';
$string['element_attachment_video_desc'] = '{$a} video(s)';
$string['attachments'] = 'Attachments';
$string['myarticles'] = 'My articles';
$string['addarticle'] = 'Add a new article …';
$string['addarticletempl'] = 'Add a new article with template \'{$a}\'';
$string['writearticletempl'] = 'Write an article with template \'{$a}\'';
$string['notemplates'] = 'No templates are available!';
$string['articlelower'] = 'article';
$string['articles_num'] = 'Amount of articles';
$string['articles_uncorrected'] = 'Uncorrected articles';
// Capabilities
$string['uniljournal:addinstance'] = 'Add instance';
$string['uniljournal:addcomment'] = 'Add a comment';
$string['uniljournal:deletecomment'] = 'Delete a comment';
$string['uniljournal:managetemplates'] = 'Manage templates';
$string['uniljournal:managethemes'] = 'Manage themes';
$string['uniljournal:view'] = 'View articles';
$string['uniljournal:createarticle'] = 'Create articles';
$string['uniljournal:deletearticle'] = 'Delete articles';
$string['uniljournal:editallarticles'] = 'Edit any article';
$string['uniljournal:viewallarticles'] = 'View all articles in a module instance';
$string['articlelower'] = 'article';
$string['managethemebanks_hint'] =
        'Information: you can use theme banks and link them from your article templates. To do so, choose "Manage theme banks" from the Administration menu.';
$string['managethemebanks'] = 'Manage theme banks';
$string['managethemebanks_help'] = 'Once a theme bank is created, click on its title to add themes into it.';
$string['addthemebank'] = 'Add theme bank';
$string['themebank'] = 'Theme bank';
$string['themebanklower'] = 'theme bank';
$string['themebank_title'] = 'Theme bank title';
$string['themebank_title_help'] = 'This is the title of the theme bank, as it will appear in the selector.';
$string['themebank_contextid'] = 'Availability';
$string['module_context'] = 'This module only';
$string['course_context'] = 'This course only';
$string['category_context'] = 'This category only';
$string['user_context'] = 'This user only';
$string['system_context'] = 'Everywhere';
$string['themebank_contextid_help'] = 'This is the context where this theme bank will be available.';
$string['managethemes'] = 'Theme bank "{$a->themebankname}"';
$string['addtheme'] = 'Add theme ';
$string['theme'] = 'Theme';
$string['themelower'] = 'theme';
$string['theme_title'] = 'Theme title';
$string['theme_title_help'] = 'This is the title of the theme, as it will appear to the end users.';
$string['theme_instructions'] = 'Instructions';
$string['theme_instructions_help'] =
        'These are the theme instructions, which will be displayed to users having selected this theme';
$string['n_articleinstances'] = 'Used in {$a} article(s)';
$string['articlestate'] = 'State';
$string['revisions'] = 'Revisions';
$string['corrected_status'] = 'Corrected';
$string['no_comment'] = 'No comment';
$string['sendcomment'] = 'Send comment';
$string['comment_deletion_confirmation'] = 'Are you sure you want to delete this comment?';
$string['version_previous'] = 'Previous version';
$string['version_next'] = 'Next version';
$string['version'] = 'version';
$string['student_uncorrected_articles'] = '{$a->students} student(s), {$a->uncorrected} uncorrected article(s)';
$string['uncorrected_articles'] = '{$a->uncorrected} uncorrected article(s)';
$string['exportarticles'] = 'Export articles';
$string['compare'] = 'Compare';
$string['author'] = 'Author';
$string['all_students'] = 'All students';
$string['logo'] = "Activity logo";
$string['elementinstance'] = "Elements uploaded";
$string['themeinstructions'] = "Theme instructions uploaded";
$string['error_notitle'] = "ERROR: no title set";
$string['preview'] = "Preview";
$string['atto_writinginstructions'] =
        '<strong>Notice:</strong> you can use the correction tools in the text editor below; to reveal the correction tools, click on the first button of the palette.';
$string['themescount'] = 'Themes count';
$string['displayarticles'] = 'Display articles';
$string['exportpdf'] = 'Export to PDF';

//Logs
$string['template_created_name'] = 'Create a template';
$string['template_created_explanation'] = 'Event when a template has been successfully created.';
$string['template_created_desc'] = 'User {$a->userid} has created the template {$a->templateid}';
$string['template_updated_name'] = 'Update a template';
$string['template_updated_explanation'] = 'Event when a template has been successfully updated.';
$string['template_updated_desc'] = 'User {$a->userid} has updated the template {$a->templateid}';
$string['template_deleted_name'] = 'Delete a template';
$string['template_deleted_explanation'] = 'Event when a template has been successfully deleted.';
$string['template_deleted_desc'] = 'User {$a->userid} has deleted the template {$a->templateid}';
$string['template_read_name'] = 'Read a template';
$string['template_read_explanation'] = 'Event when a template has been successfully read.';
$string['template_read_desc'] = 'User {$a->userid} has read the template {$a->templateid}';

$string['article_created_name'] = 'Create an article';
$string['article_created_explanation'] = 'Event when an article has been successfully created.';
$string['article_created_desc'] = 'User {$a->userid} has created the article {$a->articleid}';
$string['article_updated_name'] = 'Update an article';
$string['article_updated_explanation'] = 'Event when an article has been successfully updated.';
$string['article_updated_desc'] = 'User {$a->userid} has updated the article {$a->articleid}';
$string['article_deleted_name'] = 'Delete an article';
$string['article_deleted_explanation'] = 'Event when an article has been successfully deleted.';
$string['article_deleted_desc'] = 'User {$a->userid} has deleted the article {$a->articleid}';
$string['article_read_name'] = 'Read an article';
$string['article_read_explanation'] = 'Event when an article has been successfully read.';
$string['article_read_desc'] = 'User {$a->userid} has read the article {$a->articleid}';

$string['comment_created_name'] = 'Create a comment';
$string['comment_created_explanation'] = 'Event when a comment has been successfully created.';
$string['comment_created_desc'] =
        'User {$a->userid} has created the comment {$a->commentid} in article {$a->articleid}';
$string['comment_deleted_name'] = 'Delete a comment';
$string['comment_deleted_explanation'] = 'Event when a comment has been successfully deleted.';
$string['comment_deleted_desc'] =
        'User {$a->userid} has deleted the comment {$a->commentid} from article {$a->articleid}';

$string['themebank_created_name'] = 'Create a theme bank';
$string['themebank_created_explanation'] = 'Event when a theme bank has been successfully created.';
$string['themebank_created_desc'] = 'User {$a->userid} has created the theme bank {$a->themebankid}';
$string['themebank_updated_name'] = 'Update a theme bank';
$string['themebank_updated_explanation'] = 'Event when a theme bank has been successfully updated.';
$string['themebank_updated_desc'] = 'User {$a->userid} has updated the theme bank {$a->themebankid}';
$string['themebank_deleted_name'] = 'Delete a theme bank';
$string['themebank_deleted_explanation'] = 'Event when a theme bank has been successfully deleted.';
$string['themebank_deleted_desc'] = 'User {$a->userid} has deleted the theme bank {$a->themebankid}';

$string['theme_created_name'] = 'Create a theme';
$string['theme_created_explanation'] = 'Event when a theme has been successfully created.';
$string['theme_created_desc'] = 'User {$a->userid} has created the theme {$a->themeid} in theme bank {$a->themebankid}';
$string['theme_updated_name'] = 'Update a theme';
$string['theme_updated_explanation'] = 'Event when a theme has been successfully updated.';
$string['theme_updated_desc'] = 'User {$a->userid} has updated the theme {$a->themeid} in theme bank {$a->themebankid}';
$string['theme_deleted_name'] = 'Delete a theme';
$string['theme_deleted_explanation'] = 'Event when a theme has been successfully deleted.';
$string['theme_deleted_desc'] =
        'User {$a->userid} has deleted the theme {$a->themeid} from theme bank {$a->themebankid}';

$string['restoredon'] = ' (restored on {$a->mon}/{$a->mday}/{$a->year})';

$string['to_correct'] = 'Finished';
$string['corrected'] = 'To be improved';
$string['accepted'] = 'Accepted';

//Mails
$string['article_corrected_subject'] = 'Article corrected';
$string['article_corrected_message'] = 'Dear {$a->user_name},


Your article ({$a->article}) has been corrected.

You can check those corrections here: {$a->link}
';
$string['article_corrected_html_message'] = '<p>Dear {$a->user_name},</p>
<p>Your article ({$a->article}) has been corrected.</p>
<p>You can check those corrections here: <a href="{$a->link}">{$a->link}</a></p>
';
$string['article_accepted_subject'] = 'Article accepted';
$string['article_accepted_message'] = 'Dear {$a->user_name},


Your article ({$a->article}) has been accepted.

You can view the article here: {$a->link}
';
$string['article_accepted_html_message'] = '<p>Dear {$a->user_name},</p>
<p>Your article ({$a->article}) has been accepted.</p>
<p>You can view the article here: <a href="{$a->link}">{$a->link}</a></p>
';
$string['messageprovider:correction'] = 'Correction notification';
$string['messageprovider:accepted'] = 'Article accepted notification';

$string['article_tocorrect_subject'] = 'Article ready to be corrected';
$string['article_tocorrect_message'] = 'Dear {$a->user_name},


The article ({$a->article}) from {$a->author_name} is ready to be corrected.

You can start to correct it here: {$a->link}
';
$string['article_tocorrect_html_message'] = '<p>Dear {$a->user_name},</p>
<p>The article ({$a->article}) from {$a->author_name} is ready to be corrected.</p>
<p>You can start to correct it here: <a href="{$a->link}">{$a->link}</a></p>
';
$string['messageprovider:tocorrect'] = 'Ready to be corrected notification';

$string['toc'] = 'Table Of Contents';
$string['offlineattachments'] = 'Offline attachments';
$string['id_missing'] = 'You must specify a course_module ID or an instance ID';
$string['cannotmanagethemebank'] = 'You don\'t have the permissions to edit that theme bank';
$string['mustexist'] = 'Must exist!';
$string['userdoesnotexist'] = 'This user does not exist';
$string['mustbeteacher'] = 'You must be a teacher to do this action';
$string['editarticle'] = 'Edit the current version of this article';
$string['editarticle_teacher'] = 'Correct the current version of this article';
$string['canteditarticle'] = 'You cannot edit this article';

