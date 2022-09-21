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
 * plagiarism_compilatio.php - Contains italian Plagiarism plugin translation.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
$string["showwhenclosed"] = 'Quando l\'attività è chiusa';
$string["defaultupdated"] = 'I valori pre-impostati sono stati aggiornati';
$string["defaults_desc"] = 'I parametri seguenti sono utilizzati come valori pre-impostati nelle attività di Moodle ove integrato Compilatio';
$string["compilatiodefaults"] = 'Valori pre-impostati per Compilatio';
$string["processing_doc"] = 'Il documento è in corso di analisi da parte di Compilatio';
$string["pending"] = 'Il documento è in attesa di essere sottoposto a Compilatio';
$string["previouslysubmitted"] = 'Sottoposto in precedenza come';
$string["unknownwarning"] = 'Si è verificato un errore durante l\'invio del documento a Compilatio';
$string["unsupportedfiletype"] = 'Questo tipo di documento non è supportato da Compilatio';
$string["toolarge"] = 'Il documento è troppo esteso per essere analizzato da Compilatio. Grandezza massima : {$a->Mo} MB';
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
$string["analysistype_manual"] = 'Manuale';
$string["analysistype_prog"] = 'Programmato';
$string["enabledandworking"] = 'Il plug-in Compilatio è attivo e funzionale';
$string["subscription_state"] = 'Il Suo abbonamento Compilatio.net è valido fino alla fine del mese di {$a->end_date}.';
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
$string["update_in_progress"] = "Aggiornamento informazioni";
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
$string["enable_mod_assign"] = 'Attivare Compilatio per i compiti (assign)';
$string["enable_mod_workshop"] = 'Attivare Compilatio per i laboratori (workshop)';
$string["enable_mod_forum"] = 'Attivare Compilatio per i forum';
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
$string["waiting_time_title"] = "Per ogni analisi avviata ora, il tempo di trattamento è stimato a ";
$string["waiting_time_content"] = 'Diviso in {$a->queue} in lista d\'attesa e {$a->analysis_time} di analisi<br><br>Cliccare <a href=\'../../plagiarism/compilatio/helpcenter.php?page=moodle-info-waiting&idgroupe=';
$string["waiting_time_content_help"] = "' target='blank'>qui</a> per conoscere le buone prassi da seguire per ottimizzare il tempo di trattamento delle analisi Compilatio.";

// ALERTS.
$string["unknownlang"] = "Attenzione, la lingua di alcuni passaggi di questo documento non è stata riconosciuta.";
// Help.
$string['help_compilatio_format_content'] = "Compilatio.net prende in considerazione la maggior parte dei formati utilizzati.
I seguenti formati sono accettati";
$string['goto_helpcenter'] = "Clicca sul punto di domanda per aprire una nuova finestra e collegarti al centro di assistenza Compilatio.";
$string['admin_goto_helpcenter'] = "Accedi al centro di assistenza Compilatio per accedere agli articoli relativi alla gestione del plugin Moodle.";
// Buttons.
$string['compilatio:enable'] = "Autorizzare l'insegnante a attivare/disattivare Compilatio all'interno di un'attività";
$string['compilatio:resetfile'] = "Autorizzare l'insegnante a caricare nuovamente il documento all'interno di Compilatio dopo un errore";
$string['compilatio:triggeranalysis'] = "Autorizzare l'insegnante ad avviare manualmente l'analisi";
$string['compilatio:viewreport'] = "Autorizzare l'insegnante a consultare il rapporto completo dopo l'analisi Compilatio";
$string['get_scores'] = "Recupera la percentuale di similitudini da Compilatio.net";
$string['send_files'] = "Carica i files su Compilatio.net per il rilevamento del plagio";
$string['update_meta'] = "Esegui le operazioni pianificate da Compilatio.net";
// Indexing state.
$string['indexing_state'] = "Aggiungere i documenti alla biblioteca di riferimento";
$string['indexing_state_help'] = "Il contenuto dei documenti è indicizzato nella biblioteca di riferimento. Verrà utilizzato come materiale di confronto per le future analisi.";
$string['indexed_document'] = "Documento indicizzato nella biblioteca di riferimento";
$string['not_indexed_document'] = "Documento non indicizzato nella biblioteca di riferimento";
// Information settings.
$string['information_settings'] = "Informazioni";
// Max file size allowed.
$string['max_file_size_allowed'] = 'Grandezza massima dei documenti : <strong>{$a->Mo} MB</strong>';
// Max attempt reached.
$string['max_attempts_reach_files'] = 'I file seguenti non sono stati analizzati da Compilatio. Il limite di riavvio delle analisi è stato raggiunto :';
// Privacy (RGDP).
$string['privacy:metadata:core_files'] = "File caricati o creati da un campo di input";
$string['privacy:metadata:core_plagiarism'] = "Questo plugin è chiamato dal sottosistema di rilevamento plagio di Moodle";

