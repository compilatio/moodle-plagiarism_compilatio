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

/**
 * Helper class for display elements
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class output_helper {

    /**
     * Get the jquery HTML <script> tag
     *
     * @return string   HTML <script> tag
     */

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

        return html_writer::img($OUTPUT->image_url('compilatio-logo-' . $language, 'plagiarism_compilatio'),
            'Compilatio', array('title' => 'Compilatio', 'id' => 'compilatio-logo'));
    }

    /**
     * Get the indexing state HTML tag
     *
     * @param  mixed  $indexingstate Indexing state
     * @return string                Return the HTML tag
     */
    public static function get_indexing_state($indexingstate) {

        $html = ''; // Do not show indexing state for a "non-teacher" user.

        if ($indexingstate === true) {
            $html = html_writer::div('', 'compilatio-library-in',
                array('title' => get_string("indexed_document", "plagiarism_compilatio")));
        } else if ($indexingstate === false) {
            $html = html_writer::div('', 'compilatio-library-out',
                array('title' => get_string("not_indexed_document", "plagiarism_compilatio")));
        }

        return $html;
    }


    /**
     * Display plagiarism document area
     *
     * @param string $compid        DOM Compilatio ID
     * @param string $span          One word about the status to be displayed in the area
     * @param object $image         Identifier of an image from Compilatio plugin,
     *                              rendered using $OUTPUT->pix_url($image, 'plagiarism_compilatio')
     * @param string $title         Title
     * @param string $content       Content to be appended in the plagiarism area, such as similarity rate.
     * @param string $url           index ["target-blank"] contains a boolean : True to open in a new window.
     *                              index ["url"] contains the URL.
     * @param string $error         Span will be stylized as an error if $error is true.
     * @param mixed  $indexed       Indexing state of the document.
     * @param string $warning       warning about document or analysis
     *
     * @return string Return the HTML formatted string.
     */
    public static function get_plagiarism_area($compid,
                                               $span = "",
                                               $image = "",
                                               $title = "",
                                               $content = "",
                                               $url = array(),
                                               $error = false,
                                               $indexed = null,
                                               $warning = '') {

        if ($content == '' && $span == '') {
            return '';
        }

        global $OUTPUT;

        $html = html_writer::empty_tag('br');
        $html .= html_writer::div("", 'compilatio-clear');
        // Var $compid is spread via class because it may be purified inside id attribute.
        $html .= html_writer::start_div('compilatio-area compi-'.$compid);

        // Indexing state.
        $html .= self::get_indexing_state($indexed);

        $html .= html_writer::start_div('compilatio-plagiarismreport',
            array('title' => htmlspecialchars($title, ENT_QUOTES)));

        if (!empty($url) && !empty($url["url"])) {
            if ($url["target-blank"] === true) {
                $target = '_blank';
            } else {
                $target = '_self';
            }
            // Var $url contain & that must not be escaped.
            $html .= "<a target='" . $target . "' class='compilatio-plagiarismreport-link' href='" . $url["url"] . "'>";
        }

        $compisquare = new moodle_url("/plagiarism/compilatio/pix/logo_compilatio_carre.png");
        $html .= html_writer::div('', 'small-logo-compi',
            array('style' => 'background-image: url(\'' . $compisquare . '\'); background-size:cover;'));

        // Image.
        if ($image !== "") {
            $imgsrc = $OUTPUT->image_url($image, 'plagiarism_compilatio');
            $html .= html_writer::img($imgsrc, '%', array('class' => 'float-right'));
        }

        // State information.
        if ($span !== "") {
            if ($error) {
                $class = "compilatio-error";
            } else {
                $class = "";
            }
            if (!empty($url) && !empty($url["url"])) {
                $class .= " link"; // Used to underline span on hover.
            }
            $html .= html_writer::span($span, $class);
        }
        if ($content != '') {
            $html .= $content;
        }
        if (!empty($url) && !empty($url["url"])) {
            $html .= html_writer::end_tag('a');
        }

        $html .= html_writer::end_div();

        // Warning information.
        if ($warning != '') {
            // Get locale strings for warnings codes.
            $errorinfos = explode(",", $warning);
            if (count($errorinfos) > 1) {
                $titles = array();
                foreach ($errorinfos as $k => $e) {
                    $titles[] = $k + 1 . '°) ' .get_string($e, "plagiarism_compilatio");
                }
                $title = implode("\n", $titles);
            } else {
                $title = get_string($warning, "plagiarism_compilatio");
            }
            $html .= html_writer::start_div('compi-alert');
            $imgsrc = $OUTPUT->image_url('exclamation-yellow', 'plagiarism_compilatio');
            $html .= html_writer::img($imgsrc, '/!\\', array('title' => $title));
            $html .= html_writer::end_div();
        }

        $html .= html_writer::end_div();
        $html .= html_writer::div("", 'compilatio-clear');

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
        $imgsrc = $OUTPUT->image_url($color, 'plagiarism_compilatio');

        $html = html_writer::start_span();
        $html .= html_writer::img($imgsrc, 'dot', array('class' => 'compilatio-similarity-image'));
        $html .= html_writer::start_span('compilatio-similarity compilatio-similarity-' . $color);
        $html .= $score;
        $html .= html_writer::span('%', 'compilatio-percentage');
        $html .= html_writer::end_span();
        $html .= html_writer::end_span();

        return $html;
    }

}
