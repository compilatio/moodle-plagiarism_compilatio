Compilatio.net Plagiarism plugin for Moodle 4.0, 4.1, 4.2, 4.3, 4.4, 4.5, 5.0, 5.1

Author: Compilatio <support@compilatio.net>
Copyright 2026 Compilatio.net https://www.compilatio.net
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

Compilatio is a commercial Plagiarism Prevention product - you must have a paid subscription to be able to use this plugin.

CHANGES
3.2.10
- Add link to Toolbox for teachers in the help tabs in compilatio frame
- [Fix] Resend attached files in quiz

3.2.9
- [Fix] Typing analysistime in course_module_settings
- [Fix] Update capability in ajax to match roles needed for actions
- Set max-width to image in marketing notifications
- Add field in administration page settings to select field used as university component in Compilatio admin page.

3.2.8
- [Fix] Document frame doesn't display in quiz
- [Fix] Stop sending annotation files

3.2.7
- [Fix] Fix error 500 (memory limit at identifier generation)
- [Fix] Fix passing null parameter to groups_get_user_groups
- [Fix] Set userid to 0 instead of null in compilatio_get_document_with_failover
- Format onlinetext before sending it to Compilatio for better analysis

3.2.6
- [Fix] Fix identifier generation
- [Fix] Fix relaunch of documents in error by changing way to retreive documents
- [Fix] Fix Compilatio in group assignment
- [Fix] Student can now see document frame in group assignment
- [Fix] Resend docs in extraction error with reset document in error in technical tools (admin tab)
- Allows ZIP files to be sent to compilatio

3.2.5
- [Fix] Student doesn't have the capability to view reports
- [Fix] Error when analysistime is null while saving a course module
- [Fix] Check if not empty search tab in compilatio_frame.php
- [Fix] Use isguestuser instead of require_capability to check if the user is not anonymously logged in Ajax
- [Fix] Add DISTINCT to unsent document retrieval to avoid errors
- [Fix] Online texts were not detected if downloaded before activation of the Compilatio plugin.
- [Fix] If a document has already been analysed but is not displayed as,
    change the status to queue to retreive the score via the API.
- [Fix] Change reserved term 'user' by PostGreSQL to 'u' in sql query in "set_depositor_and_authors"
- [Fix] Compilatio didn't work in group assignment
- [Fix] Update way to create identifier to avoid similarities in case of same content

3.2.4
- [Fix] Documents analyzed with a v2 plugin returned 404 when displaying document frame
- [Fix] Add checks on APIkey field to prevent scheduled tasks errors
- [Fix] Create user 0 in database if not exist
- [Fix] Change file status if extraction error during scheduled analyses
- [Fix] Add new extraction error type
- [Fix] Avoid warnings on module id in event handler
- [Fix] Update deprecated get_plugin_method in admin_form.php for moodle versions higher than 4.5
- [Fix] Impossible to retrieve and send to compilatio online texts if upload before plugin activations
- [Fix] require_login() vulnerability into php script if connected as anonymous user
- [Fix] Online text on assignment where not retrieve if upload before Compilatio plugin activation
- Remove recipe_name from api analysis start
- Block actions that use Compilatio API if Compilatio is under maintenance
- Rename Error management tabs into Technicals tools in admin part
- Add button into Technicals tools tabs to download compilatio database tables
- Add support contact into README.txt to report about vulnerability subjects

3.2.3
- [Fix] Enhance code quality

3.2.2
- [Fix] Fix unit tests
- [Fix] Addition of plagiarism_compilatio_cm_cfg metadata for unit tests
- [Fix] Error when resending documents in error from admin tab if a file is present multiple times
- [Fix] Old scores no longer update when switching from Magister to Magister+.

3.2.1
- Fix for Moodle instances using php7

3.2.0
- Added a tab in the administration section of the plugin to manage documents in error
- [Fix] Replace deprecated callback before_standard_top_of_body_html in Moodle 4.4 to new hook callback
    before_standard_top_of_body_html_generation
- Avoid duplicate course module settings when restoring or importing a course module
- [Fix] Reload page after update scores options for all docs
- [Fix] Prevent sending .zip files that cause bugs

v3.1.2
- Fix errors in send files with unique filepath
- Add Compilatio document id in a data- attribute

v3.1.1
- Add missing translations keys for v2 plugins

v3.1.0
- Added the option to run analyses only on selected questions in quizzes
- Added a score settings tab with the possibility to ignore score elements (AI, similarities, unrecognized languages) on all
    documents in the activity
- Small front adjustments in Compilatio container
- Fix warning and email not displayed in Compilatio activity settings
- Fix Javascript syntax error on obsolete versions of safari

v3.0.3
- Fix php exception thrown in api.php
- Fix errors when plugin update to version 3 is executed twice

v3.0.2
- Remove mandatory validation of Compilatio terms of service
- Fix unsaveable course module settings

v3.0.1
- Fix error when duplicating course modules
- Fix Moodle and plugin configuration not sent to Compilatio

v3.0.0
- The plugin now uses a Compilatio Magister account for each Moodle teacher and a Compilatio folder for each Moodle activity.
- Added the option to run analyses only on selected lines (students in assignment and attempts in quizzes).
- Quiz: possibility to display statistics by user in a new tab.
- Added marketing notifications display.
- Redesign display of Compilatio frame for documents.
- The plugin now fully uses Compilatio v5 REST API.
- Cleaning up Compilatio v4 and Soap API code management.
- Rewriting, splitting and reorganizing files.

COMPLETE CHANGELOG : https://support.compilatio.net/hc/en-us/articles/360019664658

QUICK INSTALL
================
1) Place these files in a new folder in your Moodle install under /plagiarism/compilatio
2) Visit the Notifications page in Moodle to trigger the upgrade scripts
3) Enable the Plagiarism API under admin > Advanced Features
4) Configure the Compilatio plugin under admin > plugins > Plagiarism > Compilatio


For more information see: http://docs.moodle.org/en/Plagiarism_Prevention

For all security vulnerability subject, please contact support@compilatio.net.
