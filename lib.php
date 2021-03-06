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
 * Library of interface functions and constants for module uniljournal
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the uniljournal specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_uniljournal
 * @copyright  2014-2015  Université de Lausanne
 * @author     Liip AG eLearning Team <elearning@liip.ch>
 * @author     Didier Raboud <didier.raboud@liip.ch>
 * @author     Claude Bossy <claude.bossy@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/uniljournal/locallib.php');

/*
 * Example constant:
 * define('NEWMODULE_ULTIMATE_ANSWER', 42);
 */

/**
 * Moodle core API
 */

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function uniljournal_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the uniljournal into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $uniljournal An object from the form in mod_form.php
 * @param mod_uniljournal_mod_form $mform
 * @return int The id of the newly inserted uniljournal record
 */
function uniljournal_add_instance(stdClass $uniljournal, mod_uniljournal_mod_form $mform = null) {
    global $DB;
    require_once("locallib.php");

    $cmid = $uniljournal->coursemodule;
    $uniljournal->timecreated = time();

    $uniljournal->id = $DB->insert_record('uniljournal', $uniljournal);

    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $uniljournal->id, ['id' => $cmid]);
    uniljournal_set_logo($uniljournal);

    $completiontimeexpected = !empty($uniljournal->completionexpected) ? $uniljournal->completionexpected : null;
    \core_completion\api::update_completion_date_event($uniljournal->coursemodule, 'uniljournal', $uniljournal->id, $completiontimeexpected);

    return $uniljournal->id;
}

/**
 * Updates an instance of the uniljournal in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $uniljournal An object from the form in mod_form.php
 * @param mod_uniljournal_mod_form $mform
 * @return boolean Success/Fail
 */
function uniljournal_update_instance(stdClass $uniljournal, mod_uniljournal_mod_form $mform = null) {
    global $DB;
    require_once("locallib.php");

    $uniljournal->timemodified = time();
    $uniljournal->id = $uniljournal->instance;

    $DB->update_record('uniljournal', $uniljournal);
    uniljournal_set_logo($uniljournal);

    $completiontimeexpected = !empty($uniljournal->completionexpected) ? $uniljournal->completionexpected : null;
    \core_completion\api::update_completion_date_event($uniljournal->coursemodule, 'uniljournal', $uniljournal->id, $completiontimeexpected);

    return true;
}

/**
 * Removes an instance of the uniljournal from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function uniljournal_delete_instance($id) {
    global $DB;

    if (!$uniljournal = $DB->get_record('uniljournal', ['id' => $id])) {
        return false;
    }

    $module = $DB->get_record('modules', ['name' => 'uniljournal']);
    $course_module = $DB->get_record('course_modules', ['instance' => $id, 'module' => $module->id]);

    $context = context_module::instance($course_module->id);

    $templates = $DB->get_records('uniljournal_articlemodels', ['uniljournalid' => $uniljournal->id]);

    $theme_banks = $DB->get_records('uniljournal_themebanks', ['contextid' => $context->id]);
    foreach ($theme_banks as $theme_bank) {
        $DB->delete_records('uniljournal_themes', ['themebankid' => $theme_bank->id]);
        $DB->delete_records('uniljournal_themebanks', ['contextid' => $context->id]);
    }

    foreach ($templates as $template) {
        $DB->delete_records('uniljournal_articleelements', ['articlemodelid' => $template->id]);
        $articles = $DB->get_records('uniljournal_articleinstances', ['articlemodelid' => $template->id]);

        foreach ($articles as $article) {
            $DB->delete_records('uniljournal_article_comments', ['articleinstanceid' => $article->id]);
            $DB->delete_records('uniljournal_aeinstances', ['instanceid' => $article->id]);
        }
        $DB->delete_records('uniljournal_articleinstances', ['articlemodelid' => $template->id]);
    }

    $DB->delete_records('uniljournal_articlemodels', ['uniljournalid' => $uniljournal->id]);

    $DB->delete_records('uniljournal', ['id' => $uniljournal->id]);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function uniljournal_user_outline($course, $user, $mod, $uniljournal) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';

    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $uniljournal the module instance record
 * @return void, is supposed to echp directly
 */
function uniljournal_user_complete($course, $user, $mod, $uniljournal) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in uniljournal activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function uniljournal_print_recent_activity($course, $viewfullnames, $timestart) {
    return false; // True if anything was printed, otherwise false.
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link uniljournal_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function uniljournal_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0,
                                             $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@see uniljournal_get_recent_mod_activity()}
 *
 * @return void
 */
