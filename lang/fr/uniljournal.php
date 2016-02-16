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

$string['modulename'] = 'Journal d\'apprentissage';
$string['modulenameplural'] = 'Journaux d\'apprentissage';
$string['modulename_help'] =
        'Le module Journal d\'apprentissage permet aux étudiants de créer des journaux structurés selon une structure définie à l\'avance par l\'enseignant.';
$string['ujname'] = 'Nom du journal';
$string['ujname_help'] = 'Le nom du journal, tel qu\'il apparaîtra dans le cours Moodle.';
$string['ujsubtitle'] = 'Sous-titre';
$string['ujsubtitle_help'] = 'Le titre de second niveau, tel qu\'il apparaîtra à l\'exportation.';
$string['ujdescription'] = 'Description';
$string['ujdescription_help'] = 'La description du journal.';
$string['ujlogo'] = 'Logo pour page de couverture';
$string['ujlogo_help'] = 'Ce logo sera affiché dans sa taille nominale en première page de la version imprimable.';
$string['ujcomments_allowed'] = 'Activer les commentaires';
$string['ujcomments_allowed_help'] = 'Autoriser l\'étudiant et l\'enseignant à commenter les articles du journal.';
$string['allowedmimegroups'] = 'Types de fichiers joints permis';
$string['allowedmimegroupsdescription'] =
        'Permet de restreindre les groupes de type MIME de fichiers utilisables dans les Journaux d\'apprentissage.';
$string['mimegroup_any'] = 'Tous les fichiers';
$string['mimegroup_audio'] = 'Fichiers audio';
$string['mimegroup_image'] = 'Images';
$string['pluginadministration'] = 'Administration des Journaux d\'apprentissage';
$string['pluginname'] = 'Journal d\'apprentissage';
$string['managetemplates'] = 'Gérer les modèles';
$string['articletemplates'] = 'Modèles d\'articles';
$string['addtemplate'] = 'Ajouter un modèle';
$string['template'] = 'Modèle';
$string['templatelower'] = 'modèle';
$string['template_title'] = 'Titre du modèle';
$string['template_title_help'] = 'Le titre du modèle, tel qu\'il sera affiché à l\'utilisateur.';
$string['template_freetitle'] = 'L\'étudiant peut choisir un titre librement';
$string['template_freetitle_help'] =
        'Autoriser l\'étudiant à définir un titre d\'article lui-même, ou le titre doit-il être défini par la banque de thèmes.';
$string['template_instructions'] = 'Instructions';
$string['template_instructions_help'] = 'Les instructions liées au modèle.';
$string['template_themebank'] = 'Utiliser une banque de thèmes pour ce modèle.';
$string['template_instructions_help'] =
        'Permet de lier une banque de thèmes, permettant ainsi à l\'étudiant de choisir un thème pour son article.';
