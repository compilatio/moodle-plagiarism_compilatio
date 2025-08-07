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
 * cmpfile.php - Class for compilatio file.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

use stored_file;
use plagiarism_compilatio\compilatio\identifier;

class cmpfile {
    /**
     * @var string $id
     */
    public string $id;

    /**
     * @var string $cm
     */
    public string $cm;

    /**
     * @var string $userid
     */
    public string $userid;

    /**
     * @var ?string $groupid
     */
    public ?string $groupid;

    /**
     * @var string $identifier
     */
    public string $identifier;

    /**
     * @var string $filename
     */
    public string $filename;

    /**
     * @var string $externalid
     */
    public string $externalid;

    /**
     * @var string $status
     */
    public string $status;

    /**
     * @var ?int $globalscore
     */
    public ?int $globalscore;

    /**
     * @var ?int $simscore
     */
    public ?int $simscore;

    /**
     * @var ?int $utlscore
     */
    public ?int $utlscore;

    /**
     * @var ?int $aiscore
     */
    public ?int $aiscore;

    /**
     * @var ?int $ignoredscores
     */
    public ?int $ignoredscores;

    /**
     * @var ?string $analysisid
     */
    public ?string $analysisid;

    /**
     * @var int $timesubmitted
     */
    public int $timesubmitted;

    /**
     * @var int $indexed
     */
    public int $indexed;

    /**
     * @var ?string $reporturl
     */
    public ?string $reporturl;

    /**
     * Create the file submited to Compilatio
     *
     * @param string $cmid Compilatio File
     * @param string $userid The id of the user
     * @param mixed $content Content to send to Compilatio
     * @param mixed $submission Submission
     * @param ?string $filename Name to set to the file
     * 
     * @return cmpfile Return the cmpfile
     */
    public function __construct(string $cmid, string $userid, mixed $content, mixed $submission, ?string $filename = null) {
        $cm = get_coursemodule_from_id(null, $cmid);
        
        $this->cm = $cmid;
        $this->timesubmitted = time();
        $this->setidentifier($cmid, $userid, $content);
        $this->setdatafromconfig($cmid, $userid);
        $this->filename = $filename ?? $this->createfilename($cm->modname, $submission, $content instanceof stored_file ? $content : null);
        $this->setauthors($submission, $userid);
global $CFG;file_put_contents($CFG->dataroot . '/temp/compilatio/curl.log', var_export($this, true) . "\n", FILE_APPEND);

        return $this;
    }

    /**
     * Create the filename for the cmpfile if not passed at the sending of the file
     *
     * @param string $modname Module name
     * @param ?stored_file $file Moodle stored file || null if content passed is not a stored file
     * @param int $userid User ID
     * @param $submission Submission || null if the content passed come from a quiz
     * @return string Return the filename of the cmpfile
     */
    private function createfilename(string $modname, mixed $submission, ?stored_file $file = null): string {
        if ($modname != 'quiz') {
            $filename = $file ? $file->get_filename() : 'assign-' . $submission->id . '.htm';
        } else {
            $filename = $file->get_filename();
        }

        return $filename;
    }

    /**
     * Set the identifier
     *
     * @param string $cmid Module module id
     * @param string $userid User ID
     * @param mixed $content Content to create the identifier from
     * @return void
     */
    private function setidentifier( $cmid, $userid, $content): void {
        $identifier = new identifier($userid, $cmid);

        if ($content instanceof stored_file) {
            $this->identifier = $identifier->create_from_file($content);
        } else {
            $this->identifier = $identifier->create_from_string($content);
        }
    }

    /**
     * Set the datas from the course module configuration
     *
     * @param string $cmid Module module id
     * @param string $userid User ID
     * @return void
     */
    private function setdatafromconfig($cmid, $userid): void {
        $plugincm = compilatio_cm_use($cmid);
        $this->indexed = $plugincm->defaultindexing ?? true;

        if (compilatio_student_analysis($plugincm->studentanalyses, $cmid, $userid)) {
            $this->indexed = false;
        }
    }

    /**
     * Set the author and groups
     *
     * @param mixed $submission Submission
     * @param string $userid User ID
     * @return void
     */
    private function setauthors($submission, $userid) {
        $groupid = null;

        if (!empty($submission->groupid)) {
            $groupid = $submission->groupid;
            $this->userid = $userid = 0;
        }

        $this->userid = $userid;
        $this->groupid = $groupid;
    }
}