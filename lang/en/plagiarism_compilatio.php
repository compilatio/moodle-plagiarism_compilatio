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
$string['studentdisclosuredefault'] = 'All files uploaded here will be submitted to the plagiarism detection service Compilatio';
$string['students_disclosure'] = 'Student Disclosure';
$string['students_disclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['compilatioexplain'] = 'For more information on this plugin see: <a href="http://www.compilatio.net/en/" target="_blank">compilatio.net</a>';
$string['compilatio'] = 'Compilatio plagiarism plugin';
$string['compilatioapi'] = 'Compilatio API Address';
$string['compilatioapi_help'] = 'This is the address of the Compilatio API';
$string['compilatiopassword'] = 'API key';
$string['compilatiopassword_help'] = 'Personal code provided by Compilatio to access the API';
$string['use_compilatio'] = 'Allow similarity detection with Compilatio';
$string['activate_compilatio'] = 'Enable Compilatio';
$string['savedconfigsuccess'] = 'Plagiarism Settings Saved';
$string['compilatio_display_student_score'] = 'Show similarity score to student';
$string['compilatio_display_student_score_help'] = 'The similarity score is the percentage of the submission that has been matched with other content.';
$string['compilatio_display_student_report'] = 'Show similarity report to student';
$string['compilatio_display_student_report_help'] = 'The similarity report gives a breakdown on what parts of the submission were plagiarised and the location of the detected sources.';
$string['compilatio_draft_submit'] = 'When should the file be submitted to Compilatio';
$string['showwhenclosed'] = 'When Activity closed';
$string['submitondraft'] = 'Submit file when first uploaded';
$string['submitonfinal'] = 'Submit file when student sends for marking';
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
	<li><strong>Manual:</strong> The document is sent to Compilatio but the teacher must manually trigger the anaylyis of documents.</li>
	<li><strong>Timed: </strong>The document is sent to Compilatio and analysed at the time/date set.</li>
</ul>
<p>To enable all documents to be compared with each other, wait until all work is submitted by students then trigger the analyis.</p>
';
$string['analysistype_direct'] = 'Direct';
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
$string['unextractablefile'] = 'Your document doesn’t contain enough words, or text cannot be extracted.';
$string['immediately'] = "Immediately";
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
$string['similarities_disclaimer'] = "You can analyze similarities in this assignment's documents with <a href='http://www.compilatio.net/en/' target='_blank'>Compilatio</a>.<br/> Be careful: similarities measured during analysis do not necessarily mean plagiarism. The analysis report helps you to identify if the similarities matched to suitable quotation or to plagiarism.";
$string['progress'] = "Progress :";
$string['results'] = "Results :";
$string['errors'] = "Errors :";
$string['documents_analyzing'] = '{$a} document(s) are being analyzed.';
$string['documents_in_queue'] = '{$a} document(s) are in the queue to be analyzed.';
$string['documents_analyzed'] = '{$a->countAnalyzed} document(s) out of {$a->documentsCount} have been sent and analyzed.';
$string['average_similarities'] = 'In this assignment, the average similarities ratio is {$a}%.';
$string['documents_analyzed_lower_green'] = '{$a->documentsUnderGreenThreshold} document(s) lower than {$a->greenThreshold}%.';
$string['documents_analyzed_between_thresholds'] = '{$a->documentsBetweenThresholds} document(s) between {$a->greenThreshold}% and {$a->redThreshold}%.';
$string['documents_analyzed_higher_red'] = '{$a->documentsAboveRedThreshold} document(s) greater than {$a->redThreshold}%.';
$string['unsupported_files'] = 'The following file(s) can\'t be analyzed by Compilatio because their format is not supported :';
$string['unextractable_files'] = 'The following file(s) can\'t be analyzed by Compilatio. They either do not contain enough words or text cannot be extracted :';
$string['no_document_available_for_analysis'] = 'No documents were available for analysis';
$string["analysis_started"] = '{$a} analysis have been requested.';
$string["start_analysis_title"] = 'Analysis start';
$string["not_analyzed"] = "The following documents can't be analyzed :";
$string["account_expire_soon_title"] = "Your Compilatio.net account expires soon";
$string["account_expire_soon_content"] = "You can use Compilatio until the end of the month. If your subscription is not renewed, you will not be able to use Compilatio services after this date.";
$string["news_update"] = "Compilatio.net update";
$string["news_incident"] = "Compilatio.net incident";
$string["news_maintenance"] = "Compilatio.net maintenance";
$string["news_analysis_perturbated"] = "Compilatio.net - Analysis perturbated";
$string["display_stats"] = "Display statistics about this assignment";
$string["analysis_completed"] = 'Analysis completed: {$a}% of similarities.';
$string["compilatio_help_assign"] = "Display help about Compilatio plugin";
$string["display_notifications"] = "Display notifications";
// CSV.
$string["firstname"] = "First name";
$string["lastname"] = "Last name";
$string["filename"] = "Filename";
$string["similarities"] = "Similarities";
$string["unextractable"] = "The content of this document couldn't be extracted";
$string["unsupported"] = "Unsupported document";
$string["analysing"] = "Analysing document";
$string['timesubmitted'] = "Submitted to Compilatio on";
$string["not_analyzed_unextractable"] = '{$a} document(s) haven\'t been analysed because they didn\'t contain enough text.';
$string["not_analyzed_unsupported"] = '{$a} document(s) haven\'t been analysed because their format isn\'t supported.';
$string['export_csv'] = 'Export data about this assignment into a CSV file';
$string['hide_area'] = 'Hide Compilatio informations';
$string['tabs_title_help'] = 'Help';
$string['tabs_title_stats'] = 'Statistics';
$string['tabs_title_notifications'] = 'Notifications';
$string['queued'] = 'The document is now in queue and it is going to be analyzed soon by Compilatio';
$string['no_documents_available'] = 'No documents are available for analysis in this assignment.';
$string['manual_analysis'] = 'The analysis of this document must be triggered manually.';
$string['disclaimer_data'] = 'By enabling Compilatio, you accept the fact that data about your Moodle configuration will be collected in order to improve support and maintenance of this service.';
$string['reset'] = 'Reset';
$string['error'] = 'Error';
$string['analyze'] = 'Analyze';
$string['queue'] = 'Queue';
$string['analyzing'] = 'Analyzing';
$string['compilatio_enable_mod_assign'] = 'Enable Compilatio for assignments (assign)';
$string['compilatio_enable_mod_workshop'] = 'Enable Compilatio for workshops (workshop)';
$string['compilatio_enable_mod_forum'] = 'Enable Compilatio for forums';
$string['planned'] = "Planned";
$string['enable_javascript'] = "Please enable Javacript in order to have a better experience with Compilatio plugin.<br/>
 Here are the <a href='http://www.enable-javascript.com/' target='_blank'>
 instructions how to enable JavaScript in your web browser</a>.";
$string["manual_send_confirmation"] = '{$a} file(s) have been submitted to Compilatio.';
$string["unsent_documents"] = 'Document(s) not sent';
$string["unsent_documents_content"] = 'This assignment contains document(s) not submitted to Compilatio.';
$string["statistics_title"] = 'Statistics';
$string["no_statistics_yet"] = 'No documents have been analyzed yet.';
$string["minimum"] = 'Minimum rate';
$string["maximum"] = 'Maximum rate';
$string["average"] = 'Average rate';
$string["documents_number"] = 'Analyzed documents';
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
// HELP.
$string['help_compilatio_settings_title'] = 'Which mode should I use in the Compilatio settings of an activity?';
$string['help_compilatio_settings_content'] = '
Three analysis types are available with Compilatio plugin :
<ul>
<li>
Direct : <br/>
Each document is sent to Compilatio and analyzed as soon as it is uploaded by the student.<br/>
Recommended if you wish to get the results quickly and if it is not necessary that all documents are compared with each others.
</li>
<li>
Timed : <br/>
Choose date to start Compilatio analysis, later than the deadline for the students.<br/>
Recommended if you wish to compare all the documents of the activity mutually.
</li>
<li>
Manual : <br/>
The documents of the activity will not be analyzed until you trigger the analysis.<br/>
You can click on the “Start analysis” button of each document to trigger its analysis.
</li>
</ul>
';
$string['help_compilatio_format_content'] = "Compilatio.net handles most formats used in word processors and on the internet.
The following formats are supported :";
$string['goto_helpcenter'] = "Click on the question mark to open a new window and connect to the Compilatio Help Center.";
$string['admin_goto_helpcenter'] = "Access the Compilatio Help Center to see articles related to administration of the Moodle plugin.";
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
