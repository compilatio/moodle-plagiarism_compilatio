<?php

$string["pluginname"] = 'Plug-in Compilatio per il rilevamento del plagio';
$string["studentdisclosuredefault"] = 'L\'insieme dei documenti inviati sarà analizzato dal servizio di rilevamento del plagio di Compilatio';
$string["students_disclosure"] = 'Prevenzione degli studenti';
$string["students_disclosure_help"] = 'Questo testo sarà visibile a tutti gli studenti sulla pagina di upload del documento.';
$string["compilatioexplain"] = 'Per ottenere maggiori informazioni su questo plug-in, visita: <a href="http://www.compilatio.net/it/" target="_blank">compilatio.net</a>';
$string["compilatio"] = 'Plug-in di rilevamento del plagio Compilatio';
$string["compilatioapi"] = 'Indirizzo API';
$string["compilatioapi_help"] = 'È l\'indirizzo API Compilatio';
$string["compilatiopassword"] = 'Chiave API';
$string["compilatiopassword_help"] = 'Codice personale fornito da Compilatio per accedere all\'API';
$string["use_compilatio"] = 'Consentire il rilevamento delle similitudini con Compilatio';
$string["activate_compilatio"] = 'Attivare Compilatio';
$string["savedconfigsuccess"] = 'I parametri sono stati correttamenti salvati';
$string["compilatio_display_student_score_help"] = 'La percentuale di similitudine indica la quantità di testo nel documento che è stato rilevato all\'interno di altri documenti';
$string["compilatio_display_student_score"] = 'Rendere visibile la percentuale di similitudini da parte degli studenti';
$string["compilatio_display_student_report"] = 'Consentire allo studente di visualizzare il rapporto di analisi';
$string["compilatio_display_student_report_help"] = 'Il rapporto di analisi di un documento presenta i passaggi simili alle fonti rilevate e la loro percentuale di similitudine';
$string["compilatio_draft_submit"] = 'Quando il documento deve essere analizzato con Compilatio';
$string["showwhenclosed"] = 'Quando l\'attività è chiusa';
$string["submitondraft"] = 'Sottoporre un documento quando il primo è caricato';
$string["submitonfinal"] = 'Sottoporre un documento quando uno studente lo invia per l\'analisi';
$string["defaultupdated"] = 'I valori pre-impostati sono stati aggiornati';
$string["defaults_desc"] = 'I parametri seguenti sono utilizzati come valori pre-impostati nelle attività di Moodle ove integrato Compilatio';
$string["compilatiodefaults"] = 'Valori pre-impostati per Compilatio';
$string["processing_doc"] = 'Il documento è in corso di analisi da parte di Compilatio';
$string["pending"] = 'Il documento è in attesa di essere sottoposto a Compilatio';
$string["previouslysubmitted"] = 'Sottoposto in precedenza come';
$string["unknownwarning"] = 'Si è verificato un errore durante l\'invio del documento a Compilatio';
$string["unsupportedfiletype"] = 'Questo tipo di documento non è supportato da Compilatio';
$string["toolarge"] = 'Il documento è troppo esteso per essere analizzato da Compilatio';
$string["compilatio_studentemail"] = 'Inviare una mail allo studente';
$string["compilatio_studentemail_help"] = 'Questo invierà una mail allo studente quando un documento sarà stato analizzato per fargli sapere che il rapporto di analisi è disponibile';
$string["studentemailsubject"] = 'Il documento è stato analizzato da Compilatio';
$string["studentemailcontent"] = 'Il documento che ha caricato a {$a->modulename} in {$a->coursename} è stato analizzato dal software di rilevamento del plagio Compilatio {$a->modulelink}';
$string["filereset"] = 'Un documento è stato azzerato per ri-caricamento su Compilatio';
$string["analysis_type"] = 'Avvio delle analisi';
$string["analysis_type_help"] = '<p>Esistono 3 opzioni :</p>
<ul>
<li><strong> Immediato:</strong> Il documento è inviato a Compilatio e subito analizzato.</li>
<li><strong> Manuale:</strong> Il documento è inviato a Compilatio, ma il docente deve avviare manualmente le analisi dei documenti.</li>
<li><strong> Programmato:</strong> Il documento è inviato a Compilatio e successivamente analizzato all\'ora/data scelta.</li>
</ul>
<p>Affinché tutti i documenti siano confrontati tra di loro durante le analisi, consigliamo di avviare le analisi solamente quando tutti i documenti sono consegnati nel compito.</p>';
$string["analysistype_direct"] = 'Immediato';
$string["analysistype_manual"] = 'Manuale';
$string["analysistype_prog"] = 'Programmato';
$string["enabledandworking"] = 'Il plug-in Compilatio è attivo e funzionale';
$string["subscription_state"] = 'Il Suo abbonamento Compilatio.net è valido fino alla fine del mese di {$a->end_date}. Questo mese, ha analizzato l\'equivalente di {$a->used} documenti di almeno 5.000 parole';
$string["startanalysis"] = 'Avviare l\'analisi';
$string["failedanalysis"] = 'Compilatio non è riuscito ad analizzare il suo documento:';
$string["unextractablefile"] = 'Il suo documento non contiene abbastanza parole, o non è stato possibile estrarre correttamente il testo';
$string["auto_diagnosis_title"] = 'Auto-diagnosi';
$string["api_key_valid"] = 'La chiave API registrata è valida';
$string["api_key_not_tested"] = 'Non è stato possibile verificare la chiave API poiché la connessione al servizio Compilatio.net ha fallito';
$string["api_key_not_valid"] = 'La chiave API registrata non è valida. Essa è specifica alla piattaforma utilizzata. Può ottenerne uyna corretta contattando <a href=\'mailto:ent@compilatio.net\'>ent@compilatio.net</a>.';
$string["cron_check_never_called"] = 'CRON non è stato eseguito dopo l\'attivazione del plug-in. È possibile che non sia configurato correttamente';
$string["cron_check"] = 'CRON è stato eseguito l\'ultima volta il {$a}.';
$string["cron_check_not_ok"] = 'Non è stato eseguito da più di un\'ora.';
$string["cron_frequency"] = 'Sembrerebbe che sia eseguito ogni {$a} minuti.';
$string["cron_recommandation"] = 'Raccomandiamo di utilizzare un intervallo di tempo inferiore a 15 minuti tra ogni esecuzione di CRON';
$string["webservice_ok"] = 'Il server è in grado di contattare il webservice';
$string["webservice_not_ok"] = 'Non è statoi possibile contattare il webservice. È possibile che il sui firewall blocchi la connessione';
$string["plugin_enabled"] = 'Il plug-in è attivo per la piattaforma Moodle';
$string["plugin_disabled"] = 'Il plug-in non è attivo per la piattaforma Moodle';
$string["plugin_enabled_assign"] = 'Il plug-in è attivo per i compiti';
$string["plugin_disabled_assign"] = 'Il plug-in non è attivo per i compiti';
$string["plugin_enabled_workshop"] = 'Il plug-in è attivo per i laboratori';
$string["plugin_disabled_workshop"] = 'Il plug-in non è attivo per i laboratori';
$string["plugin_enabled_forum"] = 'Il plug-in è attivo per i forum';
$string["plugin_disabled_forum"] = 'Il plug-in non è attivo per i forum';
$string["compilatioenableplugin"] = 'Attivare Compilatio per {$a}';
$string["programmed_analysis_future"] = 'I documenti saranno analizzati da Compilatio il {$a}.';
$string["programmed_analysis_past"] = 'I documenti sono stati sottoposti per l\'analisi a Compilatio il {$a}.';
$string["webservice_unreachable_title"] = 'Indisponibilità di Compilatio';
$string["webservice_unreachable_content"] = 'Il servizio Compilatio.net è attualmente non disponibile. Ci scusiamo per l\'interruzione momentanea';
$string["saved_config_failed"] = 'La combinazione indirizzo - chiave API non è corretta. Il plug-in è disattivato, La preghiamo di riprovare.
La pagina di <a href="autodiagnosis.php">auto-diagnosi</a> può aiutarla a configurare questo plug-in.
Errore :';
$string["startallcompilatioanalysis"] = 'Analizzare tutti i documenti';
$string["numeric_threshold"] = 'La soglia deve essere numerica';
$string["green_threshold"] = 'Verde fino a';
$string["orange_threshold"] = 'Arancione fino a';
$string["red_threshold"] = 'Rosso oltre';
$string["similarity_percent"] = '% di similitudine';
$string["thresholds_settings"] = 'Personalizzazione delle soglie per la percentuale di similitudini:';
$string["thresholds_description"] = 'Indicare le soglie da utilizzare, in modo da facilitare la classificazione dei rapporti di analisi (% di similitudini)';
$string["similarities_disclaimer"] = 'Può analizzare le similitudini presenti nei documenti di questo compito con l\'aiuto del software <a href=\'http://compilatio.net\' target=\'_blank\'>Compilatio</a>.<br/>
Attenzione, le similitudini rilevate durante un\'analisi non rivelano necessariamente un plagio.
Il rapporto di analisi aiuterà a comprendere se le similitudini corrispondono a dei prestiti e citazioni citati in maniera conveniente o a dei plagi.';
$string["progress"] = 'Avanzamento:';
$string["results"] = 'Risultati:';
$string["errors"] = 'Errori:';
$string["documents_analyzed"] = '{$a->countAnalyzed} documenti su {$a->documentsCount} sono stati analizzati';
$string["documents_analyzing"] = '{$a} documenti in corso di analisi';
$string["documents_in_queue"] = '{$a} documenti in attesa di analisi';
$string["average_similarities"] = 'La percentuale di similitudine media per questo compito è {$a}%';
$string["documents_analyzed_lower_green"] = '{$a->documentsUnderGreenThreshold} documenti inferiori {$a->greenThreshold}%';
$string["documents_analyzed_between_thresholds"] = '{$a->documentsBetweenThresholds} documenti tra {$a->greenThreshold}% e {$a->redThreshold}%.';
$string["documents_analyzed_higher_red"] = '{$a->documentsAboveRedThreshold} documenti superiori a {$a->redThreshold}%.';
$string["unsupported_files"] = 'Non è stato possibile analizzare i seguenti documenti con Compiltio.net perché il loro formato non è supportato:';
$string["unextractable_files"] = 'Non è stato possibile analizzare i seguenti documenti con Compiltio.net. Non contengono abbastanza parole o non è stato possibile estrarre correttamente il loro contenuto:';
$string["no_document_available_for_analysis"] = 'Nessun documento era disponibile per le analisi';
$string["analysis_started"] = '{$a} analisi richieste';
$string["start_analysis_title"] = 'Avvio manuale delle analisi';
$string["not_analyzed"] = 'Non è stato possibile analizzare i seguenti documenti:';
$string["account_expire_soon_title"] = 'Fine dell\'abbonamento Compilatio.net';
$string["account_expire_soon_content"] = 'Disponete del servizio Compilatio integrato a questa piattaforma fino alla fine del mese. Se l\'abbonamento non viene rinnovato, non sarà possibile disporre di Compilatio oltre questa data.';
$string["news_update"] = 'Aggiornamento Compilatio.net';
$string["news_incident"] = 'Incidente Compilatio.net';
$string["news_maintenance"] = 'Manutenzione Compilatio.net';
$string["news_analysis_perturbated"] = 'Analisi Compilatio.net con piccoli disguidi';
$string["updatecompilatioresults"] = 'Aggiornare le informazioni';
$string["display_stats"] = 'Mostrare le statistiche di questo compito';
$string["analysis_completed"] = 'Analisi terminata: {$a}% di similitudini';
$string["compilatio_help_assign"] = 'Ottenere aiuto per il plug-in Compilatio';
$string["display_notifications"] = 'Mostrare le notifiche';
$string["firstname"] = 'Nome';
$string["lastname"] = 'Cognome';
$string["filename"] = 'Nome del documento';
$string["similarities"] = 'Similitudini';
$string["unextractable"] = 'Non è stato possibile estrarre il contenuto di questo documento';
$string["unsupported"] = 'Documento non supportato';
$string["analysing"] = 'Documento in corso di analisi';
$string["timesubmitted"] = 'Sottoposto a Compilatio il';
$string["not_analyzed_unextractable"] = '{$a} documenti non sono stati analizzati perché non contengono abbastanza testo.';
$string["not_analyzed_unsupported"] = '{$a} documenti non sono stati analizzati perché il loro formato non è supportato.';
$string["analysis_date"] = 'Data di analisi (solo avvio programmato)';
$string["export_csv"] = 'Esportare i dati di questo compito in formato CSV';
$string["hide_area"] = 'Nascondere le informazioni Compilatio';
$string["tabs_title_help"] = 'Aiuto';
$string["tabs_title_stats"] = 'Statistiche';
$string["tabs_title_notifications"] = 'Notifiche';
$string["queued"] = 'Il documento è in attesa di analisi e a breve sarà analizzato da Compilatio';
$string["no_documents_available"] = 'Nessun documento è disponibile per l\'analisi in questo compito';
$string["manual_analysis"] = 'L\'analisi di questo documento deve essere avviata manualmente';
$string["updated_analysis"] = 'I risultati dell\'analisi Compilatio sono stati aggiornati';
$string["disclaimer_data"] = 'Attivando Compilatio, accetta che delle informazioni riguardanti la configurazione della sua piattaforma Moodle saranno raccolti in modo da facilitare il supporto tecnico e la manutenzione del servizio';
$string["reset"] = 'Riavviare';
$string["error"] = 'Errore';
$string["analyze"] = 'Analizzare';
$string["queue"] = 'Attesa';
$string["analyzing"] = 'Analisi';
$string["compilatio_enable_mod_assign"] = 'Attivare Compilatio per i compiti (assign)';
$string["compilatio_enable_mod_workshop"] = 'Attivare Compilatio per i laboratori (workshop)';
$string["compilatio_enable_mod_forum"] = 'Attivare Compilatio per i forum';
$string["planned"] = 'Pianificato';
$string["immediately"] = 'Immediatamente';
$string["enable_javascript"] = 'La preghiamo di attivare JavaScript per usufruire di tutte le funzionalità del plug-in Compilatio.<br/> Qui ci sono tutte le <a href="http://www.enable-javascript.com/it/"
target="_blank"> istruzioni su come abilitare JavaScript nel browser utilizzato</a>.';
$string["manual_send_confirmation"] = '{$a} documenti sottoposti a Compilatio';
$string["unsent_documents"] = 'Documenti non sottoposti';
$string["unsent_documents_content"] = 'Attenzione, questa cartella contiene documento(i) non sottoposto(i) a Compilatio.';
$string["statistics_title"] = 'Statistiche';
$string["no_statistics_yet"] = 'Nessuna statistica è disponibile per il momento';
$string["minimum"] = 'Minimo';
$string["maximum"] = 'Massimo';
$string["average"] = 'Media';
$string["documents_number"] = 'Documenti analizzati';
$string["export_raw_csv"] = 'Cliccare qui per esportare i dati globali in formato CSV';
$string["export_global_csv"] = 'Cliccare qui per esportare questi dati in formato CSV';
$string["global_statistics"] = 'Statistiche globali';
$string["assign_statistics"] = 'Statistiche dei compiti';
$string["context"] = 'Contesto';
$string["pending_status"] = 'Attesa';
$string["allow_teachers_to_show_reports"] = 'Consentire ai docenti di mettere i rapporti di analisi a disposizione degli studenti';
$string["admin_disabled_reports"] = 'L\'amministratore ha disattivato la funzionalità che permette di mostrare i rapporti di analisi agli studenti.';
$string["teacher"] = 'Docente';
$string["loading"] = 'Caricamento in corso, si prega di attendere...';



