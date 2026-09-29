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
 * @copyright  2026 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio;

use plagiarism_compilatio\compilatio\marketing_notification;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Test class for marketing notification functionality.
 *
 * @package    plagiarism_compilatio
 * @copyright  2026 Compilatio.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \plagiarism_compilatio\compilatio\marketing_notification
 */
final class marketing_notification_test extends \advanced_testcase {
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
    public function test_format_notification_body_no_style(): void {
        $notification = new marketing_notification('en', 'test-user-id');
        $result = $notification->format_notification_body(self::BASE_INPUT_HTML . '<img src="test.jpg" alt="Test image">');

        $compactresult = str_replace(' ', '', $result);
        $this->assertStringContainsString('target="_blank"', $result);
        $this->assertStringContainsString('noopener', $result);
        $this->assertStringContainsString('noreferrer', $result);
        $this->assertStringContainsString('<span class="btn btn-primary">Action Button</span>', $result);
        $this->assertStringContainsString('class="img-fluid d-block mx-auto"', $result);
        $this->assertStringContainsString('max-height:200px', $compactresult);
        $this->assertStringContainsString('height:auto', $compactresult);
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
    public function test_format_notification_body_with_style(): void {
        $notification = new marketing_notification('en', 'test-user-id');
        $result = $notification->format_notification_body(
            self::BASE_INPUT_HTML .
            '<img src="test.jpg" style="lalala" alt="Test image">'
        );

        $compactresult = str_replace(' ', '', $result);
        $this->assertStringContainsString('class="img-fluid d-block mx-auto"', $result);
        $this->assertStringContainsString('max-height:200px', $compactresult);
        $this->assertStringNotContainsString('lalala', $result);
    }

    /**
     * Test notification body formatting functionality with style with max-width.
     *
     * This test verifies that the format_notification_body method correctly:
     * - Adds target="_blank" and rel="noopener noreferrer" to links
     * - Converts button class to Bootstrap styled buttons
     * - Removes invalid CSS declarations during the final sanitisation
     *
     * @covers ::format_notification_body
     */
    public function test_format_notification_body_with_style_and_maxwidth(): void {
        $notification = new marketing_notification('en', 'test-user-id');
        $result = $notification->format_notification_body(
            self::BASE_INPUT_HTML .
            '<img src="test.jpg" style="lalala max-width=lilili" alt="Test image">'
        );

        $this->assertStringContainsString('class="img-fluid d-block mx-auto"', $result);
        $this->assertStringNotContainsString('lalala', $result);
        $this->assertStringNotContainsString('lilili', $result);
    }

    /**
     * Test that CSS from a notification cannot affect the surrounding Moodle page.
     *
     * @covers ::format_notification_body
     */
    public function test_format_notification_body_removes_global_css(): void {
        $notification = new marketing_notification('en', 'test-user-id');
        $body = '<style>body { display: none !important; }</style>' .
            '<p onclick="alert(1)">Visible notification content</p>';

        $result = $notification->format_notification_body($body);

        $this->assertStringNotContainsString('<style', $result);
        $this->assertStringNotContainsString('display: none', $result);
        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringContainsString('Visible notification content', $result);
    }

    /**
     * Test retreive notification for the current language.
     *
     * This test verifies that the get_notification_current_language method correctly:
     * - Return the notification into the user language
     *
     * @covers ::get_notification_current_language
     */
    public function test_get_notification_current_language(): void {
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
    public function test_get_notification_current_language_no_language(): void {
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
