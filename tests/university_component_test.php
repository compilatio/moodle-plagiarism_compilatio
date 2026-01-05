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
 * Tests for university_component helper.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio;

use plagiarism_compilatio\compilatio\university_component;

/**
 * Test class for university component functionality.
 *
 * @package    plagiarism_compilatio
 * @copyright  2026 Compilatio.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \plagiarism_compilatio\compilatio\university_component
 */
class university_component_test extends \advanced_testcase {

    /**
     * Reset DB after each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Ensure the configured field is returned for a user.
     *
     * @covers ::retreive_university_component_for_user
     */
    public function test_retrieve_returns_user_field_value() {
        global $DB;

        set_config('university_component_type', 'department', 'plagiarism_compilatio');

        $user = $this->getDataGenerator()->create_user(['department' => 'Science']);

        $component = new university_component($DB);
        $value = $component->retreive_university_component_for_user((string) $user->id);

        $this->assertSame('Science', $value);
    }

    /**
     * Ensure "None" configuration short-circuits to null.
     *
     * @covers ::retreive_university_component_for_user
     */
    public function test_retrieve_returns_null_when_none_selected() {
        global $DB;

        $nonevalue = get_string('university_composable_none', 'plagiarism_compilatio');
        set_config('university_component_type', $nonevalue, 'plagiarism_compilatio');

        $user = $this->getDataGenerator()->create_user(['department' => 'Science']);

        $component = new university_component($DB);
        $value = $component->retreive_university_component_for_user((string) $user->id);

        $this->assertNull($value);
    }

    /**
     * Ensure the configured field is returned for a user when the configuration is not set.
     *
     * @covers ::retreive_university_component_for_user
     */
    public function test_retrieve_returns_user_field_value_no_configuration() {
        global $DB;

        $user = $this->getDataGenerator()->create_user(['department' => 'Science']);

        $component = new university_component($DB);
        $value = $component->retreive_university_component_for_user((string) $user->id);

        $this->assertNull($value);
    }

    /**
     * Ensure the configured field is returned for a user when the configuration is not set.
     *
     * @covers ::retreive_university_component_for_user
     */
    public function test_retrieve_returns_user_field_value_user_unknown() {
        global $DB;

        $component = new university_component($DB);
        $value = $component->retreive_university_component_for_user('fakeId');

        $this->assertNull($value);
    }

    /**
     * Ensure allowed fields list contains expected keys and filters blacklisted ones.
     *
     * @covers ::user_field_provider
     */
    public function test_user_field_provider_filters_and_includes_none() {
        global $DB;

        $component = new university_component($DB);
        $fields = $component->user_field_provider();

        $nonevalue = get_string('university_composable_none', 'plagiarism_compilatio');
        $this->assertArrayHasKey($nonevalue, $fields);
        $this->assertSame($nonevalue, $fields[$nonevalue]);

        $this->assertArrayHasKey('institution', $fields);
        $this->assertArrayHasKey('department', $fields);
        $this->assertArrayHasKey('city', $fields);

        $this->assertArrayNotHasKey('password', $fields);
        $this->assertArrayNotHasKey('id', $fields);
    }
}
