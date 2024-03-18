Compilatio.net Plagiarism plugin for Moodle 3.11, 4.0, 4.1, 4.2, 4.3

Author: Compilatio <support@compilatio.net>
Copyright 2023 Compilatio.net https://www.compilatio.net
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

Compilatio is a commercial Plagiarism Prevention product - you must have a paid subscription to be able to use this plugin.

CHANGES

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
