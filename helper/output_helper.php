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

        global $OUTPUT, $CFG, $PAGE;

        if ($compid !== null) {
            $html = html_writer::empty_tag('br');
            $html .= html_writer::div("", 'compilatio-clear');
            // Var $compid is spread via class because it may be purified inside id attribute.
            $html .= html_writer::start_div('compilatio-area compi-'.$compid);
        }

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

            if (preg_match('/^[a-f0-9]{40}$/', $url["url"])) {
                $html .= "<a
                        target='" . $target . "'
                        class='compilatio-plagiarismreport-link'
                        href='" . $CFG->httpswwwroot . "/plagiarism/compilatio/redirect_report.php?docid=" . $url["url"]
                    . "'>";
            } else {
                // Var $url contain & that must not be escaped.
                $html .= "<a target='" . $target . "' class='compilatio-plagiarismreport-link' href='" . $url["url"] . "'>";
            }
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
        if ($warning != '' && strpos($warning, '(') === false) {
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

        if ($compid !== null) {
            $html .= html_writer::end_div();
        }

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

    /**
     * Display an image and similarity percentage according to the thresholds
     *
     * @param  float  $score          Similarity score to be displayed
     * @param  int    $greenthreshold Green for $score lower than that threshold
     * @param  int    $redthreshold   Red for $score higher than that threshold
     * @return string                 the HTML string displaying colored score and an image
     */
    public static function get_scores($results, $greenthreshold, $redthreshold) {
        if ($results['score'] <= $greenthreshold) {
            $color = 'green';
        } else if ($results['score'] <= $redthreshold) {
            $color = 'orange';
        } else {
            $color = 'red';
        }

        $scores = ['similarityscore', 'utlscore', 'aiscore'];
        $tooltip = "<b>" . $results['score'] . get_string('tooltip_detailed_scores', 'plagiarism_compilatio') . "</b><br>";
        $icons = '';

        foreach ($scores as $score) {
            $message = isset($results[$score]) ? $results[$score] . '%' : get_string('unmeasured', 'plagiarism_compilatio');
            $tooltip .= get_string($score, 'plagiarism_compilatio') . " : <b>{$message}</b><br>";
            if (isset($results[$score])) {
                $icons .= self::$score($results[$score] > 0 ? $color : null);
            }
        }

        $html = "<span style='padding-top:10px;' data-toggle='tooltip' data-html='true'  title='{$tooltip}'>" . $icons . "</span>";

        return $html;
    }

    public static function aiscore($color) {
        $color = self::get_hexadecimal_color($color);
        return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' height='1em' class='mr-1 icon-inline'>
        <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
        <path
            d='M184 24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64h-8c-35.3 0-64 28.7-64 64v8H24c-13.3 0-24 10.7-24 24s10.7 24 24 24H64v48H24c-13.3 0-24 10.7-24 24s10.7 24 24 24H64v48H24c-13.3 0-24 10.7-24 24s10.7 24 24 24H64v8c0 35.3 28.7 64 64 64h8v40c0 13.3 10.7 24 24 24s24-10.7 24-24V448h48v40c0 13.3 10.7 24 24 24s24-10.7 24-24V448h48v40c0 13.3 10.7 24 24 24s24-10.7 24-24V448h8c35.3 0 64-28.7 64-64v-8h40c13.3 0 24-10.7 24-24s-10.7-24-24-24H448V280h40c13.3 0 24-10.7 24-24s-10.7-24-24-24H448V184h40c13.3 0 24-10.7 24-24s-10.7-24-24-24H448v-8c0-35.3-28.7-64-64-64h-8V24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H280V24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H184V24zM112 128c0-8.8 7.2-16 16-16H384c8.8 0 16 7.2 16 16V384c0 8.8-7.2 16-16 16H128c-8.8 0-16-7.2-16-16V128zm224 44c-11 0-20 9-20 20V320c0 11 9 20 20 20s20-9 20-20V192c0-11-9-20-20-20zM234.3 184c-3.2-7.3-10.4-12-18.3-12s-15.1 4.7-18.3 12l-56 128c-4.4 10.1 .2 21.9 10.3 26.3s21.9-.2 26.3-10.3l5.3-12h64.8l5.3 12c4.4 10.1 16.2 14.7 26.3 10.3s14.7-16.2 10.3-26.3l-56-128zM216 241.9L230.9 276H201.1L216 241.9z'
            fill='{$color}'
        />
    </svg>";
    }

    public static function utlscore($color) {
        $color = self::get_hexadecimal_color($color);
        return "<svg xmlns='http://www.w3.org/2000/svg' height='1em' viewBox='0 0 640 512' class='mr-1'>
                <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                <path
                    fill='{$color}' 
                    d='M0 64C0 28.7 28.7 0 64 0C192 0 320 0 448 0c35.3 0 64 28.7 64 64c0 42.9 0 85.8 0 128.7c-5.3-.5-10.6-.7-16-.7s-10.7 .2-16 .7c0-42.9 0-85.8 0-128.7c0-17.7-14.3-32-32-32c-128 0-256 0-384 0C46.3 32 32 46.3 32 64c0 96 0 192 0 288c0 17.7 14.3 32 32 32c32 0 64 0 96 0c17.7 0 32 14.3 32 32c0 16 0 32 0 48c32.7-24.5 65.4-49.1 98.1-73.6c5.5-4.2 12.3-6.4 19.2-6.4c3.8 0 7.6 0 11.4 0c1 11 3 21.7 5.9 32c-5.8 0-11.6 0-17.3 0c-41.2 30.9-82.5 61.9-123.7 92.8c-4.9 3.6-11.4 4.2-16.8 1.5s-8.8-8.2-8.8-14.3c0-16 0-32 0-48c0-10.7 0-21.3 0-32c-10.7 0-21.3 0-32 0c-21.3 0-42.7 0-64 0c-35.3 0-64-28.7-64-64C0 256 0 160 0 64zm128 96c0-8.8 7.2-16 16-16c74.7 0 149.3 0 224 0c8.8 0 16 7.2 16 16s-7.2 16-16 16c-74.7 0-149.3 0-224 0c-8.8 0-16-7.2-16-16zm0 96c0-8.8 7.2-16 16-16c42.7 0 85.3 0 128 0c8.8 0 16 7.2 16 16s-7.2 16-16 16c-42.7 0-85.3 0-128 0c-8.8 0-16-7.2-16-16zM352 368c0-79.5 64.5-144 144-144s144 64.5 144 144s-64.5 144-144 144s-144-64.5-144-144zm32 0c0 61.9 50.1 112 112 112s112-50.1 112-112s-50.1-112-112-112s-112 50.1-112 112zm88 56c0-13.3 10.7-24 24-24s24 10.7 24 24s-10.7 24-24 24s-24-10.7-24-24zm8-120c0-8.8 7.2-16 16-16s16 7.2 16 16c0 21.3 0 42.7 0 64c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-64z'
                />
            </svg>";
    }

    public static function similarityscore($color) {
        $color = self::get_hexadecimal_color($color);  
        return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' height='1em' fill='none' class='mx-1 icon-inline'>
            <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
            <path
                fill='{$color}'
                d='M104.6 48H64C28.7 48 0 76.7 0 112V384c0 35.3 28.7 64 64 64h96V400H64c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16H80c0 17.7 14.3 32 32 32h72.4C202 108.4 227.6 96 256 96h62c-7.1-27.6-32.2-48-62-48H215.4C211.6 20.9 188.2 0 160 0s-51.6 20.9-55.4 48zM144 56a16 16 0 1 1 32 0 16 16 0 1 1 -32 0zM448 464H256c-8.8 0-16-7.2-16-16V192c0-8.8 7.2-16 16-16l140.1 0L464 243.9V448c0 8.8-7.2 16-16 16zM256 512H448c35.3 0 64-28.7 64-64V243.9c0-12.7-5.1-24.9-14.1-33.9l-67.9-67.9c-9-9-21.2-14.1-33.9-14.1H256c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64z'
            />
        </svg>";
    }

    private static function get_hexadecimal_color($color) {
        switch ($color) {
            case 'green':
                return '#6ab35a';
            case 'orange':
                return '#f39c12';
            case 'red':
                return '#e7685a';
            default:
                return '#B0B0B0';
        }
    }
}
