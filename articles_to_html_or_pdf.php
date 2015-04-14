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
require_once(dirname(dirname(dirname(__FILE__))).'/lib/pdflib.php');

$cmid    = optional_param('cmid', 0, PARAM_INT);  // Course_module ID
$articleinstanceids = $_POST['articles'];
$pdf = optional_param('format', 'html', PARAM_TEXT);
$pdf = ($pdf == 'pdf') ? true : false;

if ($cmid) {
    $cm              = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course          = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $uniljournal     = $DB->get_record('uniljournal', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
  print_error('id_missing', 'mod_uniljournal');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$uniljournal_renderer = $PAGE->get_renderer('mod_uniljournal');

$articleinstances = uniljournal_get_article_instances(array('id' => $articleinstanceids), true);

$articles = '';
$pdf_articles = [];

$count = 1;
$numarticles = count($articleinstances);
foreach($articleinstances as $articleinstance) {

    if ($articleinstance->userid == $USER->id) {
        require_capability('mod/uniljournal:view', $context);
    }

    // Get all elements of the model
    $articleelements = $DB->get_records_select('uniljournal_articleelements', "articlemodelid = $articleinstance->amid ORDER BY sortorder ASC");

    // Log the article read action
    $event = \mod_uniljournal\event\article_read::create(array(
        'other' => array(
            'userid' => $USER->id,
            'articleid' => $articleinstance->id
        ),
        'courseid' => $course->id,
        'objectid' => $articleinstance->id,
        'context' => $context,
    ));
    $event->trigger();

    $articletitle = uniljournal_articletitle($articleinstance);
    $articleinstance->title = $articletitle;

    list($article_html, $attachment_html, $attachment_files)= $uniljournal_renderer->display_article($articleinstance, $articleelements, $context, $pdf);

    if ($pdf) {
        $article_html = '<html><head><style>' . file_get_contents('pdf_CSS.css') . '</style></head><body>' . $article_html;
        if ($count != $numarticles) {
            $article_html .= '<br pagebreak="true"/>';
        }

        $article_html = $article_html . '</body></html>';

        $pdf_articles[$articleinstance->id] = [$article_html, $attachment_html, $attachment_files, $articleinstance];

        $count++;
    } else {
        $articles .= $article_html;
    }

}

if ($pdf) {
    make_cache_directory('tcpdf');

//  Create PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8');
    $pdf->setFontSubsetting(FALSE);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $pdf->setPrintHeader(FALSE);
    $pdf->setPrintFooter(FALSE);

    $pdf->AddPage(PDF_PAGE_ORIENTATION, PDF_PAGE_FORMAT, true, false);
    $pdf->writeHTML(uniljournal_title_page($cm, $uniljournal));

    foreach($pdf_articles as $pdf_article) {
        $pdf->AddPage(PDF_PAGE_ORIENTATION, PDF_PAGE_FORMAT, true, false);

        $pdf->Bookmark($pdf_article[3]->title, 0, 0, '', '', array(0,64,128));
        $pdf->writeHTML($pdf_article[0] . '</body></html>');

        if ($pdf_article[1]) {
          $pdf->AddPage(PDF_PAGE_ORIENTATION, PDF_PAGE_FORMAT, true, false);
          $pdf->writeHTML('<html><head><style>' . file_get_contents('pdf_CSS.css') . '</style></head><body>' . $pdf_article[1] . '</body></html>');
        }

        if (count($pdf_article[2]) > 0 ){
          $pdf->AddPage(PDF_PAGE_ORIENTATION, PDF_PAGE_FORMAT, true, false);
          $pdf->Bookmark(get_string('attachments', 'mod_uniljournal'), 1, 0, '', '', array(128,0,0));
          $pdf->Cell(0, 0, get_string('offlineattachments', 'mod_uniljournal'), 0, 1);
          $i = 0;
          foreach($pdf_article[2] as $attachment) {
            if ($i == 39) {
              $i = -1;
              $pdf->AddPage(PDF_PAGE_ORIENTATION, PDF_PAGE_FORMAT, true, false);
            }
            $pdf->Annotation(8, 33+6*$i, 2, 4, $attachment['filename'], array('Subtype' => 'FileAttachment', 'Name' => 'Paperclip', 'FS' => $attachment['url']));
            $pdf->Cell(0, 6, $attachment['filename'], 0, 1);
            $i++;
          }
        }
    }
    $pdfname = 'articles';
    foreach ($articleinstanceids as $id) {
      $pdfname .= '_' . $id;
    }
    $pdfname .= '_' . time() . '.pdf';

    $pdf->addTOCPage();

    $pdf->MultiCell(0, 0, get_string('toc', 'mod_uniljournal'), 0, '', 0, 1, '', '', true, 0);
    $pdf->Ln();
    $pdf->SetFont('helvetica', '', 10);

    $pdf->addTOC(2);
    $pdf->endTOCPage();

    $pdf->Output($pdfname, 'D');
} else {
    $PAGE->set_url('/mod/uniljournal/export_articles.php', array('id' => $cm->id));
    $PAGE->set_title(get_string('exportarticles', 'mod_uniljournal'));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->set_context($context);

// Output starts here.
    echo $OUTPUT->header();

    echo $articles;

// Finish the page.
    echo $OUTPUT->footer();

}