$string['template_element'] = 'Elément du modèle';
$string['template_nothemebank'] = 'Ne pas utiliser une banque de thèmes';
$string['element_subtitle'] = 'Sous-titre';
$string['article_theme'] = 'Choisir un thème';
$string['article_theme_mandatory'] = 'Vous devez choisir un thème !';
$string['article_instructions'] = 'Instructions de rédaction';
$string['article_theme_unpicked'] = '-';
$string['article_title'] = 'Titre de l\'article';
$string['element_text'] = 'Texte';
$string['element_image'] = 'Image';
$string['element_subtitle_desc'] = '{$a} sous-titre(s)';
$string['element_text_desc'] = '{$a} texte(s)';
$string['element_textonly'] = 'Texte';
$string['element_textonly_desc'] = '{$a} texte(s)';
$string['element_attachment_any'] = 'Tout type de fichier';
$string['element_attachment_any_desc'] = '{$a} fichier(s)';
$string['element_attachment_archive'] = 'Archive';
$string['element_attachment_archive_desc'] = '{$a} archive(s)';
$string['element_attachment_audio'] = 'Fichier audio';
$string['element_attachment_audio_desc'] = '{$a} fichier(s) audio';
$string['element_attachment_document'] = 'Document';
$string['element_attachment_document_desc'] = '{$a} document(s)';
$string['element_attachment_image'] = 'Image';
$string['element_attachment_image_desc'] = '{$a} image(s)';
$string['element_attachment_presentation'] = 'Présentation';
$string['element_attachment_presentation_desc'] = '{$a} présentation(s)';
$string['element_attachment_spreadsheet'] = 'Feuille de calcul';
$string['element_attachment_spreadsheet_desc'] = '{$a} feuille(s) de calcul';
$string['element_attachment_video'] = 'Vidéo';
$string['element_attachment_video_desc'] = '{$a} vidéo(s)';
$string['attachments'] = 'Pièces jointes';
$string['myarticles'] = 'Mes articles';
$string['addarticle'] = 'Ajouter un nouvel article …';
$string['addarticletempl'] = 'Ajouter un nouvel article en utilisant le modèle \'{$a}\'';
$string['writearticletempl'] = 'Ecrire un article en utilisant le modèle \'{$a}\'';
$string['notemplates'] = 'Aucun modèle disponible ! Veuillez créer au moins un modèle.';
$string['articlelower'] = 'article';
$string['articles_num'] = 'Nombre d\'articles';
$string['articles_uncorrected'] = 'Articles non corrigés';
// Capabilities
$string['uniljournal:addinstance'] = 'Ajouter une instance';
$string['uniljournal:addcomment'] = 'Ajouter un commentaire';
$string['uniljournal:deletecomment'] = 'Supprimer un commentaire';
$string['uniljournal:managetemplates'] = 'Gérer les modèles';
$string['uniljournal:managethemes'] = 'Gérer les thèmes';
$string['uniljournal:view'] = 'Voir les articles';
$string['uniljournal:createarticle'] = 'Créer des articles';
$string['uniljournal:deletearticle'] = 'Supprimer des articles';
$string['uniljournal:editallarticles'] = 'Modifier n\'importe quel article';
$string['uniljournal:viewallarticles'] = 'Voir tous les articles d\'une instance de module';
$string['articlelower'] = 'article';
$string['managethemebanks_hint'] =
        'Information : vous avez la possibilité d\'utiliser des banques de thèmes, puis de les lier à un modèle d\'article. Pour ce faire, utilisez l\'option "Gérer les banques de thèmes" du menu Administration.';
$string['managethemebanks'] = 'Gérer les banques de thèmes';
$string['managethemebanks_help'] =
        'Une fois une banque de thèmes créée, cliquez sur son titre pour y ajouter des thèmes.';
$string['addthemebank'] = 'Ajouter une banque de thèmes';
$string['themebank'] = 'Banque de thèmes';
$string['themebanklower'] = 'banque de thèmes';
$string['themebank_title'] = 'Titre de la banque de thèmes';
$string['themebank_title_help'] = 'Le titre de la banque de thèmes, tel qu\'il apparaîtra à la sélection.';
$string['themebank_contextid'] = 'Disponibilité';
$string['module_context'] = 'Ce module uniquement';
$string['course_context'] = 'Tout le cours';
$string['category_context'] = 'Tous les cours de cette catégorie';
$string['user_context'] = 'Cet utilisateur uniquement';
$string['system_context'] = 'Partout';
$string['themebank_contextid_help'] = 'Cette banque de thèmes sera disponible dans ces contextes.';
$string['managethemes'] = 'Banque de thèmes "{$a->themebankname}"';
$string['addtheme'] = 'Ajouter un thème';
$string['theme'] = 'Thème';
$string['themelower'] = 'thème';
$string['theme_title'] = 'Titre du thème';
$string['theme_title_help'] = 'Le titre du thème, tel qu\'il apparaîtra à l\'utilisateur.';
$string['theme_instructions'] = 'Instructions';
$string['theme_instructions_help'] =
        'Les instructions de rédaction, qui seront affichées à l\'utilisateur sélectionnant ce thème.';
