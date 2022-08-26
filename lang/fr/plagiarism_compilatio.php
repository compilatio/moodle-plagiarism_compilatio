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
 * plagiarism_compilatio.php - Contains french Plagiarism plugin translation.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Compilatio - Plugin de détection de plagiat';
$string['studentdisclosuredefault'] = 'L\'ensemble des fichiers envoyés seront soumis au service de détection de similitudes de Compilatio';
$string['students_disclosure'] = 'Message de prévention pour les étudiants';
$string['students_disclosure_help'] = 'Ce texte sera affiché à tous les étudiants sur la page de téléchargement de fichier.';
$string['compilatioexplain'] = 'Pour obtenir des informations complémentaires sur ce plugin, voir : <a href="http://compilatio.net" target="_blank">compilatio.net</a>';
$string['compilatio'] = 'Plugin de détection de plagiat Compilatio';
// API configuration.
$string['compilatioapi'] = 'Adresse de l\'API';
$string['compilatioapi_help'] = 'Il s\'agit de l\'adresse de l\'API Compilatio';
$string['compilatiopassword'] = 'Clé API';
$string['compilatiopassword_help'] = 'Code personnel fourni par Compilatio pour accéder à l\'API';
$string['compilatiodate'] = "Date d'activation";
$string['compilatiodate_help'] = "Cliquez sur \"Activer\" si vous voulez que cette configuration de l'API s'active automatiquement à une date voulue. Laisser la date vide si vous souhaitez l'activer tout de suite.";
$string['apiconfiguration'] = "Configuration de l'API";
$string['formenabled'] = "Activée";
$string['formurl'] = "Adresse de l'API";
$string['formapikey'] = "Clé API";
$string['formstartdate'] = "Date d'activation";
$string['formcheck'] = "Valide";
$string['formdelete'] = "Supprimer";

$string['migration_title'] = "Migration v4 vers v5";
$string['migration_info'] = "Compilatio met en place une nouvelle plateforme technique v5 pour l'ensemble de ses clients.<br>
    Lorsque vous y serez invités par l'équipe technique, vous devrez exécuter les actions ci-dessous pour finaliser cette migration.";
$string['migration_apikey'] = "Saisir la nouvelle clé API v5";
$string['migration_btn'] = "Lancer la mise à jour des données enregistrées dans Moodle";
$string['migration_inprogress'] = "Mise à jour en cours, veuillez patienter";
$string['migration_success_doc'] = "documents ont été mis à jour";
$string['migration_form_title'] = "Lancer la mise à jour des données enregistrées dans Moodle, pour finaliser la migration de v4 vers v5.";
$string['migration_support'] = "
    <p>Si tous les documents n'ont pas été correctement mis à jour, merci de prendre contact avec l'équipe support de Compilatio en écrivant à support@compilatio.net et en précisant :</p>
    <p><<
        <br>
        <ul>
            <li>Le message d’erreur suivant est apparu : [message]</li>
            <li>[nom_de_l_etablissement]</li>
            <li>[nom de l’instance Moodle, si plusieurs instances utilisées]</li>
            <li>N° de votre clé API v4 : [____]</li>
            <li>N° de clé API v5 : [____]</li>
            <li>Nom de la personne à contacter dans l’établissement :</li>
            <li>Email de la personne à contacter :</li>
            <li>Téléphone de la personne à contacter :</li>
        </ul>
    >><p>";

