<?php

/**
 * Steckbriefe im UCP  - by risuena
 * https://github.com/katjalennartz
 * 
 * Dieses Plugin gibt die Möglichkeit im UCP einen eigenen Ausfüllbereich für Steckbriefe/Charakterinfos zu haben.
 * Die Felder können frei im ACP erstellt werden.
 * Bitte die Readme beachten. Wirklich! Da steht alles wichtige drin ;) 
 * 
 * 
 * PDF EXPORT:
 * CREDITS to https://tcpdf.org/
 * and https://www.php-einfach.de/experte/php-codebeispiele/pdf-per-php-erstellen-pdf-rechnung/
 */

//Fehleranzeige bei Bedarf anschalten, in dem die folgenden 2 Zeilen einkommentiert werden
// error_reporting(-1);
// ini_set('display_errors', true);

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
  die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function application_ucp_info()
{
  global $lang;
  $lang->load('application_ucp');

  return array(
    "name" => $lang->application_ucp_name,
    "description" => $lang->application_ucp_info_descr,
    "website" => "https://github.com/katjalennartz/application_ucp",
    "author" => "risuena",
    "authorsite" => "https://github.com/katjalennartz",
    "version" => "1.1.4",
    "compatibility" => "18*"
  );
}

function application_ucp_is_installed()
{
  global $db;
  if ($db->table_exists("application_ucp_fields")) {
    return true;
  }
  return false;
}

function application_ucp_install()
{
  global $db;
  application_ucp_uninstall();

  $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "application_ucp_fields` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `fieldtyp` varchar(100) NOT NULL DEFAULT '',
    `fieldname` varchar(100) NOT NULL DEFAULT '',
    `fielddescr` varchar(500) NOT NULL DEFAULT '',
    `label` varchar(100) NOT NULL DEFAULT '',
    `options` varchar(500) NOT NULL DEFAULT '',
    `editable` int(1) NOT NULL DEFAULT 0,
    `mandatory` int(1) NOT NULL DEFAULT 1,
    `dependency` varchar(500) NOT NULL DEFAULT '',
    `dependency_value` varchar(500) NOT NULL DEFAULT '',
    `postbit` int(1) NOT NULL DEFAULT 0,
    `profile` int(1) NOT NULL DEFAULT 0,
    `memberlist` int(1) NOT NULL DEFAULT 0,
    `template` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `sorting` int(10) NOT NULL DEFAULT 0,
    `active` int(1) NOT NULL DEFAULT 1,
    `allow_html` int(1) NOT NULL DEFAULT 1,
    `allow_mybb` int(1) NOT NULL DEFAULT 1,
    `allow_img` int(1) NOT NULL DEFAULT 1,
    `allow_video` int(1) NOT NULL DEFAULT 1,
    `searchable` int(1) NOT NULL DEFAULT 0,
    `suggestion` int(1) NOT NULL DEFAULT 0,
    `guest` int(1) NOT NULL DEFAULT 1,
    `guest_content` varchar(500) NOT NULL DEFAULT '',
    `container` varchar(500) NOT NULL DEFAULT '',
    `cat_id` int(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

  $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "application_ucp_userfields` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `fieldid` int(10) NOT NULL,
  UNIQUE KEY `uid_fieldidid` (`uid`,`fieldid`),
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

  $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "application_ucp_management` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `tid` int(10) NOT NULL DEFAULT 0,
  `uid_mod` int(10) NOT NULL DEFAULT 0,
  `submission_time` datetime NOT NULL DEFAULT NOW(),
  `modcorrection_time` datetime NULL,
  `usercorrection_time` datetime NULL,
  `correctioncnt` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

  $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "application_ucp_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `cat_order` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

  //Verlängerung
  $db->add_column("users", "aucp_extend", "INT(10) NOT NULL DEFAULT 0");
  $db->add_column("users", "aucp_extenddate", "DATE NULL");
  $db->add_column("users", "wob_date", "INT(10) NOT NULL DEFAULT 0");

  // ALTER TABLE `mybb_users` ADD `aucp_extenddate` DATE NOT NULL AFTER `aucp_extend`;

  // Admin Einstellungen
  $setting_group = array(
    'name' => 'application_ucp',
    'title' => 'Steckbrief im UCP',
    'description' => 'Allgemeine Einstellungen für die Steckbriefe im UCP.',
    'disporder' => 7, // The order your setting group will display
    'isdefault' => 0
  );
  $gid = $db->insert_query("settinggroups", $setting_group);

  application_ucp_add_settings();

  application_ucp_add_templates();


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

  /*tabstyling/*
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

  require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

  $sid = $db->insert_query("themestylesheets", $css);
  $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

  $tids = $db->simple_select("themes", "tid");
  while ($theme = $db->fetch_array($tids)) {
    update_theme_stylesheet_list($theme['tid']);
  }
}

/**
 * Funktion um die Settings hinzuzufügen - einfachere Verwendung für Upgrades 
 */
function application_ucp_add_settings($type = 'install')
{

  global $db;

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
      'description' => 'Hier kannst du die Nachricht für den Stecki einfügen. HTML möglich. $wanted ist für die Angabe ob es sich um ein Gesuch handelt und welches, $affected für die mitbetroffenen Mitglieder, $avatar und $username möglich, sowie $aucp_fields für den kompletten Steckbrief im Thread.',
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
    'application_ucp_trigger' => array(
      'title' => 'Trigger',
      'description' => 'Soll eine Triggerwarnung für Profile angegeben werden können?',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 17
    ),
    'application_ucp_acp_pagination' => array(
      'title' => 'Seitenzahlen in der Userliste im ACP',
      'description' => 'Sollen die User im ACP auf Seiten aufgeteilt werden? 0 wenn nicht, sonst anzahl der Einträge pro Seite',
      'optionscode' => 'numeric',
      'value' => '0', // Default
      'disporder' => 18
    ),
    'application_ucp_acp_pagination_fields' => array(
      'title' => 'Seitenzahlen in der Feldliste im ACP',
      'description' => 'Sollen die Felder im ACP auf Seiten aufgeteilt werden? 0 wenn nicht, sonst anzahl der Einträge pro Seite',
      'optionscode' => 'numeric',
      'value' => '0', // Default
      'disporder' => 19
    ),
    'application_ucp_acp_cats' => array(
      'title' => 'Aufspaltung',
      'description' => 'Sollen die Steckbrieffelder in Kategorien aufgespaltet werden?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 20
    ),
    'application_ucp_acp_cats_tabs' => array(
      'title' => 'Aufspaltung - Tabs?',
      'description' => 'Sollen diese Kategorien in Tabs angezeigt werden?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 21
    ),
    'application_ucp_acp_cat_defaultname' => array(
      'title' => 'Defaultname Kategorie',
      'description' => 'Wenn ein Feld keine Kategorie zugeordnet hat, wie soll der Defaultname für diese sein?',
      'optionscode' => 'text',
      'value' => 'Andere', // Default
      'disporder' => 22
    ),

  );

  $gid = $db->fetch_field($db->write_query("SELECT gid FROM `" . TABLE_PREFIX . "settinggroups` WHERE name like 'application_ucp%' LIMIT 1;"), "gid");

  if ($type == 'install') {
    foreach ($setting_array as $name => $setting) {
      $setting['name'] = $name;
      $setting['gid'] = $gid;
      $db->insert_query('settings', $setting);
    }
  }
  if ($type == 'update') {
    foreach ($setting_array as $name => $setting) {
      $setting['name'] = $name;
      $check = $db->write_query("SELECT name FROM `" . TABLE_PREFIX . "settings` WHERE name = '{$name}'");
      $check = $db->num_rows($check);
      $setting['gid'] = $gid;
      if ($check == 0) {
        $db->insert_query('settings', $setting);
        echo "Setting: {$name} wurde hinzugefügt.";
      }
    }
    echo "<p>Einstellungen wurden aktualisiert</p>";
  }
  rebuild_settings();
}

/**
 * Funktion um die Templates hinzuzufügen - einfachere Verwendung für Upgrades 
 */
function application_ucp_add_templates($type = 'install')
{
  global $db;

  //Templates erstellen:
  if ($type == 'install') {
    // templategruppe
    $templategrouparray = array(
      'prefix' => 'application',
      'title'  => $db->escape_string('Steckbrief im UCP'),
      'isdefault' => 1
    );
    $db->insert_query("templategroups", $templategrouparray);
  }

  $template[0] = array(
    "title" => 'application_ucp_index',
    "template" => '<div class="red_alert">
      {$application_ucp_index_modbit}
      {$application_ucp_index_bit}
    </div>
      ',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[1] = array(
    "title" => 'application_ucp_index_bit',
    "template" => '<div class="aucp_indexuser">{$message}
      </div>
      ',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[2] = array(
    "title" => 'application_ucp_mods',
    "template" => '<html>
      <head>
      <title>{$mybb->settings[\\\'bbname\\\']} - {$lang->application_ucp_temps_title}</title>
      {$headerinclude}
      </head>
      <body>
      {$header}
      <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="aucp-modoverview tborder tfixed">
      <tr>
      <td class="trow1"><h2>{$lang->application_ucp_temps_ready_applications}</h2><br/>
          <table>
          <tr>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_charakter}</td>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_application}</td>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_wip}</td>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_correctdate}</td>
          </tr>
            {$application_ucp_mods_readybit}
          </table>
        </td>
      </tr>
        
          <tr>
      <td class="trow1"><h2>{$lang->application_ucp_temps_in_correction_user}</h2><br/>
        <table>
          <tr>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_charakter}</td>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_application}</td>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_responsible}</td>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_frist}</td>
          </tr>
            {$application_ucp_mods_users}
          </table>
        </td>
      </tr>
        <tr>
      <td class="trow1"><h2>{$lang->application_ucp_temps_notready}</h2><br/>
          <table>
          <tr>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_profilelink}</td>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_registeredsince}</td>
            <td class="aucp-tdhead"{$lang->application_ucp_temps_lastactive}></td>
            <td class="aucp-tdhead">{$lang->application_ucp_temps_frist}</td>
          </tr>
            {$application_ucp_mods_new}
          </table>
      </td>
      </tr>
      <tr>
      <td class="trow1 scaleimages"></td>
      </tr>
      </table>
      {$footer}
      </body>
      </html>
      ',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[3] = array(
    "title" => 'application_ucp_mods_bit',
    "template" => '<tr>
      <td>{$aucp_mod_profillink}</td>
      <td>{$aucp_mod_steckilink}</td>
      <td>{$aucp_mod_modlink}</td>
      <td>{$aucp_mod_date} {$correction}</td>
    </tr>',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[4] = array(
    "title" => 'application_ucp_wobbutton',
    "template" => '<form action="misc.php?action=wob&tid={$thread[\\\'tid\\\']}" method="post" style="display: inline !important;">
    <div class="aucp_showthread-wob">
      <div class="aucp_showthread-wob__item">
         <input type="hidden" name="uid" value="{$thread[\\\'uid\\\']}" />
          <input type="hidden" name="tid" value="{$thread[\\\'tid\\\']}" />
          <input type="hidden" name="fid" value="{$thread[\\\'fid\\\']}" />
          <label for="usergroups">{$lang->application_ucp_wobgroups}</label><br />
          <select name="usergroups" id="usergroups" required>
             {$usergroups_bit}
          </select>
      </div>
      <div class="aucp_showthread-wob__item">
      <label for="usergroups">{$lang->application_ucp_wobgroups2}</label><br />
          <select name="additionalgroups[]" id="additionalgroups[]" size="3" multiple="multiple">
              <option value="">{$lang->application_ucp_nonewobgroups2}</option>
              {$additionalgroups_bit}
          </select>
      </div>
        <div class="aucp_showthread-wob__item">
        <input type="submit" name="wob" value="{$lang->application_ucp_wobbtn}" class="button" />
      </div>
      </div>    
  </form>',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[5] = array(
    "title" => 'application_ucp_ucp_main',
    "template" => '<html>
      <head>
      <title>{$mybb->settings[\\\'bbname\\\']} - {$lang->application_ucp_fillapplication}</title>
      {$headerinclude}
      </head>
      <body>
      {$header}
      <form action="" method="post" id="application_ucp_form">
      <input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
      <table width="100%" border="0" align="center">
      <tr>
      {$usercpnav}
      <td valign="top">
        <div class="applucp-con">
        {$cats_html}
        {$application_ucp_infos}
        {$fields}
        <div align="center" class="applucp-con__item applucp-buttons">
         {$extend_button}
         <input type="submit" class="button" name="application_ucp_save" value="{$lang->application_ucp_save}" />
         {$savebtn}
         </div>
        </div>
      </td>
      </tr>
      </table>
      </form>
      {$application_ucpcats_js}{$application_ucp_js}
      {$footer}
        
      </body>
      </html>',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[6] = array(
    "title" => 'application_ucp_filtermemberlist',
    "template" => '
    <div class="bl-filtermemberlist">
		{$filter}
    </div>',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[7] = array(
    "title" => 'application_ucp_filtermemberlist_bit',
    "template" => '<div class="filterinput">
    <label for="{$searchfield[\\\'fieldname\\\']}">Nach {$searchfield[\\\'label\\\']}:</label> 
  <input type="{$typ}" class="filterfield {$searchfield[\\\'fieldname\\\']}" name="{$searchfield[\\\'fieldname\\\']}" id="{$searchfield[\\\'fieldname\\\']}">
  </div>',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[8] = array(
    "title" => 'application_ucp_filtermemberlist_selectbit',
    "template" => '<div class="filterinput"><label for="{$searchfield[\\\'fieldname\\\']}">Nach {$searchfield[\\\'label\\\']}:</label>
    <select name="{$searchfield[\\\'fieldname\\\']}[]" id="{$searchfield[\\\'fieldname\\\']}" >
      {$selects} 
    </select>
</div>
{$filterjs}',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[9] = array(
    "title" => 'application_ucp_infos',
    "template" => '<div class="aucp_infoheader">
    {$lang->application_ucp_infoheader}
      {$application_ucp_correction_status}
    </div>',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[10] = array(
    "title" => 'application_ucp_profile_trigger',
    "template" => '<div class="aucp_trigger">
    {$lang->application_ucp_profile_trigger} {$trigger}
    </div>',
    "sid" => "-2",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );


  foreach ($template as $row) {
    $check = $db->num_rows($db->simple_select("templates", "title", "title LIKE '{$row['title']}'"));
    if ($check == 0) {
      $db->insert_query("templates", $row);
      echo "Neues Template {$row['title']} wurde hinzugefügt.<br>";
      if ($row['title' == 'application_ucp_profile_trigger']) {
        echo '<b>Achtung</b>: Variable {$application_ucp_profile_trigger} in member_profile an die Stelle einfügen, wo die Trigger Warnung angezeigt werden soll.</br>';
      }
    }
  }
}


function application_ucp_uninstall()
{
  global $db, $mybb;
  if ($db->table_exists("application_ucp_fields")) {
    $db->drop_table("application_ucp_fields");
  }
  if ($db->table_exists("application_ucp_userfields")) {
    $db->drop_table("application_ucp_userfields");
  }

  if ($db->table_exists("application_ucp_management")) {
    $db->drop_table("application_ucp_management");
  }

  // if ($db->table_exists("application_ucp_useralerts")) {
  //   $db->drop_table("application_ucp_useralerts");
  // }  

  if ($db->table_exists("application_ucp_userfields")) {
    $db->drop_table("application_ucp_userfields");
  }

  if ($db->field_exists("aucp_extend", "users")) {
    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "users DROP aucp_extend");
  }
  if ($db->field_exists("aucp_extenddate", "users")) {
    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "users DROP aucp_extenddate");
  }
  if ($db->field_exists("wob_date", "users")) {
    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "users DROP wob_date");
  }

  // Einstellungen entfernen
  $db->delete_query("settings", "name LIKE 'application_ucp%'");
  $db->delete_query('settinggroups', "name = 'application_ucp'");

  // Templates löschen
  $db->delete_query("templates", "title LIKE 'application_ucp%'");
  $db->delete_query("templategroups", "prefix = 'application'");

  // CSS löschen
  require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
  $db->delete_query("themestylesheets", "name = 'application_ucp.css'");
  $query = $db->simple_select("themes", "tid");
  while ($theme = $db->fetch_array($query)) {
    update_theme_stylesheet_list($theme['tid']);
  }

  rebuild_settings();
}