function uniljournal_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function uniljournal_cron() {
    global $CFG, $DB;
    list($sql, $params) = $DB->get_in_or_equal(array('editlock'), SQL_PARAMS_QM, 'param', false, null);
    $lockedarticleinstances = $DB->get_records_sql('SELECT * FROM {uniljournal_articleinstances} ai WHERE editlock ' . $sql, $params);
    foreach($lockedarticleinstances as $lockedarticleinstance) {
        $lock = unserialize($lockedarticleinstance->editlock);
        $lockinguser = $DB->get_record('user', array('id' => (int)$lock['userid']));
        if (time() - $lockinguser->lastaccess > $CFG->sessiontimeout) {
            uniljournal_unset_article_lock($lockedarticleinstance->id, $lockinguser->id);
        }
    }
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function uniljournal_get_extra_capabilities() {
    return ['moodle/site:viewfullnames'];
}

/**
 * Gradebook API                                                              //
 */

/**
 * Is a given scale used by the instance of uniljournal?
 *
 * This function returns if a scale is being used by one uniljournal
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $uniljournalid ID of an instance of this module
 * @return bool true if the scale is used by the given uniljournal instance
 */
function uniljournal_scale_used($uniljournalid, $scaleid) {
    global $DB;

    /* @example */
    if ($scaleid and $DB->record_exists('uniljournal', ['id' => $uniljournalid, 'grade' => -$scaleid])) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of uniljournal.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any uniljournal instance
 */
function uniljournal_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * File API                                                                   //
 */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function uniljournal_get_file_areas($course, $cm, $context) {
    return ["logo"            => get_string('logo', 'mod_uniljournal'),
            "elementinstance" => get_string('elementinstance', 'mod_uniljournal'),
            "theme"           => get_string('themeinstructions', 'mod_uniljournal'),];
}

/**
 * File browsing support for uniljournal file areas
 *
 * @package mod_uniljournal
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cmget_mimetypes_array
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function uniljournal_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {

    if (!has_capability('moodle/course:managefiles', $context)) {
        return null;
    }

    $fs = get_file_storage();

    switch ($filearea) {
        case "logo":
            $filepath = is_null($filepath) ? '/' : $filepath;
            $filename = is_null($filename) ? '.' : $filename;

            $urlbase = $CFG->wwwroot . '/pluginfile.php';
            if (!$storedfile = $fs->get_file($context->id, 'mod_uniljournal', 'logo', 0, $filepath, $filename)) {
                if ($filepath === '/' and $filename === '.') {
                    $storedfile = new virtual_root_file($context->id, 'mod_uniljournal', 'logo', 0);
                }
                else {
                    // not found
                    return null;
                }
            }

            return new file_info_stored($browser, $context, $storedfile, $urlbase, $areas[$filearea], false, true,
                    false, false);
        case "elementinstance":
            $urlbase = $CFG->wwwroot . '/pluginfile.php';
            if (!$storedfile =
                    $fs->get_file($context->id, 'mod_uniljournal', 'elementinstance', $itemid, $filepath, $filename)
            ) {
                return null;
            }

            return new file_info_stored($browser, $context, $storedfile, $urlbase, $areas[$filearea], false, true,
                    false, false);
    }

    return false;
}

/**
 * Serves the files from the uniljournal file areas
 *
 * @package mod_uniljournal
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the uniljournal's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function uniljournal_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = []) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    //hack to make the image readable by TCPDF
    if ($filearea != 'elementinstance_pdf') {
        if ($filearea != 'logo') {
            require_login($course, true, $cm);
        }
    }
    else {
        $filearea = 'elementinstance';
    }

    if (!$uniljournal = $DB->get_record('uniljournal', ['id' => $cm->instance])) {
        return false;
    }

    $fileareas = uniljournal_get_file_areas($course, $cm, $context);
    if (!array_key_exists($filearea, $fileareas)) {
        return false;
    }

    if (count($args) == 0) {
        return false;
    }

    if (count($args) == 1) {
        $filepath = array_shift($args);
    }
    else {
        $itemid = (int)array_shift($args);
        $filepath = array_shift($args);
    }

    $fs = get_file_storage();
    switch ($filearea) {
        case "logo":
            $itemid = 0;
        case "elementinstance":
            break;
        case "theme":
            break;
        default:
            return false;
    }

    $fullpath = "/$context->id/mod_uniljournal/$filearea/$itemid/$filepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, true);
}

/**
 * Navigation API                                                             //
 */

/**
 * Extends the global navigation tree by adding uniljournal nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the uniljournal module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
// function uniljournal_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
// }

/**
 * Extends the settings navigation with the uniljournal settings
 *
 * This function is called when the context for the page is a uniljournal module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $uniljournalnode {@link navigation_node}
 */
function uniljournal_extend_settings_navigation(settings_navigation $settingsnav,
                                                navigation_node $uniljournalnode = null) {
    global $PAGE;

    if (has_capability('mod/uniljournal:managetemplates', $PAGE->cm->context)) {
        $uniljournalnode->add(get_string("managetemplates", "mod_uniljournal"),
                new moodle_url('/mod/uniljournal/manage_templates.php', ['id' => $PAGE->cm->id]));
    }

    if (has_capability('mod/uniljournal:managethemes', $PAGE->cm->context)) {
        $uniljournalnode->add(get_string("managethemebanks", "mod_uniljournal"),
                new moodle_url('/mod/uniljournal/manage_themebanks.php', ['id' => $PAGE->cm->id]));
    }

    if (has_capability('mod/uniljournal:view', $PAGE->cm->context)) {
        $uniljournalnode->add(get_string("exportarticles", "mod_uniljournal"),
                new moodle_url('/mod/uniljournal/export_articles.php', ['id' => $PAGE->cm->id]));
    }
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_uniljournal_core_calendar_provide_event_action(calendar_event $event,
                                                            \core_calendar\action_factory $factory) {
    $cm = get_fast_modinfo($event->courseid)->instances['uniljournal'][$event->instance];

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
            get_string('view'),
            new \moodle_url('/mod/uniljournal/view.php', ['id' => $cm->id]),
            1,
            true
    );
}

