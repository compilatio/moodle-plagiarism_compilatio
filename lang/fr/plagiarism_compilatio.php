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
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
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
    Erreur :";
$string['read_only_apikey'] = "Votre clé API en lecture seule ne permet pas de télécharger ou d'analyser des documents.";
$string['subscription'] = '<b>Informations concernant votre abonnement :</b>';
$string['subscription_start'] = 'Date de début :';
$string['subscription_end'] = 'Date de fin incluse :';
$string['subscription_analysis_count'] = 'Documents analysés : {$a->usage} sur {$a->value}';
$string['subscription_analysis_page_count'] = 'Pages analysés : {$a->usage} sur {$a->value}';
$string['disable_ssl_verification'] = "Ignorer la vérification du certificat SSL.";
$string['disable_ssl_verification_help'] = "Activez cette option si vous rencontrez des problèmes de vérification de certificats SSL ou si vous constatez des erreurs lors des envois de fichiers à Compilatio.";
$string["teacher_features_title"] = "Fonctionnalités activées pour les enseignants";
$string["enable_show_reports"] = "Possibilité de mettre les rapports d'analyse à disposition des étudiants";
$string['enable_student_analyses'] = "Possibilité d'activer l'analyse des brouillons par les étudiants.";
$string['enable_student_analyses_help'] = "Cette option permettra aux enseignants d'activer sur une activité l'analyse par les étudiants de leurs documents rendus en mode brouillon avec Compilatio Magister, avant le rendu final à l'enseignant.";
$string["enable_search_tab"] = "Outil de recherche permettant d'identifier l'auteur d'un document.";
$string["enable_search_tab_help"] = "L'outil de recherche permet de rechercher le nom et prénom d'un étudiant d'après un identifiant de document visible dans les rapports d'analyses parmi tous les documents présent sur votre plateforme.";
$string["enable_analyses_auto"] = "Possibilité de lancer immédiatement les analyses";
$string["enable_analyses_auto_help"] = "<p>Cette option permettra aux enseignants d'activer sur une activité le lancement automatique de l'analyse des documents (i.e. immédiatement après leur dépôt).<br>
Notez que dans ce cas :
<ul>
    <li>Le nombre d'analyses effectuées par votre établissement peut être significativement plus important.</li>
    <li>Les documents des premiers déposants ne seront pas comparés avec les documents des derniers déposants.</li>
</ul>
Pour que tous les documents d'un devoir soient comparés entre eux, il est nécessaire d'utiliser l'analyse “programmée”, en choisissant une date postérieure à la date de fin de rendu des devoirs.</p>";
$string["enable_activities_title"] = "Activer Compilatio pour les activités";
$string['enable_mod_assign'] = ' Devoirs (assign)';
$string['enable_mod_workshop'] = 'Ateliers (workshop)';
$string['enable_mod_forum'] = 'Forums';
$string['enable_mod_quiz'] = 'Tests (quiz)';
$string['document_deleting'] = "Suppression des documents";
$string['keep_docs_indexed'] = "Conserver les documents en bibliothèque de référence";
$string['keep_docs_indexed_help'] = "Lors de la suppression d'un cours, de la réinitialisation d'un cours ou de la suppression d'une activité, vous pouvez choisir de supprimer définitivement les documents envoyés à Compilatio ou de les conserver en bibliothèque de référence (seul le texte sera conservé et sera utilisé comme matériel de comparaison lors de vos prochaines analyses)";
$string['owner_file'] = 'RGPD et propriété du devoir';
$string['owner_file_school'] = "L'établissement est propriétaire des devoirs";
$string['owner_file_school_details'] = "En cas de demande de suppression des données personnelles d'un élève, le contenu des devoirs sera conservé et disponible pour une comparaison future avec de nouveaux devoirs. À échéance du contrat avec Compilatio, toutes les données à caractère personnel de votre établissement, dont les devoirs, sont supprimées dans les délais prévus contractuellement.";
$string['owner_file_student'] = "L'élève est l'unique propriétaire de son devoir";
$string['owner_file_student_details'] = "En cas de demande de suppression des données personnelles d'un élève, les devoirs seront supprimés de la plateforme Moodle et de la bibliothèque de références Compilatio. Les devoirs ne seront plus disponibles pour une comparaison avec de nouveaux documents.";

