<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

//Array of questions to be displayed to the teachers
$teacher = array();
$teacher[] =array(
"title"=>"Which mode should I use in the Compilatio settings of an activity?",
"content"=>"
Three analysis types are available with Compilatio plugin :
<ul>
<li>
Direct : <br/>
Each document is sent to Compilatio and analyzed as soon as it is uploaded by the student.<br/>
Recommended if you wish to get the results quickly and if it is not necessary that all documents are compared with each others.
</li>
<li>
Timed : <br/>
Choose date to start Compilatio analysis, later than the deadline for the students.<br/>
Recommended if you wish to compare all the documents of the activity mutually.
</li>
<li>
Manual : <br/>
The documents of the activity will not be analyzed until you trigger the analysis.<br/>
You can click on the “Start analysis” button of each document to trigger its analysis.
</li>
</ul>

");

$teacher[] =array(
"title"=>"How can I change the colors of the documents’ analysis results?",
"content"=>"
The results' colors can be defined in the settings of each activities, in the section “Compilatio plagiarism plugin”.<br/>
It is possible to choose the thresholds that determines the display color of similarities ratios.
");

$teacher[] =array(
"title"=>"Is it possible to analyze the documents uploaded before the activation of Compilatio plugin ?",
"content"=>"
Compilatio must be enabled in the assignment before the upload of the documents, in order for them to be analyzable.<br/>
If some documents are uploaded before the activation of Compilatio, they will not be analyzed by Compilatio.
");



$teacher[] =array(
"title"=>"Which documents formats are accepted?",
"content"=>"Compilatio.net handles most formats used in word processors and on the internet.
The following formats are supported :
<ul>
<li>
Text '.txt'
</li>
<li>
Adobe Acrobat '.pdf'
</li>
<li>
Rich Text Format '.rtf'
</li>
<li>
Text Processors '.doc', '.docx', '.odt'
</li>
<li>
Spreadsheet '.xls ', '.xlsx'
</li>
<li>
Slideshows '.ppt ', '.pptx'
</li>
<li>
Web files '.html'
</li>
</ul>
");


$teacher[] =array(
"title"=>"Which languages are supported?",
"content"=>"
Compilatio analysis can be performed in more than 40 languages (including all latin languages).<br/>
Chinese, Japanese, Arabic and Cyrillic alphabet are not supported yet.
");

//Array of questions to be displayed to the admin

$admin = array();
$admin[] =array(
"title"=>"How to get an API Key?",
"content"=>"This plugin requires a subscription to Compilatio.net services in order to operate.<br/>
Please reach your commercial contact, or ask for an API key to <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.");


//Link to Compilatio FAQ: 

$more = "<a target='_blank' href='https://www.compilatio.net/en/faq/'>Compilatio.net - Frequently Asked Questions.</a>";





