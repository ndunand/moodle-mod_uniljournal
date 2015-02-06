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
 * Internal library of functions for module uniljournal
 *
 * All the uniljournal specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_uniljournal
 * @copyright  2015 Liip AG {@link http://www.liip.ch/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("lib.php");

function uniljournal_set_logo($data) {
    global $DB;
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->logo;

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_uniljournal', 'logo', 0, array('subdirs'=>false));
    }
}

function uniljournal_get_logo($context) {
    global $DB;
    $fs = get_file_storage();
    
    $logos = $fs->get_area_files($context->id, 'mod_uniljournal', 'logo', false, $sort = "itemid, filepath, filename", false);
    foreach($logos as $logo) {
      if ($logo->is_valid_image()) {
        return $logo;
      }
    }
    return false;
}

function uniljournal_get_elements_array() {
  global $CFG;

  $options = array();
  $types = array('subtitle', 'textonly', 'text');
  foreach(explode(',',get_config('uniljournal','allowedmimegroups')) as $allowedmime) {
    // Exclude the web_.* types anyway
    if(!uniljournal_startswith($allowedmime, 'web_')) {
      $types[] = 'attachment_'.$allowedmime;
    }
  }
  foreach($types as $elem) {
    $options[$elem] = get_string('element_'.$elem, 'uniljournal');
  }
  return $options;
}

function uniljournal_startswith($elementstr, $prefix = 'attachment_') {
  return (substr_compare($elementstr, $prefix, 0, strlen($prefix)) === 0);
}

function uniljournal_translate_templatedesc(&$item, $key) {
   $item = get_string('element_'.$key.'_desc', 'mod_uniljournal', $item);
}

function uniljournal_get_template_descriptions($uniljournalid, $onlyhidden=true) {
  global $DB;
  
  $hiddenSQL = '';
  if($onlyhidden) $hiddenSQL = " AND am.hidden != '\x31' ";
  
  $articleelements = $DB->get_records_sql("
      SELECT ae.id as aeid, am.id as id, am.title, am.sortorder as sortorder, ae.element_type, ae.sortorder as aesortorder
          FROM {uniljournal_articlemodels} am
      INNER JOIN {uniljournal_articleelements} ae ON ae.articlemodelid = am.id
      WHERE am.uniljournalid = :uniljournalid "
      .$hiddenSQL.
      "ORDER BY am.sortorder ASC, ae.sortorder ASC", array('uniljournalid' => $uniljournalid));

  $articleelementsgroups = array();
  foreach($articleelements as $aeid => $aehybrid) {
    if(!array_key_exists($aehybrid->id, $articleelementsgroups)) {
      $articleelementsgroups[$aehybrid->id] = array();
    }
    if(!array_key_exists($aehybrid->element_type, $articleelementsgroups[$aehybrid->id])) {
      $articleelementsgroups[$aehybrid->id][$aehybrid->element_type] = 0;
    }
    $articleelementsgroups[$aehybrid->id][$aehybrid->element_type]++;
  }

  $templatesoptions = array();

  foreach($articleelements as $aeid => $am)  {
    if(array_key_exists($am->id, $articleelementsgroups) && !array_key_exists($am->id, $templatesoptions)) {
      array_walk($articleelementsgroups[$am->id], 'uniljournal_translate_templatedesc');
      
      $templatesoptions[$am->id] = $articleelementsgroups[$am->id];
    }
  }
  
  return $templatesoptions;
}

function uniljournal_article_status($id = null) {
  $statuses = array();
  $statuses[0] = '-';
  $statuses[10] = '◯'; // Started
  $statuses[20] = '◐'; // In progress
  $statuses[30] = '⬤'; // Finished
  $statuses[40] = '✓'; // Finished
  
  if(is_null($id)) {
    return $statuses;
  } else if (array_key_exists($id, $statuses)) {
    return $statuses[$id];
  } else {
    return false;
  }
}

