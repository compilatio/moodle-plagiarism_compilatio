<?php
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');

use plagiarism_compilatio\compilatio\csv_generator;

require_login();
admin_externalpage_setup('plagiarismcompilatio');
$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, 'nopermissions');

csv_generator::generate_database_data_csv();