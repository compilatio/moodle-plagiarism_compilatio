<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

//Array of questions to be displayed to the teachers
$teacher = array();
$teacher[] = array(
    "title" => "Quale regolazione scegliere nei parametri Compilatio di un'attività?",
    "content" => "3 tipi di analisi sono disponibili con il plug-in Compilatio:
<ul>
<li>
Immediato:<br/>
Ogni documento è inviato a Compilatio ed analizzato dal momento in cui lo studente lo deposita
Consigliato se desidera avere i suoi risultati il più presto possibile e che non sai necessario che tutti i documenti siano paragonati tra di loro
</li>
<li>
Programmato: <br/>
Scelga una data di avvio delle analisi Compilatio successiva alla data limite di consegna da parte degli studenti.
Consigliato se desidera paragonare tutti i documenti della sua attività tra di loro
</li>
<li>
Manuale: <br/>
I documenti della sua attività sono analizzati solo se Lei personalmnete avvia le analisi. Per avviare l'analisi di un documento, clicchi sul tasto \"analizzare\" di ogni documento. Il tasto \"analizzare tutti i documenti\" le permette di avviare l'analisi di tutti i documenti presenti in una cartella
</li>
</ul>
");

$teacher[] = array(
    "title" => "Come modificare i colori dei risultati di analisi dei documenti?",
    "content" => "
I colori dei risultati possono essere definiti nella configurazione di ogni cartella, nella sezione \"Plug-in di rilevamento de plagio Compilatio\"<br/>
È possibile scegliere le soglie che determinano il colore mostrato per i tasso di similitudini
");

$teacher[] = array(
    "title" => "Quali sono i formati dei doculenti supportati?",
    "content" => "Compilatio.net prende in considerazione la maggior parte dei formati utilizzati in burotica e su Internet.
I formati seguenti sono accettati :
<ul>
<li>
Testo '.txt'
</li>
<li>
Adobe Acrobat '.pdf'
</li>
<li>
Rich Text Format '.rtf'
</li>
<li>
Trattamento del testo '.doc', '.docx', '.odt'
</li>
<li>
Tabelle '.xls ', '.xlsx'
</li>
<li>
Presentazioni '.ppt ', '.pptx'
</li>
<li>
Files '.html'
</li>
</ul>
");


$teacher[] = array(
    "title" => "Quali lingue sono supportate?",
    "content" => "
Le analisi delle similitudini possono essere effettuate con più di 40 lingue (di cui tutte le lingue scritte in alfabeto latino)
I cinese, il giapponese, l'arabo e l'alfabeto cirillico non sono ancora supportate
");

//Array of questions to be displayed to the admin
$admin = array();
$admin[] = array(
    "title" => "Come ottenere una chiave API?",
    "content" => "Questo plug-in necessita un abbonamento ai servizi Compilatio.net
Contatti il suo consulente commerciale, o invii una richiesta di chiave API all'indirizzo <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.");

//Link to Compilatio FAQ: 
$more = "<a target='_blank' href='https://www.compilatio.net/it/faq/'>Domande frequenti - Compilatio.net</a>";





