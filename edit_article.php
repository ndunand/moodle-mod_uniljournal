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
 * @copyright  2014-2015  Université de Lausanne
 * @author     Liip AG eLearning Team <elearning@liip.ch>
 * @author     Didier Raboud <didier.raboud@liip.ch>
 * @author     Claude Bossy <claude.bossy@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

$cmid = optional_param('cmid', 0, PARAM_INT);  // Course_module ID, or
$amid = optional_param('amid', 0, PARAM_INT);  // template ID
$id = optional_param('id', 0, PARAM_INT);    // Article instance ID

if ($cmid) {
    $cm = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $uniljournal = $DB->get_record('uniljournal', ['id' => $cm->instance], '*', MUST_EXIST);
}
else {
    print_error('id_missing', 'mod_uniljournal');
}

global $USER;

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
if (!has_capability('mod/uniljournal:createarticle', $context) && !has_capability('mod/uniljournal:editallarticles',
                $context)
) {
    print_error('canteditarticle', 'mod_uniljournal');
}

// Get the model we're editing
if (!$articlemodel = $DB->get_record_select('uniljournal_articlemodels', "id = $amid AND hidden != '\x31'")) {
    print_error('invalidentry');
}
// Get all elements of the model
$articleelements =
        $DB->get_records_select('uniljournal_articleelements', "articlemodelid = $amid ORDER BY sortorder ASC");

$textfieldoptions =
        ['subdirs' => false, 'maxfiles' => '12', 'maxbytes' => $articlemodel->maxbytes, 'context' => $context];

$textonlyoptions = ['subdirs' => false, 'maxfiles' => 0, 'context' => $context];

$attachmentoptions =
        ['subdirs' => false, 'maxfiles' => '1', 'maxbytes' => $articlemodel->maxbytes, 'context' => $context];