$string['use_compilatio'] = 'Détecter des similitudes avec Compilatio';
$string['activate_compilatio'] = 'Activer le plug-in Compilatio';
$string['savedconfigsuccess'] = 'Les paramètres ont bien été sauvegardés';
$string['compilatio_display_student_score_help'] = 'Le pourcentage de similitudes indique la quantité de texte dans le document qui a été retrouvée dans d’autres documents.';
$string['compilatio_display_student_score'] = 'Rendre le pourcentage de similitudes visible par les étudiants';
$string['compilatio_display_student_report'] = 'Permettre à l\'étudiant de visualiser le rapport d\'analyse';
$string['compilatio_display_student_report_help'] = 'Le rapport d’analyse d’un document présente les passages similaires avec les sources détectées et leurs pourcentages de similitudes.';
$string['showwhenclosed'] = 'Quand l\'activité est fermée';
$string['defaultupdated'] = 'Les valeurs par défaut ont été mises à jour';
$string['defaults_desc'] = 'Les paramètres suivants sont utilisés comme valeurs par défaut dans les activités de Moodle intégrant Compilatio.';
$string['compilatiodefaults'] = 'Valeurs par défaut pour Compilatio';
$string['processing_doc'] = 'Le fichier est en cours d\'analyse par Compilatio.';
$string['pending'] = 'Le fichier est en attente de soumission à Compilatio.';
$string['previouslysubmitted'] = 'Auparavant soumis comme';
$string['unknownwarning'] = 'Une erreur s\'est produite lors de l\'envoi du fichier à Compilatio';
$string['unsupportedfiletype'] = 'Ce type de fichier n\'est pas supporté par Compilatio';
$string['toolarge'] = 'Le fichier est trop volumineux pour être traité par Compilatio. Taille maximale : {$a->Mo} Mo';
$string['tooshort'] = 'Ce document ne contient pas assez de mots pour être traité par Compilatio. Taille minimale : {$a} mots';
$string['toolong'] = 'Ce document contient trop de mots pour être analysé. Taille maximale : {$a} mots';
$string['failed'] = "L'analyse de ce document n'a pas fonctionné correctement.";
$string['notfound'] = "Ce document n'a pas été trouvé. Veuillez contacter votre administrateur de moodle. Erreur : document non trouvé pour cette clé API.";
$string['compilatio_studentemail'] = 'Envoyer un mail à l\'étudiant';
$string['compilatio_studentemail_help'] = 'Ceci enverra un e-mail à l\'élève quand un fichier a été traité pour leur faire savoir que le rapport est disponible.';
$string['studentemailsubject'] = 'Le fichier a été traité par Compilatio';
$string['studentemailcontent'] = 'Le fichier que vous avez soumis à {$a->modulename} dans {$a->coursename} a été traité par l\'outil de détection de plagiat Compilatio
{$a->modulelink}';
$string['filereset'] = 'Un fichier a été remis à zéro pour re-soumission à Compilatio';
$string['analysis'] = 'Lancement des analyses';
$string['analysis_help'] = "<p>Vous disposez de deux options :
    <ul>
        <li><strong> Manuel :</strong> L'analyse des documents doit être déclenchée manuellement via le bouton “Analyser” de chaque document ou via le bouton “Analyser tous les documents”.</li>
        <li><strong> Programmé :</strong> Tous les documents sont analysés à l'heure/date choisie.</li>
    </ul>
    Pour que tous les documents soient comparés entre eux lors des analyses, déclenchez les analyses seulement lorsque tous les documents sont présents dans le devoir.</p>";
$string['analysis_auto'] = 'Lancement des analyses';
$string['analysis_auto_help'] = "<p>Vous disposez de trois options :
    <ul>
        <li><strong> Manuel :</strong> L'analyse des documents doit être déclenchée manuellement via le bouton “Analyser” de chaque document ou via le bouton “Analyser tous les documents”.</li>
        <li><strong> Programmé :</strong> Tous les documents sont analysés à l'heure/date choisie.</li>
        <li><strong> Immédiat :</strong> Chaque document est analysé dès le dépôt par l’étudiant. Les documents de l’activité ne seront pas comparés entre eux.</li>
    </ul>
    Pour que tous les documents soient comparés entre eux lors des analyses, déclenchez les analyses seulement lorsque tous les documents sont présents dans le devoir.</p>";
$string['analysistype_manual'] = 'Manuel';
$string['analysistype_prog'] = 'Programmé';
$string['analysistype_auto'] = 'Immédiat';
$string['enabledandworking'] = 'Le plugin Compilatio est actif et fonctionnel.';
$string['subscription_state'] = '<strong>Votre abonnement Compilatio.net est valable jusqu\'à la fin du mois de {$a->end_date}. Ce mois-ci, vous avez analysé l\'équivalent de {$a->used} document(s) de moins de 5.000 mots.</strong>';
$string['startanalysis'] = 'Démarrer l\'analyse';
$string['failedanalysis'] = 'Compilatio n\'a pas réussi à analyser votre document : ';
$string['unextractablefile'] = 'Le texte de votre document n’a pas pu être extrait correctement.';
$string['quiz_help'] = 'Seules les questions de type composition dont la réponse contient au moins {$a} mots seront analysés.';
$string['allow_analyses_auto'] = 'Possibilité de lancer immédiatement les analyses';
$string["allow_analyses_auto_help"] = "<p>Cette option permettra aux enseignants d'activer sur une activité le lancement automatique de l'analyse des documents (i.e. immédiatement après leur dépôt).<br>
Notez que dans ce cas : 
<ul>
    <li>Le nombre d'analyses effectuées par votre établissement peut être significativement plus important.</li>
    <li>Les documents des premiers déposants ne seront pas comparés avec les documents des derniers déposants.</li>