// Activity settings.
$string['info_cm_activation'] = 'En activant Compilatio sur cette activité, les documents rendus seront chargés sur votre compte Compilatio ({$a}).<br>Tous les enseignants inscrits dans ce cours pourrons utiliser Compilatio sur cette activité.';
$string['info_cm_activated'] = 'Les documents rendus dans cette activité sont chargés sur le compte Compilatio {$a}.<br>Tous les enseignants inscrits dans ce cours peuvent utiliser Compilatio sur cette activité.';
$string['terms_of_service'] = 'J\'ai pris connaissance des <a href=\'{$a}\'>Conditions générales d\'utilisation</a> de Compilatio et je les accepte.';
$string['terms_of_service_info'] = '<a href=\'{$a}\'>Conditions générales d\'utilisation</a> de Compilatio';
$string['terms_of_service_alert'] = 'Les <a href=\'{$a}\'>Conditions générales d\'utilisation</a> de Compilatio n\'ont pas été validés ou on été mises à jour.<br> Merci d\'en prendre connaissance et de les valider pour pouvoir utiliser Compilatio.';
$string['terms_of_service_alert_btn'] = "J'ai pris connaissance des Conditions générales d'utilisation et je les accepte.";
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
$string['analysistype'] = 'Lancement des analyses';
$string['analysistype_help'] = "<p>Vous disposez de deux options :
    <ul>
        <li><strong> Manuel :</strong> L'analyse des documents doit être déclenchée manuellement via le bouton “Analyser” de chaque document ou via le bouton “Analyser tous les documents”.</li>
        <li><strong> Programmé :</strong> Tous les documents sont analysés à l'heure/date choisie.</li>
    </ul>
    Pour que tous les documents soient comparés entre eux lors des analyses, déclenchez les analyses seulement lorsque tous les documents sont présents dans le devoir.</p>";
$string['analysistype_auto'] = 'Lancement des analyses';
$string['analysistype_auto_help'] = "<p>Vous disposez de trois options :
    <ul>
        <li><strong> Manuel :</strong> L'analyse des documents doit être déclenchée manuellement via le bouton “Analyser” de chaque document ou via le bouton “Analyser tous les documents”.</li>
        <li><strong> Programmé :</strong> Tous les documents sont analysés à l'heure/date choisie.</li>
        <li><strong> Immédiat :</strong> Chaque document est analysé dès le dépôt par l’étudiant. Les documents de l’activité ne seront pas comparés entre eux.</li>
    </ul>
    Pour que tous les documents soient comparés entre eux lors des analyses, déclenchez les analyses seulement lorsque tous les documents sont présents dans le devoir.</p>";
$string['analysistype_manual'] = 'Manuel';
$string['analysistype_prog'] = 'Programmé';
$string['analysistype_auto'] = 'Immédiat';
$string['analysis_date'] = "Date d'analyse (lancement programmé uniquement)";
$string['detailed'] = 'Rapport détaillé';
$string['certificate'] = "Certificat d'analyse";
$string['reporttype'] = 'Rapport disponible pour les étudiants';
$string['reporttype_help'] = "<p>Vous disposez de deux options :</p>
<ul>
    <li><strong> Certificat d'analyse :</strong> L'étudiant aura accès au certificat d'analyse de son document.</li>
    <li><strong> Rapport détaillé :</strong> L'étudiant aura accès à la version PDF du rapport.</li>
