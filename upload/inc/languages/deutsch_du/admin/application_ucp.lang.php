<?php
//Infos
$l['application_ucp_info_name'] = 'Bewerbung im UCP von Risuena';
$l['application_ucp_info_descr'] = 'Möglichkeit im UCP einen eigenen Ausfüllbereich für Steckbriefe/Charakterinfos zu haben';


$l['application_ucp_permission'] = 'Darf Setckbrieffelder erstellen/verwalten?';
$l['application_ucp_name'] = 'Steckbrief im UCP';
$l['application_ucp_menu'] = 'Steckbrief Einstellungen';

$l['application_ucp_overview'] = 'Übersicht';
$l['application_ucp_overview_appl'] = 'Eine Übersicht aller Steckbrieffelder';
$l['application_ucp_overview_sort'] = 'Sortierung';
$l['application_ucp_overview_opt'] = 'Verwaltung';

$l['application_ucp_overview_cat'] = 'Kategorie';


$l['application_ucp_delete'] = 'löschen';
$l['application_ucp_deactivate'] = 'deaktiviert';
$l['application_ucp_activate'] = 'aktiviert';

$l['application_ucp_delete_ask'] = "Soll das Feld wirklich gelöscht werden? Achtung, auch die Inhalte der User werden gelöscht! Sonst das Feld einfach deaktivieren.";
$l['application_ucp_deactivate_ask'] = "Soll das Feld wirklich deaktiviert werden? Die Inhalte werden nicht gelöscht und die Daten werden behalten.";
$l['application_ucp_activate_ask'] = "Soll das Feld wirklich aktiviert werden? Die vorher ausgeblendeten Inhalte werden wieder angezeigt.";

$l['application_ucp_manageusers'] = 'Steckbriefe verwalten';
$l['application_ucp_manageusers_dscr'] = 'Steckbriefe der User verwalten und bearbeiten';
$l['application_ucp_manageusers_all'] = 'Übersicht aller User und die Möglichkeit ihre Steckbriefe zu verwalten.';

$l['application_ucp_manageusers_manage'] = 'Verwalten';
$l['application_ucp_manageusers_application'] = 'Steckbrief verwalten';
$l['application_ucp_manageusers_profile'] = 'Profil ansehen';

$l['application_ucp_edituser'] = 'Steckbrief bearbeiten.';

$l['application_ucp_cat_delete'] = 'Kategorie löschen';
$l['application_ucp_cat_edit'] = 'Kategorie ändern';

$l['application_ucp_managecats'] = 'Kategorien verwalten';
$l['application_ucp_managecats_dscr'] = 'Kategorien für Aufteilen von Steckbrieffeldern verwalten';

$l['application_ucp_managecats'] = 'Kategorien verwalten';
$l['application_ucp_namecat'] = 'Eine Kategorie erstellen';
$l['application_ucp_managecats_dscr'] = 'Kategorien für Aufteilen von Steckbrieffeldern verwalten';
$l['application_ucp_add_cat_name'] = 'Kategoriename';
$l['application_ucp_add_cat_name_descr'] = 'Gebe hier einen Namen für die Kategorie ein.';
$l['application_ucp_managecats_delete_ask'] ='Soll die Kategorie wirklich gelöscht werden?';
$l['application_ucp_add_cat_order'] = 'Reihenfolge';
$l['application_ucp_add_cat_order_descr'] = 'An welcher Stelle soll die Kategorie sein';

$l['application_ucp_err_namecat'] = 'Du hast keinen Namen für die Kategorie angegeben.';
$l['application_ucp_err_namecat_exists'] = 'Du hast schon eine Kategorie mit diesem Namen. Wähle einen Anderen.';



$l['application_ucp_createfieldtype'] = 'Steckbrieffeld erstellen';
$l['application_ucp_createfieldtype_dscr'] = 'Hier kannst du ein Steckbrieffeld anlegen und alle Einstellungen vornehmen.';
$l['application_ucp_editfieldtype'] = 'Steckbrieffeld editieren';
$l['application_ucp_editfieldtype_dscr'] = 'Hier kannst du ein Steckbrieffeld editiere und alle Einstellungen ändern.';

