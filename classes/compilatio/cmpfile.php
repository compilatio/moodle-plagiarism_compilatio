<?php
namespace plagiarism_compilatio\compilatio;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/plagiarism/compilatio/lib.php');

use stored_file;
use plagiarism_compilatio\compilatio\identifier;

class cmpfile {
    public string $id;
    public string $cm;
    public string $userid;
    public ?string $groupid;
    public string $identifier;
    public string $filename;
    public string $externalid;
    public string $status;
    public ?int $globalscore;
    public ?int $simscore;
    public ?int $utlscore;
    public ?int $aiscore;
    public ?int $ignoredscores;
    public ?string $analysisid;
    public int $timesubmitted;
    public int $indexed;
    public ?string $reporturl;

    /**
     * Create the file submited to Compilatio
     *
     * @param string $cmid Compilatio File
     * @param string $userid The id of the user
     * @param mixed $content Content to send to Compilatio
     * @param mixed $submission Submission
     * @param ?string $filename Name to set to the file
     * @return Return the cmpfile
     */
    public function create(string $cmid, string $userid, mixed $content, mixed $submission, ?string $filename = null) {
        $cmpfile = new cmpfile();
        $identifier = new identifier($userid, $cmid);

        $this->cm = $cmid;
        $this->userid = $userid;
        $this->timesubmitted = time();

        if ($content instanceof stored_file) {
            $this->identifier = $identifier->create_from_file($content);
        } else {
            $this->identifier = $identifier->create_from_string($content);
        }

        $plugincm = compilatio_cm_use($cmid);
        $this->indexed = $plugincm->defaultindexing ?? true;

        if (compilatio_student_analysis($plugincm->studentanalyses, $cmid, $userid)) {
            $this->indexed = false;
        }
        $cm = get_coursemodule_from_id(null, $cmid);
        $this->filename = $filename ?? $this->createfilename($cm->modname, $submission, $content instanceof stored_file ? $content : null);

        $groupid = null;

        if (!empty($submission->groupid)) {
            $groupid = $submission->groupid;
            $this->userid = $userid = 0;
        }

        $this->groupid = $groupid;

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
}