</ul>";
$string['thresholds_settings'] = "Réglage des seuils d'affichage des taux de textes suspects :";
$string['thresholds_description'] = "Indiquez les seuils que vous souhaitez utiliser, afin de faciliter le repérage des rapports d’analyse (% de textes suspects) : ";
$string['numeric_threshold'] = "Le seuil doit être numérique.";
$string['green_threshold'] = "Vert jusqu'à";
$string['orange_threshold'] = "Orange jusqu'à";
$string['red_threshold'] = "rouge au delà";
$string['similarity_percent'] = "% de textes suspects";
$string['studentanalyses'] = "Permettre aux étudiants d'analyser leurs documents";
$string['studentanalyses_help'] = "Ceci permet aux étudiants d'analyser leur fichiers en brouillon avec Compilatio Magister, avant le rendu final à l'enseignant.";
$string['activate_submissiondraft'] = 'Pour permettre aux étudiants d\'analyser leurs brouillons, vous devez activer l\'option <b>{$a}</b> dans la partie';
$string['quiz_help'] = 'Seules les questions de type composition dont la réponse contient au moins {$a} mots seront analysés.';
$string["admin_disabled_reports"] = "L'administrateur a désactivé l'affichage des rapports de similitudes aux étudiants.";
$string['help_compilatio_format_content'] = "Compilatio prend en charge la plupart des formats utilisés en bureautique et sur Internet. Les formats suivants sont acceptés : ";
$string['max_file_size'] = 'Les fichiers ne doivent pas excéder <strong>{$a} Mo</strong>';
$string['word_limits'] = 'Pour pouvoir être analysé, un texte doit avoir entre {$a->min} et {$a->max} mots';

// Default settings page.
$string['compilatiodefaults'] = "Valeurs par défaut pour Compilatio";
$string['defaultupdated'] = "Les valeurs par défaut ont été mises à jour";
$string['defaults_desc'] = "Les paramètres suivants sont utilisés comme valeurs par défaut dans les activités de Moodle intégrant Compilatio.";

// Compilatio document frame.
$string["title_score"] = 'Analyse terminée: {$a}% de textes suspects.';
$string["title_score_teacher"] = 'Si vous avez ignoré des sources dans le rapport, cliquez ici pour mettre à jour le score.';
$string['btn_unsent'] = 'Envoyer';
$string['title_unsent'] = "Envoyer le document à Compilatio";
$string['btn_sent'] = 'Analyser';
$string['title_sent'] = "Démarrer l'analyse";
$string['btn_planned'] = "Analyse planifiée";
$string['title_planned'] = 'Ce fichier sera traité le {$a}';
$string['btn_queue'] = "Dans la file d'attente";
$string['title_queue'] = "Le document est en attente d'analyse et va bientôt être traité par Compilatio";
$string['btn_analysing'] = 'Analyse en cours';
$string['title_analysing'] = "Le fichier est en cours d'analyse par Compilatio.";
$string['btn_error_analysis_failed'] = 'Analyse échouée';
$string['title_error_analysis_failed'] = "L'analyse de ce document n'a pas fonctionné correctement.";
$string['btn_error_sending_failed'] = 'Envoi échoué';
$string['title_error_sending_failed'] = "Une erreur s'est produite lors de l'envoi du fichier à Compilatio";
$string['btn_error_unsupported'] = 'Fichier non supporté';
$string['title_error_unsupported'] = "Ce type de fichier n'est pas supporté par Compilatio";
$string['btn_error_too_large'] = 'Fichier trop volumineux';
$string['title_error_too_large'] = 'Le fichier est trop volumineux pour être traité par Compilatio. Taille maximale : {$a} Mo';
$string['btn_error_too_short'] = 'Document trop court';
$string['title_error_too_short'] = 'Ce document ne contient pas assez de mots pour être traité par Compilatio. Taille minimale : {$a} mots';
$string['btn_error_too_long'] = 'Document trop long';
$string['title_error_too_long'] = 'Ce document contient trop de mots pour être analysé. Taille maximale : {$a} mots';
$string['btn_error_not_found'] = 'Document non trouvé';
$string['title_error_not_found'] = "Ce document n'a pas été trouvé. Veuillez contacter votre administrateur de moodle. Erreur : document non trouvé pour cette clé API.";

$string['tooltip_detailed_scores'] = '% de textes suspects, dont :';
$string['similarityscore'] = 'Similitudes';
$string['utlscore'] = 'Langue non reconnue';
$string['aiscore'] = 'Texte généré par IA';
$string['unmeasured'] = 'non mesuré';
$string['ai_score_not_included'] = "non inclus dans votre abonnement";

