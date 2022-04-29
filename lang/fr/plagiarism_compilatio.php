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
$string['compilatio'] = 'Plugin de détection de plagiat Compilatio';

// Admin Compilatio settings.
$string['activate_compilatio'] = 'Activer le plug-in Compilatio';
$string['disclaimer_data'] = 'En activant Compilatio, vous acceptez que des informations concernant la configuration de votre plateforme Moodle soient collectées afin de faciliter le support et la maintenance du service.';
$string['studentdisclosuredefault'] = "L'ensemble des fichiers envoyés seront soumis au service de détection de similitudes de Compilatio";
$string['students_disclosure'] = 'Message de prévention pour les étudiants';
$string['students_disclosure_help'] = 'Ce texte sera affiché à tous les étudiants sur la page de téléchargement de fichier.';
$string['compilatioexplain'] = 'Pour obtenir des informations complémentaires sur ce plugin, voir : <a href="http://compilatio.net" target="_blank">compilatio.net</a>';
$string['apikey'] = 'Clé API';
$string['apikey_help'] = "Code personnel fourni par Compilatio pour accéder à l'API";
$string['enabledandworking'] = 'Le plugin Compilatio est actif et fonctionnel.';
$string['saved_config_failed'] = "<strong>La combinaison adresse - clé API est incorrecte. Le plugin est désactivé, merci de réessayer.<br/>
    La page d'<a href='autodiagnosis.php'>auto-diagnostic</a> peut vous aider à configurer ce plugin.</strong><br/>
    Erreur : ";
$string["teacher_features_title"] = "Fonctionnalités activées pour les enseignants";
$string["checkbox_show_reports"] = "Mettre les rapports d'analyse à disposition des étudiants";
$string['checkbox_student_analyses'] = "Permettre aux étudiants d'analyser leur fichiers en brouillon avec Compilatio Magister, avant le rendu final à l'enseignant.";
$string["checkbox_search_tab"] = "Outil de recherche permettant d'identifier l'auteur d'un document.";
$string["checkbox_search_tab_help"] = "L'outil de recherche permet de rechercher le nom et prénom d'un étudiant d'après un identifiant de document visible dans les rapports d'analyses parmi tous les documents présent sur votre plateforme.";
$string["checkbox_analyses_auto"] = "Lancement automatique des analyses";
$string["checkbox_analyses_auto_help"] = "Les enseignants peuvent lancer les analyses manuellement ou programmer une date de lancement des analyses.";
$string["enable_activities_title"] = "Activer Compilatio pour les activités";
$string['enable_mod_assign'] = ' Devoirs (assign)';
$string['enable_mod_workshop'] = 'Ateliers (workshop)';
$string['enable_mod_forum'] = 'Forums';
$string['enable_mod_quiz'] = 'Tests (quiz)';
$string['owner_file'] = 'RGPD et propriété du devoir';
$string['owner_file_school'] = "L'établissement est propriétaire des devoirs";
$string['owner_file_school_details'] = "En cas de demande de suppression des données personnelles d'un élève, le contenu des devoirs sera conservé et disponible pour une comparaison future avec de nouveaux devoirs. À échéance du contrat avec Compilatio, toutes les données à caractère personnel de votre établissement, dont les devoirs, sont supprimées dans les délais prévus contractuellement.";
$string['owner_file_student'] = "L'élève est l'unique propriétaire de son devoir";
$string['owner_file_student_details'] = "En cas de demande de suppression des données personnelles d'un élève, les devoirs seront supprimés de la plateforme Moodle et de la bibliothèque de références Compilatio. Les devoirs ne seront plus disponibles pour une comparaison avec de nouveaux documents.";

// Activity settings.
$string['terms_of_service'] = 'J\'ai pris connaissance des <a href=\'{$a}\'>Conditions générales d\'utilisation</a> de Compilatio et je les accepte.';
$string['terms_of_service_info'] = '<a href=\'{$a}\'>Conditions générales d\'utilisation</a> de Compilatio';
$string['tos'] = 'Les <a href=\'{$a}\'>Conditions générales d\'utilisation</a> de Compilatio n\'ont pas été validés ou on été mises à jour.<br> Merci d\'en prendre connaissance et de les valider pour pouvoir utiliser Compilatio.';
$string['tos_btn'] = "J'ai pris connaissance des Conditions générales d'utilisation et je les accepte.";
$string['activated'] = 'Détecter des similitudes avec Compilatio';
$string['defaultindexing'] = "Ajouter les documents à la bibliothèque de références";
$string['defaultindexing_help'] = "Oui: Ajoute les documents dans la bibliothèque de références. Ces documents seront utilisés comme matériel de comparaison pour vos analyses.
    Non: Les documents ne sont pas ajoutés à la bibliothèque de références et ne seront pas utilisés comme matériel de comparaison.";
