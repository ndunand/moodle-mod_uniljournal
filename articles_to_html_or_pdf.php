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
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/pdflib.php');

$cmid = optional_param('cmid', 0, PARAM_INT);  // Course_module ID
$articleinstanceids = required_param_array('articles', PARAM_INT);
$pdf = optional_param('format', 'html', PARAM_TEXT);
$pdf = ($pdf == 'pdf') ? true : false;

if ($cmid) {
    $cm = get_coursemodule_from_id('uniljournal', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $uniljournal = $DB->get_record('uniljournal', ['id' => $cm->instance], '*', MUST_EXIST);
}
else {
    print_error('id_missing', 'mod_uniljournal');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$uniljournal_renderer = $PAGE->get_renderer('mod_uniljournal');

// TODO : remove because perf problem here
//$articleinstances = uniljournal_get_article_instances(['id' => $articleinstanceids], true,
//        't.sortorder, am.sortorder, ai.timecreated ASC');
// TODO : replace with this:
$articleinstances = [];
foreach ($articleinstanceids as $articleinstanceid) {
    $articleinstance = uniljournal_get_article_instances(['id' => $articleinstanceid], true,
            't.sortorder, am.sortorder, ai.timecreated ASC');
    $articleinstance = array_pop($articleinstance);
    $articleinstances[$articleinstance->id] = $articleinstance;
}

$articles = '';
$pdf_articles = [];

$count = 1;
$numarticles = count($articleinstances);
$authorid = 0;
foreach ($articleinstances as $articleinstance) {

    if ($articleinstance->userid == $USER->id) {
        require_capability('mod/uniljournal:view', $context);
    }
    else {
        require_capability('mod/uniljournal:viewallarticles', $context);
    }

    if (!$authorid || ($articleinstance->userid === $authorid)) {
        $authorid = $articleinstance->userid;
    }
    else {
        $authorid = 0;
    }

    // Get all elements of the model
    $articleelements = $DB->get_records_select('uniljournal_articleelements',
            "articlemodelid = $articleinstance->amid ORDER BY sortorder ASC");

    // Log the article read action
    $event = \mod_uniljournal\event\article_read::create([
            'other'       => [
                    'userid' => $USER->id, 'articleid' => $articleinstance->id
            ], 'courseid' => $course->id, 'objectid' => $articleinstance->id, 'context' => $context,
    ]);
    $event->trigger();

    $articletitle = uniljournal_articletitle($articleinstance);
    $articleinstance->title = $articletitle;

    list($article_html, $attachment_html, $attachment_files) =
            $uniljournal_renderer->display_article($articleinstance, $articleelements, $context, $pdf);

    if ($pdf) {
        $article_html = '<style>' . file_get_contents('pdf_CSS.css') . '</style>' . $article_html;
        if ($count != $numarticles) {
            //            $article_html .= '<br pagebreak="true"/>';
        }

        $pdf_articles[$articleinstance->id] = [$article_html, $attachment_html, $attachment_files, $articleinstance];

        $count++;
    }
    else {
        $articles .= $article_html;
    }
}

if ($pdf) {
    make_cache_directory('tcpdf');

    define('PDF_IMAGE_SIZE', 60);

    //  Create PDF
    $pdf = new mod_uniljournal_mypdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8');
    $pdf->setFontSubsetting(false);
    $pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(true, 20);

    $pdf->setPrintHeader(false); // no headers
    $pdf->setPrintFooter(false); // no footer on cover page

    $pdf->SetFont('times', '', 16);
    $pdf->AddPage(PDF_PAGE_ORIENTATION, PDF_PAGE_FORMAT, true, false);
    $pdf->writeHTML(uniljournal_title_page($cm, $uniljournal, $authorid));
    $pdf->SetFont('times', '', 12);

    foreach ($pdf_articles as $pdf_article) {

        $pdf->AddPage(PDF_PAGE_ORIENTATION, PDF_PAGE_FORMAT, true, false);
        $pdf->setPrintFooter(true); // we want a footer on content pages

        if (count($pdf_article[2]) > 0) {
            // attachments (attached assets)
            $i = 0;
            $j = 0;
            $imgx = PDF_MARGIN_LEFT;
            foreach ($pdf_article[2] as $attachment) {
                if (strpos($attachment['mimetype'], 'image') !== false) {
                    // it's an image: display it
                    $pdf->Image($attachment['url'], $imgx, PDF_MARGIN_TOP,
                            PDF_IMAGE_SIZE * $attachment['width'] / $attachment['height'], PDF_IMAGE_SIZE);
                    $imgx += PDF_IMAGE_SIZE * $attachment['width'] / $attachment['height'] + 10;
                    $j++;
                }
                else {
                    // general attachment type: add as annotation
                    $pdf->Annotation(PDF_MARGIN_LEFT + 10 * $i, 297 - PDF_MARGIN_FOOTER, 2, 4, $attachment['filename'],
                            ['Subtype' => 'FileAttachment', 'Name' => 'Paperclip', 'FS' => $attachment['url']]);
                    $i++;
                }
            }
            if ($j > 0) {
                $pdf->Ln(PDF_IMAGE_SIZE);
            }
        }

        // article itself
        $pdf->Bookmark($pdf_article[3]->title, 0, 0, '', '', [0, 64, 128]);
        $article_contents_html = $pdf_article[0];
        $strip_nodes = ['span' => 'atto_corrections_comment'];
        foreach ($strip_nodes as $strip_node_name => $strip_node_class) {
            $re = '/<' . $strip_node_name . ' class="' . $strip_node_class . '">[^<]*<\/' . $strip_node_name . '>/';
            $article_contents_html = preg_replace($re, '', $article_contents_html);
        }
        $article_contents_html = preg_replace('/<\w+><\/\w+>/', '', $article_contents_html); // strip empty tags
        $strip_tags = ['span'];
        foreach ($strip_tags as $strip_tag) {
            $article_contents_html = preg_replace('/<\/?' . $strip_tag . '[^>]*>/', '', $article_contents_html);
        }
        $pdf->writeHTML($article_contents_html);
    }
    $pdfname = 'articles';
    foreach ($articleinstanceids as $id) {
        $pdfname .= '_' . $id;
    }
    $pdfname .= '_' . time() . '.pdf';

    // Table of contents
    $pdf->endPage();
    $pdf->setPrintFooter(false); // no footer on TOC page
    $pdf->SetFont('times', '', 16);
    $pdf->addTOCPage();
    $pdf->MultiCell(0, 0, get_string('toc', 'mod_uniljournal'), 0, '', 0, 1, '', '', true, 0);
    $pdf->Ln();
    $pdf->SetFont('times', '', 12);
    $pdf->addTOC(2);
    $pdf->endTOCPage();

    $pdf->Output($pdfname, 'D');
}
else {
    $PAGE->set_url('/mod/uniljournal/export_articles.php', ['id' => $cm->id]);
    $PAGE->set_title(get_string('exportarticles', 'mod_uniljournal'));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->set_context($context);

    // Output starts here.
    echo $OUTPUT->header();

    echo $articles;

    // Finish the page.
    echo $OUTPUT->footer();
}
