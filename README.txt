Compilatio.net Plagiarism plugin for Moodle 3.11, 4.0, 4.1, 4.2
Other supported version (problems may occurs using these versions, we recommend upgrading Moodle to a more recent version): 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10

Author: Compilatio <support@compilatio.net>
Copyright 2020 Compilatio.net https://www.compilatio.net
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

Compilatio is a commercial Plagiarism Prevention product - you must have a paid subscription to be able to use this plugin.

CHANGES
- Update Compilatio container to minimize display size and support Moove theme
- Add conversion from markdown to html in notifications
- Fix Auto-diagnosis message "Invalid API key" when plugin is disabled
- Fix error "coding problem..." displayed when starting an analysis
- Fix : no more display empty notifications
- Fix in quiz : not all texts are sent to Compilatio if their contents are identical.
- English translation corrections / Update some language strings
- Fix insert null value error in function get_account_expiration_date
- Fix warning "get_record return more than one record" in function get_non_uploaded_documents
- Fix warning caused by deprecated setting compilatio_use not removed

CHANGELOG : https://support.compilatio.net/hc/en-us/articles/360019664658

QUICK INSTALL
================
1) Place these files in a new folder in your Moodle install under /plagiarism/compilatio
2) Visit the Notifications page in Moodle to trigger the upgrade scripts
3) Enable the Plagiarism API under admin > Advanced Features
4) Configure the Compilatio plugin under admin > plugins > Plagiarism > Compilatio

For more information see: http://docs.moodle.org/en/Plagiarism_Prevention
