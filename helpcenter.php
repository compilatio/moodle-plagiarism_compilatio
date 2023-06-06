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
 * This script redirects to Compilatio helpcenter
 *
 * @copyright  2018 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * It is called from assignments pages or plugin administration section
 *
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_login();

$idgroupe = required_param('idgroupe', PARAM_TEXT);

// Gheck GET parameter.
$availpages = ['moodle-admin', 'moodle-teacher', 'moodle-info-waiting', 'service-state'];

$page = optional_param('page', 'moodle-teacher', PARAM_RAW);
if (in_array($page, $availpages) === false) {
    $page = 'moodle-teacher';
}
echo("<!doctype html>
<html>
	<head>
		<title>Compilatio helpcenter</title>
        <meta charset='utf-8'>
	</head>
	<body>
		<script type='text/javascript'>
			(function redirectHC(g, e, l, p){
				var s,u,b,m,i,t,o;s=document;
				function x(a,b){return a.createElement(b);};function y(a,b,c){a.setAttribute(b,c);};function z(a,b){a.appendChild(b);};
				u=x(s,\"form\");y(u,'action','https://www.compilatio.net/support/?zeRedirect=zeLogin');y(u,'method','post');
				b=x(s,'input');y(b,'type','hidden');y(b,'name','moodle_id_groupe');y(b,'value',g);
				m=x(s,'input');y(m,'type','hidden');y(m,'name','lms-user-email');y(m,'value',e);
				i=x(s,'input');y(i,'type','hidden');y(i,'name','lms-user-locale');y(i,'value',l);
				o=x(s,'input');y(o,'type','hidden');y(o,'name','helpcenter-page');y(o,'value',p);
				t=x(s,'input');y(t,'type','submit');y(t,'id','compilatio-submit-redirect-hc');y(t,'style',\"display: none;\");
				z(u,b);z(u,m);z(u,i);z(u,o);z(u,t);z(s.body,u);
				s.getElementById('compilatio-submit-redirect-hc').click();
			})('".$idgroupe."', '".$USER->email."', '".current_language()."', '".$page."')
		</script>
	</body>
</html>");