$string['showstudentscore'] = 'Rendre le pourcentage de similitudes visible par les étudiants';
$string['showstudentscore_help'] = 'Le pourcentage de similitudes indique la quantité de texte dans le document qui a été retrouvée dans d’autres documents.';
$string['showstudentreport'] = "Permettre à l'étudiant de visualiser le rapport d'analyse";
$string['showstudentreport_help'] = 'Le rapport d’analyse d’un document présente les passages similaires avec les sources détectées et leurs pourcentages de similitudes.';
$string['immediately'] = "Immédiatement";
$string['showwhenclosed'] = "Quand l'activité est fermée";
$string['studentemail'] = "Envoyer un mail à l'étudiant";
$string['studentemail_help'] = "Ceci enverra un e-mail à l'élève quand un fichier a été traité pour leur faire savoir que le rapport est disponible.";
$string['analysis'] = 'Lancement des analyses';
$string['analysis_help'] = "<p>Vous disposez de trois options :</p>
    <ul>
        <li><strong> Manuel:</strong> L'analyse des documents doit être déclenchée manuellement via le bouton “Analyser” de chaque document ou via le bouton “Analyser tous les documents”.</li>
        <li><strong> Programmé:</strong> Tous les documents sont analysés à l'heure/date choisie.</li>
        <li><strong> Immédiat:</strong> Chaque document est analysé dès le dépôt par l’étudiant. Les documents de l’activité ne seront pas comparés entre eux.</li>
    </ul>
    <p>Pour que tous les documents soient comparés entre eux lors des analyses, déclenchez les analyses seulement lorsque tous les documents sont présents dans le devoir.</p>";
$string['analysistype_manual'] = 'Manuel';
$string['analysistype_prog'] = 'Programmé';
$string['analysistype_auto'] = 'Immédiat';
$string['analysis_date'] = "Date d'analyse (lancement programmé uniquement)";
$string['thresholds_settings'] = "Réglage des seuils d'affichage des taux de similitudes :";
$string['thresholds_description'] = "Indiquez les seuils que vous souhaitez utiliser, afin de faciliter le repérage des rapports d’analyse (% de similitudes) : ";
$string['numeric_threshold'] = "Le seuil doit être numérique.";
$string['warningthreshold'] = "Vert jusqu'à";
$string['criticalthreshold'] = "Orange jusqu'à";
$string['red_threshold'] = "rouge au delà";
$string['similarity_percent'] = "% de similitudes";
$string['studentanalyses'] = "Permettre aux étudiants d'analyser leurs documents";
$string['studentanalyses_help'] = "Ceci permet aux étudiants d'analyser leur fichiers en brouillon avec Compilatio Magister, avant le rendu final à l'enseignant.";
$string['activate_submissiondraft'] = 'Pour permettre aux étudiants d\'analyser leurs brouillons, vous devez activer l\'option <b>{$a}</b> dans la partie';
$string['quiz_help'] = 'Seules les questions de type composition dont la réponse contient au moins {$a} mots seront analysés.';
$string["admin_disabled_reports"] = "L'administrateur a désactivé l'affichage des rapports de similitudes aux étudiants.";
$string['help_compilatio_format_content'] = "Compilatio.net prend en charge la plupart des formats utilisés en bureautique et sur Internet. Les formats suivants sont acceptés : ";
$string['max_file_size_allowed'] = 'Les fichiers ne doivent pas excéder <strong>{$a} Mo</strong>';
$string['min_max_word_required'] = 'Pour pouvoir être analysé, un texte doit avoir entre {$a->min} et {$a->max} mots';

// Default settings page.
$string['compilatiodefaults'] = "Valeurs par défaut pour Compilatio";
$string['defaultupdated'] = "Les valeurs par défaut ont été mises à jour";
$string['defaults_desc'] = "Les paramètres suivants sont utilisés comme valeurs par défaut dans les activités de Moodle intégrant Compilatio.";