/* HELP */

$string['help_compilatio_settings_title'] = 'Quali parametri scegliere nelle impostazioni Compilatio di un compito?';
$string['help_compilatio_settings_content'] = "Il plug-in Compilatio permette di avviare le analisi secondo 3 opzioni:
<ul>
<li>
Immediato:<br/>
Ogni documento è inviato a Compilatio ed analizzato dal momento in cui lo studente lo deposita.
Consigliato se si desidera avere i risultati il più presto possibile e se non è necessario che tutti i documenti siano confrontati tra di loro
</li>
<li>
Programmato: <br/>
Tutti i documenti vengono analizzati alla data indicata. Scegliere una data di avvio delle analisi Compilatio successiva alla data limite di consegna da parte degli studenti.
Consigliato se si desidera confrontare tutti i documenti del compito tra di loro.
</li>
<li>
Manuale: <br/>
I documenti del compito sono analizzati solo se le analisi sono avviate personalmente. Per avviare l'analisi di un documento, cliccare sul tasto \"Analizzare\" di ogni documento. Il tasto \"Analizzare tutti i documenti\" permette di avviare l'analisi di tutti i documenti presenti nel compito.
</li>
</ul>";



$string['help_compilatio_thresholds_title'] = 'Come modificare il codice colore dei risultati di analisi?';
$string['help_compilatio_thresholds_content'] = "Il codice colore dei risultati può essere personalizzato nelle impostazioni di ogni compito, nella sezione \"Plug-in di rilevamento del plagio Compilatio\"
È possibile scegliere le soglie che determinano il colore della percentuale di similitudine.";

