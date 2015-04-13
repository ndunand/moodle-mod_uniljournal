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
$string['modulename_help'] = 'The UNIL journal module allows students to create learning journals structured in a structure of articles defined in advance.';
$string['ujname'] = 'Journal name';
$string['ujname_help'] = '(TO BE COMPLETED !) This is the name of the journal.';
$string['ujsubtitle'] = 'Subtitle';
$string['ujsubtitle_help'] = '(TO BE COMPLETED !) This is the subtitle of the journal.';
$string['ujdescription'] = 'Description';
$string['ujdescription_help'] = '(TO BE COMPLETED !) This is the description of the journal.';
$string['ujlogo'] = 'Coverpage logo';
$string['ujlogo_help'] = '(TO BE COMPLETED !) The logo will be displayed in its nominal size on the first page of the printable version.';
$string['ujcomments_allowed'] = 'Activate comments';
$string['ujcomments_allowed_help'] = '(TO BE COMPLETED !) Activate (or not) the possibility to associate general comments to each article of the journal.';
$string['allowedmimegroups'] = 'Allowed file types within Unil journal module';
$string['allowedmimegroupsdescription'] = 'Restricts the list of allowed file MIME type groups in the UNIL journal module instances.';
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
$string['template_title_help'] = '(TO BE COMPLETED !) This is the title of the template';
$string['template_freetitle'] = 'Students can freely pick a title';
$string['template_freetitle_help'] = '(TO BE COMPLETED !) Whether students can freely pick a title outside of theme banks';
$string['template_instructions'] = 'Instructions';
$string['template_instructions_help'] = '(TO BE COMPLETED !) These are the template instructions';
$string['template_themebank'] = 'Pick a theme bank for that template';
$string['template_instructions_help'] = '(TO BE COMPLETED !) …';
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
$string['element_textonly'] = 'Text without attachments';
$string['element_textonly_desc'] = '{$a} text(s) without attachments';
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
$string['uniljournal:managetemplates'] = 'Manage templates';
$string['uniljournal:view'] = 'View articles';
$string['uniljournal:createarticle'] = 'Create articles';
$string['uniljournal:deletearticle'] = 'Delete articles';
$string['uniljournal:viewallarticles'] = 'View all articles in a module instance';
$string['articlelower'] = 'article';
$string['managethemebanks'] = 'Manage theme banks';
$string['addthemebank'] = 'Add theme bank';
$string['themebank'] = 'Theme bank';
$string['themebanklower'] = 'theme bank';
$string['themebank_title'] = 'Theme bank title';
$string['themebank_title_help'] = '(TO BE COMPLETED !) This is the title of the theme bank';
$string['themebank_contextid'] = 'Availability';
$string['module_context'] = 'This module only';
$string['course_context'] = 'This course only';
$string['category_context'] = 'This category only';
$string['user_context'] = 'This user only';
$string['system_context'] = 'Everywhere';
$string['themebank_contextid_help'] = '(TO BE COMPLETED !) This where this bank will be available.';
$string['managethemes'] = 'Manage themes';
$string['addtheme'] = 'Add theme ';
$string['theme'] = 'Theme';
$string['themelower'] = 'theme';
$string['theme_title'] = 'Theme title';
$string['theme_title_help'] = '(TO BE COMPLETED !) This is the title of the theme';
$string['theme_instructions'] = 'Instructions';
$string['theme_instructions_help'] = '(TO BE COMPLETED !) These are the theme instructions';
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
$string['all_students']  = 'All students';
$string['logo'] =  "Activity logo";
$string['elementinstance'] = "Elements uploaded";
$string['themeinstructions'] = "Theme instructions uploaded";
$string['error_notitle'] = "ERROR: no title set";
$string['preview'] = "Preview";

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
$string['comment_created_desc'] = 'User {$a->userid} has created the comment {$a->commentid} in article {$a->articleid}';
$string['comment_deleted_name'] = 'Delete a comment';
$string['comment_deleted_explanation'] = 'Event when a comment has been successfully deleted.';
$string['comment_deleted_desc'] = 'User {$a->userid} has deleted the comment {$a->commentid} from article {$a->articleid}';

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
$string['theme_deleted_desc'] = 'User {$a->userid} has deleted the theme {$a->themeid} from theme bank {$a->themebankid}';

$string['restoredon'] = ' (restored on {$a->mday}/{$a->mon}/{$a->year})';

$string['to_correct'] = 'To correct';
$string['corrected'] = 'Corrected';

//Mails
$string['article_corrected_subject'] = 'Article corrected';
$string['article_corrected_message'] = 'Dear {$a->user_name},


Your article ({$a->article}) has been corrected.

You can check those corrections here: {$a->link}
';
$string['article_corrected_html_message'] = '<p>Dear {$a->user_name},</p>
<p>Your article ({$a->article}) has been corrected.</p>
<p>You can check those corrections here: {$a->link}</p>
';
$string['messageprovider:correction'] = 'Correction notification';

$string['article_tocorrect_subject'] = 'Article ready to be corrected';
$string['article_tocorrect_message'] = 'Dear {$a->user_name},


The article ({$a->article}) from {$a->author_name} is ready to be corrected.

You can start to correct it here: {$a->link}
';
$string['article_tocorrect_html_message'] = '<p>Dear {$a->user_name},</p>
<p>The article ({$a->article}) from {$a->author_name} is ready to be corrected.</p>
<p>You can start to correct it here: {$a->link}</p>
';
$string['messageprovider:tocorrect'] = 'Ready to be corrected notification';

$string['toc'] = 'Table Of Contents';