$l['application_ucp_formname'] = 'Steckbrieffeld erstellen';
$l['application_ucp_formname_edit'] = 'Steckbrieffeld editieren';

$l['application_ucp_add_name'] = 'Name/Identifikator (eindeutig!) des Felds';
$l['application_ucp_add_name_descr'] = 'Die Bezeichnung für das Feld, keine Sonderzeichen, keine Leertasten. Dient als Identifikator.';

$l['application_ucp_add_descr'] = 'Beschreibung des Felds';
$l['application_ucp_add_descr_descr'] = 'Die Beschreibung für das Feld.';


$l['application_ucp_add_fieldlabel'] = 'Label des Felds';
$l['application_ucp_add_fieldlabel_descr'] = 'Was soll vor dem Feld stehen? Also z.B. Vorname';

$l['application_ucp_add_fieldtyp'] = 'Typ des Felds';
$l['application_ucp_add_fieldtyp_descr'] = 'Welchen Typ soll das Feld haben?';

$l['application_ucp_add_fieldoptions'] = 'Auswahloptionen des Felds';
$l['application_ucp_add_fieldoptions_descr'] = 'Die bei Select, Select-Multiple, Checkbox und Radiobuttons möglichen Antwortoptionen. Sonst leer lassen.<br/>Mit \',\' getrennt. Zum Beispiel: Ravenclaw, Hufflepuff, Gryffindor, Slytherin';

$l['application_ucp_add_fieldeditable'] = 'Editierbar?';
$l['application_ucp_add_fieldeditable_descr'] = 'Ist das Feld auch nach der Annahme des Steckbriefs editierbar?';

$l['application_ucp_add_fieldmandatory'] = 'Pflichtfeld?';
$l['application_ucp_add_fieldmandatory_descr'] = 'Ist das Feld ein Pflichtfeld?';

$l['application_ucp_add_fielddependency'] = 'Abhängigkeit?';
$l['application_ucp_add_fielddependency_descr'] = 'Ist das Feld nur sichtbar, wenn es von dem Wert eines anderen abhängig ist? Wenn ja, dieses hier auswählen. <b>Hinweis:</b> Funktioniert nicht richtig mit Select beim übergeordneten Feld. Alternativ Checkbox oder Radio nehmen.';

$l['application_ucp_add_fielddependencyval'] = 'Abhängigkeitswert?';
$l['application_ucp_add_fielddependencyval_descr'] = 'Von welcher auswählbaren Option soll das Feld abhängigkeit? Z.B "Hogwarts".<br/> Bei mehreren mit Komma getrennt, aber <b>ohne</b> Leertaste angeben: Also "Ravenclaw,Hufflepuff".<br/> Leer lassen wenn keine Abhängigkeit.<br/>
 <b>Achtung:</b> genauso geschrieben, wie in den Auswahloptionen des Felds, welches bei Abhängigkeit gewählt ist!';

$l['application_ucp_add_fieldpostbit'] = 'Anzeige Postbit?';
$l['application_ucp_add_fieldpostbit_descr'] = 'Soll das Feld im Postbit anzeigbar sein?';

$l['application_ucp_add_fieldprofile'] = 'Anzeige Profil?';
$l['application_ucp_add_fieldprofile_descr'] = 'Soll das Feld im Profil anzeigbar sein?';

$l['application_ucp_add_fieldmember'] = 'Anzeige Memberlist?';
$l['application_ucp_add_fieldmember_descr'] = 'Soll das Feld in der Memberlist anzeigbar sein?';

$l['application_ucp_add_fieldtemplate'] = 'Vorlage/Template?';
$l['application_ucp_add_fieldtemplate_descr'] = 'Hier könnt ihr ein Template für das Feld erstellen. Z.B. den Code für eine Timeline.';

