<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}
//Array of questions to be displayed to the teachers
$teacher = array();
$teacher[] =array(
"title"=>"Quels réglages choisir dans les paramètres Compilatio d’une activité?",
"content"=>"Trois types d’analyse sont disponibles avec le plugin Compilatio :
<ul>
<li>
Immédiat : <br/>
Chaque document est envoyé à Compilatio et analysé dès le dépôt par l’étudiant. 
Recommandé si vous souhaitez avoir vos résultats au plus vite et qu’il n’est pas nécessaire que tous les documents de l’activité soient comparés mutuellement.
</li>
<li>
Programmé : <br/>
Choisissez une date de démarrage des analyses Compilatio postérieure à la date limite de rendu par les étudiants. 
Recommandé si vous souhaitez comparer tous les documents de votre activité entre eux.
</li>
<li>
Manuel : <br/>
Les documents de votre activité ne sont analysés que si vous démarrez vous-même les analyses.
Pour lancer l’analyse d’un document, cliquez sur le bouton “analyser” de chaque document.
Le bouton “Analyser tous les documents” vous permet de lancer l’analyse de tous les documents présents dans un devoir.
</li>
</ul>

");

$teacher[] =array(
"title"=>"Comment modifier la couleur du résultat d’analyse des documents ?",
"content"=>"Les couleurs des résultats peuvent être définies dans la configuration de chaque devoir, dans la rubrique “Plugin de détection de plagiat Compilatio”.<br/>
 Il est possible de choisir les seuils qui déterminent la couleur d’affichage des taux de similitudes.");

$teacher[] =array(
"title"=>"Est-il possible d’analyser les documents déposés avant l’activation du plugin Compilatio ?",
"content"=>"Compilatio doit être activé dans un devoir avant le dépôt des documents, pour que les documents puissent être analysés. <br/>
Si les documents sont déposés avant activation de Compilatio, ils ne pourront pas être analysés par Compilatio.");



$teacher[] =array(
"title"=>"Quels sont les formats de documents supportés?",
"content"=>"Compilatio.net prend en charge la plupart des formats utilisés en bureautique et sur Internet.
Les formats suivants sont acceptés :
<ul>
	<li>
		Texte '.txt'
	</li>
	<li>
		Adobe Acrobat '.pdf'
	</li>
	<li>
		Texte enrichi '.rtf'
	</li>
	<li>
		Traitement de texte '.doc', '.docx', '.odt'
	</li>
	<li>
		Tableur '.xls ', '.xlsx'
	</li>
	<li>
		Diaporamas '.ppt ', '.pptx'
	</li>
	<li>
		Fichiers '.html'
	</li>
</ul>
");


$teacher[] =array(
"title"=>"Quelles sont les langues supportées?",
"content"=>"Les analyses de similitudes peuvent être effectuées avec plus de 40 langues (dont toutes les langues latines).<br/>
Le chinois, le japonais, l’arabe et l’alphabet cyrillique ne sont pas encore supportés.");






//Array of questions to be displayed to the admin

$admin = array();
$admin[] =array(
"title"=>"Comment obtenir une clé API?",
"content"=>"Ce plugin nécessite un abonnement aux services Compilatio.net pour fonctionner. <br/>
Contactez votre interlocuteur commercial, ou faites une demande de clé API à l’adresse <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.");

//Link to Compilatio FAQ: 

$more = "<a target='_blank' href='https://www.compilatio.net/faq/'>Questions fréquemment posées - Compilatio.net</a>";




