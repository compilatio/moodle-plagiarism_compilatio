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
$string['pluginname'] = 'Compilatio - Plugin de détection de plagiat';
$string['studentdisclosuredefault'] = 'L\'ensemble des fichiers envoyés seront soumis au service de détection de plagiat de Compilatio';
$string['students_disclosure'] = 'Message de prévention pour les étudiants';
$string['students_disclosure_help'] = 'Ce texte sera affiché à tous les étudiants sur la page de téléchargement de fichier.';
$string['compilatioexplain'] = 'Pour obtenir des informations complémentaires sur ce plugin, voir : <a href="http://compilatio.net" target="_blank">compilatio.net</a>';
$string['compilatio'] = 'Plugin de détection de plagiat Compilatio';
$string['compilatioapi'] = 'Adresse de l\'API';
$string['compilatioapi_help'] = 'Il s\'agit de l\'adresse de l\'API Compilatio';
$string['compilatiopassword'] = 'Clé API';
$string['compilatiopassword_help'] = 'Code personnel fourni par Compilatio pour accéder à l\'API';
$string['use_compilatio'] = 'Détecter des similitudes avec Compilatio';
$string['activate_compilatio'] = 'Activer le plug-in Compilatio';
$string['savedconfigsuccess'] = 'Les paramètres ont bien été sauvegardés';
$string['compilatio_display_student_score_help'] = 'Le pourcentage de similitudes indique la quantité de texte dans le document qui a été retrouvée dans d’autres documents.';
$string['compilatio_display_student_score'] = 'Rendre le pourcentage de similitudes visible par les étudiants';
$string['compilatio_display_student_report'] = 'Permettre à l\'étudiant de visualiser le rapport d\'analyse';
$string['compilatio_display_student_report_help'] = 'Le rapport d’analyse d’un document présente les passages similaires avec les sources détectées et leurs pourcentages de similitudes.';
$string['compilatio_draft_submit'] = 'Quand le fichier doit être soumis à Compilatio';
$string['showwhenclosed'] = 'Quand l\'activité est fermée';
$string['submitondraft'] = 'Soumettre un fichier quand le premier est chargé';
$string['submitonfinal'] = 'Soumettre un fichier lorsqu\'un étudiant l\'envoie pour l\'analyse';
$string['defaultupdated'] = 'Les valeurs par défaut ont été mises à jour';
$string['defaults_desc'] = 'Les paramètres suivants sont utilisés comme valeurs par défaut dans les activités de Moodle intégrant Compilatio.';
$string['compilatiodefaults'] = 'Valeurs par défaut pour Compilatio.';
$string['processing_doc'] = 'Le fichier est en cours d\'analyse par Compilatio.';
$string['pending'] = 'Le fichier est en attente de soumission à Compilatio.';
$string['previouslysubmitted'] = 'Auparavant soumis comme';
$string['unknownwarning'] = 'Une erreur s\'est produite lors de l\'envoi du fichier à Compilatio';
$string['unsupportedfiletype'] = 'Ce type de fichier n\'est pas supporté par Compilatio';
$string['toolarge'] = 'Le fichier est trop volumineux pour être traité par Compilatio';
$string['compilatio_studentemail'] = 'Envoyer un mail à l\'étudiant';
$string['compilatio_studentemail_help'] = 'Ceci enverra un e-mail à l\'élève quand un fichier a été traité pour leur faire savoir que le rapport est disponible.';
$string['studentemailsubject'] = 'Le fichier a été traité par Compilatio';
$string['studentemailcontent'] = 'Le fichier que vous avez soumis à {$a->modulename} dans {$a->coursename} a été traité par l\'outil de détection de plagiat Compilatio
{$a->modulelink}';

$string['filereset'] = 'Un fichier a été remis à zéro pour re-soumission à Compilatio';
$string['analysis_type'] = 'Lancement des analyses';

