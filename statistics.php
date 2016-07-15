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
 * statistics.php - Display global statistics about assignments
 *
 * @package   plagiarism_compilatio
 * @author    Dan Marsden <dan@danmarsden.com>
 * @copyright 2012 Dan Marsden http://danmarsden.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');
require_once($CFG->dirroot . '/plagiarism/compilatio/compilatio_form.php');

require_login();
admin_externalpage_setup('plagiarismcompilatio');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$plagiarismplugin = new plagiarism_plugin_compilatio();

$plagiarismsettings = (array) get_config('plagiarism');

echo $OUTPUT->header();
$currenttab = 'compilatiostatistics';
require_once('compilatio_tabs.php');

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');


$rows = compilatio_get_global_statistics();

if (count($rows) === 0) {
    echo get_string("no_statistics_yet", "plagiarism_compilatio");
} else {

    echo "<legend class='compilatio_legend'>".get_string("global_statistics", "plagiarism_compilatio")."</legend>";

    $url = new moodle_url('/plagiarism/compilatio/CSV.php');
    echo "<a href='$url' style='margin-bottom:20px;' class='button'>" .
    get_string("export_raw_csv", "plagiarism_compilatio") . "</a>";

    echo "<legend class='compilatio_legend'>".get_string("assign_statistics", "plagiarism_compilatio")."</legend>";

    $jquery_url = new moodle_url("/plagiarism/compilatio/jquery.min.js");

    echo "<script src='$jquery_url'></script>";
    ?>

    <script>
        var wait_message = "<?php echo get_string("loading", "plagiarism_compilatio"); ?>";

        function percentage(v) {
            return v + "%";
        }

        function urlSorter(a, b) {
            //Strip tags to compare their content:
            a = a.replace(/(<([^>]+)>)/ig, "");
            b = b.replace(/(<([^>]+)>)/ig, "");
            return a.localeCompare(b)
        }
    </script>

    <?php
    $tablecssURL = new moodle_url("/plagiarism/compilatio/table/table.css");
    $tablejsURL = new moodle_url("/plagiarism/compilatio/table/table.js");

    echo "<link rel='stylesheet' href='$tablecssURL'>";
    echo "<script src='$tablejsURL'></script>";
    ?>
    
    <?php $url = new moodle_url("/plagiarism/compilatio/stats_json.php"); ?>

    <noscript>
    <style>
        #table-js{
            display:none;
        }
    </style>

    <p><?php echo get_string("enable_javascript", "plagiarism_compilatio"); ?></p>
    </noscript>


    <table id="table-js"
           data-toggle="table"
           data-url="<?php echo $url; ?>">
        <thead>
            <tr>
                <th colspan='3'></th>
                <th colspan='4'><?php echo "Compilatio - " . get_string("similarities", "plagiarism_compilatio"); ?></th>
            </tr>
            <tr>
                <th data-field="course"
                    data-sortable="true"
                    data-sorter="urlSorter">
                        <?php echo get_string("course"); ?>
                </th>
                <th data-field="teacher" 
                    data-sortable="true"
                    data-sorter="urlSorter">
                        <?php echo get_string("teacher", "plagiarism_compilatio"); ?>
                </th>
                <th data-field="assign"
                    data-sortable="true"
                    data-sorter="urlSorter">
                        <?php echo get_string("modulename", "assign"); ?>
                </th>
                <th data-field="analyzed_documents_count" 
                    data-sortable="true">
                        <?php echo str_replace(" ", "<br/>", get_string("documents_number", "plagiarism_compilatio")); ?>
                </th>
                <th data-field="minimum_rate" 
                    data-sortable="true"
                    data-formatter="percentage">
                        <?php echo get_string("minimum", "plagiarism_compilatio"); ?>
                </th>
                <th data-field="maximum_rate" 
                    data-sortable="true"
                    data-formatter="percentage">
                        <?php echo get_string("maximum", "plagiarism_compilatio"); ?>
                </th>
                <th data-field="average_rate" 
                    data-sortable="true"
                    data-formatter="percentage">
                        <?php echo get_string("average", "plagiarism_compilatio"); ?>
                </th>
            </tr>
        </thead>
    </table>

    <?php
    if ($CFG->version < 2014051200) {
        // Moodle < 2.7 does not include boostrap, add basic style.
        ?>
        <style>
            table{
                width:100%;
            }
            table, td, th{
                border:thin solid #ddd;
            }
        </style>

        <?php
    }
    ?>

    <table id="table-no-js" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th colspan='3'></th>
                <th colspan='4'><?php echo "Compilatio - " . get_string("similarities", "plagiarism_compilatio"); ?></th>
            </tr>
            <tr>
                <th>
                    <?php echo get_string("course"); ?>
                </th>
                <th>
                    <?php echo get_string("teacher", "plagiarism_compilatio"); ?>
                </th>
                <th>
                    <?php echo get_string("modulename", "assign"); ?>
                </th>
                <th>
                    <?php echo str_replace(" ", "<br/>", get_string("documents_number", "plagiarism_compilatio")); ?>
                </th>
                <th>
                    <?php echo get_string("minimum", "plagiarism_compilatio"); ?>
                </th>
                <th>
                    <?php echo get_string("maximum", "plagiarism_compilatio"); ?>
                </th>
                <th>
                    <?php echo get_string("average", "plagiarism_compilatio"); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>

                <tr>
                    <td><?php echo $row["course"]; ?></td>
                    <td><?php echo $row["teacher"]; ?></td>
                    <td><?php echo $row["assign"]; ?></td>
                    <td><?php echo $row["analyzed_documents_count"]; ?></td>
                    <td><?php echo $row["minimum_rate"]; ?>%</td>
                    <td><?php echo $row["maximum_rate"]; ?>%</td>
                    <td><?php echo $row["average_rate"]; ?>%</td>
                </tr>

            <?php endforeach; ?>
        <tbody>
    </table>

    <script>
        document.getElementById("table-no-js").style.display = 'none';
    </script>
    <?php
    
    
    $url = new moodle_url('/plagiarism/compilatio/CSV.php', array("raw" => 0));
    echo "<a href='$url' style='margin-top:20px;' class='button'>" .
    get_string("export_global_csv", "plagiarism_compilatio") . "</a>";
    
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer();


