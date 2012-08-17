Compilatio.net Plagiarism plugin for Moodle 2

Author: Dan Marsden <dan@danmarsden.com>
Copyright 2012 Dan Marsden http://danmarsden.com
License: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

Compilatio is a commercial Plagiarism Prevention product - you must have a paid subscription to be able to use this plugin.

KNOWN ISSUE WITH Moodle 2.0-2.2 (fixed in 2.3)
================
When running cron this error occurs:
PHP Fatal error:  plagiarism_plugin ::event_handler(): The script tried to execute a method or access a property
of an incomplete object. Please ensure that the class definition "stored_file" of the object you are trying to
operate on was loaded _before_ unserialize() gets called or provide a __autoload() function to load the class definition

The fix is to make a change to lib/cronlib.php. -find these lines:
    mtrace('Starting processing the event queue...');
    events_cron();
    mtrace('done.');
and replace them with this:
    mtrace('Starting processing the event queue...');
    require_once($CFG->libdir.'/filelib.php');
    events_cron();
    mtrace('done.');

QUICK INSTALL
================
1) Place these files in a new folder in your Moodle install under /plagiarism/compilatio
2) Visit the Notifications page in Moodle to trigger the upgrade scripts
3) Enable the Plagiarism API under admin > Advanced Features
4) Configure the Compilatio plugin under admin > plugins > Plagiarism > Compilatio


For more information see: http://docs.moodle.org/en/Plagiarism_Prevention
