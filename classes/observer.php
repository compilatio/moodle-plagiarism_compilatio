<?php

defined('MOODLE_INTERNAL') || die();

class plagiarism_compilatio_observer {

    public static function forum_file_uploaded(
    \mod_forum\event\assessable_uploaded $event) {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        event_handler($event->get_data());
    }

    public static function workshop_file_uploaded(
    \mod_workshop\event\assessable_uploaded $event) {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        event_handler($event->get_data());
    }

    public static function assignsubmission_onlinetext_uploaded(
    \assignsubmission_onlinetext\event\assessable_uploaded $event) {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        event_handler($event->get_data(), false, true);
    }

    public static function assignsubmission_file_uploaded(
    \assignsubmission_file\event\assessable_uploaded $event) {
        global $CFG;
        require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
        event_handler($event->get_data(), true, false);
    }

}