</ul>
Pour que tous les documents d'un devoir soient comparés entre eux, il est nécessaire d'utiliser l'analyse “programmée”, en choisissant une date postérieure à la date de fin de rendu des devoirs.</p>";

// Auto diagnostic.
$string["auto_diagnosis_title"] = "Auto-diagnostic";
// API key.
$string["api_key_valid"] = "La clé API enregistrée est valide.";
$string["api_key_not_tested"] = "La clé API n'a pas pû être vérifiée car la connexion au service Compilatio.net à échouée.";
$string["api_key_not_valid"] = "La clé API enregistrée est invalide. Elle est spécifique à la plateforme utilisée. Vous pouvez en obtenir une en contactant <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.";
// CRON.
$string['cron_check_never_called'] = "CRON n'a pas été exécuté depuis l'activation du plugin. Il est possible qu'il soit mal configuré.";
$string['cron_check'] = 'CRON a été exécuté le {$a} pour la dernière fois.';
$string['cron_check_not_ok'] = 'Il n\'a pas été exécuté depuis plus d\'une heure.';
$string['cron_frequency'] = 'Il semblerait qu\'il soit exécuté toutes les {$a} minutes.';
$string['cron_recommandation'] = 'Nous recommandons d\'utiliser un délai inférieur à 15 minutes entre chaque exécution de CRON.';
// Connect to webservice.
$string['webservice_ok'] = "Le serveur est capable de contacter le webservice.";
$string['webservice_not_ok'] = "Le webservice n'a pas pu être contacté. Il est possible que votre pare-feu bloque la connexion.";
// Plugin enabled.
$string['plugin_enabled'] = "Le plugin est activé pour la plateforme Moodle.";
$string['plugin_disabled'] = "Le plugin n'est pas activé pour la plateforme Moodle.";
// Plugin enabled for "assign".
$string['plugin_enabled_assign'] = "Le plugin est activé pour les devoirs.";
$string['plugin_disabled_assign'] = "Le plugin n'est pas activé pour les devoirs.";
// Plugin enabled for "workshop".
$string['plugin_enabled_workshop'] = "Le plugin est activé pour les ateliers.";
$string['plugin_disabled_workshop'] = "Le plugin n'est pas activé pour les ateliers.";
// Plugin enabled for "forum".
$string['plugin_enabled_forum'] = "Le plugin est activé pour les forums.";
$string['plugin_disabled_forum'] = "Le plugin n'est pas activé pour les forums.";
// Plugin enabled for "quiz".
$string['plugin_enabled_quiz'] = "Le plugin est activé pour les tests.";
$string['plugin_disabled_quiz'] = "Le plugin n'est pas activé pour les tests.";
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
// Student analyses.
$string['compi_student_analyses'] = "Permettre aux étudiants d'analyser leurs documents";
$string['compi_student_analyses_help'] = "Ceci permet aux étudiants d'analyser leur fichiers en brouillon avec Compilatio Magister, avant le rendu final à l'enseignant.";
$string['activate_submissiondraft'] = 'Pour permettre aux étudiants d\'analyser leurs brouillons, vous devez activer l\'option <b>{$a}</b> dans la partie';
$string['allow_student_analyses'] = "Possibilité d'activer l'analyse des brouillons par les étudiants.";
$string['allow_student_analyses_help'] = "Cette option permettra aux enseignants d'activer sur une activité l'analyse par les étudiants de leurs documents rendus en mode brouillon avec Compilatio Magister, avant le rendu final à l'enseignant.";
$string['student_analyze'] = "Analyse par l'étudiant";
$string['student_start_analyze'] = "L'analyse peut être lancée par l'étudiant";
$string['student_help'] = "Vous pouvez analyser votre brouillon avec Compilatio Magister, afin de mesurer les similitudes présentes dans le texte de vos fichiers.<br/>
Le contenu de votre brouillon ne sera pas utilisé par Compilatio comme matériel de comparaison pour les futures analyses effectuées.<br/>
Votre enseignant aura cependant accès à ce rapport d'analyse.";
// TODO.
$string['similarities_disclaimer'] = "Vous pouvez analyser les similitudes présentes dans les documents de cette activité à l’aide du logiciel <a href='http://compilatio.net' target='_blank'>Compilatio</a>.<br/>
	Attention, des similitudes mesurées lors d’une analyse ne révèlent pas nécessairement un plagiat.
	Le rapport d’analyse vous aide à comprendre si les similitudes correspondent à des emprunts et citations convenablement identifiés ou à des plagiats.";
