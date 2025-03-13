<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * admin_tab_technical_tools.php
 *
 * @package   plagiarism_compilatio
 * @author    Compilatio <support@compilatio.net>
 * @copyright 2024 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/admin_forms.php');

use plagiarism_compilatio\compilatio\file;
use plagiarism_compilatio\compilatio\api;
use plagiarism_compilatio\compilatio\analysis;

require_login();
admin_externalpage_setup('plagiarismcompilatio');
$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, 'nopermissions');

$mform = new compilatio_restart_form();

if (($data = $mform->get_data()) && confirm_sesskey()) {
    $alerts = [];

    $compilatio = new api();

    $select = "status = ? AND timesubmitted BETWEEN ? AND ?";

    if ($data->reset === 'documents') {
        // Send documents whose analysis failed and documents whose send failed.
        $files = $DB->get_records_select(
            'plagiarism_compilatio_files',
            $select,
            ['error_sending_failed', $data->startdate, $data->enddate]
        );

        $string = 'document_sent';
    } else if ($data->reset === 'analyses') {
        // Delete documents whose analysis failed.
        $files = $DB->get_records_select(
            'plagiarism_compilatio_files',
            $select,
            ['error_analysis_failed', $data->startdate, $data->enddate]
        );

        if (!empty($files)) {
            // Check if files have been automatically relaunched and analyzed.
            foreach ($files as $key => $cmpfile) {
                $cmpfile = analysis::check_analysis($cmpfile);

                if ($cmpfile->status !== 'error_analysis_failed') {
                    unset($files[$key]);
                }
            }

            compilatio_delete_files($files);
        }

        $string = 'analyses_restarted';
    }

    if (!empty($files)) {
        $countsuccess = 0;

        foreach ($files as $cmpfile) {
            if (file::retrieve_and_send_file($cmpfile, $data->reset === 'analyses')) {
                $countsuccess++;
            }
        }

        $alerts[] = [
            'class' => 'info',
            'content' => get_string($string, 'plagiarism_compilatio', $countsuccess),
        ];

        if ($countsuccess < count($files)) {
            $alerts[] = [
                'class' => 'danger',
                'content' => '<div>'
                    . get_string('document_reset_failures', 'plagiarism_compilatio', count($files) - $countsuccess)
                    . '</div>',
            ];
        }
    } else {
        $alerts[] = [
            'class' => 'info',
            'content' => '<div>' . get_string('no_documents_to_reset', 'plagiarism_compilatio') . '</div>',
        ];
    }
}

echo $OUTPUT->header();

$currenttab = 'compilatioadmintest';
require_once($CFG->dirroot . '/plagiarism/compilatio/admin_tabs.php');

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');

echo '<h3>' . get_string('reset_documents_in_error', 'plagiarism_compilatio') . '</h3>';

$mform->display();

foreach ($alerts ?? [] as $alert) {
    echo "<div class='cmp-alert cmp-alert-" . $alert['class'] . "'>
        <span class='mr-1 d-flex'>" . $alert['content'] . "</span>
    </div>";
}

echo '<h3> Download Compilatio Database Tables </h3>';
echo '<form action="admin_tab_export_compilatio_database_tables.php" method="post">
        <button type="submit" class="btn btn-warning mt-2">Download Compilatio Tables</button>
      </form>';

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
