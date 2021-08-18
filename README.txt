Compilatio.net Plagiarism plugin for Moodle 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 3.11

Author: Compilatio <support@compilatio.net>
Copyright 2020 Compilatio.net https://www.compilatio.net
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

Compilatio is a commercial Plagiarism Prevention product - you must have a paid subscription to be able to use this plugin.

CHANGES
- Add support for essay question in quiz (Moodle 3.11).
- Add error handling for documents with too many words.

FIXES
- Fix SQL Query to search document author for PostgresSQL.
- Fix SQL Query to export global statistics to CSV for PostgresSQL.
- Fix function to get not uploaded documents in order to analyse all documents in assign retroactively.
- Fix document not automatically indexed in reference library.
- Fix calls to unset "nb_mots_min" setting.

QUICK INSTALL
================
1) Place these files in a new folder in your Moodle install under /plagiarism/compilatio
2) Visit the Notifications page in Moodle to trigger the upgrade scripts
3) Enable the Plagiarism API under admin > Advanced Features
4) Configure the Compilatio plugin under admin > plugins > Plagiarism > Compilatio


For more information see: http://docs.moodle.org/en/Plagiarism_Prevention