$string['progress'] = "Progression :";
$string['results'] = "Résultats :";
$string['errors'] = "Erreurs :";
$string['documents_analyzed'] = '{$a->countAnalyzed} document(s) sur {$a->documentsCount} ont été analysés.';
$string['documents_analyzing'] = '{$a} document(s) en cours d\'analyse.';
$string['documents_in_queue'] = '{$a} document(s) en attente d\'analyse.';
$string['average_similarities'] = 'Le taux de similitudes moyen pour cette activité est de {$a}%.';
$string['documents_analyzed_lower_green'] = '{$a->documentsUnderGreenThreshold} document(s) inférieur(s) à {$a->greenThreshold}%.';
$string['documents_analyzed_between_thresholds'] = '{$a->documentsBetweenThresholds} document(s) entre {$a->greenThreshold}% et {$a->redThreshold}%.';
$string['documents_analyzed_higher_red'] = '{$a->documentsAboveRedThreshold} document(s) supérieur(s) à {$a->redThreshold}%.';
$string['documents_notfound'] = '{$a} document(s) qui n\'ont pas été trouvés.';
$string['documents_failed'] = '{$a} document(s) dont l\'analyse n\'a pas fonctionné correctement.';
$string['unsupported_files'] = 'Les fichiers suivants n\'ont pas pu être analysés par Compilatio car leur format n\'est pas supporté :';
$string['unextractable_files'] = 'Les fichiers suivants n\'ont pas pu être analysés par Compilatio car leur contenu n\'a pas pu être extrait correctement :';
$string['tooshort_files'] = 'Les fichiers suivants n\'ont pas pu être analysés par Compilatio car ils ne contenaient pas assez de mots (Taille minimale : {$a} mots) :';
$string['toolong_files'] = 'Les fichiers suivants n\'ont pas pu être analysés par Compilatio car ils contenaient trop de mots (Taille maximale : {$a} mots) :';
$string['failedanalysis_files'] = "L'analyse des fichier(s) suivant(s) n'a pas fonctionné correctement. Vous pouvez relancer ces analyses :";
$string['no_document_available_for_analysis'] = "Aucun document n'était disponible pour analyse.";
$string["analysis_started"] = '{$a} analyse(s) démandée(s).';
$string["start_analysis_title"] = 'Démarrage manuel des analyses';
$string["start_analysis_in_progress"] = 'Lancement des analyses en cours';
$string["not_analyzed"] = "Les documents suivants n'ont pas pu être analysés :";
$string["account_expire_soon_title"] = "Votre abonnement Compilatio.net expire bientôt";
$string["admin_account_expire_content"] = "Votre abonnement actuel se terminera à la fin du mois en cours. Si votre contrat n'expire pas à la fin du mois, un nouvel abonnement sera automatiquement mis en place par nos services. Lorsque cela sera fait, ce message disparaitra. Pour plus d'informations, vous pouvez contacter notre service commercial ou notre support à l'adresse support@compilatio.net.";
$string["news_update"] = "Mise à jour Compilatio.net";
$string["news_incident"] = "Incident Compilatio.net";
$string["news_maintenance"] = "Maintenance Compilatio.net";
$string["news_analysis_perturbated"] = "Analyses Compilatio.net perturbées";
$string["updatecompilatioresults"] = "Rafraîchir les informations";
$string["update_in_progress"] = "Mise à jour des informations en cours";
$string["display_stats"] = "Afficher les statistiques de cette activité";
$string["analysis_completed"] = 'Analyse terminée: {$a}% de similitudes.';
$string["compilatio_help_assign"] = "Obtenir de l&#39aide sur le plugin Compilatio";
$string["display_notifications"] = "Afficher les notifications";
$string["compilatio_search_tab"] = "Rechercher l&#39auteur d&#39un document.";
$string["compilatio_search"] = "Rechercher";
$string["compilatio_iddocument"] = "Identifiant du document";
$string["compilatio_search_notfound"] = "Aucun document n'a été trouvé pour cet identifiant parmi les documents chargés sur votre plateforme Moodle.";
$string["compilatio_author"] = 'Le document {$a->idcourt} présent dans l\'activité <b>{$a->modulename}</b> appartient à <b>{$a->lastname} {$a->firstname}</b>.';
$string["compilatio_search_help"] = "Vous pouvez retrouver l'auteur d'un document en récupérant l'identifiant du document dans les sources du rapport d'analyse. Exemple : 1. Votre document: <b>1st5xfj2</b> - Nom_Activité(30)Nom_Document_Copié.odt.";
$string["allow_search_tab"] = "Outil de recherche permettant d'identifier l'auteur d'un document.";
$string["allow_search_tab_help"] = "L'outil de recherche permet de rechercher le nom et prénom d'un étudiant d'après un identifiant de document visible dans les rapports d'analyses parmi tous les documents présent sur votre plateforme.";
$string["waiting_time_title"] = "Pour toute analyse lancée maintenant, le temps de traitement estimé est de ";
$string["waiting_time_content"] = 'Dont {$a->queue} en file d\'attente et {$a->analysis_time} d\'analyse<br><br>Cliquer <a href=\'../../plagiarism/compilatio/helpcenter.php?page=moodle-info-waiting&idgroupe=';
$string["waiting_time_content_help"] = "' target='_blank'>ici</a> pour connaître les bonnes pratiques à suivre afin d'optimiser le temps de traitement des analyses Compilatio.";
$string["teacher_features_title"] = "Fonctionnalités activées pour les enseignants";
$string["enable_activities_title"] = "Activer Compilatio pour les activités";