$string['analysis_type_help'] = '<p>Vous disposez de trois options :</p>
<ul>
	<li><strong> Immédiat:</strong> Le document est envoyé à Compilatio et analysé immédiatement </li>
	<li><strong> Manuel:</strong> Le document est envoyé à Compilatio, mais l\'enseignant doit déclencher manuellement les analyses des documents</li>
	<li><strong> Programmé:</strong> Le document est envoyé à Compilatio puis analysé à l\'heure/date choisie(s) </li>
</ul>
<p>Pour que tous les documents soient comparés entre eux lors des analyses, déclenchez les analyses seulement lorsque tous les documents sont présents dans le devoir.</p>';

$string['analysistype_direct'] = 'Immédiat';
$string['analysistype_manual'] = 'Manuel';
$string['analysistype_prog'] = 'Programmé';
$string['enabledandworking'] = 'Le plugin Compilatio est actif et fonctionnel.';
$string['subscription_state'] = '<strong>Votre abonnement Compilatio.net est valable jusqu\'à la fin du mois de {$a->end_date}. Ce mois-ci, vous avez analysé l\'équivalent de {$a->used} document(s) de moins de 5.000 mots.</strong>';
$string['startanalysis'] = 'Démarrer l\'analyse';
$string['failedanalysis'] = 'Compilatio n\'a pas réussi à analyser votre document : ';
$string['unextractablefile'] = 'Votre document ne contient pas assez de mots, ou le texte n’a pas pu être extrait correctement.';


//Auto diagnostic
$string["auto_diagnosis_title"] = "Auto-diagnostic";
//API key
$string["api_key_valid"] = "La clé API enregistrée est valide.";
$string["api_key_not_tested"] = "La clé API n'a pas pû être vérifiée car la connexion au service Compilatio.net à échouée.";
$string["api_key_not_valid"] = "La clé API enregistrée est invalide. Elle est spécifique à la plateforme utilisée. Vous pouvez en obtenir une en contactant <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.";

//CRON
$string['cron_check_never_called'] = "CRON n'a pas été exécuté depuis l'activation du plugin. Il est possible qu'il soit mal configuré.";
$string['cron_check'] = 'CRON a été exécuté le {$a} pour la dernière fois.';

$string['cron_check_not_ok'] = 'Il n\'a pas été exécuté depuis plus d\'une heure.';

$string['cron_frequency'] = 'Il semblerait qu\'il soit exécuté toutes les {$a} minutes.';
$string['cron_recommandation'] = 'Nous recommandons d\'utiliser un délai inférieur à 15 minutes entre chaque exécution de CRON.';



//Connect to webservice
$string['webservice_ok'] = "Le serveur est capable de contacter le webservice.";
$string['webservice_not_ok'] = "Le webservice n'a pas pu être contacté. Il est possible que votre pare-feu bloque la connexion.";

//Plugin enabled
$string['plugin_enabled'] = "Le plugin est activé pour la plateforme Moodle.";
$string['plugin_disabled'] = "Le plugin n'est pas activé pour la plateforme Moodle.";

//Plugin enabled for "assign"
$string['plugin_enabled_assign'] = "Le plugin est activé pour les devoirs.";
$string['plugin_disabled_assign'] = "Le plugin n'est pas activé pour les devoirs.";

$string['plugin_enabled_workshop'] = "Le plugin est activé pour les ateliers.";
$string['plugin_disabled_workshop'] = "Le plugin n'est pas activé pour les ateliers.";

$string['plugin_enabled_forum'] = "Le plugin est activé pour les forums.";
$string['plugin_disabled_forum'] = "Le plugin n'est pas activé pour les forums.";


$string['compilatioenableplugin'] = 'Activer Compilatio pour {$a}';

$string['programmed_analysis_future'] = 'Les documents seront analysés par Compilatio le {$a}.';
$string['programmed_analysis_past'] = 'Les documents ont été soumis pour analyse à Compilatio le {$a}.';



$string['compilatio:enable'] = 'Autoriser l\'enseignant à activer/désactiver Compilatio au sein d\'une activité';
$string['compilatio:resetfile'] = 'Autoriser l\'enseignant à soumettre à nouveau le fichier à Compilatio après une erreur';
$string['compilatio:triggeranalysis'] = 'Autoriser l\'enseignant à déclencher manuellement l\'analyse ';
$string['compilatio:viewreport'] = 'Autoriser l\'enseignant à consulter le rapport complet depuis Compilatio';