// Compilatio button.
$string["title_scored"] = 'Analyse terminée: {$a}% de similitudes.';
$string['btn_sent'] = 'Analyser';
$string['title_sent'] = "Démarrer l'analyse";
$string['btn_planned'] = "Planifié";
$string['title_planned'] = 'Ce fichier sera traité le {$a}';
$string['btn_queue'] = 'Attente';
$string['title_queue'] = "Le document est en attente d'analyse et va bientôt être traité par Compilatio";
$string['btn_analyzing'] = 'Analyse';
$string['title_analyzing'] = "Le fichier est en cours d'analyse par Compilatio.";
$string['btn_error_analysis_failed'] = 'Relancer';
$string['title_error_analysis_failed'] = "L'analyse de ce document n'a pas fonctionné correctement.";
$string['btn_error_sending_failed'] = 'Renvoyer';
$string['title_error_sending_failed'] = "Une erreur s'est produite lors de l'envoi du fichier à Compilatio";
$string['btn_error'] = 'Erreur';
$string['title_error_unsupported'] = "Ce type de fichier n'est pas supporté par Compilatio";
$string['title_error_too_large'] = 'Le fichier est trop volumineux pour être traité par Compilatio. Taille maximale : {$a} Mo';
$string['title_error_too_short'] = 'Ce document ne contient pas assez de mots pour être traité par Compilatio. Taille minimale : {$a} mots';
$string['title_error_too_long'] = 'Ce document contient trop de mots pour être analysé. Taille maximale : {$a} mots';
$string['title_error_not_found'] = "Ce document n'a pas été trouvé. Veuillez contacter votre administrateur de moodle. Erreur : document non trouvé pour cette clé API.";

$string['previouslysubmitted'] = "Auparavant soumis comme";
$string['student_analyze'] = "Analyse par l'étudiant";
$string['student_start_analyze'] = "L'analyse peut être lancée par l'étudiant";
$string['student_help'] = "Vous pouvez analyser votre brouillon avec Compilatio Magister, afin de mesurer les similitudes présentes dans le texte de vos fichiers.<br/>
    Le contenu de votre brouillon ne sera pas utilisé par Compilatio comme matériel de comparaison pour les futures analyses effectuées.<br/>
    Votre enseignant aura cependant accès à ce rapport d'analyse.";
$string['failedanalysis'] = "Compilatio n'a pas réussi à analyser votre document : ";
$string['indexed_document'] = "Document ajouté à la bibliothèque de références de votre établissement. Son contenu pourra être utilisé pour détecter des similitudes avec d’autres documents.";
$string['not_indexed_document'] = "Document non ajouté à la bibliothèque de références de votre établissement. Son contenu ne sera pas utilisé pour détecter des similitudes avec d’autres documents.";

// Student email.
$string['studentemailsubject'] = 'Le fichier a été traité par Compilatio';
$string['studentemailcontent'] = 'Le fichier que vous avez soumis à {$a->modulename} dans {$a->coursename} a été traité par l\'outil de détection de plagiat Compilatio
    {$a->modulelink}';

// Compilatio frame.
$string['similarities_disclaimer'] = "Vous pouvez analyser les similitudes présentes dans les documents de cette activité à l’aide du logiciel <a href='http://compilatio.net' target='_blank'>Compilatio</a>.<br/>
	Attention, des similitudes mesurées lors d’une analyse ne révèlent pas nécessairement un plagiat. Le rapport d’analyse vous aide à comprendre si les similitudes correspondent à des emprunts et citations convenablement identifiés ou à des plagiats.";
$string['programmed_analysis_future'] = 'Les documents seront analysés par Compilatio le {$a}.';
$string['programmed_analysis_past'] = 'Les documents ont été soumis pour analyse à Compilatio le {$a}.';
$string['webservice_unreachable_title'] = "Indisponibilité Compilatio.net";
$string['webservice_unreachable_content'] = "Le service Compilatio.net est actuellement indisponible. Veuillez nous excuser pour la gêne occasionnée.";
$string['startallcompilatioanalysis'] = "Analyser tous les documents";
$string["updatecompilatioresults"] = "Rafraîchir les informations";
$string['restart_failed_analysis'] = 'Relancer les analyses échouées';
$string["compilatio_help_assign"] = "Obtenir de l&#39aide sur le plugin Compilatio";
$string['hide_area'] = 'Masquer les informations Compilatio';