// CSV.
$string["firstname"] = "Prénom";
$string["lastname"] = "Nom";
$string["filename"] = "Nom du fichier";
$string["similarities"] = "Taux de similitudes";
$string["unextractable"] = "Le contenu de ce document n'a pas pu être extrait";
$string["unsupported"] = "Document non supporté";
$string["analysing"] = "Document en cours d'analyse";
$string['timesubmitted'] = "Soumis à Compilatio le";
$string["not_analyzed_unextractable"] = '{$a} document(s) n\'ont pas été analysés car leur contenu n\'a pas pu être extrait.';
$string["not_analyzed_unsupported"] = '{$a} document(s) n\'ont pas été analysés car leur format n\'est pas supporté.';
$string["not_analyzed_tooshort"] = '{$a} document(s) n\'ont pas été analysés car ils ne contenaient pas assez de mots.';
$string["not_analyzed_toolong"] = '{$a} document(s) n\'ont pas été analysés car ils contenaient trop de mots.';
$string['analysis_date'] = "Date d'analyse (lancement programmé uniquement)";
$string['export_csv'] = 'Exporter les données de cette activité au format CSV';
$string['hide_area'] = 'Masquer les informations Compilatio';
$string['tabs_title_help'] = 'Aide';
$string['tabs_title_stats'] = 'Statistiques';
$string['tabs_title_notifications'] = 'Notifications';
$string['queued'] = 'Le document est en attente d\'analyse et va bientôt être traité par Compilatio';
$string['no_documents_available'] = 'Aucun document n\'est disponible pour analyse dans cette activité.';
$string['manual_analysis'] = "L'analyse de ce document doit être déclenchée manuellement";
$string['updated_analysis'] = 'Les résultats de l\'analyse Compilatio ont été mis à jour.';
$string['disclaimer_data'] = 'En activant Compilatio, vous acceptez que des informations concernant la configuration de votre plateforme Moodle soient collectées afin de faciliter le support et la maintenance du service.';
$string['reset'] = 'Relancer';
$string['error'] = 'Erreur';
$string['analyze'] = 'Analyser';
$string['queue'] = 'Attente';
$string['analyzing'] = 'Analyse';
$string['compilatioenableplugin'] = 'Activer Compilatio pour {$a}';
$string['enable_mod_assign'] = 'Activer Compilatio pour les devoirs (assign)';
$string['enable_mod_workshop'] = 'Activer Compilatio pour les ateliers (workshop)';
$string['enable_mod_forum'] = 'Activer Compilatio pour les forums';
$string['enable_mod_quiz'] = 'Activer Compilatio pour les tests';
$string['planned'] = "Planifié";
$string['immediately'] = "Immédiatement";
$string['enable_javascript'] = "Veuillez activer Javascript pour profiter de toutes les fonctionnalités du plugin Compilatio. <br/> Voici les <a href='http://www.enable-javascript.com/fr/' target='_blank'>
 instructions pour activer JavaScript dans votre navigateur Web</a>.";