$string['n_articleinstances'] = '{$a} articles';
$string['articlestate'] = 'Etat';
$string['revisions'] = 'Révisions';
$string['corrected_status'] = 'Corrigé';
$string['no_comment'] = 'Aucun commentaire';
$string['sendcomment'] = 'Envoyer';
$string['comment_deletion_confirmation'] = 'Etes-vous sûr de vouloir supprimer ce commentaire ?';
$string['version_previous'] = 'Version précédente';
$string['version_next'] = 'Version suivante';
$string['version'] = 'version';
$string['student_uncorrected_articles'] = '{$a->students} étudiant(s), {$a->uncorrected} article(s) non corrigé(s)';
$string['uncorrected_articles'] = '{$a->uncorrected} article(s) non corrigé(s)';
$string['exportarticles'] = 'Exporter les articles';
$string['compare'] = 'Comparer';
$string['author'] = 'Auteur';
$string['all_students'] = 'Tous les étudiants';
$string['logo'] = 'Logo de l\'activité';
$string['elementinstance'] = "Eléments déposés";
$string['themeinstructions'] = "Instructions du thème déposées";
$string['error_notitle'] = 'Erreur : aucun titre n\'a été défini';
$string['preview'] = "Prévisualiser";
$string['atto_writinginstructions'] =
        '<strong>Information :</strong> vous pouvez utiliser les outils de correction dans l\'éditeur ci-dessous ; pour faire apparaître ces outils, cliquez sur le premier bouton de la palette d\'outils.';
$string['themescount'] = 'Nombre de thèmes';
$string['displayarticles'] = 'Afficher les articles';
$string['exportpdf'] = 'Exporter en PDF';

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

$string['restoredon'] = ' (restauré le {$a->mday}/{$a->mon}/{$a->year})';

$string['to_correct'] = 'Terminé';
$string['corrected'] = 'A améliorer';
$string['accepted'] = 'Accepté';

//Mails
$string['article_corrected_subject'] = 'Article corrigé';
$string['article_corrected_message'] = 'Cher/Chère {$a->user_name},


Votre article ({$a->article}) a été corrigé.

Vous pouvez examiner les corrections ici : {$a->link}
';
$string['article_corrected_html_message'] = '<p>Cher/Chère {$a->user_name},</p>
<p>Votre article ({$a->article}) a été corrigé.</p>
<p>Vous pouvez examiner les corrections ici : <a href="{$a->link}">{$a->link}</a></p>
';
$string['article_accepted_subject'] = 'Article accepté';
$string['article_accepted_message'] = 'Cher/Chère {$a->user_name},


Votre article ({$a->article}) a été accepté par l\'enseifgnant-e.

Vous pouvez le consulter ici : {$a->link}
';
$string['article_accepted_html_message'] = '<p>Cher/Chère {$a->user_name},</p>
<p>Votre article ({$a->article}) a été accepté par l\'enseignant-e.</p>
<p>Vous pouvez le consulter ici : <a href="{$a->link}">{$a->link}</a></p>
';
$string['messageprovider:correction'] = 'Notification de correction';
$string['messageprovider:accepted'] = 'Notification d\'article accepté';

$string['article_tocorrect_subject'] = 'Article prêt à être corrigé';
$string['article_tocorrect_message'] = 'Cher/Chère {$a->user_name},


L\'article "{$a->article}" de {$a->author_name} est prêt à être corrigé.

Vous pouvez le corriger ici : {$a->link}
';
$string['article_tocorrect_html_message'] = '<p>Cher/Chère {$a->user_name},</p>
<p>L\'article "{$a->article}" de {$a->author_name} est prêt à être corrigé.</p>
<p>Vous pouvez le corriger ici : <a href="{$a->link}">{$a->link}</a></p>
';
$string['messageprovider:tocorrect'] = 'Notification d\'article à corriger';

$string['toc'] = 'Table des matières';
$string['offlineattachments'] = 'Pièces jointes';
$string['id_missing'] = 'You must specify a course_module ID or an instance ID';
$string['cannotmanagethemebank'] = 'Vous ne pouvez pas modifier cette banque de thèmes';
$string['mustexist'] = 'Must exist!';
$string['userdoesnotexist'] = 'This user does not exist';
$string['mustbeteacher'] = 'Vous n\'avez pas les droits nécessaires pour une telle action';
$string['editarticle'] = 'Modifier la version actuelle de cet article';
$string['editarticle_teacher'] = 'Corriger la version actuelle de cet article';
$string['canteditarticle'] = 'Vous ne pouvez pas modifier cet article';