// Detailed error status.
$string['detailed_error_unsupported'] = "Ces documents n'ont pas pu être analysés par Compilatio car leur format n'est pas supporté.";
$string['detailed_error_sending_failed'] = "Ces documents n'ont pas pu être envoyés à Compilatio. Vous pouvez renvoyer ces documents.";
$string['detailed_error_too_short'] = 'Ces documents n\'ont pas pu être analysés par Compilatio car ils ne contenaient pas assez de mots (Taille minimale : {$a} mots).';
$string['detailed_error_too_long'] = 'Ces documents n\'ont pas pu être analysés par Compilatio car ils contenaient trop de mots (Taille maximale : {$a} mots).';
$string['detailed_error_too_large'] = 'Ces documents n\'ont pas pu être analysés par Compilatio car ils sont trop volumineux (Taille maximale : {$a} Mo).';
$string['detailed_error_analysis_failed'] = "L'analyse de ces documents n'a pas fonctionné correctement. Vous pouvez relancer ces analyses.";
$string['detailed_error_not_found'] = "Ces document n'ont pas été trouvés. Veuillez contacter votre administrateur de moodle. Erreur : document non trouvé pour cette clé API.";

// Short error status.
$string['short_error_not_found'] = 'documents non trouvés.';
$string['short_error_analysis_failed'] = 'analyses échouées.';
$string["short_error_sending_failed"] = "envois échoués.";
$string["short_error_unsupported"] = 'documents non supportés.';
$string["short_error_too_short"] = 'documents trop courts.';
$string["short_error_too_long"] = 'documents trop longs.';
$string["short_error_too_large"] = 'documents trop volumineux';

// Notifications tab.
$string['tabs_title_notifications'] = 'Notifications';
$string["display_notifications"] = "Afficher les notifications";
$string['max_attempts_reach_files'] = "Ces documents n'ont pas pu être analysés par Compilatio. La limite de relance d'analyses a été atteinte.";
$string['no_document_available_for_analysis'] = "Aucun document n'était disponible pour analyse.";
$string["analysis_started"] = '{$a} analyse(s) démandée(s).';
$string["start_analysis_title"] = 'Démarrage manuel des analyses';
$string["start_analysis_in_progress"] = 'Lancement des analyses en cours';
$string["not_analyzed"] = "Les documents suivants n'ont pas pu être analysés :";
$string["update_in_progress"] = "Mise à jour des informations en cours";
$string["unsent_documents"] = 'Document(s) non-soumis';
$string["unsent_documents_content"] = 'Attention, cette activité contient un (des) document(s) non soumis à Compilatio.';
$string['restart_failed_analysis_title'] = 'Relance des analyses échouées :';
$string['restart_failed_analysis_in_progress'] = 'Relance des analyses échouées en cours';

// Search author tab.
$string["compilatio_search_tab"] = "Rechercher le déposant d&#39un document.";
$string["compilatio_search"] = "Rechercher";
$string["compilatio_search_help"] = "Vous pouvez retrouver l'auteur d'un document en récupérant l'identifiant du document dans les sources du rapport d'analyse. Exemple : 1. Votre document: <b>de3ccb</b> - Nom_Activité(30)Nom_Document_Copié.odt.";
$string["compilatio_iddocument"] = "Identifiant du document";
$string["compilatio_search_notfound"] = "Aucun document n'a été trouvé pour cet identifiant parmi les documents chargés sur votre plateforme Moodle.";
$string["compilatio_author"] = 'Le document présent dans l\'activité <b>{$a->modulename}</b> à été rendu par l\'utilisateur Moodle <b>{$a->lastname} {$a->firstname}</b>.';

// Assign statistics tab.
$string['tabs_title_stats'] = 'Statistiques';
$string["display_stats"] = "Afficher les statistiques de cette activité";
$string['export_csv'] = 'Exporter les données de cette activité au format CSV';
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
$string['no_documents_available'] = "Aucun document n'est disponible pour analyse dans cette activité.";

// Global Statistics.
$string["no_statistics_yet"] = 'Aucunes statistiques ne sont disponibles pour le moment.';
$string["teacher"] = "Enseignant";
$string["minimum"] = 'Minimum';
$string["maximum"] = 'Maximum';
$string["average"] = 'Moyenne';
$string["documents_number"] = 'Documents analysés';
$string["stats_errors"] = "Erreurs";
$string["export_raw_csv"] = 'Cliquez-ici pour exporter les données brutes au format CSV';
$string["export_global_csv"] = 'Cliquez-ici pour exporter ces données au format CSV';
$string["global_statistics_description"] = 'Toutes les données des documents envoyés à Compilatio.';
$string["global_statistics"] = 'Statistiques globales';
$string["activities_statistics"] = 'Statistiques des activités';
$string["similarities"] = 'Similitudes';