$string["manual_send_confirmation"] = '{$a} fichier(s) soumis à Compilatio.';
$string["unsent_documents"] = 'Document(s) non-soumis';
$string["unsent_documents_content"] = 'Attention, cette activité contient un (des) document(s) non soumis à Compilatio.';
$string["statistics_title"] = 'Statistiques';
$string["no_statistics_yet"] = 'Aucunes statistiques ne sont disponibles pour le moment.';
$string["minimum"] = 'Minimum';
$string["maximum"] = 'Maximum';
$string["average"] = 'Moyenne';
$string["documents_number"] = 'Documents analysés';
$string["stats_errors"] = "Erreurs";
$string["stats_failed"] = 'Analyses échouées';
$string["stats_notfound"] = 'Fichier non trouvé';
$string["stats_unextractable"] = "Le contenu du fichier n'a pas pu être extrait";
$string["stats_unsupported"] = 'Format de fichier non supporté';
$string["stats_tooshort"] = 'Le fichier ne contient pas assez de mots';
$string["stats_toolong"] = 'Le fichier contient trop de mots';
$string["export_raw_csv"] = 'Cliquez-ici pour exporter les données brutes au format CSV';
$string["export_global_csv"] = 'Cliquez-ici pour exporter ces données au format CSV';
$string["global_statistics_description"] = 'Toutes les données des documents envoyés à Compilatio.';
$string["global_statistics"] = 'Statistiques globales';
$string["assign_statistics"] = 'Statistiques des devoirs';
$string["similarities"] = 'Similitudes';
$string["context"] = 'Contexte';
$string["pending_status"] = 'Attente';
$string["allow_teachers_to_show_reports"] = "Possibilité de mettre les rapports d'analyse à disposition des étudiants";
$string["admin_disabled_reports"] = "L'administrateur a désactivé l'affichage des rapports de similitudes aux étudiants.";
$string["teacher"] = "Enseignant";
$string["loading"] = "Chargement en cours, veuillez patienter...";
// ALERTS.
$string["unknownlang"] = "Attention, la langue de certains passages de ce document n'a pas été reconnue.";
$string["badqualityanalysis"] = "Des incidents ont été détectés lors l'analyse du document. Il est possible que certaines sources n'aient pas été identifiées ou que le résultat soit incomplet.";
/* HELP */
$string['help_compilatio_format_content'] = "Compilatio.net prend en charge la plupart des formats utilisés en bureautique et sur Internet. Les formats suivants sont acceptés :";
$string['goto_compilatio_service_status'] = "Voir l'état des services Compilatio.";
$string['helpcenter'] = "Accédez au centre d'aide Compilatio pour l'utilisation du plugin Compilatio dans Moodle.";
$string['goto_helpcenter'] = "Cliquez sur le point d'interrogation pour ouvrir une nouvelle fenêtre et vous connecter au centre d'aide Compilatio.";
$string['admin_goto_helpcenter'] = "Accédez au centre d'aide Compilatio pour voir des articles relatifs à l'administration du plugin Moodle.";
$string['helpcenter_error'] = "Nous ne pouvons pas vous connecter automatiquement au centre d'aide. Veuillez ré-essayer ultérieurement ou vous y rendre directement grâce au lien suivant : ";
/* END HELP */
$string['get_scores'] = "Récupère les taux de similitudes depuis Compilatio.net";
$string['send_files'] = "Envoie les fichiers à Compilatio.net pour détection de plagiat";
$string['update_meta'] = "Exécute les tâches planifiées par Compilatio.net";
$string['trigger_analyses'] = "Déclenche les analyses";
// Indexing state.
$string['indexing_state'] = "Ajouter les documents à la bibliothèque de références";
$string['indexing_state_help'] = "Oui: Ajoute les documents dans la bibliothèque de références. Ces documents seront utilisés comme matériel de comparaison pour vos analyses.
Non: Les documents ne sont pas ajoutés à la bibliothèque de références et ne seront pas utilisés comme matériel de comparaison.";
$string['indexed_document'] = "Document ajouté à la bibliothèque de références de votre établissement. Son contenu pourra être utilisé pour détecter des similitudes avec d’autres documents.";
$string['not_indexed_document'] = "Document non ajouté à la bibliothèque de références de votre établissement. Son contenu ne sera pas utilisé pour détecter des similitudes avec d’autres documents.";
// Information settings.
$string['information_settings'] = "Informations";
// Max file size allowed.
$string['max_file_size_allowed'] = 'Taille maximale des documents : <strong>{$a->Mo} Mo</strong>';
// Failed documents.
$string['restart_failed_analysis'] = 'Relancer les analyses échouées';
$string['restart_failed_analysis_title'] = 'Relance des analyses échouées :';
$string['restart_failed_analysis_in_progress'] = 'Relance des analyses échouées en cours';
// Max attempt reached.
$string['max_attempts_reach_files'] = 'Les fichiers suivants n\'ont pas pu être analysés par Compilatio. La limite de relance d\'analyses a été atteinte :';