function uniljournal_get_theme_banks($cm, $course) {
  global $DB, $USER;

  $module_context      = context_module::instance($cm->id);
  $course_context      = context_course::instance($course->id);
  $category_context    = context_coursecat::instance($course->category);
  $system_context      = context_system::instance();
  $user_context        = context_user::instance($USER->id);
  $contexts = array('module_context' => $module_context->id,
      'course_context' => $course_context->id,
      'category_context' => $category_context->id,
      'system_context' => $system_context->id,
      'user_context' => $user_context->id);

  return $DB->get_records_sql('
        SELECT tb.*, COUNT(t.id) as themescount
          FROM {uniljournal_themebanks} tb
          LEFT JOIN {uniljournal_themes} t ON t.themebankid = tb.id
          WHERE contextid = :module_context
          OR contextid = :course_context
          OR contextid = :category_context
          OR contextid = :system_context
          OR contextid = :user_context
          GROUP BY tb.id', $contexts);
}

function uniljournal_get_article_instances($query_args = array('id' => '0'), $status = false) {
  global $DB;
  $where = array();
  foreach($query_args as $key => $v) {
    if($key == 'id') {
        if (is_array($v)) {
            $where[] = "ai.id IN (" . implode(',', $v) . ")";
        } else {
            $where[] = "ai.id = :id";
        }
    } else {
      $where[] = "$key = :$key";
    }
  }
  
  $attributes = array(
    'ai.id as id',
    'ai.timemodified',
    'ai.userid',
    'ai.title',
    't.title as themetitle',
    'ai.status',
    'am.id as amid',
    'am.title as amtitle',
    'am.freetitle as freetitle',
    'am.instructions as instructions');
  
  $statusrequest = '';
  if($status) {
    // Fetch the article instance status. Make that an option as that's an expensive request
    array_push($attributes,
      'astatus.maxversion',
      'astatus.edituserid',
      'astatus.commentuserid');
    $statusrequest = 'LEFT JOIN (
              SELECT DISTINCT instanceid as id, version as maxversion, aei.userid as edituserid, c.userid as commentuserid
                         FROM {uniljournal_aeinstances} aei
                    LEFT JOIN {uniljournal_article_comments} c ON c.articleinstanceid = aei.instanceid AND c.articleinstanceversion = aei.version
                        WHERE (instanceid, version) IN (
                                                        SELECT instanceid, max(version) as maxversion
                                                          FROM {uniljournal_aeinstances} GROUP BY instanceid
                                                        )
            ) astatus ON astatus.id = ai.id';
  }
  // MONSTER query to get a list of articles, with all relevant informations concerning comments, max versions, etc
  return $DB->get_records_sql('SELECT '.implode($attributes, ', ').'
       FROM {uniljournal_articleinstances} ai
            '.$statusrequest.'
  LEFT JOIN {uniljournal_articlemodels} am ON am.id = ai.articlemodelid
  LEFT JOIN {uniljournal_themes} t ON ai.themeid = t.id
      WHERE '.implode($where, ' AND ').'
   ORDER BY ai.status ASC, ai.timemodified DESC', $query_args);

}

function uniljournal_articletitle($articleinstance) {
  // Set the article title based on the theme
  if($articleinstance->freetitle == 1 && !empty($articleinstance->title)) {
    $title = $articleinstance->title;
  } elseif(property_exists($articleinstance, 'themetitle') && !empty($articleinstance->themetitle)) {
    $title = $articleinstance->themetitle;
  } else {
    $title = 'ERROR: no title set';
  }
  return $title;
}

/*
  Display a version toggler to be used in articles view
*/
function uniljournal_versiontoggle($articleinstance, $cm, $actualversion, $targetfile = 'view_article.php', $targetargument = 'version', $otherargs = array()) {

  $base_args = array(
    'id' => $articleinstance->id,
    'cmid' => $cm->id
  );
  
  $args = array_merge($base_args, $otherargs);

  $html = '<div class="article-version-toggle">';
  if($actualversion > 1) {
    $html .= link_arrow_left(
      get_string('version_previous', 'uniljournal'),
      new moodle_url('/mod/uniljournal/'.$targetfile, array_merge($args, array($targetargument => $actualversion-1))),
      true);
  }
  
  // Add links to the standalone versions
  $actualversionlink = html_writer::link(
      new moodle_url('/mod/uniljournal/view_article.php', array_merge($args, array('version' => $actualversion))),
      $actualversion);
      
  $maxversionlink = html_writer::link(
      new moodle_url('/mod/uniljournal/view_article.php', array_merge($args, array('version' => $articleinstance->maxversion))),
      $articleinstance->maxversion);


  $html .= html_writer::tag('span', get_string('version').' '.$actualversionlink." / ".$maxversionlink);

  if($actualversion < $articleinstance->maxversion) {
    $html .= link_arrow_right(
      get_string('version_next', 'uniljournal'),
      new moodle_url('/mod/uniljournal/'.$targetfile, array_merge($args, array($targetargument => $actualversion+1))),
      true);
  }

  $html .= '</div>';

  return $html;
}