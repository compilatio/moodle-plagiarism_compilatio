Compilatio.net Plagiarism plugin for Moodle 4.0, 4.1, 4.2, 4.3, 4.4

Author: Compilatio <support@compilatio.net>
Copyright 2023 Compilatio.net https://www.compilatio.net
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

Compilatio is a commercial Plagiarism Prevention product - you must have a paid subscription to be able to use this plugin.

CHANGES
3.2.4
- [Fix] Documents analyzed with a v2 plugin returned 404 when displaying document frame

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
