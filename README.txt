Compilatio.net Plagiarism plugin for Moodle 3.11, 4.0, 4.1, 4.2
Other supported version (problems may occurs using these versions, we recommend upgrading Moodle to a more recent version): 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10

Author: Compilatio <support@compilatio.net>
Copyright 2020 Compilatio.net https://www.compilatio.net
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

Compilatio is a commercial Plagiarism Prevention product - you must have a paid subscription to be able to use this plugin.

CHANGES
- Fix quiz attempt throw error in version < 3.5 (may also affect other versions < 3.11)
- Update Compilatio service status link (in Compilatio container)
- Display error message returned by API on failed document upload to Compilatio
- Fix send files error when using groups in assignments with postgresql
- Compilatio container can now be minimized
- Update document's depositor on old documents
- Handling read-only API key errors
- Add global score and detailed scores for Magister+ API keys

CHANGELOG : https://support.compilatio.net/hc/en-us/articles/360019664658

QUICK INSTALL
================
1) Place these files in a new folder in your Moodle install under /plagiarism/compilatio
2) Visit the Notifications page in Moodle to trigger the upgrade scripts
3) Enable the Plagiarism API under admin > Advanced Features
4) Configure the Compilatio plugin under admin > plugins > Plagiarism > Compilatio

For more information see: http://docs.moodle.org/en/Plagiarism_Prevention
