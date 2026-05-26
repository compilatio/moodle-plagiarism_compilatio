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
 * Assign group restriction helper.
 *
 * @package   plagiarism_compilatio
 * @copyright 2026 Compilatio.net {@link https://www.compilatio.net}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\compilatio;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Helper to restrict Assign documents to the grader's separate groups.
 */
class assign_group_restriction {
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
     * Get an SQL restriction for Assign separate groups.
     *
     * @return array SQL fragment and parameters
     */
    public function get_sql(): array {
        if (!$this->needs_restriction()) {
            return ['', []];
        }

        $groupids = $this->get_allowed_group_ids();
        if (empty($groupids)) {
            return ['1 = 0', []];
        }

        return $this->build_sql($groupids, $this->get_user_ids_for_groups($groupids));
    }

    /**
     * Check whether the current user needs a separate-groups restriction.
     *
     * @return bool
     */
    private function needs_restriction(): bool {
        return groups_get_activity_groupmode($this->cm) == SEPARATEGROUPS
            && !has_capability('moodle/site:accessallgroups', $this->context);
    }

    /**
     * Get group IDs available to the current user for this activity.
     *
     * @return array
     */
    private function get_allowed_group_ids(): array {
        $groups = groups_get_activity_allowed_groups($this->cm);

        return array_values(array_map('intval', array_keys($groups)));
    }

    /**
     * Get user IDs in the given groups.
     *
     * @param array $groupids Group IDs
     * @return array
     */
    private function get_user_ids_for_groups(array $groupids): array {
        $userids = [];

        foreach ($groupids as $groupid) {
            foreach (groups_get_members($groupid, 'u.id') as $member) {
                $userids[] = (int) $member->id;
            }
        }

        return array_values(array_unique($userids));
    }

    /**
     * Build the SQL fragment for user and group submissions.
     *
     * @param array $groupids Group IDs
     * @param array $userids User IDs
     * @return array SQL fragment and parameters
     */
    private function build_sql(array $groupids, array $userids): array {
        global $DB;

        $conditions = [];
        $params = [];

        if (!empty($userids)) {
            [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'groupuserid');
            $conditions[] = "userid $usersql";
            $params = array_merge($params, $userparams);
        }

        [$groupsql, $groupparams] = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED, 'groupid');
        $conditions[] = "groupid $groupsql";
        $params = array_merge($params, $groupparams);

        return ['(' . implode(' OR ', $conditions) . ')', $params];
    }
}
