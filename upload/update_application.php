<?php
define("IN_MYBB", 1);
require("global.php");
// error_reporting(-1);
// ini_set('display_errors', 1);

global $db, $mybb, $lang;
echo '<html lang="de">
<head>
<meta http-equiv="Content-Type" 
      content="text/html; charset=utf-8">';
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
</style>
</head>
<body>'
);
$gid = $db->fetch_field($db->write_query("SELECT gid FROM `" . TABLE_PREFIX . "settings` WHERE name like 'application_ucp%' LIMIT 1;"), "gid");

if ($mybb->usergroup['canmodcp'] == 1) {
  echo "<h1>Update Script für Steckbrief Plugin</h1>";
  echo "<p>Updatescript wurde zuletzt am 11.09.24 aktualisiert</p>";
  echo "<p>Das Skript muss nur ausgeführt werden, wenn von einer alten auf eine neue Version geupdatet wird.<br> Bei Neuinstallation, muss hier nichts getan werden!</p>";

  echo '<form action="" method="post">';
  echo '<input type="submit" name="update" value="Update durchführen">';
  echo '</form>';

  if (isset($_POST['update'])) {
    echo "<h2>Neue Settings hinzufügen</h2>";
    application_ucp_add_settings('update');
    rebuild_settings();
    echo "<h2>Neue Templates hinzufügen</h2>";
    application_ucp_add_templates("update");
    echo "<p>Datenbankfelder durchgehen</p>";
    $dbcheck = 0;
    if (!$db->field_exists("guest", "application_ucp_fields")) {
      $db->add_column("guest", "application_ucp_fields", "int(1) NOT NULL DEFAULT 1");
      echo "Feld guest wurde zu application_ucp_fields hinzugefügt.";
      $dbcheck = 1;
    }
    if (!$db->field_exists("guest_content", "application_ucp_fields")) {
      $db->add_column("application_ucp_fields", "guest_content", "varchar(500) NOT NULL DEFAULT ''");
      echo "Feld guest_content wurde zu application_ucp_fields hinzugefügt.";
      $dbcheck = 1;
    }

    //Table hinzufügen 

    $db->write_query("ALTER TABLE `" . TABLE_PREFIX . "application_ucp_fields` CHANGE `template` `template` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';");

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
    if (!$db->table_exists("application_ucp_categories")) {
      $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "application_ucp_categories` (
      `id` int(10) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL DEFAULT '',
      `cat_order` int(10) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
      echo "Tabelle application_ucp_categories wurde erstellt.";
      $dbcheck = 1;
    }
    if (!$db->field_exists("wob_date", "users")) {
      $db->add_column("users", "wob_date", "INT(10) NOT NULL DEFAULT 0");
      echo "Feld wob_date wurde zu users hinzugefügt.";
      $dbcheck = 1;
    }
    if (!$db->field_exists("container", "application_ucp_fields")) {
      $db->add_column("application_ucp_fields", "container", "varchar(500) NOT NULL DEFAULT ''");
      echo "Feld container wurde zu application_ucp_fields hinzugefügt.";
      $dbcheck = 1;
    }
    if (!$db->field_exists("cat_id", "application_ucp_fields")) {
      $db->add_column("application_ucp_fields", "cat_id", "INT(10) NOT NULL DEFAULT '0'");
      echo "Feld cat_id wurde zu application_ucp_fields hinzugefügt.";
      $dbcheck = 1;

      include MYBB_ROOT . "/inc/adminfunctions_templates.php";
      find_replace_templatesets("application_ucp_ucp_main", "#" . preg_quote('{$footer}') . "#i", '{$footer}{$application_ucpcats_js}{$application_ucp_js}');
      echo "Template application_ucp_ucp_main - wurde aktualisiert. Variablen für Javascript wurden hinzugefügt  ";

      echo "Achtung zum CSS folgendes hinzufügen: <textarea style='width: 80%; height: 300px;'>.cat_tabs {
    margin: 0px;
    padding: 0px;
    list-style: none;
    background:#000;
    border-bottom: 5px #0072BC solid;
}
.cat_tabs li{
    display: inline-block;
    margin:0;
    padding: 10px 20px 5px 20px;
    cursor: pointer;
    color:#FFF;
}
.cat_tabs li:hover {
    background:#0072BC;
}

.cat_tabs li.current{
    background: #0072BC;
    color: #FFF;
}
.con_cat_content {
    display: none;
    background: #f2f2f2;
}
.con_cat_content.current{
    display: inherit;
}</teaxtarea> ";
    }
  }

  echo "<h1>CSS Nachträglich hinzufügen?</h1>";
  echo "<p>Nach einem MyBB Upgrade fehlen die Stylesheets? <br> Hier kannst du den Standard Stylesheet nachträglich zum Master style neu hinzufügen.</p>";
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
      
      /*Styling for tabs */
      .cat_tabs {
          margin: 0px;
          padding: 0px;
          list-style: none;
          background:#000;
          border-bottom: 5px #0072BC solid;
      }
      .cat_tabs li{
          display: inline-block;
          margin:0;
          padding: 10px 20px 5px 20px;
          cursor: pointer;
          color:#FFF;
      }
      .cat_tabs li:hover {
          background:#0072BC;
      }

      .cat_tabs li.current{
          background: #0072BC;
          color: #FFF;
      }
      .con_cat_content {
          display: none;
          background: #f2f2f2;
      }
      .con_cat_content.current{
          display: inherit;
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
<div> <a href="https://github.com/katjalennartz/application_ucp/" target="_blank">Github Rep</a></div>
<div> <b>Kontakt:</b> risuena (Discord)</div>
<div> <b>Support:</b>  <a href="https://storming-gates.de/showthread.php?tid=1030089">SG Thread</a> oder via Discord</div>
</div>
</body>
</html>';
} else {
  echo "<h1>Kein Zugriff</h1>";
}