$string['webservice_unreachable_title'] = "Indisponibilité Compilatio.net";
$string['webservice_unreachable_content'] = "Le service Compilatio.net est actuellement indisponible. Veuillez nous excuser pour la gêne occasionnée.";




$string['saved_config_failed'] = '<strong>La combinaison adresse - clé API est incorrecte. Le plugin est désactivé, merci de réessayer.<br/>
								La page d\'<a href="autodiagnosis.php">auto-diagnostic</a> peut vous aider à configurer ce plugin.</strong><br/> 
								Erreur : ';

$string['startallcompilatioanalysis'] = "Analyser tous les documents";
$string['numeric_threshold'] = "Le seuil doit être numérique.";


$string['green_threshold'] = "Vert jusqu'à";
$string['orange_threshold'] = "Orange jusqu'à";
$string['red_threshold'] = "rouge au delà";
$string['similarity_percent'] = "% de similitudes";

$string['thresholds_settings'] = "Réglage des seuils d'affichage des taux de similitudes :";
$string['thresholds_description'] = "Indiquez les seuils que vous souhaitez utiliser, afin de faciliter le repérage des rapports d’analyse (% de similitudes) : ";
//TODO


$string['similarities_disclaimer'] = "Vous pouvez analyser les similitudes présentes dans les documents de ce devoir à l’aide du logiciel <a href='http://compilatio.net' target='_blank'>Compilatio</a>.<br/>
	Attention, des similitudes mesurées lors d’une analyse ne révèlent pas nécessairement un plagiat. 
	Le rapport d’analyse vous aide à comprendre si les similitudes correspondent à des emprunts et citations convenablement identifiés ou à des plagiats.";
$string['progress'] = "Progression :";
$string['results'] = "Résultats :";
$string['errors'] = "Erreurs :";

$string['documents_analyzed'] = '{$a->countAnalyzed} document(s) sur {$a->documentsCount} ont été analysés.';

$string['documents_analyzing'] = '{$a} document(s) en cours d\'analyse.';
$string['documents_in_queue'] = '{$a} document(s) en attente d\'analyse.';

$string['average_similarities'] = 'Le taux de similitudes moyen pour ce devoir est de {$a}%.';


$string['documents_analyzed_lower_green'] = '{$a->documentsUnderGreenThreshold} document(s) inférieur(s) à {$a->greenThreshold}%.';
$string['documents_analyzed_between_thresholds'] = '{$a->documentsBetweenThresholds} document(s) entre {$a->greenThreshold}% et {$a->redThreshold}%.';
$string['documents_analyzed_higher_red'] = '{$a->documentsAboveRedThreshold} document(s) supérieur(s) à {$a->redThreshold}%.';


$string['unsupported_files'] = 'Le(s) fichier(s) suivant(s) n\'ont pas pu être analysés par Compilatio car leur format n\'est pas supporté :';
$string['unextractable_files'] = 'Le(s) fichier(s) suivant(s) n\'ont pas pu être analysés par Compilatio. Ils ne contenaient pas assez de mots ou leur contenu n\'a pas pu être extrait correctement :';


$string['no_document_available_for_analysis'] = "Aucun document n'était disponible pour analyse.";

$string["analysis_started"] = '{$a} analyse(s) démandée(s).';
$string["start_analysis_title"] = 'Démarrage manuel des analyses';

$string["not_analyzed"] = "Les documents suivants n'ont pas pu être analysés :";


$string["account_expire_soon_title"] = "Fin d'abonnement Compilatio.net";
$string["account_expire_soon_content"] = "Vous disposez du service Compilatio au sein de votre plateforme jusqu'à la fin du mois. Si l'abonnement n'est pas reconduit, vous ne disposerez plus de Compilatio passé cette date.";


$string["news_update"] = "Mise à jour Compilatio.net";
$string["news_incident"] = "Incident Compilatio.net";
$string["news_maintenance"] = "Maintenance Compilatio.net";
$string["news_analysis_perturbated"] = "Analyses Compilatio.net perturbées";

