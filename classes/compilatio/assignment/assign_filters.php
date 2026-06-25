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
 * Assign filters helper.
 *
 * @package   plagiarism_compilatio
 * @copyright 2026 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio\assignment;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Helper to read Assign grading filters and build analysis restrictions.
 */
class assign_filters {
    /** @var \stdClass Course module */
    private \stdClass $cm;

    /** @var \context_module Module context */
    private \context_module $context;

    /**
     * Class constructor.
     *
     * @param int $cmid Course module ID
     */
    public function __construct(int $cmid) {
        $this->cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
        $this->context = \context_module::instance($cmid);
    }

    /**
     * Check if the Assign grading table has active display filters.
     *
     * @return bool
     */
    public function has_active_filters(): bool {
        return
            $this->has_status_filter() ||
            $this->has_group_filter() ||
            $this->has_initials_filter() ||
            $this->has_workflow_filter() ||
            $this->has_marker_filter() ||
            $this->has_suspended_participants_filter();
    }


    /**
     * Get an SQL restriction for the requested analysis scope.
     *
     * @param string $scope Analysis scope
     * @param array $selectedstudents Selected or visible user IDs
     * @return array SQL fragment and parameters
     */
    public function get_scope_sql(string $scope, array $selectedstudents): array {
        if ('all' === $scope) {
            return ['', []];
        }

        $userids = 'filtered' === $scope ? $this->get_filtered_user_ids() : $selectedstudents;

        return $this->get_user_group_sql($userids);
    }

    /**
     * Get all user IDs matching the current Assign grading filters, across pagination.
     *
     * @return array User IDs
     */
    public function get_filtered_user_ids(): array {
        $assign = $this->get_assign();
        $this->apply_filter_params($assign);
        $filter = get_user_preferences('assign_filter', '');
        $table = new \assign_grading_table($assign, 0, $filter, 0, false);

        return array_values(array_unique(array_map('intval', $table->get_column_data('userid'))));
    }

    /**
     * Check status filter.
     *
     * @return bool
     */
    private function has_status_filter(): bool {
        $statusfilter = optional_param('status', null, PARAM_ALPHA);
        if ($statusfilter === null) {
            $statusfilter = get_user_preferences('assign_filter', '');
        }

        return $statusfilter !== '' && $statusfilter !== 'none';
    }

    /**
     * Check group filter.
     *
     * @return bool
     */
    private function has_group_filter(): bool {
        $groupid = optional_param('group', null, PARAM_INT);
        if ($groupid === null) {
            $groupid = groups_get_activity_group($this->cm);
        }

        return (int) $groupid !== 0;
    }

    /**
     * Check initials filter.
     *
     * @return bool
     */
    private function has_initials_filter(): bool {
        $initials = $this->get_initials_filters();

        return $initials['firstname'] !== '' || $initials['lastname'] !== '';
    }

    /**
     * Check workflow filter.
     *
     * @return bool
     */
    private function has_workflow_filter(): bool {
        $workflowfilter = optional_param('workflowfilter', null, PARAM_ALPHA);
        if ($workflowfilter === null) {
            $workflowfilter = get_user_preferences('assign_workflowfilter', '');
        }

        return $workflowfilter !== '';
    }

    /**
     * Check marker allocation filter.
     *
     * @return bool
     */
    private function has_marker_filter(): bool {
        $markerfilter = optional_param('markingallocationfilter', null, PARAM_ALPHANUMEXT);
        if ($markerfilter === null) {
            $markerfilter = get_user_preferences('assign_markerfilter', '');
        }

        return $markerfilter !== '' && (string) $markerfilter !== '0';
    }

    /**
     * Check suspended participants filter.
     *
     * @return bool
     */
    private function has_suspended_participants_filter(): bool {
        $suspendedfilter = optional_param('suspendedparticipantsfilter', null, PARAM_BOOL);
        if ($suspendedfilter !== null) {
            return (bool) $suspendedfilter;
        }

        return !get_user_preferences('grade_report_showonlyactiveenrol', true);
    }

