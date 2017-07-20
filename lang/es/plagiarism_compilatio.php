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

$string["Identifiant"] = 'Texte ES';
$string["pluginname"] = 'Compilatio - Plugin de detección de plagio';
$string["studentdisclosuredefault"] = 'La totalidad de los ficheros enviados serán objeto del servicio de detección de plagio de Compilatio';
$string["students_disclosure"] = 'Mensaje de prevención para los estudiantes';
$string["students_disclosure_help"] = 'Este texto será visible para todos los estudiantes en la página de descarga de ficheros.';
$string["compilatioexplain"] = 'Para obtener informaciones adicionales sobre este plugin, véase : <a href="http://compilatio.net" target="_blank">compilatio.net</a>';
$string["compilatio"] = 'Plugin de detección de plagio Compilatio';
$string["compilatioapi"] = 'Dirección de la API';
$string["compilatioapi_help"] = 'Se trata de la dirección de la API Compilatio';
$string["compilatiopassword"] = 'Clave API';
$string["compilatiopassword_help"] = 'Código personal provisto por Compilatio para acceder a la API';
$string["use_compilatio"] = 'Detectar similitudes con Compilatio';
$string["activate_compilatio"] = 'Activar el plugin Compilatio';
$string["savedconfigsuccess"] = 'Los parámetros se salvaron correctamente';
$string["compilatio_display_student_score_help"] = 'El porcentaje de similitudes indica la cantidad de texto en el documento que se encontró en otros documentos.';
$string["compilatio_display_student_score"] = 'Hacer que el porcentaje de similitudes sea visible para los estudiantes';
$string["compilatio_display_student_report"] = 'Permitir al estudiante visualizar el informe de análisis';
$string["compilatio_display_student_report_help"] = 'El informe de análisis de un documento presenta los fragmentos similares con las fuentes detectadas y sus porcentajes de similitudes.';
$string["compilatio_draft_submit"] = 'Cuando el fichero debe ser entregado a Compilatio';
$string["showwhenclosed"] = 'Cuando la actividad está cerrada';
$string["submitondraft"] = 'Entregar un fichero cuando el primero se subió';
$string["submitonfinal"] = 'Entregar un fichero cuando un estudiante lo envía para el análisis';
$string["defaultupdated"] = 'Los valores predeterminados han sido actualizados';
$string["defaults_desc"] = 'Los parámetros siguientes son utilizados como valores por defecto en las actividades de Moodle integrando Compilatio.';
$string["compilatiodefaults"] = 'Valores predeterminados para Compilatio.';
$string["processing_doc"] = 'Compilatio está analizando el fichero.';
$string["pending"] = 'Fichero en espera de entrega a Compilatio.';
$string["previouslysubmitted"] = 'Ya entregado como';
$string["unknownwarning"] = 'Un error ocurrió durante el envío del fichero a Compilatio.';
$string["unsupportedfiletype"] = 'Este tipo de fichero no es compatible con Compilatio.';
$string["toolarge"] = 'El fichero es demasiado voluminoso para ser tratado por Compilatio. Tamaño máximo : {$a->Mo} Mo';
$string["compilatio_studentemail"] = 'Enviar un e-mail al estudiante';
$string["compilatio_studentemail_help"] = 'Se enviará un e-mail al alumno cuando un fichero ha sido tratado para informarle que el informe está disponible.';
$string["studentemailsubject"] = 'Compilatio trató el fichero.';
$string["studentemailcontent"] = 'El fichero entregado a {$a->modulename} en {$a->coursename} ha sido tratado por la herramienta de detección de plagio Compilatio {$a->modulelink}';
$string["filereset"] = 'Un fichero ha sido reiniciado para re-entrega a Compilatio';
$string["analysis_type"] = 'Lanzamiento de los análisis';
$string["analysis_type_help"] = '<p> Tiene tres opciones :</p> <ul> <li><strong>Inmediato :</strong> El documento está enviado a Compilatio y está analizado inmediatamente.</li><li><strong>Manual :</strong> El documento está enviado a Compilatio, pero el profesor tiene que activar los análisis de los documentos manualmente.</li><li><strong>Programado :</strong> El documento está enviado a Compilatio, luego está analizado a la hora/fecha elegida(s).</li></ul><p>Para que todos los documentos sean comparados entre ellos durante los análisis, activa los análisis sólo cuando todos los documentos están presentes en la tarea.</p>';
$string["analysistype_direct"] = 'Inmediato';
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
$string["compilatio_enable_mod_assign"] = 'Activar Compilatio para las tareas (assign)';
$string["compilatio_enable_mod_workshop"] = 'Activar Compilatio para los talleres (workshop)';
$string["compilatio_enable_mod_forum"] = 'Activar Compilatio para los foros';
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
$string["help_compilatio_settings_title"] = '¿Qué ajustes elegir en los parámetros Compilatio de una actividad?';
$string["help_compilatio_settings_content"] = 'Tres tipos de análisis son disponibles con el plugin Compilatio : <ul><li>Inmediato : Cada documento está enviado a Compilatio y analizado tan pronto como está entregado por el estudiante. Recomendado si desea obtener los resultados lo más rápidamente posible, y si no es necesario que todos los documentos de la actividad sean comparados mutualmente. </li><li>Programado : <br/> Elige una fecha de comienzo de los análisis Compilatio posterior a la fecha límite de entrega de los estudiantes.Recomendado si desea comparar todos los documentos de su actividad entre ellos. </li><li> Manual : <br/> Los documentos de su actividad están analizados sólo si usted mismo activa los análisis. Para empezar el análisis de un documento, haga clic en el botón "analizar" de cada documento. El botón "analizar todos los documentos" le permite activar el análisis de todos los documentos de una tarea. </li></ul>';
$string["help_compilatio_thresholds_title"] = '¿Cómo modificar el color del resultado de análisis de los documentos?';
$string["help_compilatio_thresholds_content"] = 'Se puede definir los colores de los resultados en la configuraciónde cada tarea, en la sección "Plugin de detección de plagio Compilatio".<br/> Es posible elegir los umbrales que determinan el color de visualización de los grados de similitudes.';
$string["help_compilatio_format_title"] = '¿Cuáles son los formatos de documentos compatibles?';
$string["help_compilatio_format_content"] = 'Compilatio.net es compatible con la mayoría de los formatos utilizados en la ofimática y en Internet. Se aceptan los formatos siguientes :';
$string["help_compilatio_languages_title"] = '¿Cuáles son los idiomas compatibles?';
$string["help_compilatio_languages_content"] = 'Los análisis de similitudes pueden ser realizadas con más de 40 idiomas (incluidos todos las lenguas latinas).<br/> El chino, el japonés, el arabe y el alfabeto cirílico todavía no son compatibles. ';
$string["admin_help_compilatio_api_title"] = '¿Cómo obtener una clave API?';
$string["admin_help_compilatio_api_content"] = 'Para funcionar, este plugin necesita una suscripción a los servicios Compilatio.net. <br/> Póngase en contacto con su interlocutor comerical, o pregunte por una clave API en la dirección <a href=\'mailto:ent@compilatio.net\'>ent@compilatio.net</a>.';
$string["compilatio_faq"] = '<a target=\'_blank\' href=\'https://www.compilatio.net/es/faq/\'>Preguntas más frecuentes - Compilatio.net</a>';