if ($id) { // if entry is specified
    if (!$articleinstance = $DB->get_record('uniljournal_articleinstances', ['id' => $id, 'articlemodelid' => $amid])) {
        print_error('invalidentry');
    }
    $authorid = $articleinstance->userid;
    $groupid = $articleinstance->groupid;
    $articleinstance_db = clone $articleinstance;
    if (!uniljournal_is_my_articleinstance($articleinstance, $USER->id) && !has_capability('mod/uniljournal:editallarticles', $context)) {
        print_error('canteditarticle', 'mod_uniljournal');
    }

    if (uniljournal_is_my_articleinstance($articleinstance, $USER->id) && in_array($articleinstance->status, uniljournal_noneditable_statuses()) && !has_capability('mod/uniljournal:editallarticles', $context)) {
        $a = (object)['status' => get_string('status' . $articleinstance->status, 'mod_uniljournal')];
        print_error('readonlybecausestatus', 'mod_uniljournal', '', $a);
    }
    else if (has_capability('mod/uniljournal:editallarticles', $context) && in_array($articleinstance->status, uniljournal_noneditable_statuses())) {
        $a = (object)['status' => get_string('status' . $articleinstance->status, 'mod_uniljournal')];
        \core\notification::add(get_string('readonlyforstudentbecausestatus', 'mod_uniljournal', $a), \core\notification::WARNING);
    }


    // make sure there's no lock on this article
    uniljournal_check_article_lock($id, $USER->id);
    uniljournal_set_article_lock($id, $USER->id);

    // Get the existing article elements for edition
    $version = 0;
    foreach ($articleelements as $ae) {
        $property_name = 'element_' . $ae->id;
        $property_edit = $property_name . '_editor';
        $property_format = $property_name . 'format';
        $aeinstance = $DB->get_record_sql('
      SELECT * FROM {uniljournal_aeinstances}
        WHERE instanceid = :instanceid
          AND elementid  = :elementid
     ORDER BY version DESC LIMIT 1', ['instanceid' => $articleinstance->id, 'elementid' => $ae->id]);
        if ($aeinstance !== false) {
            $articleinstance->$property_name = $aeinstance->value;
            $articleinstance->$property_format = $aeinstance->valueformat;
            $version = max($version, $aeinstance->version);

            if ($ae->element_type == 'text' || $ae->element_type == 'textonly') {
                $articleinstance =
                        file_prepare_standard_editor($articleinstance, $property_name, $textfieldoptions, $context,
                                'mod_uniljournal', 'elementinstance', $aeinstance->id);
            }
            elseif (uniljournal_startswith($ae->element_type, 'attachment_')) { // begins with
                $articleinstance->$property_name = $aeinstance->id;
                $attoptions = $attachmentoptions;
                $attoptions['accepted_types'] = substr($ae->element_type, 11);
                $articleinstance =
                        file_prepare_standard_filemanager($articleinstance, $property_name, $attoptions, $context,
                                'mod_uniljournal', 'elementinstance', $aeinstance->id);
            }
        }
    }
}
else { // new entry
    $articleinstance = new stdClass();
    $articleinstance->id = null;
    $version = 0;
}

$articleinstance->cmid = $cmid;
$articleinstance->amid = $amid;
if ($articleinstance->id) {
    $authorid = $articleinstance->userid;
}

$customdata = [];
$customdata['current'] = $articleinstance;
$customdata['course'] = $course;
$customdata['articlemodel'] = $articlemodel;
$customdata['articleelements'] = $articleelements;
$customdata['attachmentoptions'] = $attachmentoptions;
$customdata['textfieldoptions'] = $textfieldoptions;
$customdata['textonlyoptions'] = $textonlyoptions;
$customdata['cm'] = $cm;

if ($articlemodel->themebankid) {
    $customdata['themes'] = $DB->get_records_select('uniljournal_themes',
            "themebankid = " . $articlemodel->themebankid . " AND hidden != '\x31' ORDER BY sortorder ASC");
}

require_once('edit_article_form.php');
$mform = new edit_article_form(null, $customdata);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    // is the article existing (or were we creating a new one?)
    if ($id) {
        // existing
        uniljournal_unset_article_lock($id, $USER->id);
        redirect(new moodle_url('/mod/uniljournal/view_article.php', ['id' => $id, 'cmid' => $cm->id]));
    }
    else {
        // cancelling creating a new one (so none to get back to)
        redirect(new moodle_url('/mod/uniljournal/view.php', ['id' => $cm->id]));
    }
}
else if ($articleinstance = $mform->get_data()) {
    $isnewentry = empty($articleinstance->id);
    $articleinstance->articlemodelid = $amid;
    $articleinstance->timemodified = time();

    if (!isset($articlemodel->freetitle) || $articlemodel->freetitle == 0) {
        $articleinstance->title = ""; // Makes sure the article title is deleted if it exists.
    }

    if ($isnewentry) {
        $articleinstance->timecreated = time();
        $authorid = $USER->id;
        $articleinstance->userid = $USER->id; // A new article is always owned by its creator
        $articleinstance->groupid = uniljournal_get_activegroup();
        $articleinstance->id = $DB->insert_record('uniljournal_articleinstances', $articleinstance);
        // Log the article creation
        $event = \mod_uniljournal\event\article_created::create(['other'    => ['userid'    => $USER->id,
                                                                                'articleid' => $articleinstance->id],
                                                                 'courseid' => $course->id,
                                                                 'objectid' => $articleinstance->id,
                                                                 'context'  => $context,]);
        $event->trigger();
    }
    else {
        unset($articleinstance->userid); // Don't let a teacher take over an article
        $DB->update_record('uniljournal_articleinstances', $articleinstance);
        // Log the article update
        $event = \mod_uniljournal\event\article_updated::create(['other'    => ['userid'    => $USER->id,
                                                                                'articleid' => $articleinstance->id],
                                                                 'courseid' => $course->id,
                                                                 'objectid' => $articleinstance->id,
                                                                 'context'  => $context,]);
        $event->trigger();
    }

    foreach ($articleelements as $ae) {
        $property_name = 'element_' . $ae->id;
        $property_edit = $property_name . '_editor';
        $property_format = $property_name . 'format';
        if (isset($articleinstance->$property_name) or isset($articleinstance->$property_edit)) {
            $element = new stdClass();
            $element->instanceid = $articleinstance->id;
            $element->elementid = $ae->id;
            $element->version = $version + 1;
            $element->timemodified = time();
            $element->userid = $USER->id;
            if (isset($articleinstance->$property_name)) {
                $element->value = $articleinstance->$property_name;
            }
            // TODO: Avoid re-writing records that haven't changed !
            $element->id = $DB->insert_record('uniljournal_aeinstances', $element);

            if ($ae->element_type == 'text' or $ae->element_type == 'textonly') {
                $articleinstance =
                        file_postupdate_standard_editor($articleinstance, $property_name, $textfieldoptions, $context,
                                'mod_uniljournal', 'elementinstance', $element->id);
                $element->value = $articleinstance->$property_name;
                $element->valueformat = $articleinstance->$property_format;
                $DB->update_record('uniljournal_aeinstances', $element);
            }
            elseif (uniljournal_startswith($ae->element_type, 'attachment_')) {
                $draftitemid = $articleinstance->$property_name;
                $context = context_module::instance($cmid);
                if ($draftitemid) {
                    file_save_draft_area_files($draftitemid, $context->id, 'mod_uniljournal', 'elementinstance',
                            $element->id, $attachmentoptions);
                }
            }
        }
    }
    uniljournal_unset_article_lock($id, $USER->id);
    if (has_capability('mod/uniljournal:editallarticles', $context)) {
        redirect(new moodle_url('/mod/uniljournal/view_articles.php', ['id' => $cm->id, 'uid' => $authorid]));
    }
    else {
        redirect(new moodle_url('/mod/uniljournal/view.php', ['id' => $cm->id]));
    }
}

$url = new moodle_url('/mod/uniljournal/edit_article.php', ['cmid' => $cm->id, 'articlemodelid' => $amid]);
$uniljournal_renderer = $PAGE->get_renderer('mod_uniljournal');
$PAGE->set_url($url);
$PAGE->set_title(format_string(get_string('writearticletempl', 'mod_uniljournal', $articlemodel->title)));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js('/mod/uniljournal/edit_article.js');

echo $OUTPUT->header();
if (isset($groupid)) {
    $group = $DB->get_record('groups', ['id' => $groupid]);
    if ($group) {
        $groupname = $group->name;
    }
    else {
        $groupname = '(' . get_string('nogroup', 'group') . ')';
    }
    echo $OUTPUT->heading(get_string('group') . ': ' . $groupname);
}
else if (isset($authorid) && !uniljournal_is_my_articleinstance($articleinstance_db, $USER->id)) {
    $author = $DB->get_record('user', ['id' => $authorid]);
    echo $OUTPUT->heading(fullname($author, has_capability('moodle/site:viewfullnames', $context)));
}
echo $OUTPUT->heading(get_string('writearticletempl', 'mod_uniljournal', $articlemodel->title));

if ($uniljournal->comments_allowed) {
    echo '<div class="article-comments">';
    echo $uniljournal_renderer->display_comments($cmid, $id, $version, $USER->id, -1);
    echo '</div>';
}

echo '<div class="article clearfix">';
echo '<div class="article-edit ' . ($uniljournal->comments_allowed ? '' : 'nocomments') . '">';
$mform->display();
echo '</div>';
echo '</div>';

echo $OUTPUT->footer();
