<?php
define("IN_MYBB", 1);
require("global.php");
error_reporting(-1);
ini_set('display_errors', 1);

global $db, $mybb, $lang;

echo (
  '<style type="text/css">
body {
  background-color: #efefef;
  text-align: center;
  margin: 40px 100px;
  font-family: Verdana;
}
fieldset {
  width: 50%;
  margin: auto;
  margin-bottom: 20px;
}
legend {
  font-weight: bold;
}
</style>'
);
$gid = $db->fetch_field($db->write_query("SELECT gid FROM `" . TABLE_PREFIX . "settings` WHERE name like 'application_ucp%' LIMIT 1;"), "gid");

if ($mybb->usergroup['canmodcp'] == 1) {

  $setting_array = array(
    'application_ucp_applicants' => array(
      'title' => 'Bewerbergruppe',
      'description' => 'Wähle deine Gruppe für Bewerber aus.',
      'optionscode' => 'groupselectsingle',
      'value' => '2', // Default
      'disporder' => 1
    ),
    'application_ucp_approved' => array(
      'title' => 'Gruppen der angenommenen User',
      'description' => 'Bitte wähle alle Benutzergruppen für angenommene Charaktere aus.',
      'optionscode' => 'groupselect',
      'value' => '3', // Default
      'disporder' => 1
    ),
    'application_ucp_applicationtime' => array(
      'title' => 'Steckbrief Frist',
      'description' => 'Wieviele Tage nach der Registrierung hat der User Zeit seinen Steckbrief abzugeben?',
      'optionscode' => 'numeric',
      'value' => '14', // Default
      'disporder' => 2
    ),
    'application_ucp_correctiontime' => array(
      'title' => 'Korrektur Frist',
      'description' => 'Wieviele Tage hat der User Zeit Korrekturen vorzunehmen?',
      'optionscode' => 'numeric',
      'value' => '14', // Default
      'disporder' => 3
    ),
    'application_ucp_extend' => array(
      'title' => 'Verlängerung',
      'description' => 'Kann die Frist verlängert werden. Wenn ja um wieviele Tage? (sonst 0 eintragen)',
      'optionscode' => 'numeric',
      'value' => '0', // Default
      'disporder' => 4
    ),
    'application_ucp_extend_cnt' => array(
      'title' => 'Wie oft verlängern? ',
      'description' => 'Wie oft kann ein Mitglied die Frist um die angegebenen Tage verlängern? ',
      'optionscode' => 'numeric',
      'value' => '1', // Default
      'disporder' => 5
    ),
    'application_ucp_export' => array(
      'title' => 'Exportfunktion',
      'description' => 'Können Mitglieder ihre Steckbriefe als PDFS exportieren?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 6
    ),
    'application_ucp_steckiarea' => array(
      'title' => 'Area für die Steckbriefe',
      'description' => 'Wie ist die ID für eure Steckbriefarea?',
      'optionscode' => 'numeric',
      'value' => '2', // Default
      'disporder' => 7
    ),
    'application_ucp_stecki_message' => array(
      'title' => 'Steckbriefthread',
      'description' => 'Hier kannst du die Nachricht für den Stecki einfügen. HTML möglich. $wanted ist für die Angabe ob es sich um ein Gesuch handelt und welches, $affected für die mitbetroffenen Mitglieder. ',
      'optionscode' => 'textarea',
      'value' => '
      <div style="width:80%;">
      $wanted
      $affected
      </div>', // Default
      'disporder' => 8
    ),
    'application_ucp_stecki_wanted' => array(
      'title' => 'Gesuch',
      'description' => 'Soll abgefragt werden ob es sich um ein Gesuch handelt?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 9
    ),
    'application_ucp_stecki_affected' => array(
      'title' => 'Betroffene Mitglieder',
      'description' => 'Soll abgefragt werden, ob weitere Mitglieder betroffen sind und ihr Okay zu geben?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 10
    ),
    'application_ucp_stecki_affected_alert' => array(
      'title' => 'Benachrichtigung betroffener Mitglieder',
      'description' => '',
      'optionscode' => "select\n0=PM\n1=My Alert\n2=Mention Me mit Alert\n3=Gar Nicht",
      'value' => '1', // Default
      'disporder' => 10
    ),
    'application_ucp_stecki_mods' => array(
      'title' => 'Moderatoren Gruppen',
      'description' => 'Welche Gruppen sollen informiert werden, wen ein neuer Steckbrief erstellt wurde?',
      'optionscode' => 'groupselect',
      'value' => '4', // Default
      'disporder' => 11
    ),
    'application_ucp_profile_view' => array(
      'title' => 'Anzeige im Profil',
      'description' => 'Soll die Anzeige automatisch gebaut werden? Wenn ja, werden alle Felder mit einer Variable ($aucp_fields) ausgegeben. Wenn nein, müssen die Variablen für die Felder selbst eingefügt werden. (Bennenung findet ihr in der Übersicht).',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 12
    ),
    'application_ucp_postbit_view' => array(
      'title' => 'Anzeige im Postbit',
      'description' => 'Soll die Anzeige automatisch gebaut werden? Wenn ja, werden alle Felder mit einer Variable ($post[&#039;aucp_fields&#039;]) ausgegeben. Wenn nein, müssen die Variablen für die Felder selbst eingefügt werden. (Bennenung findet ihr in der Übersicht).',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 13
    ),
    'application_ucp_memberlist_view' => array(
      'title' => 'Anzeige in der Memberlist',
      'description' => 'Soll die Anzeige automatisch gebaut werden? Wenn ja, werden alle Felder mit einer Variable ($user[&#039;aucp_fields&#039;]) ausgegeben. Wenn nein, müssen die Variablen für die Felder selbst eingefügt werden. (Bennenung findet ihr in der Übersicht).',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 13
    ),
    'application_ucp_wobtext_yesno' => array(
      'title' => 'Antworttext WoB',
      'description' => 'Soll eine automatische Antwort bei einem wob erstellt werden?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 14
    ),
    'application_ucp_wobtext' => array(
      'title' => 'Antworttext WoB Inhalt',
      'description' => 'Gib hier den Antworttext ein, der bei einem WoB gepostet werden soll.',
      'optionscode' => 'textarea',
      'value' => '', // Default
      'disporder' => 15
    ),
    'application_ucp_search' => array(
      'title' => 'Durchsuchbarkeit',
      'description' => '<b style="color: red;">Wichtig:</b> Änderungen für die memberlist.php in der Readme beachten. Sonst auf nein stehen lassen!',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 16
    ),
  );
  $setcheck = 0;

  foreach ($setting_array as $name => $setting) {
    $setting['name'] = $name;
    $check = $db->write_query("SELECT name FROM `" . TABLE_PREFIX . "settings` WHERE name = '{$name}'");
    $setting['gid'] = $gid;
    $check = $db->num_rows($check);
    if ($check == 0) {
      echo "Die Einstellung {$name} fehlt. Du musst ein Update durchführen <br>";
      $setcheck = 1;
    }
  }

  echo "<h1>Update Script für Steckbrief Plugin</h1>";
  echo "<p>Updatescript wurde zuletzt am 6.12.23 aktualisiert</p>";
  echo "<p>Das Skript muss nur ausgeführt werden, wenn von einer alten auf eine neue Version geupdatet wird.<br> Bei Neuinstallation, muss hier nichts getan werden!</p>";
  if ($setcheck == 0) {
    echo "<p><b>Alle einstellungen sind vorhanden</b></p>";
  }
  echo '<form action="" method="post">';
  echo '<input type="submit" name="update" value="Update durchführen">';
  echo '</form>';

  if (isset($_POST['update'])) {

    $settingtrue = 0;
    foreach ($setting_array as $name => $setting) {
      $setting['name'] = $name;
      $check = $db->write_query("SELECT name FROM `" . TABLE_PREFIX . "settings` WHERE name = '{$name}'");
      $check = $db->num_rows($check);
      $setting['gid'] = $gid;
      if ($check == 0) {
        $db->insert_query('settings', $setting);
        echo "Setting: {$name} wurde hinzugefügt.";
        $settingtrue = 1;
      }
    }
    rebuild_settings();
    if ($settingtrue == 0) {
      echo "<p>Einstellungen waren aktuell - keine Felder hinzugefügt</p>";
    } else {
      echo "<p>Einstellungen wurden aktualisiert</p>";
    }
    echo "<p>Datenbankfelder durchgehen</p>";
    $dbcheck = 0;

    if (!$db->field_exists("application_ucp_fields", "guest")) {
      $db->add_column("guest", "application_ucp_fields", "int(1) NOT NULL DEFAULT 1");
      echo "Feld guest wurde zu application_ucp_fields hinzugefügt.";
      $dbcheck = 1;
    }
    if (!$db->field_exists("application_ucp_fields", "guest_content")) {
      $db->add_column("application_ucp_fields", "guest_content", "varchar(500) NOT NULL DEFAULT ''");
      echo "Feld guest_content wurde zu application_ucp_fields hinzugefügt.";
      $dbcheck = 1;
    }

    $db->modify_column("application_ucp_fields", "template", "CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");

    if (!$db->field_exists("aucp_extend", "users")) {
      $db->add_column("users", "aucp_extend", "INT(10) NOT NULL DEFAULT 0");
      echo "Feld aucp_extend wurde zu users hinzugefügt.";
      $dbcheck = 1;
    }
    if (!$db->field_exists("aucp_extenddate", "users")) {
      $db->add_column("users", "aucp_extenddate", "DATE NULL");
      echo "Feld aucp_extenddate wurde zu users hinzugefügt.";
      $dbcheck = 1;
    }

    if (!$db->field_exists("wob_date", "users")) {
      $db->add_column("users", "wob_date", "INT(10) NOT NULL DEFAULT 0");
      echo "Feld wob_date wurde zu users hinzugefügt.";
      $dbcheck = 1;
    }

    if (!$db->field_exists("scenetracker_calendarsettings_big", "users")) {
      $db->add_column("users", "scenetracker_calendarsettings_big", "INT(1) NOT NULL DEFAULT '0'");
      echo "Feld scenetracker_calendar_settings wurde zu threads hinzugefügt.";
      $dbcheck = 1;
    }
    if (!$db->field_exists("scenetracker_calendarsettings_mini", "users")) {
      $db->add_column("users", "scenetracker_calendarsettings_mini", "INT(1) NOT NULL DEFAULT '0'");
      echo "Feld scenetracker_calendar_settings wurde zu threads hinzugefügt.";
      $dbcheck = 1;
    }
    if ($dbcheck == 0) {
      echo "<p>Datenbank aktuell - keine Felder/Tabellen hinzugefügt.</p>";
    }

    // Einfügen der Trackeroptionen in die user tabelle
    if (!$db->field_exists("", "users")) {
      $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` ADD `tracker_index` INT(1) NOT NULL DEFAULT '1', ADD `tracker_indexall` INT(1) NOT NULL DEFAULT '1', ADD `tracker_reminder` INT(1) NOT NULL DEFAULT '1';");
    }

    // Neue Tabelle um Szenen zu speichern und informationen, wie die benachrichtigungen sein sollen.
    if (!$db->table_exists("scenetracker")) {
      $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "scenetracker` (
          `id` int(10) NOT NULL AUTO_INCREMENT,
          `uid` int(10) NOT NULL,
          `tid` int(10) NOT NULL,
          `alert` int(1) NOT NULL DEFAULT 0,
          `type` varchar(50) NOT NULL DEFAULT 'always',
          `inform_by` int(10) NOT NULL DEFAULT 1,
          `index_view` int(1) NOT NULL DEFAULT 1,
          `profil_view` int(1) NOT NULL DEFAULT 1,
          PRIMARY KEY (`id`)
      ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
    }
  }
  echo "<h1>CSS Nachträglich hinzufügen?</h1>";
  echo "<p>Nach einem MyBB Upgrade fehlen die Stylesheets? <br> Hier kannst du den Standard Stylesheet neu hinzufügen.</p>";
  echo '<form action="" method="post">';
  echo '<input type="submit" name="css" value="css hinzufügen">';
  echo '</form>';
  if (isset($_POST['css'])) {
    //Stylesheets checken
    $themesids = $db->write_query("SELECT tid FROM `" . TABLE_PREFIX . "themes`");
    echo "CSS zu Masterstyle hinzufügen";
    $check_tid = $db->simple_select("themestylesheets", "*", "tid = '1' AND name = 'application_ucp.css'");

    if ($db->num_rows($check_tid) == 0) {
      $css = array(
        'name' => 'application_ucp.css',
        'tid' => 1,
        'attachedto' => '',
        "stylesheet" =>    '
           
        /*showthread*/
        .aucp_showthread-wob {
            margin: 10px;
            display: flex;
            align-items: start;
            justify-content: center;
            gap: 20px;
        }
        
        .aucp_showthread-wob__item:last-child {
            align-self: center;
        }
        
        /*Benutzer CP */
        .applucp-con {
            display: grid;
            width: 80%;
            margin: auto;
            gap: 19px 15px;
        }
        
        .app_ucp_label {
            font-weight: 600;
            text-align: left;
        }
        
        .applucp-con__item {
            display: grid;
        }
        
        .applucp-con__item.applucp-buttons {
          display: grid;
          grid-template-columns: 1fr 1fr 1fr;
          gap: 10px;
      }
        
        /*Display Profil and Postbit */
        .aucp_fieldContainer {
            display: grid;
            grid-template-columns: 1fr;
        }
        
        .aucp_fieldContainer__item {
            display: flex;
            gap: 10px;
        }
        ',
        'cachefile' => $db->escape_string(str_replace('/', '', 'application_ucp.css')),
        'lastmodified' => time()
      );


      $sid = $db->insert_query("themestylesheets", $css);
      $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

    }
    // ("themestylesheets", "name = 'scenetracker.css'");
    require_once "admin/inc/functions_themes.php";
    update_theme_stylesheet_list($theme['tid']);
  }

  echo '<div style="width:100%; background-color: rgb(121 123 123 / 50%); display: flex; position:absolute; bottom:0;right:0; height:50px; justify-content: center; align-items:center; gap:20px;">
<div> <a href="https://github.com/katjalennartz/scenetracker" target="_blank">Github Rep</a></div>
<div> <b>Kontakt:</b> risuena (Discord)</div>
<div> <b>Support:</b>  <a href="https://storming-gates.de/showthread.php?tid=1030089">SG Thread</a> oder via Discord</div>
</div>';
} else {
  echo "<h1>Kein Zugriff</h1>";
}
