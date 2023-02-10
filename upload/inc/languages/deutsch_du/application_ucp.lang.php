<?php
/**
 * Steckbriefe im UCP  1.0 Main Language File
 * by risuena
 */

//Templates 
$l['application_ucp_temps_title'] = 'Steckbriefübersicht';
$l['application_ucp_temps_ready_applications'] = 'fertige Steckbriefe';
$l['application_ucp_temps_in_correction_user'] = 'Steckbriefe die vom User korrigiert werden müssen';

$l['application_ucp_temps_charakter'] = 'Charakter';
$l['application_ucp_temps_application'] = 'Steckbrief';
$l['application_ucp_temps_wip'] = 'In Bearbeitung?';
$l['application_ucp_temps_frist'] = 'Fristende?';
$l['application_ucp_temps_registeredsince'] = 'Registriert seit?';
$l['application_ucp_temps_lastactive'] = 'Letzte Aktivität?';
$l['application_ucp_temps_notready'] = 'noch nicht eingereichte Steckbriefe';
$l['application_ucp_temps_profilelink'] = 'Link zum Profil';
$l['application_ucp_temps_responsible'] = 'Verantwortlicher angefordert?';
$l['application_ucp_temps_correctdate'] = 'eingereicht/korrigiert am';

//UCP
$l['application_ucp_usernav'] = 'Steckbrief';
$l['application_ucp_fillapplication'] = 'Steckbrief ausfüllen';
$l['application_ucp_mandatory'] = '*';
$l['application_ucp_wanted'] = 'Ist der Charakter ein Gesuch?';
$l['application_ucp_wanted_url'] = 'Titel des Gesuch, oder Name';
$l['application_ucp_affected_ucp'] = 'Betroffene Mitglieder';
$l['application_ucp_extbtn'] = 'Frist Verlängern';
$l['application_ucp_save'] = 'Speichern';
$l['application_ucp_readybtn'] = 'Speichern & zur Kontrolle';
$l['application_ucp_saveerror'] = 'Du kannst deinen Steckbrief nicht einreichen, ehe nicht alle Pflichtfelder ausgefuellt sind. Wenn du neue betroffene Mitglieder eingetragen hattest, musst du diese vor dem Speichern neu eintragen.';
$l['application_ucp_export'] = 'Steckbrief exportieren';
$l['application_ucp_infoheader'] = 'Du bist seit dem {1} registriert. Du hast {2} verlängert und Zeit bis zum {3}.';
$l['application_ucp_correction'] = 'Dein Steckbrief wurde eingereicht.';

//INDEX
$l['application_ucp_index_extinfo'] = "Du hast {1} Mal verlängert.";
$l['application_ucp_index_extinfo_deadline'] = "Du hast noch bis zum {1} Zeit deinen Steckbrief zu vervollständigen.{2}";
$l['application_ucp_index_nomod'] = "Dein Steckbrief wurde noch von keinem Moderator übernommen.";
$l['application_ucp_index_token'] = "Dein Steckbrief wurde von {1} übernommen.";
$l['application_ucp_index_correction'] = "Dein Steckbrief wurde von {1} korrigiert.<br/> Du hast für die Korrektur Zeit bis zum {2}.";
$l['application_ucp_index_mod_steckialert'] =  "{1} ist mit dem Steckbrief fertig.<br /> <a href=\"misc.php?action=take_application&uid={2}\">Korrektur übernehmen</a>";
$l['application_ucp_index_mod_steckialert_modturn'] =  "Du hast den Stecki von {1} übernommen. Du musst ihn noch korrigieren.";
$l['application_ucp_index_mod_steckialert_userturn'] =  "Du hast den Stecki von {1} übernommen. Der User hat noch nicht korrigiert";
$l['application_ucp_index_mod_steckialert_userhascorrected'] =  "Du hast den Stecki von {1} übernommen. Der User korrigiert hat und du musst drüberschauen.";

//WOB - Thread
$l['application_ucp_thread_wantedurltitle'] = "Charakter ist ein Gesuch";
$l['application_ucp_wobgroups'] = 'Primäre Benutzergruppe *';
$l['application_ucp_wobgroups2'] = 'Sekundäre Benutzergruppe';
$l['application_ucp_nonewobgroups2'] = 'Keine sekundäre Gruppe';
$l['application_ucp_responsible'] ="<span class=\"aucp_respmod\">Korrektur übernommen von {1}</span>";
$l['application_ucp_noresponsible'] ="<span class=\"aucp_respmod\">Korrektur wurde noch nicht übernommen</span>";
$l['application_ucp_wobbtn'] = 'WOB';
$l['application_ucp_affected_label'] = 'Betroffene Charaktere:';


//My Alert
$l['myalerts_setting_application_ucp_affected'] = 'Steckbriefe: Benachrichtigung wenn dich ein Steckbrief betrifft.';
$l['application_ucp_affected'] = '{1} hat einen Steckbrief gepostet der dich betrifft. Bitte gib dein Okay.';