    /**
     * Get Assign initial filters from request or persistent table preferences.
     *
     * @return array Initial filters
     */
    private function get_initials_filters(): array {
        $firstname = optional_param('tifirst', null, PARAM_RAW);
        $lastname = optional_param('tilast', null, PARAM_RAW);

        if ($firstname === null || $lastname === null) {
            $preferences = json_decode(
                get_user_preferences('flextable_mod_assign_grading-' . $this->context->id, ''),
                true
            );

            if (is_array($preferences)) {
                $firstname = $firstname ?? ($preferences['i_first'] ?? '');
                $lastname = $lastname ?? ($preferences['i_last'] ?? '');
            }
        }

        return [
        'firstname' => $firstname ?? '',
        'lastname' => $lastname ?? '',
        ];
    }

    /**
     * Mirror Assign grading page filter preference handling.
     *
     * @param \assign $assign Assignment instance
     * @return void
     */
    private function apply_filter_params(\assign $assign): void {
        $submittedfilter = optional_param('status', null, PARAM_ALPHA);
        if (isset($submittedfilter)) {
            $validfilters = array_column($assign->get_filters(), 'key');
            $validfilters = array_diff($validfilters, [ASSIGN_FILTER_NONE]);
            if ($submittedfilter === '' || in_array($submittedfilter, $validfilters)) {
                set_user_preference('assign_filter', $submittedfilter);
            }
        }

        $workflowfilter = optional_param('workflowfilter', null, PARAM_ALPHA);
        if ($workflowfilter !== null && array_key_exists($workflowfilter, $assign->get_marking_workflow_filters())) {
            set_user_preference('assign_workflowfilter', $workflowfilter);
        }

        $markingallocationfilter = optional_param('markingallocationfilter', null, PARAM_ALPHANUMEXT);
        if (
            $markingallocationfilter !== null
            && array_key_exists($markingallocationfilter, $assign->get_marking_allocation_filters())
        ) {
            set_user_preference('assign_markerfilter', $markingallocationfilter);
        }

        $suspendedparticipantsfilter = optional_param('suspendedparticipantsfilter', null, PARAM_BOOL);
        if (
            $suspendedparticipantsfilter !== null
            && has_capability('moodle/course:viewsuspendedusers', $assign->get_context())
        ) {
            set_user_preference('grade_report_showonlyactiveenrol', !$suspendedparticipantsfilter);
        }
    }

    /**
     * Build a restriction for Assign users and their group submissions.
     *
     * @param array $userids User IDs
     * @return array SQL fragment and parameters
     */
    private function get_user_group_sql(array $userids): array {
        global $DB;

        $userids = array_values(array_unique(array_map('intval', $userids)));
        if (empty($userids)) {
            return ['1 = 0', []];
        }

        $conditions = [];
        [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'assignuserid');
        $conditions[] = "userid $usersql";
        $params = $userparams;

        $groupids = $this->get_group_ids_for_users($userids);
        if (!empty($groupids)) {
            [$groupsql, $groupparams] = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED, 'assigngroupid');
            $conditions[] = "groupid $groupsql";
            $params = array_merge($params, $groupparams);
        }

        return ['(' . implode(' OR ', $conditions) . ')', $params];
    }

    /**
     * Get Assign group IDs for a list of users.
     *
     * @param array $userids User IDs
     * @return array Group IDs
     */
    private function get_group_ids_for_users(array $userids): array {
        global $DB;

        $assign = $DB->get_record('assign', ['id' => $this->cm->instance], '*', MUST_EXIST);
        $groups = groups_get_all_groups($this->cm->course, $userids, $assign->teamsubmissiongroupingid, 'g.id');

        return array_values(array_unique(array_map('intval', array_keys($groups))));
    }

    /**
     * Get Assign instance.
     *
     * @return \assign
     */
    private function get_assign(): \assign {
        global $CFG;

        require_once($CFG->dirroot . '/mod/assign/gradingtable.php');

        return new \assign($this->context, $this->cm, get_course($this->cm->course));
    }
}
