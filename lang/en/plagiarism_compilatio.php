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
 * plagiarism_compilatio.php - Contains english Plagiarism plugin translation.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Compilatio plagiarism plugin';
$string['studentdisclosuredefault'] = 'All files uploaded here will be submitted to the similarities detection service Compilatio';
$string['students_disclosure'] = 'Student Disclosure';
$string['students_disclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['compilatioexplain'] = 'For more information on this plugin see: <a href="http://www.compilatio.net/en/" target="_blank">compilatio.net</a>';
$string['compilatio'] = 'Compilatio plagiarism plugin';
// API configuration.
$string['compilatioapi'] = 'Compilatio API Address';
$string['compilatioapi_help'] = 'This is the address of the Compilatio API';
$string['compilatiopassword'] = 'API key';
$string['compilatiopassword_help'] = 'Personal code provided by Compilatio to access the API';
$string['compilatiodate'] = 'Activation date';
$string['compilatiodate_help'] = 'Click "Enable" if you want this API configuration to be automatically activated on a desired date. Leave the date blank if you want to activate it right away.';
$string['apiconfiguration'] = "API configuration";
$string['formenabled'] = "Enabled";
$string['formurl'] = "API url";
$string['formapikey'] = "API key";
$string['formstartdate'] = "Activation date";
$string['formcheck'] = "Check";
$string['formdelete'] = "Delete";

$string['migration_title'] = "Migration v4 to v5";
$string['migration_info'] = "Compilatio is implementing a new v5 technical platform for all its customers.<br>
    When prompted by the technical team, you will need to perform an action to complete this migration.";
$string['migration_apikey'] = "Enter the new v5 API key";
$string['migration_btn'] = "Initiate the update of the data stored in Moodle";
$string['migration_success_doc'] = "documents have been updated";
$string['migration_form_title'] = "Launch the update of the data stored in Moodle, to complete the migration from v4 to v5.";
$string['migration_support'] = "
    <p>If all documents have not been updated correctly, please contact the Compilatio support team at support@compilatio.net and specify:</p>
    <p><<
        <br>
        An error occurred during the migration of data from v4 to v5 of a Moodle platform in our school
        <ul>
            <li>The following error message appeared: [message]</li>
            <li>[school_name]</li>
            <li>[Moodle instance name, if multiple instances used]</li>
            <li>Your API v4 key number: [____]</li>
            <li>API v5 key number: [____]</li>
            <li>Name of the contact person at the institution:</li>
            <li>Contact person's email:</li>
            <li>Contact person's phone:</li>
        </ul>
    >><p>";

$string['use_compilatio'] = 'Allow similarity detection with Compilatio';
$string['activate_compilatio'] = 'Enable Compilatio';
$string['savedconfigsuccess'] = 'Plagiarism Settings Saved';
$string['compilatio_display_student_score'] = 'Show similarity score to student';
$string['compilatio_display_student_score_help'] = 'The similarity score is the percentage of the submission that has been matched with other content.';
$string['compilatio_display_student_report'] = 'Show similarity report to student';
$string['compilatio_display_student_report_help'] = 'The similarity report gives a breakdown on what parts of the submission were plagiarised and the location of the detected sources.';
$string['showwhenclosed'] = 'When Activity closed';
$string['defaultupdated'] = 'Default values updated';
$string['defaults_desc'] = 'The following settings are the defaults set when enabling Compilatio within an Activity Module';
$string['compilatiodefaults'] = 'Compilatio defaults';
$string['compilatio:enable'] = 'Allow the teacher to enable/disable Compilatio inside an activity';
$string['compilatio:resetfile'] = 'Allow the teacher to resubmit the file to Compilatio after an error';
$string['compilatio:triggeranalysis'] = 'Allow the teacher to manually trigger analysis';
$string['compilatio:viewreport'] = 'Allow the teacher to view the full report from Compilatio';
$string['processing_doc'] = 'Compilatio is analyzing this file.';
$string['pending'] = 'This file is pending submission to Compilatio';
$string['previouslysubmitted'] = 'Previously submitted as';
$string['report'] = 'report';
$string['unknownwarning'] = 'An error occurred trying to send this file to Compilatio';
$string['unsupportedfiletype'] = 'This file type is not supported by Compilatio';
$string['toolarge'] = 'This file is too large for Compilatio to process. Maximum size : {$a->Mo} MB';
$string['tooshort'] = 'This document doesn’t contain enough words for Compilatio to process. Minimum size : {$a} words';
$string['toolong'] = 'This document contain too many words to be analyzed. Maximum size : {$a} words';
$string['failed'] = 'The analysis of this document did not work correctly.';
$string['notfound'] = 'This document was not found. Please contact your moodle administrator. Error : document not found for this API key.';
$string['compilatio_studentemail'] = 'Send Student email';
$string['compilatio_studentemail_help'] = 'This will send an e-mail to the student when a file has been processed to let them know that a report is available.';
$string['studentemailsubject'] = 'File processed by Compilatio';
$string['studentemailcontent'] = 'The file you submitted to {$a->modulename} in {$a->coursename} has now been processed by the Plagiarism tool Compilatio.
{$a->modulelink}';
$string['filereset'] = 'A file has been reset for re-submission to Compilatio';
$string['analysis_type'] = 'Analysis Start';
$string['analysis_type_help'] = '<p>There are 3 possible options:</p>
<ul>
	<li><strong>Direct: </strong>The document is sent to Compilatio and analyzed straight away.</li>
	<li><strong>Manual:</strong> The document is sent to Compilatio but the teacher must manually trigger the analysis of documents.</li>
	<li><strong>Timed: </strong>The document is sent to Compilatio and analysed at the time/date set.</li>
</ul>
<p>To enable all documents to be compared with each other, wait until all work is submitted by students then trigger the analysis.</p>';
$string['analysis'] = 'Analysis Start';
$string['analysis_help'] = '<p>There are 2 possible options:</p>
<ul>
	<li><strong>Manual:</strong> The document is sent to Compilatio but the teacher must manually trigger the analysis of documents.</li>
	<li><strong>Timed: </strong>The document is sent to Compilatio and analysed at the time/date set.</li>
</ul>
<p>To enable all documents to be compared with each other, wait until all work is submitted by students then trigger the analysis.</p>';
$string['analysistype_manual'] = 'Manual';
$string['analysistype_prog'] = 'Timed';
$string['analysis_date'] = 'Analysis Date (Timed analysis only)';
$string['enabledandworking'] = 'The Compilatio plugin is enabled and working.';
$string['subscription_state'] = '<strong>Your Compilatio.net subscription will expire at the end of {$a->end_date}. This month, you have analyzed the equivalent of {$a->used} document(s) containing less than 5,000 words.</strong>';
$string['startanalysis'] = 'Start analysis';
$string['compilatioenableplugin'] = 'Enable Compilatio for {$a}';
$string['failedanalysis'] = 'Compilatio failed to analyse your document: ';
$string['waitingforanalysis'] = 'This file will be processed on {$a}';
$string['updatecompilatioresults'] = 'Refresh the informations';
$string["update_in_progress"] = "Updating informations";
$string['updated_analysis'] = 'Compilatio analysis results have been updated.';
$string['compilatio:enable'] = 'Allow the teacher to enable/disable Compilatio inside an activity';
$string['compilatio:resetfile'] = 'Allow the teacher to resubmit the file to Compilatio after an error';
$string['compilatio:triggeranalysis'] = 'Allow the teacher to manually trigger analysis';
$string['compilatio:viewreport'] = 'Allow the teacher to view the full report from Compilatio';
$string['unextractablefile'] = 'The text in your document cannot be extracted.';
$string['immediately'] = "Immediately";
$string['quiz_help'] = 'Only essay questions whose answer contain at least {$a} words will be analyzed.';

// Auto diagnostic.
$string["auto_diagnosis_title"] = "Auto-diagnosis";
// API key.
$string["api_key_valid"] = "Your API key is valid.";
$string["api_key_not_tested"] = "Your API key haven't been verified because the connection to Compilatio.net has failed.";
$string["api_key_not_valid"] = "Your API key is not valid. It is specific to the used platform. You can obtain one by contacting <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.";
// CRON.
$string['cron_check_never_called'] = 'CRON has never been executed since the activation of the plugin. It may be misconfigured in your server.';
$string['cron_check'] = 'CRON has been executed on {$a} for the last time.';
$string['cron_check_not_ok'] = 'It hasn\'t been executed in the last hour.';
$string['cron_frequency'] = ' It seems to be run every {$a} minutes.';
$string['cron_recommandation'] = 'We recommend using a delay below 15 minutes between each CRON execution.';
// Connect to webservice.
$string['webservice_ok'] = "The server is able to connect to the web service.";
$string['webservice_not_ok'] = "The server wasn't able to connect to the web service. Your firewall may be blocking the connection.";
// Plugin enabled.
$string['plugin_enabled'] = "The plugin is enabled in the Moodle platform.";
$string['plugin_disabled'] = "The plugin isn't enabled in the Moodle platform.";
// Plugin enabled for "assign".
$string['plugin_enabled_assign'] = "The plugin is enabled for assignments.";
$string['plugin_disabled_assign'] = "The plugin isn't enabled for assignments.";
// Plugin enabled for "workshop".
$string['plugin_enabled_workshop'] = "The plugin is enabled for workshops.";
$string['plugin_disabled_workshop'] = "The plugin isn't enabled for workshops.";
// Plugin enabled for "forum".
$string['plugin_enabled_forum'] = "The plugin is enabled for forums.";
$string['plugin_disabled_forum'] = "The plugin isn't enabled for forums.";
// Plugin enabled for "quiz".
$string['plugin_enabled_quiz'] = "The plugin is enabled for quiz.";
$string['plugin_disabled_quiz'] = "The plugin isn't enabled for quiz.";
$string['programmed_analysis_future'] = 'Documents will be analyzed by Compilatio on {$a}.';
$string['programmed_analysis_past'] = 'Documents have been submitted for analysis to Compilatio on {$a}.';
$string['webservice_unreachable_title'] = "Compilatio.net is unavailable.";
$string['webservice_unreachable_content'] = "Compilatio.net is currently unavailable. We apologize for the inconvenience.";
$string['saved_config_failed'] = '<strong>The combination API key - adress entered is invalid. Compilatio is disabled, please try again.<br/>
								The <a href="autodiagnosis.php">auto-diagnosis</a> page can help you to configure this plugin.</strong><br/>
								Error : ';
$string['startallcompilatioanalysis'] = "Analyze all documents";
$string['numeric_threshold'] = "Threshold must be a number.";
$string['green_threshold'] = "Green up to";
$string['orange_threshold'] = "Orange up to";
$string['red_threshold'] = "red otherwise";
$string['similarity_percent'] = '% of similarities';
$string['thresholds_settings'] = "Limits :";
$string['thresholds_description'] = "Indicate the threshold that you want to use, in order to facilitate the finding of analysis report (% of similarities) :";
// Student submit.
$string['compi_student_analyses'] = "Allow students to analyze their documents";
$string['compi_student_analyses_help'] = "This allows students to analyze their draft files with Compilatio Magister, before final submission to the teacher.";
$string['activate_submissiondraft'] = 'To allow students to analyze their drafts, you must enable the <b>{$a}</b> option in the section';
$string['allow_student_analyses'] = "Allow teachers to enable the option \"Allow students to analyze their draft files with Compilatio Magister, before final submission to the teacher\".";
$string['student_analyze'] = "Student analysis";
$string['student_start_analyze'] = "The analysis can be started by the student";
$string['student_help'] = "You can analyze your draft with Compilatio Magister, to measure similarities in the text of your files.<br/>
The contents of your draft will not be used by Compilatio as comparison material for future analyses.<br/>
Your teacher will, however, have access to this analysis report.";

$string['similarities_disclaimer'] = "You can analyze similarities in this activity's documents with <a href='http://www.compilatio.net/en/' target='_blank'>Compilatio</a>.<br/> Be careful: similarities measured during analysis do not necessarily mean plagiarism. The analysis report helps you to identify if the similarities matched to suitable quotation or to plagiarism.";
$string['progress'] = "Progress :";
$string['results'] = "Results :";
$string['errors'] = "Errors :";
$string['documents_analyzing'] = '{$a} document(s) are being analyzed.';
$string['documents_in_queue'] = '{$a} document(s) are in the queue to be analyzed.';
$string['documents_analyzed'] = '{$a->countAnalyzed} document(s) out of {$a->documentsCount} have been sent and analyzed.';
$string['average_similarities'] = 'In this activity, the average similarities ratio is {$a}%.';
$string['documents_analyzed_lower_green'] = '{$a->documentsUnderGreenThreshold} document(s) lower than {$a->greenThreshold}%.';
$string['documents_analyzed_between_thresholds'] = '{$a->documentsBetweenThresholds} document(s) between {$a->greenThreshold}% and {$a->redThreshold}%.';
$string['documents_analyzed_higher_red'] = '{$a->documentsAboveRedThreshold} document(s) greater than {$a->redThreshold}%.';
$string['documents_notfound'] = '{$a} document(s) were not found.';
$string['documents_failed'] = '{$a} document(s) whose analysis did not work correctly.';
$string['unsupported_files'] = 'The following file(s) can\'t be analyzed by Compilatio because their format is not supported :';
$string['unextractable_files'] = 'The following file(s) can\'t be analyzed by Compilatio because their content could not be extracted :';
$string['tooshort_files'] = 'The following file(s) can\'t be analyzed by Compilatio because they doesn’t contain enough words (Minimum size : {$a} words) :';
$string['toolong_files'] = 'The following file(s) can\'t be analyzed by Compilatio because they contain too many words (Maximum size : {$a} words) :';
$string['failedanalysis_files'] = "The analysis of the following file(s) did not work correctly. You can restart these analyses :";
$string['no_document_available_for_analysis'] = 'No documents were available for analysis';
$string["analysis_started"] = '{$a} analysis have been requested.';
$string["start_analysis_title"] = 'Analysis start';
$string["start_analysis_in_progress"] = 'Launching of the analyses in progress';
$string["not_analyzed"] = "The following documents can't be analyzed :";
$string["account_expire_soon_title"] = "Your Compilatio.net account expires soon";
$string["admin_account_expire_content"] = "Your current subscription will end at the end of the current month. If your contract does not expire at the end of the month, a new subscription will automatically be set up by our services. When this is done, this message will disappear. For more information, you can contact our sales or support department at support@compilatio.net.";
$string["news_update"] = "Compilatio.net update";
$string["news_incident"] = "Compilatio.net incident";
$string["news_maintenance"] = "Compilatio.net maintenance";
$string["news_analysis_perturbated"] = "Compilatio.net - Analysis perturbated";
$string["display_stats"] = "Display statistics about this activity";
$string["analysis_completed"] = 'Analysis completed: {$a}% of similarities.';
$string["compilatio_help_assign"] = "Display help about Compilatio plugin";
$string["display_notifications"] = "Display notifications";
$string["compilatio_search_tab"] = "Find the author of a document.";
$string["compilatio_search"] = "Search";
$string["compilatio_iddocument"] = "Document identifier";
$string["compilatio_search_notfound"] = "No document was found for this identifier among the documents loaded on your Moodle platform.";
$string["compilatio_author"] = 'Le document {$a->idcourt} in activity <b>{$a->modulename}</b> belongs to <b>{$a->lastname} {$a->firstname}</b>.';
$string["compilatio_search_help"] = "You can find the author of a document by retrieving the document identifier from the sources of the analysis report. Example: 1. Your document: <b>1st5xfj2</b> - Assign_Name(30)Name_Copied_Document.odt.";
$string["allow_search_tab"] = "Activate the search tool to identify the author of a document.";
$string["allow_search_tab_help"] = "The search tool allows you to search for a student's first and last name based on a document identifier visible in the analysis reports among all the documents present on your platform.";
$string["waiting_time_title"] = "The estimated processing time for an analysis started now is ";
$string["waiting_time_content"] = 'Including {$a->queue} in queue and {$a->analysis_time} of analysis<br><br>Click <a href=\'../../plagiarism/compilatio/helpcenter.php?page=moodle-info-waiting&idgroupe=';
$string["waiting_time_content_help"] = "' target=\'_blank\'>here</a> to see best practices to follow to optimise the processing time of Compilatio analyses.";


// CSV.
$string["firstname"] = "First name";
$string["lastname"] = "Last name";
$string["filename"] = "Filename";
$string["similarities"] = "Similarities";
$string["unextractable"] = "The content of this document couldn't be extracted";
$string["unsupported"] = "Unsupported document";
$string["analysing"] = "Analysing document";
$string['timesubmitted'] = "Submitted to Compilatio on";
$string["not_analyzed_unextractable"] = '{$a} document(s) haven\'t been analysed because their content could not be extracted.';
$string["not_analyzed_unsupported"] = '{$a} document(s) haven\'t been analysed because their format isn\'t supported.';
$string["not_analyzed_tooshort"] = '{$a} document(s) haven\'t been analysed because they doesn\'t contain enough words.';
$string["not_analyzed_toolong"] = '{$a} document(s) haven\'t been analysed because they contain too many words.';
$string['export_csv'] = 'Export data about this activity into a CSV file';
$string['hide_area'] = 'Hide Compilatio informations';
$string['tabs_title_help'] = 'Help';
$string['tabs_title_stats'] = 'Statistics';
$string['tabs_title_notifications'] = 'Notifications';
$string['queued'] = 'The document is now in queue and it is going to be analyzed soon by Compilatio';
$string['no_documents_available'] = 'No documents are available for analysis in this activity.';
$string['manual_analysis'] = 'The analysis of this document must be triggered manually.';
$string['disclaimer_data'] = 'By enabling Compilatio, you accept the fact that data about your Moodle configuration will be collected in order to improve support and maintenance of this service.';
$string['reset'] = 'Reset';
$string['error'] = 'Error';
$string['analyze'] = 'Analyze';
$string['queue'] = 'Queue';
$string['analyzing'] = 'Analyzing';
$string['enable_mod_assign'] = 'Enable Compilatio for assignments (assign)';
$string['enable_mod_workshop'] = 'Enable Compilatio for workshops (workshop)';
$string['enable_mod_forum'] = 'Enable Compilatio for forums';
$string['enable_mod_quiz'] = 'Enable Compilatio for quiz';
$string['planned'] = "Planned";
$string['enable_javascript'] = "Please enable Javacript in order to have a better experience with Compilatio plugin.<br/>
 Here are the <a href='http://www.enable-javascript.com/' target='_blank'>
 instructions how to enable JavaScript in your web browser</a>.";
$string["manual_send_confirmation"] = '{$a} file(s) have been submitted to Compilatio.';
$string["unsent_documents"] = 'Document(s) not sent';
$string["unsent_documents_content"] = 'This activity contains document(s) not submitted to Compilatio.';
$string["statistics_title"] = 'Statistics';
$string["no_statistics_yet"] = 'No documents have been analyzed yet.';
$string["minimum"] = 'Minimum rate';
$string["maximum"] = 'Maximum rate';
$string["average"] = 'Average rate';
$string["documents_number"] = 'Analyzed documents';
$string["stats_errors"] = "Errors";
$string["stats_failed"] = 'Analyses failed';
$string["stats_notfound"] = 'File not found';
$string["stats_unextractable"] = 'File content could not be extracted';
$string["stats_unsupported"] = 'File format not supported';
$string["stats_tooshort"] = 'File doesn\'t contain enough words';
$string["stats_toolong"] = 'File contain too many words';
$string["export_raw_csv"] = 'Click here to export raw data in CSV format';
$string["export_global_csv"] = 'Click here to export this data in CSV format';
$string["global_statistics_description"] = 'All the documents data send to Compilatio.';
$string["global_statistics"] = 'Global statistics';
$string["assign_statistics"] = 'Statistics about assignments';
$string["similarities"] = 'Similarities';
$string["context"] = 'Context';
$string["pending_status"] = 'Pending';
$string["allow_teachers_to_show_reports"] = "Allow teachers to show similarity reports to their students";
$string["admin_disabled_reports"] = "The administrator does not allow the teachers to display the similarity reports to the students.";
$string["teacher"] = "Teacher";
$string["loading"] = "Loading, please wait...";
// ALERTS.
$string["unknownlang"] = "Caution, the language of some passages in this document was not recognized.";
$string["badqualityanalysis"] = "Issues were encountered while analysing the document. It is possible that certain sources may not have been identified, or the result may be incomplete.";
// HELP.
$string['help_compilatio_format_content'] = "Compilatio.net handles most formats used in word processors and on the internet.
The following formats are supported :";
$string['goto_compilatio_service_status'] = "See Compilatio services status.";
$string['helpcenter'] = "Access the Compilatio Help Center for the using of Compilatio plugin in Moodle.";
$string['goto_helpcenter'] = "Click on the question mark to open a new window and connect to the Compilatio Help Center.";
$string['admin_goto_helpcenter'] = "Access the Compilatio Help Center to see articles related to administration of the Moodle plugin.";
$string['helpcenter_error'] = "We can't automatically connect you to the help centre. Please try again later or go there directly using the following link : ";
// Buttons.
$string['get_scores'] = "Retrieve plagiarism scores from Compilatio.net";
$string['send_files'] = "Upload files to Compilatio.net for plagiarism detection";
$string['update_meta'] = "Perform Compilatio.net's scheduled operations";
$string['trigger_timed_analyses'] = "Trigger scheduled plagiarism analysis";
// Indexing state.
$string['indexing_state'] = "Add documents into the Document Database";
$string['indexing_state_help'] = "Yes: Add documents in the document database. These documents will be used as comparison material for future analysis.
No: Documents are not added in document database and won't be used for comparisons.";
$string['indexed_document'] = "Document added to your institution's document database. Its content may be used to detect similarities with other documents.";
$string['not_indexed_document'] = "Document not added to your institution's document database. Its content will not be used to detect similarities with other documents.";
// Information settings.
$string['information_settings'] = "Informations";
// Max file size allowed.
$string['max_file_size_allowed'] = 'Maximum document size : <strong>{$a->Mo} MB</strong>';
// Failed documents.
$string['restart_failed_analysis'] = 'Restart interrupted analysis';
$string['restart_failed_analysis_title'] = 'Restart interrupted analysis :';
$string['restart_failed_analysis_in_progress'] = 'Restart interrupted analyses in progress';
// Max attempt reached.
$string['max_attempts_reach_files'] = 'Analysis has been interrupted for the following files. Analyses were sent too many times, you cannot restart them anymore :';

// Privacy (GDPR).
$string['privacy:metadata:core_files'] = 'Files attached to submissions or created from online text submissions';
$string['privacy:metadata:core_plagiarism'] = 'This plugin is called by Moodle plagiarism subsystem';

$string['privacy:metadata:plagiarism_compilatio_files'] = 'Informations about the submissions uploaded';
$string['privacy:metadata:plagiarism_compilatio_files:id'] = 'The submission\'s ID stored in the Moodle database';
$string['privacy:metadata:plagiarism_compilatio_files:cm'] = 'The course module\'s ID where the submission is stored';
$string['privacy:metadata:plagiarism_compilatio_files:userid'] = 'The Moodle user\'s ID who made the submission';
$string['privacy:metadata:plagiarism_compilatio_files:identifier'] = 'The submission\'s contenthash';
$string['privacy:metadata:plagiarism_compilatio_files:filename'] = 'The submission\'s name (eventually auto-generated)';
$string['privacy:metadata:plagiarism_compilatio_files:timesubmitted'] = 'The timestamp when the submission was stored in the Moodle database of the plugin';
$string['privacy:metadata:plagiarism_compilatio_files:externalid'] = 'The submission\'s ID stored in the Compilatio database';
$string['privacy:metadata:plagiarism_compilatio_files:statuscode'] = 'The submission\'s status code (Analyzed, In queue, Timeout...)';
$string['privacy:metadata:plagiarism_compilatio_files:reporturl'] = 'The submission\'s URL report';
$string['privacy:metadata:plagiarism_compilatio_files:similarityscore'] = 'The submission\'s similarity score';
$string['privacy:metadata:plagiarism_compilatio_files:attempt'] = 'The number of times the user tried to analyze his submission';
$string['privacy:metadata:plagiarism_compilatio_files:errorresponse'] = 'The response if an error occurred - actually, this field is not used anymore and is automatically set to \'NULL\'';
$string['privacy:metadata:plagiarism_compilatio_files:recyclebinid'] = 'The recycle bin identifier in case the course module or course has been put in the recycle bin';
$string['privacy:metadata:plagiarism_compilatio_files:apiconfigid'] = 'The identifier of the API configuration with which the submission is linked';
$string['privacy:metadata:plagiarism_compilatio_files:idcourt'] = 'The submission\'s short ID stored in the Compilatio database';

$string['privacy:metadata:external_compilatio_document'] = 'Informations about the documents in Compilatio database';
$string['privacy:metadata:external_compilatio_document:lastname'] = 'The last name of the Compilatio user who submitted the file - beware, this user is the one linked to the Compilatio API key of the Moodle platform (so it\'s usually the administrator of the platform)';
$string['privacy:metadata:external_compilatio_document:firstname'] = 'The first name of the Compilatio user who submitted the file - beware, this user is the one linked to the Compilatio API key of the Moodle platform (so it\'s usually the administrator of the platform)';
$string['privacy:metadata:external_compilatio_document:email_adress'] = 'The email adress of the Compilatio user who submitted the file - beware, this user is the one linked to the Compilatio API key of the Moodle platform (so it\'s usually the administrator of the platform)';
$string['privacy:metadata:external_compilatio_document:user_id'] = 'The Compilatio user\'s ID who submitted the file - beware, this user is the one linked to the Compilatio API key of the Moodle platform (so it\'s usually the administrator of the platform)';
$string['privacy:metadata:external_compilatio_document:filename'] = 'The submission\'s name';
$string['privacy:metadata:external_compilatio_document:upload_date'] = 'The timestamp when the submission was stored in the Compilatio database';
$string['privacy:metadata:external_compilatio_document:id'] = 'The submission\'s ID stored in the Compilatio database';
$string['privacy:metadata:external_compilatio_document:indexed'] = 'The submission\'s indexing state (if the submission can be used to detect similarities with other documents)';

$string['privacy:metadata:external_compilatio_report'] = 'Informations about the reports in Compilatio database (only if the document has been analyzed)';
$string['privacy:metadata:external_compilatio_report:id'] = 'The Compilatio report\'s ID';
$string['privacy:metadata:external_compilatio_report:doc_id'] = 'The Compilatio submission\'s ID which was analyzed';
$string['privacy:metadata:external_compilatio_report:user_id'] = 'The Compilatio user\'s ID who submitted the file - beware, this user is the one linked to the Compilatio API key of the Moodle platform (so it\'s usually the administrator of the platform)';
$string['privacy:metadata:external_compilatio_report:start'] = 'The timestamp when the analysis started';
$string['privacy:metadata:external_compilatio_report:end'] = 'The timestamp when the analysis ended';
$string['privacy:metadata:external_compilatio_report:state'] = 'The submission\'s state (Analyzed, In queue, Timeout...)';
$string['privacy:metadata:external_compilatio_report:plagiarism_percent'] = 'The submission\'s similarity score';

$string['owner_file'] = 'GDPR and document ownership';
$string['owner_file_school'] = 'The school owns the documents';
$string['owner_file_school_details'] = 'When a student request to delete all his data, the documents and reports will be stored and available for future comparison with other documents. At the end of the contract with Compilatio, all your school\'s personnal data, including analyzed documents, are deleted within the contractual deadlines.';
$string['owner_file_student'] = 'The student is the only owner of his document';
$string['owner_file_student_details'] = 'When a student request to delete all his data, his documents and reports will be deleted from Moodle and the Compilatio document database. Documents will no longer be available for comparison with other documents.';
