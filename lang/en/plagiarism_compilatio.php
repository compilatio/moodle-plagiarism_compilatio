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
$string['compilatio'] = 'Compilatio plagiarism plugin';

// Admin Compilatio settings.
$string['activate_compilatio'] = 'Enable Compilatio';
$string['disclaimer_data'] = 'By enabling Compilatio, you accept the fact that data about your Moodle configuration will be collected in order to improve support and maintenance of this service.';
$string['studentdisclosuredefault'] = 'All files uploaded here will be submitted to the similarities detection service Compilatio';
$string['students_disclosure'] = 'Student Disclosure';
$string['students_disclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['compilatioexplain'] = 'For more information on this plugin see: <a href="http://www.compilatio.net/en/" target="_blank">compilatio.net</a>';
$string['apikey'] = 'API key';
$string['apikey_help'] = 'Personal code provided by Compilatio to access the API';
$string['enabledandworking'] = 'The Compilatio plugin is enabled and working.';
$string['saved_config_failed'] = '<strong>The combination API key - adress entered is invalid. Compilatio is disabled, please try again.<br/>
    The <a href="autodiagnosis.php">auto-diagnosis</a> page can help you to configure this plugin.</strong><br/>
    Error :';
$string['subscription'] = '<b>Informations about your subscription:</b>';
$string['subscription_start'] = 'Start date:';
$string['subscription_end'] = 'End date included:';
$string['subscription_analysis_count'] = 'Analysed documents: {$a->usage} of {$a->value}';
$string['subscription_analysis_page_count'] = 'Analysed pages: {$a->usage} of {$a->value}';
$string['disable_ssl_verification'] = "Ignore SSL certificate verification.";
$string['disable_ssl_verification_help'] = "Enable this option if you have problems verifying SSL certificates or if you experience errors when sending files to Compilatio.";
$string["teacher_features_title"] = "Features enabled for teachers";
$string["enable_show_reports"] = "Possibility to show similarity reports to students";
$string['enable_student_analyses'] = "Possibility to enable student analysis on drafts.";
$string['enable_student_analyses_help'] = "This option will allow teachers to activate on an activity the analysis by students of their documents submitted in draft mode with Compilatio Magister, before final submission to the teacher.";
$string["enable_search_tab"] = "Search tool to identify the author of a document.";
$string["enable_search_tab_help"] = "The search tool allows you to search for a student's first and last name based on a document identifier visible in the analysis reports among all the documents present on your platform.";
$string["enable_analyses_auto"] = "Possibility to start the analyses directly";
$string["enable_analyses_auto_help"] = "This option will allow teachers to activate the automatic launch of documents analysis on an activity (i.e. immediately after they have been submitted).<br>
Note that in this case : 
<ul>
    <li>The number of scans performed by your institution may be significantly higher.</li>
    <li>The documents of the first submitters are not compared with the documents of the last depositors.</li>
</ul>
In order to compare all the documents of an assignement, it is necessary to use the “scheduled” analysis, by choosing a date after the submission deadline.";
$string["enable_activities_title"] = "Enable Compilatio for activities";
$string['enable_mod_assign'] = 'Assignments';
$string['enable_mod_workshop'] = 'Workshops';
$string['enable_mod_forum'] = 'Forums';
$string['enable_mod_quiz'] = 'Quiz';
$string['document_deleting'] = "Documents deletion";
$string['keep_docs_indexed'] = "Keep documents in reference library";
$string['keep_docs_indexed_help'] = "When deleting a course, resetting a course or deleting an activity, you can choose to permanently delete the documents sent to Compilatio or to keep them in the reference library (only the text will be kept and will be used as comparison material in your next analyses)";
$string['owner_file'] = 'GDPR and document ownership';
$string['owner_file_school'] = 'The school owns the documents';
$string['owner_file_school_details'] = 'When a student request to delete all his data, the documents and reports will be stored and available for future comparison with other documents. At the end of the contract with Compilatio, all your school\'s personnal data, including analyzed documents, are deleted within the contractual deadlines.';
$string['owner_file_student'] = 'The student is the only owner of his document';
$string['owner_file_student_details'] = 'When a student request to delete all his data, his documents and reports will be deleted from Moodle and the Compilatio document database. Documents will no longer be available for comparison with other documents.';

// Activity settings.
$string['terms_of_service'] = 'I have read the <a href=\'{$a}\'>Terms of Service</a> of Compilatio and I accept them.';
$string['terms_of_service_info'] = '<a href=\'{$a}\'>Terms of Service</a> of Compilatio';
$string['terms_of_service_alert'] = 'The Compilatio <a href=\'{$a}\'>Terms of Service</a> have not been validated or have been updated. Please read and accept them before using Compilatio.';
$string['terms_of_service_alert_btn'] = "I have read the Terms of Service and I accept them.";
$string['activated'] = 'Allow similarity detection with Compilatio';
$string['defaultindexing'] = "Add documents into the Document Database";
$string['defaultindexing_help'] = "Yes: Add documents in the document database. These documents will be used as comparison material for future analysis.
    No: Documents are not added in document database and won't be used for comparisons.";
$string['showstudentscore'] = 'Show similarity score to student';
$string['showstudentscore_help'] = 'The similarity score is the percentage of the submission that has been matched with other content.';
$string['showstudentreport'] = 'Show similarity report to student';
$string['showstudentreport_help'] = 'The similarity report gives a breakdown on what parts of the submission were plagiarised and the location of the detected sources.';
$string['immediately'] = "Immediately";
$string['showwhenclosed'] = 'When Activity closed';
$string['studentemail'] = 'Send Student email';
$string['studentemail_help'] = 'This will send an e-mail to the student when a file has been processed to let them know that a report is available.';
$string['analysistype'] = 'Analysis Start';
$string['analysistype_help'] = "<p>You have two options:
    <ul>
        <li><strong>Manual:</strong> Analysis of documents must be triggered manually with the “Analyze” button of each document or with the “Analyze all documents” button.</li>
        <li><strong>Scheduled: </strong> All documents are analyzed at the selected time/date.</li>
    </ul>
    To have all documents compared with each other during the analyses, wait until all works are submitted by students then trigger the analyses.</p>";
$string['analysistype_auto'] = 'Analysis Start';
$string['analysistype_auto_help'] = "<p>You have three options:
    <ul>
        <li><strong>Manual:</strong> Analysis of documents must be triggered manually with the “Analyze” button of each document or with the “Analyze all documents” button.</li>
        <li><strong>Scheduled: </strong> All documents are analyzed at the selected time/date.</li>
        <li><strong>Direct: </strong> Each document is analyzed as soon as the student submits it. The documents in the activity will not be compared to each other.</li>
    </ul>
    To have all documents compared with each other during the analyses, wait until all works are submitted by students then trigger the analyses.</p>";
$string['analysistype_manual'] = 'Manual';
$string['analysistype_prog'] = 'Scheduled';
$string['analysistype_auto'] = 'Direct';
$string['analysis_date'] = 'Analysis Date (Scheduled analysis only)';
$string['thresholds_settings'] = "Limits :";
$string['thresholds_description'] = "Indicate the threshold that you want to use, in order to facilitate the finding of analysis report (% of similarities) :";
$string['numeric_threshold'] = "Threshold must be a number.";
$string['warningthreshold'] = "Green up to";
$string['criticalthreshold'] = "Orange up to";
$string['red_threshold'] = "red otherwise";
$string['similarity_percent'] = '% of similarities';
$string['studentanalyses'] = "Allow students to analyze their documents";
$string['studentanalyses_help'] = "This allows students to analyze their draft files with Compilatio Magister, before final submission to the teacher.";
$string['activate_submissiondraft'] = 'To allow students to analyze their drafts, you must enable the <b>{$a}</b> option in the section';
$string['quiz_help'] = 'Only essay questions whose answer contain at least {$a} words will be analyzed.';
$string["admin_disabled_reports"] = "The administrator does not allow the teachers to display the similarity reports to the students.";
$string['help_compilatio_format_content'] = "Compilatio.net handles most formats used in word processors and on the internet. The following formats are supported : ";
$string['max_file_size_allowed'] = 'Files must not exceed <strong>{$a} MB</strong>';
$string['min_max_word_required'] = 'To be able to be analyzed, a text must have between {$a->min} and {$a->max} word';

// Default settings page.
$string['compilatiodefaults'] = 'Compilatio defaults';
$string['defaultupdated'] = 'Default values updated';
$string['defaults_desc'] = 'The following settings are the defaults set when enabling Compilatio within an Activity Module';

// Compilatio button.
//$string["title_scored"] = 'Analysis completed: {$a}% of similarities.';
$string['btn_sent'] = 'Analyze';
$string['title_sent'] = "Start analysis";
$string['btn_planned'] = "Planned";
$string['title_planned'] = 'This file will be processed on {$a}';
$string['btn_queue'] = 'Queue';
$string['title_queue'] = "The document is now in queue and it is going to be analyzed soon by Compilatio";
$string['btn_analyzing'] = 'Analyzing';
$string['title_analyzing'] = "Compilatio is analyzing this file.";
$string['btn_error_analysis_failed'] = 'Restart';
$string['title_error_analysis_failed'] = "The analysis of this document did not work correctly.";
$string['btn_error_sending_failed'] = 'Resend';
$string['title_error_sending_failed'] = "An error occurred trying to send this file to Compilatio";
$string['btn_error'] = 'Error';
$string['title_error_unsupported'] = "This file type is not supported by Compilatio";
$string['title_error_too_large'] = 'This file is too large for Compilatio to process. Maximum size : {$a} MB';
$string['title_error_too_short'] = 'This document doesn’t contain enough words for Compilatio to process. Minimum size : {$a} words';
$string['title_error_too_long'] = 'This document contain too many words to be analyzed. Maximum size : {$a} words';
$string['title_error_not_found'] = "This document was not found. Please contact your moodle administrator. Error : document not found for this API key.";

$string['previouslysubmitted'] = 'Previously submitted as';
$string['students_analyze'] = "The analysis can be started by the student";
$string['student_help'] = "You can analyze your draft with Compilatio Magister, to measure similarities in the text of your files.<br/>
    The contents of your draft will not be used by Compilatio as comparison material for future analyses.<br/>
    Your teacher will, however, have access to this analysis report.";
$string['failedanalysis'] = 'Compilatio failed to analyse your document: ';
$string['indexed_document'] = "Document added to your institution's document database. Its content may be used to detect similarities with other documents.";
$string['not_indexed_document'] = "Document not added to your institution's document database. Its content will not be used to detect similarities with other documents.";

// Student email.
$string['studentemailsubject'] = 'File processed by Compilatio';
$string['studentemailcontent'] = 'The file you submitted to {$a->modulename} in {$a->coursename} has now been processed by the Plagiarism tool Compilatio.
    {$a->modulelink}';

// Compilatio frame.
$string['similarities_disclaimer'] = "You can analyze similarities in this activity's documents with <a href='http://www.compilatio.net/en/' target='_blank'>Compilatio</a>.<br/>
    Be careful: similarities measured during analysis do not necessarily mean plagiarism. The analysis report helps you to identify if the similarities matched to suitable quotation or to plagiarism.";
$string['programmed_analysis_future'] = 'Documents will be analyzed by Compilatio on {$a}.';
$string['programmed_analysis_past'] = 'Documents have been submitted for analysis to Compilatio on {$a}.';
$string['webservice_unreachable_title'] = "Compilatio.net is unavailable.";
$string['webservice_unreachable_content'] = "Compilatio.net is currently unavailable. We apologize for the inconvenience.";
$string['startallcompilatioanalysis'] = "Analyze all documents";
$string['updatecompilatioresults'] = 'Refresh the informations';
$string['restart_failed_analysis'] = 'Restart interrupted analysis';
$string["compilatio_help_assign"] = "Display help about Compilatio plugin";
$string['hide_area'] = 'Hide Compilatio informations';

// Detailed error status.
$string['detailed_error_unsupported'] = "These documents could not be analyzed by Compilatio because their format is not supported.";
$string['detailed_error_sending_failed'] = "Ces documents n'ont pas pu être envoyés à Compilatio. Vous pouvez revoyer ces documents.";
$string['detailed_error_too_short'] = 'These documents could not be analyzed by Compilatio because they didn\'t contain enough words (Minimum size: {$a} words).';
$string['detailed_error_too_long'] = 'These documents could not be analyzed by Compilatio because they contained too many words (Maximum size: {$a} words).';
$string['detailed_error_too_large'] = 'These documents could not be analyzed by Compilatio because they are too large (Maximum size: {$a} MB).';
$string['detailed_error_analysis_failed'] = "The analysis of these documents didn't work correctly. You can restart these analyses.";
$string['detailed_error_not_found'] = "These documents were not found. Please contact your Moodle administrator. Error : document not found for this API key.";

// Short error status.
$string['short_error_not_found'] = 'documents not found.';
$string['short_error_analysis_failed'] = 'failed analyses.';
$string["short_error_sending_failed"] = "sending failed.";
$string["short_error_unsupported"] = 'unsupported documents.';
$string["short_error_too_short"] = 'documents too short.';
$string["short_error_too_long"] = 'documents too long.';
$string["short_error_too_large"] = 'documents too large.';

// Notifications tab.
$string['tabs_title_notifications'] = 'Notifications';
$string["display_notifications"] = "Display notifications";
$string['max_attempts_reach_files'] = 'Analysis has been interrupted for the following files. Analyses were sent too many times, you cannot restart them anymore :';
$string['no_document_available_for_analysis'] = 'No documents were available for analysis';
$string["analysis_started"] = '{$a} analysis have been requested.';
$string["start_analysis_title"] = 'Analysis start';
$string["start_analysis_in_progress"] = 'Launching of the analyses in progress';
$string["not_analyzed"] = "The following documents can't be analyzed :";
$string["update_in_progress"] = "Updating informations";
$string["unsent_documents"] = 'Document(s) not sent';
$string["unsent_documents_content"] = 'This activity contains document(s) not submitted to Compilatio.';
$string['restart_failed_analysis_title'] = 'Restart interrupted analysis :';
$string['restart_failed_analysis_in_progress'] = 'Restart interrupted analyses in progress';

// Search author tab.
$string["compilatio_search_tab"] = "Find the depositor of a document.";
$string["compilatio_search"] = "Search";
$string["compilatio_search_help"] = "You can find the depositor of a document by retrieving the document identifier from the sources of the analysis report. Example: 1. Your document: <b>1st5xfj2</b> - Assign_Name(30)Name_Copied_Document.odt.";
$string["compilatio_iddocument"] = "Document identifier";
$string["compilatio_search_notfound"] = "No document was found for this identifier among the documents loaded on your Moodle platform.";
$string["compilatio_author"] = 'The document in activity <b>{$a->modulename}</b> was submitted by the Moodle user <b>{$a->lastname} {$a->firstname}</b>.';

// Assign statistics tab.
$string['tabs_title_stats'] = 'Statistics';
$string["display_stats"] = "Display statistics about this activity";
$string['export_csv'] = 'Export data about this activity into a CSV file';
$string['progress'] = "Progress";
$string['results'] = "Results";
$string['errors'] = "Errors";
$string['documents_analyzed'] = '{$a} analyzed documents';
$string['documents_analyzing'] = '{$a} documents being analyzed';
$string['documents_in_queue'] = '{$a} documents awaiting analysis';
$string["stats_min"] = 'Minimum';
$string["stats_max"] = 'Maximum';
$string["stats_avg"] = 'Average';
$string['stats_score'] = 'Similarities percentage';
$string["stats_error_empty"] = "No errors detected";
$string["stats_error_unknown"] = " unknown errors";
$string['stats_threshold'] = 'Number of documents per threshold';
$string['no_documents_available'] = 'No documents are available for analysis in this activity.';

// Global Statistics.
$string["no_statistics_yet"] = 'No documents have been analyzed yet.';
$string["teacher"] = "Teacher";
$string["minimum"] = 'Minimum rate';
$string["maximum"] = 'Maximum rate';
$string["average"] = 'Average rate';
$string["documents_number"] = 'Analyzed documents';
$string["stats_errors"] = "Errors";
$string["export_raw_csv"] = 'Click here to export raw data in CSV format';
$string["export_global_csv"] = 'Click here to export this data in CSV format';
$string["global_statistics_description"] = 'All the documents data send to Compilatio.';
$string["global_statistics"] = 'Global statistics';
$string["activities_statistics"] = 'Statistics about activities';
$string["similarities"] = 'Similarities';

// Help tab.
$string['tabs_title_help'] = 'Help';
$string['goto_compilatio_service_status'] = "See Compilatio services status.";
$string['helpcenter'] = "Access the Compilatio Help Center for the using of Compilatio plugin in Moodle.";
$string['admin_goto_helpcenter'] = "Access the Compilatio Help Center to see articles related to administration of the Moodle plugin.";
$string['helpcenter_error'] = "We can't automatically connect you to the help centre. Please try again later or go there directly using the following link : ";

// Auto diagnostic page.
$string["auto_diagnosis_title"] = "Auto-diagnosis";
$string["api_key_valid"] = "Your API key is valid.";
$string["api_key_not_tested"] = "Your API key haven't been verified because the connection to Compilatio.net has failed.";
$string["api_key_not_valid"] = "Your API key is not valid. It is specific to the used platform. You can obtain one by contacting <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.";
$string['cron_check_never_called'] = 'CRON has never been executed since the activation of the plugin. It may be misconfigured in your server.';
$string['cron_check'] = 'CRON has been executed on {$a} for the last time.';
$string['cron_check_not_ok'] = 'It hasn\'t been executed in the last hour.';
$string['cron_frequency'] = ' It seems to be run every {$a} minutes.';
$string['cron_recommandation'] = 'We recommend using a delay below 15 minutes between each CRON execution.';
$string['webservice_ok'] = "The server is able to connect to the web service.";
$string['webservice_not_ok'] = "The server wasn't able to connect to the web service. Your firewall may be blocking the connection.";
$string['plugin_enabled'] = "The plugin is enabled in the Moodle platform.";
$string['plugin_disabled'] = "The plugin isn't enabled in the Moodle platform.";
$string['plugin_enabled_assign'] = "The plugin is enabled for assignments.";
$string['plugin_disabled_assign'] = "The plugin isn't enabled for assignments.";
$string['plugin_enabled_workshop'] = "The plugin is enabled for workshops.";
$string['plugin_disabled_workshop'] = "The plugin isn't enabled for workshops.";
$string['plugin_enabled_forum'] = "The plugin is enabled for forums.";
$string['plugin_disabled_forum'] = "The plugin isn't enabled for forums.";
$string['plugin_enabled_quiz'] = "The plugin is enabled for quiz.";
$string['plugin_disabled_quiz'] = "The plugin isn't enabled for quiz.";

// Capabilities.
$string['compilatio:enable'] = 'Allow the teacher to enable/disable Compilatio inside an activity';
$string['compilatio:triggeranalysis'] = 'Allow the teacher to manually trigger analysis';
$string['compilatio:viewreport'] = 'Allow the teacher to view the full report from Compilatio';

// CSV.
$string["firstname"] = "First name";
$string["lastname"] = "Last name";
$string["filename"] = "Filename";
$string['timesubmitted'] = "Submitted to Compilatio on";
$string["similarities_rate"] = "Similarities rate";
$string['manual_analysis'] = 'The analysis of this document must be triggered manually.';

// Scheduled tasks.
$string['get_scores'] = "Retrieve plagiarism scores from Compilatio.net";
$string['send_files'] = "Upload files to Compilatio.net for plagiarism detection";
$string['update_meta'] = "Perform Compilatio.net's scheduled operations";
$string['trigger_timed_analyses'] = "Trigger scheduled plagiarism analysis";

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
$string['privacy:metadata:plagiarism_compilatio_files:status'] = 'The submission\'s status (Scored, In queue, Error...)';
$string['privacy:metadata:plagiarism_compilatio_files:similarityscore'] = 'The submission\'s similarity score';
$string['privacy:metadata:plagiarism_compilatio_files:attempt'] = 'The number of times the user tried to analyze his submission';
$string['privacy:metadata:plagiarism_compilatio_files:indexed'] = 'The submission\'s indexing state';

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
$string['privacy:metadata:external_compilatio_report:state'] = 'The submission\'s state (Analyzed, In queue, Error...)';
$string['privacy:metadata:external_compilatio_report:plagiarism_percent'] = 'The submission\'s similarity score';