$string['privacy:metadata:plagiarism_compilatio_files'] = "Informazioni sui file inviati a Compilatio nel database dei plugin";
$string['privacy:metadata:plagiarism_compilatio_files:id'] = "L'identificante del documento inviato nel database di Moodle";
$string['privacy:metadata:plagiarism_compilatio_files:cm'] = "L'identificante del modulo del corso in cui si trova il documento inviato";
$string['privacy:metadata:plagiarism_compilatio_files:userid'] = "L'identificante dell'utente Moodle che ha effettuato l’invio del documento";
$string['privacy:metadata:plagiarism_compilatio_files:identifier'] = "La contenthash del documento inviato";
$string['privacy:metadata:plagiarism_compilatio_files:filename'] = "Il nome (eventualmente generato automaticamente) del documento inviato";
$string['privacy:metadata:plagiarism_compilatio_files:timesubmitted'] = "Il momento in cui il file è stato salvato nel database di Moodle del plugin";
$string['privacy:metadata:plagiarism_compilatio_files:externalid'] = "L'Identificante del documento inviato nel database di Compilatio";
$string['privacy:metadata:plagiarism_compilatio_files:statuscode'] = "Lo stato del documento inviato (analisi, attesa, tempo passato...)";
$string['privacy:metadata:plagiarism_compilatio_files:reporturl'] = "L'indirizzo URL del rapporto di analisi";
$string['privacy:metadata:plagiarism_compilatio_files:similarityscore'] = "La percentuale di somiglianze riscontrate per questo documento inviato";
$string['privacy:metadata:plagiarism_compilatio_files:attempt'] = "Il numero di volte che un utente ha tentato di eseguire l'analisi di un documento inviato";
$string['privacy:metadata:plagiarism_compilatio_files:errorresponse'] = "La risposta in caso di errore - attualmente questo campo non è più utilizzato e viene impostato automaticamente su 'NULL'";

$string['privacy:metadata:external_compilatio_document'] = "Informazioni sui documenti nel database Compilatio";
$string['privacy:metadata:external_compilatio_document:lastname'] = "Nome dell'utente Compilatio che ha inviato il file - attenzione, questo utente è quello che è collegato alla chiave API di Compilatio sulla piattaforma Moodle (quindi probabilmente è l'amministratore della piattaforma)";
$string['privacy:metadata:external_compilatio_document:firstname'] = "Nome dell'utente della Compilatio che ha inviato il file - attenzione, questo utente è quello che è collegato alla chiave API Compilatio sulla piattaforma Moodle (quindi probabilmente è l'amministratore della piattaforma)";
$string['privacy:metadata:external_compilatio_document:email_adress'] = "Indirizzo e-mail dell'utente Compilatio che ha inviato il file - attenzione, questo utente è quello che è collegato alla chiave API Compilatio sulla piattaforma Moodle (quindi probabilmente è l'amministratore della piattaforma)";
$string['privacy:metadata:external_compilatio_document:user_id'] = "L'identificante dell'utente Compilatio che ha inviato il file - attenzione, questo utente è quello che è collegato alla chiave API Compilatio sulla piattaforma Moodle (quindi probabilmente è l'amministratore della piattaforma)";
$string['privacy:metadata:external_compilatio_document:filename'] = "Il nome del documento inviato";
$string['privacy:metadata:external_compilatio_document:upload_date'] = "L'ora in cui il file è stato salvato nel database Compilatio";
$string['privacy:metadata:external_compilatio_document:id'] = "L'identificante del documento inviato nel database di Compilatio";
$string['privacy:metadata:external_compilatio_document:indexed'] = "Lo stato di indicizzazione del documento inviato (se utilizzato come riferimento per l'analisi)";

$string['privacy:metadata:external_compilatio_report'] = "Informazioni sul rapporto di analisi nel database del Compilatio (solo se il documento è stato analizzato)";
$string['privacy:metadata:external_compilatio_report:id'] = "L'identificatore Compilatio del rapporto di analisi";
$string['privacy:metadata:external_compilatio_report:doc_id'] = "L'identificatore Compilatio del documento che è stato analizzato";
$string['privacy:metadata:external_compilatio_report:user_id'] = "L'Identificante dell'utente Compilatio che ha inviato il file - attenzione, questo utente è quello che è collegato alla chiave API Compilatio sulla piattaforma Moodle (quindi spesso è l'amministratore della piattaforma)";
$string['privacy:metadata:external_compilatio_report:start'] = "La data di inizio dell'analisi";
$string['privacy:metadata:external_compilatio_report:end'] = "La data di fine dell'analisi";
$string['privacy:metadata:external_compilatio_report:state'] = "Lo stato d’analisi del documento inviato (Analisi, Attesa, Tempo passato...)";
$string['privacy:metadata:external_compilatio_report:plagiarism_percent'] = "La percentuale di somiglianze riscontrate per il documento inviato";

$string['owner_file'] = 'RGPD e proprietà dei testi';
$string['owner_file_school'] = 'Lo stabilimento è proprietario degli elaborati';
$string['owner_file_school_details'] = 'In caso di richiesta di cancellazione dei dati personali di un allievo, il contenuto dei suoi elaborati sarà disponibile per un confronto futuro con eventuali nuovi testi. Al termine del contratto con Compilatio, tutti i dati personali del vostro stabilimento – compresi gli elaborati caricati sul sito – saranno cancellati entro i termini contrattuali.';
$string['owner_file_student'] = 'L\'allievo è l\'unico proprietario dei suoi elaborati';
$string['owner_file_student_details'] = 'In caso di richiesta di cancellazione dei dati personali di uno studente, i suoi elaborati saranno cancellati dalla piattaforma Moodle e dalla biblioteca di riferimento di Compilatio. Gli elaborati non saranno più disponibili per un confronto con nuovi documenti.';