$string['previouslysubmitted'] = "Auparavant soumis comme";
$string['student_analyse'] = "L'analyse peut être lancée par l'étudiant";
$string['student_help'] = "Vous pouvez analyser votre brouillon avec Compilatio Magister, afin de mesurer les similitudes présentes dans le texte de vos fichiers.<br/>
    Le contenu de votre brouillon ne sera pas utilisé par Compilatio comme matériel de comparaison pour les futures analyses effectuées.<br/>
    Votre enseignant aura cependant accès à ce rapport d'analyse.";
$string['failedanalysis'] = "Compilatio n'a pas réussi à analyser votre document : ";
$string['indexed_document'] = "Document ajouté à la bibliothèque de références de votre établissement. Son contenu pourra être utilisé pour détecter des similitudes avec d’autres documents.";
$string['not_indexed_document'] = "Document non ajouté à la bibliothèque de références de votre établissement. Son contenu ne sera pas utilisé pour détecter des similitudes avec d’autres documents.";
$string['extraction_in_progress'] = 'extraction du document en cours, veuillez réessayer plus tard';

// Compilatio frame.
$string['similarities_disclaimer'] = "Vous pouvez analyser les similitudes présentes dans les documents de cette activité à l’aide du logiciel <a href='http://compilatio.net' target='_blank'>Compilatio</a>.<br/>
	Attention, des similitudes mesurées lors d’une analyse ne révèlent pas nécessairement un plagiat. Le rapport d’analyse vous aide à comprendre si les similitudes correspondent à des emprunts et citations convenablement identifiés ou à des plagiats.";
$string['programmed_analysis_future'] = 'Les documents seront analysés par Compilatio le {$a}.';
$string['programmed_analysis_past'] = 'Les documents ont été soumis pour analyse à Compilatio le {$a}.';
$string['webservice_unreachable'] = "Le service Compilatio est actuellement indisponible. Veuillez nous excuser pour la gêne occasionnée.";
$string['start_all_analysis'] = "Analyser tous les documents";
$string['send_all_documents'] = "Envoyer tous les documents";
$string['reset_docs_in_error'] = 'Réinitialiser les documents en erreur';
$string["compilatio_help_assign"] = "Obtenir de l&#39aide sur le plugin Compilatio";
$string['hide_area'] = 'Masquer les informations Compilatio';
$string['show_area'] = 'Afficher les informations Compilatio';

// Detailed error status.
$string['detailed_error_unsupported'] = "Ces documents n'ont pas pu être analysés par Compilatio car leur format n'est pas supporté.";
$string['detailed_error_sending_failed'] = "Ces documents n'ont pas pu être envoyés à Compilatio. Vous pouvez renvoyer ces documents.";
$string['detailed_error_too_short'] = 'Ces documents n\'ont pas pu être analysés par Compilatio car ils ne contenaient pas assez de mots (Taille minimale : {$a} mots).';
$string['detailed_error_too_long'] = 'Ces documents n\'ont pas pu être analysés par Compilatio car ils contenaient trop de mots (Taille maximale : {$a} mots).';
$string['detailed_error_too_large'] = 'Ces documents n\'ont pas pu être analysés par Compilatio car ils sont trop volumineux (Taille maximale : {$a} Mo).';
$string['detailed_error_analysis_failed'] = "L'analyse de ces documents n'a pas fonctionné correctement. Vous pouvez réinitialiser ces documents.";
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
$string["display_notifications"] = "Afficher les notifications";
$string['no_document_available_for_analysis'] = "Aucun document n'était disponible pour analyse.";
$string["analysis_started"] = '{$a} analyses démandées.';
$string["start_analysis_in_progress"] = 'Lancement des analyses en cours';
$string["document_sent"] = '{$a} documents envoyés.';
$string["not_sent"] = "Les documents suivants n'ont pas pu être envoyés : ";
$string["send_documents_in_progress"] = 'Envoi des documents en cours';
$string["not_analyzed"] = "Les documents suivants n'ont pas pu être analysés : ";
$string["not_analyzed_extracting"] = "Les documents suivants n'ont pas pu être analysés car ils sont en cours d'extraction, veuillez réessayez plus tard";
$string["unsent_docs"] = 'Attention, cette activité contient des documents non soumis à Compilatio.';
$string['reset_docs_in_error_in_progress'] = 'Réinitialisation des documents en erreur en cours';

