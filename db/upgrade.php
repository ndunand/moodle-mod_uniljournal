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
 * This file keeps track of upgrades to the uniljournal module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_uniljournal
 * @copyright  2014 Liip AG {@link http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute uniljournal upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_uniljournal_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2014122200) {

        // Define field course to be added to uniljournal.
        $table = new xmldb_table('uniljournal');
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');

        // Add field course.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field timecreated to be added to uniljournal.
        $table = new xmldb_table('uniljournal');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
            'introformat');

        // Add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field timemodified to be added to uniljournal.
        $table = new xmldb_table('uniljournal');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0',
            'timecreated');

        // Add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define index course (not unique) to be added to uniljournal.
        $table = new xmldb_table('uniljournal');
        $index = new xmldb_index('courseindex', XMLDB_INDEX_NOTUNIQUE, array('course'));

        // Add index to course field.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Another save point reached.
        upgrade_mod_savepoint(true, 2014122200, 'uniljournal');
    }
    
    if ($oldversion < 2015010500) {

        // Define field subtitle to be added to uniljournal.
        $table = new xmldb_table('uniljournal');
        $field = new xmldb_field('subtitle', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'name');

        // Conditionally launch add field subtitle.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
         // Define field description to be added to uniljournal.
        $table = new xmldb_table('uniljournal');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'subtitle');

        // Conditionally launch add field description.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define field logo to be added to uniljournal.
        $table = new xmldb_table('uniljournal');
        $field = new xmldb_field('logo', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'description');

        // Conditionally launch add field logo.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Uniljournal savepoint reached.
        upgrade_mod_savepoint(true, 2015010500, 'uniljournal');
    }
    
    if ($oldversion < 2015010501) {

        // Define field comments_allowed to be added to uniljournal.
        $table = new xmldb_table('uniljournal');
        $field = new xmldb_field('comments_allowed', XMLDB_TYPE_BINARY, null, null, null, null, null, 'logo');

        // Conditionally launch add field comments_allowed.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Uniljournal savepoint reached.
        upgrade_mod_savepoint(true, 2015010501, 'uniljournal');
    }

    if ($oldversion < 2015010502) {

        // Define field id to be added to uniljournal_articlemodels.
        $table = new xmldb_table('uniljournal_articlemodels');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('uniljournalid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'uniljournalid');
        $table->add_field('maxbytes', XMLDB_TYPE_INTEGER, '10', null, null, null, '100000', 'title');
        $table->add_field('instructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'maxbytes');
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'instructions');
        $table->add_field('hidden', XMLDB_TYPE_BINARY, null, null, XMLDB_NOTNULL, null, null, 'sortorder');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('uniljournalid', XMLDB_INDEX_NOTUNIQUE, array('uniljournalid'));

        // Conditionally launch create table for assign_user_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Uniljournal savepoint reached.
        upgrade_mod_savepoint(true, 2015010502, 'uniljournal');
    }

    if ($oldversion < 2015010600) {

        // Rename field description on table uniljournal to intro.
        $table = new xmldb_table('uniljournal');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'subtitle');

        // Launch rename field description.
        $dbman->rename_field($table, $field, 'intro');
        
         // Define field introformat to be added to uniljournal.
        $field = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'intro');

        // Conditionally launch add field introformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Uniljournal savepoint reached.
        upgrade_mod_savepoint(true, 2015010600, 'uniljournal');
    }
    
    if ($oldversion < 2015010602) {

        // Define field instructionsformat to be added to uniljournal_articlemodels.
        $table = new xmldb_table('uniljournal_articlemodels');
        $field = new xmldb_field('instructionsformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'instructions');

        // Conditionally launch add field instructionsformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Uniljournal savepoint reached.
        upgrade_mod_savepoint(true, 2015010602, 'uniljournal');
    }
    
    if ($oldversion < 2015011300) {
    
        // Define field id to be added to uniljournal_articlemodels.
        $table = new xmldb_table('uniljournal_articleelements');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('articlemodelid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        $table->add_field('element_type', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null, 'articlemodelid');
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'element_type');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('articlemodelid', XMLDB_INDEX_NOTUNIQUE, array('articlemodelid'));

        // Conditionally launch create table for assign_user_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Uniljournal savepoint reached.
        upgrade_mod_savepoint(true, 2015011300, 'uniljournal');
    }
    
    if ($oldversion < 2015011400) {
    
        $table = new xmldb_table('uniljournal_articleinstances');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('articlemodelid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'articlemodelid');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('articlemodelid', XMLDB_INDEX_NOTUNIQUE, array('articlemodelid'));
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch create table for assign_user_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        $table = new xmldb_table('uniljournal_aeinstances');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('instanceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        $table->add_field('elementid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'instanceid');
        $table->add_field('version', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'elementid');
        $table->add_field('text', XMLDB_TYPE_TEXT, null, null, null, null, null, 'version');
        $table->add_field('file', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'text');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('instanceid', XMLDB_INDEX_NOTUNIQUE, array('instanceid'));
        $table->add_index('elementid', XMLDB_INDEX_NOTUNIQUE, array('elementid'));

        // Conditionally launch create table for assign_user_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Uniljournal savepoint reached.
        upgrade_mod_savepoint(true, 2015011400, 'uniljournal');
    }
    
    if ($oldversion < 2015011401) {
    
        $table = new xmldb_table('uniljournal_articleinstances');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'userid');

        // Conditionally launch add field instructionsformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('uniljournal_aeinstances');
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'version');

        // Conditionally launch add field instructionsformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Uniljournal savepoint reached.
        upgrade_mod_savepoint(true, 2015011401, 'uniljournal');
    }
    
    return true;
}