$string["updatecompilatioresults"] = "Rafraîchir les informations";

$string["display_stats"] = "Afficher les statistiques de ce devoir";


$string["analysis_completed"] = 'Analyse terminée: {$a}% de similitudes.';


$string["compilatio_help_assign"] = "Obtenir de l'aide sur le plugin Compilatio";



$string["display_notifications"] = "Afficher les notifications";



//CSV
$string["firstname"] = "Prénom";
$string["lastname"] = "Nom";
$string["filename"] = "Nom du fichier";
$string["similarities"] = "Taux de similitudes";
$string["unextractable"] = "Le contenu de ce document n'a pas pu être extrait";
$string["unsupported"] = "Document non supporté";
$string["analysing"] = "Document en cours d'analyse";
$string['timesubmitted'] = "Soumis à Compilatio le";



$string["not_analyzed_unextractable"] = '{$a} document(s) n\'ont pas été analysés car ils ne contenaient pas assez de texte.';
$string["not_analyzed_unsupported"] = '{$a} document(s) n\'ont pas été analysés car leur format n\'est pas supporté.';


$string['analysis_date'] = "Date d'analyse (lancement programmé uniquement)";



$string['export_csv'] = 'Exporter les données de ce devoir au format CSV';


$string['hide_area'] = 'Masquer les informations Compilatio';


$string['tabs_title_help'] = 'Aide';
$string['tabs_title_stats'] = 'Statistiques';
$string['tabs_title_notifications'] = 'Notifications';



$string['queued'] = 'Le document est en attente d\'analyse et va bientôt être traité par Compilatio';


$string['no_documents_available'] = 'Aucun document n\'est disponible pour analyse dans ce devoir.';



$string['manual_analysis'] = "L'analyse de ce document doit être déclenchée manuellement";



$string['updated_analysis'] = 'Les résultats de l\'analyse Compilatio ont été mis à jour.';

$string['disclaimer_data'] = 'En activant Compilatio, vous acceptez que des informations concernant la configuration de votre plateforme Moodle soient collectées afin de faciliter le support et la maintenance du service.';

$string['reset'] = 'Relancer';
$string['error'] = 'Erreur';
$string['analyze'] = 'Analyser';
$string['queue'] = 'Attente';
$string['analyzing'] = 'Analyse';



$string['compilatioenableplugin'] = 'Activer Compilatio pour {$a}';
$string['compilatio_enable_mod_assign'] = 'Activer Compilatio pour les devoirs (assign)';
$string['compilatio_enable_mod_workshop'] = 'Activer Compilatio pour les ateliers (workshop)';
$string['compilatio_enable_mod_forum'] = 'Activer Compilatio pour les forums';


$string['planned'] = "Planifié";



$string['immediately'] = "Immédiatement";



$string['enable_javascript'] = "Veuillez activer Javascript pour profiter de toutes les fonctionnalités du plugin Compilatio. <br/> Voici les <a href='http://www.enable-javascript.com/fr/' target='_blank'>
 instructions pour activer JavaScript dans votre navigateur Web</a>.";




$string["manual_send_confirmation"] = '{$a} fichier(s) soumis à Compilatio.';
$string["unsent_documents"] = 'Document(s) non-soumis';
$string["unsent_documents_content"] = 'Attention, ce devoir contient un (des) document(s) non soumis à Compilatio.';


$string["statistics_title"] = 'Statistiques';
$string["no_statistics_yet"] = 'Aucunes statistiques ne sont disponibles pour le moment.';

$string["minimum"] = 'Minimum';
$string["maximum"] = 'Maximum';
$string["average"] = 'Moyenne';
$string["documents_number"] = 'Documents analysés';


$string["export_raw_csv"] = 'Cliquez-ici pour exporter les données brutes au format CSV';
$string["export_global_csv"] = 'Cliquez-ici pour exporter ces données au format CSV';
$string["global_statistics"] = 'Statistiques globales';
$string["assign_statistics"] = 'Statistiques des devoirs';
$string["similarities"] = 'Similitudes';
$string["context"] = 'Contexte';
$string["pending_status"] = 'Attente';