$string['get_scores'] = "Recupera los grados de similitudes de Compilatio.net";
$string['send_files'] = "Envia los documentos a Compilatio.net";
$string['update_meta'] = "Realiza las tareas programadas de Compilatio.net";
$string['trigger_timed_analyses'] = "Provoca los análisis programadas";

/* MAJ 06/2017 */

// Indexing state.
$string['indexing_state'] = "Añadir el documento a la biblioteca de referencias";
$string['indexing_state_help'] = "El contenido del documento es indexado a la biblioteca de referencias. Sirve como base de comparación para los próximos análisis.";
$string['indexed_document'] = "Documento indexado a la biblioteca de referencias";
$string['not_indexed_document'] = "Documento suprimido de la biblioteca de referencias";

// Information settings.
$string['information_settings'] = "Información";

// Max file size allowed.
$string['max_file_size_allowed'] = 'Tamaño máximo de los documentos : <strong>{$a->Mo} Mo</strong>';

// Failed documents.
$string['restart_failed_analysis'] = 'Lanzar de nuevo los análisis que fracasaron';
$string['restart_failed_analysis_title'] = 'Lanzar de nuevo los análisis que fracasaron :';

// Max attempt reached.
$string['max_attempts_reach_files'] = 'Los ficheros siguientes no pudieron ser analizados por Compilatio. El límite de lanzamiento de análisis ha sido alcanzado :';