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
 * output_helper.php - Contains Plagiarism plugin helper methods for display graphics elements.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Helper class for display elements
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class output_helper
{

    /**
     * Get the jquery HTML <script> tag
     *
     * @return string   HTML <script> tag
     */
    public static function get_jquery() {
        return '
            <script
                src="https://code.jquery.com/jquery-1.12.4.js"
                integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU="
                crossorigin="anonymous">
            </script>';
    }

    /**
     * Display Compilatio's logo according to the user's language (English by default)
     * FR, IT, ES & EN are currently available
     *
     * @return string   HTML tag displaying the right image
     */
    public static function get_logo() {

        global $OUTPUT;

        $ln = current_language();

        if (!in_array($ln, array("fr", "en", "it", "es"))) {
            $language = "en";
        } else {
            $language = $ln;
        }

        return '<img title="Compilatio" id="compilatio-logo" src="' .
            $OUTPUT->image_url('compilatio-logo-' . $language, 'plagiarism_compilatio') .'">';
    }

    /**
     * Get the indexing state HTML tag
     *
     * @param  mixed  $indexingstate Indexing state
     * @return string                Return the HTML tag
     */
    public static function get_indexing_state($indexingstate) {

        if ($indexingstate === true) {
            $html = '
                <div
                    class="library-in"
                    style="background-image: url(\'' . new moodle_url("/plagiarism/compilatio/pix/library-in.png") . '\');"
                    title="'.get_string("indexed_document", "plagiarism_compilatio").'">
                </div>';
        } else if ($indexingstate === false) {
            $html = '
                <div
                    class="library-out"
                    style="background-image: url(\'' . new moodle_url("/plagiarism/compilatio/pix/library-out.png") . '\');"
                    title="'.get_string("not_indexed_document", "plagiarism_compilatio").'">
                </div>';
        } else {
             $html = '
                <div
                    class="library"
                    style="background-image: url(\'' . new moodle_url("/plagiarism/compilatio/pix/library.png") . '\');" >
                </div>';
        }

        return $html;

    }

    /**
     * Display plagiarism document area
     *
     * @param string $span    One word about the status to be displayed in the area
     * @param object $image   Identifier of an image from Compilatio plugin,
     *                        rendered using $OUTPUT->pix_url($image, 'plagiarism_compilatio')
     * @param string $title   Title
     * @param string $content Content to be appended in the plagiarism area, such as similarity rate.
     * @param string $url     index ["target-blank"] contains a boolean : True to open in a new window.
     *                        index ["url"] contains the URL.
     * @param string $error   Span will be stylized as an error if $error is true.
     * @param mixed  $indexed Indexing state of the document.
     * @param string $compid  DOM Compilatio ID
     *
     * @return string Return the HTML formatted string.
     */
    public static function get_plagiarism_area($span = "",
                                               $image = "",
                                               $title = "",
                                               $content = "",
                                               $url = array(),
                                               $error = false,
                                               $indexed = null,
                                               $compid) {

        global $OUTPUT;

        $html = "<br/>";
        $html .= "<div class='clear'></div>";
        // Var $compid is spread via class because it may be purified inside id attribute.
        $html .= '<div class="compilatio-area compi-'.$compid.'">';

        // Indexing state.
        $html .= self::get_indexing_state($indexed);

        $html .= "<div class='plagiarismreport' title='" . htmlspecialchars($title, ENT_QUOTES) . "'>";

        if (!empty($url) && !empty($url["url"])) {
            if ($url["target-blank"] === true) {
                $target = "target='_blank'";
            } else {
                $target = "";
            }
            $html .= "<a $target class='plagiarismreport-link' href='" . $url["url"] . "'>";
        }

        $html .= '<div class="small-logo-compi" style="background-image: url(\'' .
            new moodle_url("/plagiarism/compilatio/pix/logo_compilatio_carre.png") .
            '\');background-size:cover;" title="Compilatio.net"></div>';

        if ($image !== "") {
            $html .= '<img src="' . $OUTPUT->image_url($image, 'plagiarism_compilatio') . '" class="float-right" />';
        }
        if ($span !== "") {
            if ($error) {
                $class = "compilatio-error";
            } else {
                $class = "";
            }
            if (!empty($url) && !empty($url["url"])) {
                $class .= " link"; // Used to underline span on hover.
            }
            $html .= "<span class='$class'>$span</span>";
        }
        if ($content !== "") {
            $html .= $content;
        }
        if (!empty($url) && !empty($url["url"])) {
            $html .= "</a>";
        }

        $html .= "</div>";
        $html .= "</div>";

        $html .= "<div class='clear'></div>";

        return $html;
    }

    /**
     * Display an image and similarity percentage according to the thresholds
     *
     * @param  float  $score          Similarity score to be displayed
     * @param  int    $greenthreshold Green for $score lower than that threshold
     * @param  int    $redthreshold   Red for $score higher than that threshold
     * @return string                 the HTML string displaying colored score and an image
     */
    public static function get_image_similarity($score,
                                                $greenthreshold,
                                                $redthreshold) {

        global $OUTPUT;

        if ($score <= $greenthreshold) {
            $color = 'green';
        } else if ($score <= $redthreshold) {
            $color = 'orange';
        } else {
            $color = 'red';
        }
        $image = $OUTPUT->image_url($color, 'plagiarism_compilatio');

        return '
            <span>
                <img class="similarity-image" src="' . $image . '"/>
                <span class="similarity similarity-' . $color . '">'
                    .$score.
                    '<span class="percentage">%</span>
                </span>
            </span>';

    }

}