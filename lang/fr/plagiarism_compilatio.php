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
 *
 * @package   plagiarism_compilatio
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Plugin Compilatio de détection de plagiat';
$string['studentdisclosuredefault'] ='L\'ensemble des fichiers chargés a été envoyé au service de détection de plagiat de Compilatio';
$string['studentdisclosure'] = 'Divulgation aux étudiants';
$string['studentdisclosure_help'] = 'Ce texte sera affiché à tous les étudiants sur la page de téléchargement de fichier.';
$string['compilatioexplain'] = 'Pour obtenir des informations complémentaires sur ce plugin, voir : <a href="http://compilatio.net" target="_blank">http://compilatio.net</a>';
$string['compilatio'] = 'Plugin de détection de plagiat Compilatio';
$string['compilatio_api'] = 'Adresse de l\'API Compilatio';
$string['compilatio_api_help'] = 'Il s\'agit de l\'adresse de l\'API Compilatio';
$string['compilatio_password'] = 'Code établissement';
$string['compilatio_password_help'] = 'Code établissement fourni par Compilatio pour accéder à l\'API';
$string['usecompilatio'] ='Activer Compilatio';
$string['savedconfigsuccess'] = 'Paramètres de détection de plagiat sauvegardés';
$string['savedconfigfailed'] = 'Une intégration incorrecte adresse/établissement code a été saisie, Compilatio est désactivé, merci de réessayez de nouveau.';
$string['compilatio_show_student_score'] = 'Afficher le score de similarité à l\'étudiant';
$string['compilatio_show_student_score_help'] = 'Le score de similarité est le pourcentage de la soumission qui a été jumelé avec un autre contenu.';
$string['compilatio_show_student_report'] = 'Afficher le rapport d\'analyse de similarité à l\'étudiant';
$string['compilatio_show_student_report_help'] = 'Le rapport de similitude donne une ventilation sur les parties de la présentation qui ont été plagiés et l\'emplacement où Compilatio a trouvé ce contenu la première fois';
$string['compilatio_draft_submit'] = 'Quand le dossier doit être soumis à Compilatio';
$string['showwhenclosed'] = 'Quand il n\'y a plus d\'activité';
$string['submitondraft'] = 'Soumettre un fichier quand le premier est chargé';
$string['submitonfinal'] = 'Soumettre un fichier lorsqu\'un étudiant l\'envoie pour l\'analyse';
$string['defaultupdated'] = 'Les valeurs par défaut ont été mises à jour';
$string['defaultsdesc'] = 'Les paramètres suivants sont les paramètres par défaut définis lors de l\'activation Compilatio au sein d\'un module d\'activité';
$string['compilatiodefaults'] = 'Compilatio oar défaut';
$string['similarity'] = 'similarité';
$string['processing'] = 'Le fichier a été soumis à Compilatio et attend maintenant que l\'analyse soit disponible';
$string['pending'] = 'Le fichier est en attente de soumission à Compilatio';
$string['previouslysubmitted'] = 'Auparavant soumis comme';
$string['report'] = 'rapport';
$string['unknownwarning'] = 'Une erreur s\'est produite lors de l\'envoi du fichier à Compilatio';
$string['unsupportedfiletype'] = 'Ce type de fichier n\'est pas supporté par Compilatio';
$string['toolarge'] = 'Le fichier est trop volumineux pour être traité par Compilatio';
$string['optout'] = 'Opt-out';
$string['compilatio_studentemail'] = 'Envoyer un mail à l\'étudiant';
$string['compilatio_studentemail_help'] = 'Ceci enverra un e-mail à l\'élève quand un fichier a été traitée pour leur faire savoir que le rapport est disponible.';
$string['studentemailsubject'] = 'Le fichier est en cours de traitement chez Compilatio';
$string['studentemailcontent'] = 'Le fichier que vous avez soumis à {$a->modulename} dans {$a->coursename} a été traitée par l\'outil de détection de plagiat Compilatio
{$a->modulelink}';

$string['filereset'] = 'Un fichier a été remis à zéro pour re-soumission à Compilatio';
$string['analysistype'] = 'Type d\'analyse';
$string['analysistype_help'] = '<p>Vous disposez de trois options possibles :</p>
<ul>
<li><strong> Instantanée:</strong> Le document est envoyé à Compilatio et analysé immédiatement </li>
<li><strong> Manuel:</strong> Le document est envoyé à Compilatio, mais l\'enseignant doit déclencher manuellement les analyses des documents</li>
<li><strong> Programmée:</strong> Le document est envoyé à Compilatio et analysé, à l\'heure/date choisie(s) </i>
<p>Pour permettre à tous les documents qui doivent être comparés les uns avec les autres, choisir le mode manuel et attendre jusqu\'à ce que tous les travaux soient soumis par les étudiants, puis déclencher l\'analyse</p>
';
$string['analysistypeauto'] = 'Instantanée';
$string['analysistypemanual'] = 'Manuelle';
$string['analysistypeprog'] = 'Programmée';
$string['enabledandworking'] = 'Le plugin Compilatio est actif et fonctionnel.';
$string['usedcredits'] = '<strong>Vous avez utilisé {$a->used} credit(s) sur {$a->credits} et il vous reste {$a->remaining} credit(s)</strong>';
$string['startanalysis'] = 'Démarrer l\'analyse';
$string['failedanalysis'] = 'Compilatio n\'a pas réussi à analyser votre document : ';