// Privacy (RGPD).
$string['privacy:metadata:core_files'] = 'Fichiers déposés ou créés depuis un champ de saisie';
$string['privacy:metadata:core_plagiarism'] = 'Ce plugin est appelé par le sous-système de détection de plagiat de Moodle';

$string['privacy:metadata:plagiarism_compilatio_files'] = 'Informations à propos des fichiers soumis à Compilatio dans la base de données du plugin';
$string['privacy:metadata:plagiarism_compilatio_files:id'] = 'L\'identifiant de la soumission dans la base de données de Moodle';
$string['privacy:metadata:plagiarism_compilatio_files:cm'] = 'L\'identifiant du module de cours où se trouve la soumission';
$string['privacy:metadata:plagiarism_compilatio_files:userid'] = 'L\'identifiant de l\'utilisateur Moodle qui a fait la soumission';
$string['privacy:metadata:plagiarism_compilatio_files:identifier'] = 'La contenthash de la soumission';
$string['privacy:metadata:plagiarism_compilatio_files:filename'] = 'Le nom (éventuellement auto-généré) de la soumission';
$string['privacy:metadata:plagiarism_compilatio_files:timesubmitted'] = 'L\'heure à laquelle le fichier a été enregistré dans la base de données Moodle du plugin';
$string['privacy:metadata:plagiarism_compilatio_files:externalid'] = 'L\'identifiant de la soumission dans la base de données de Compilatio';
$string['privacy:metadata:plagiarism_compilatio_files:statuscode'] = 'L\'état de l\'analyse la soumission (Analysé, En attente, Temps dépassé...)';
$string['privacy:metadata:plagiarism_compilatio_files:reporturl'] = 'L\'adresse URL du rapport d\'analyse';
$string['privacy:metadata:plagiarism_compilatio_files:similarityscore'] = 'Le pourcentage de similitudes trouvées pour cette soumission';
$string['privacy:metadata:plagiarism_compilatio_files:attempt'] = 'Le nombre de fois qu\'un utilisateur a essayé de lancer l\'analyse d\'une soumission';
$string['privacy:metadata:plagiarism_compilatio_files:errorresponse'] = 'La réponse au cas où il y aurait une erreur - actuellement, ce champ n\'est plus utilisé et est automatiquement mis à \'NULL\'';
$string['privacy:metadata:plagiarism_compilatio_files:recyclebinid'] = "L'identifiant de la corbeille dans le cas où le module de cours ou le cours à été mis à la corbeille";
$string['privacy:metadata:plagiarism_compilatio_files:apiconfigid'] = "L'identifiant de la configuration de l'API avec laquelle la soumission est liée";
$string['privacy:metadata:plagiarism_compilatio_files:idcourt'] = "L'identifiant court de la soumission dans la base de données de Compilatio";

