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
 * @copyright  2023 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * CompilatioIcons class
 */
class CompilatioIcons {
    public static function report() {
        return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 67' width='20' class='mr-2 icon-inline'>
                <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->          
                <path fill='#494c4e' d='M71.61,34.39h0A3.6,3.6,0,1,1,68,30.79,3.59,3.59,0,0,1,71.61,34.39ZM91.14.15a9,9,0,0,0-7.91,13.34L72,26.31a8.91,8.91,0,0,0-4-.94,9,9,0,0,0-8.44,5.83L43.11,27.9a9,9,0,1,0-16.64,6.59L13.18,49.44a8.88,8.88,0,0,0-4-.95,9,9,0,1,0,7.92,4.71l13.29-15a8.92,8.92,0,0,0,4,1,9,9,0,0,0,8.43-5.83l16.47,3.3A9,9,0,0,0,77,34.39a8.93,8.93,0,0,0-1.11-4.33L87.14,17.24a9,9,0,1,0,4-17.09Zm-82,61a3.6,3.6,0,1,1,3.6-3.59A3.59,3.59,0,0,1,9.16,61.1ZM34.39,33.78A3.6,3.6,0,1,1,38,30.18,3.6,3.6,0,0,1,34.39,33.78Zm56.74-21a3.6,3.6,0,1,1,3.6-3.6A3.6,3.6,0,0,1,91.13,12.76Z'></path>
            </svg>";
    }

    public static function library() {
        return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' fill-opacity='50%'>
                <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->        
                <path d='M21.43 8.438c-.088-.701-.101-1.909.52-2.314.011-.007.02-.018.03-.027.507-.17.859-.41.583-.731L15.07 3 2.941 4.768s-1.39.208-1.265 2.47c.067 1.231.436 1.836.758 2.133l-.996.315c-.276.321.075.56.583.73.01.01.018.02.03.028.62.405.608 1.613.519 2.314-2.23.664-1.43.88-1.43.88l.49.124c-.344.326-.686.944-.622 2.116.124 2.262 1.265 2.418 1.265 2.418L10.21 21l11.981-3.042s.801-.216-1.43-.88c-.09-.7-.102-1.907.52-2.314.012-.007.02-.018.03-.027.508-.17.859-.41.583-.73l-.521-.166c.347-.22.869-.793.95-2.283.057-1.025-.198-1.626-.493-1.98l1.03-.26s.8-.216-1.43-.88zm-10.021-.03l2.014-.433 6.81-1.467 1.014-.219c-.324.622-.31 1.473-.257 2.02.012.124.025.237.039.323l-1.11.29-8.595 2.24.085-2.753zM2.754 10.61l1.014.218 6.54 1.41.57.122 1.714.37.084 2.752-8.833-2.303-.87-.227c.012-.086.026-.199.038-.323.053-.546.067-1.397-.257-2.02zM2.36 7.129c-.013-.602.09-1.037.296-1.258a.526.526 0 01.394-.17c.056 0 .097.008.1.008l5.226 1.786 2.608.89-.086 2.773-7.315-2.15-.387-.113a.224.224 0 00-.048-.008c-.03-.002-.753-.072-.788-1.758zm7.87 12.67l-7.701-2.263a.218.218 0 00-.049-.008c-.03-.002-.754-.072-.789-1.758-.012-.603.09-1.037.297-1.259a.527.527 0 01.393-.17c.057 0 .097.008.1.008l7.834 2.677-.085 2.773zm10.091-2.85c.013.124.026.237.04.323l-9.705 2.53.084-2.753 2.075-.447.307.078 1.148-.391 5.294-1.14 1.015-.22c-.325.622-.311 1.474-.258 2.02zm.535-3.742a.178.178 0 00-.052.009l-.732.215-6.969 2.048-.085-2.773 2.286-.781 5.537-1.893s.291-.068.504.16c.207.22.31.656.297 1.257-.036 1.686-.76 1.756-.786 1.758z'></path>
            </svg>";
    }

