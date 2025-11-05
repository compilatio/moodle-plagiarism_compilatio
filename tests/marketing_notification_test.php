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
 * marketing_notification_test.php - Test class for marketing notification handler
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2025 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\tests;

use plagiarism_compilatio\compilatio\marketing_notification;
use DateTime;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Test class for marketing notification functionality.
 *
 * @package    plagiarism_compilatio
 * @copyright  2025 Compilatio.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \plagiarism_compilatio\compilatio\marketing_notification
 */
class marketing_notification_test extends \advanced_testcase {

    public $BASE_INPUT_HTML = '<p>Test notification</p>' .
                    '<a href="https://example.com">Click here</a>' .
                    '<button>Action Button</button>';

    public $BASE_OUTPUT_HTML ='<p>Test notification</p>' .
                    '<a target="_blank" rel="noopener noreferrer" href="https://example.com">Click here</a>' .
                    '<btn btn-primary>Action Button</btn btn-primary>';
    /**
     * Set up test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test notification body formatting functionality.
     *
     * This test verifies that the format_notification_body method correctly:
     * - Adds target="_blank" and rel="noopener noreferrer" to links
     * - Converts button class to Bootstrap styled buttons
     * - Adds responsive styling to images
     *
     * @covers ::format_notification_body
     */
    public function test_format_notification_body_no_style() {
        $notification = new marketing_notification('en', 'test-user-id');
        $result = $notification->format_notification_body($this->BASE_INPUT_HTML . '<img src="test.jpg" alt="Test image">');

        $expected_result = $this->BASE_OUTPUT_HTML .
            '<img src="test.jpg" alt="Test image" style="max-width: 100%; max-height: 200px; height: auto; display: block; margin: 0 auto;">';

        $this->assertEquals($expected_result, $result);
    }

    /**
     * Test notification body formatting functionality with style without max-width.
     *
     * This test verifies that the format_notification_body method correctly:
     * - Adds target="_blank" and rel="noopener noreferrer" to links
     * - Converts button class to Bootstrap styled buttons
     * - Adds responsive styling to images
     *
     * @covers ::format_notification_body
     */
    public function test_format_notification_body_with_style() {
        $notification = new marketing_notification('en', 'test-user-id');
        $result = $notification->format_notification_body($this->BASE_INPUT_HTML . '<img src="test.jpg" style="lalala" alt="Test image">');

        $expected_result = $this->BASE_OUTPUT_HTML .
            '<img src="test.jpg" style="lalala max-width: 100%; max-height: 200px; display: block; margin: 0 auto;" alt="Test image">';

        $this->assertEquals($expected_result, $result);
    }

    /*
     * Test notification body formatting functionality with style with max-width.
     *
     * This test verifies that the format_notification_body method correctly:
     * - Adds target="_blank" and rel="noopener noreferrer" to links
     * - Converts button class to Bootstrap styled buttons
     * - Adds responsive styling to images
     *
     * @covers ::format_notification_body
     */
    public function test_format_notification_body_with_style_and_maxwidth() {
        $notification = new marketing_notification('en', 'test-user-id');
        $result = $notification->format_notification_body($this->BASE_INPUT_HTML . '<img src="test.jpg" style="lalala max-width=lilili" alt="Test image">');

        $expected_result = $this->BASE_OUTPUT_HTML .
            '<img src="test.jpg" style="lalala max-width=lilili" alt="Test image">';

        $this->assertEquals($expected_result, $result);
    }

    /**
     * Test retreive notification for the current language.
     *
     * This test verifies that the get_notification_current_language method correctly:
     * - Return the notification into the user language
     *
     * @covers ::get_notification_current_language
     */
    public function test_get_notification_current_language() {
        $notification = new marketing_notification('en', 'test-user-id');

        $test = [
            (object) ['title' => "A crazy notification", "body" => "Beautiful", "language" => "en"],
            (object) ['title' => "Une notif incroyable", "body" => "pioupiou", "language" => "fr"]
        ];        
        $result = $notification->get_notification_current_language($test);
        $expected_result = (object) ['title' => "A crazy notification", "body" => "Beautiful", "language" => "en"];

        $this->assertEquals($expected_result, $result);
    }

    public function test_get_notification_current_language_no_language() {
        $notification = new marketing_notification('pt', 'test-user-id');

        $test = [
            (object) ['title' => "A crazy notification", "body" => "Beautiful", "language" => "en"],
            (object) ['title' => "Une notif incroyable", "body" => "pioupiou", "language" => "fr"]
        ];        
        $result = $notification->get_notification_current_language($test);

        $this->assertNull($result);
    }
}