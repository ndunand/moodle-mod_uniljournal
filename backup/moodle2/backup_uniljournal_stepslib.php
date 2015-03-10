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
 * @copyright  2014-2015  Université de Lausanne
 * @author     Liip AG eLearning Team <elearning@liip.ch>
 * @author     Didier Raboud <didier.raboud@liip.ch>
 * @author     Claude Bossy <claude.bossy@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete uniljournal structure for backup, with file and id annotations
 */
class backup_uniljournal_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $uniljournal = new backup_nested_element('uniljournal', array('id'), array(
            'course', 'name', 'subtitle', 'intro',
            'introformat', 'logo', 'comments_allowed', 'timecreated', 'timemodified'));

        $articlemodels = new backup_nested_element('articlemodels');
        $articlemodel = new backup_nested_element('articlemodel', array('id'), array(
            'uniljournalid', 'title', 'maxbytes', 'instructions', 'instructionsformat',
            'freetitle', 'themebankid', 'sortorder', 'hidden'
        ));

        $articleelements = new backup_nested_element('articleelements');
        $articleelement = new backup_nested_element('articleelement', array('id'), array(
            'articlemodelid', 'element_type', 'sortorder'
        ));

        $articleinstances = new backup_nested_element('articleinstances');
        $articleinstance = new backup_nested_element('articleinstance', array('id'), array(
            'articlemodelid', 'userid', 'timemodified', 'title', 'status', 'themeid'
        ));

        $aeinstances = new backup_nested_element('aeinstances');
        $aeinstance = new backup_nested_element('aeinstance', array('id'), array(
            'instanceid', 'elementid', 'userid', 'version', 'timemodified', 'value', 'valueformat'
        ));

        $themebanks = new backup_nested_element('themebanks');
        $themebank = new backup_nested_element('themebank', array('id'), array(
            'contextid', 'title'
        ));

        $themes = new backup_nested_element('themes');
        $theme = new backup_nested_element('theme', array('id'), array(
            'themebankid', 'title', 'instructions', 'instructionsformat', 'sortorder', 'hidden'
        ));

        $article_comments = new backup_nested_element('article_comments');
        $article_comment = new backup_nested_element('article_comment', array('id'), array(
            'articleinstanceid', 'articleinstanceversion', 'userid', 'text'
        ));

        // Build the tree
        $uniljournal->add_child($articlemodels);
        $uniljournal->add_child($articleelements);
        $uniljournal->add_child($articleinstances);
        $uniljournal->add_child($aeinstances);
        $uniljournal->add_child($themebanks);
        $uniljournal->add_child($themes);
        $uniljournal->add_child($article_comments);

        $articlemodels->add_child($articlemodel);
        $articleelements->add_child($articleelement);
        $articleinstances->add_child($articleinstance);
        $aeinstances->add_child($aeinstance);
        $themebanks->add_child($themebank);
        $themes->add_child($theme);
        $article_comments->add_child($article_comment);


        // Define sources
        $uniljournal->set_source_table('uniljournal', array('id' => backup::VAR_ACTIVITYID));

        $articlemodel->set_source_sql('
            SELECT *
              FROM {uniljournal_articlemodels}
             WHERE uniljournalid = ?',
            array(backup::VAR_PARENTID)
        );

        $articleelement->set_source_sql('
            SELECT e.*
            FROM {uniljournal_articlemodels} m, {uniljournal_articleelements} e
            WHERE m.uniljournalid = ?
            AND m.id = e.articlemodelid',
            array(backup::VAR_PARENTID)
        );

        $themebank->set_source_sql('
            SELECT tb.*
            FROM {uniljournal_articlemodels} m, {uniljournal_themebanks} tb
            WHERE m.uniljournalid = ?
            AND tb.id = m.themebankid
        ', array(backup::VAR_PARENTID));

        $theme->set_source_sql('
            SELECT t.*
            FROM {uniljournal_articlemodels} m, {uniljournal_themebanks} tb, {uniljournal_themes} t
            WHERE m.uniljournalid = ?
            AND tb.id = m.themebankid
            AND t.themebankid = tb.id
        ', array(backup::VAR_PARENTID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $articleinstance->set_source_sql('
                SELECT a.*
                FROM {uniljournal_articleinstances} a, {uniljournal_articlemodels} m
                WHERE m.uniljournalid = ?
                AND m.id = a.articlemodelid
            ', array(backup::VAR_PARENTID));


            $aeinstance->set_source_sql('
                SELECT ae.*
                FROM {uniljournal_articleinstances} a, {uniljournal_articlemodels} m, {uniljournal_aeinstances} ae
                WHERE m.uniljournalid = ?
                AND m.id = a.articlemodelid
                AND a.id = ae.instanceid
            ', array(backup::VAR_PARENTID));

            $article_comment->set_source_sql('
                SELECT c.*
                FROM {uniljournal_articleinstances} a, {uniljournal_articlemodels} m, {uniljournal_article_comments} c
                WHERE m.uniljournalid = ?
                AND m.id = a.articlemodelid
                AND a.id = c.articleinstanceid
            ', array(backup::VAR_PARENTID));
        }

        // Define id annotations
        $articleinstance->annotate_ids('user', 'userid');
        $aeinstance->annotate_ids('user', 'userid');
        $article_comment->annotate_ids('user', 'userid');

        // Define file annotations
        $uniljournal->annotate_files('mod_uniljournal', 'intro', null);
        $uniljournal->annotate_files('mod_uniljournal', 'logo', null);
        $aeinstance->annotate_files('mod_uniljournal', 'elementinstance', 'id');
        $theme->annotate_files('mod_uniljournal', 'theme', 'id');

        // Return the root element (uniljournal), wrapped into standard activity structure
        return $this->prepare_activity_structure($uniljournal);
    }
}