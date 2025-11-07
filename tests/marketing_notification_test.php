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

    /**
     * Base HTML input for testing notification body formatting.
     *
     * Contains basic HTML elements that will be processed by the format_notification_body method:
     * - A paragraph with text
     * - A link without target attributes
     * - A button without CSS classes
     */
    public const BASE_INPUT_HTML = '<p>Test notification</p>' .
                    '<a href="https://example.com">Click here</a>' .
                    '<button>Action Button</button>';

    /**
     * Expected HTML output after formatting by format_notification_body method.
     *
     * Contains the processed HTML with:
     * - Links with target="_blank" and rel="noopener noreferrer" attributes
     * - Buttons with Bootstrap CSS classes applied
     */
    public const BASE_OUTPUT_HTML = '<p>Test notification</p>' .
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
        $result = $notification->format_notification_body(self::BASE_INPUT_HTML . '<img src="test.jpg" alt="Test image">');

        $expectedresult = self::BASE_OUTPUT_HTML .
            '<img
                src="test.jpg"
                alt="Test image"
                style="max-width: 100%;
                max-height: 200px;
                height: auto;
                display: block;
                margin: 0 auto;
            ">';

        $this->assertEquals($expectedresult, $result);
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
        $result = $notification->format_notification_body(
            self::BASE_INPUT_HTML .
            '<img src="test.jpg" style="lalala" alt="Test image">'
        );

        $expectedresult = self::BASE_OUTPUT_HTML .
            '<img
                src="test.jpg"
                style="
                    lalala max-width: 100%;
                    max-height: 200px;
                    display: block;
                    margin: 0 auto;
                "
                alt="Test image"
            >';

        $this->assertEquals($expectedresult, $result);
    }

    /**
     * Test notification body formatting functionality with style with max-width.
     *
     * This test verifies that the format_notification_body method correctly:
     * - Adds target="_blank" and rel="noopener noreferrer" to links
     * - Converts button class to Bootstrap styled buttons
     * - Does not override existing max-width in style attribute
     *
     * @covers ::format_notification_body
     */
    public function test_format_notification_body_with_style_and_maxwidth() {
        $notification = new marketing_notification('en', 'test-user-id');
        $result = $notification->format_notification_body(
            self::BASE_INPUT_HTML .
            '<img src="test.jpg" style="lalala max-width=lilili" alt="Test image">'
        );

        $expectedresult = self::BASE_OUTPUT_HTML .
            '<img src="test.jpg" style="lalala max-width=lilili" alt="Test image">';

        $this->assertEquals($expectedresult, $result);
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
            (object) [
                'title' => "A crazy notification",
                "body" => "Beautiful",
                "language" => "en",
            ],
            (object) ['title' => "Une notif incroyable",
                "body" => "pioupiou",
                "language" => "fr",
            ],
        ];
        $result = $notification->get_notification_current_language($test);
        $expectedresult = (object) ['title' => "A crazy notification", "body" => "Beautiful", "language" => "en"];

        $this->assertEquals($expectedresult, $result);
    }

    /**
     * Test retreive notification when language is not available.
     *
     * This test verifies that the get_notification_current_language method correctly:
     * - Returns null when no notification matches the user's language
     *
     * @covers ::get_notification_current_language
     */
    public function test_get_notification_current_language_no_language() {
        $notification = new marketing_notification('pt', 'test-user-id');

        $test = [
            (object) [
                'title' => "A crazy notification",
                "body" => "Beautiful",
                "language" => "en",
            ],
            (object) [
                'title' => "Une notif incroyable",
                "body" => "pioupiou",
                "language" => "fr",
            ],
        ];
        $result = $notification->get_notification_current_language($test);

        $this->assertNull($result);
    }
}
