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
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Compilatio plagiarism plugin';
$string['compilatio'] = 'Compilatio plagiarism plugin';

// Admin Compilatio settings.
$string['activate_compilatio'] = 'Enable Compilatio';
$string['disclaimer_data'] = 'By enabling Compilatio, you accept the fact that data about your Moodle configuration will be collected in order to improve support and maintenance of this service.';
$string['studentdisclosuredefault'] = 'All files uploaded here will be submitted to the suspicious texts detection service Compilatio';
$string['students_disclosure'] = 'Student Disclosure';
$string['students_disclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['compilatioexplain'] = 'For more information on this plugin see: <a href="http://www.compilatio.net/en/" target="_blank">compilatio.net</a>';
$string['apikey'] = 'API key';
$string['apikey_help'] = 'Personal code provided by Compilatio to access the API';
$string['enabledandworking'] = 'The Compilatio plugin is enabled and working.';
$string['wrong_apikey_type'] = 'The API key entered is not valid, if you have recently updated the version of your Compilatio plugin contact support (support@compilatio.net) to obtain a new key';
$string['saved_config_failed'] = '<strong>The combination API key - adress entered is invalid. Compilatio is disabled, please try again.<br/>
    The <a href="autodiagnosis.php">auto-diagnosis</a> page can help you to configure this plugin.</strong><br/>
    Error :';
$string['read_only_apikey'] = 'Your read-only API key does not allow uploading or analysing documents.';
$string['subscription'] = '<b>Informations about your subscription:</b>';
$string['subscription_start'] = 'Start date:';
$string['subscription_end'] = 'End date included:';
$string['subscription_analysis_count'] = 'Analysed documents: {$a->usage} of {$a->value}';
$string['subscription_analysis_page_count'] = 'Analysed pages: {$a->usage} of {$a->value}';
$string['disable_ssl_verification'] = "Ignore SSL certificate verification.";
$string['disable_ssl_verification_help'] = "Enable this option if you have problems verifying SSL certificates or if you experience errors when sending files to Compilatio.";
$string["teacher_features_title"] = "Features enabled for teachers";
$string["enable_show_reports"] = "Possibility to show analysis reports to students";
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
$string['owner_file_school_details'] = 'When a student request to delete all his data, the documents and reports will be stored and available for future comparison with other documents. At the end of the contract with Compilatio, all your school\'s personnal data, including analysed documents, are deleted within the contractual deadlines.';
$string['owner_file_student'] = 'The student is the only owner of his document';
$string['owner_file_student_details'] = 'When a student request to delete all his data, his documents and reports will be deleted from Moodle and the Compilatio document database. Documents will no longer be available for comparison with other documents.';

// Activity settings.
$string['info_cm_activation'] = 'By activating Compilatio on this activity, submitted documents will be uploaded to your Compilatio account {$a}.<br>All teachers enrolled in this course will be able to use Compilatio on this activity.';
$string['info_cm_activated'] = 'Documents submitted in this activity are uploaded to the Compilatio account {$a}.<br>All teachers enrolled in this course can use Compilatio on this activity.';
$string['terms_of_service_info'] = '<a href=\'{$a}\'>Terms of Service</a> of Compilatio';
$string['activated'] = 'Allow suspicious texts detection with Compilatio';
$string['defaultindexing'] = "Add documents into the Document Database";
$string['defaultindexing_help'] = "Yes: Add documents in the document database. These documents will be used as comparison material for future analysis.
    No: Documents are not added in document database and won't be used for comparisons.";
$string['showstudentscore'] = 'Show suspicious texts score to student';
$string['showstudentscore_help'] = 'The suspicious texts score is the percentage of the submission that may potentially not be authentic.';
$string['showstudentreport'] = 'Show analysis report to student';
$string['showstudentreport_help'] = 'The analysis report gives a breakdown on what parts of the submission were plagiarised and the location of the detected sources.';
$string['immediately'] = "Immediately";
$string['showwhenclosed'] = 'When Activity closed';
$string['analysistype'] = 'Analysis Start';
$string['analysistype_help'] = "<p>You have two options:
    <ul>
        <li><strong>Manual:</strong> Analysis of documents must be triggered manually with the “Analyse” button of each document or with the “Analyse all documents” button.</li>
        <li><strong>Scheduled: </strong> All documents are analysed at the selected time/date.</li>
    </ul>
    To have all documents compared with each other during the analyses, wait until all works are submitted by students then trigger the analyses.</p>";
$string['analysistype_auto_help'] = "<p>You have three options:
    <ul>
        <li><strong>Manual:</strong> Analysis of documents must be triggered manually with the “Analyse” button of each document or with the “Analyse all documents” button.</li>
        <li><strong>Scheduled: </strong> All documents are analysed at the selected time/date.</li>
        <li><strong>Direct: </strong> Each document is analysed as soon as the student submits it. The documents in the activity will not be compared to each other.</li>
    </ul>
    To have all documents compared with each other during the analyses, wait until all works are submitted by students then trigger the analyses.</p>";
$string['analysistype_manual'] = 'Manual';
$string['analysistype_prog'] = 'Scheduled';
$string['analysistype_auto'] = 'Direct';
$string['analysis_date'] = 'Analysis Date (Scheduled analysis only)';
$string['detailed'] = 'Detailed report';
$string['certificate'] = 'Analysis certificate';
$string['reporttype'] = 'Report available for students';
$string['reporttype_help'] = "<p>There are 2 possible options :</p>
<ul>
    <li><strong> Analysis certificate :</strong> The student will have access to his document's analysis certificate.</li>
    <li><strong> Detailed report :</strong> The student will have access to the report PDF version.</li>
</ul>";
$string['thresholds_settings'] = "Limits :";
$string['thresholds_description'] = "Indicate the threshold that you want to use, in order to facilitate the finding of analysis report (% of suspicious texts) :";
$string['numeric_threshold'] = "Threshold must be a number.";
$string['green_threshold'] = "Green up to";
$string['orange_threshold'] = "Orange up to";
$string['red_threshold'] = "red otherwise";
$string['similarity_percent'] = '% of suspicious texts';
$string['studentanalyses'] = "Allow students to analyse their documents";
$string['studentanalyses_help'] = "This allows students to analyse their draft files with Compilatio Magister, before final submission to the teacher.";
$string['activate_submissiondraft'] = 'To allow students to analyse their drafts, you must enable the <b>{$a}</b> option in the section';
$string['quiz_help'] = 'Only essay questions whose answer contain at least {$a} words will be analysed.';
$string["admin_disabled_reports"] = "The administrator does not allow the teachers to display the analysis reports to the students.";
$string['help_compilatio_format_content'] = "Compilatio handles most formats used in word processors and on the internet. The following formats are supported : ";
$string['max_file_size'] = 'Files must not exceed <strong>{$a} MB</strong>';
$string['word_limits'] = 'To be able to be analysed, a text must have between {$a->min} and {$a->max} word';

// Default settings page.
$string['compilatiodefaults'] = 'Compilatio defaults';
$string['defaultupdated'] = 'Default values updated';
$string['defaults_desc'] = 'The following settings are the defaults set when enabling Compilatio within an Activity Module';

// Compilatio document frame.
$string["title_score"] = 'Analysis completed: {$a}% of suspicious texts.';
$string["title_score_teacher"] = 'If you have ignored any sources in the report, click here to update the score.';
$string['btn_unsent'] = 'Send';
$string['title_unsent'] = "Send the document to Compilatio";
$string['btn_sent'] = 'Analyse';
$string['title_sent'] = "Start analysis";
$string['btn_planned'] = "Analysis planned";
$string['title_planned'] = 'This file will be processed on {$a}';
$string['btn_queue'] = 'In the queue';
$string['title_queue'] = "The document is now in queue and it is going to be analysed soon by Compilatio";
$string['btn_analysing'] = 'Analysis in progress';
$string['title_analysing'] = "Compilatio is analysing this file.";
$string['btn_error_analysis_failed'] = 'Analysis failed';
$string['title_error_analysis_failed'] = "The analysis of this document did not work correctly.";
$string['btn_error_sending_failed'] = 'Sending failed';
$string['title_error_sending_failed'] = "An error occurred trying to send this file to Compilatio";
$string['btn_error_unsupported'] = 'File not supported';
$string['title_error_unsupported'] = 'This file type is not supported by Compilatio';
$string['btn_error_too_large'] = 'File too large';
$string['title_error_too_large'] = 'This file is too large for Compilatio to process. Maximum size : {$a} MB';
$string['btn_error_too_short'] = 'Document too short';
$string['title_error_too_short'] = 'This document doesn’t contain enough words for Compilatio to process. Minimum size : {$a} words';
$string['btn_error_too_long'] = 'Document too long';
$string['title_error_too_long'] = 'This document contain too many words to be analysed. Maximum size : {$a} words';
$string['btn_error_not_found'] = 'Document not found';
$string['title_error_not_found'] = 'This document was not found. Please contact your moodle administrator. Error : document not found for this API key.';

$string['tooltip_detailed_scores'] = '% of suspect texts, including:';
$string['simscore'] = 'Similarities';
$string['utlscore'] = 'Language not recognised';
$string['aiscore'] = 'AI-generated text';
$string['unmeasured'] = 'not measured';
$string['ai_score_not_included'] = "not included in your subscription";
$string['excluded_from_score'] = "Excluded from the score:";

$string['student_analyse'] = "The analysis can be started by the student";
$string['student_help'] = "You can analyse your draft with Compilatio Magister, to measure similarities in the text of your files.<br/>
    The contents of your draft will not be used by Compilatio as comparison material for future analyses.<br/>
    Your teacher will, however, have access to this analysis report.";
$string['failedanalysis'] = 'Compilatio failed to analyse your document: ';
$string['indexed_document'] = "Document added to your institution's document database. Its content may be used to detect similarities with other documents.";
$string['not_indexed_document'] = "Document not added to your institution's document database. Its content will not be used to detect similarities with other documents.";
$string['extraction_in_progress'] = 'document extraction in progress, please try again later';

// Compilatio frame.
$string['similarities_disclaimer'] = "You can analyse suspicious texts in this activity's documents with <a href='http://www.compilatio.net/en/' target='_blank'>Compilatio</a>.<br/>
    Be careful: suspicious texts measured during analysis do not necessarily mean plagiarism. The analysis report helps you to identify if the suspicious texts matched to suitable quotation or to plagiarism.";
$string['programmed_analysis_future'] = 'Documents will be analysed by Compilatio on {$a}.';
$string['programmed_analysis_past'] = 'Documents have been submitted for analysis to Compilatio on {$a}.';
$string['webservice_unreachable'] = "Compilatio is currently unavailable. We apologize for the inconvenience.";
$string['start_all_analysis'] = "Analyse all documents";
$string['send_all_documents'] = "Send all document";
$string['reset_docs_in_error'] = 'Reset documents in error';
$string["compilatio_help_assign"] = "Display help about Compilatio plugin";
$string['start_selected_files_analysis'] = 'Analyse selected documents';
$string['start_selected_questions_analysis'] = 'Analyse selected questions';
$string['access_report'] = 'Access report';
$string["other_analysis_options"] = 'Other analysis options';

// Detailed error status.
$string['detailed_error_unsupported'] = "These documents could not be analysed by Compilatio because their format is not supported.";
$string['detailed_error_sending_failed'] = "These documents could not be sent to Compilatio. You can resend these documents.";
$string['detailed_error_too_short'] = 'These documents could not be analysed by Compilatio because they didn\'t contain enough words (Minimum size: {$a} words).';
$string['detailed_error_too_long'] = 'These documents could not be analysed by Compilatio because they contained too many words (Maximum size: {$a} words).';
$string['detailed_error_too_large'] = 'These documents could not be analysed by Compilatio because they are too large (Maximum size: {$a} MB).';
$string['detailed_error_analysis_failed'] = "The analysis of these documents didn't work correctly. You can reset these documents.";
$string['detailed_error_not_found'] = "These documents were not found. Please contact your Moodle administrator. Error : document not found for this API key.";

// Short error status.
$string['short_error_not_found'] = 'documents not found.';
$string['short_error_analysis_failed'] = 'failed analyses.';
$string["short_error_sending_failed"] = "sending failed.";
$string["short_error_unsupported"] = 'documents unsupported.';
$string["short_error_too_short"] = 'documents too short.';
$string["short_error_too_long"] = 'documents too long.';
$string["short_error_too_large"] = 'documents too large.';

// Notifications tab.
$string["notifications"] = "Notifications";
$string["see_all_notifications"] = "See all notifications";
$string["open"] = "Open";
$string["no_notification"] = "No notification";
$string["display_notifications"] = "Display notifications";
$string["display_settings_frame"] = "Display settings for scores";
$string['no_document_available_for_analysis'] = 'No documents were available for analysis';
$string["analysis_started"] = '{$a} analysis have been requested.';
$string["start_analysis_in_progress"] = 'Launching of the analyses in progress';
$string["document_sent"] = '{$a} documents successfully sent.';
$string["analyses_restarted"] = '{$a} analyses successfully restarted.';
$string["not_sent"] = "The following documents couldn't be sent: ";
$string["send_documents_in_progress"] = 'Sending documents in progress';
$string["not_analyzed"] = "The following documents couldn't be analysed: ";
$string["not_analyzed_extracting"] = "The following documents can't be analysed because they are being extracted, please try again later";
$string["unsent_docs"] = 'This activity contains documents not submitted to Compilatio.';
$string['reset_docs_in_error_in_progress'] = 'Reset of documents in error in progress';

// Search author tab.
$string["compilatio_search_tab"] = "Find the depositor of a document.";
$string["compilatio_search"] = "Search";
$string["compilatio_search_help"] = "You can find the depositor of a document by retrieving the document identifier from the sources of the analysis report.";
$string["compilatio_iddocument"] = "Document identifier";
$string["compilatio_search_notfound"] = "No document was found for this identifier among the documents loaded on your Moodle platform.";
$string["compilatio_depositor"] = 'The document in activity <b>{$a->modulename}</b> was submitted by the Moodle user <b>{$a->lastname} {$a->firstname}</b>.';

// Settings scores tab.
$string["include_percentage_in_suspect_text"] = 'Include in the percentage of suspect texts displayed :';
$string["simscore_percentage"] = 'Percentage of similarities';
$string["aiscore_percentage"] = 'Percentage of text potentially written by AI';
$string["utlscore_percentage"] = 'Percentage of unrecognized types of languages';
$string["score_settings_info"] = 'Updating scores will affect all analysed documents in the assignment,<br> including those modified individually.';

// Assign statistics tab.
$string['tabs_title_stats'] = 'Statistics';
$string["display_stats"] = "Display statistics about this activity";
$string["display_stats_per_student"] = "Display statistics per student about this activity";
$string['export_csv'] = 'Export data about this activity into a CSV file';
$string['export_csv_per_student'] = 'Export this student\'s results to a CSV file';
$string['progress'] = "Progress";
$string['results'] = "Results";
$string['errors'] = "Errors";
$string['analysed_docs'] = '{$a} analysed document(s)';
$string['analysing_docs'] = '{$a} document(s) being analysed';
$string['queuing_docs'] = '{$a} document(s) awaiting analysis';
$string['stats_min'] = 'Minimum';
$string['stats_max'] = 'Maximum';
$string['stats_avg'] = 'Average';
$string['stats_score'] = 'Suspicious texts percentage';
$string['stats_error_unknown'] = ' unknown errors';
$string['stats_threshold'] = 'Number of documents per threshold';
$string['results_by_student'] = 'Results by student';
$string['previous_student'] = 'Previous student';
$string['next_student'] = 'Next student';
$string['suspect_words_quiz_on_total'] = 'words suspect / <br>total words';
$string['suspect_words/total_words'] = 'words suspect / total words';
$string['score'] = 'Score';
$string['word'] = 'words';
$string['total'] = 'Total';
$string['globalscore'] = 'Total';
$string['no_students_finished_quiz'] = 'No students finished the quiz';
$string['select_a_student'] = 'Select a student';
$string['response_type'] = 'Response type';
$string['file'] = 'File';
$string['text'] = 'Text';
$string['no_document_to_display'] = 'No documents to display';
$string['student'] = 'Student';
$string["not_analysed"] = 'not analysed';
$string['no_document_analysed'] = 'No documents analysed';

// Global Statistics.
$string["no_statistics_yet"] = 'No documents have been analysed yet.';
$string["teacher"] = "Teacher";
$string["activity"] = "Activity";
$string["minimum"] = 'Minimum rate';
$string["maximum"] = 'Maximum rate';
$string["average"] = 'Average rate';
$string["documents_number"] = 'Analysed documents';
$string["stats_errors"] = "Errors";
$string["export_raw_csv"] = 'Click here to export raw data in CSV format';
$string["export_global_csv"] = 'Click here to export this data in CSV format';
$string["global_statistics_description"] = 'All the documents data send to Compilatio.';
$string["global_statistics"] = 'Global statistics';
$string["activities_statistics"] = 'Statistics by activity';
$string["similarities"] = 'Suspicious texts';

// Help tab.
$string['tabs_title_help'] = 'Help';
$string['goto_compilatio_service_status'] = "See Compilatio services status.";
$string['helpcenter'] = "Access the Compilatio Help Center for the using of Compilatio plugin in Moodle.";
$string['admin_goto_helpcenter'] = "Access the Compilatio Help Center to see articles related to administration of the Moodle plugin.";
$string['helpcenter_error'] = "We can't automatically connect you to the help centre. Please try again later or go there directly using the following link : ";
$string['element_included_in_subscription'] = "Your subscription includes: <ul><li>similarity detection</li><li>altered texts detection";
$string['ai_included_in_subscription'] = "detection of text generated by AI</li></ul>";
$string['ai_not_included_in_subscription'] = "Your subscription does not include AI text detection.";

// Error management tab.
$string['tabs_title_error_management'] = "Error management";
$string['restart_failed_analyses'] = "Restart failed analyses";
$string['resend_document_in_error'] = "Resend documents in error";
$string['reset_documents_in_error'] = "Reset documents in error";
$string['no_documents_to_reset'] = "No documents to reset";
$string['document_reset_failures'] = '{$a} document reset failures';

// Auto diagnostic page.
$string["auto_diagnosis_title"] = "Auto-diagnosis";
$string["api_key_valid"] = "Your API key is valid.";
$string["api_key_not_tested"] = "Your API key haven't been verified because the connection to Compilatio has failed.";
$string["api_key_not_valid"] = "Your API key is not valid. It is specific to the used platform. You can obtain one by contacting (ent@compilatio.net).";
$string['cron_check_never_called'] = 'Plugin scheduled tasks get_scores has never been executed since the activation of the plugin. It may be misconfigured in your server.';
$string['cron_check'] = 'Plugin scheduled tasks get_scores has been executed on {$a} for the last time.';
$string['cron_check_not_ok'] = 'Plugin scheduled tasks get_scores hasn\'t been executed in the last hour.';
$string['cron_frequency'] = ' It seems to be run every {$a} minutes.';
$string['cron_recommandation'] = 'For Compilatio plugin scheduled tasks, we recommend using a delay below 15 minutes between each execution.';
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
$string['manual_analysis'] = 'The analysis of this document must be triggered manually.';

// Scheduled tasks.
$string['get_scores'] = "Retrieve plagiarism scores from Compilatio";
$string['update_meta'] = "Perform Compilatio's scheduled operations";
$string['trigger_analyses'] = "Trigger Compilatio analyses";

// Report.
$string['redirect_report_failed'] = "An error occurred while retrieving the analysis report. Please try again later or contact support (support@compilatio.net) if the problem persists.";
$string['download_report_failed'] = "An error occurred while downloading the analysis report.";

// Privacy (GDPR).
$string['privacy:metadata:core_files'] = 'Files attached to submissions or created from online text submissions';
$string['privacy:metadata:core_plagiarism'] = 'This plugin is called by Moodle plagiarism subsystem';

$string['privacy:metadata:plagiarism_compilatio_files'] = "Information about files submitted to Compilatio";
$string['privacy:metadata:plagiarism_compilatio_files:userid'] = "The Moodle ID of the user who made the submission";
$string['privacy:metadata:plagiarism_compilatio_files:filename'] = "Name of file submitted or generated name for online text";

$string['privacy:metadata:plagiarism_compilatio_user'] = "Information about the teacher who created a course module with Compilatio";
$string['privacy:metadata:plagiarism_compilatio_user:userid'] = "The Moodle ID of the teacher";
$string['privacy:metadata:plagiarism_compilatio_user:compilatioid'] = "The Compilatio ID of the teacher";

$string['privacy:metadata:external_compilatio_document'] = 'Information and content of the documents in Compilatio database';
$string['privacy:metadata:external_compilatio_document:authors'] = 'First name, last name and email of the Moodle user (or members of group) who submitted the file';
$string['privacy:metadata:external_compilatio_document:depositor'] = 'First name, last name and email of the Moodle user who submitted the file';
$string['privacy:metadata:external_compilatio_document:filename'] = "Name of file submitted or generated name for online text";

$string['privacy:metadata:external_compilatio_user'] = 'Information about the teacher who created a course module with Compilatio';
$string['privacy:metadata:external_compilatio_user:firstname'] = 'First name of the teacher';
$string['privacy:metadata:external_compilatio_user:lastname'] = 'Last name of the teacher';
$string['privacy:metadata:external_compilatio_user:email'] = 'Email of the teacher';
$string['privacy:metadata:external_compilatio_user:username'] = 'Email of the teacher';

// Legacy strings for plugin v2.
$string['read_only_apikey_title'] = 'Read-only API key.';
$string['read_only_apikey_error'] = 'Your read-only API key does not allow uploading or analyzing documents.';
$string['compilatioapi'] = 'Compilatio API Address';
$string['compilatioapi_help'] = 'This is the address of the Compilatio API';
$string['compilatiopassword'] = 'API key';
$string['compilatiopassword_help'] = 'Personal code provided by Compilatio to access the API';
$string['compilatiodate'] = 'Activation date';
$string['compilatiodate_help'] = 'Click "Enable" if you want this API configuration to be automatically activated on a desired date. Leave the date blank if you want to activate it right away.';
$string['apiconfiguration'] = 'API configuration';
$string['formenabled'] = 'Enabled';
$string['formurl'] = 'API url';
$string['formapikey'] = 'API key';
$string['formstartdate'] = 'Activation date';
$string['formcheck'] = 'Check';
$string['formdelete'] = 'Delete';
$string['migration_title'] = 'Migration v4 to v5';
$string['migration_info'] = 'Compilatio is implementing a new v5 technical platform for all its customers.<br>
    When prompted by the technical team, you will need to perform an action to complete this migration.';
$string['migration_np'] = 'You can use the Compilatio plugin even if the migration is not finished.';
$string['migration_apikey'] = 'Enter the new v5 API key';
$string['migration_btn'] = 'Initiate the update of the data stored in Moodle';
$string['migration_completed'] = 'Update completed:';
$string['migration_toupdate_doc'] = 'documents to update';
$string['migration_success_doc'] = 'documents have been updated';
$string['migration_failed_doc'] = 'document couldn\'t be updated, you can try again to update of these documents at the end of the update';
$string['migration_restart'] = 'Retry';
$string['migration_inprogress'] = 'Update in progress, it can take several hours <small>(you can exit this page during the update)</small>';
$string['migration_form_title'] = 'Launch the update of the data stored in Moodle, to complete the migration from v4 to v5.';
$string['use_compilatio'] = 'Allow similarity detection with Compilatio';
$string['savedconfigsuccess'] = 'Plagiarism Settings Saved';
$string['compilatio_display_student_score'] = 'Show similarity score to student';
$string['compilatio_display_student_score_help'] = 'The similarity score is the percentage of the submission that has been matched with other content.';
$string['compilatio_display_student_report'] = 'Show similarity report to student';
$string['compilatio_display_student_report_help'] = 'The similarity report gives a breakdown on what parts of the submission were plagiarised and the location of the detected sources.';
$string['compilatio:resetfile'] = 'Allow the teacher to resubmit the file to Compilatio after an error';
$string['processing_doc'] = 'Compilatio is analyzing this file.';
$string['pending'] = 'This file is pending submission to Compilatio';
$string['previouslysubmitted'] = 'Previously submitted as';
$string['report'] = 'report';
$string['unknownwarning'] = 'An error occurred trying to send this file to Compilatio';
$string['unsupportedfiletype'] = 'This file type is not supported by Compilatio';
$string['toolarge'] = 'This file is too large for Compilatio to process. Maximum size : {$a->Mo} MB';
$string['tooshort'] = 'This document doesn’t contain enough words for Compilatio to process. Minimum size : {$a} words';
$string['toolong'] = 'This document contain too many words to be analysed. Maximum size : {$a} words';
$string['failed'] = 'The analysis of this document did not work correctly.';
$string['notfound'] = 'This document was not found. Please contact your moodle administrator. Error : document not found for this API key.';
$string['compilatio_studentemail'] = 'Send Student email';
$string['compilatio_studentemail_help'] = 'This will send an email to the student when a file has been processed to let them know that a report is available.';
$string['studentemailsubject'] = 'File processed by Compilatio';
$string['studentemailcontent'] = 'The file you submitted to {$a->modulename} in {$a->coursename} has now been processed by the Plagiarism tool Compilatio.       ' . "\n" . '{$a->modulelink}';
$string['filereset'] = 'A file has been reset for re-submission to Compilatio';
$string['analysis'] = 'Analysis Start';
$string['analysis_help'] = '<p>You have two options:
    <ul>
        <li><strong>Manual:</strong> Analysis of documents must be triggered manually with the “Analyse” button of each document or with the “Analyse all documents” button.</li>
        <li><strong>Scheduled: </strong> All documents are analysed at the selected time/date.</li>
    </ul>
    To have all documents compared with each other during the analyses, wait until all works are submitted by students then trigger the analyses.</p>';
$string['analysis_auto'] = 'Analysis Start';
$string['analysis_auto_help'] = '<p>You have three options:
    <ul>
        <li><strong>Manual:</strong> Analysis of documents must be triggered manually with the “Analyse” button of each document or with the “Analyse all documents” button.</li>
        <li><strong>Scheduled: </strong> All documents are analysed at the selected time/date.</li>
        <li><strong>Direct: </strong> Each document is analysed as soon as the student submits it. The documents in the activity will not be compared to each other.</li>
    </ul>
    To have all documents compared with each other during the analyses, wait until all works are submitted by students then trigger the analyses.</p>';
$string['subscription_will_expire'] = 'Your Compilatio subscription will expire at the end of';
$string['startanalysis'] = 'Start analysis';
$string['compilatioenableplugin'] = 'Enable Compilatio for {$a}';
$string['waitingforanalysis'] = 'This file will be processed on {$a}';
$string['updatecompilatioresults'] = 'Refresh the informations';
$string['update_in_progress'] = 'Updating informations';
$string['updated_analysis'] = 'Compilatio analysis results have been updated.';
$string['unextractablefile'] = 'This document could not be loaded on Compilatio.';
$string['sending_failed'] = 'File upload to Compilatio failed {$a}';
$string['allow_analyses_auto'] = 'Possibility to start the analyses directly';
$string['allow_analyses_auto_help'] = 'This option will allow teachers to activate the automatic launch of documents analysis on an activity (i.e. immediately after they have been submitted).<br>
Note that in this case:
<ul>
    <li>The number of scans performed by your institution may be significantly higher.</li>
    <li>The documents of the first submitters are not compared with the documents of the last depositors.</li>
</ul>
In order to compare all the documents of an assignement, it is necessary to use the “scheduled” analysis, by choosing a date after the submission deadline.';
$string['webservice_unreachable_title'] = 'Compilatio is unavailable.';
$string['webservice_unreachable_content'] = 'Compilatio is currently unavailable. We apologize for the inconvenience.';
$string['startallcompilatioanalysis'] = 'Analyse all documents';
$string['compi_student_analyses'] = 'Allow students to analyse their documents';
$string['compi_student_analyses_help'] = 'This allows students to analyse their draft files with Compilatio Magister, before final submission to the teacher.';
$string['allow_student_analyses'] = 'Possibility to enable student analysis on drafts.';
$string['allow_student_analyses_help'] = 'This option will allow teachers to activate on an activity the analysis by students of their documents submitted in draft mode with Compilatio Magister, before final submission to the teacher.';
$string['student_analyze'] = 'Student analysis';
$string['student_start_analyze'] = 'The analysis can be started by the student';
$string['documents_analyzing'] = '{$a} document(s) are being analysed.';
$string['documents_in_queue'] = '{$a} document(s) are in the queue to be analysed.';
$string['documents_analyzed'] = '{$a->countAnalyzed} document(s) out of {$a->documentsCount} have been sent and analysed.';
$string['average_similarities'] = 'In this activity, the average suspicious texts ratio is {$a}%.';
$string['documents_analyzed_lower_green'] = '{$a->documentsUnderGreenThreshold} document(s) lower than {$a->greenThreshold}%.';
$string['documents_analyzed_between_thresholds'] = '{$a->documentsBetweenThresholds} document(s) between {$a->greenThreshold}% and {$a->redThreshold}%.';
$string['documents_analyzed_higher_red'] = '{$a->documentsAboveRedThreshold} document(s) greater than {$a->redThreshold}%.';
$string['documents_notfound'] = '{$a} document(s) were not found.';
$string['documents_failed'] = '{$a} document(s) whose analysis did not work correctly.';
$string['unsupported_files'] = 'The following file(s) can\'t be analysed by Compilatio because their format is not supported :';
$string['unextractable_files'] = 'The following file(s) can\'t be analysed because they could not be loaded on Compilatio :';
$string['tooshort_files'] = 'The following file(s) can\'t be analysed by Compilatio because they doesn’t contain enough words (Minimum size : {$a} words) :';
$string['toolong_files'] = 'The following file(s) can\'t be analysed by Compilatio because they contain too many words (Maximum size : {$a} words) :';
$string['failedanalysis_files'] = 'The analysis of the following documents did not work correctly. You can reset these documents and re-launch their analysis:';
$string['start_analysis_title'] = 'Analysis start';
$string['account_expire_soon_title'] = 'Your Compilatio account expires soon';
$string['admin_account_expire_content'] = 'Your current subscription will end at the end of the current month. If your contract does not expire at the end of the month, a new subscription will automatically be set up by our services. When this is done, this message will disappear. For more information, you can contact our sales or support department at support@compilatio.net.';
$string['news_update'] = 'Compilatio update';
$string['news_incident'] = 'Compilatio incident';
$string['news_maintenance'] = 'Compilatio maintenance';
$string['news_analysis_perturbated'] = 'Compilatio - Analysis perturbated';
$string['analysis_completed'] = 'Analysis completed: {$a}% of suspicious texts.';
$string['compilatio_author'] = 'The document {$a->idcourt} in activity <b>{$a->modulename}</b> belongs to <b>{$a->lastname} {$a->firstname}</b>.';
$string['allow_search_tab'] = 'Search tool to identify the author of a document.';
$string['allow_search_tab_help'] = 'The search tool allows you to search for a student\'s first and last name based on a document identifier visible in the analysis reports among all the documents present on your platform.';
$string['unextractable'] = 'The document could not be loaded on Compilatio';
$string['unsupported'] = 'Unsupported document';
$string['analysing'] = 'Analysing document';
$string['not_analyzed_unextractable'] = '{$a} document(s) haven\'t been analysed because they could not be loaded on Compilatio.';
$string['not_analyzed_unsupported'] = '{$a} document(s) haven\'t been analysed because their format isn\'t supported.';
$string['not_analyzed_tooshort'] = '{$a} document(s) haven\'t been analysed because they doesn\'t contain enough words.';
$string['not_analyzed_toolong'] = '{$a} document(s) haven\'t been analysed because they contain too many words.';
$string['hide_area'] = 'Hide Compilatio informations';
$string['show_area'] = 'Show Compilatio informations';
$string['tabs_title_notifications'] = 'Notifications';
$string['queued'] = 'The document is now in queue and it is going to be analysed soon by Compilatio';
$string['no_documents_available'] = 'No documents are available for analysis in this activity.';
$string['reset'] = 'Reset';
$string['error'] = 'Error';
$string['analyze'] = 'Analyse';
$string['queue'] = 'Queue';
$string['analyzing'] = 'Analyzing';
$string['planned'] = 'Planned';
$string['enable_javascript'] = 'Please enable Javacript in order to have a better experience with Compilatio plugin.<br/>
 Here are the <a href="http://www.enable-javascript.com/" target="_blank">
 instructions how to enable JavaScript in your web browser</a>.';
$string['manual_send_confirmation'] = '{$a} file(s) have been submitted to Compilatio.';
$string['unsent_documents'] = 'Document(s) not sent';
$string['unsent_documents_content'] = 'This activity contains document(s) not submitted to Compilatio.';
$string['statistics_title'] = 'Statistics';
$string['stats_failed'] = 'Analyses failed';
$string['stats_notfound'] = 'File not found';
$string['stats_unextractable'] = 'File could not be loaded on Compilatio';
$string['stats_unsupported'] = 'File format not supported';
$string['stats_tooshort'] = 'File doesn\'t contain enough words';
$string['stats_toolong'] = 'File contain too many words';
$string['assign_statistics'] = 'Statistics about assignments';
$string['context'] = 'Context';
$string['pending_status'] = 'Pending';
$string['allow_teachers_to_show_reports'] = 'Possibility to show similarity reports to students';
$string['loading'] = 'Loading, please wait...';
$string['unknownlang'] = 'Caution, the language of some passages in this document was not recognized.';
$string['badqualityanalysis'] = 'Issues were encountered while analysing the document. It is possible that certain sources may not have been identified, or the result may be incomplete.';
$string['goto_helpcenter'] = 'Click on the question mark to open a new window and connect to the Compilatio Help Center.';
$string['send_files'] = 'Upload files to Compilatio for plagiarism detection';
$string['migration_task'] = 'Update documents from v4 to v5';
$string['indexing_state'] = 'Add documents into the Document Database';
$string['indexing_state_help'] = 'Yes: Add documents in the document database. These documents will be used as comparison material for future analysis.
No: Documents are not added in document database and won\'t be used for comparisons.';
$string['information_settings'] = 'Informations';
$string['max_file_size_allowed'] = 'Maximum document size : <strong>{$a->Mo} MB</strong>';
$string['reset_failed_document'] = 'Reset documents in error';
$string['reset_failed_document_title'] = 'Reset documents in error:';
$string['reset_failed_document_in_progress'] = 'Reset documents in error in progress';
$string['max_attempts_reach_files'] = 'Analysis has been interrupted for the following files. Analyses were sent too many times, you cannot restart them anymore :';
