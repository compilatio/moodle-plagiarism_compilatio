Compilatio.net Plagiarism plugin for Moodle 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 3.11, 4.0

Author: Compilatio <support@compilatio.net>
Copyright 2020 Compilatio.net https://www.compilatio.net
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

Compilatio is a commercial Plagiarism Prevention product - you must have a paid subscription to be able to use this plugin.

CHANGES
- Add admin setting to ignore SSL certificate verification for API calls to Compilatio.
- Replace restart failed analyses feature to reset all types of document in error.
- Update default API URL in admin settings.
- Processing the document update from v4 to v5 by a scheduled task with saving of the progress.

FIXES
- Fix documents deletion in assigns when the submission is in draft status.
- Fix subscription end date which is not displayed or not up to date (with v5).
- Improve files sending process.

QUICK INSTALL
================
1) Place these files in a new folder in your Moodle install under /plagiarism/compilatio
2) Visit the Notifications page in Moodle to trigger the upgrade scripts
3) Enable the Plagiarism API under admin > Advanced Features
4) Configure the Compilatio plugin under admin > plugins > Plagiarism > Compilatio

For more information see: http://docs.moodle.org/en/Plagiarism_Prevention