$string['privacy:metadata:external_compilatio_document'] = 'Informations à propos des documents dans la base de données de Compilatio';
$string['privacy:metadata:external_compilatio_document:lastname'] = 'Nom de l\'utilisateur Compilatio qui a soumis le fichier - attention, cet utilisateur est celui qui est lié à la clé d\'API Compilatio sur la plateforme Moodle (c\'est donc souvent l\'administrateur de la plateforme)';
$string['privacy:metadata:external_compilatio_document:firstname'] = 'Prénom de l\'utilisateur Compilatio qui a soumis le fichier - attention, cet utilisateur est celui qui est lié à la clé d\'API Compilatio sur la plateforme Moodle (c\'est donc souvent l\'administrateur de la plateforme)';
$string['privacy:metadata:external_compilatio_document:email_adress'] = 'Adresse email de l\'utilisateur Compilatio qui a soumis le fichier - attention, cet utilisateur est celui qui est lié à la clé d\'API Compilatio sur la plateforme Moodle (c\'est donc souvent l\'administrateur de la plateforme)';
$string['privacy:metadata:external_compilatio_document:user_id'] = 'L\'identifiant de l\'utilisateur Compilatio qui a soumis le fichier - attention, cet utilisateur est celui qui est lié à la clé d\'API Compilatio sur la plateforme Moodle (c\'est donc souvent l\'administrateur de la plateforme)';
$string['privacy:metadata:external_compilatio_document:filename'] = 'Le nom de la soumission';
$string['privacy:metadata:external_compilatio_document:upload_date'] = 'L\'heure à laquelle le fichier à été enregistré dans la base de données Compilatio';
$string['privacy:metadata:external_compilatio_document:id'] = 'L\'identifiant de la soumission dans la base de données de Compilatio';
$string['privacy:metadata:external_compilatio_document:indexed'] = 'L\'état d\'indéxation de la soumission (si elle est utilisée comme document de référence lors des analyses)';

$string['privacy:metadata:external_compilatio_report'] = 'Informations à propos du rapport d\'analyse dans la base de données de Compilatio (uniquement si le document a été analysé)';
$string['privacy:metadata:external_compilatio_report:id'] = 'L\'identifiant Compilatio du rapport d\'analyse';
$string['privacy:metadata:external_compilatio_report:doc_id'] = 'L\'identifiant Compilatio du document qui a été analysé';
$string['privacy:metadata:external_compilatio_report:user_id'] = 'L\'identifiant de l\'utilisateur Compilatio qui a soumis le fichier - attention, cet utilisateur est celui qui est lié à la clé d\'API Compilatio sur la plateforme Moodle (c\'est donc souvent l\'administrateur de la plateforme)';
$string['privacy:metadata:external_compilatio_report:start'] = 'La date de début de l\'analyse';
$string['privacy:metadata:external_compilatio_report:end'] = 'La date de fin de l\'analyse';
$string['privacy:metadata:external_compilatio_report:state'] = 'L\'état de l\'analyse de la soumission (Analysé, En attente, Temps dépassé...)';
$string['privacy:metadata:external_compilatio_report:plagiarism_percent'] = 'Le pourcentage de similitudes trouvées pour cette soumission';

$string['owner_file'] = 'RGPD et propriété du devoir';
$string['owner_file_school'] = 'L\'établissement est propriétaire des devoirs';
$string['owner_file_school_details'] = 'En cas de demande de suppression des données personnelles d\'un élève, le contenu des devoirs sera conservé et disponible pour une comparaison future avec de nouveaux devoirs. À échéance du contrat avec Compilatio, toutes les données à caractère personnel de votre établissement, dont les devoirs, sont supprimées dans les délais prévus contractuellement.';
$string['owner_file_student'] = 'L\'élève est l\'unique propriétaire de son devoir';
$string['owner_file_student_details'] = 'En cas de demande de suppression des données personnelles d\'un élève, les devoirs seront supprimés de la plateforme Moodle et de la bibliothèque de références Compilatio. Les devoirs ne seront plus disponibles pour une comparaison avec de nouveaux documents.';