// Search author tab.
$string["compilatio_search_tab"] = "Rechercher le déposant d&#39un document.";
$string["compilatio_search"] = "Rechercher";
$string["compilatio_search_help"] = "Vous pouvez retrouver l'auteur d'un document en récupérant l'identifiant du document dans les sources du rapport d'analyse.";
$string["compilatio_iddocument"] = "Identifiant du document";
$string["compilatio_search_notfound"] = "Aucun document n'a été trouvé pour cet identifiant parmi les documents chargés sur votre plateforme Moodle.";
$string["compilatio_depositor"] = 'Le document présent dans l\'activité <b>{$a->modulename}</b> à été rendu par l\'utilisateur Moodle <b>{$a->lastname} {$a->firstname}</b>.';

// Assign statistics tab.
$string['tabs_title_stats'] = 'Statistiques';
$string["display_stats"] = "Afficher les statistiques de cette activité";
$string['export_csv'] = 'Exporter les données de cette activité au format CSV';
$string['progress'] = "Progression";
$string['results'] = "Résultats";
$string['errors'] = "Erreurs";
$string['analysed_docs'] = '{$a} document(s) analysés.';
$string['analysing_docs'] = '{$a} document(s) en cours d\'analyse.';
$string['queuing_docs'] = '{$a} document(s) en attente d\'analyse.';
$string['stats_min'] = 'Minimum';
$string['stats_max'] = 'Maximum';
$string['stats_avg'] = 'Moyen';
$string['stats_score'] = 'Pourcentage de similitudes';
$string['stats_error_unknown'] = ' erreurs inconnues';
$string['stats_threshold'] = 'Nombre de documents par seuil';

// Global Statistics.
$string["no_statistics_yet"] = "Aucun document n'a encore été analysé.";
$string["teacher"] = "Enseignant";
$string["activity"] = "Activité";
$string["minimum"] = 'Taux minimum';
$string["maximum"] = 'Taux maximum';
$string["average"] = 'Taux moyen';
$string["documents_number"] = 'Documents analysés';
$string["stats_errors"] = "Erreurs";
$string["export_raw_csv"] = 'Cliquez-ici pour exporter les données brutes au format CSV';
$string["export_global_csv"] = 'Cliquez-ici pour exporter ces données au format CSV';
$string["global_statistics_description"] = 'Toutes les données des documents envoyés à Compilatio.';
$string["global_statistics"] = 'Statistiques globales';
$string["activities_statistics"] = 'Statistiques par activité';
$string["similarities"] = 'Similitudes';

// Help tab.
$string['tabs_title_help'] = 'Aide';
$string['goto_compilatio_service_status'] = "Voir l'état des services Compilatio.";
$string['helpcenter'] = "Accédez au centre d'aide Compilatio pour l'utilisation du plugin Compilatio dans Moodle.";
$string['admin_goto_helpcenter'] = "Accédez au centre d'aide Compilatio pour voir des articles relatifs à l'administration du plugin Moodle.";
$string['helpcenter_error'] = "Nous ne pouvons pas vous connecter automatiquement au centre d'aide. Veuillez ré-essayer ultérieurement ou vous y rendre directement grâce au lien suivant : ";
$string['element_included_in_subscription'] = "Votre abonnement comprend : <ul><li>la détection de similitudes</li><li>la détection d'obfuscation";
$string['ai_included_in_subscription'] = "la détection de texte rédigé par IA</li></ul>";
$string['ai_not_included_in_subscription'] = "Votre abonnement ne comprend pas la détection de texte rédigé par IA.";