$string['help_compilatio_format_title'] = 'Quali sono i formati di files supportati?';
$string['help_compilatio_format_content'] = "Compilatio.net prende in considerazione la maggior parte dei formati utilizzati.
I seguenti formati sono accettati 
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
</ul>";

$string['help_compilatio_languages_title'] = 'Quali lingue sono supportate?';
$string['help_compilatio_languages_content'] = "Le analisi possono essere effettuate in più di 40 lingue (di cui tutte le lingue scritte in alfabeto latino).
Il cinese, il giapponese, l'arabo e l'alfabeto cirillico non sono ancora supportati";

$string['admin_help_compilatio_api_title'] = 'Come ottenere una chiave API?';
$string['admin_help_compilatio_api_content'] = "Questo plug-in necessita un abbonamento ai servizi Compilatio.net per essere attivato.
Contattare il proprio consulente commerciale, o inviare una richiesta di chiave API all'indirizzo <a href='mailto:ent@compilatio.net'>ent@compilatio.net</a>.";

$string['compilatio_faq'] = "<a target='_blank' href='https://www.compilatio.net/it/faq/'>Domande frequenti - Compilatio.net</a>";


/* END HELP */

$string['compilatio:enable'] = "Autorizzare l'insegnante a attivare/disattivare Compilatio all'interno di un'attività";
$string['compilatio:resetfile'] = "Autorizzare l'insegnante a caricare nuovamente il documento all'interno di Compilatio dopo un errore";
$string['compilatio:triggeranalysis'] = "Autorizzare l'insegnante ad avviare manualmente l'analisi";
$string['compilatio:viewreport'] = "Autorizzare l'insegnante a consultare il rapporto completo dopo l'analisi Compilatio";


$string['get_scores'] = "Recupera la percentuale di similitudini da Compilatio.net";
$string['send_files'] = "Carica i files su Compilatio.net per il rilevamento del plagio";
$string['update_meta'] = "Esegui le operazioni pianificate da Compilatio.net";
$string['trigger_timed_analyses'] = "Avvia le analisi anti plagio programmate";