// Help tab.
$string['tabs_title_help'] = 'Aide';
$string['goto_compilatio_service_status'] = "Voir l'état des services Compilatio.";
$string['helpcenter'] = "Accédez au centre d'aide Compilatio pour l'utilisation du plugin Compilatio dans Moodle.";
$string['admin_goto_helpcenter'] = "Accédez au centre d'aide Compilatio pour voir des articles relatifs à l'administration du plugin Moodle.";
$string['helpcenter_error'] = "Nous ne pouvons pas vous connecter automatiquement au centre d'aide. Veuillez ré-essayer ultérieurement ou vous y rendre directement grâce au lien suivant : ";

// Auto diagnostic page.
$string["auto_diagnosis_title"] = "Auto-diagnostic";
$string["api_key_valid"] = "La clé API enregistrée est valide.";
$string["api_key_not_tested"] = "La clé API n'a pas pû être vérifiée car la connexion au service Compilatio.net à échouée.";
$string["api_key_not_valid"] = "La clé API enregistrée est invalide. Elle est spécifique à la plateforme utilisée. Vous pouvez en obtenir une en contactant <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.";
$string['cron_check_never_called'] = "CRON n'a pas été exécuté depuis l'activation du plugin. Il est possible qu'il soit mal configuré.";
$string['cron_check'] = 'CRON a été exécuté le {$a} pour la dernière fois.';
$string['cron_check_not_ok'] = "Il n'a pas été exécuté depuis plus d'une heure.";
$string['cron_frequency'] = 'Il semblerait qu\'il soit exécuté toutes les {$a} minutes.';
$string['cron_recommandation'] = "Nous recommandons d'utiliser un délai inférieur à 15 minutes entre chaque exécution de CRON.";
$string['webservice_ok'] = "Le serveur est capable de contacter le webservice.";
$string['webservice_not_ok'] = "Le webservice n'a pas pu être contacté. Il est possible que votre pare-feu bloque la connexion.";
$string['plugin_enabled'] = "Le plugin est activé pour la plateforme Moodle.";
$string['plugin_disabled'] = "Le plugin n'est pas activé pour la plateforme Moodle.";
$string['plugin_enabled_assign'] = "Le plugin est activé pour les devoirs.";
$string['plugin_disabled_assign'] = "Le plugin n'est pas activé pour les devoirs.";
$string['plugin_enabled_workshop'] = "Le plugin est activé pour les ateliers.";
$string['plugin_disabled_workshop'] = "Le plugin n'est pas activé pour les ateliers.";
$string['plugin_enabled_forum'] = "Le plugin est activé pour les forums.";
$string['plugin_disabled_forum'] = "Le plugin n'est pas activé pour les forums.";
$string['plugin_enabled_quiz'] = "Le plugin est activé pour les tests.";
$string['plugin_disabled_quiz'] = "Le plugin n'est pas activé pour les tests.";

// Capabilities.
$string['compilatio:enable'] = "Autoriser l'enseignant à activer/désactiver Compilatio au sein d'une activité";
$string['compilatio:triggeranalysis'] = "Autoriser l'enseignant à déclencher manuellement l'analyse";
$string['compilatio:viewreport'] = "Autoriser l'enseignant à consulter le rapport complet depuis Compilatio";

// CSV.
$string["firstname"] = "Prénom";
$string["lastname"] = "Nom";
$string["filename"] = "Nom du fichier";
$string['timesubmitted'] = "Soumis à Compilatio le";
$string["similarities_rate"] = "Taux de similitudes";
$string['manual_analysis'] = "L'analyse de ce document doit être déclenchée manuellement";

// Scheduled tasks.
$string['get_scores'] = "Récupère les taux de similitudes depuis Compilatio.net";
$string['send_files'] = "Envoie les fichiers à Compilatio.net pour détection de plagiat";
$string['update_meta'] = "Exécute les tâches planifiées par Compilatio.net";
$string['trigger_timed_analyses'] = "Déclenche les analyses de plagiat programmées";

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
$string['privacy:metadata:plagiarism_compilatio_files:status'] = 'L\'état de l\'analyse la soumission (Analysé, En attente, Temps dépassé...)';
$string['privacy:metadata:plagiarism_compilatio_files:reporturl'] = 'L\'adresse URL du rapport d\'analyse';
$string['privacy:metadata:plagiarism_compilatio_files:similarityscore'] = 'Le pourcentage de similitudes trouvées pour cette soumission';
$string['privacy:metadata:plagiarism_compilatio_files:attempt'] = 'Le nombre de fois qu\'un utilisateur a essayé de lancer l\'analyse d\'une soumission';
$string['privacy:metadata:plagiarism_compilatio_files:indexed'] = "L'état d'indexation en bibliothèque de référence du fichier.";

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
