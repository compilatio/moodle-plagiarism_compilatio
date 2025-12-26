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
 * icons.php - Contains methods to get svg icons.
 *
 * @package    plagiarism_compilatio
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2025 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_compilatio\output;

/**
 * icons class
 */
class icons {
    /**
     * Returns HTML for arrow left icon
     *
     * @return string
     */
    public static function arrow_left() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" height="1em" class="mr-2">'
            . '<path fill="#6D6D6D" d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.'
            . '3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.'
            . '5-45.3 0l-160 160z"></path>
            </svg>';
    }

    /**
     * Returns HTML for statistics per student icon
     *
     * @return string
     */
    public static function statistics_per_student() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 650 512" height="1em">'
            . '<path d="M160 64c0-35.3 28.7-64 64-64H576c35.3 0 64 28.7 64 64V352c0 35.3-28.7 64-64 64H336.8c-11.8-25.5-29.9-47.'
            . '5-52.4-64H384V320c0-17.7 14.3-32 32-32h64c17.7 0 32 14.3 32 32v32h64V64L224 64v49.1C205.2 102.2 183.3 96 160 '
            . '96V64zm0 64a96 96 0 1 1 0 192 96 96 0 1 1 0-192zM133.3 352h53.3C260.3 352 320 411.7 320 485.3c0 14.7-11.9 26.7-26.7 '
            . '26.7H26.7C11.9 512 0 500.1 0 485.3C0 411.7 59.7 352 133.3 352z"></path>
            </svg>';
    }

    /**
     * Returns HTML for report icon
     *
     * @return string
     */
    public static function report() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 67" width="20" class="mr-1">'
            . '<!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license '
            . '(Commercial License) Copyright 2023 Fonticons, Inc. -->'
            . '<path fill="#494c4e" d="M71.61,34.39h0A3.6,3.6,0,1,1,68,30.79,3.59,3.59,0,0,1,71.61,34.39ZM91.14.15a9,9,0,0,0-7.91'
            . ',13.34L72,26.31a8.91,8.91,0,0,0-4-.94,9,9,0,0,0-8.44,5.83L43.11,27.9a9,9,0,1,0-16.64,6.59L13.18,49.44a8.88,8.88,0,'
            . '0,0-4-.95,9,9,0,1,0,7.92,4.71l13.29-15a8.92,8.92,0,0,0,4,1,9,9,0,0,0,8.43-5.83l16.47,3.3A9,9,0,0,0,77,34.39a8.93,8'
            . '.93,0,0,0-1.11-4.33L87.14,17.24a9,9,0,1,0,4-17.09Zm-82,61a3.6,3.6,0,1,1,3.6-3.59A3.59,3.59,0,0,1,9.16,61.1ZM34.39,'
            . '33.78A3.6,3.6,0,1,1,38,30.18,3.6,3.6,0,0,1,34.39,33.78Zm56.74-21a3.6,3.6,0,1,1,3.6-3.6A3.6,3.6,0,0,1,91.13,12.76Z"'
            . '></path>
            </svg>';
    }

    /**
     * Returns HTML for library icon
     *
     * @return string
     */
    public static function library() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" fill-opacity="50%">
                <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license'
                . ' (Commercial License) Copyright 2023 Fonticons, Inc. -->'
                . '<path d="M21.43 8.438c-.088-.701-.101-1.909.52-2.314.011-.007.02-.018.03-.027.507-.17.859-.41.583-.731L15.07 3 '
                . '2.941 4.768s-1.39.208-1.265 2.47c.067 1.231.436 1.836.758 2.133l-.996.315c-.276.321.075.56.583.73.01.01.018.02'
                . '.03.028.62.405.608 1.613.519 2.314-2.23.664-1.43.88-1.43.88l.49.124c-.344.326-.686.944-.622 2.116.124 2.262 1.'
                . '265 2.418 1.265 2.418L10.21 21l11.981-3.042s.801-.216-1.43-.88c-.09-.7-.102-1.907.52-2.314.012-.007.02-.018.03'
                . '-.027.508-.17.859-.41.583-.73l-.521-.166c.347-.22.869-.793.95-2.283.057-1.025-.198-1.626-.493-1.98l1.03-.26s.8'
                . '-.216-1.43-.88zm-10.021-.03l2.014-.433 6.81-1.467 1.014-.219c-.324.622-.31 1.473-.257 2.02.012.124.025.237.039'
                . '.323l-1.11.29-8.595 2.24.085-2.753zM2.754 10.61l1.014.218 6.54 1.41.57.122 1.714.37.084 2.752-8.833-2.303-.87-'
                . '.227c.012-.086.026-.199.038-.323.053-.546.067-1.397-.257-2.02zM2.36 7.129c-.013-.602.09-1.037.296-1.258a.526.5'
                . '26 0 01.394-.17c.056 0 .097.008.1.008l5.226 1.786 2.608.89-.086 2.773-7.315-2.15-.387-.113a.224.224 0 00-.048-'
                . '.008c-.03-.002-.753-.072-.788-1.758zm7.87 12.67l-7.701-2.263a.218.218 0 00-.049-.008c-.03-.002-.754-.072-.789-'
                . '1.758-.012-.603.09-1.037.297-1.259a.527.527 0 01.393-.17c.057 0 .097.008.1.008l7.834 2.677-.085 2.773zm10.091-'
                . '2.85c.013.124.026.237.04.323l-9.705 2.53.084-2.753 2.075-.447.307.078 1.148-.391 5.294-1.14 1.015-.22c-.325.62'
                . '2-.311 1.474-.258 2.02zm.535-3.742a.178.178 0 00-.052.009l-.732.215-6.969 2.048-.085-2.773 2.286-.781 5.537-1.'
                . '893s.291-.068.504.16c.207.22.31.656.297 1.257-.036 1.686-.76 1.756-.786 1.758z"></path>
            </svg>';
    }

    /**
     * Returns HTML for AI score icon
     *
     * @param  string $color
     * @return string
     */
    public static function aiscore($color) {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" height="1em" class="cmp-score-icon">
        <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license '
        . '(Commercial License) Copyright 2023 Fonticons, Inc. -->
        <path
            d="M184 24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64h-8c-35.3 0-64 28.7-64 64v8H24c-13.3 0-24 10.7-24 24s10.7 24 24 '
            . '24H64v48H24c-13.3 0-24 10.7-24 24s10.7 24 24 24H64v48H24c-13.3 0-24 10.7-24 24s10.7 24 24 24H64v8c0 35.3 28.7 '
            . '64 64 64h8v40c0 13.3 10.7 24 24 24s24-10.7 24-24V448h48v40c0 13.3 10.7 24 24 24s24-10.7 24-24V448h48v40c0 13.3'
            . ' 10.7 24 24 24s24-10.7 24-24V448h8c35.3 0 64-28.7 64-64v-8h40c13.3 0 24-10.7 24-24s-10.7-24-24-24H448V280h40c1'
            . '3.3 0 24-10.7 24-24s-10.7-24-24-24H448V184h40c13.3 0 24-10.7 24-24s-10.7-24-24-24H448v-8c0-35.3-28.7-64-64-64h'
            . '-8V24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H280V24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H184V24zM112 128c0-8'
            . '.8 7.2-16 16-16H384c8.8 0 16 7.2 16 16V384c0 8.8-7.2 16-16 16H128c-8.8 0-16-7.2-16-16V128zm224 44c-11 0-20 9-2'
            . '0 20V320c0 11 9 20 20 20s20-9 20-20V192c0-11-9-20-20-20zM234.3 184c-3.2-7.3-10.4-12-18.3-12s-15.1 4.7-18.3 12l'
            . '-56 128c-4.4 10.1 .2 21.9 10.3 26.3s21.9-.2 26.3-10.3l5.3-12h64.8l5.3 12c4.4 10.1 16.2 14.7 26.3 10.3s14.7-16.'
            . '2 10.3-26.3l-56-128zM216 241.9L230.9 276H201.1L216 241.9z"
            fill="' . self::get_hexadecimal_color($color) . '"
        />
    </svg>';
    }

    /**
     * Returns HTML for UTL score icon
     *
     * @param  string $color
     * @return string
     */
    public static function utlscore($color) {
        return '<svg xmlns="http://www.w3.org/2000/svg" file="none" height="1em" viewBox="0 0 640 512" class="cmp-score-icon">
                <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license '
                . '(Commercial License) Copyright 2023 Fonticons, Inc. -->
                <path
                    fill="' . self::get_hexadecimal_color($color) . '"
                    d="M0 417.5C0 406 3.1 394.8 9.1 385C75.2 276.4 141.3 167.9 207.4 59.3C217.7 42.4 236.1 32 256 32s38.3 10.4 48'
                    . '.6 27.3c48.4 79.5 96.8 159 145.2 238.6c-11.6 11.6-23.3 23.3-34.9 34.9C364.5 250 314 167.1 263.6 84.3C262 8'
                    . '1.6 259.1 80 256 80s-6 1.6-7.6 4.3C182.3 192.9 116.2 301.4 50.1 410c-1.4 2.2-2.1 4.8-2.1 7.5c0 8 6.5 14.5 '
                    . '14.5 14.5c90.7 0 181.3 0 272 0c-4 16-8 32-12.1 48c-86.6 0-173.3 0-260 0C28 480 0 452 0 417.5zM224 368c0-17'
                    . '.7 14.3-32 32-32s32 14.3 32 32s-14.3 32-32 32s-32-14.3-32-32zm8-184c0-13.3 10.7-24 24-24s24 10.7 24 24c0 3'
                    . '2 0 64 0 96c0 13.3-10.7 24-24 24s-24-10.7-24-24c0-32 0-64 0-96zM353.5 492.1c5-20 10-40.1 15-60.1c1.4-5.7 4'
                    . '.3-10.8 8.4-14.9C420 374 463.1 331 506.2 287.9c-.1 0 0 0-.1 0c23.7 23.7 47.3 47.3 71 71C534 402 491 445 44'
                    . '7.9 488.1c-4.1 4.1-9.3 7-14.9 8.4c-20 5-40.1 10-60.1 15c-5.5 1.4-11.2-.2-15.2-4.2s-5.6-9.7-4.2-15.2zM528.8'
                    . ' 265.3c9.8-9.8 19.6-19.6 29.4-29.4c15.7-15.6 41-15.6 56.6 0c4.8 4.8 9.5 9.5 14.3 14.3c15.6 15.7 15.6 41 0 '
                    . '56.6c-9.8 9.8-19.6 19.6-29.4 29.4l-70.9-70.9z"
                />
            </svg>';
    }

    /**
     * Returns HTML for similarity score icon
     *
     * @param  string $color
     * @return string
     */
    public static function simscore($color) {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" height="1em" fill="none" class="cmp-score-icon">
            <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license '
            . '(Commercial License) Copyright 2023 Fonticons, Inc. -->
            <path
                fill="' . self::get_hexadecimal_color($color) . '"
                d="M104.6 48H64C28.7 48 0 76.7 0 112V384c0 35.3 28.7 64 64 64h96V400H64c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 '
                . '16-16H80c0 17.7 14.3 32 32 32h72.4C202 108.4 227.6 96 256 96h62c-7.1-27.6-32.2-48-62-48H215.4C211.6 20.9 1'
                . '88.2 0 160 0s-51.6 20.9-55.4 48zM144 56a16 16 0 1 1 32 0 16 16 0 1 1 -32 0zM448 464H256c-8.8 0-16-7.2-16-1'
                . '6V192c0-8.8 7.2-16 16-16l140.1 0L464 243.9V448c0 8.8-7.2 16-16 16zM256 512H448c35.3 0 64-28.7 64-64V243.9c'
                . '0-12.7-5.1-24.9-14.1-33.9l-67.9-67.9c-9-9-21.2-14.1-33.9-14.1H256c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64'
                . ' 64 64z"
            />
        </svg>';
    }

    /**
     * Returns HTML for ignored AI score icon
     *
     * @return string
     */
    public static function ignoredaiscore() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" height="1em" class="cmp-score-icon">
                <!--!Font Awesome Pro 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license'
                . ' (Commercial License) Copyright 2024 Fonticons, Inc.-->
                <path
                    fill="#B0B0B0"
                    d="M5.1 9.2C13.3-1.2 28.4-3.1 38.8 5.1L143 86.8C154.8 72.9 172.4 64 192 64l8 0 0-40c0-13.3 10.7-24 24-24s24 1'
                    . '0.7 24 24l0 40 48 0 0-40c0-13.3 10.7-24 24-24s24 10.7 24 24l0 40 48 0 0-40c0-13.3 10.7-24 24-24s24 10.7 24'
                    . ' 24l0 40 8 0c35.3 0 64 28.7 64 64l0 8 40 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-40 0 0 48 40 0c13.3 0 24 1'
                    . '0.7 24 24s-10.7 24-24 24l-40 0 0 48 40 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-40 0c39.6 31 79.2 62.1 118.8'
                    . ' 93.1c10.4 8.2 12.3 23.3 4.1 33.7s-23.3 12.3-33.7 4.1L9.2 42.9C-1.2 34.7-3.1 19.6 5.1 9.2zM64 160c0-4.1 1-'
                    . '8 2.8-11.3L111.7 184 88 184c-13.3 0-24-10.7-24-24zm0 96c0-13.3 10.7-24 24-24l40 0 0-35.1 48 37.8L176 384c0'
                    . ' 8.8 7.2 16 16 16l193.8 0 60.9 48-6.8 0 0 40c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-40-48 0 0 40c0 13.3-10.'
                    . '7 24-24 24s-24-10.7-24-24l0-40-48 0 0 40c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-40-8 0c-35.3 0-64-28.7-64-6'
                    . '4l0-8-40 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l40 0 0-48-40 0c-13.3 0-24-10.7-24-24zM180.9 116.5l82.4 64.6'
                    . 'c3.7-5.6 9.9-9.1 16.7-9.1c7.9 0 15.1 4.7 18.3 12l16.3 37.3L380 272.5l0-80.5c0-11 9-20 20-20s20 9 20 20l0 1'
                    . '11.9 44 34.5L464 128c0-8.8-7.2-16-16-16l-256 0c-4.3 0-8.2 1.7-11.1 4.5zM205.7 312l17.5-40.1 56 44.1-31.6 0'
                    . '-5.3 12c-4.4 10.1-16.2 14.7-26.3 10.3s-14.7-16.2-10.3-26.3zM512 376s0 0 0 0z"
                />
            </svg>';
    }

    /**
     * Returns HTML for ignored UTL score icon
     *
     * @return string
     */
    public static function ignoredutlscore() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" height="1em" class="cmp-score-icon">
                <!--!Font Awesome Pro 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license'
                . ' (Commercial License) Copyright 2024 Fonticons, Inc.-->
                <path
                    fill="#B0B0B0"
                    d="M5.1 9.2C13.3-1.2 28.4-3.1 38.8 5.1L218.6 146l52.8-86.7C281.7 42.4 300.1 32 320 32s38.3 10.4 48.6 27.3L566'
                    . '.9 385c5.9 9.8 9.1 21 9.1 32.5c0 2.8-.2 5.5-.5 8.2l55.3 43.4c10.4 8.2 12.3 23.3 4.1 33.7s-23.3 12.3-33.7 4'
                    . '.1L9.2 42.9C-1.2 34.7-3.1 19.6 5.1 9.2zM64 417.5c0-11.5 3.1-22.7 9.1-32.5l95.2-156.4 38 29.9L114.1 410c-1.'
                    . '4 2.2-2.1 4.8-2.1 7.5c0 8 6.5 14.5 14.5 14.5l300 0 60.9 48-360.9 0C92 480 64 452 64 417.5zM256.7 175.9L296'
                    . ' 206.7l0-22.7c0-13.3 10.7-24 24-24s24 10.7 24 24l0 60.3L499 365.8 327.6 84.3C326 81.6 323.1 80 320 80s-6 1'
                    . '.6-7.6 4.3l-55.7 91.6zM288 368c0-13.3 8.1-24.7 19.7-29.6L351.6 373c-2.4 15.3-15.6 27-31.6 27c-17.7 0-32-14'
                    . '.3-32-32z"
                />
            </svg>';
    }

    /**
     * Returns HTML for ignored similarity score icon
     *
     * @return string
     */
    public static function ignoredsimscore() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" height="1em" class="cmp-score-icon">
                <!--!Font Awesome Pro 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license'
                . ' (Commercial License) Copyright 2024 Fonticons, Inc.-->
                <path
                    fill="#B0B0B0"
                    d="M5.1 9.2C13.3-1.2 28.4-3.1 38.8 5.1l62.3 48.8c8.2-3.8 17.3-5.9 26.9-5.9l40.6 0C172.4 20.9 195.8 0 224 0s51'
                    . '.6 20.9 55.4 48L320 48c29.8 0 54.9 20.4 62 48l-62 0c-28.4 0-54 12.4-71.6 32l-52.8 0 62.2 48.8c6.8-28 32.1-'
                    . '48.8 62.2-48.8l140.1 0c12.7 0 24.9 5.1 33.9 14.1L561.9 210c9 9 14.1 21.2 14.1 33.9l0 182.2 54.8 43c10.4 8.'
                    . '2 12.3 23.3 4.1 33.7s-23.3 12.3-33.7 4.1L9.2 42.9C-1.2 34.7-3.1 19.6 5.1 9.2zM64 146.4l48 37.8L112 384c0 8'
                    . '.8 7.2 16 16 16l96 0 0 48-96 0c-35.3 0-64-28.7-64-64l0-237.6zM208 56c0 8.8 7.2 16 16 16s16-7.2 16-16s-7.2-'
                    . '16-16-16s-16 7.2-16 16zm48 241.7l48 37.8L304 448c0 8.8 7.2 16 16 16l147.1 0 59 46.5c-4.5 1-9.2 1.5-14 1.5l'
                    . '-192 0c-35.3 0-64-28.7-64-64l0-150.3zM304 192l0 21L528 388.5l0-144.6L460.1 176 320 176c-8.8 0-16 7.2-16 16z"
                />
            </svg>';
    }

    /**
     * Returns hexadecimal color code
     *
     * @param  string $color
     * @return string color code
     */
    private static function get_hexadecimal_color($color) {
        switch ($color) {
            case 'green':
                return '#38ba7d';
            case 'orange':
                return '#f39c12';
            case 'red':
                return '#f34541';
            default:
                return '#B0B0B0';
        }
    }
}
