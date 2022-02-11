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
 * plagiarism_compilatio.php - Contains spanish Plagiarism plugin translation.
 *
 * @since 2.0
 * @package    plagiarism_compilatio
 * @subpackage plagiarism
 * @author     Compilatio <support@compilatio.net>
 * @copyright  2017 Compilatio.net {@link https://www.compilatio.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string["Identifiant"] = 'Texte ES';
$string["pluginname"] = 'Compilatio - Plugin de detección de plagio';
$string["studentdisclosuredefault"] = 'La totalidad de los ficheros enviados serán objeto del servicio de detección de plagio de Compilatio';
$string["students_disclosure"] = 'Mensaje de prevención para los estudiantes';
$string["students_disclosure_help"] = 'Este texto será visible para todos los estudiantes en la página de descarga de ficheros.';
$string["compilatioexplain"] = 'Para obtener informaciones adicionales sobre este plugin, véase : <a href="http://compilatio.net" target="_blank">compilatio.net</a>';
$string["compilatio"] = 'Plugin de detección de plagio Compilatio';
$string["compilatioapi"] = 'Dirección de la API';
$string["compilatioapi_help"] = 'Se trata de la dirección de la API Compilatio';
$string["apikey"] = 'Clave API';
$string["apikey_help"] = 'Código personal provisto por Compilatio para acceder a la API';
$string["use_compilatio"] = 'Detectar similitudes con Compilatio';
$string["activate_compilatio"] = 'Activar el plugin Compilatio';
$string["savedconfigsuccess"] = 'Los parámetros se salvaron correctamente';
$string["compilatio_display_student_score_help"] = 'El porcentaje de similitudes indica la cantidad de texto en el documento que se encontró en otros documentos.';
$string["compilatio_display_student_score"] = 'Hacer que el porcentaje de similitudes sea visible para los estudiantes';
$string["compilatio_display_student_report"] = 'Permitir al estudiante visualizar el informe de análisis';
$string["compilatio_display_student_report_help"] = 'El informe de análisis de un documento presenta los fragmentos similares con las fuentes detectadas y sus porcentajes de similitudes.';
$string["showwhenclosed"] = 'Cuando la actividad está cerrada';
$string["defaultupdated"] = 'Los valores predeterminados han sido actualizados';
$string["defaults_desc"] = 'Los parámetros siguientes son utilizados como valores por defecto en las actividades de Moodle integrando Compilatio.';
$string["compilatiodefaults"] = 'Valores predeterminados para Compilatio.';
$string["processing_doc"] = 'Compilatio está analizando el fichero.';
$string["pending"] = 'Fichero en espera de entrega a Compilatio.';
$string["previouslysubmitted"] = 'Ya entregado como';
$string["unknownwarning"] = 'Un error ocurrió durante el envío del fichero a Compilatio.';
$string["unsupportedfiletype"] = 'Este tipo de fichero no es compatible con Compilatio.';
$string["toolarge"] = 'El fichero es demasiado voluminoso para ser tratado por Compilatio. Tamaño máximo : {$a} Mo';
$string["compilatio_studentemail"] = 'Enviar un e-mail al estudiante';
$string["compilatio_studentemail_help"] = 'Se enviará un e-mail al alumno cuando un fichero ha sido tratado para informarle que el informe está disponible.';
$string["studentemailsubject"] = 'Compilatio trató el fichero.';
$string["studentemailcontent"] = 'El fichero entregado a {$a->modulename} en {$a->coursename} ha sido tratado por la herramienta de detección de plagio Compilatio {$a->modulelink}';
$string["filereset"] = 'Un fichero ha sido reiniciado para re-entrega a Compilatio';
$string["analysis_type"] = 'Lanzamiento de los análisis';
$string["analysis_type_help"] = '<p> Tiene tres opciones :</p> <ul> <li><strong>Inmediato :</strong> El documento está enviado a Compilatio y está analizado inmediatamente.</li><li><strong>Manual :</strong> El documento está enviado a Compilatio, pero el profesor tiene que activar los análisis de los documentos manualmente.</li><li><strong>Programado :</strong> El documento está enviado a Compilatio, luego está analizado a la hora/fecha elegida(s).</li></ul><p>Para que todos los documentos sean comparados entre ellos durante los análisis, activa los análisis sólo cuando todos los documentos están presentes en la tarea.</p>';
$string["analysistype_manual"] = 'Manual';
$string["analysistype_prog"] = 'Programado';
$string["enabledandworking"] = 'El plugin Compilatio está activo y funcional.';
$string["subscription_state"] = '<strong>Su suscripción Compilatio.net está válida hasta el final del mes de {$a->end_date}. Este mes, ha analizado el equivalente de {$a->used} documento(s) de menos de 5000 palabras.</strong>';
$string["startanalysis"] = 'Empezar el análisis';
$string["failedanalysis"] = 'Compilatio no consiguió analizar su documento :';
$string["unextractablefile"] = 'Su documento no tiene bastante palabras o no se pudo extraer el texto correctamente.';
$string["auto_diagnosis_title"] = 'Auto-diagnóstico';
$string["api_key_valid"] = 'La clave API registrada es válida.';
$string["api_key_not_tested"] = 'No se pudo verificar la clave API porque falló la conexión con el servicio Compilatio.net.';
$string["api_key_not_valid"] = 'La clave API registrada no es válida. Es propia a la plataforma utilizada. Se puede obtener otra contactando <a href=\'mailto:ent@compilatio.net\'>ent@compilatio.net</a>.';
$string["cron_check_never_called"] = 'CRON no ha sido ejecutado desde la activación del plugin. Puede que sea configurado incorrectamente.';
$string["cron_check"] = 'CRON se ejecutó por última vez el {$a}.';
$string["cron_check_not_ok"] = 'No ha sido ejecutado desde más de una hora.';
$string["cron_frequency"] = 'Parece que sea ejecutado todos los {$a} minutos.';
$string["cron_recommandation"] = 'Recomendamos utilizar un plazo inferior a 15 minutos entre cada ejecución de CRON.';
$string["webservice_ok"] = 'El servidor es capaz de contactar el webservicio.';
$string["webservice_not_ok"] = 'No se pudo contactar el webservicio. Puede que su cortafuegos bloquee la conexión.';
$string["plugin_enabled"] = 'El plugin está activado para la plataforma Moodle.';
$string["plugin_disabled"] = 'El plugin no está activado para la plataforma Moodle.';
$string["plugin_enabled_assign"] = 'El plugin está activado para las tareas.';
$string["plugin_disabled_assign"] = 'El plugin no está activado para las tareas.';
$string["plugin_enabled_workshop"] = 'El plugin está activado para los talleres.';
$string["plugin_disabled_workshop"] = 'El plugin no está activado para los talleres.';
$string["plugin_enabled_forum"] = 'El plugin está activado para los foros.';
$string["plugin_disabled_forum"] = 'El plugin no está activado para los foros.';
$string["compilatioenableplugin"] = 'Activar Compilatio para {$a}';
$string["programmed_analysis_future"] = 'Compilatio analizará los documentos el {$a}.';
$string["programmed_analysis_past"] = 'Los documentos fueron entregados a Compilatio para analizar el {$a}.';
$string["compilatio:enable"] = 'Autorizar al profesor para activar/desactivar Compilatio en una actividad';
$string["compilatio:resetfile"] = 'Autorizar al profesor para entregar el fichero a Compilatio otra vez después de un error';
$string["compilatio:triggeranalysis"] = 'Autorizar al profesor para activar el análisis manualmente';
$string["compilatio:viewreport"] = 'Autorizar al profesor para consultar el informe completo desde Compilatio';
$string["webservice_unreachable_title"] = 'Indisponibilidad Compilatio.net';
$string["webservice_unreachable_content"] = 'El servicio Compilatio.net está indisponible actualmente. Le rogamos que nos disculpe por la molestia ocasionada.';
$string["saved_config_failed"] = '<strong>La combinación dirección - clave API es incorrecta. El plugin está desactivado, gracias por reensayar.<br/> La página de<a href="autodiagnosis.php">auto-diagnóstico</a> le puede ayudar para configurar este plugin.';
$string["startallcompilatioanalysis"] = 'Analizar todos los documentos';
$string["numeric_threshold"] = 'El umbral tiene que ser numérico.';
$string["green_threshold"] = 'Verde hasta';
$string["orange_threshold"] = 'Naranja hasta';
$string["red_threshold"] = 'Rojo más allá';
$string["similarity_percent"] = '% de similitudes';
$string["thresholds_settings"] = 'Ajuste de los umbrales de visualización de los grados de similitudes :';
$string["thresholds_description"] = 'Indique los umbrales que desea utilizar, para facilitar la localización de los informes de análisis (% de similitudes) :';
$string["similarities_disclaimer"] = 'Se puede analizar las similitudes en los documentos de esta tarea gracias al software <a href=\'http://compilatio.net/es/\' target=\'_blank\'>Compilatio</a>.<br/> Cuidado, similitudes medidas durante un análisis no indican necesariamente un plagio. El informe de análisis le ayuda para comprender si las similitudes corresponden a citaciones y préstamos identificados adecuadamente, o a casos de plagio.';
$string["progress"] = 'Progresión :';
$string["results"] = 'Resultados :';
$string["errors"] = 'Errores :';
$string["documents_analyzed"] = '{$a->countAnalyzed} documento(s) sobre {$a->documentsCount} han sido analizados.';
$string["documents_analyzing"] = '{$a} documento(s) en curso de análisis.';
$string["documents_in_queue"] = '{$a} documento(s) en espera de análisis.';
$string["average_similarities"] = 'El grado de similitudes medio para esta tarea es de {$a}%.';
$string["documents_analyzed_lower_green"] = '{$a->documentsUnderGreenThreshold} documento(s) inferior(es) a {$a->greenThreshold}%.';
$string["documents_analyzed_between_thresholds"] = '{$a->documentsBetweenThresholds} documento(s) entre {$a->greenThreshold}% y {$a->redThreshold}%.';
$string["documents_analyzed_higher_red"] = '{$a->documentsAboveRedThreshold} documento(s) superior(es) a {$a->redThreshold}%.';
$string["unsupported_files"] = 'Compilatio no pudo analizar el(los) fichero(s) siguiente(s) porque su formato es incompatible :';
$string["unextractable_files"] = 'Compilatio no pudo analizar el(los) fichero(s) siguiente(s). No contenían bastante palabras o no se pudo extraer su contenido correctamente :';
$string["no_document_available_for_analysis"] = 'Ningún documento estaba disponible para el análisis.';
$string["analysis_started"] = '{$a} análisis solicitado(s).';
$string["start_analysis_title"] = 'Comienzo manual de los análisis';
$string["not_analyzed"] = 'Los documentos siguientes no pudieron ser analizados :';
$string["account_expire_soon_title"] = 'Final de suscripción Compilatio.net';
$string["account_expire_soon_content"] = 'Dispone del servicio Compilatio en su plataforma hasta el final del mes. Si no se prorroga la suscripción, Compilatio no estará disponible más tras esa fecha.';
$string["news_update"] = 'Actualización Compilatio.net';
$string["news_incident"] = 'Incidente Compilatio.net';
$string["news_maintenance"] = 'Mantenimiento Compilatio.net';
$string["news_analysis_perturbated"] = 'Análisis Compilatio.net perturbadas';
$string["updatecompilatioresults"] = 'Actualizar las informaciones';
$string["update_in_progress"] = "Actualización de la información";
$string["display_stats"] = 'Mostrar las estadísticas de esta tarea';
$string["analysis_completed"] = 'Análisis terminado : {$a}% de similitudes.';
$string["compilatio_help_assign"] = 'Obtener ayuda sobre el plugin Compilatio';
$string["display_notifications"] = 'Mostrar las notificaciones';
$string["firstname"] = 'Nombre';
$string["lastname"] = 'Apellido';
$string["filename"] = 'Nombre del fichero';
$string["similarities"] = 'Similitudes';
$string["unextractable"] = 'No se pudo extraer el contenido de este documento';
$string["unsupported"] = 'Documento incompatible';
$string["analysing"] = 'Documento en curso de análisis';
$string["timesubmitted"] = 'Entregado a Compilatio el';
$string["not_analyzed_unextractable"] = '{$a} documento(s) no fue(ron) analizado(s) porque no contenían bastante texto.';
$string["not_analyzed_unsupported"] = '{$a} documento(s) no fue(ron) analizado(s) porque su formato es incompatible.';
$string["analysis_date"] = 'Fecha de análisis (sólo lanzamiento programado)';
$string["export_csv"] = 'Exportar los datos de esta tarea en formato CSV';
$string["hide_area"] = 'Ocultar las informaciones Compilatio';
$string["tabs_title_help"] = 'Ayuda';
$string["tabs_title_stats"] = 'Estadísticas';
$string["tabs_title_notifications"] = 'Notificaciones';
$string["queued"] = 'El documento está en espera de análisis y será tratado por Compilatio pronto.';
$string["no_documents_available"] = 'Ningún documento está disponible para anaizar en esta tarea.';
$string["manual_analysis"] = 'El análisis de este documento debe ser activado manualmente.';
$string["updated_analysis"] = 'Los resultados del análisis Compilatio han sido actualizados.';
$string["disclaimer_data"] = 'Con la activación de Compilatio, acepta que informaciones relativas a la configuración de su plataforma Moodle sean recogidas a fin de facilitar el soporte y el mantenimiento del servicio.';
$string["reset"] = 'Reactivar';
$string["error"] = 'Error ';
$string["analyze"] = 'Analizar ';
$string["queue"] = 'Espera';
$string["analyzing"] = 'Análisis';
$string["enable_mod_assign"] = 'Activar Compilatio para las tareas (assign)';
$string["enable_mod_workshop"] = 'Activar Compilatio para los talleres (workshop)';
$string["enable_mod_forum"] = 'Activar Compilatio para los foros';
$string["planned"] = 'Planificado';
$string["immediately"] = 'Inmediatamente';
$string["enable_javascript"] = 'Por favor, active JavaScript para aprovechar todas las funcionalidades del plugin Compilatio. <br/> Aquí se puede encontrar las<a href=\'http://www.enable-javascript.com/es/\' target=\'_blank\'> instrucciones para activar JavaScript en su navegador Web</a>.';
$string["manual_send_confirmation"] = '{$a} fichero(s) entregado(s) a Compilatio.';
$string["unsent_documents"] = 'Documento(s) no entregado(s)';
$string["unsent_documents_content"] = 'Cuidado, esta tarea contiene un(os) documento(s) no entregado(s) a Compilatio.';
$string["statistics_title"] = 'Estadísticas';
$string["no_statistics_yet"] = 'No hay ningunas estadísticas por el momento.';
$string["minimum"] = 'Mínimo';
$string["maximum"] = 'Máximo';
$string["average"] = 'Media';
$string["documents_number"] = 'Documentos analizados';
$string["export_raw_csv"] = 'Haga clic aquí para exportar los datos brutos en formato CSV';
$string["export_global_csv"] = 'Haga clic aquí para exportar estos datos en formato CSV';
$string["global_statistics"] = 'Estadísticas globales';
$string["assign_statistics"] = 'Estadísticas de la tareas';
$string["context"] = 'Contexto';
$string["pending_status"] = 'Espera';
$string["allow_teachers_to_show_reports"] = 'Autorizar a los profesores para poner los informes de análisis a disposición de los estudiantes.';
$string["admin_disabled_reports"] = 'La visualización de los informes de similitudes para los estudiantes fue desactivada por el administrador.';
$string["teacher"] = 'Profesor';
$string["loading"] = 'Cargando, un momento por favor...';
$string["waiting_time_title"] = "El tiempo estimado de procesamiento de cualquier análisis ejecutada ahora es de ";
$string["waiting_time_content"] = 'De los cuales {$a->queue} de cola y {$a->analysis_time} de análisis<br><br>Haga clic <a href=\'../../plagiarism/compilatio/helpcenter.php?page=moodle-info-waiting&idgroupe=';
$string["waiting_time_content_help"] = "' target='_blank'>aquí</a>, si quiere conocer cómo optimizar el tiempo de sus análisis con Compilatio.";

// ALERTS.
$string["unknownlang"] = "Atención, el idioma de algunos pasajes de este documento no fue reconocido.";
// Help.
$string["help_compilatio_format_content"] = 'Compilatio.net es compatible con la mayoría de los formatos utilizados en la ofimática y en Internet. Se aceptan los formatos siguientes : ';
$string['goto_helpcenter'] = "Haga clic en el signo de interrogación para abrir una nueva ventana y conectarse al centro de ayuda Compilatio.";
$string['admin_goto_helpcenter'] = "Visite el centro de ayuda Compilatio para acceder a los artículos relativos a la administración del plugin Moodle.";
// Buttons.
$string['get_scores'] = "Recupera los grados de similitudes de Compilatio.net";
$string['send_files'] = "Envia los documentos a Compilatio.net";
$string['update_meta'] = "Realiza las tareas programadas de Compilatio.net";
$string['trigger_timed_analyses'] = "Provoca los análisis programadas";
// Indexing state.
$string['indexing_state'] = "Añadir el documento a la biblioteca de referencias";
$string['indexing_state_help'] = "El contenido del documento es indexado a la biblioteca de referencias. Sirve como base de comparación para los próximos análisis.";
$string['indexed_document'] = "Documento añadido a la biblioteca de referencias de su centro. Su contenido podrá ser utilizado para detectar similitudes con otros documentos.";
$string['not_indexed_document'] = "Documento no añadido a la biblioteca de referencias de su centro. Su contenido no será utilizado para detectar similitudes con otros documentos.";
// Information settings.
$string['information_settings'] = "Información";
// Max file size allowed.
$string['max_file_size_allowed'] = 'Tamaño máximo de los documentos : <strong>{$a} Mo</strong>';
// Failed documents.
$string['restart_failed_analysis'] = 'Lanzar de nuevo los análisis que fracasaron';
$string['restart_failed_analysis_title'] = 'Lanzar de nuevo los análisis que fracasaron :';
// Max attempt reached.
$string['max_attempts_reach_files'] = 'Los ficheros siguientes no pudieron ser analizados por Compilatio. El límite de lanzamiento de análisis ha sido alcanzado :';
// Privacy (GDPR).
$string['privacy:metadata:core_files'] = 'Archivos almacenados o creados desde un campo de entrada de datos';
$string['privacy:metadata:core_plagiarism'] = 'Este plugin es operado por el subsistema de detección de plagio de Moodle';

$string['privacy:metadata:plagiarism_compilatio_files'] = 'Información en cuanto a los archivos enviados a Compilatio en la base de datos del plugin';
$string['privacy:metadata:plagiarism_compilatio_files:id'] = 'El identificador del envío en la base de datos de Moodle';
$string['privacy:metadata:plagiarism_compilatio_files:cm'] = 'El identificador del módulo del curso donde se encuentra el envío';
$string['privacy:metadata:plagiarism_compilatio_files:userid'] = 'El identificador del usuario de Moodle que ha realizado el envío';
$string['privacy:metadata:plagiarism_compilatio_files:identifier'] = 'La contenthash del envío';
$string['privacy:metadata:plagiarism_compilatio_files:filename'] = 'El nombre (posiblemente autogenerado) del envío';
$string['privacy:metadata:plagiarism_compilatio_files:timesubmitted'] = 'La hora a la que el archivo se guardó en la base de datos Moodle del plugin';
$string['privacy:metadata:plagiarism_compilatio_files:externalid'] = 'El identificador del envío en la base de datos de Compilatio';
$string['privacy:metadata:plagiarism_compilatio_files:statuscode'] = 'El estado de análisis del envío (Analizado, En espera, Tiempo sobrepasado…)';
$string['privacy:metadata:plagiarism_compilatio_files:reporturl'] = 'La dirección URL del informe de análisis';
$string['privacy:metadata:plagiarism_compilatio_files:similarityscore'] = 'El porcentaje de similitudes encontradas para este envío';
$string['privacy:metadata:plagiarism_compilatio_files:attempt'] = 'El número de veces que un usuario intentó iniciar el análisis de un envío';

$string['privacy:metadata:external_compilatio_document'] = 'Información en cuanto a documentos en la base de datos de Compilatio';
$string['privacy:metadata:external_compilatio_document:lastname'] = 'Apellido del usuario Compilatio que ha enviado el archivo - atención: este usuario es aquel que está vinculado con la clave API Compilatio en la plataforma Moodle (es, por lo tanto, a menudo el administrador de la plataforma)';
$string['privacy:metadata:external_compilatio_document:firstname'] = 'Nombre de pila del usuario Compilatio que ha enviado el archivo - atención: este usuario es aquel que está vinculado con la clave API Compilatio en la plataforma Moodle (es, por lo tanto, a menudo el administrador de la plataforma)';
$string['privacy:metadata:external_compilatio_document:email_adress'] = 'Dirección de correo electrónico del usuario Compilatio que ha enviado el archivo - atención: este usuario es aquel que está vinculado con la clave API Compilatio en la plataforma Moodle (es, por lo tanto, a menudo el administrador de la plataforma)';
$string['privacy:metadata:external_compilatio_document:user_id'] = 'El identificador del usuario Compilatio que ha enviado el archivo - atención: este usuario es aquel que está vinculado con la clave API Compilatio en la plataforma Moodle (es, por lo tanto, a menudo el administrador de la plataforma)';
$string['privacy:metadata:external_compilatio_document:filename'] = 'El nombre del envío';
$string['privacy:metadata:external_compilatio_document:upload_date'] = 'La hora a la que el archivo se guardó en la base de datos Compilatio';
$string['privacy:metadata:external_compilatio_document:id'] = 'El identificador del envío en la base de datos de Compilatio';
$string['privacy:metadata:external_compilatio_document:indexed'] = 'El estado de indexación del envío (si se utiliza como documento de referencia durante los análisis)';

$string['privacy:metadata:external_compilatio_report'] = 'Información en cuanto al informe de análisis en la base de datos de Compilatio (únicamente si el documento ha sido analizado)';
$string['privacy:metadata:external_compilatio_report:id'] = 'El identificador Compilatio del informe de análisis';
$string['privacy:metadata:external_compilatio_report:doc_id'] = 'El identificador Compilatio del documento que ha sido analizado';
$string['privacy:metadata:external_compilatio_report:user_id'] = 'El identificador del usuario Compilatio que ha enviado el archivo - atención: este usuario es aquel que está vinculado con la clave API Compilatio en la plataforma Moodle (es, por lo tanto, a menudo el administrador de la plataforma)';
$string['privacy:metadata:external_compilatio_report:start'] = 'La fecha de inicio del análisis';
$string['privacy:metadata:external_compilatio_report:end'] = 'La fecha del final del análisis';
$string['privacy:metadata:external_compilatio_report:state'] = 'El estado de análisis del envío (Analizado, En espera, Tiempo sobrepasado…)';
$string['privacy:metadata:external_compilatio_report:plagiarism_percent'] = 'El porcentaje de similitudes encontradas para este envío';

$string['owner_file'] = 'RGPD y propiedad de la tarea';
$string['owner_file_school'] = 'El centro es el propietario de las tareas';
$string['owner_file_school_details'] = 'En caso de solicitud de supresión de los datos personales de un alumno, el contenido de las tareas se conservará y estará disponible para una futura comparación con nuevas tareas. Al vencimiento del contrato con Compilatio, todos los datos personales de su centro, incluidas las tareas, se suprimen en los plazos previstos contractualmente.';
$string['owner_file_student'] = 'El alumno es el único propietario de su tarea';
$string['owner_file_student_details'] = 'En caso de solicitud de supresión de los datos personales de un alumno, las tareas se suprimirán de la plataforma Moodle y de la biblioteca de referencias Compilatio. Las tareas dejarán de estar disponibles para una comparación con nuevos documentos.';