$l['application_ucp_add_fieldhtml'] = 'HTML erlaubt?';
$l['application_ucp_add_fieldhtml_descr'] = 'Soll HTML in dem Feld benutzt werden dürfen?';

$l['application_ucp_add_fieldmybb'] = 'MyBB-Code erlaubt?';
$l['application_ucp_add_fieldmybb_descr'] = 'Soll MyBB-Code in dem Feld benutzt werden dürfen?';

$l['application_ucp_add_fieldimg'] = 'IMG-Tag erlaubt?';
$l['application_ucp_add_fieldimg_descr'] = 'Dürfen Bilder via IMG Tag in dem Feld eingebunden werden?';

$l['application_ucp_add_searchable'] = 'Durchsuchbar?';
$l['application_ucp_add_searchable_descr'] = 'Soll in der Mitgliederliste nach dem Feld gesucht werden können?';

$l['application_ucp_add_suggestion'] = 'Suchvorschläge?';
$l['application_ucp_add_suggestion_descr'] = 'Sollen Vorschläge für die Suche anhand von schon eingetragener Werte gemacht werden?';

$l['application_ucp_add_guest'] = 'Gäste?';
$l['application_ucp_add_guest_descr'] = 'Dürfen Gäste das Feld sehen?';

$l['application_ucp_add_guest_content'] = 'alternativer Inhalt für Gäste';
$l['application_ucp_add_guest_content_descr'] = 'Was sollen Gäste anstatt des eigentlichen sehen? Leer lassen wenn es nur ausgeblendet werden soll.
Für den themeordner $themepath angeben also z.B. $themepath/default.png';

$l['application_ucp_add_container'] = 'Html Element um Value und Label?';
$l['application_ucp_add_container_descr'] = 'Soll um die Variable, die den reinen Textwert von Label oder Value ausgibt noch ein html Element gelegt werden? Dieses enthält zusätzlich die klasse "is_empty", welche sich in css dann mit display:none; ausblenden lässt.';

$l['application_ucp_add_active'] = 'Aktiv?';
$l['application_ucp_add_active_descr'] = 'Bei Nein: Die Daten der User werden nicht gelöscht, das Feld wird nur ausgeblendet, ist nicht mehr bearbeitbar und wird nirgends mehr angezeigt.';

$l['application_ucp_add_fieldvideo'] = 'Videos erlaubt?';
$l['application_ucp_add_fieldvideo_descr'] = 'Dürfen Videos in dem Feld eingebunden werden?';

$l['application_ucp_add_fieldsort'] = 'Darstellungsreihenfolge?';
$l['application_ucp_add_fieldsort_descr'] = 'An welcher Position soll das Feld dargestellt werden?';

$l['application_ucp_save'] = 'Speichern';

// Errors
$l['application_ucp_err_name'] = 'Bitte einen Namen angeben.';
$l['application_ucp_err_name_exists'] = 'Der Name existiert schon. Bitte eindeutigen Identifikator verwenden.';

$l['application_ucp_err_name_sonder'] = 'Der Name darf keine Sonderzeichen oder Leertasten enthalten.';
$l['application_ucp_err_label'] = 'Bitte ein Label angeben.';
$l['application_ucp_err_fieldtyp'] = 'Bitte einen Feldtypen auswählen.';
$l['application_ucp_err_fieldoptions'] = 'Du hast einen Feldtypen ausgewählt, der Optionen erfordert. Bitte ausfüllen.';
$l['application_ucp_err_dependency_value_empty'] = 'Du hast einen Abhängigkeit ausgewählt, aber nicht angegeben von welchem Wert dein erstelltes Abhängig sein soll.';
$l['application_ucp_err_dependency_value_wrong'] = 'Für dein ausewähltes Feld gibt es die angegeben Option nicht. Vertippt? Bitte auch Groß- und Kleinschreibung beachten.';

$l['application_ucp_err_delete'] = "Das Feld konnte nicht gelöscht werden.";

$l['application_ucp_success'] = 'Erfolgreich gespeichert.';