function application_ucp_activate()
{
  global $db, $mybb, $cache, $lang;
  include MYBB_ROOT . "/inc/adminfunctions_templates.php";
  //usercp link
  find_replace_templatesets("usercp_nav_misc", "#" . preg_quote('<tbody style="{$collapsed[\'usercpmisc_e\']}" id="usercpmisc_e">') . "#i", '
  <tbody style="{$collapsed[\'usercpmisc_e\']}" id="usercpmisc_e"><tr><td class="trow1 smalltext"><a href="usercp.php?action=application_ucp">Steckbrief</a></td></tr>
  ');
  //Member Profil - Felder
  find_replace_templatesets("member_profile", "#" . preg_quote('{$avatar}</td></tr>') . "#i", '
  {$avatar}</td></tr>{$aucp_fields}
  ');
  //Button export Profil
  find_replace_templatesets("member_profile", "#" . preg_quote('<strong>{$formattedname}</strong>') . "#i", '
  <strong>{$formattedname}</strong> {$exportbtn}
  ');

  //showthread wob form
  find_replace_templatesets("showthread", "#" . preg_quote('{$posts}
	</div>') . "#i", '
  {$posts}
	</div>
  {$give_wob}
  ');

  find_replace_templatesets("member_profile", "#" . preg_quote('<td width="75%">') . "#i", '<td width="75%"> {$application_ucp_profile_trigger}');


  find_replace_templatesets("showthread", "#" . preg_quote('{$thread[\'subject\']}') . "#i", '{$thread[\'subject\']} {$aucp_responsible_mod}');
  //postbit classic 
  find_replace_templatesets("postbit_classic", "#" . preg_quote('{$post[\'user_details\']}') . "#i", '{$post[\'user_details\']}{$post[\'aucp_fields\']}');
  //postbit
  find_replace_templatesets("postbit", "#" . preg_quote('{$post[\'user_details\']}') . "#i", '{$post[\'user_details\']}{$post[\'aucp_fields\']}');

  //memberlist
  find_replace_templatesets("memberlist", "#" . preg_quote('{$referrals_option}</select></td></tr>') . "#i", '{$referrals_option}</select></td></tr><tr><td colspan="3">{$applicationfilter}</tr></td>');
  find_replace_templatesets("memberlist", "#" . preg_quote('</body>') . "#i", '{$filterjs}</body>');

  //Meldung auf dem index
  find_replace_templatesets("index", "#" . preg_quote('{$header}') . "#i", '{$header}{$application_ucp_index}');

  //Javascript fürs Aufspalten 
  find_replace_templatesets("application_ucp_ucp_main", "#" . preg_quote('{$footer}') . "#i", '{$footer}{$application_ucpcats_js}{$application_ucp_js}');

  if (function_exists('myalerts_is_activated') && myalerts_is_activated()) {

    $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

    if (!$alertTypeManager) {
      $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
    }
    $alertTypeAucpAffected = new MybbStuff_MyAlerts_Entity_AlertType();
    $alertTypeAucpAffected->setCanBeUserDisabled(true);
    $alertTypeAucpAffected->setCode("application_ucp_affected");
    $alertTypeAucpAffected->setEnabled(true);
    $alertTypeManager->add($alertTypeAucpAffected);
  }

  change_admin_permission("config", "application_ucp", 1);
}

function application_ucp_deactivate()
{
  global $mybb;
  //Variablen entfernen
  include MYBB_ROOT . "/inc/adminfunctions_templates.php";
  find_replace_templatesets("usercp_nav_misc", "#" . preg_quote('<tr><td class="trow1 smalltext"><a href="usercp.php?action=application_ucp">Steckbrief</a></td></tr>') . "#i", '');
  find_replace_templatesets("member_profile", "#" . preg_quote('{$aucp_fields}') . "#i", '');
  find_replace_templatesets("member_profile", "#" . preg_quote('{$exportbtn}') . "#i", '');
  find_replace_templatesets("showthread", "#" . preg_quote('{$give_wob}') . "#i", '');
  find_replace_templatesets("showthread", "#" . preg_quote('{$aucp_responsible_mod}') . "#i", '');
  find_replace_templatesets("postbit", "#" . preg_quote('{$post[\'aucp_fields\']}') . "#i", '');
  find_replace_templatesets("memberlist", "#" . preg_quote('<tr><td colspan="3">{$applicationfilter}</tr></td>') . "#i", '');
  find_replace_templatesets("memberlist", "#" . preg_quote('{$filterjs}') . "#i", '');
  find_replace_templatesets("index", "#" . preg_quote('{$application_ucp_index}') . "#i", '');
  find_replace_templatesets("member_profile", "#" . preg_quote('{$application_ucp_profile_trigger}') . "#i", '');
  find_replace_templatesets("application_ucp_ucp_main", "#" . preg_quote('{$application_ucpcats_js}{$application_ucp_js}') . "#i", '');
  //My alerts wieder löschen
  if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

    if (!$alertTypeManager) {
      $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
    }
    $alertTypeManager->deleteByCode('application_ucp_affected');
  }
  change_admin_permission("config", "application_ucp", -1);
}

//Hinzufügen  upgrade Hinweis
$plugins->add_hook("admin_settings_print_peekers", "application_ucp_settings_peek");
function application_ucp_settings_peek(&$peekers)
{
  global $db, $mybb;
  if (
    empty($mybb->settings['application_ucp_approved'])
    || !$db->field_exists("guest", "application_ucp_fields")
    || !$db->field_exists("guest_content", "application_ucp_fields")
    || !$db->field_exists("wob_date", "users")
    || !$db->table_exists("application_ucp_categories")
  ) {
    $peekers[] = "
    $('a:contains(\"Steckbrief im UCP\")').after('<br><span style=\"color:red; font-weight:bold;\">Bitte das Updatescript ausführen!</span>');
    ";
  }
}

/**
 * action handler fürs acp konfigurieren
 */
$plugins->add_hook("admin_config_action_handler", "application_ucp_admin_config_action_handler");
function application_ucp_admin_config_action_handler(&$actions)
{
  $actions['application_ucp'] = array('active' => 'application_ucp', 'file' => 'application_ucp');
}

/**
 * Berechtigungen im ACP
 */
$plugins->add_hook("admin_config_permissions", "application_ucp_admin_config_permissions");
function application_ucp_admin_config_permissions(&$admin_permissions)
{
  global $lang;
  $lang->load('application_ucp');

  $admin_permissions['application_ucp'] = $lang->application_ucp_permission;
  return $admin_permissions;
}

/**
 * Admin Menü einfügen
 */
$plugins->add_hook("admin_config_menu", "application_ucp_admin_config_menu");
function application_ucp_admin_config_menu($sub_menu)
{
  global $mybb, $lang;
  $lang->load('application_ucp');

  $sub_menu[] = [
    "id" => "application_ucp",
    "title" => $lang->application_ucp_menu,
    "link" => "index.php?module=config-application_ucp"
  ];
  return $sub_menu;
}

/**
 * Verwaltung der Steckriefe im ACP
 * (Felder Anlegen/Löschen/etc)
 */
$plugins->add_hook("admin_load", "application_ucp_admin_load");
function application_ucp_admin_load()
{
  global $mybb, $db, $lang, $page, $run_module, $action_file;
  //Sprachvariable laden
  $lang->load('application_ucp');

  if ($page->active_action != 'application_ucp') {
    return false;
  }

  // Übersicht 
  if ($run_module == 'config' && $action_file == 'application_ucp') {

    //Startpage acp  // Übersicht angelegter Felder
    $action = $mybb->get_input('action');
    if ($action == "" || !isset($action)) {
      $page->add_breadcrumb_item($lang->application_ucp_name);
      $page->output_header($lang->application_ucp_name);

      // submenü erstellen - dafür wurde eine Funktion gebastelt.
      $sub_tabs = application_ucp_do_submenu();
      $page->output_nav_tabs($sub_tabs, 'application_ucp');
      // fehleranzeige
      if (isset($errors)) {
        $page->output_inline_error($errors);
      }

      // if (!$db->field_exists("guest", "application_ucp_fields")) {

      //   $form_container = new FormContainer("Upgrade!");

      //   $form_container->output_row('<font style="color: red;">
      //   <b>UPGRADE</b> Du musst noch das Upgradescript ausführen 
      //   um neue Felder (Gast, und alternativer Inhalt für Gäste) hinzuzufügen!<bR>
      //   Es gibt auch neue Sprachvariablen, languages also auch hochladen ;)<br>
      //    <a href="' . $mybb->settings['bburl'] . '/update_fields.php" target="_blank">Jetzt ausführen</a></font><br/>
      //   ', "", "", "", array("colspan" => "5"));
      //   $form_container->end();
      // }

      //Form erstellen - Feld suchen
      $search = new Form("index.php?module=config-application_ucp&action=browsefield", 'post', 'search_form');
      echo "<div style=\"padding-bottom: 3px; margin-top: -9px; text-align: right;\">";

      echo $search->generate_text_box('keywords', '', array('id' => 'search_keywords', 'class' => "field150 field_small")) . "\n";
      echo "<input type=\"submit\" class=\"search_button\" value=\"Suchen\" />\n";
      echo '
      <link rel="stylesheet" href="../jscripts/select2/select2.css">
      <script type="text/javascript" src="../jscripts/select2/select2.min.js?ver=1804"></script>
      <script type="text/javascript">
      <!--
      $("#search_keywords").select2({
        placeholder: "Feld suchen",
        minimumInputLength: 2,
        multiple: false,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
          url: "../xmlhttp.php?action=get_aucpfields",
          dataType: \'json\',
          data: function (term, page) {
            return {
              query: term, // search term
            };
          },
          results: function (data, page) { // parse the results into the format expected by Select2.
            // since we are using custom formatting functions we do not need to alter remote JSON data
            return {results: data};
          }
        },
        initSelection: function(element, callback) {
          var query = $(element).val();
          if (query !== "") {
            $.ajax("../xmlhttp.php?action=get_aucpfields", {
              data: {
                query: query
              },
              dataType: "json"
            }).done(function(data) { callback(data); });
          }
        },
      });
  
      $(\'[for=search_keywords]\').on(\'click\', function(){
        $("#search_keywords").select2(\'open\');
        return false;
      });
      // -->
      </script>';

      echo "</div>\n";
      echo $search->end();

      //Hier erstellen wir jetzt eine Übersicht über unsere ganzen Felder
      //erst brauchen wir einen Container und ein Formular - für delete, die Sortierung etc.
      $form = new Form("index.php?module=config-application_ucp&amp;action=update_order", "post");
      $form_container = new FormContainer($lang->application_ucp_overview);
      $get_fields = $db->simple_select("application_ucp_fields", "*", "", array(array("order_by" => "cat_id, sorting")));
      $form_container->output_row_header("Details");
      if ($mybb->settings['application_ucp_acp_cats'] == "1") {
        $form_container->output_row_header($lang->application_ucp_overview_cat);
      }
      $form_container->output_row_header($lang->application_ucp_overview_sort);
      $form_container->output_row_header($lang->application_ucp_overview_opt);


      $fields_acp = $db->num_rows($get_fields, "id");
      if ($fields_acp > 0) {
        // Figure out if we need to display multiple pages.
        $per_page = 0;
        if ($mybb->settings['application_ucp_acp_pagination_fields'] != 0) {
          $per_page = $mybb->settings['application_ucp_acp_pagination_fields'];
        }
        $mybb->input['page'] = $mybb->get_input('page', MyBB::INPUT_INT);
        if ($mybb->input['page'] > 0) {
          $current_page = $mybb->input['page'];
          $start = ($current_page - 1) * $per_page;
          $pages = $fields_acp / $per_page;
          $pages = ceil($pages);
          if ($current_page > $pages) {
            $start = 0;
            $current_page = 1;
          }
        } else {
          $start = 0;
          $current_page = 1;
        }
      }
      if ($mybb->settings['application_ucp_acp_pagination_fields'] != 0) {
        $get_field_pages = $db->write_query("SELECT *  FROM " . TABLE_PREFIX . "application_ucp_fields ORDER BY cat_id, sorting
			LIMIT {$start}, {$per_page}");
        $pagination = draw_admin_pagination($current_page, $per_page, $fields_acp, "index.php?module=config-application_ucp&amp;page={page}");
        echo $pagination;
      } else {
        $get_field_pages = $db->write_query("SELECT *  FROM " . TABLE_PREFIX . "application_ucp_fields ORDER BY cat_id, sorting");
      }

      //Alle existierenden Felder bekommen
      while ($field = $db->fetch_array($get_field_pages)) {
        //Infos zusammenbauen
        if ($field['editable']) {
          $editable = "editierbar nach Annahme |";
        } else {
          $editable = "nicht editierbar nach Annahme |";
        }
        if ($field['options']) {
          $options = "Auswählbar:  {$field['options']} |";
        } else {
          $options = "";
        }
        if ($field['mandatory']) {
          $mandatory = "Pflichtfeld |";
        } else {
          $mandatory = "";
        }
        if ($field['dependency'] != "none") {
          $dependency = "Nur wenn {$field['dependency']} = {$field['dependency_value']}|";
        } else {
          $dependency = "";
        }
        if ($field['template']) {
          $activ = "Feld mit Vorlage |";
        } else {
          $activ = "";
        }
        if ($field['active']) {
          $activ_start = "";
          $activ_end = "";
        } else {
          $activ_start = "<b style=\"color:red;\">Deaktiviert:</b> <s><i>";
          $activ_end = "</i></s>";
        }
        if ($field['searchable']) {
          $searchable = "In Mitgliederliste suchbar |";
        } else {
          $searchable = "";
        }
        if ($field['postbit'] || $field['profile'] || $field['memberlist']) {
          $view = "";
          if ($field['postbit'] && $mybb->settings['application_ucp_postbit_view'] == 0) {
            $view_postbit = "<ul>
            <li><b>Anzeige im Postbit:</b> </li>
            <li>Label & Value: &#x007B;&dollar;post['labelvalue_{$field['fieldname']}']&#x007D;
            <li>Label & Value in div: &#x007B;&dollar;post['labelvalue_div_{$field['fieldname']}']&#x007D;
            <li>Label & Value in div + elemente in div: &#x007B;&dollar;post['labelvalue_divcon_{$field['fieldname']}']&#x007D;
            <li>Label: &#x007B;&dollar;post['label_{$field['fieldname']}']&#x007D;
            <li>Label in div: &#x007B;&dollar;post['label_div_{$field['fieldname']}']&#x007D;
            <li>Value: &#x007B;&dollar;post['value_{$field['fieldname']}']&#x007D;
            <li>Value in div: &#x007B;&dollar;post['value_div_{$field['fieldname']}']&#x007D;	</ul>";
          } else {
            $view_postbit = "<ul>
            <li><b>Anzeige im Postbit:</b> automatisch</li>
            </uL>";
          }
          if ($field['profile'] && $mybb->settings['application_ucp_profile_view'] == 0) {
            $view_profile = "<ul>
            <li><b>Anzeige im Profil:</b> </li>
            <li>Label & Value: &#x007B;&dollar;fields['labelvalue_{$field['fieldname']}']&#x007D;
            <li>Label & Value in div: &#x007B;&dollar;fields['labelvalue_div_{$field['fieldname']}']&#x007D;
            <li>Label & Value in div + elemente in div: &#x007B;&dollar;fields['labelvalue_divcon_{$field['fieldname']}']&#x007D;
            <li>Label: &#x007B;&dollar;fields['label_{$field['fieldname']}']&#x007D;
            <li>Label in div: &#x007B;&dollar;fields['label_div_{$field['fieldname']}']&#x007D;
            <li>Value: &#x007B;&dollar;fields['value_{$field['fieldname']}']&#x007D;	
            <li>Value in div: &#x007B;&dollar;fields['value_div_{$field['fieldname']}']&#x007D;	</ul>";
          } else {
            $view_profile = "<ul>
            <li><b>Anzeige im Profile:</b> automatisch</li>
            </ul>";
          }
          if ($field['profile'] && $mybb->settings['application_ucp_profile_view'] == 0) {
            $view_memberlist = "<ul>
            <li><b>Anzeige in der Memberlist:</b> </li>
            <li>Label & Value: &#x007B;&dollar;user['labelvalue_{$field['fieldname']}']&#x007D;
            <li>Label & Value in div: &#x007B;&dollar;user['labelvalue_div_{$field['fieldname']}']&#x007D;
            <li>Label & Value in div + elemente in div: &#x007B;&dollar;user['labelvalue_divcon_{$field['fieldname']}']&#x007D;
            <li>Label: &#x007B;&dollar;user['label_{$field['fieldname']}']&#x007D;
            <li>Label in div: &#x007B;&dollar;user['label_div_{$field['fieldname']}']&#x007D;
            <li>Value: &#x007B;&dollar;user['value_{$field['fieldname']}']&#x007D;
            <li>Value in div: &#x007B;&dollar;user['value_div_{$field['fieldname']}']&#x007D;	</ul>";
          } else {
            $view_memberlist = "<ul>
            <li><b>Anzeige im Profile:</b> automatisch</li>
            </ul>";
          }
          $view .= $view_postbit . $view_profile . $view_memberlist;
        } else {
          $view = "";
        }

        //spalte name und Infos
        $form_container->output_cell(
          $activ_start . "<strong>" . htmlspecialchars_uni($field['label']) . "</strong> <br />
        Typ: {$field['fieldtyp']} | Name (Identifikator): {$field['fieldname']} | Label: {$field['label']} | 
        {$editable} 
        {$options}
        {$mandatory}
        {$dependency}
        {$searchable}
         <div style='max-height: 100px; overflow:auto;'>
        <div class=\"appacp_con\" style=\"display: flex; flex-wrap:wrap;\">
        {$view}
        </div></div>
        <br/>Alle Elemente(textfeld, das zugehörige label etc) bekommen die Klasse \"{$field['fieldname']}\", die zum Stylen verwenden werden kann.
        " . $activ_end . "</div>"
        );

        //Kategorien
        if ($mybb->settings['application_ucp_acp_cats'] == "1") {
          //$cats bekommen
          $cats = array();
          $cats[0] = "keine Auswahl";
          $get_cats = $db->simple_select("application_ucp_categories", "*", "", array("order_by" => "name"));
          while ($cat = $db->fetch_array($get_cats)) {
            $id = $cat['id'];
            $name = $cat['name'];
            $cats[$id] = $name;
          }
          $form_container->output_cell($form->generate_select_box("cat[{$field['id']}]", $cats, $field['cat_id'], array('id' => 'cat' . $field['id'], 'style' => "width: 30px;", 'min' => 0)));
        }
        //spalte reihenfolge
        $form_container->output_cell($form->generate_text_box("sorting[{$field['id']}]", $field['sorting'], array('id' => 'sorting' . $field['id'], 'style' => "width: 25px;", 'min' => 0)));
        //spalte für options
        //erst pop up dafür bauen
        $popup = new PopupMenu("application_ucp_{$field['id']}", "verwalten");
        $popup->add_item(
          "edit",
          "index.php?module=config-application_ucp&amp;action=application_ucp_edit&amp;fieldid={$field['id']}"
        );
        //Je nachdem ob das Feld gerade aktiv ist, option anzeigen
        if ($field['active'] == 1) {
          $popup->add_item(
            "deactivate",
            "index.php?module=config-application_ucp&amp;action=application_ucp_deactivate&amp;fieldid={$field['id']}"
              . "&amp;my_post_key={$mybb->post_code}"
          );
        } else {
          $popup->add_item(
            "activate",
            "index.php?module=config-application_ucp&amp;action=application_ucp_activate&amp;fieldid={$field['id']}"
              . "&amp;my_post_key={$mybb->post_code}"
          );
        }
        $popup->add_item(
          "delete",
          "index.php?module=config-application_ucp&amp;action=application_ucp_delete&amp;fieldid={$field['id']}"
            . "&amp;my_post_key={$mybb->post_code}"
        );

        $form_container->output_cell($popup->fetch(), array("class" => "align_center"));
        $form_container->construct_row();
      }
      $form_container->end();
      $buttons[] = $form->generate_submit_button("Sortierung speichern");
      $form_container->output_cell($form->output_submit_wrapper($buttons));


      $form->end();
      $page->output_footer();
      die();
    }

    //Suche von einem Feld
    $action = $mybb->get_input('action');
    if ($action == "browsefield") {
      $keywords = "";
      $fieldid = "";
      if ($mybb->get_input('keywords')) {
        $keywords = $db->escape_string($mybb->input['keywords']);
        $fieldid = $db->fetch_field($db->simple_select("application_ucp_fields", "id", "fieldname = '{$keywords}'"), "id");
        if ($fieldid == "") {
          $error = "Kein Feld mit dieser Bezeichnung gefunden.";
          if (isset($error)) {
            flash_message($error, 'error');
            admin_redirect("index.php?module=config-application_ucp&action=application_ucp_edit");
          }
        } else {
          admin_redirect("index.php?module=config-application_ucp&action=application_ucp_edit&fieldid=" . $fieldid);
        }
      }
    }

    $action = $mybb->get_input('action');

    if ($action == "update_order" && $mybb->request_method == "post") {
      $sorting = $mybb->get_input('sorting', MyBB::INPUT_ARRAY);
      //Reihenfolge speichern
      foreach ($sorting as $id => $order) {
        $update_query = array(
          "sorting" => (int)$order
        );
        $db->update_query("application_ucp_fields", $update_query, "id='" . (int)$id . "'");
      }
      //kategorie speichern
      $cats = $mybb->get_input('cat', MyBB::INPUT_ARRAY);
      foreach ($cats as $id => $cat) {
        $update_cat = array(
          "cat_id" => (int)$cat
        );
        $db->update_query("application_ucp_fields", $update_cat, "id='" . (int)$id . "'");
      }
      admin_redirect("index.php?module=config-application_ucp");
    }

    //Hier werden jetzt die Felder im ACP erstellt
    if ($mybb->input['action'] == "application_ucp_add") {
      //einfügen in der DB
      if ($mybb->request_method == "post") {
        // als erstes prüfen ob alle Felder ausgefüllt sind und Fehler abfangen
        // Name muss ausgefüllt sein
        if (empty($mybb->input['fieldname'])) {
          $errors[] = $lang->application_ucp_err_name;
        }
        //Name muss eindeutig sein
        $testname = $db->simple_select("application_ucp_fields", "*", "fieldname = '{$mybb->get_input('fieldname')}'");
        if ($db->num_rows($testname)) {
          $errors[] = $lang->application_ucp_err_name_exists;
        }
        // Name darf keine Sonderzeichen enthalten
        if (!preg_match("#^[a-zA-Z\-\_]+$#", $mybb->get_input('fieldname'))) {
          $errors[] = $lang->application_ucp_err_name_sonder;
        }
        // Label muss ausgefüllt sein
        if (empty($mybb->get_input('fieldlabel'))) {
          $errors[] = $lang->application_ucp_err_label;
        }
        // Feldtyp muss ausgewählt sein
        if (empty($mybb->get_input('fieldtyp'))) {
          $errors[] = $lang->application_ucp_err_fieldtyp;
        }
        // Feldtyp muss ausgewählt sein
        if (empty($mybb->get_input('fieldtyp'))) {
          $errors[] = $lang->application_ucp_err_fieldtyp;
        }

        // fieldoptions muss bei folgenden ausgefüllt sein
        if (
          $mybb->get_input('fieldtyp') == "select" ||
          $mybb->get_input('fieldtyp') == "select_multiple" ||
          $mybb->get_input('fieldtyp') == "checkbox" ||
          $mybb->get_input('fieldtyp') == "radio"
        ) {
          if (empty($mybb->get_input('fieldoptions'))) {
            $errors[] = $lang->application_ucp_err_fieldoptions;
          }
        }
        // Feldtyp muss ausgewählt sein
        if (empty($mybb->get_input('fieldtyp'))) {
          $errors[] = $lang->application_ucp_err_fieldtyp;
        }

        // Wurde eine Abhängigkeit ausgewählt?
        if ($mybb->get_input('dependency') != "none") {
          //Abhängigkeitswert wurde leer gelasse
          if (empty($mybb->get_input('dependency_value'))) {
            $errors[] = $lang->application_ucp_err_dependency_value_empty;
          }
          //Falscher Abhängigkeitswert
          //wir brauchen erst die options des Felds von dem es abhängig ist
          // $get_dep = $db->fetch_field($db->simple_select("application_ucp_fields", "options", "fieldname = '{$mybb->input['dependency']}'"), "options");
          // // wir prüfen ob die Options den angegebenen Wert enthält. 
          // if (strpos($get_dep, $mybb->get_input('dependency_value')) === false) {
          //   //gibt keine Option mit diesem Wert
          //   $errors[] = $lang->application_ucp_err_dependency_value_wrong;
          // }
        }

        // wenn es keine Fehler gibt, speichern
        if (empty($errors)) {

          if ($mybb->get_input('guest') == '0') {
            $guestcontent = $db->escape_string($mybb->get_input('guest_content'));
          } else {
            $guestcontent = "";
          }

          $insert = [
            "fieldname" => $db->escape_string($mybb->get_input('fieldname')),
            "fieldtyp" => $db->escape_string($mybb->get_input('fieldtyp')),
            "fielddescr" => $db->escape_string($mybb->get_input('fielddescr')),
            "label" => $db->escape_string($mybb->get_input('fieldlabel')),
            "options" => $db->escape_string($mybb->get_input('fieldoptions')),
            "editable" => intval($mybb->get_input('fieldeditable')),
            "mandatory" => intval($mybb->get_input('fieldmandatory')),
            "dependency" => $db->escape_string($mybb->get_input('dependency')),
            "dependency_value" => $db->escape_string($mybb->get_input('dependency_value')),
            "postbit" => intval($mybb->get_input('fieldpostbit')),
            "profile" => intval($mybb->get_input('fieldprofile')),
            "memberlist" => intval($mybb->get_input('fieldmember')),
            "template" => $db->escape_string($mybb->get_input('fieldtemplate')),
            "sorting" => intval($mybb->get_input('fieldsort')),
            "allow_html" => intval($mybb->get_input('fieldhtml')),
            "allow_mybb" => intval($mybb->get_input('fieldmybb')),
            "allow_img" => intval($mybb->get_input('fieldimg')),
            "allow_video" => intval($mybb->get_input('fieldvideo')),
            "searchable" => intval($mybb->get_input('searchable')),
            "suggestion" => intval($mybb->get_input('suggestion')),
            "guest" => intval($mybb->get_input('guest')),
            "guest_content" => $guestcontent,
            "container" => $db->escape_string($mybb->get_input('container')),
          ];
          $db->insert_query("application_ucp_fields", $insert);
          $mybb->input['module'] = "application_ucp";
          $mybb->input['action'] = $lang->application_ucp_success;
          log_admin_action(htmlspecialchars_uni($mybb->input['fieldname']));

          flash_message($lang->application_ucp_success, 'success');
          admin_redirect("index.php?module=config-application_ucp");
        }
      }

      //felder erstellen
      //Navigation basteln
      $page->add_breadcrumb_item($lang->application_ucp_createfieldtype);
      $page->output_header($lang->application_ucp_name);
      $sub_tabs = application_ucp_do_submenu();
      $page->output_nav_tabs($sub_tabs, 'application_ucp_add');

      if (isset($errors)) {
        $page->output_inline_error($errors);
      }

      //Formular bauen 
      $form = new Form("index.php?module=config-application_ucp&amp;action=application_ucp_add", "post", "", 1);
      $form_container = new FormContainer($lang->application_ucp_formname);

      // Welche Auswahlmöglichkeiten an Feldtypen
      $select = array(
        "text" => "Textfeld",
        "textarea" => "Textarea",
        "select" => "Select",
        "select_multiple" => "Select Mehrfachauswahl",
        "checkbox" => "Checkbox",
        "radio" => "Radiobuttons",
        "date" => "Datum",
        "datetime-local" => "Datum und Uhrzeit",
        "url" => "URL"
      );
      //name des felds
      $form_container->output_row(
        $lang->application_ucp_add_name . " <em>*</em>",
        $lang->application_ucp_add_name_descr,
        $form->generate_text_box('fieldname', $mybb->get_input('fieldname'))
      );
      //beschreibung/anzeige des Felds
      $form_container->output_row(
        $lang->application_ucp_add_fieldlabel . " <em>*</em>",
        $lang->application_ucp_add_fieldlabel_descr,
        $form->generate_text_box('fieldlabel', $mybb->get_input('fieldlabel'))
      );
      //Typ des Felds
      $form_container->output_row(
        $lang->application_ucp_add_fieldtyp,
        $lang->application_ucp_add_fieldtyp_descr,
        $form->generate_select_box('fieldtyp', $select, $mybb->get_input('fieldtyp'), array('id' => 'fieldtyp'))
      );
      //Feldbeschreibung
      $form_container->output_row(
        $lang->application_ucp_add_descr,
        $lang->application_ucp_add_descr_descr,
        $form->generate_text_box('fielddescr', $mybb->get_input('fielddescr'))
      );
      //Auswahloptionen 
      $form_container->output_row(
        $lang->application_ucp_add_fieldoptions,
        $lang->application_ucp_add_fieldoptions_descr,
        $form->generate_text_box('fieldoptions', $mybb->get_input('fieldoptions'))
      );
      //pflichtfeld
      $form_container->output_row(
        $lang->application_ucp_add_fieldmandatory,
        $lang->application_ucp_add_fieldmandatory_descr,
        $form->generate_yes_no_radio('fieldmandatory', $mybb->get_input('fieldmandatory'))
      );
      //editierbar
      $form_container->output_row(
        $lang->application_ucp_add_fieldeditable,
        $lang->application_ucp_add_fieldeditable_descr,
        $form->generate_yes_no_radio('fieldeditable', $mybb->get_input('fieldeditable'))
      );
      //Abhängigkeit? 
      $select_dep_query = $db->simple_select("application_ucp_fields", "fieldname, label", "");
      $select_dep = array("none" => "keine Abhängigkeit");
      while ($deps = $db->fetch_array($select_dep_query)) {
        $name = $deps['fieldname'];
        $select_dep[$name] = $deps['label'];
      }
      //von welchem feld
      $form_container->output_row(
        $lang->application_ucp_add_fielddependency,
        $lang->application_ucp_add_fielddependency_descr,
        $form->generate_select_box('dependency', $select_dep, $mybb->get_input('dependency'), array("id" => "sel_dep"))
      );
      //von welchem wert ist die Abhängigkeit abhängig?
      $form_container->output_row(
        $lang->application_ucp_add_fielddependencyval,
        $lang->application_ucp_add_fielddependencyval_descr,
        $form->generate_text_box('dependency_value', $mybb->get_input('dependency_value'))
      );
      //Anzeige im postbit?
      $form_container->output_row(
        $lang->application_ucp_add_fieldpostbit,
        $lang->application_ucp_add_fieldpostbit_descr,
        $form->generate_yes_no_radio('fieldpostbit', $mybb->get_input('fieldpostbit'))
      );
      //anzeige im profil
      $form_container->output_row(
        $lang->application_ucp_add_fieldprofile,
        $lang->application_ucp_add_fieldprofile_descr,
        $form->generate_yes_no_radio('fieldprofile', $mybb->get_input('fieldprofile'))
      );
      //anzeige in der Mitgliederliste
      $form_container->output_row(
        $lang->application_ucp_add_fieldmember,
        $lang->application_ucp_add_fieldmember_descr,
        $form->generate_yes_no_radio('fieldmember', $mybb->get_input('fieldmember'))
      );
      //Vorlage im Feld? 
      $form_container->output_row(
        $lang->application_ucp_add_fieldtemplate,
        $lang->application_ucp_add_fieldtemplate_descr,
        $form->generate_text_area('fieldtemplate', $mybb->get_input('fieldtemplate'))
      );
      //html
      $form_container->output_row(
        $lang->application_ucp_add_fieldhtml,
        $lang->application_ucp_add_fieldhtml_descr,
        $form->generate_yes_no_radio('fieldhtml', $mybb->get_input('fieldhtml'))
      );
      //mybb code
      $form_container->output_row(
        $lang->application_ucp_add_fieldmybb,
        $lang->application_ucp_add_fieldmybb_descr,
        $form->generate_yes_no_radio('fieldmybb', $mybb->get_input('fieldmybb'))
      );
      // img
      $form_container->output_row(
        $lang->application_ucp_add_fieldimg,
        $lang->application_ucp_add_fieldimg_descr,
        $form->generate_yes_no_radio('fieldimg', $mybb->get_input('fieldimg'))
      );
      // video
      $form_container->output_row(
        $lang->application_ucp_add_fieldvideo,
        $lang->application_ucp_add_fieldvideo_descr,
        $form->generate_yes_no_radio('fieldvideo', $mybb->get_input('fieldvideo'))
      );
      // In Mitgliederliste suchbar? 
      $form_container->output_row(
        $lang->application_ucp_add_searchable,
        $lang->application_ucp_add_searchable_descr,
        $form->generate_yes_no_radio('searchable', $mybb->get_input('searchable'))
      );
      // In Mitgliederliste suchbar? 
      $form_container->output_row(
        $lang->application_ucp_add_suggestion,
        $lang->application_ucp_add_suggestion_descr,
        $form->generate_yes_no_radio('suggestion', $mybb->get_input('suggestion'))
      );
      // In Mitgliederliste suchbar? 
      $form_container->output_row(
        $lang->application_ucp_add_active,
        $lang->application_ucp_add_active_descr,
        $form->generate_yes_no_radio('active', $mybb->get_input('active'))
      );
      // Dürfen Gäste das Feld sehen? 
      $form_container->output_row(
        $lang->application_ucp_add_guest,
        $lang->application_ucp_add_guest_descr,
        $form->generate_yes_no_radio('guest', $mybb->get_input('guest'))
      );
      //optionaler Inhalt für Gäste?
      $form_container->output_row(
        $lang->application_ucp_add_guest_content,
        $lang->application_ucp_add_guest_content_descr,
        $form->generate_text_box('guest_content', $mybb->get_input('guest_content'))
      );
      //Html Element um reines value Feld
      $select = array(
        "span" => "span",
        "div" => "div",
        "keins" => "keins",
      );
      $form_container->output_row(
        $lang->application_ucp_add_container,
        $lang->application_ucp_add_container_descr,
        $form->generate_select_box('container', $select, $mybb->get_input('container'), array('id' => 'container'))
      );
      //anzeige reihenfolge
      $form_container->output_row(
        $lang->application_ucp_add_fieldsort,
        $lang->application_ucp_add_fieldsort_descr,
        $form->generate_numeric_field('fieldsort', $mybb->get_input('fieldsort'))
      );

      $form_container->end();
      $buttons[] = $form->generate_submit_button($lang->application_ucp_save);
      $form->output_submit_wrapper($buttons);
      $form->end();
      $page->output_footer();

      die();
    }

    //Steckbriefe der User verwalten
    if ($mybb->get_input('action') == "application_ucp_manageusers") {
      $page->add_breadcrumb_item($lang->application_ucp_manageusers);
      $page->output_header($lang->application_ucp_name);
      $sub_tabs = application_ucp_do_submenu();
      $page->output_nav_tabs($sub_tabs, 'application_ucp_manageusers');
      if (isset($errors)) {
        $page->output_inline_error($errors);
      }
      $get_users = $db->simple_select("users", "*");
      $users = $db->num_rows($get_users, "unapprovedthreads");

      //Benutzer suchen 
      //Form erstellen
      $search = new Form("index.php?module=config-application_ucp&action=browse", 'post', 'search_form');
      echo "<div style=\"padding-bottom: 3px; margin-top: -9px; text-align: right;\">";

      echo $search->generate_text_box('keywords', '', array('id' => 'search_keywords', 'class' => "field150 field_small")) . "\n";
      echo "<input type=\"submit\" class=\"search_button\" value=\"Suchen\" />\n";
      echo '
      <link rel="stylesheet" href="../jscripts/select2/select2.css">
      <script type="text/javascript" src="../jscripts/select2/select2.min.js?ver=1804"></script>
      <script type="text/javascript">
      <!--
      $("#search_keywords").select2({
        placeholder: "Benutzer suchen",
        minimumInputLength: 2,
        multiple: false,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
          url: "../xmlhttp.php?action=get_users",
          dataType: \'json\',
          data: function (term, page) {
            return {
              query: term, // search term
            };
          },
          results: function (data, page) { // parse the results into the format expected by Select2.
            // since we are using custom formatting functions we do not need to alter remote JSON data
            return {results: data};
          }
        },
        initSelection: function(element, callback) {
          var query = $(element).val();
          if (query !== "") {
            $.ajax("../xmlhttp.php?action=get_users&getone=1", {
              data: {
                query: query
              },
              dataType: "json"
            }).done(function(data) { callback(data); });
          }
        },
      });
  
      $(\'[for=username]\').on(\'click\', function(){
        $("#username").select2(\'open\');
        return false;
      });
      // -->
      </script>';

      echo "</div>\n";
      echo $search->end();

      //alle registrierten User bekommen
      $form = new Form("index.php?module=config-application_ucp&action=application_ucp_manageusers", "post");
      $form_container = new FormContainer($lang->application_ucp_manageusers_dscr);
      $form_container->output_row_header($lang->application_ucp_manageusers_all);

      if ($users > 0) {
        // Figure out if we need to display multiple pages.
        $per_page = 0;
        if ($mybb->settings['application_ucp_acp_pagination'] != 0) {
          $per_page = $mybb->settings['application_ucp_acp_pagination'];
        }
        $mybb->input['page'] = $mybb->get_input('page', MyBB::INPUT_INT);
        if ($mybb->input['page'] > 0) {
          $current_page = $mybb->input['page'];
          $start = ($current_page - 1) * $per_page;
          $pages = $users / $per_page;
          $pages = ceil($pages);
          if ($current_page > $pages) {
            $start = 0;
            $current_page = 1;
          }
        } else {
          $start = 0;
          $current_page = 1;
        }
      }
      if ($mybb->settings['application_ucp_acp_pagination'] != 0) {
        $get_users_pages = $db->write_query("SELECT *  FROM " . TABLE_PREFIX . "users ORDER BY username
			LIMIT {$start}, {$per_page}");
        $pagination = draw_admin_pagination($current_page, $per_page, $users, "index.php?module=config-application_ucp&action=application_ucp_manageusers&amp;page={page}");
        echo $pagination;
      } else {
        $get_users_pages = $db->write_query("SELECT *  FROM " . TABLE_PREFIX . "users ORDER BY username");
      }

      //Bewerber oder angenommen?
      while ($user = $db->fetch_array($get_users_pages)) {
        if (is_member($mybb->settings['application_ucp_applicants'], $user['uid'])) {
          $userstatus = "Bewerber";
        } else {
          $userstatus = "angenommen";
        }
        $popup = new PopupMenu("user_{$user['uid']}", $lang->application_ucp_manageusers_manage);
        $popup->add_item(
          $lang->application_ucp_manageusers_application,
          "index.php?module=config-application_ucp&action=application_ucp_manageusers_user&amp;uid={$user['uid']}"
        );
        $popup->add_item(
          $lang->application_ucp_manageusers_profile,
          "{$mybb->settings['bburl']}/member.php?action=profile&uid={$user['uid']}"
        );
        $form_container->output_cell("<div style='display: flex; justify-content: space-around;'>
        <div style='font-weight: 700; flex-basis: 33%;'>" . htmlspecialchars_uni($user['username']) . "</div> 
        <div style='flex-basis: 33%; text-align:center;'>{$userstatus}</div>
        <div style='flex-basis: 33%; text-align:right;'>" . $popup->fetch() . "</div>
        </div>");
        $form_container->construct_row();
      }
      $form_container->end();
      $form->end();
      $page->output_footer();
    }

    //Bearbeiten vom Steckbrief eines Users
    if ($mybb->get_input('action') == "application_ucp_manageusers_user") {
      //speichern
      if ($mybb->request_method == "post") {
        $fields = $mybb->input;
        foreach ($fields as $key => $val) {
          //werte evt. löschen bei selects
          if (is_array($val)) {
            if (in_array("keineangabe", $val) || in_array(" keineangabe", $val)) {
              $fields[$key] = "";
            }
          }
          if ($val == "keineangabe") {
            $fields[$key] = "";
          }
        }
        // var_dump($fields );
        $uid = $mybb->get_input('uid', MyBB::INPUT_INT);

        if (empty($errors)) {
          //wir nutzen hier unsere speicherfunktion
          application_ucp_savefields($fields, $uid);
        }
      }

      //Navigation bauen
      $page->add_breadcrumb_item($lang->application_ucp_createfieldtype);
      $page->output_header($lang->application_ucp_name);
      $sub_tabs = application_ucp_do_submenu();
      $page->output_nav_tabs($sub_tabs, 'application_ucp_manageusers');
      //welchen User bearbeiten wir?
      $uid = $mybb->get_input('uid', MyBB::INPUT_INT);
      //alle infos des users
      $user = get_user($uid);
      //alle Felder bekommen, die ausgefüllt werden können
      $get_fields = $db->simple_select("application_ucp_fields", "*", "", array('order_by' => 'sorting'));

      //Formular bauen 
      $form = new Form("index.php?module=config-application_ucp&amp;action=application_ucp_manageusers_user", "post", "", 1);
      $form_container = new FormContainer(htmlspecialchars_uni($user['username']) . ": " . $lang->application_ucp_edituser);

      //welchen user bearbeiten wir?
      echo $form->generate_hidden_field('uid', $uid);
      //Jetzt das formular bauen
      while ($field = $db->fetch_array($get_fields)) {
        $get_input = "";
        if ($field['mandatory']) {
          $required = " (Pflichtfeld)";
        } else {
          $required = "";
        }
        if ($field['dependency'] != "none") {
          $dep = "Abhängig von <b>{$field['dependency']}</b>.";
        } else {
          $dep = "";
        }
        if ($field['active'] != "1") {
          $notactive = "Feld ist <i>Deaktiviert</i>.";
        } else {
          $notactive = "";
        }

        // Label und Beschreibung basteln
        $label = $field['label'] . $required . ":";
        $descr = $dep . " " . $notactive;
        //wenn es schon input gibt, ausgeben
        $get_input = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "uid = '{$uid}' AND fieldid = '{$field['id']}'"), "value");
        //je nach art des felds, formularfeld bauen
        if ($field['fieldtyp'] == "text") {
          $form_container->output_row(
            $label,
            $descr,
            $form->generate_text_box($field['id'], $get_input)
          );
        }
        if ($field['fieldtyp'] == "textarea") {
          $form_container->output_row(
            $label,
            $descr,
            $form->generate_text_area($field['id'], $get_input)
          );
        }
        if ($field['fieldtyp'] == "select") {
          $options = explode(",", $field['options']);
          $get_options = array();
          $get_options = array("keineangabe" => "keine Angabe");
          foreach ($options as $option) {
            $option = trim($option);
            $get_options[$option] = $option;
          }
          $form_container->output_row(
            $label,
            $descr,
            $form->generate_select_box($field['id'], $get_options, $get_input, array('checked' => "bla", 'id' => 'fieldtyp'))
          );
        }
        if ($field['fieldtyp'] == "select_multiple") {
          $options = explode(",", $field['options']);
          $get_options = array("keineangabe" => "keine Angabe");
          foreach ($options as $option) {
            $option = trim($option);
            $get_options[$option] = $option;
          }
          $get_inputs = explode(",",  $get_input);
          $form_container->output_row(
            $label,
            $descr,
            $form->generate_select_box($field['id'] . "[]", $get_options, $get_inputs, array('id' => $field['fieldname'], 'multiple' => 'multiple'))
          );
        }
        if ($field['fieldtyp'] == "checkbox") {
          $checkboxes = "";
          $options = explode(",", $field['options']);
          $get_options = array();
          foreach ($options as $option) {
            $option = trim($option);
            $get_options[$option] = $option;
          }
          foreach ($options as $option) {
            if (strpos($get_input, trim($option)) !== false) {
              $check = 1;
            } else {
              $check = 0;
            }
            $checkboxes .= $form->generate_check_box("{$field['id']}[]", $option, $option, array('checked' => $check)) . "<br/>";
          }
          $form_container->output_row(
            $label,
            $descr,
            $checkboxes
          );
        }
        if ($field['fieldtyp'] == "radio") {
          $radios = "";
          $options = explode(",", $field['options']);
          $get_options = array();
          foreach ($options as $option) {
            $option = trim($option);
            $get_options[$option] = $option;
          }

          $radios = $form->generate_radio_button($field['id'], "keineangabe", "keine angabe", array('checked' => $check)) . "<br/>";

          foreach ($options as $option) {
            if (strpos($get_input, trim($option)) !== false) {
              $check = 1;
            } else {
              $check = 0;
            }
            $radios .= $form->generate_radio_button($field['id'], $option, $option, array('checked' => $check)) . "<br/>";
          }
          $form_container->output_row(
            $label,
            $descr,
            $radios
          );
        }
        //für date und url gibt es keine mybbfunktion, also bauen wir es selber
        if ($field['fieldtyp'] == "date" or $field['fieldtyp'] == "datetime-local" or $field['fieldtyp'] == 'url') {
          $form_container->output_row(
            $label,
            $descr,
            "<input type=\"{$field['fieldtyp']}\" name=\"{$field['id']}\" value=\"{$get_input}\" />"
          );
        }
      }

      $form_container->end();
      $buttons[] = $form->generate_submit_button($lang->application_ucp_save);
      $form->output_submit_wrapper($buttons);
      $form->end();
      $page->output_footer();
    }

    //Ergebnis Benutzersuchen
    if ($mybb->input['action'] == "browse") {
      $user = array();
      $keywords = "";
      if ($mybb->get_input('keywords')) {
        $keywords = $db->escape_string($mybb->input['keywords']);
        $user = get_user_by_username($keywords);
        if (!$user) {
          $error = "Kein User mit diesem Username gefunden.";
          if (isset($error)) {
            $page->output_inline_error($error);
          }
        } else {
          admin_redirect("index.php?module=config-application_ucp&action=application_ucp_manageusers_user&uid=" . $user['uid']);
        }
      }
    }

    //Editieren eines Felds
    if ($mybb->get_input('action') == "application_ucp_edit") {
      //hier wird das speichern des zu editierenden Feldes gemanaged
      //erst wieder Fehler abfangen
      if ($mybb->request_method == "post") {
        $fieldid = $mybb->get_input('fieldid');
        if (empty($mybb->get_input('fieldname'))) {
          $errors[] = $lang->application_ucp_err_name;
        }
        // Name darf keine Sonderzeichen enthalten
        if (!preg_match("#^[a-zA-Z\-\_]+$#", $mybb->get_input('fieldname'))) {
          $errors[] = $lang->application_ucp_err_name_sonder;
        }
        // Label muss ausgefüllt sein
        if (empty($mybb->get_input('fieldlabel'))) {
          $errors[] = $lang->application_ucp_err_label;
        }
        // fieldoptions muss bei folgenden ausgefüllt sein
        if (
          $mybb->get_input('fieldtyp') == "select" ||
          $mybb->get_input('fieldtyp') == "select_multiple" ||
          $mybb->get_input('fieldtyp') == "checkbox" ||
          $mybb->get_input('fieldtyp') == "radio"
        ) {
          if (empty($mybb->get_input('fieldoptions'))) {
            $errors[] = $lang->application_ucp_err_fieldoptions;
          }
        }
        // Feldtyp muss ausgewählt sein
        if (empty($mybb->get_input('fieldtyp'))) {
          $errors[] = $lang->application_ucp_err_fieldtyp;
        }

        // Wurde eine Abhängigkeit ausgewählt?
        if ($mybb->get_input('dependency') != "none") {
          //Abhängigkeitswert wurde leer gelasse
          if (empty($mybb->get_input('dependency_value'))) {
            $errors[] = $lang->application_ucp_err_dependency_value_empty;
          }
          //Falscher Abhängigkeitswert
          //wir brauchen erst die options des Felds von dem es abhängig ist
          // $get_dep = $db->fetch_field($db->simple_select("application_ucp_fields", "options", "fieldname = '" . $mybb->get_input('dependency') . "'"), "options");
          // // wir prüfen ob die Options den angegebenen Wert enthält. 
          // $depinput = $mybb->get_input('dependency_value');
          // if (strpos($get_dep, $depinput) === false) {
          //   //gibt keine Option mit diesem Wert
          //   $errors[] = $lang->application_ucp_err_dependency_value_wrong;
          // }
        }
        // dependency_value
        // wenn es keine Fehler gibt, speichern
        if (empty($errors)) {

          if ($mybb->get_input('guest') == '0') {
            $guestcontent = $db->escape_string($mybb->get_input('guest_content'));
          } else {
            $guestcontent = "";
          }

          $update = [
            "fieldname" => $db->escape_string($mybb->get_input('fieldname')),
            "fieldtyp" => $db->escape_string($mybb->get_input('fieldtyp')),
            "label" => $db->escape_string($mybb->get_input('fieldlabel')),
            "fielddescr" => $db->escape_string($mybb->get_input('fielddescr')),
            "options" => $db->escape_string($mybb->get_input('fieldoptions')),
            "editable" => $mybb->get_input('fieldeditable', MyBB::INPUT_INT),
            "mandatory" => $mybb->get_input('fieldmandatory', MyBB::INPUT_INT),
            "dependency" => $db->escape_string($mybb->get_input('dependency')),
            "dependency_value" => $db->escape_string($mybb->get_input('dependency_value')),
            "postbit" => $mybb->get_input('fieldpostbit', MyBB::INPUT_INT),
            "profile" => $mybb->get_input('fieldprofile', MyBB::INPUT_INT),
            "memberlist" => $mybb->get_input('fieldmember', MyBB::INPUT_INT),
            "template" => $db->escape_string($mybb->get_input('fieldtemplate')),
            "sorting" => $mybb->get_input('fieldsort', MyBB::INPUT_INT),
            "allow_html" => $mybb->get_input('fieldhtml', MyBB::INPUT_INT),
            "allow_mybb" => $mybb->get_input('fieldmybb', MyBB::INPUT_INT),
            "allow_img" => $mybb->get_input('fieldimg', MyBB::INPUT_INT),
            "allow_video" => $mybb->get_input('fieldvideo', MyBB::INPUT_INT),
            "searchable" => $mybb->get_input('searchable', MyBB::INPUT_INT),
            "suggestion" => $mybb->get_input('suggestion', MyBB::INPUT_INT),
            "active" => $mybb->get_input('active', MyBB::INPUT_INT),
            "guest" => $mybb->get_input('guest', MyBB::INPUT_INT),
            "guest_content" => $guestcontent,
            "container" => $db->escape_string($mybb->get_input('container')),
          ];

          $db->update_query("application_ucp_fields", $update, "id = {$fieldid}");
          flash_message($lang->application_ucp_success, 'success');
          admin_redirect("index.php?module=config-application_ucp");
          die();
        }
      }

      //Das Formular erstellen
      $page->add_breadcrumb_item($lang->application_ucp_editfieldtype);
      //Header und Navigation
      $page->output_header($lang->application_ucp_editfieldtype);
      $sub_tabs = application_ucp_do_submenu();
      $page->output_nav_tabs($sub_tabs, 'application_ucp');

      if (isset($errors)) {
        $page->output_inline_error($errors);
      }

      $fieldid = $mybb->get_input('fieldid', MyBB::INPUT_INT);
      $get_field_data =  $db->simple_select("application_ucp_fields", "*", "id={$fieldid}");
      $field_data = $db->fetch_array($get_field_data);

      $form = new Form("index.php?module=config-application_ucp&amp;action=application_ucp_edit", "post", "", 1);
      echo $form->generate_hidden_field('fieldid', $fieldid);
      $form_container = new FormContainer($lang->application_ucp_formname_edit);
      $form_container->output_row(
        $lang->application_ucp_add_name,
        $lang->application_ucp_add_name_descr,
        $form->generate_text_box('fieldname', $field_data['fieldname'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldlabel,
        $lang->application_ucp_add_fieldlabel_descr,
        $form->generate_text_box('fieldlabel', $field_data['label'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_descr,
        $lang->application_ucp_add_descr_descr,
        $form->generate_text_box('fielddescr', $field_data['fielddescr'])
      );

      $select = array(
        "text" => "Textfeld",
        "textarea" => "Textarea",
        "select" => "Select",
        "select_multiple" => "Select Mehrfachauswahl",
        "checkbox" => "Checkbox",
        "radio" => "Radiobuttons",
        "date" => "Datum",
        "datetime-local" => "Datum und Uhrzeit",
        "url" => "url"
      );
      $form_container->output_row(
        $lang->application_ucp_add_fieldtyp,
        $lang->application_ucp_add_fieldtyp_descr,
        $form->generate_select_box('fieldtyp', $select, array($field_data['fieldtyp']), array('id' => 'fieldtyp'))
      );
      $form_container->output_row(
        $lang->application_ucp_add_fieldoptions,
        $lang->application_ucp_add_fieldoptions_descr,
        $form->generate_text_box('fieldoptions', $field_data['options'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldmandatory,
        $lang->application_ucp_add_fieldmandatory_descr,
        $form->generate_yes_no_radio('fieldmandatory', $field_data['mandatory'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldeditable,
        $lang->application_ucp_add_fieldeditable_descr,
        $form->generate_yes_no_radio('fieldeditable', $field_data['editable'])
      );

      //auswählbares felder für abhängigkeit
      $select_dep_query = $db->simple_select("application_ucp_fields", "fieldname, label", "");
      $select_dep = array("none" => "keine Abhängigkeit");
      while ($deps = $db->fetch_array($select_dep_query)) {
        $name = $deps['fieldname'];
        $select_dep[$name] = $deps['label'];
      }

      $form_container->output_row(
        $lang->application_ucp_add_fielddependency,
        $lang->application_ucp_add_fielddependency_descr,
        $form->generate_select_box('dependency', $select_dep, array($field_data['dependency']), array("id" => "sel_dep"))
      );

      $form_container->output_row(
        $lang->application_ucp_add_fielddependencyval,
        $lang->application_ucp_add_fielddependencyval_descr,
        $form->generate_text_box('dependency_value', $field_data['dependency_value'])

      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldpostbit,
        $lang->application_ucp_add_fieldpostbit_descr,
        $form->generate_yes_no_radio('fieldpostbit', $field_data['postbit'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldprofile,
        $lang->application_ucp_add_fieldprofile_descr,
        $form->generate_yes_no_radio('fieldprofile', $field_data['profile'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldmember,
        $lang->application_ucp_add_fieldmember_descr,
        $form->generate_yes_no_radio('fieldmember', $field_data['memberlist'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldtemplate,
        $lang->application_ucp_add_fieldtemplate_descr,
        $form->generate_text_area('fieldtemplate', $field_data['template'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldhtml,
        $lang->application_ucp_add_fieldhtml_descr,
        $form->generate_yes_no_radio('fieldhtml', $field_data['allow_html'])
      );
      $form_container->output_row(
        $lang->application_ucp_add_fieldmybb,
        $lang->application_ucp_add_fieldmybb_descr,
        $form->generate_yes_no_radio('fieldmybb', $field_data['allow_mybb'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldimg,
        $lang->application_ucp_add_fieldimg_descr,
        $form->generate_yes_no_radio('fieldimg', $field_data['allow_img'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldvideo,
        $lang->application_ucp_add_fieldvideo_descr,
        $form->generate_yes_no_radio('fieldvideo', $field_data['allow_video'])
      );

      $form_container->output_row(
        $lang->application_ucp_add_searchable,
        $lang->application_ucp_add_searchable_descr,
        $form->generate_yes_no_radio('searchable', $field_data['searchable'])
      );
      $form_container->output_row(
        $lang->application_ucp_add_suggestion,
        $lang->application_ucp_add_suggestion_descr,
        $form->generate_yes_no_radio('suggestion', $field_data['suggestion'])
      );
      $form_container->output_row(
        $lang->application_ucp_add_active,
        $lang->application_ucp_add_active_descr,
        $form->generate_yes_no_radio('active', $field_data['active'])
      );
      $form_container->output_row(
        $lang->application_ucp_add_guest,
        $lang->application_ucp_add_guest_descr,
        $form->generate_yes_no_radio('guest', $field_data['guest'])
      );
      $form_container->output_row(
        $lang->application_ucp_add_guest_content,
        $lang->application_ucp_add_guest_content_descr,
        $form->generate_text_box('guest_content', $field_data['guest_content'])
      );
      //Html Element um reines value Feld
      $select = array(
        "span" => "span",
        "div" => "div",
        "keins" => "keins",
      );
      $form_container->output_row(
        $lang->application_ucp_add_container,
        $lang->application_ucp_add_container_descr,
        $form->generate_select_box('container', $select, array($field_data['container']), array('id' => 'container'))
      );
      $form_container->output_row(
        $lang->application_ucp_add_fieldsort,
        $lang->application_ucp_add_fieldsort_descr,
        $form->generate_numeric_field('fieldsort', $field_data['sorting'])
      );

      $form_container->end();
      $buttons[] = $form->generate_submit_button($lang->application_ucp_save);
      $form->output_submit_wrapper($buttons);
      $form->end();
      $page->output_footer();
      die();
    }

    // Feld löschen.
    if ($mybb->input['action'] == "application_ucp_delete") {
      $fieldid = $mybb->get_input('fieldid', MyBB::INPUT_INT);
      if (empty($fieldid)) {
        flash_message($lang->application_ucp_err_delete, 'error');
        admin_redirect("index.php?module=config-application_ucp");
      }

      if (isset($mybb->input['no']) && $mybb->input['no']) {
        admin_redirect("index.php?module=config-application_ucp");
      }

      if (!verify_post_check($mybb->input['my_post_key'])) {
        flash_message($lang->invalid_post_verify_key2, 'error');
        admin_redirect("index.php?module=config-application_ucp");
      } else {
        if ($mybb->request_method == "post") {
          $fieldname = $db->fetch_field($db->simple_select("application_ucp_fields", "fieldname", "id='{$fieldid}'"), "fieldname");

          $db->delete_query("application_ucp_fields", "id='{$fieldid}'");
          $db->delete_query("application_ucp_userfields", "fieldid='{$fieldid}'");

          $mybb->input['module'] = "application-ucp";
          $mybb->input['action'] = $lang->application_ucp_delete;
          log_admin_action(htmlspecialchars_uni($fieldname));
          flash_message($lang->application_ucp_delete, 'success');
          admin_redirect("index.php?module=config-application_ucp");
        } else {
          $page->output_confirm_action(
            "index.php?module=config-application_ucp&amp;action=application_ucp_delete&amp;fieldid={$fieldid}",
            $lang->application_ucp_delete_ask
          );
        }
      }
    }

    //Feldtyp deaktivieren
    if ($mybb->input['action'] == "application_ucp_deactivate") {
      $fieldid = $mybb->get_input('fieldid', MyBB::INPUT_INT);

      if (!verify_post_check($mybb->input['my_post_key'])) {
        flash_message($lang->invalid_post_verify_key2, 'error');
        admin_redirect("index.php?module=config-application_ucp");
      } else {
        if ($mybb->request_method == "post") {
          $fieldname = $db->fetch_field($db->simple_select("application_ucp_fields", "fieldname", "id='{$fieldid}'"), "fieldname");

          $update = [
            "active" => 0,
          ];
          $db->update_query("application_ucp_fields", $update, "id='{$fieldid}'");

          $mybb->input['module'] = "application-ucp";
          $mybb->input['action'] = $lang->application_ucp_deactivate;
          log_admin_action(htmlspecialchars_uni($fieldname));
          flash_message($lang->application_ucp_deactivate, 'success');
          admin_redirect("index.php?module=config-application_ucp");
        } else {
          $page->output_confirm_action(
            "index.php?module=config-application_ucp&amp;action=application_ucp_deactivate&amp;fieldid={$fieldid}",
            $lang->application_ucp_deactivate_ask
          );
        }
      }
    }

    //Feldtypen aktivieren
    if ($mybb->input['action'] == "application_ucp_activate") {
      $fieldid = $mybb->get_input('fieldid', MyBB::INPUT_INT);

      if (!verify_post_check($mybb->input['my_post_key'])) {
        flash_message($lang->invalid_post_verify_key2, 'error');
        admin_redirect("index.php?module=config-application_ucp");
      } else {
        if ($mybb->request_method == "post") {
          $fieldname = $db->fetch_field($db->simple_select("application_ucp_fields", "fieldname", "id='{$fieldid}'"), "fieldname");

          $update = [
            "active" => 1,
          ];
          $db->update_query("application_ucp_fields", $update, "id='{$fieldid}'");

          $mybb->input['module'] = "application-ucp";
          $mybb->input['action'] = $lang->application_ucp_activate;
          log_admin_action(htmlspecialchars_uni($fieldname));
          flash_message($lang->application_ucp_activate, 'success');
          admin_redirect("index.php?module=config-application_ucp");
        } else {
          $page->output_confirm_action(
            "index.php?module=config-application_ucp&amp;action=application_ucp_activate&amp;fieldid={$fieldid}",
            $lang->application_ucp_activate_ask
          );
        }
      }
    }

    //Verwalten von Kategorien
    if ($mybb->get_input('action') == "application_ucp_managecats") {
      //speichern
      $action = $mybb->get_input('do');
      if ($action == "add" && $mybb->request_method == "post") {

        //speichern neue Kategorie
        $catname = $db->escape_string($mybb->get_input('catname'));
        $catorder = $mybb->get_input('catorder', MyBB::INPUT_INT);
        //fehler test - schon kategorie name vorhanden? 
        if (empty($mybb->get_input('catname'))) {
          $errors[] = $lang->application_ucp_err_namecat;
        }

        $test_name = $db->simple_select("application_ucp_categories", "name", "name = '" . $catname . "'");
        if ($db->num_rows($test_name) > 0) {
          $errors[] = $lang->application_ucp_err_namecat_exists;
        }
        //Keine Fehler - erstellen der Kategorie
        if (empty($errors)) {
          $add_cat = [
            "name" => $catname,
            "cat_order" => $catorder,
          ];
          $db->insert_query("application_ucp_categories", $add_cat);
        }
        admin_redirect("index.php?module=config-application_ucp&action=application_ucp_managecats");
      }

      if ($action == "change" && $mybb->request_method == "post") {
        //speichern neue Reihenfolge und namensänderung
        $sorting = $mybb->get_input('sorting', MyBB::INPUT_ARRAY);
        foreach ($sorting as $id => $order) {
          $update_query = array(
            "cat_order" => (int)$order
          );
          $db->update_query("application_ucp_categories", $update_query, "id='" . (int)$id . "'");
        }

        $changename = $mybb->get_input('changename', MyBB::INPUT_ARRAY);
        foreach ($changename as $id => $name) {
          $update_query = array(
            "name" => $db->escape_string($name)
          );
          $db->update_query("application_ucp_categories", $update_query, "id='" . (int)$id . "'");
        }
        admin_redirect("index.php?module=config-application_ucp&action=application_ucp_managecats");
      }

      //Das Formular erstellen
      $page->add_breadcrumb_item($lang->application_ucp_managecats);
      //Header und Navigation
      $page->output_header($lang->application_ucp_managecats);
      $sub_tabs = application_ucp_do_submenu();
      $page->output_nav_tabs($sub_tabs, 'application_ucp_managecats');
      //ausgabe wenn es Fehler gab
      if (isset($errors)) {
        $page->output_inline_error($errors);
      }
      if ($mybb->settings['application_ucp_acp_cats'] == 0) {
        echo "<h3 style='text-align: center; color:red;'><strong>Hinweis:</strong> Du hast aktuell Kategorien in den Einstellungen deaktiviert.</h2>";
      }
      //formular um eine neue Kategorie anzulegen
      $form = new Form("index.php?module=config-application_ucp&amp;action=application_ucp_managecats&amp;do=add", "post", "add_cat", 1);
      // echo $form->generate_hidden_field('fieldid', $fieldid)."bla";
      $form_container = new FormContainer($lang->application_ucp_namecat);
      $form_container->output_row(
        $lang->application_ucp_add_cat_name,
        $lang->application_ucp_add_cat_name_descr,
        $form->generate_text_box('catname', "")
      );
      $form_container->output_row(
        $lang->application_ucp_add_cat_order,
        $lang->application_ucp_add_cat_order_descr,
        $form->generate_numeric_field('catorder', "")
      );

      $buttons_add[] = $form->generate_submit_button($lang->application_ucp_save);
      $form_container->end();
      $form->output_submit_wrapper($buttons_add);
      $form->end();
      echo ("<br><br>");

      //Ausgabe vorhandener Kategorien - Sortierung/Name ändern
      $get_cats = $db->simple_select("application_ucp_categories", "*", "", array("order_by" => "cat_order"));
      $cats = $db->num_rows($get_cats, "name");
      $form = new Form("index.php?module=config-application_ucp&amp;action=application_ucp_managecats&amp;do=change", "post");
      $form_container = new FormContainer($lang->application_ucp_managecats);

      while ($cat = $db->fetch_array($get_cats)) {
        $form_container->output_cell($cat['name'], array('width' => '10%'));
        $form_container->output_cell("<label for='cat_name" . $cat['id'] . "'>Name ändern zu </label><br>" . $form->generate_text_box("changename[{$cat['id']}]", $cat['name'], array('id' => 'cat_name' . $cat['id'], 'style' => "width: 150px;")));

        $form_container->output_cell($form->generate_text_box("sorting[{$cat['id']}]", $cat['cat_order'], array('id' => 'cat_order' . $cat['id'], 'style' => "width: 25px;", 'min' => 0)), array('width' => '10%'));
        $popup = new PopupMenu("cat_{$cat['id']}", $lang->application_ucp_manageusers_manage);
        $popup->add_item(
          $lang->application_ucp_cat_delete,
          "index.php?module=config-application_ucp&action=application_ucp_cat_delete&amp;id={$cat['id']}"
            . "&amp;my_post_key={$mybb->post_code}"
        );
        $form_container->output_cell($popup->fetch(), array('width' => '150'));

        $form_container->construct_row();
      }
      $form_container->end();
      $buttons_manage[] = $form->generate_submit_button($lang->application_ucp_save, array('name' => 'manage'));
      $form->output_submit_wrapper($buttons_manage);
      $form->end();
      $page->output_footer();
    }

    // Kategorie löschen
    if ($mybb->input['action'] == "application_ucp_cat_delete") {
      $catid = $mybb->get_input('id', MyBB::INPUT_INT);
      if (empty($catid)) {
        flash_message($lang->application_ucp_err_delete, 'error');
        admin_redirect("index.php?module=config-application_ucp&action=application_ucp_managecats");
      }

      if (isset($mybb->input['no']) && $mybb->input['no']) {
        admin_redirect("index.php?module=config-application_ucp&action=application_ucp_managecats");
      }

      if (!verify_post_check($mybb->input['my_post_key'])) {
        flash_message($lang->invalid_post_verify_key2, 'error');
        admin_redirect("index.php?module=config-application_ucp&action=application_ucp_managecats");
      } else {
        if ($mybb->request_method == "post") {
          //TO DO - leeren in anderen tabellen wenn gelösch
          $db->delete_query("application_ucp_categories", "id='{$catid}'");

          $mybb->input['module'] = "application-ucp";
          $mybb->input['action'] = $lang->application_ucp_delete;
          log_admin_action(htmlspecialchars_uni($catid));
          flash_message($lang->application_ucp_managecats_delete_ask, 'success');
          admin_redirect("module=config-application_ucp&action=application_ucp_managecats");
        } else {
          $page->output_confirm_action(
            "index.php?module=config-application_ucp&action=application_ucp_cat_delete&amp;id={$catid}",
            $lang->application_ucp_managecats_delete_ask
          );
        }
      }
    }
  }
}

/**
 * Hilfsfunktion um das Submenü im ACP zu erstellen.
 */
function application_ucp_do_submenu()
{
  global $lang;
  //Übersicht
  $sub_tabs['application_ucp'] = [
    "title" => $lang->application_ucp_overview,
    "link" => "index.php?module=config-application_ucp",
    "description" => $lang->application_ucp_overview_appl
  ];

  //Steckbrieffelder anlegen
  $sub_tabs['application_ucp_add'] = [
    "title" => $lang->application_ucp_createfieldtype,
    "link" => "index.php?module=config-application_ucp&amp;action=application_ucp_add",
    "description" => $lang->application_ucp_createfieldtype_dscr
  ];

  //Steckbriefe verwalten
  $sub_tabs['application_ucp_manageusers'] = [
    "title" => $lang->application_ucp_manageusers,
    "link" => "index.php?module=config-application_ucp&amp;action=application_ucp_manageusers",
    "description" => $lang->application_ucp_manageusers_dscr
  ];

  //Steckbriefe verwalten
  $sub_tabs['application_ucp_managecats'] = [
    "title" => $lang->application_ucp_managecats,
    "link" => "index.php?module=config-application_ucp&amp;action=application_ucp_managecats",
    "description" => $lang->application_ucp_managecats_dscr
  ];

  return $sub_tabs;
}

/**
 * Verwaltung im UCP 
 * Anzeige der Felder / Speichern der Inhalte / Zur Korrektureinreichen
 */
$plugins->add_hook("usercp_start", "application_ucp_usercp");
function application_ucp_usercp()
{
  global $mybb, $db, $templates, $cache, $lang, $templates, $themes, $headerinclude, $header, $footer, $usercpnav, $application_ucp_ucp_main, $fields;

  if ($mybb->input['action'] != "application_ucp") {
    return false;
  }
  $lang->load('application_ucp');
  $savebtn = "";
  $extend_button = $fristdate = $application_ucp_correction_status = $fields =  $regdate = $regdate_read = $adddays = $application_ucp_infos = "";
  $thisuser = $mybb->user['uid'];
  $adddays = $mybb->settings['application_ucp_applicationtime'];
  $ext = intval($mybb->settings['application_ucp_extend']);
  $regdate =  $mybb->user['regdate'];
  $frist = $mybb->settings['application_ucp_applicationtime'];
  //just in case, keine berechigungen für Gäste
  if ($mybb->user['uid'] == 0) {
    error_no_permission();
  }

  //Bewerbergruppe oder angenommenes Mitglied?
  $applicant =  $mybb->settings['application_ucp_applicants'];
  if (is_member($applicant, $thisuser)) {
    $member = false;
  } else {
    $member = true;
  }

  //Infos angezeigt
  if ($member == false) {
    //Anzeige Frist
    $extend_cnt = intval($db->fetch_field($db->simple_select("users", "aucp_extend", "uid = {$thisuser}"), "aucp_extend"));
    $regdate_read = date("d.m.Y", $regdate);
    //noch nicht verlängert. Fristdatum also ganz normal reg datum + erlaubte zeit#
    $savebtn = "<input type=\"submit\" class=\"button\" name=\"application_ucp_ready\" value=\"{$lang->application_ucp_readybtn}\" >";
    if ($extend_cnt == 0) {
      $add = $adddays;
      $fristdate = date("d.m.Y", strtotime("+{$add} day", $regdate));
      $extend_cnt = 0;
      $application_ucp_correction_status = "";
    }

    //Die Frist wurde schon einmal verlängert
    if ($extend_cnt > 0) {
      // ext = erlaubte frist zum verlängern * wie oft wurde verlängert
      $ext = $ext * $extend_cnt;
      //Normale frist + verlängerung
      $add = $adddays + $ext;
      //datum für frist berechnen
      $fristdate = date("d.m.Y", strtotime("+{$add} day", $regdate));
      $regdate = date("d.m.Y", $regdate);
      $application_ucp_correction_status = "";
    }

    $lang->application_ucp_infoheader = $lang->sprintf($lang->application_ucp_infoheader, $regdate_read, $extend_cnt, $fristdate);

    $korrektur = $db->simple_select("application_ucp_management", "*", "uid = {$thisuser} AND submission_time < modcorrection_time AND usercorrection_time < modcorrection_time");
    if ($db->num_rows($korrektur) > 0) {
      //Der Steckbrief wurde eingereicht.
      $application_ucp_correction_status =  $lang->application_ucp_correction;
    }

    //TODO Anzeige in Korrektur, wartet auf deine Korrektur
    eval("\$application_ucp_infos = \"" . $templates->get("application_ucp_infos") . "\";");
  }


  //UCP bauen
  // alle aktiven Felder holen
  $get_fields = $db->simple_select("application_ucp_fields", "*", "active = 1", array('order_by' => 'cat_id, sorting'));

  //start für javascript das wir brauchen
  $application_ucp_js = "<script> $(function() {
    //initial alle abhängigen verstecken
    $('.depends').hide();

    //alle checkboxen einmal durchgehen und schauen welche aktiviert sind.

    var checkboxen = $('input[type=checkbox]:checked');

    checkboxen.each(function() {
      var string = $(this).val();
      var str = string.replace(/[^A-Za-z0-9]+/g, '');
    
      $('.dep_value_'+str).each(function() {
        //checkbox ist aktiviert. - div box wrapper, label, hideinfo, etc. anzeigen
        $(this).show();
        $(this).children().show();
      });
    });

    $('select').each(function() {
      var selectName = $(this).attr('name');
      var selectedOptions = $(this).find('option:selected');
      
      // Durchlaufe jede ausgewählte Option des aktuellen Select-Elements
      selectedOptions.each(function() {
        var string = $(this).val();
        var str = string.replace(/[^A-Za-z0-9]+/g, '');
        $('.dep_value_'+str).each(function() {
          //checkbox ist aktiviert. - div box wrapper, label, hideinfo, etc. anzeigen
          $(this).show();
          $(this).children().show();
        });
      });
    });
    ";

  //Javascript und markup für Kategorien.
  $application_ucpcats_js = "";
  $cats_html = "";

  if ($mybb->settings['application_ucp_acp_cats']) {  //Kategorien bekommen

    $cats_html = "<ul class=\"cat_tabs\">";
    $cats_html_inner = "";
    //testen ob es Felder ohne Kategorie gibt
    $get_fields_nocat = $db->simple_select("application_ucp_fields", "*", "active = 1 AND (cat_id = '0' OR cat_id ='') ");
    if ($db->num_rows($get_fields_nocat) > 0) {
      $cats_html_inner .= "<li class=\"cat_tabs__tab0\" data-tab=\"con_cat0\">{$mybb->settings['application_ucp_acp_cat_defaultname']}</li>";
    }
    $get_cats = $db->simple_select("application_ucp_categories", "*");
    $catarray = array();

    while ($cat = $db->fetch_array($get_cats)) {

      if ($mybb->settings['application_ucp_acp_cats_tabs']) {
        $cat['name'] = $cat['name'];
      } else {
        $cat['name'] = "<a href=\"#con_cat{$cat['id']}\">{$cat['name']}</a>";
      }

      $cats_html_inner .= "<li class=\"cat_tabs__tab{$cat['id']}\" data-tab=\"con_cat{$cat['id']}\">{$cat['name']}</li>";
    }

    if ($mybb->settings['application_ucp_acp_cats_tabs']) {
      $application_ucpcats_js .= "<script> $(document).ready(function(){
      $('ul.cat_tabs li').on('click', function(){
        // get the data attribute
        var tab_id = $(this).attr('data-tab');
        // remove the default classes
        $('ul.cat_tabs li').removeClass('current');
        $('.con_cat_content').removeClass('current');
        // add new classes on mouse click
        $(this).addClass('current');
        $('#'+tab_id).addClass('current');
      });
    });</script>";
    } else {
      $application_ucpcats_js = "";
    }
    $cats_html .= $cats_html_inner . "</ul>";
  }


  $cat_start = $tabclass = $closedivcat = "";
  $cnt = "0";
  //felder durchgehen
  while ($type = $db->fetch_array($get_fields)) {
    //ist das Feld editierbar? -> wenn mitglied berücksichtigen
    if ($member &&  $type['editable'] == 0) {
      $readonly = "readonly"; //für textfelder/textarea
      $disabled = "disabled"; //selects / checkboxen etc.

      //moderators still can edit fields
      if ($mybb->usergroup['canmodcp'] == '1') {
        $readonly = ""; //für textfelder/textarea
        $disabled = ""; //selects / checkboxen etc.
      }
    } else { //ist Bewerber, darf alle Felder editieren
      $readonly = "";
      $disabled = "";
    }
    $catclass = "";
    //Kategorienamen
    if ($mybb->settings['application_ucp_acp_cats'] && $mybb->settings['application_ucp_acp_cats_tabs']) {
      //default für keine angegbene Kategorie
      if ($type['cat_id'] == "" || $type['cat_id'] == "0") {
        $type['cat_id'] = 0;
      }
      $catclass = " cat" . $type['cat_id'];
    }
    //gibt es schon inhalte für die felder? 
    $get_value = $db->fetch_array($db->simple_select("application_ucp_userfields", "*", "uid = '{$thisuser}' AND fieldid='{$type['id']}'"));
    if (empty($get_value['value'])) {
      $get_value['value'] = "";
    }

    //wenn nein, gibt es eine vorlage für das feld?
    if ($type['template'] != "") {
      if ($get_value['value'] == "") {
        //Es gibt eine Vorlage und der user hat das Feld noch nicht bearbeitet
        $get_value['value'] = $type['template'];
      } else {
        //Es gibt zwar eine Vorlage, aber der user hat schon bearbeitet, dann den wert laden, nicht die vorlage
        $get_value['value'] = $get_value['value'];
      }
    }
    //handelt es sich um ein Pflichtfeld
    $dep_classname = "";
    $dep_classname_wrap = "";
    $dep_classes = "";
    if ($type['mandatory']) {
      $requiredstar = "<span class\"app_ucp_star\">" . $lang->application_ucp_mandatory . "</span>"; //markierung mit sternchen ux und so :D    

      $required = "";
      // $required = "required"; //feld muss ausgefüllt werden
    } else { //kein pflichtfeld
      $requiredstar = "";
      $required = "";
    }
    //prüfen ob Feld initial versteckt sein soll -> wenn es von einem anderen abhängig ist
    if ($type['dependency'] != "none") {
      //get name of inout (id of field)
      if ($type['fieldtyp'] == 'select_multiple' || $type['fieldtyp'] == 'checkbox') {
        $inputname = $type['id'] . "[]";
      } else {
        $inputname = $type['id'];
      }
      //Klassen zusammen bauen für mehrere abhängigekeiten
      $dep_val_arr = explode(",", $type['dependency_value']);

      foreach ($dep_val_arr as $dep) {
        $dep_classes .= " dep_value_" . preg_replace('/[^A-Za-z0-9\_]/', '', $dep);
      }

      $dep_classname = "has_dep dep_" . $type['dependency'];
      $dep_classname_wrap = "depends wrap_dep_" . preg_replace('/[^A-Za-z0-9\_]/', '', $type['dependency']) . $dep_classes;
      $hide = true;

      //javascript dynamisch zusammen bauen.
      //wenn dependency, von welchem feld und welchem wert? Entsprechend element ein oder ausblenden.
      $application_ucp_js .= "
      //checkbox wird aktiviert oder deaktiviert

      $('.{$type['dependency']}_check').off('change').on('change', function(){

          //Hier testen wir, ob ein Feld versteckt / gezeigt werden muss, wenn eine Checkbox/Select aktiviert wird
         
          //Es handelt sich um ein SELECT Feld
          if(this.nodeName == 'SELECT') {
            //alle options bekommen
            var selectedOptions = $(this).find('option');
            //diese durchgehen
            selectedOptions.each(function() {
              //wert bekommen
              var string = $(this).val();
              // ersetzen
              var str = string.replace(/[^A-Za-z0-9]+/g, '');

              //wird es ausgewählt
              if($(this).prop('selected')) {              
                $('.wrap_dep_{$type['dependency']}.dep_value_'+str).each(function() {
                  //checkbox ist aktiviert. - div box wrapper, label, hideinfo, etc. anzeigen
                  $(this).show();
                  $(this).children().show();
                });
              } else { // oder wieder abgewählt
                $('.wrap_dep_{$type['dependency']}.dep_value_'+str).each(function() {
                  $(this).hide();
                  $(this).children().hide();
                });
              }
            });
          }  else if(this.nodeName == 'INPUT') {
            //kein select sondern Radio oder Checkbox

              //wert bekommen
              var string = $(this).val();
              // ersetzen
              var str = string.replace(/[^A-Za-z0-9]+/g, '');
              //wird es ausgewählt
              if($(this).is(':checked')){
                //dann abhängige einblenden
                $('.wrap_dep_{$type['dependency']}.dep_value_'+str).each(function() {
                //checkbox ist aktiviert. - div box wrapper, label, hideinfo, etc. anzeigen
                $(this).show();
                $(this).children().show();
                });
              } else { //oder abgewählt - dann wieder verstecken
                $('.wrap_dep_{$type['dependency']}.dep_value_'+str).each(function() {
                $(this).hide();
                $(this).children().hide();
                });
              }
          }    
        });   
      ";
    } else { //keine abhängigkeit
      $hidden = "";
      $hide = false;
    }
    //was für einen feldtyp haben wir
    $typ = $type['fieldtyp'];
    if ($hide == true) {
      // $hidden = "display: none;"
    } else {
      $hidden = "";
    }
    if ($mybb->settings['application_ucp_acp_cats']) {
      if ($mybb->settings['application_ucp_acp_cats_tabs']) {
        $tabclass = "con_cat_content";
      } else {
        $tabclass = "";
      }
      if ($cat_start != $type['cat_id']) {
        if ($cnt == 0) {
          $openinital = " current";
          $closedivcat = "";
        } else {
          $openinital = "";
          $closedivcat = "</div>";
        }
        $cnt++;
        $cat_name = $db->fetch_field($db->simple_select("application_ucp_categories", "name", "id = '{$type['cat_id']}'"), "name");
        if ($cat_name == "") $cat_name = $mybb->settings['application_ucp_acp_cat_defaultname'];
        $catdivstart = "{$closedivcat}<div class=\"{$tabclass}{$openinital}\" id=\"con_cat{$type['cat_id']}\"><h2>{$cat_name}</h2>";
        $cat_start = $type['cat_id'];
      } else {
        $catdivstart = "";
      }
    }
    $fields .= "$catdivstart<div class=\"applucp-con__item {$dep_classname_wrap}{$catclass}\" id=\"container_{$type['fieldname']}\" style=\"{$hidden}\">";
    //Felder bauen
    //Das Feld ist initial versteckt, das brauchen wir um vorm speichern zu prüfen ob der inhalt gespeichert werden soll
    if ($hide == true) {
      $fields .= "<input type=\"hidden\" id=\"hideinfo_{$type['fieldname']}\" name=\"hideinfo_{$type['id']}\" value=\"false\" />";
    }
    //Beschreibung falls vorhanden
    if ($type['fielddescr'] != "") {
      $fielddescr = "<span class=\"descr_{$type['fieldname']}\" id=\"descr_{$type['fieldname']}\">{$type['fielddescr']}</span>";
    } else {
      $fielddescr = "";
    }

    //Feld ist einfaches Textfeld, Datum oder Datum mit Zeit
    if ($typ == "text" || $typ == "date" || $typ == "datetime-local" || $typ == "url") {
      $fields .= "<label  class=\"app_ucp_label\" for=\"{$type['fieldname']}\" style=\"{$hidden}\" id=\"label_{$type['fieldname']}\">{$type['label']}{$requiredstar}:</label> 
      " . $fielddescr . "
      <input type=\"{$typ}\" class=\"{$type['fieldname']} $dep_classname \" value=\"{$get_value['value']}\" name=\"{$type['id']}\" id=\"{$type['fieldname']}\" style=\"{$hidden}\" {$required} {$readonly}/>
      ";
    }
    //Feld ist Textarea
    else if ($typ == "textarea") {
      $fields .= "<label for=\"{$type['fieldname']}\" class=\"app_ucp_label\" style=\"{$hidden}\" id=\"label_{$type['fieldname']}\">{$type['label']}{$requiredstar}:</label>
      " . $fielddescr . "
      <textarea class=\"{$type['fieldname']}\" name=\"{$type['id']}\"  id=\"{$type['fieldname']} {$dep_classname}\" rows=\"4\" cols=\"50\" style=\"{$hidden}\" {$readonly} {$required} >{$get_value['value']}</textarea>";
    }
    //Feld ist Select
    else if ($typ == "select" || $typ == "select_multiple") {
      //auswählbare Optionen holen und in array speichern
      $options = explode(",", $type['options']);
      $selects = "";

      //Mehrfachauswahl? 
      if ($typ == "select_multiple") {
        $getselects = explode(",", $get_value['value']);
        $multiple = "multiple";
        $mult_flag = true;
      } else {
        $multiple = "";
        $getselects = $get_value['value'];

        $mult_flag = false;
      }
      //array mit optionen durchgehen und auswahl bauen
      foreach ($options as $option) {
        //leertasten rauswerfen
        $option = trim($option);

        if ($mult_flag) {
          //vorauswahl von schon ausgefüllten werten
          if (in_array($option, $getselects)) {
            $selected = "selected=\"selected\"";
          } else {
            $selected = "";
          }
        } else {
          if (trim($option) == trim($getselects)) {
            $selected = "selected=\"selected\"";
          } else {
            $selected = "";
          }
        }
        $selects .= "<option value=\"{$option}\" {$selected} >{$option}</option>";
      }

      //hier bauen wir das feld und packen die optionen rein
      $fields .= " <label class=\"app_ucp_label {$type['fieldname']}\" for=\"{$type['fieldname']}\"  style=\"{$hidden}\" id=\"label_{$type['fieldname']}\">
      {$type['label']}{$requiredstar}:</label>
      " . $fielddescr . "
      <select name=\"{$type['id']}[]\" class=\"{$type['fieldname']}_check  {$dep_classname}\" id=\"{$type['fieldname']}\" style=\"{$hidden}\"  {$multiple} {$required} {$disabled}>
      {$selects} 
      </select>";
      // Variable leeren
      $selects = "";
    }
    //Feld ist Checkbox oder Radio
    else if ($typ == "checkbox" || $typ == "radio") {

      $inner = "";
      $options = explode(",", $type['options']);

      $getval = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "fieldid = '{$type['id']}' AND uid = '{$thisuser}'"), "value");

      //die checkboxes basteln - erst einmal die einzelnen pro option
      foreach ($options as $option) {
        //Wurde das Feld schon einmal ausgefüllt -> gespeicherten Wert suchen und Checkbox vorauswählen
        $option = trim($option); //leertasten rauswerfen
        $pos = strpos($getval, $option); //testen 
        if ($pos === false) {
          $checked = ""; //enthält die option nicht
        } else {
          $checked = "checked"; //enthält die option, also soll sie schon ausgewählt sein.
        }
        //und alles zusammenbasteln
        $inner .= "
        <input type=\"{$typ}\" class=\"{$type['fieldname']}_check {$dep_classname} \" id=\"{$type['fieldname']}\" name=\"{$type['id']}[]\" value=\"{$option}\" {$checked} {$required} {$disabled} > 
        <label for=\"{$type['fieldname']}\">{$option}</label><br/>";
      }
      //auswahl löschen hinzufügen, damit man das feld auch wieder leeren kann
      $inner .= "
      <input type=\"{$typ}\" class=\"{$type['fieldname']}_check\" id=\"{$type['fieldname']}\" name=\"{$type['id']}[]\" value=\"deleteinput\" > 
      <label for=\"{$type['fieldname']}\">Auswahl löschen</label><br/>";

      // dann hier das außenrum
      $fields .= "
      <label class=\"app_ucp_label {$type['fieldname']}\" style=\"{$hidden}\" id=\"label_{$type['fieldname']}\">
      {$type['label']}{$requiredstar}:</label>
      " . $fielddescr . "
      <div class=\"application_ucp_checkboxes\"  style=\"{$hidden}\" id=\"{$type['fieldname']}\">
        {$inner}
      </div>
      ";
      // Variable leeren
      $inner = "";
      // Das Javascript das wir benötigen, wenn mindestens eine Checkbox ausgewählt sein muss.  (also wenn es Pflichtfeld ist). Erst einmal sind alle boxen auf Required gestellt, wird eine box ausgewählt,
      // schmeißt das JS hier die required wieder raus, so dass man speichern kann :) 
      if ($required != "" && $typ == "checkbox") {
        $application_ucp_js .= "
        var checkboxes = $('." . $type['fieldname'] . "_check');
        if($('." . $type['fieldname'] . "_check:checked').length > 0) {
          checkboxes.removeAttr('required');
        } else {
          checkboxes.attr('required', 'required');
        }
        checkboxes.change(function(){
        if($('." . $type['fieldname'] . "_check:checked').length > 0) {
          checkboxes.removeAttr('required');
        } else {
          checkboxes.attr('required', 'required');
        }
        });
        ";
      }

      //checkbox wurde mal ausgewählt und gespeichert, jetzt soll es möglich sein auch wieder leere felder zu speichern
      //dafür etwas javascript
      if ($typ == "checkbox") {
        $application_ucp_js .= "        
        $('.{$type['fieldname']}_check').on('change', function() {
          if($(this).val() == 'deleteinput' && this.checked) {
     
          $('.{$type['fieldname']}_check').not(this).prop('checked', false);
          $('.{$type['fieldname']}_check').not(this).prop('disabled', true); 
     
          }
          if($(this).val() == 'deleteinput' && this.checked === false) { 
            $('.{$type['fieldname']}_check').not(this).prop('disabled', false); 
            $('.{$type['fieldname']}_check').not(this).removeAttr('disabled'); 
          }
      }); 
      ";
      }
    }
    $fields .= "</div>";
  }

  //ende Javascript
  $application_ucp_js .= "});</script>";

  //admin einstellungen - Felder für Steckbrief thread
  $setting_trigger = $mybb->settings['application_ucp_trigger'];
  $setting_wanted = $mybb->settings['application_ucp_stecki_wanted'];
  $setting_affected = $mybb->settings['application_ucp_stecki_affected'];
  //Es soll eine Triggerwarnung geben können
  if ($setting_trigger) {
    $trigger = "";
    $trigger = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "uid = '{$mybb->user['uid']}' AND fieldid = '-4'"), "value");
    $fields .= "
    <label class=\"app_ucp_label trigger\"  id=\"label_trigger\">
   {$lang->application_ucp_trigger}</label>
    <div class=\"application_ucp_checkboxes\"  id=\"box_trigger\">
      <input type=\"text\" class=\"input_trigger\" placeholder=\"{$lang->application_ucp_trigger}\" id=\"trigger\" name=\"-4\" value=\"{$trigger}\" \>
    </div>
    ";
  }

  //Es soll ausgewählt werden können, ob es sich um ein Gesuch handelt
  if ($setting_wanted) {
    $checked_yes = $checked_no = $inner = $wantedurl = $affected_data = "";
    //Die Angabe ist Pflicht
    $requiredstar = "<span class=\"app_ucp_star\">" . $lang->application_ucp_mandatory . "</span>";
    //testen ob schon einmal ausgefüllt und entsprechend die Checkbox vorauswählen oder nicht
    $get_checked = $db->simple_select("application_ucp_userfields", "*", "uid = '{$mybb->user['uid']}' AND fieldid = '-1'");
    $get_checked_row = $db->num_rows($get_checked);
    $get_checked_data = $db->fetch_array($get_checked);
    if ($get_checked_row > 0) {
      if ($get_checked_data['value'] == 1) {
        $checked_yes = "CHECKED ";
        $checked_no = "";
      } else {
        $checked_no = "CHECKED ";
        $checked_yes = "";
      }
    } else {
      $checked = "";
    }
    //Daten für URL
    $get_url = $db->simple_select("application_ucp_userfields", "*", "uid = '{$mybb->user['uid']}' AND fieldid = '-2'");
    if ($db->num_rows($get_url) > 0) {
      $get_url_data = $db->fetch_array($get_url);
      $wantedurl = $get_url_data['value'];
    }


    //Die Checkboxen für wanted
    $inner .= "
        <input type=\"radio\" class=\"wanted_check\" id=\"wanted\" name=\"-1\" value=\"1\" {$checked_yes}\> 
        <label for=\"wanted\">Ja</label><br/>
        <input type=\"radio\" class=\"wanted_check\" id=\"wanted\" name=\"-1\" value=\"0\" {$checked_no} required \> 
        <label for=\"wanted\">Nein</label><br/>";
    //dann hier das außenrum
    $fields .= "
      <label class=\"app_ucp_label wanted\"  id=\"label_wanted\">
     {$lang->application_ucp_wanted}{$requiredstar}</label>
      <div class=\"application_ucp_checkboxes\"  id=\"boxwanted\">
        {$inner}
        <input type=\"url\" class=\"wanted_url\" placeholder=\"{$lang->application_ucp_wanted_url}\" id=\"wanted_url\" name=\"-2\" value=\"{$wantedurl}\" \>
      </div>
      ";
  }
  if ($setting_affected) {
    //Wenn eingetragen werden soll ob andere Mitglieder betroffen sind
    $requiredstar = "";
    //input basteln
    //Daten für affected
    $get_affected = $db->simple_select("application_ucp_userfields", "*", "uid = '{$mybb->user['uid']}' AND fieldid = '-3'");
    if ($db->num_rows($get_affected) > 0) {
      $get_affected_data = $db->fetch_array($get_affected);
      $affected_data = $get_affected_data['value'];
    }
    $fields .= " 
    <label class=\"app_ucp_label\" for=\"affected\" id=\"label_affected\">{$lang->application_ucp_affected_ucp}{$requiredstar}:</label> 
    <input type=\"text\" autocomplete=\"off\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\"
          class=\"select2-input select2-default\" id=\"s2id_autogen1\" tabindex=\"1\" placeholder=\"\" name=\"-3\" value=\"{$affected_data}\">";

    //Javascript für Autocomplete von Usernamen
    $fields .= "<link rel=\"stylesheet\" href=\"{$mybb->asset_url}/jscripts/select2/select2.css?ver=1807\">
          <script type=\"text/javascript\" src=\"{$mybb->asset_url}/jscripts/select2/select2.min.js?ver=1806\"></script>
          <script type=\"text/javascript\">
          <!--
          if(use_xmlhttprequest == \"1\")
          {
              MyBB.select2();
              $(\"#s2id_autogen1\").select2({
                  placeholder: \"{$lang->search_user}\",
                  minimumInputLength: 2,
                  maximumSelectionSize: \"\",
                  multiple: true,
                  ajax: { // instead of writing the function to execute the request we use Select2s convenient helper
                      url: \"xmlhttp.php?action=get_users\",
                      dataType: \"json\",
                      data: function (term, page) {
                          return {
                              query: term, // search term
                          };
                      },
                      results: function (data, page) { // parse the results into the format expected by Select2.
                          // since we are using custom formatting functions we do not need to alter remote JSON data
                          return {results: data};
                      }
                  },
                  initSelection: function(element, callback) {
                      var query = $(element).val();
                      if (query !== \"\") {
                          var newqueries = [];
                          exp_queries = query.split(\",\");
                          $.each(exp_queries, function(index, value ){
                              if(value.replace(/s/g, \"\") != \"\")
                              {
                                  var newquery = {
                                      id: value.replace(/,s?/g, \",\"),
                                      text: value.replace(/,s?/g, \",\")
                                  };
                                  newqueries.push(newquery);
                              }
                          });
                          callback(newqueries);
                      }
                  }
              });
          }
          // -->
          </script> 
        ";
  }

  //extend button
  if ($mybb->settings['application_ucp_extend'] > 0) {
    //wurde schon häufiger als erlaubt verlängert?
    $extend_cnt = $db->fetch_field($db->simple_select("users", "aucp_extend", "uid = {$mybb->user['uid']}"), "aucp_extend");
    if ($extend_cnt < $mybb->settings['application_ucp_extend_cnt']) {
      if ($member == false) {
        $extend_button = "<input type=\"submit\" class=\"button\" name=\"application_ucp_extend\" value=\"" . $lang->application_ucp_extbtn . "\"/>";
      } else {
        $extend_button = "";
      }
    }
  }

  //Steckbrief speichern, aber nicht abgeben
  if ($mybb->get_input('application_ucp_save')) {
    //Hier speichern wir, was eingetragen wurde
    //wir bekommen ein array mit allen werten
    $fields = $mybb->input;
    //Hilfsunktion - wir übergeben den input und handeln da alles, weil wir das gleiche so oft machen müssen
    application_ucp_savefields($fields, $mybb->user['uid']);
    redirect("usercp.php?action=application_ucp");
  }

  //Steckbrief speichern und zur Korrektur geben.

  if ($mybb->get_input('application_ucp_ready')) {
    // alle Inputs
    $fields_numerickey = array();
    // //einmal fürs überprüfen
    // $fields = $mybb->input;
    //einmal fürs später speichern, evt. doppelt gemoppelt aber well.
    $fields = $mybb->input;
    //wir wollen nur unsere fields in dem array
    foreach ($fields as $key => $value) {
      if (is_numeric($key)) {
        $fields_numerickey[$key] = $value;
      }
    }
    //wir speichern hier den alten wert (Alte betroffene User suchen), um ihn später zu vergleichen
    $old_affected = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "uid = '{$mybb->user['uid']}' AND fieldid='-3'"), "value");

    //dann speichern wir alle felder, damit nichts verloren geht
    application_ucp_savefields($fields_numerickey, $mybb->user['uid']);


    //Einreichen abbrechen, wenn nicht alle Pflichtfelder ausgefüllt sind.
    //alle inputs durchgehen
    foreach ($fields_numerickey as $key => $value) {
      //array mit werten des Felds
      $field = $db->fetch_array($db->simple_select("application_ucp_fields", "*", "id = {$key}"));
      //wir müssen auch schauen, ob es eine abhängigkeit gibt. wenn ja dann nur pflicht berücksichtigen, wenn das entsprechende ausgewählt ist 
      //ist das Feld ein Pflichtfeld
      $ismandatory = 0;
      $ismandatory =  $field['mandatory'];
      // $ismandatory = $db->fetch_field($db->write_query("SELECT mandatory FROM " . TABLE_PREFIX . "application_ucp_fields WHERE id = '" . $key . "'"), "mandatory");
      if ($field['dependency'] != "") {
        //pflicht zurücksetzen, weil wir sie hier neu bewerten müssen
        $ismandatory = 0;
        //es gibt eine Abhängigkeit
        //Von welchem Feld ist es abhängig? 
        $fielddep = $db->fetch_array($db->simple_select("application_ucp_fields", "*", "fieldname = '{$field['dependency']}'"));
        //ID bekommen um aktuellen input des Felds zu bekommen
        $fielddep_id = $fielddep['id'];
        //input kriegen
        $fielddep_value = $fields_numerickey[$fielddep_id];
        // Wenn es ein Array ist (select, multiselect) müssen wir es anders behandeln
        if (is_array($fielddep_value)) {
          //schauen ob der wert einmal gleich ist, dann muss pflicht
          foreach ($fielddep_value as $val) {
            if ($val == $field['dependency_value']) {

              $ismandatory = 1;
            }
          }
        } else {
          //ist der wert von dem es abhängig sein soll = dem wert der im abhängigen ausgewählt wurde
          if ($fielddep_value === $field['dependency']) {
            // ist gleich -> Pflicht ja
            $ismandatory = 1;
          } else {
            // pflicht nein
            $ismandatory = 0;
          }
        }

        //ABHÄNGIGKEIT von Wert bla

        //female -> zeige bla
        //divers -> zeige bla 
        //male -> zeuge bla nicht, bla kann leer sein obwohl pflicht

        //hole die Abhänigkeiten des felds
        // female
        // wenn input != female  dann ist die pflicht egal

        //hole id des felds
        // $fieldep = $db->fetch_array($db->simple_select("application_ucp_fields", "*", "fieldname = '{$field['dependency']}'"));
        // //ist
        // $values = explode(",", $field['dependency_value']);

        //ist der value (der evt. leer ist = dem value der abhängigkeit)
        // foreach ($values as $val) {
        //   $val = $db->escape_string(trim($val));
        //   echo ("valist.".$val."fielddep.". $key.$value);
        //   $numrow = $db->num_rows($db->simple_select("application_ucp_userfields", "*", "fieldid = '{$fieldep['id']}' and value = '$val' and uid='{$mybb->user['uid']}'"));
        //   if ($numrow > 0) $ismandatory = 1;

        // } die();
      }
      //pflichtfeld, aber nicht ausgefüllt.
      if ($ismandatory && empty($value)) {
        // wir vergleichen kurz alte affected user und neue, wenn neue setzen wir den wert wieder auf die alten
        // dadurch muss zwar neu eingetragen werden, aber so wird sichergestellt, dass die neu eingetragenen user, 
        // noch informiert werden, wenn erneut losgeschickt wird

        if ($fields_numerickey['-3'] != $old_affected) {
          $fields_numerickey['-3'] = $old_affected;
          application_ucp_save_single_field($fields_numerickey, '-3', $mybb->user['uid']);
        }
        echo "<script>alert('" . $lang->application_ucp_saveerror . "')
        window.location = './usercp.php?action=application_ucp';</script>";
      }
    }


    //Inputs waren in Ordnung - alle Pflichfelder ausgefüllt
    //Schauen ob es schon einen eintrag im managenent gibt
    $fetch_management = $db->simple_select("application_ucp_management", "*", "uid = {$mybb->user['uid']}");

    //wenn ja, ist es die Verbesserung nach einer Korrektur
    if ($db->num_rows($fetch_management) > 0) {
      //die daten
      $managmentdata = $db->fetch_array($fetch_management);
      //vergleich zu alten werten
      //wurde was am feld geändert?
      if ($fields_numerickey['-3'] != $old_affected) {
        //es wurden neue Betroffene hinzugefügt und müssen noch informiert werden
        //wir brauchen arrays
        $array_new = explode(",", $fields['-3']);
        $old_field = explode(",", $old_affected);
        foreach ($array_new as $user) {
          //user war nicht im alten, also muss er informiert werden
          if (!in_array($user, $old_field)) {
            $touid = get_user_by_username($user);
            application_ucp_affected_alert($managmentdata['uid'], $touid, $managmentdata['tid'], 1);
          }
        }
      }

      // Wenn die Korrekturzeit vom Mod kleiner ist, als heute ist es eine Korrektur des Users

      if (strtotime($managmentdata['modcorrection_time']) <= time()) {
        $add = $db->fetch_field($db->simple_select("application_ucp_management", "correctioncnt", "uid = {$mybb->user['uid']}"), "correctioncnt");
        $add++;
        $update = array(
          "usercorrection_time" => date('Y-m-d H:i:s'),
          "correctioncnt" => $add,
        );
        //speichern
        $db->update_query("application_ucp_management", $update, "uid = {$mybb->user['uid']}");
      }
      echo "<script>
      alert('Gerne kannst du noch im Thread Bescheid geben und etwas zu deiner Korrektur sagen.');
      window.location = './newreply.php?tid={$managmentdata['tid']}';</script>";

      // echo "<script>alert('Gerne kannst du noch im Thread Bescheid geben und etwas zu deiner Korrektur sagen.');</script>";

      // redirect("newreply.php?tid={$managmentdata['tid']}");
    } else { //Der Steckbrief wird das erste Mal eingereicht

      //Wir wollen einen Thread erstellen, wenn der Stecki fertig ist und nutzen dafür den Posthandler von MyBB
      //Steckbriefarea holen
      $steckbriefarea = $mybb->settings['application_ucp_steckiarea'];

      //Nachricht zusammenbauen
      $trigger_div = "";
      //Gibt es eine Trigger Warnung für den Steckbrief? 
      //Wir schauen erst noch, ob angegeben wurde, ob der Charakter ein Gesuch ist. 
      $get_trigger = $db->simple_select("application_ucp_userfields", "value", "uid = {$mybb->user['uid']} AND fieldid = -4");

      $get_trigger_row = $db->num_rows($get_trigger);
      if ($get_trigger_row && $mybb->settings['application_ucp_trigger']) {
        //Trigger Div bauen
        $trigger = $db->fetch_field($get_trigger, "value");
        $trigger_div = "<div class=\"aucp_trigger--thread\"><strong>{$lang->application_ucp_thread_trigger}</strong> {$trigger}</div>";
      }

      //Wir schauen erst noch, ob angegeben wurde, ob der Charakter ein Gesuch ist. 
      $get_wanted = $db->simple_select("application_ucp_userfields", "*", "uid = {$mybb->user['uid']} AND fieldid = -1 AND value = '1'");
      $get_wanted_row = $db->num_rows($get_wanted);
      if ($get_wanted_row) {
        //Daten für URL des Gesuchs
        $get_url_data = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "uid = {$mybb->user['uid']} AND fieldid = -2"), "value");
        $wanted = "<a href=\"" . $get_url_data . "\">{$lang->application_ucp_thread_wantedurltitle}</a>";
      } else {
        $wanted = $lang->application_ucp_thread_nowanted;
      }
      $get_affected_names = "";
      //Gibt es betroffene User?
      $get_affected = $db->simple_select("application_ucp_userfields", "*", "uid = '{$mybb->user['uid']}' AND fieldid = '-3' AND value != ''");
      $get_affected_row = $db->num_rows($get_affected);
      if ($get_affected_row > 0) {
        // Welche Mitglieder sind betroffen?
        $get_affected_names = explode(",", $db->fetch_field($get_affected, "value"));
        $affectedusers = "";
        //Zu den betroffenen den Link bauen
        foreach ($get_affected_names as $name) {
          //Mention me oder nicht? 
          if ($mybb->settings['application_ucp_stecki_affected_alert'] == 2) {
            $affectedusers .= " @\"{$name}\", ";
          } else {
            $affectedusers .= "{$name}, ";
          }
        }
        // das letzte Komma und leertase entfernen
        $affectedusers = (substr($affectedusers, 0, -2));
        $affected = "<strong>" . $lang->application_ucp_affected_label . "</strong> {$affectedusers}";
      } else {
        $affected = $lang->application_ucp_noaffected;
      }
      $threadmessage = $trigger_div;

      $threadmessage = $mybb->settings['application_ucp_stecki_message'];
      //Die admin cp message holen und die variable $wanted ersetzen
      $threadmessage = $threadmessage ? str_replace("\$wanted", $wanted, $threadmessage) : "";

      //Die Variable affected ersetzen
      $threadmessage =  $threadmessage ? str_replace("\$affected", $affected, $threadmessage) : "";
      // $threadmessage = str_replace("\$affected", $affected, $threadmessage);

      //Den usernamen ersetzen
      $threadmessage = str_replace("\$username", build_profile_link($mybb->user['username'], $mybb->user['uid']), $threadmessage);
      //das Avatar ersetzen 
      $threadmessage = str_replace("\$avatar", "<img src=\"{$mybb->user['avatar']}\">", $threadmessage);

      //blurred lines kram mit abfangen für den fall, dass ich es vergesse vorm upload ins gitlab rauszunehmen :D
      //kann gerne als beispiel für eigenen ergänzungen genommen werden. Wir checken hier ob bestimmte Dinge eingetragen/ausgefüllt wurden
      // $firststeps_check = "";
      // //jobliste - abfangen ob es die Tabelle gibt oder nicht
      // if ($db->table_exists("jl_entry")) {
      //   //gibt es einen Eintrag
      //   $fetch_job = $db->simple_select("jl_entry", "*", "je_uid= {$mybb->user['uid']}");
      //   if ($db->num_rows($fetch_job) > 0) {
      //     //wenn ja mit Häkchen in den Thread schreiben
      //     $firststeps_check .= "<li><i class=\"fa-solid fa-check\"></i> In Jobliste eingetragen</li>";
      //   } else {
      //     //wenn nein mit Kreuz
      //     $firststeps_check .= "<li><i class=\"fa-solid fa-xmark\"></i> Nicht in Jobliste eingetragen</li>";
      //   }
      // }
      // //wohnort
      // if ($db->table_exists("residences_user")) {
      //   $fetch_job = $db->simple_select("residences_user", "*", "uid= {$mybb->user['uid']}");
      //   if ($db->num_rows($fetch_job) > 0) {
      //     $firststeps_check .= "<li><i class=\"fa-solid fa-check\"></i> In Residences eingetragen</li>";
      //   } else {
      //     $firststeps_check .= "<li><i class=\"fa-solid fa-xmark\"></i> Nicht in Residences eingetragen</li>";
      //   }
      // }
      // //relas
      // //wohnort
      // if ($db->table_exists("relas_entries")) {
      //   $fetch_job = $db->simple_select("relas_entries", "*", "r_from = {$mybb->user['uid']}");
      //   if ($db->num_rows($fetch_job) > 0) {
      //     $firststeps_check .= "<li><i class=\"fa-solid fa-check\"></i> Es sind Relations eingetragen</li>";
      //   } else {
      //     $firststeps_check .= "<li><i class=\"fa-solid fa-xmark\"></i> Keine Relations eingetragen</li>";
      //   }
      // }
      // //Wir holen uns die Avaperson, weil wir die Info direkt im Thread sehen wollen (id für avatarperson bei uns = 20 )
      // $fetch_ava = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "uid= {$mybb->user['uid']} AND fieldid = '20'"), "value");
      // if ($fetch_ava != "") {
      //   $firststeps_check .= "<li><strong>Avatarperson:</strong> {$fetch_ava}</li>";
      // } else {
      //   $firststeps_check .= "<li><i class=\"fa-solid fa-xmark\"></i> Keine Avaperson eingetragen</li>";
      // }
      // //und wir holen uns noch den Spielernamen der bei uns im Profilfeld mit ID 4 gespeichert ist 
      // $firststeps_check .= "<li>gespielt von {$mybb->user['fid4']}</li>";

      // //das ganze wird in der Variable $firststeps_check gespeichert, schreiben wir diese nun ins ACP, wird in der Message der Inhalt eingefügt
      // $threadmessage = str_replace("\$firststeps_check", $firststeps_check, $threadmessage);

      //Wenn ihr den ganzen Steckbrief im Thread haben wollt, könnt ihr euch, die zwei Zeilen hier einfach einkommentieren
      // Im ACP dann einfach $aucp_fields einfügen.
      $aucp_fields = application_ucp_build_view($mybb->user['uid'], "profile", "html");
      // $threadmessage = str_replace("\$aucp_fields", $aucp_fields, $threadmessage);

      // Kopiert aus newthread.php
      // Set up posthandler. (Wir nutzen hier einfach komplett die funktion aus newthread.php)
      // Post key
      verify_post_check($mybb->get_input('my_post_key'));
      require_once "./global.php";
      require_once MYBB_ROOT . "inc/datahandlers/post.php";
      $posthandler = new PostDataHandler("insert");
      $posthandler->action = "thread";

      // Set the thread data that came from the input to the $thread array.
      $new_thread = array(
        "fid" => $steckbriefarea,
        "subject" => $mybb->user['username'],
        "prefix" => "",
        "icon" => "",
        "uid" => $mybb->user['uid'],
        "username" => $mybb->user['username'],
        "message" => $threadmessage,
        "ipaddress" => $session->packedip,
        "posthash" => $mybb->get_input('posthash')
      );

      if ($pid != '') {
        $new_thread['pid'] = $pid;
      }

      $new_thread['savedraft'] = 0;
      // $new_thread['tid'] = $thread['tid'];


      // Set up the thread options from the input.
      $new_thread['options'] = array(
        "signature" => 0,
        "subscriptionmethod" => 0,
        "disablesmilies" => 0
      );

      // Apply moderation options if we have them
      $new_thread['modoptions'] = $mybb->get_input('modoptions', MyBB::INPUT_ARRAY);
      // $new_thread['modoptions'] = "";
      $posthandler->set_data($new_thread);
      // Now let the post handler do all the hard work.
      $valid_thread = $posthandler->validate_thread();

      $post_errors = array();
      // Fetch friendly error messages if this is an invalid thread
      if (!$valid_thread) {
        $post_errors = $posthandler->get_friendly_errors();
      }
      $thread_errors = inline_error($post_errors);

      // One or more errors returned, fetch error list and throw to newthread page
      if (count($post_errors) > 0) {
        $thread_errors = inline_error($post_errors);
        $mybb->input['action'] = "newthread";
      }

      $thread_info = $posthandler->insert_thread();
      $tid = $thread_info['tid'];
      // Mark thread as read
      require_once MYBB_ROOT . "inc/functions_indicators.php";
      mark_thread_read($tid, $steckbriefarea);
      //Bis hier (abschnittsweise) kopiert aus new thread kopiert

      //user informieren
      if ($get_affected_names != "") {
        foreach ($get_affected_names as $name) {
          // Daten des Users bekommen
          $user = get_user_by_username($name);
          //betroffene user informieren
          application_ucp_affected_alert($mybb->user['uid'], $user['uid'], $tid, 0);
        }
      }

      //und jetzt noch einen eintrag in der Management Tabelle
      $insert = array(
        "uid" => $mybb->user['uid'],
        "tid" => $tid
      );
      $db->insert_query("application_ucp_management", $insert);
      //und zum Thread weiterleiten
      redirect(get_thread_link($tid));
    }
  }

  //Steckbrieffrist verlängern
  if ($mybb->get_input('application_ucp_extend')) {

    //Speichern damit nichts verloren geht.
    $fields = $mybb->input;
    //Hilfsunktion - wir übergeben den input und handeln da alles, weil wir das gleiche so oft machen müssen
    application_ucp_savefields($fields, $mybb->user['uid']);

    $db->write_query("UPDATE " . TABLE_PREFIX . "users SET `aucp_extend`= aucp_extend + 1,`aucp_extenddate`= '" . date("Y-m-d") . "' WHERE uid = {$mybb->user['uid']}");
    redirect("usercp.php?action=application_ucp");
  }

  $application_ucp_ucp_main = "";
  eval("\$application_ucp_ucp_main =\"" . $templates->get("application_ucp_ucp_main") . "\";");
  output_page($application_ucp_ucp_main);
}

/**
 * automatische Anzeige von den Feldern im Profil
 * Export Steckbrief button
 */
$plugins->add_hook("member_profile_end", "application_ucp_showinprofile");
function application_ucp_showinprofile()
{
  global $db, $mybb, $memprofile, $templates, $aucp_fields, $exportbtn, $lang, $fields, $application_ucp_profile_trigger;
  $lang->load('application_ucp');
  $userprofil = $memprofile['uid'];
  // Sollen die Felder automatisch zusammengebaut werden
  if ($mybb->settings['application_ucp_profile_view']) {
    //wir kriegen einen String mit html zurück der alles baut.
    $aucp_fields = application_ucp_build_view($userprofil, "profile", "html");
  } else {
    //Wir stellen uns ein Array zusammen
    $fields = application_ucp_build_view($userprofil, "profile", "array");
  }
  $fields = application_ucp_build_view($userprofil, "profile", "array");

  // Trigger Warnung. Wenn vorhanden, geben wir diese extra aus.
  $trigger = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "uid = '{$userprofil}' AND fieldid = '-4'"), "value");
  if ($mybb->settings['application_ucp_trigger'] && $trigger != "") {
    eval("\$application_ucp_profile_trigger =\"" . $templates->get("application_ucp_profile_trigger") . "\";");
  }

  //Export des Steckbriefes
  if ($mybb->settings['application_ucp_export'] && $mybb->user['uid'] != 0 && ($mybb->user['uid'] == $userprofil || $mybb->usergroup['canmodcp'] == 1)) {
    $exportbtn = "
    <form action=\"misc.php?action=exp_app\" method=\"post\" target=\"_blank\">
    <input type=\"hidden\" name=\"uid\" value=\"{$mybb->input['uid']}\" id=\"uid\" />
    <input type=\"submit\" name=\"exp_app\" class=\"bl-btn\" value=\"" . $lang->application_ucp_export . "\" id=\"exp_app\" />
    </form>";
  }
}

/**
 * automatische Anzeige von den Feldern im Profil
 *
 */
$plugins->add_hook("memberlist_user", "application_ucp_showinmemberlist");
function application_ucp_showinmemberlist(&$user)
{
  global $db, $mybb, $memprofile, $templates, $aucp_fields, $exportbtn, $lang, $fields;
  $lang->load('application_ucp');
  $user['aucp_fields'] = "";
  $uid = $user['uid'];
  // die Felder sollen automatisch zusammengebaut werden
  if ($mybb->settings['application_ucp_postbit_view']) {
    $user['aucp_fields'] = application_ucp_build_view($uid, "memberlist", "html");
  } else {
    // nicht automatisch -> wir basteln ein array, damit man auf die einzelnen sachen zugreifen kann
    // Wir stellen uns ein Array zusammen
    $fields = application_ucp_build_view($uid, "postbit", "array");
    $user = array_merge($user, $fields);
  }
}

/**
 ************* WICHTIG!!!!*********
 * Suche in Memberlist - Filtern
 * Funktioniert nur, wenn die memberlist.php umgebaut wird! 
 * Dafür Readme lesen! 
 ************** WICHTIG!!!!*********
 */
$plugins->add_hook("memberlist_intermediate", "application_ucp_filter");
function application_ucp_filter()
{
  global $mybb, $db, $search_query, $js_getinputs, $filterurl, $fieldvalue, $filter, $templates, $applicationfilter, $selectfield, $selectstring, $filterjs, $search_url;
  if ($mybb->settings['application_ucp_search']) {
    //Hier fangen wir an unseren queriy zu bauen. Wir müssten die felder der noch dazu kommenden tabelle mit auswählen
    $selectfield = ", fields.* ";
    //dann basteln wir unseren join
    // $selectstring = "LEFT JOIN (select um.uid as auid, ";
    $filterurl = "?";
    //wir brauchen alle durchsuchbaren felder
    $getfields = $db->simple_select("application_ucp_fields", "*", "searchable = 1 and active = 1");
    $filterjs = "";
    //und gehen sie durch
    $selectstring = application_ucp_buildsql();


    while ($searchfield = $db->fetch_array($getfields)) {

      // //weiter im Querie, hier modeln wir unsere Felder ders users (apllication_ucp_fields taballe) zu einer Tabellenreihe um -> name der Spalte ist fieldname, wert wie gehabt value
      // $selectstring .= " max(case when um.fieldid ='{$searchfield['id']}' then um.value end) AS '{$searchfield['fieldname']}',";

      //Javascript zusammenbauen, wenn die Suche Vorschläge beinhalten soll.
      if ($searchfield['suggestion']) {
        $filterjs .= "
              <script type=\"text/javascript\">
                    <!--
                    if(use_xmlhttprequest == \"1\")
                    {
                      MyBB.select2();
                      $(\"#{$searchfield['fieldname']}\").select2({
                        placeholder: \"filter: {$searchfield['fieldname']}\",
                        minimumInputLength: 2,
                        multiple: false,
                        allowClear: true,
                        ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
                          url: \"xmlhttp.php?action=get_field_aucp\",
                          dataType: 'json',
                          data: function (term, page) {
                            return {
                              query: term, // search term
                              fieldid: \"{$searchfield['id']}\",
                            };
                          },
                          results: function (data, page) { // parse the results into the format expected by Select2.
                            // since we are using custom formatting functions we do not need to alter remote JSON data
                            return {results: data};
                          }
                        },
                        initSelection: function(element, callback) {
                          var value = $(element).val();
                          if (value !== \"\") {
                            callback({
                              id: value,
                              text: value
                            });
                          }
                        },
                          // Allow the user entered text to be selected as well
                          createSearchChoice:function(term, data) {
                          if ( $(data).filter( function() {
                            return this.text.localeCompare(term)===0;
                          }).length===0) {
                            return {id:term, text:term};
                          }
                        },
                      });
      
                        $('[for={$searchfield['fieldname']}]').on('click', function(){
                        $(\"#{$searchfield['fieldname']}\").select2('open');
                        return false;
                      });
                    }
                    // -->
                    </script>
              ";
      }

      // //kleines javascript um url parameter zu bekommen und felder auszufüllen - not working at the moment
      // $js_urlstring .= "
      // if(urlParams.has('" . $searchfield['fieldname'] . "')){
      //   let fill = urlParams.get('" . $searchfield['fieldname'] . "');
      //   $('#" . $searchfield['fieldname'] . "').val(fill);
      // }";

      $fieldvalue = htmlspecialchars_uni($mybb->input[$searchfield['fieldname']]);

      //filterinput feld fürs template - wenn textfeld oder textarea
      if ($searchfield['fieldtyp'] == "text" || $searchfield['fieldtyp'] == "textarea") {
        $typ = "text";
        eval("\$filter .= \"" . $templates->get("application_ucp_filtermemberlist_bit") . "\";");
      }
      //input date
      if ($searchfield['fieldtyp'] == "date") {
        $typ = "date";
        eval("\$filter .= \"" . $templates->get("application_ucp_filtermemberlist_bit") . "\";");
      }
      //input time
      if ($searchfield['fieldtyp'] == "datetime-local") {
        $typ = "datetime-local";
        eval("\$filter .= \"" . $templates->get("application_ucp_filtermemberlist_bit") . "\";");
      }

      //filterinput feld fürs template - wenn Select oder Multiselect
      if (
        $searchfield['fieldtyp'] == "select" ||
        $searchfield['fieldtyp'] == "select_multiple" || $searchfield['fieldtyp'] == "radio" || $searchfield['fieldtyp'] == "checkbox"
      ) {
        $options = explode(",", $searchfield['options']);
        $selects = "<option value=\"\">{$searchfield['label']}</option>";
        foreach ($options as $option) {
          $option = trim($option); //leertasten vorne und hinten rauswerfen
          $selects .= "<option value=\"{$option}\">{$option}</option>";
        }
        eval("\$filter .= \"" . $templates->get("application_ucp_filtermemberlist_selectbit") . "\";");
      }

      if (is_array($mybb->input[$searchfield['fieldname']])) {
        $mybb->input[$searchfield['fieldname']] = $mybb->input[$searchfield['fieldname']][0];
      }

      //Query bauen zum suchen 
      if (trim($mybb->input[$searchfield['fieldname']])) {

        $value = $db->escape_string($mybb->input[$searchfield['fieldname']]);

        if ($searchfield['fieldtyp'] == "text" || $searchfield['fieldtyp'] == "textarea" || $searchfield['fieldtyp'] == "checkbox" || $searchfield['fieldtyp'] == "select_multiple") {
          $search_query .= " AND " . $searchfield['fieldname'] . " LIKE '%" . $value . "%'";
        }
        if (
          $searchfield['fieldtyp'] == "select" ||
          $searchfield['fieldtyp'] == "radio" || $searchfield['fieldtyp'] == "date"
        ) {
          $search_query .= " AND trim(" . $searchfield['fieldname'] . ") = '" . $value . "'";
        }
        $filterurl .= $searchfield['fieldname'] . "=" . urlencode($mybb->input[$searchfield['fieldname']]) . "&";
      }
    }

    if (!empty($mybb->input['age_range'])) {
      if ($mybb->input['age_range'] != 50) {
        $search_query .= "AND TIMESTAMPDIFF(YEAR, geburtstag, CURDATE()) BETWEEN {$mybb->input['age_range']} ";
      } else {
        $search_query .= "AND TIMESTAMPDIFF(YEAR, geburtstag, CURDATE()) > {$mybb->input['age_range']} ";
      }
      $filterurl .= "age_range=" . $mybb->input['age_range'];
    }

    if (!empty($mybb->input['fid4'])) {
      $search_query .= "AND fid4 LIKE '%{$mybb->input['fid4']}%' ";
    }

    $search_url =  $filterurl;
    $filterurl = substr($filterurl, 0, -1);
    // $selectstring = substr($selectstring, 0, -1);
    // $selectstring .= " from `".TABLE_PREFIX."_application_ucp_userfields` as um group by uid) as fields ON auid = u.uid";

    eval("\$applicationfilter .= \"" . $templates->get("application_ucp_filtermemberlist") . "\";");
  }
}

// Über diese Hook kriegen wir die daten für die Inputfelder 

/**
 * Daten bekommen für automatische Suchvorschläge
 */
$plugins->add_hook('xmlhttp', 'application_ucp_getdata');
function application_ucp_getdata()
{
  global $mybb, $charset, $db;
  //action definieren (adresse für xml request in javascript)
  if ($mybb->get_input('action') == 'get_field_aucp') {
    //charset definieren
    header("Content-type: application/json; charset={$charset}");

    //Wert nach dem gesucht werden soll
    $likestring = $db->escape_string_like($mybb->input['query']);
    //welches feld
    $fieldid = intval($mybb->input['fieldid']);
    //Query um die daten zu bekommen
    $query = $db->simple_select("application_ucp_userfields", "distinct(value)", "value LIKE '%{$likestring}%' AND fieldid = '{$fieldid}'");

    //array zusammenbauen
    while ($user = $db->fetch_array($query)) {
      $datacontent = strip_tags($user['value']);
      $data[] = array('fieldid' => $user['uid'], 'id' => $datacontent, 'text' => $datacontent);
    }
    //als JSON ausgeben, weil damit unser javascript arbeitet
    echo json_encode($data);
    exit;
  }

  if ($mybb->input['action'] == "get_aucpfields") {
    //charset definieren
    header("Content-type: application/json; charset={$charset}");

    //Wert nach dem gesucht werden soll
    $likestring = $db->escape_string_like($mybb->input['query']);
    //welches feld
    $fieldid = intval($mybb->input['fieldid']);
    //Query um die daten zu bekommen
    $query = $db->simple_select("application_ucp_fields", "fieldname", "fieldname LIKE '%{$likestring}%'");

    //array zusammenbauen
    while ($field = $db->fetch_array($query)) {
      $datacontent = strip_tags($field['fieldname']);
      $data[] = array('id' => $datacontent, 'text' => $datacontent);
    }
    //als JSON ausgeben, weil damit unser javascript arbeitet
    echo json_encode($data);
    exit;
  }

  if ($mybb->get_input('action') == 'get_userfield') {
    $likestring = $db->escape_string_like($mybb->input['query']);
    $fieldid = $mybb->input['fieldid'];

    //Query um die daten zu bekommen
    $query = $db->query("
		SELECT distinct({$fieldid})
		FROM " . TABLE_PREFIX . "users u
		LEFT JOIN " . TABLE_PREFIX . "userfields f ON (f.ufid=u.uid)
		WHERE {$fieldid} LIKE '%{$likestring}%'
	");
    //array zusammenbauen
    while ($user = $db->fetch_array($query)) {
      $data[] = array('uid' => $user['uid'], 'id' => $user[$fieldid], 'text' => $user[$fieldid]);
    }
    //als JSON ausgeben, weil damit unser javascript arbeitet
    echo json_encode($data);
    exit;
  }
}

/**
 * automatische Anzeige von den Feldern im Postbit
 */
$plugins->add_hook("postbit", "application_ucp_postbit");
function application_ucp_postbit(&$post)
{
  global $db, $mybb, $templates, $fields;

  $uid = $post['uid'];
  $post['aucp_fields'] = "";
  // die Felder sollen automatisch zusammengebaut werden
  if ($mybb->settings['application_ucp_postbit_view']) {
    $post['aucp_fields'] = application_ucp_build_view($uid, "postbit", "html");
  } else {
    // nicht automatisch -> wir basteln ein array, damit man auf die einzelnen sachen zugreifen kann
    // Wir stellen uns ein Array zusammen
    $fields = application_ucp_build_view($uid, "postbit", "array");
    $post = array_merge($post, $fields);
  }
}

/***
 * WOB verteilen
 */
$plugins->add_hook("showthread_start", "application_ucp_showthread");
function application_ucp_showthread()
{
  global $lang, $db, $mybb, $templates, $thread, $tid, $give_wob, $aucp_responsible_mod;
  //Sprachvariable laden
  $lang->load('application_ucp');
  $mods = $mybb->settings['application_ucp_stecki_mods'];

  // Nur Moderatoren 
  if (is_member($mods, $mybb->user['uid'])) {
    // Gruppen holen und sortieren
    $usergroups_query = $db->query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER by usertitle ASC");
    $usergroups_bit = "";
    // Select bauen
    while ($usergroups = $db->fetch_array($usergroups_query)) {
      $usergroups_bit .= "<option value=\"{$usergroups['gid']}\">{$usergroups['title']}</option>";
    }

    // Sekundäre Gruppen hinzufügen
    $additionalgroups_query = $db->query("SELECT * FROM " . TABLE_PREFIX . "usergroups ORDER by usertitle ASC");

    $additionalgroups_bit = "";
    // Select basteln
    while ($additionalgroups = $db->fetch_array($additionalgroups_query)) {
      $additionalgroups_bit .= "<option value=\"{$additionalgroups['gid']}\">{$additionalgroups['title']}</option>";
    }
    // var_dump($thread);
    // echo "fid".$mybb->setting['application_ucp_steckiarea'];
    // application_ucp_wobbutton
    if ($thread['fid'] == $mybb->settings['application_ucp_steckiarea']) {

      $responsible_uid = $db->fetch_field($db->simple_select("application_ucp_management", "uid_mod", "tid = {$tid}"), "uid_mod");
      $usergroup = $db->fetch_field($db->simple_select("users", "usergroup", "uid = {$thread['uid']}"), "usergroup");

      if ($responsible_uid != 0) {
        $responsible = get_user($responsible_uid);
        $responsible_link = build_profile_link($responsible['username'], $responsible_uid);
        $aucp_responsible_mod = $lang->sprintf($lang->application_ucp_responsible, $responsible_link);
      } else {
        $aucp_responsible_mod = $lang->application_ucp_noresponsible;
        if ($usergroup == "$mybb->settings['application_ucp_approved']") {
          $aucp_responsible_mod = "Charakter angenommen.";
        }
      }
      eval("\$give_wob .= \"" . $templates->get("application_ucp_wobbutton") . "\";");
    }
  }
}

$plugins->add_hook("forumdisplay_thread", "application_ucp_forumdisplay");
function application_ucp_forumdisplay()
{
  global $fid, $db, $mybb, $lang, $thread, $aucp_responsible_mod;
  //Sprachvariable laden
  $lang->load('application_ucp');
  $mods = $mybb->settings['application_ucp_stecki_mods'];

  if ($fid == $mybb->settings['application_ucp_steckiarea']) {
    $responsible_uid = $db->fetch_field($db->simple_select("application_ucp_management", "uid_mod", "tid = {$thread['tid']}"), "uid_mod");
    $usergroup = $db->fetch_field($db->simple_select("users", "usergroup", "uid = {$thread['uid']}"), "usergroup");

    if ($responsible_uid != 0) {
      $responsible = get_user($responsible_uid);
      $responsible_link = build_profile_link($responsible['username'], $responsible_uid);
      $aucp_responsible_mod = $lang->sprintf($lang->application_ucp_responsible, $responsible_link);
    } else {
      $aucp_responsible_mod = $lang->application_ucp_noresponsible;
      if ($usergroup == 8) {
        $aucp_responsible_mod = "Charakter angenommen.";
      }
    }
  }
}

/**
 * WOB Funktionalität eintragen - funktionalität
 * Exportfunktion für Steckbrief
 */
$plugins->add_hook("misc_start", "application_ucp_misc");
function application_ucp_misc()
{
  global $mybb, $db, $cache, $groupscache, $templates, $header, $footer, $lang, $theme, $headerinclude, $application_ucp_mods, $application_ucp_mods_readybit;
  //php 8 fix

  $mybb->input['action'] = $mybb->get_input('action');
  //wob in showthread vergeben 
  if ($mybb->input['action']  == 'wob') {

    //daten die wir brauchen
    $textwelcome =  $mybb->settings['application_ucp_wobtext'];
    $textwelcome_flag =  $mybb->settings['application_ucp_wobtext_yesno'];
    $threadauthor = $mybb->input['uid'];
    $newusergroup = $mybb->get_input('usergroups', MyBB::INPUT_INT);
    $subject = "RE: {$mybb->input['subject']}";
    $username = $mybb->user['username'];
    $posttid = $mybb->input['tid'];
    $fid = $mybb->input['fid'];
    $uid = $mybb->user['uid'];
    $ownip = $db->fetch_field($db->query("SELECT ip FROM " . TABLE_PREFIX . "sessions WHERE " . TABLE_PREFIX . "sessions.uid = '$uid'"), "ownip");

    //sekundäre usergruppe eintragen wenn vorhanden
    if ($_POST['additionalgroups'] != '') {
      $additionalgroups_string = implode(', ', $mybb->input['additionalgroups']);
    }
    $new_record = array(
      "usergroup" => $newusergroup,
      "additionalgroups" => $additionalgroups_string,
    );

    //wob date speichern - falls das feld existiert. (später hinzugefügt :D evt. manuell in der DB users tabelle anlegen, wenn gewünscht)
    if ($db->field_exists("wob_date", "users")) {
      $new_record["wob_date"] =  time();
    }

    //speichern
    $db->update_query("users", $new_record, "uid = '$threadauthor'");

    //aus management tabelle schmeißen
    $db->delete_query("application_ucp_management", "uid = '$threadauthor' ");


    //wenn im acp angegeben den welcometext/wob text automatisch posten
    if ($textwelcome_flag) {
      // Antwort-Post erstellen (für Annahme)
      $new_record = array(
        "tid" => $posttid,
        "replyto" => $posttid,
        "fid" => $fid,
        "subject" => $db->escape_string($subject),
        "icon" => "0",
        "uid" => $uid,
        "username" => $db->escape_string($username),
        "dateline" => TIME_NOW,
        "ipaddress" => $ownip,
        "message" => $db->escape_string($textwelcome),
        "includesig" => "1",
        "smilieoff" => "0",
        "edituid" => "0",
        "edittime" => "0",
        "editreason" => "",
        "visible" => "1"
      );
      $db->insert_query("posts", $new_record);

      // Letzten Post im Forum updaten (für Annahme)
      $new_record = array(
        "lastpost" => TIME_NOW,
        "lastposter" => $username,
        "lastposteruid" => $uid,
        "lastposttid" => $posttid
      );
      $db->update_query("forums", $new_record, "fid = '$fid'");
    }
    //zurück zum post leiten
    redirect("showthread.php?tid={$posttid}");
  }

  //Moderator übernimmt Steckbrief
  if ($mybb->input['action'] == "take_application") {
    //modcorrection time aktualisieren
    $uid = intval($mybb->input['uid']);
    $update = array(
      "uid_mod" => $mybb->user['uid']
    );
    $db->update_query("application_ucp_management", $update, "uid = {$uid}");
    redirect('index.php');
  }

  //Steckbrieffrist verlängern
  if ($mybb->input['action'] == "ext_app") {
    //Steckbrief speichern und zur Korrektur geben.
    $update = array(
      "aucp_extend" => '+1',
      "aucp_extenddate" => date("Y-m-d")
    );
    $db->write_query("users", $update, "uid = {$mybb->user['uid']}");
  }

  // Steckbrief als PDF speichern
  if ($mybb->input['action'] == "exp_app" && $mybb->user['uid'] != 0) {
    if ($mybb->settings['application_ucp_export']) {
      //Userinformationen bekommen
      $uid = (int)$mybb->input['uid'];
      $user = get_user($uid);
      //HTML Bauen
      $fields = application_ucp_build_view($uid, "profile", "array");

      $html = '<h1 style="text-align:center">Steckbrief: ' . $user['username'] . '</h1>
      <div style="width:80%">
       <table> ';
      foreach ($fields as $key => $value) {

        if (substr($key, 0, 4) == "pdf_") {
          $key = substr($key, 4);

          // if (strpos($key, "bild")) {
          // } else {
          $html .= '
            <tr>
            <td width="30%"> 
              <p style="padding:5px;"> ' . $key . ': </p> 
            </td>
            <td>
            <p style="padding:5px;"> ' . $value . '</p>
              </td>
              </tr>';
          // }
        }
      }
      $html .= "
      </table>
      </div>";
      require_once('tcpdf/tcpdf.php');

      $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

      // Dokumenteninformationen
      $pdf->SetCreator(PDF_CREATOR);
      $pdf->SetAuthor($pdfAuthor);
      $pdf->SetTitle('Steckbrief ' . $user['username']);
      $pdf->SetSubject('Steckbrief' . $user['username']);

      // Header und Footer Informationen
      $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
      $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

      // Auswahl des Font
      $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

      // Auswahl der MArgins
      $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
      $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

      // Automatisches Autobreak der Seiten
      $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

      // Image Scale 
      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

      // Schriftart
      $pdf->SetFont('helvetica', '', 10);

      // Neue Seite
      $pdf->AddPage();

      // Fügt den HTML Code in das PDF Dokument ein
      $pdf->writeHTML($html, true, false, true, false, '');

      //Ausgabe der PDF
      //Variante 1: PDF direkt an den Benutzer senden:
      $pdf->Output($user['username'] . ".pdf", 'I');

      //Thanks to 
      // https://www.php-einfach.de/experte/php-codebeispiele/pdf-per-php-erstellen-pdf-rechnung/
    }
  }
}
/**
 * Overview für Moderatoren
 */
$plugins->add_hook("misc_start", "application_ucp_modoverview");
function application_ucp_modoverview()
{
  global $mybb, $db, $templates, $lang, $header, $footer, $theme, $headerinclude, $application_ucp_mods, $application_ucp_mods_readybit;
  $addtext = "";
  if ($mybb->get_input('action', MyBB::INPUT_STRING) == "application_mods" || $mybb->get_input('action', MyBB::INPUT_STRING) == "aplication_mods") {
    // get settings
    $lang->load('application_ucp');
    $applicantgroup = $mybb->settings['application_ucp_applicants'];
    $app_deadline = $mybb->settings['application_ucp_applicationtime'];
    $app_corr_deadline = $mybb->settings['application_ucp_correctiontime'];
    $mods = $mybb->settings['application_ucp_stecki_mods'];

    // Nur Moderatoren haben Zugriff auf die Seite.
    if (!is_member($mods, $mybb->user['uid'])) {
      error_no_permission();
    }

    // fertige Steckbriefe bekommen (alle wo der Mod quasi etwas tun muss)
    $ready_for_mod = $db->simple_select(
      "application_ucp_management",
      "*",
      "(modcorrection_time is null) 
      OR 
      (usercorrection_time > modcorrection_time)"
    );

    // OR (usercorrection_time is null) 
    // OR (modcorrection_time < usercorrection_time)
    // (uid_mod = 0 or modcorrection_time is NULL or modcorrection_time ='') 
    while ($data = $db->fetch_array($ready_for_mod)) {
      if ($data['correctioncnt'] > 0) {
        $correction = "<br/> {$data['correctioncnt']}. Korrektur.";
      } else {
        $correction = "";
      }
      $user = get_user($data['uid']);
      if ($data['uid_mod'] != "0") {
        $modinfos = get_user($data['uid_mod']);
        $mod = "<a href=\"" . get_profile_link($modinfos['uid']) . "\">" . $modinfos['username'] . "</a>";
      } else {
        $mod = "<span class=\"bl-alert\">kein Bearbeiter</b><br/>
        <a href=\"misc.php?action=take_application&uid={$user['uid']}\">Korrektur übernehmen</a>";
      }
      $aucp_mod_profillink = "<a href=\"" . get_profile_link($user['uid']) . "\">" . $user['username'] . "</a>";
      $aucp_mod_steckilink = "<a href=\"" . get_thread_link($data['tid']) . "\">Steckbrief</a>";

      $aucp_mod_modlink = $mod;
      if ($data['usercorrection_time'] > $data['submission_time']) {
        $aucp_mod_date = date("d.m.Y", strtotime($data['usercorrection_time']));
      } else {
        $aucp_mod_date = date("d.m.Y", strtotime($data['submission_time']));
      }
      eval("\$application_ucp_mods_readybit .= \"" . $templates->get("application_ucp_mods_bit") . "\";");
    }
    //Variablen leeren
    $aucp_mod_steckilink = $aucp_mod_profillink = $aucp_mod_modlink = $aucp_mod_date = $correction = "";

    // Steckbriefe die vom User korrigiert werden müssen
    $round_two = $db->simple_select(
      "application_ucp_management",
      "*",
      "(modcorrection_time is not null && usercorrection_time is null) 
    || (modcorrection_time > usercorrection_time)
    "
    );
    while ($data = $db->fetch_array($round_two)) {
      $user = get_user($data['uid']);
      if ($data['uid_mod'] != "0") {
        $modinfos = get_user($data['uid_mod']);
        $mod = "<a href=\"" . get_profile_link($modinfos['uid']) . "\">" . $modinfos['username'] . "</a>";
      } else {
        $mod = "<span class=\"bl-alert\">kein Bearbeiter</b>";
      }
      $aucp_mod_profillink = "<a href=\"" . get_profile_link($user['uid']) . "\">" . $user['username'] . "</a>";
      $aucp_mod_steckilink = "<a href=\"" . get_thread_link($data['tid']) . "\">Steckbrief</a>";

      $aucp_mod_modlink = $mod;
      $aucp_mod_date = date("d.m.Y", strtotime($data['submission_time'] . " + {$app_corr_deadline} days"));
      // $aucp_mod_date = "";
      eval("\$application_ucp_mods_users .= \"" . $templates->get("application_ucp_mods_bit") . "\";");
    }

    //Variablen leeren
    $aucp_mod_steckilink = $aucp_mod_profillink = $aucp_mod_modlink = $aucp_mod_date = "";
    $correction = "";
    // noch nicht eingereichte Steckbriefe
    // Steckbriefe die vom User korrigiert werden müssen
    $get_new = $db->write_query("
      SELECT * FROM " . TABLE_PREFIX . "users 
      WHERE uid NOT IN 
        (SELECT uid FROM " . TABLE_PREFIX . "application_ucp_management) 
        AND usergroup = {$applicantgroup}");
    while ($data = $db->fetch_array($get_new)) {
      $user = get_user($data['uid']);
      // var_dump($user);
      $regdate = date("d.m.Y", $user['regdate']);
      $lastactiv = date("d.m.Y", $user['lastactive']);
      //hier registierungsdatum statt steckilink
      $aucp_mod_steckilink =  $regdate;
      //Link zum Profil des users
      $aucp_mod_profillink = "<a href=\"" . get_profile_link($user['uid']) . "\">" . $user['username'] . "</a>";
      //hier statt link zum mod, letzte aktivität des users
      $aucp_mod_modlink = $lastactiv;

      $aucp_mod_date = date("d.m.Y", strtotime(date("d.m.Y", $data['regdate']) . " + {$app_deadline} days"));

      if ($mybb->settings['application_ucp_extend'] > 0) {
        //wie oft wurde verlängert
        $extend_cnt = $db->fetch_field($db->simple_select("users", "aucp_extend", "uid = {$user['uid']}"), "aucp_extend");
        if ($extend_cnt > 0) {
          $to_add = $mybb->settings['application_ucp_extend'] * $extend_cnt;
          $add_extend = date("d.m.Y", strtotime("+{$to_add} day", strtotime($aucp_mod_date)));
          $addtext = " ({$extend_cnt}x verlängert.)";
          $aucp_mod_date = $add_extend . $addtext;
        }
      }
      // $aucp_mod_date = "";
      eval("\$application_ucp_mods_new .= \"" . $templates->get("application_ucp_mods_bit") . "\";");
    }
    eval("\$application_ucp_mods = \"" . $templates->get("application_ucp_mods") . "\";");
    output_page($application_ucp_mods);
  }
}

/***
 * Mod antwortet auf Steckbriefthread -> also Korrektur keine Annahme
 * Datum muss in Management Tabelle gespeichert werden
 */
$plugins->add_hook("newreply_do_newreply_end", "application_ucp_do_reply", "0");
function application_ucp_do_reply()
{
  global $mybb, $db, $fid, $tid;
  $appfid = $mybb->settings['application_ucp_steckiarea'];
  $moderator = $mybb->settings['application_ucp_stecki_mods'];
  if (is_member($moderator, $mybb->user['uid']) && $fid == $appfid) {
    $moduid = $db->fetch_field($db->simple_select("application_ucp_management", "uid_mod", "tid = {$tid}"), "uid_mod");
    if ($moduid == "0") {
      $update = array(
        "uid_mod" => $mybb->user['uid'],
        "modcorrection_time" => date('Y-m-d H:i:s'),
      );
    } else {
      $update = array(
        "modcorrection_time" => date('Y-m-d H:i:s'),
      );
    }
    $db->update_query("application_ucp_management", $update, "tid = {$tid}");
  }
}

/**
 * Meldungen auf Index.
 */
$plugins->add_hook('index_start', 'application_ucp_indexalert');
function application_ucp_indexalert()
{
  global $templates, $db, $mybb, $application_ucp_index, $lang;
  $extend_button = $addtext = $profilelink = "";
  //settings holen 
  $applicants = $mybb->settings['application_ucp_applicants'];
  $mods = $mybb->settings['application_ucp_stecki_mods'];
  $friststecki = $mybb->settings['application_ucp_applicationtime'];
  $fristkorrektur = $mybb->settings['application_ucp_correctiontime'];
  $application_ucp_index_bit = $application_ucp_index = $application_ucp_index_modbit = "";
  $alertflag = 0;

  $lang->load('application_ucp');

  //wer ist online
  $uid = $mybb->user['uid'];
  //Daten aus Management tabelle
  $get_managment = $db->simple_select("application_ucp_management", "*", "uid = {$uid}");
  //Benutzer ist ein Bewerber
  if ($mybb->user['usergroup'] == $applicants) {

    //Der Benutzer hat noch keinen Steckbrief abgegben. Zeit bis zum X. 
    if ($db->num_rows($get_managment) == 0) {
      $alertflag = 1;
      $frist = strtotime("+{$friststecki} days", $mybb->user['regdate']);
      $add_extend =  $frist;
      //extend button
      if ($mybb->settings['application_ucp_extend'] > 0) {
        //wie oft wurde verlängert
        $extend_cnt = $db->fetch_field($db->simple_select("users", "aucp_extend", "uid = {$mybb->user['uid']}"), "aucp_extend");
        if ($extend_cnt < $mybb->settings['application_ucp_extend_cnt']) {
          $extend_button = "<a href=\"misc.php&action=ext_app\" class=\"aucp extbtn\">{$lang->application_ucp_extbtn}</a>";
        }
        if ($extend_cnt > 0) {
          $to_add = $mybb->settings['application_ucp_extend'] * $extend_cnt;
          $add_extend = strtotime("+{$to_add} days", $frist);
          $lang->application_ucp_index_extinfo = $lang->sprintf($lang->application_ucp_index_extinfo, $extend_cnt);

          $addtext = $lang->application_ucp_index_extinfo;
        }
      }
      $deadline = date("d.m.Y", $add_extend);
      $lang->application_ucp_index_extinfo_deadline = $lang->sprintf($lang->application_ucp_index_extinfo_deadline, $deadline, $addtext);
      $message = $lang->application_ucp_index_extinfo_deadline;
      eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
    } else {
      //Steckbrief wurde eingereicht
      while ($alert = $db->fetch_array($get_managment)) {
        // Noch kein verantwortlicher Moderator
        if ($alert['uid_mod'] == "0") {
          $alertflag = 1;
          $message = $lang->application_ucp_index_nomod;
          eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
        } else {
          //Moderator hat übernommen
          $mod = get_user($alert['uid_mod']);
          //noch in Korrektur
          if (strtotime($alert['modcorrection_time']) <= strtotime($alert['usercorrection_time'])) {
            //Info: XY hat deinen Steckbrief übernommen //aber noch keine Korrektur
            $alertflag = 1;
            $profilelink = build_profile_link($mod['username'], $mod['uid']);
            $lang->application_ucp_index_token = $lang->sprintf($lang->application_ucp_index_token, $profilelink);
            $message = $lang->application_ucp_index_token;
            //Korrektur vorgenommen - user muss reagieren
            eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
          } elseif (strtotime($alert['modcorrection_time']) > strtotime($alert['usercorrection_time'])) {
            //Dein Steckbrief ist fertig korrigiert Zeit zur kontrolle bis
            $alertflag = 1;
            $profilelink = build_profile_link($mod['username'], $mod['uid']);
            $frist = strtotime("+{$fristkorrektur} days", strtotime($alert['modcorrection_time']));
            $deadline = date("d.m.Y", $frist);
            $lang->application_ucp_index_correction = $lang->sprintf($lang->application_ucp_index_correction, $profilelink, $deadline);
            $message = $lang->application_ucp_index_correction;
            eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
          }
        }
      }
    }
  }

  if (is_member($mods, $uid)) {
    $get_alerts = $db->simple_select("application_ucp_management", "*");
    while ($alert = $db->fetch_array($get_alerts)) {
      //Steckbrief wurde abgegeben
      $about = get_user($alert['uid']);
      $aboutuserlink = build_profile_link($about['username'], $about['uid'], "_blank");

      // if (strtotime($alert['submission_time']) > strtotime($alert['modcorrection_time'])) {
      //Noch kein Mod zugeteilt - Mod kann ihn übernehmen
      if ($alert['uid_mod'] == "0") {
        $alertflag = 1;
        $message = $lang->sprintf($lang->application_ucp_index_mod_steckialert, $aboutuserlink, $alert['uid']);
        eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
      } else {
        //alle charas des mods bekommen
        $mod_charas = application_ucp_allchars($uid);
        //schauen ob der der übernommen hat, dieser Mod ist (uid müsste im array sein)
        $charaflag = array_key_exists($alert['uid_mod'], $mod_charas);
        if ($charaflag) {
          $alert['modcorrection_time'] = strtotime($alert['modcorrection_time']);
          $alert['usercorrection_time'] = strtotime($alert['usercorrection_time']);
          //man hat ihn selbst übernommen
          //Man hat noch keine Korrektur vorgenommen
          //modcorrection_time ist NULL oder //modcorrection_time < als usercorrection_time
          if ($alert['modcorrection_time'] == NULL) {
            $alertflag = 1;
            $message = $lang->sprintf($lang->application_ucp_index_mod_steckialert_modturn, $aboutuserlink);
            eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
          }
          //Korrektur vorgenommen und warten auf user
          //modcorrection time != null && usercorrectiontime == null || modcorrection_time > als usercorrectiontime
          if (($alert['modcorrection_time'] != NULL && $alert['usercorrection_time'] == NULL)
            || $alert['modcorrection_time'] > $alert['usercorrection_time']
          ) {
            $alertflag = 1;
            $message = $lang->sprintf($lang->application_ucp_index_mod_steckialert_userturn, $aboutuserlink);
            eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
          }
          //User hat korrigiert
          //usercorrection_time > modcorrectiontime
          if ($alert['usercorrection_time'] > $alert['modcorrection_time']) {
            $alertflag = 1;
            $message = $lang->sprintf($lang->application_ucp_index_mod_steckialert_userhascorrected, $aboutuserlink);
            eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
          }
        }
      }
      // }
    }
  }
  if ($alertflag) {
    eval("\$application_ucp_index = \"" . $templates->get("application_ucp_index") . "\";");
  }
}

/**
 * 
 * make fields of current user global showable 
 * 
 */
$plugins->add_hook("global_start", "application_ucp_global");
function application_ucp_global()
{

  global $db, $mybb;
  //wir bauen unser querie um die infos von dem user zu kriegen, der online ist. 
  $ucp_data = array();
  $getinfos = $db->write_query("SELECT uf.*, fieldname FROM `" . TABLE_PREFIX . "application_ucp_userfields` uf, " . TABLE_PREFIX . "application_ucp_fields f WHERE uf.fieldid = f.id AND uid = {$mybb->user['uid']} AND f.active = 1");
  while ($data = $db->fetch_array($getinfos)) {
    $ucp_data[$data['fieldname']] = $data['value'];
  }
}


/***
 * 
 * Hilfsfunktionen
 *
 */
/**
 * Helperfunction for check if dep is right
 * 
 */
function application_ucp_checkdep($dep, $deptestvalue, $uid)
{
  global $db, $mybb;

  $depflag = true;
  if ($dep != "none") {

    $depid = $db->fetch_field($db->simple_select("application_ucp_fields", "id", "fieldname = '{$dep}'"), "id");
    $depvalue = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "fieldid = '{$depid}' and uid = '{$uid}'"), "value");

    $depvalue = "," . $depvalue;
    $deptestvalue = "," . $deptestvalue;

    if (strpos($deptestvalue, $depvalue)  !== false) {
      $depflag = true;
    } else {
      //wenn nicht, setzen wir die flag auf false, das feld soll nicht angezeigt werden.
      $depflag = false;
    }
  }

  return $depflag;
}

/**
 * ****
 * Helper Function for building SQL String. 
 * ****
 * 
 * Default wert für Parameter = searchable
 * 
 * searchable = Funktion ist durchsuchbar
 * all = alle aktiven felder, auch die nicht durchsuchbaren
 */
function application_ucp_buildsql($type = "searchable")
{
  global $db, $mybb;

  $selectstring = "LEFT JOIN (select um.uid as auid,";
  if ($type == "searchable") {
    $getfields = $db->simple_select("application_ucp_fields", "*", "searchable = 1 and active = 1");
  }
  if ($type == "all") {
    $getfields = $db->simple_select("application_ucp_fields", "*", "active = 1");
  }
  while ($searchfield = $db->fetch_array($getfields)) {
    //weiter im Querie, hier modeln wir unsere Felder ders users (apllication_ucp_fields taballe) zu einer Tabellenreihe um -> name der Spalte ist fieldname, wert wie gehabt value
    $selectstring .= " max(case when um.fieldid ='{$searchfield['id']}' then um.value end) AS '{$searchfield['fieldname']}',";
  }

  $selectstring = substr($selectstring, 0, -1);
  $selectstring .= " from `" . TABLE_PREFIX . "application_ucp_userfields` as um group by uid) as fields ON auid = uid";

  //Kein durchsuchbares feld, wir müssen so mysql fehler abfangen.
  if ($db->num_rows($getfields) == 0) {
    $selectstring = "LEFT JOIN (select um.uid as auid from `" . TABLE_PREFIX . "application_ucp_userfields` as um group by uid) as fields ON auid = uid";
  }
  return $selectstring;
}

/**
 * Anzeige der Felder im Profil und im Postbit
 * Wir sind faul und wollen das ganze nicht mehrmals schreiben :D 
 * uid von wem sollen die Felder angezeigt werden, location 
 * location: wo, profil, memberlist oder postbit
 * return string oder array 
 */

function application_ucp_build_view($uid, $location, $kind)
{
  global $db, $mybb, $theme;
  require_once MYBB_ROOT . "inc/class_parser.php";
  $parser = new postParser;
  //wir gehen davon aus, das feld ist erst einmal von nichts abhängig, deswegen setzen wir die flag auf true
  $depflag = true;
  $thisuser = $mybb->user['uid'];
  //soll als plane html ausgegeben werden - wir bauen direk das markup
  if ($kind == "html") {
    //äußerer Container
    $buildhtml = "<div class=\"aucp_fieldContainer aucp_{$location}\">";
    //alle felder bekommen
    $fieldquery = $db->write_query("
        SELECT * FROM `" . TABLE_PREFIX . "application_ucp_userfields` uf 
          inner JOIN 
        " . TABLE_PREFIX . "application_ucp_fields f 
        ON f.id = uf.fieldid 
        and uid = {$uid} AND {$location} = 1 
        AND fieldid > 0 AND active = 1 and value != 'deleteinput' ORDER by sorting");
    //Felder durchgehen
    while ($field = $db->fetch_array($fieldquery)) {
      //erst testen wir die abhängigkeit, das feld hat eine, also schauen wir ob die bedingung erfüllt ist
      $depflag = application_ucp_checkdep($field['dependency'], $field['dependency_value'], $uid);
      //parser options
      $parser_options = array(
        "allow_html" => $field['allow_html'],
        "allow_mycode" => $field['allow_mybb'],
        "allow_smilies" => 0,
        "allow_imgcode" => $field['allow_img'],
        "allow_videocode" => $field['allow_video'],
        "nl2br" => 1
      );

      if ($field['fieldtyp'] == "date") {
        //field date? Dann wollen wir es hübsch ausgeben.
        $fieldvalue = date("d.m.Y", strtotime($field['value']));
      } else {
        $fieldvalue = $field['value'];
      }
      //innerer container mit werten und label
      if ($depflag) {
        //Gast und feld soll nicht für Gäste angezeigt werden
        if ($thisuser == 0 && $field['guest'] == 0) {
          //alternativer Inhalt
          $fieldvalue = $field['guest_content'];
          $fieldvalue = str_replace('$themepath', $theme['imgdir'], $fieldvalue);
          $buildhtml .= "<div class=\"aucp_fieldContainer__item\"><div class=\"aucp_fieldContainer__field label\">{$field['label']}:</div>
          <div class=\"aucp_fieldContainer__field field {$field['fieldname']}\">" . $parser->parse_message($fieldvalue, $parser_options) . "</div>
          </div>
          ";
        } else {
          $buildhtml .= "<div class=\"aucp_fieldContainer__item\"><div class=\"aucp_fieldContainer__field label\">{$field['label']}:</div>
    <div class=\"aucp_fieldContainer__field field {$field['fieldname']}\">" . $parser->parse_message($fieldvalue, $parser_options) . "</div>
    </div>
    ";
        }
      }
    }
    //ende äußerer container
    $buildhtml .= "</div>";
    return $buildhtml; //rückgabe
  }
  //Rückgabe als Array, also einzelne Variablen die sich ansprechen lassen - ohne html code
  if ($kind == "array") {
    $array = array();
    //erst einmal felder bekommen
    $fieldquery = $db->write_query("
      SELECT * FROM `" . TABLE_PREFIX . "application_ucp_userfields` uf 
        inner JOIN 
      " . TABLE_PREFIX . "application_ucp_fields f 
      ON f.id = uf.fieldid 
      and uid = {$uid} 
      AND {$location} = 1 
      AND fieldid > 0
      AND active = 1 
      AND value != 'deleteinput'");
    //durchgehen
    while ($field = $db->fetch_array($fieldquery)) {
      //erst testen wir die abhängigkeit, das feld hat eine, also schauen wir ob die bedingung erfüllt ist
      $depflag = application_ucp_checkdep($field['dependency'], $field['dependency_value'], $uid);

      if ($depflag) {
        $parser_options = array(
          "allow_html" => $field['allow_html'],
          "allow_mycode" => $field['allow_mybb'],
          "allow_smilies" => 0,
          "allow_imgcode" => $field['allow_img'],
          "allow_videocode" => $field['allow_video']
        );
        if ($field['fieldtyp'] == "date") {
          $fieldvalue = date("d.m.Y", strtotime($field['value']));
        } elseif ($field['fieldtyp'] == "select_multiple") {
          $fieldvalue = str_replace(",", ", ", $field['value']);
        } else {
          $fieldvalue = $field['value'];
        }
        if ($thisuser == 0 && $field['guest'] == 0) {
          //alternativer Inhalt
          $fieldvalue = $field['guest_content'];
          $fieldvalue = str_replace('$themepath', $theme['imgdir'], $fieldvalue);
        }
        // Wir bauen unsere Variablen zusammen
        // Label & Value in div: {$application['labelvalue_vorname']}
        if ($fieldvalue == "") {
          $emptyflag = "is_empty";
        } else {
          $emptyflag = "";
        }

        // Wir bauen unsere Variablen zusammen
        // Label & Value in div Container, mit divs um elemente: {$application['labelvalue_vorname']}
        $arrayfieldlabelvalue = "labelvalue_divcon_{$field['fieldname']}";
        $array[$arrayfieldlabelvalue] = "
          <div class=\"labelvalue_divcon_{$field['fieldname']} {$emptyflag} \">
            <div class=\"aucp_divcon_label\">" . $field['label'] . ":</div> 
            <div class=\"aucp_divcon_value\">" . $parser->parse_message($fieldvalue, $parser_options) . "</div>
          </div>";

        // Label & Value in div: {$application['labelvalue_vorname']}
        $arrayfieldlabelvalue = "labelvalue_div_{$field['fieldname']}";
        $array[$arrayfieldlabelvalue] = "<div class=\"labelvalue_div_{$field['fieldname']} {$emptyflag} \">" . $field['label'] . ": " . $parser->parse_message($fieldvalue, $parser_options) . "</div>";

        // Wir bauen unsere Variablen zusammen
        // Label & Value: {$application['labelvalue_vorname']}
        $arrayfieldlabelvalue = "labelvalue_{$field['fieldname']}";
        $array[$arrayfieldlabelvalue] = "<span class=\"{$emptyflag} {$field['fieldname']}\">" . $field['label'] . ": " . $parser->parse_message($fieldvalue, $parser_options) . "</span>";


        // Label: {$application['label_vorname']}
        $arraylabel = "label_{$field['fieldname']}";
        if ($field['container'] == 'span') {
          $array[$arraylabel] = "<span class=\"{$emptyflag} label_{$field['fieldname']}\">" . $field['label'] . "</span>";
        } else if ($field['container'] == 'div') {
          $array[$arraylabel] = "<div class=\"{$emptyflag} label_{$field['fieldname']}\">" . $field['label'] . "</div>";
        } else {
          $array[$arraylabel] = $field['label'];
        }
        // Label in div box: {$application['label_div_vorname']}
        $arraylabel = "label_div_{$field['fieldname']}";
        $array[$arraylabel] = "<div class=\"label_div_{$field['fieldname']} {$emptyflag}\">" . $field['label'] . "</div>";

        // Value: {$application['value_vorname']}
        $arraylabel = "value_{$field['fieldname']}";
        if ($field['container'] == 'span') {
          $array[$arraylabel] = "<span class=\"{$emptyflag} value_{$field['fieldname']}\">" . $parser->parse_message($fieldvalue, $parser_options) . "</span>";
        } else if ($field['container'] == 'div') {
          $array[$arraylabel] = "<div class=\"{$emptyflag} value_{$field['fieldname']}\">" . $parser->parse_message($fieldvalue, $parser_options) . "</div>";
        } else {
          $array[$arraylabel] = $parser->parse_message($fieldvalue, $parser_options);
        }

        // Value in divbox: {$application['value_div_vorname']}
        $arraylabel = "value_div_{$field['fieldname']}";
        $array[$arraylabel] = "<div class=\"value_div_{$field['fieldname']} {$emptyflag}\">" . $parser->parse_message($fieldvalue, $parser_options) . "</div>";

        // label as key, value as value, needed for pdf: {$application['pdf_Vorname']}
        $arraylabel = "pdf_{$field['label']}";
        $array[$arraylabel] = $parser->parse_message($fieldvalue, $parser_options);
      }
    }
    return $array;
  }
}

/**
 * Funktion um ein einzelnes Felder zu speichern
 */
function application_ucp_save_single_field($fields, $key, $uid)
{
  global $db, $mybb;
  if (is_array($fields[$key])) {
    $fields[$key] = implode(",", $fields[$key]);
  }
  $value = trim($db->escape_string($fields[$key]));

  // speichern
  $db->write_query("
    INSERT INTO " . TABLE_PREFIX . "application_ucp_userfields (uid, value, fieldid) 
    VALUES('{$uid}', '{$value}', {$key}) ON 
    DUPLICATE KEY UPDATE value='{$value}'");
}

/**
 * Funktion um die Felder zu speichern
 */
function application_ucp_savefields($fields, $uid)
{
  global $db, $mybb, $lang;

  if (!verify_post_check($mybb->get_input('my_post_key'))) {
    error($lang->invalid_post_code);
  }
  foreach ($fields as $key => $value) {
    //key -> id des felds  //Value -> der wert
    //checkboxen kriegen wir als array, wir müssen es erst in einen string umwandeln, den wir speichern können
    if (is_array($value)) {
      $value = implode(",", $value);
    }
    //Weil wir nur infofelder haben, wir wollen nur die Felder mit einem numerischen wert, also einer ID und somit einem Steckbrieffeld absspeichern
    if (is_numeric($key)) {
      //Füge den Wert neu ein, wenn er noch nicht existiert
      //dabei einmal sonderzeichen escapen und leertasten vorne und hinten rauswerfen
      $value = trim($db->escape_string($value));
      $db->write_query("
        INSERT INTO " . TABLE_PREFIX . "application_ucp_userfields (uid, value, fieldid) 
        VALUES('{$uid}', '{$value}', {$key}) ON 
        DUPLICATE KEY UPDATE value='{$value}'");
    }
  }
}
//GET USER
function application_ucp_allchars($thisuser)
{
  global $mybb, $db;
  //wir brauchen die id des Hauptcharas
  $getas_uid = get_user($thisuser);
  $as_uid = $getas_uid['as_uid'];
  $charas = array();
  if ($as_uid == 0) {
    // as_uid = 0 wenn hauptaccount oder keiner angehangen
    $get_all_users = $db->query("SELECT uid,username FROM " . TABLE_PREFIX . "users WHERE ((as_uid = $thisuser) OR (uid = $thisuser)) ORDER BY username");
  } else if ($as_uid != 0) {
    //id des users holen wo alle an gehangen sind 
    $get_all_users = $db->query("SELECT uid,username FROM " . TABLE_PREFIX . "users WHERE ((as_uid = $as_uid) OR (uid = $thisuser) OR (uid = $as_uid)) ORDER BY username");
  }
  while ($users = $db->fetch_array($get_all_users)) {
    $uid = $users['uid'];
    $charas[$uid] = $users['username'];
  }
  return $charas;
}

/***
 * PN oder Alert an user, der betroffen ist.
 * @param 
 * $charakter welchen Charakter betriff es, 
 * $touid an welche pn
 * $tid thread
 * $editflag Es wurde nur editiert
 */
function application_ucp_affected_alert($charakter, $touid, $tid, $editflag)
{
  global $mybb;
  $alerttype = $mybb->settings['application_ucp_stecki_affected_alert'];
  if ($alerttype == 0) { //private message
    $user = get_user($charakter);

    $userprofil = build_profile_link($user['username'], $charakter);

    $steckilink = get_thread_link($tid);
    $alertmsg = "Der Steckbrief({$steckilink}) von {$userprofil} betrifft dich. Bitte gib dein Okay.";
    $pm = array(
      'subject' => "Charakter der dich betrifft",
      'message' => $alertmsg,
      'touid' => $touid,
      'from_user' => $charakter,
    );
    send_pm($pm, -1, true);
  } else if ($alerttype == 1) { // MyAlert
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
      $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('application_ucp_affected');
      if ($alertType != NULL && $alertType->getEnabled()) {
        //constructor for MyAlert gets first argument, $user (not sure), second: type  and third the objectId 
        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$touid, $alertType);
        //some extra details
        $alert->setExtraDetails([
          'tid' => $tid,
          'fromuser' => $charakter
        ]);
        //add the alert
        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
      }
    }
  } else if ($alerttype == 2) { // Mention Me -> with MyAlert
    //sollte beim Threaderstellen funktionieren
    //achtung ein edit -> also greift hier nicht das automatische.
    if ($editflag) { //wir schicken also einen MyAlert
      $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('application_ucp_affected');
      if ($alertType != NULL && $alertType->getEnabled()) {
        //constructor for MyAlert gets first argument, $user (not sure), second: type  and third the objectId 
        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$touid, $alertType);
        //some extra details
        $alert->setExtraDetails([
          'tid' => $tid,
          'fromuser' => $charakter
        ]);
        //add the alert
        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
      }
    }
  } else if ($alerttype == 3) { //nothing
    //ja nichts halt, eh?
  }
}


/**************************** 
 * 
 *  My Alert Integration
 * Alert für Accounts die vom Steckbrief betroffen sind (verwandte freunde gesuche... )
 * 
 * *************************** */
if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
  $plugins->add_hook("global_start", "application_ucp_myalert");
}
function application_ucp_myalert()
{
  global $mybb, $lang;
  $lang->load('application_ucp');
  /**
   * Wir brauchen unseren MyAlert Formatter
   * Alert für betroffene User
   */
  class MybbStuff_MyAlerts_Formatter_ApplicationUcpAffectedFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
  {
    /**
     * Build the output string for listing page and the popup.
     * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
     * @return string The formatted alert string.
     */
    public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
    {
      $alertContent = $alert->getExtraDetails();
      $from = get_user($alertContent['fromuser']);
      return $this->lang->sprintf(
        $this->lang->application_ucp_affected,
        $from['username'],
        $alertContent['tid'],
        $outputAlert['dateline']
      );
    }
    /**
     * Initialize the language, we need the variables $l['myalerts_setting_alertname'] for user cp! 
     * and if need initialize other stuff
     * @return void
     */
    public function init()
    {
      if (!$this->lang->application_ucp) {
        $this->lang->load('application_ucp');
      }
    }
    /**
     * We want to define where we want to link to. 
     * @param MybbStuff_MyAlerts_Entity_Alert $alert for which alert.
     * @return string return the link.
     */
    public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
    {
      $alertContent = $alert->getExtraDetails();
      return $this->mybb->settings['bburl'] . '/showthread.php?tid=' . $alertContent['tid'];
    }
  }
  if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
    $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
    if (!$formatterManager) {
      $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
    }
    $formatterManager->registerFormatter(
      new MybbStuff_MyAlerts_Formatter_ApplicationUcpAffectedFormatter($mybb, $lang, 'application_ucp_affected')
    );
  }
}


// $plugins->add_hook('xmlhttp', 'application_ucp_xmlhttp', -1);
// function application_ucp_xmlhttp()
// {
//   global $mybb;

//   if ($mybb->get_input('action') == 'post_check') {
//     $toReturn = array();
//     if (!verify_post_check($mybb->get_input('my_post_key'), true)) {
//       $toReturn = array(
//         'errors' => array("Falscher Autorisierungscode. Hast du dich zwischen durch umgeloggt?"),
//       );
//       echo json_encode($toReturn);
//     } else {
//       $toReturn = array(
//         'success'  => true,
//       );
//       echo json_encode($toReturn);
//     }
//   }
// }

/**
 * Was passiert wenn ein User gelöscht wird
 */
$plugins->add_hook("admin_user_users_delete_commit_end", "application_ucp_delete");
function application_ucp_delete()
{
  global $db, $cache, $mybb, $user;

  $db->delete_query('application_ucp_management', "uid = " . (int)$user['uid'] . "");
  $db->delete_query('application_ucp_userfields', "uid = " . (int)$user['uid'] . "");

  // add_task_log($task, "Reservierungen bereinigt uid war {$user['uid']} {$username}");
}

