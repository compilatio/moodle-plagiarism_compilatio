Compilatio.net Plagiarism plugin for Moodle 3.3

Author: Compilatio <support@compilatio.net>
Updated by Loïc Balleydier <loic@compilatio.net>
Copyright 2017 Compilatio.net https://www.compilatio.net
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

Compilatio is a commercial Plagiarism Prevention product - you must have a paid subscription to be able to use this plugin.

NEW FUNCTIONALITIES
======================
- Indexing document support from Moodle (Add documents into the Document Database) :
    - Global indexing : From "Site Administration > Plugins > Plagiarism > Compilatio plagiarism plugin > Compilatio defaults",
    you can now manage the indexing state of all documents sent by Moodle to Compilatio
    - Specific indexing : Directy in the activity's settings,
    you can now manage the indexing state of all documents within the specific activity
    - When you change the indexing state, only documents uploaded after the change, will be concerned
- Failover support :
    - If an analysis fails, you can restart it.
    A restart button is available on the submissions page if an analysis failed.
    Comment : restart analysis attempts aren't unlimited
- Additionnal information :
    - The maximum authorised size is now shown in the activity's settings
    - Documents extensions are now shown in the activity's settings
- Additional informations :
    - The plugin is now available for workshops !
    - The plugin's code has been updated to avoid display errors in the platform pages
    - We fixed general and display bugs


QUICK INSTALL
================
1) Place these files in a new folder in your Moodle install under /plagiarism/compilatio
2) Visit the Notifications page in Moodle to trigger the upgrade scripts
3) Enable the Plagiarism API under admin > Advanced Features
4) Configure the Compilatio plugin under admin > plugins > Plagiarism > Compilatio


For more information see: http://docs.moodle.org/en/Plagiarism_Prevention
