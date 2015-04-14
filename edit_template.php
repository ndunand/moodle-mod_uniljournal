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
 * Prints a particular instance of uniljournal
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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');

$cmid  = optional_param('cmid', 0, PARAM_INT); // Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);  // template ID

if ($cmid) {
    $cm         = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $uniljournal  = $DB->get_record('uniljournal', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
  print_error('id_missing', 'mod_uniljournal');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/uniljournal:managetemplates', $context);

$instructionsoptions = array('trusttext'=> true, 'maxfiles'=> 0, 'context'=> $context, 'subdirs'=>0);

if ($id) { // if entry is specified
    if (!$entry = $DB->get_record('uniljournal_articlemodels', array('id' => $id))) {
        print_error('invalidentry');
    }
    $elements = $DB->get_records('uniljournal_articleelements', array('articlemodelid' => $id), 'sortorder');
    if(count($elements) > 0) {
      // Extract the ids only
      $elementsids = $elements;
      array_walk($elementsids, function (&$item) { $item =  $item->id;});
      list ($inequal, $values) = $DB->get_in_or_equal($elementsids);
      if($DB->count_records_sql('SELECT COUNT(id) FROM {uniljournal_aeinstances} WHERE elementid '.$inequal, $values) > 0) {
        // Elements are already in use
        print_error('invalidentry');
      }
    }
} else { // new entry
    $entry = new stdClass();
    $entry->id = null;
    $elements = array();
}

$entry = file_prepare_standard_editor($entry, 'instructions', $instructionsoptions, $context, 'mod_uniljournal', 'articletemplates', $entry->id);
$entry->cmid = $cm->id;

require_once('locallib.php');
$themebanks = uniljournal_get_theme_banks($cm, $course);
// Don't allow selection of theme banks without themes
$themebanks = array_filter($themebanks, function($item) { return ($item->themescount > 0);});
array_walk($themebanks, function (&$item) { $item =  $item->title;});
$themebanks[-1] = get_string('template_nothemebank', 'uniljournal');
ksort($themebanks);

require_once('edit_template_form.php');
$customdata = array();
$customdata['current'] = $entry;
$customdata['course'] = $course;
$customdata['instructionsoptions'] = $instructionsoptions;
$customdata['cm'] = $cm;
$customdata['elements'] = $elements;
$customdata['themebanks'] = $themebanks;
$customdata['elementsoptions'] = uniljournal_get_elements_array();

$mform = new template_edit_form(null, $customdata);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/uniljournal/manage_templates.php', array('id' => $cm->id)));
} else if ($entry = $mform->get_data()) {
    $isnewentry = empty($entry->id);
    $entry->instructions       = '';          // updated later
    $entry->instructionsformat = FORMAT_HTML; // updated later
    $entry->sortorder = 0;
    $entry->hidden = false;
    $entry->uniljournalid = $uniljournal->id;
    if(!array_key_exists($entry->themebankid, $themebanks) || $entry->themebankid == -1) { // Force freetitle as there's no themebank
      $entry->freetitle = true;
      $entry->themebankid = null;
    } else {
      $entry->freetitle = (isset($entry->freetitle) && $entry->freetitle == 1);
    }

    if ($isnewentry) {
        // Add new entry.
        $entry->id = $DB->insert_record('uniljournal_articlemodels', $entry);

        // Log the template creation
        $event = \mod_uniljournal\event\template_created::create(array(
            'other' => array(
                'userid' => $USER->id,
                'templateid' => $entry->id
            ),
            'courseid' => $course->id,
            'objectid' => $entry->id,
            'context' => $context,
        ));
        $event->trigger();
    } else {
        // Update existing entry.
        $DB->update_record('uniljournal_articlemodels', $entry);

        // Log the template update
        $event = \mod_uniljournal\event\template_updated::create(array(
            'other' => array(
                'userid' => $USER->id,
                'templateid' => $entry->id
            ),
            'courseid' => $course->id,
            'objectid' => $entry->id,
            'context' => $context,
        ));
        $event->trigger();
    }
    $articleelementorder = 0;
    foreach($mform->getArticleElements() as $articleelementid => $articleelement) {
      $articleelementobject = new stdClass();
      $articleelementobject->articlemodelid = $entry->id;
      $articleelementobject->sortorder = $articleelementorder++;
      if(array_key_exists($articleelement, $customdata['elementsoptions'])) {
        $articleelementobject->element_type = $articleelement;
        if ($articleelement !== "0") {
          if(!array_key_exists($articleelementid, $elements)) { // -1 should never be in there
            // Add new entry
            $articleelementobject->id = $DB->insert_record('uniljournal_articleelements', $articleelementobject);
          } else {
            // Old element, update!
            $articleelementobject->id = $articleelementid;
            $DB->update_record('uniljournal_articleelements', $articleelementobject);
            // Don't delete it later on
            unset($elements[$articleelementid]);
          }
        }
      }
    }

    // Delete the elements that were there before and that aren't here anymore (see "unset(" above)
    foreach($elements as $articleelementid => $articleelement) {
      $DB->delete_records('uniljournal_articleelements', array('id' => $articleelementid));
    }

    // save and relink embedded images and save attachments
    $entry = file_postupdate_standard_editor($entry, 'instructions', $instructionsoptions, $context, 'mod_uniljournal', 'articletemplates', $entry->id);
    // store the updated value values
    $DB->update_record('uniljournal_articlemodels', $entry);

    redirect(new moodle_url('/mod/uniljournal/manage_templates.php', array('id' => $cm->id)));
}

$url = new moodle_url('/mod/uniljournal/edit_template.php', array('cmid'=>$cm->id));
if (!empty($id)) {
    $url->param('id', $id);

    // Log the template read action
    $event = \mod_uniljournal\event\template_read::create(array(
        'other' => array(
            'userid' => $USER->id,
            'templateid' => $id
        ),
        'courseid' => $course->id,
        'objectid' => $id,
        'context' => $context,
    ));
    $event->trigger();
}
$PAGE->set_url($url);
$PAGE->set_title(format_string($uniljournal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managetemplates', 'mod_uniljournal'));

//displays the form
$mform->display();

echo $OUTPUT->footer();

echo '
<script>
    $(function() {
        $( "#elementsToAdd" ).sortable({
            connectWith: "#elementsAdded",
            update: function(event, ui) {
                var item = $(ui.item[0].cloneNode(true));
                var position = parseInt(item.prop("id")[item.prop("id").length - 1]);
                if (position > 1) {
                    $(item).insertAfter($("#elementsToAdd #element" + (position - 1)));
                } else {
                    console.log(position)
                    console.log("#elementsToAdd #element" + (position + 1))
                    $(item).insertBefore($("#elementsToAdd #element" + (position + 1)));
                }

                var elementsAdded = $("#elementsAdded input[type=\"text\"]");
                for (var i = 0; i < elementsAdded.length; i++) {
                    var element = elementsAdded[i];
                    $(element).prop("name", "articleelements[]");
                }
            },
            beforeStop: function(event, ui) {
                var table = $("#elementsAdded");
                var minX = table.offset().left;
                var maxX = minX + table.width();
                var minY = table.offset().top;
                var maxY = minY + table.height();
                if (event.pageX > maxX || event.pageX < minX || event.pageY > maxY || event.pageY < minY) {
                    $(this).sortable("cancel");
                } else {
                    $("#error_elementsAdded").hide();
                }
            }
        });
        $( "#elementsAdded" ).sortable({
            stop: function(event, ui) {
                var table = $("#elementsAdded");
                var minX = table.position().left;
                var maxX = minX + table.width();
                var minY = table.position().top;
                var maxY = minY + table.height();
                if (event.pageX > maxX || event.pageX < minX || event.pageY > maxY || event.pageY < minY) {
                    $(event.originalEvent.target).remove();
                    if ($("#elementsAdded li").length < 1) {
                        $("#error_elementsAdded").show();
                    }
                }
            }
        });
        $("form[method=\"post\"]").on("submit", function (e) {
            if ($("#elementsAdded li").length > 0) {
                $("#error_elementsAdded").hide();
            } else {
                $("#error_elementsAdded").show();
                $("#error_elementsAdded").focus();
                $("html, body").animate({
                    scrollTop: $("#elementsAdded").offset().top
                }, 0);
                return false;
            }
        });
    });
</script>';