// Auto diagnostic page.
$string["auto_diagnosis_title"] = "Auto-diagnostic";
$string["api_key_valid"] = "La clé API enregistrée est valide.";
$string["api_key_not_tested"] = "La clé API n'a pas pû être vérifiée car la connexion au service Compilatio à échouée.";
$string["api_key_not_valid"] = "La clé API enregistrée est invalide. Elle est spécifique à la plateforme utilisée. Vous pouvez en obtenir une en contactant <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.";
$string['cron_check_never_called'] = "La tâche planifiée send_files du plugin n'a pas été exécuté depuis l'activation du plugin. Il est possible qu'il soit mal configuré.";
$string['cron_check'] = 'La tâche planifiée send_files du plugin a été exécuté le {$a} pour la dernière fois.';
$string['cron_check_not_ok'] = "La tâche planifiée send_files du plugin n'a pas été exécuté depuis plus d'une heure.";
$string['cron_frequency'] = 'Il semblerait qu\'il soit exécuté toutes les {$a} minutes.';
$string['cron_recommandation'] = "Pour les tâches planifiées du plugin Compilatio, nous recommandons d'utiliser un délai inférieur à 15 minutes entre chaque exécution.";
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
$string['manual_analysis'] = "L'analyse de ce document doit être déclenchée manuellement";

// Scheduled tasks.
$string['get_scores'] = "Récupère les taux de similitudes depuis Compilatio";
$string['update_meta'] = "Exécute les tâches planifiées par Compilatio";
$string['trigger_analyses'] = "Déclenche les analyses Compilatio"; // ADTD v2 document management.

// Report.
$string['redirect_report_failed'] = "Une erreur s'est produite lors de la récupération du rapport d'analyse. Veuillez réessayer plus tard ou contacter le support (support@compilatio.net) si le problème persiste.";
$string['download_report_failed'] = "Une erreur s'est produite lors du téléchargement du rapport d'analyse.";

// Privacy (RGPD).
$string['privacy:metadata:core_files'] = 'Fichiers déposés ou créés depuis un champ de saisie';
$string['privacy:metadata:core_plagiarism'] = 'Ce plugin est appelé par le sous-système de détection de plagiat de Moodle';

$string['privacy:metadata:plagiarism_compilatio_file'] = 'Informations à propos des fichiers soumis à Compilatio';
$string['privacy:metadata:plagiarism_compilatio_file:userid'] = "L'identifiant Moodle de l'utilisateur qui a fait la soumission";
$string['privacy:metadata:plagiarism_compilatio_file:filename'] = "Nom du fichier soumis ou nom généré pour les contenus texte";

$string['privacy:metadata:plagiarism_compilatio_user'] = "Informations à propos de l'enseignant qui a créé un module avec Compilatio";
$string['privacy:metadata:plagiarism_compilatio_user:userid'] = "L'identifiant Moodle de l'enseignant";
$string['privacy:metadata:plagiarism_compilatio_user:compilatioid'] = "L'identifiant Compilatio de l'enseignant";

$string['privacy:metadata:external_compilatio_document'] = 'Informations et contenu des documents dans la base de données de Compilatio';
$string['privacy:metadata:external_compilatio_document:authors'] = "Nom, prénom et adresse mail de l'utilisateur Moodle (ou les membres du groupe) qui a soumis le fichier";
$string['privacy:metadata:external_compilatio_document:depositor'] = "Nom, prénom et adresse mail de l'utilisateur Moodle qui a soumis le fichier";
$string['privacy:metadata:external_compilatio_document:filename'] = "Nom du fichier soumis ou nom généré pour les contenus texte";

$string['privacy:metadata:external_compilatio_user'] = "Informations à propos de l'enseignant qui a créé un module avec Compilatio";
$string['privacy:metadata:external_compilatio_user:firstname'] = "Prénom de l'enseignant";
$string['privacy:metadata:external_compilatio_user:lastname'] = "Nom de l'enseignant";
$string['privacy:metadata:external_compilatio_user:email'] = "Adresse mail de l'enseignant";
$string['privacy:metadata:external_compilatio_user:username'] = "Adresse mail de l'enseignant";
