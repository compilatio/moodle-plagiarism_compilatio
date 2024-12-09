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
 * Get JWT to redirect user to report
 *
 * @package   plagiarism_compilatio
 * @author    Compilatio <support@compilatio.net>
 * @copyright 2023 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/lib.php');

use plagiarism_compilatio\compilatio\api;

require_login();

global $OUTPUT;

$docid = required_param('docid', PARAM_RAW);
$cmid = required_param('cmid', PARAM_RAW);
$reporttype = optional_param('type', 'detailed', PARAM_RAW);

$modulecontext = context_module::instance($cmid);
$isteacher = has_capability('plagiarism/compilatio:viewreport', $modulecontext);

$userid = $DB->get_field('plagiarism_compilatio_cm_cfg', 'userid', ['cmid' => $cmid]);
$compilatio = new api($userid);

if ($isteacher) {
    $jwt = $compilatio->get_report_token($docid);

    if ($jwt === false) {
        echo $OUTPUT->header();

        echo "<p><img src='" . $OUTPUT->image_url('compilatio', 'plagiarism_compilatio') . "' alt='Compilatio' width='250'></p>";

        echo "<div class='compilatio-alert compilatio-alert-danger'>"
                . get_string('redirect_report_failed', 'plagiarism_compilatio') .
            '</div>';
        echo $OUTPUT->footer();
    } else {
        header('location: https://app.compilatio.net/api/private/reports/redirect/' . $jwt);
    }
} else {
    $doc = $compilatio->get_document($docid);

    $lang = substr(current_language(), 0, 2);
    $lang = in_array($lang, ['fr', 'en', 'it', 'es', 'de', 'pt']) ? $lang : 'fr';

    $recipe = get_config('plagiarism_compilatio', 'recipe');

    if (isset($doc->analyses->$recipe->id)) {
        $filepath = $compilatio->get_pdf_report($doc->analyses->$recipe->id, $lang, $reporttype);

        if (is_file($filepath)) {
            header('HTTP/1.1 200 OK');
            header('Date: ' . date('D M j G:i:s T Y'));
            header('Last-Modified: ' . date('D M j G:i:s T Y'));
            header('Content-Disposition: attachment;filename=' . basename($filepath));
            header('Content-Type: application/pdf');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit(0);
        }
    }

    echo $OUTPUT->header();

    echo "<p><img src='" . $OUTPUT->image_url('compilatio', 'plagiarism_compilatio') . "' alt='Compilatio' width='250'></p>";

    echo "<div class='compilatio-alert compilatio-alert-danger'>"
            . get_string('download_report_failed', 'plagiarism_compilatio') .
        '</div>';
    echo $OUTPUT->footer();
}
