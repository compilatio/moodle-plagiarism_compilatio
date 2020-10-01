Compilatio.net Plagiarism plugin for Moodle 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9

Author: Compilatio <support@compilatio.net>
Copyright 2020 Compilatio.net https://www.compilatio.net
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

Compilatio is a commercial Plagiarism Prevention product - you must have a paid subscription to be able to use this plugin.

CHANGES
- Improve plugin compatibility :
    - Replace the setting plagiarism:compilatio_use by plagiarism_compilatio:enabled.
    - Replace deprecated functions save_form_elements() by plagiarism_compilatio_coursemodule_edit_post_actions() 
        and get_form_elements_module() by plagiarism_compilatio_coursemodule_standard_elements().
- Fix bugs in plugin statistics.
- Update plugin's settings handle

QUICK INSTALL
================
1) Place these files in a new folder in your Moodle install under /plagiarism/compilatio
2) Visit the Notifications page in Moodle to trigger the upgrade scripts
3) Enable the Plagiarism API under admin > Advanced Features
4) Configure the Compilatio plugin under admin > plugins > Plagiarism > Compilatio


For more information see: http://docs.moodle.org/en/Plagiarism_Prevention