$string["allow_teachers_to_show_reports"] = "Permettre aux enseignants de mettre les rapports d'analyse à disposition des étudiants";
$string["admin_disabled_reports"] = "L'administrateur a désactivé l'affichage des rapports de similitudes aux étudiants.";
$string["teacher"] = "Enseignant";
$string["loading"] = "Chargement en cours, veuillez patienter...";



/* HELP */

$string['help_compilatio_settings_title'] = 'Quels réglages choisir dans les paramètres Compilatio d’une activité?';
$string['help_compilatio_settings_content'] = 'Trois types d’analyse sont disponibles avec le plugin Compilatio :
<ul>
<li>
Immédiat : <br/>
Chaque document est envoyé à Compilatio et analysé dès le dépôt par l’étudiant. 
Recommandé si vous souhaitez avoir vos résultats au plus vite et qu’il n’est pas nécessaire que tous les documents de l’activité soient comparés mutuellement.
</li>
<li>
Programmé : <br/>
Choisissez une date de démarrage des analyses Compilatio postérieure à la date limite de rendu par les étudiants. 
Recommandé si vous souhaitez comparer tous les documents de votre activité entre eux.
</li>
<li>
Manuel : <br/>
Les documents de votre activité ne sont analysés que si vous démarrez vous-même les analyses.
Pour lancer l’analyse d’un document, cliquez sur le bouton “analyser” de chaque document.
Le bouton “Analyser tous les documents” vous permet de lancer l’analyse de tous les documents présents dans un devoir.
</li>
</ul>
';



$string['help_compilatio_thresholds_title'] = 'Comment modifier la couleur du résultat d’analyse des documents ?';
$string['help_compilatio_thresholds_content'] = 'Les couleurs des résultats peuvent être définies dans la configuration de chaque devoir, dans la rubrique “Plugin de détection de plagiat Compilatio”.<br/>
 Il est possible de choisir les seuils qui déterminent la couleur d’affichage des taux de similitudes.';

$string['help_compilatio_format_title'] = 'Quels sont les formats de documents supportés?';
$string['help_compilatio_format_content'] = "Compilatio.net prend en charge la plupart des formats utilisés en bureautique et sur Internet.
Les formats suivants sont acceptés :
<ul>
	<li>
		Texte '.txt'
	</li>
	<li>
		Adobe Acrobat '.pdf'
	</li>
	<li>
		Texte enrichi '.rtf'
	</li>
	<li>
		Traitement de texte '.doc', '.docx', '.odt'
	</li>
	<li>
		Tableur '.xls ', '.xlsx'
	</li>
	<li>
		Diaporamas '.ppt ', '.pptx'
	</li>
	<li>
		Fichiers '.html'
	</li>
</ul>";

$string['help_compilatio_languages_title'] = 'Quelles sont les langues supportées?';
$string['help_compilatio_languages_content'] = "Les analyses de similitudes peuvent être effectuées avec plus de 40 langues (dont toutes les langues latines).<br/>
Le chinois, le japonais, l’arabe et l’alphabet cyrillique ne sont pas encore supportés.";

$string['admin_help_compilatio_api_title'] = 'Comment obtenir une clé API?';
$string['admin_help_compilatio_api_content'] = "Ce plugin nécessite un abonnement aux services Compilatio.net pour fonctionner. <br/>
Contactez votre interlocuteur commercial, ou faites une demande de clé API à l’adresse <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.";

$string['compilatio_faq'] = "<a target='_blank' href='https://www.compilatio.net/faq/'>Questions fréquemment posées - Compilatio.net</a>";


/* END HELP */


$string['get_scores'] = "Récupère les taux de similitudes depuis Compilatio.net";
$string['send_files'] = "Envoie les fichiers à Compilatio.net pour détection de plagiat";
$string['update_meta'] = "Exécute les tâches planifiées par Compilatio.net";
$string['trigger_timed_analyses'] = "Déclenche les analyses de plagiat programmées";