    public static function microchip_ai($color) {
        $color = self::get_hexadecimal_color($color);
        return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' height='1em' class='mr-1 icon-inline'>
        <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
        <path
            d='M184 24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64h-8c-35.3 0-64 28.7-64 64v8H24c-13.3 0-24 10.7-24 24s10.7 24 24 24H64v48H24c-13.3 0-24 10.7-24 24s10.7 24 24 24H64v48H24c-13.3 0-24 10.7-24 24s10.7 24 24 24H64v8c0 35.3 28.7 64 64 64h8v40c0 13.3 10.7 24 24 24s24-10.7 24-24V448h48v40c0 13.3 10.7 24 24 24s24-10.7 24-24V448h48v40c0 13.3 10.7 24 24 24s24-10.7 24-24V448h8c35.3 0 64-28.7 64-64v-8h40c13.3 0 24-10.7 24-24s-10.7-24-24-24H448V280h40c13.3 0 24-10.7 24-24s-10.7-24-24-24H448V184h40c13.3 0 24-10.7 24-24s-10.7-24-24-24H448v-8c0-35.3-28.7-64-64-64h-8V24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H280V24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H184V24zM112 128c0-8.8 7.2-16 16-16H384c8.8 0 16 7.2 16 16V384c0 8.8-7.2 16-16 16H128c-8.8 0-16-7.2-16-16V128zm224 44c-11 0-20 9-20 20V320c0 11 9 20 20 20s20-9 20-20V192c0-11-9-20-20-20zM234.3 184c-3.2-7.3-10.4-12-18.3-12s-15.1 4.7-18.3 12l-56 128c-4.4 10.1 .2 21.9 10.3 26.3s21.9-.2 26.3-10.3l5.3-12h64.8l5.3 12c4.4 10.1 16.2 14.7 26.3 10.3s14.7-16.2 10.3-26.3l-56-128zM216 241.9L230.9 276H201.1L216 241.9z'
            fill='{$color}'
        />
    </svg>";
    }

    public static function utl($color) {
        $color = self::get_hexadecimal_color($color);
        return "<svg xmlns='http://www.w3.org/2000/svg' height='1em' viewBox='0 0 640 512' class='mr-1'>
                <!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                <path
                    fill='{$color}' 
                    d='M0 64C0 28.7 28.7 0 64 0C192 0 320 0 448 0c35.3 0 64 28.7 64 64c0 42.9 0 85.8 0 128.7c-5.3-.5-10.6-.7-16-.7s-10.7 .2-16 .7c0-42.9 0-85.8 0-128.7c0-17.7-14.3-32-32-32c-128 0-256 0-384 0C46.3 32 32 46.3 32 64c0 96 0 192 0 288c0 17.7 14.3 32 32 32c32 0 64 0 96 0c17.7 0 32 14.3 32 32c0 16 0 32 0 48c32.7-24.5 65.4-49.1 98.1-73.6c5.5-4.2 12.3-6.4 19.2-6.4c3.8 0 7.6 0 11.4 0c1 11 3 21.7 5.9 32c-5.8 0-11.6 0-17.3 0c-41.2 30.9-82.5 61.9-123.7 92.8c-4.9 3.6-11.4 4.2-16.8 1.5s-8.8-8.2-8.8-14.3c0-16 0-32 0-48c0-10.7 0-21.3 0-32c-10.7 0-21.3 0-32 0c-21.3 0-42.7 0-64 0c-35.3 0-64-28.7-64-64C0 256 0 160 0 64zm128 96c0-8.8 7.2-16 16-16c74.7 0 149.3 0 224 0c8.8 0 16 7.2 16 16s-7.2 16-16 16c-74.7 0-149.3 0-224 0c-8.8 0-16-7.2-16-16zm0 96c0-8.8 7.2-16 16-16c42.7 0 85.3 0 128 0c8.8 0 16 7.2 16 16s-7.2 16-16 16c-42.7 0-85.3 0-128 0c-8.8 0-16-7.2-16-16zM352 368c0-79.5 64.5-144 144-144s144 64.5 144 144s-64.5 144-144 144s-144-64.5-144-144zm32 0c0 61.9 50.1 112 112 112s112-50.1 112-112s-50.1-112-112-112s-112 50.1-112 112zm88 56c0-13.3 10.7-24 24-24s24 10.7 24 24s-10.7 24-24 24s-24-10.7-24-24zm8-120c0-8.8 7.2-16 16-16s16 7.2 16 16c0 21.3 0 42.7 0 64c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-64z'
                />
            </svg>";
    }

    public static function copy_paste($color) {
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
