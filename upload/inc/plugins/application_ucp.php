<?php

/**
 * Steckbriefe im UCP  - by risuena
 * https://github.com/katjalennartz
 * 
 * Dieses Plugin gibt die Möglichkeit im UCP einen eigenen Ausfüllbereich für Steckbriefe/Charakterinfos zu haben.
 * Die Felder können frei im ACP erstellt werden.
 * Bitte die Readme beachten. Wirklich! Da steht alles wichtige drin ;) 
 * 
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
    "name" => $lang->application_ucp_permission,
    "description" => $lang->application_ucp_info_descr,
    "website" => "https://github.com/katjalennartz/application_ucp",
    "author" => "risuena",
    "authorsite" => "https://github.com/katjalennartz",
    "version" => "1.0",
    "compatibility" => "18*"
  );
}

$plugins->add_hook("usercp_start", "application_ucp_usercp");

function application_ucp_is_installed()
{
  global $db;
  if ($db->table_exists("application_ucp_fields")) {
    return false;
  }
  return false;
}

function application_ucp_install()
{
  global $db;
  application_ucp_uninstall();

  $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "application_ucp_fields` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `fieldtyp` varchar(100) NOT NULL,
    `fieldname` varchar(100) NOT NULL,
    `label` varchar(100) NOT NULL,
    `options` varchar(500) NOT NULL DEFAULT '',
    `editable` int(1) NOT NULL DEFAULT 0,
    `mandatory` int(1) NOT NULL DEFAULT 1,
    `dependency` varchar(500) NOT NULL DEFAULT '',
    `dependency_value` varchar(500) NOT NULL DEFAULT '',
    `postbit` int(1) NOT NULL DEFAULT 0,
    `profile` int(1) NOT NULL DEFAULT 0,
    `template` varchar(2500) NOT NULL DEFAULT '',
    `sorting` int(10) NOT NULL DEFAULT 0,
    `active` int(1) NOT NULL DEFAULT 1,
    `allow_html` int(1) NOT NULL DEFAULT 1,
    `allow_mybb` int(1) NOT NULL DEFAULT 1,
    `allow_img` int(1) NOT NULL DEFAULT 1,
    `allow_video` int(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

  $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "application_ucp_userfields` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `value` varchar(10000) NOT NULL DEFAULT '',
  `fieldid` int(10) NOT NULL,
  UNIQUE KEY `uid_fieldidid` (`uid`,`fieldid`),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

  $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "application_ucp_management` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `tid` int(10) NOT NULL,
  `uid_mod` int(10) NOT NULL DEFAULT 0,
  `submission_time` datetime NOT NULL DEFAULT NOW(),
  `modcorrection_time` datetime,
  `usercorrection_time` datetime,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

  //Verlängerung
  $db->add_column("users", "aucp_extend", "INT(10) NOT NULL DEFAULT 0");

  // Admin Einstellungen
  $setting_group = array(
    'name' => 'application_ucp',
    'title' => 'Steckbrief im UCP',
    'description' => 'Allgemeine Einstellungen für die Steckbriefe im UCP?',
    'disporder' => 7, // The order your setting group will display
    'isdefault' => 0
  );
  $gid = $db->insert_query("settinggroups", $setting_group);

  $setting_array = array(
    'application_ucp_applicants' => array(
      'title' => 'Bewerbergruppe',
      'description' => 'Wähle deine Gruppe für Bewerber aus.',
      'optionscode' => 'groupselectsingle',
      'value' => '2', // Default
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
      'description' => 'Soll die Anzeige automatisch gebaut werden? Wenn ja, werden alle Felder mit einer Variable ($aucp_fields) ausgegeben. Wenn nein, müssen die Variablen für die Felder selbst eingefügt werden. (Bennenung findet ihr in der Übersicht).',
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
  );

  foreach ($setting_array as $name => $setting) {
    $setting['name'] = $name;
    $setting['gid'] = $gid;
    $db->insert_query('settings', $setting);
  }
  rebuild_settings();

  //Templates erstellen:

  $template[0] = array(
    "title" => 'application_ucp_index',
    "template" => '<div class="red_alert">
      {$application_ucp_index_modbit}
      {$application_ucp_index_bit}
    </div>
      ',
    "sid" => "-1",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[1] = array(
    "title" => 'application_ucp_index_bit',
    "template" => '<div class="aucp_indexuser">{$message}
      </div>
      ',
    "sid" => "-1",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[2] = array(
    "title" => 'application_ucp_mods',
    "template" => '<html>
      <head>
      <title>{$mybb->settings[\\\'bbname\\\']} - Steckbriefübersicht</title>
      {$headerinclude}
      </head>
      <body>
      {$header}
      <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder tfixed">
      <tr>
      <td class="trow1"><h2>fertige Steckbriefe</h2><br/>
          <table>
          <tr>
            <td class="bl-tdhead">Charakter</td>
            <td class="bl-tdhead">Steckbrief</td>
            <td class="bl-tdhead">In Bearbeitung? </td>
            <td class="bl-tdhead">eingereicht/korrigiert am</td>
          </tr>
            {$application_ucp_mods_readybit}
          </table>
        </td>
      </tr>
        
          <tr>
      <td class="trow1"><h2>Steckbriefe die vom User korrigiert werden müssen</h2><br/>
        <table>
          <tr>
            <td class="bl-tdhead">Charakter</td>
            <td class="bl-tdhead">Steckbrief</td>
            <td class="bl-tdhead">Verantwortlicher angefordert? </td>
            <td class="bl-tdhead">Fristende </td>
          </tr>
            {$application_ucp_mods_users}
          </table>
        </td>
      </tr>
        <tr>
      <td class="trow1"><h2>noch nicht eingereichte Steckbriefe</h2><br/>
          <table>
          <tr>
            <td class="bl-tdhead">Link zum Profil</td>
            <td class="bl-tdhead">Registriert seit</td>
            <td class="bl-tdhead">Letzte Aktivität</td>
            <td class="bl-tdhead">Fristende </td>
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
    "sid" => "-1",
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
    "sid" => "-1",
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
              <option value="">Keine sekundäre Gruppe</option>
              {$additionalgroups_bit}
          </select>
      </div>
        <div class="aucp_showthread-wob__item">
        <input type="submit" name="wob" value="{$lang->application_ucp_wobbtn}" class="button" />
      </div>
      </div>    
  </form>',
    "sid" => "-1",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  $template[5] = array(
    "title" => 'application_ucp_ucp_main',
    "template" => '<html>
      <head>
      <title>{$mybb->settings[\\\'bbname\\\']} - Steckbrief ausfüllen</title>
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
          {$fields}
        <div align="center" class="applucp-con__item applucp-buttons">
         {$extend_button}
         <input type="submit" class="button" name="application_ucp_save" value="{$lang->application_ucp_save}" />
         <input type="submit" class="button" name="application_ucp_ready" value="{$lang->application_ucp_readybtn}"/>
        </div>
        </div>
      </td>
      </tr>
      </table>
      </form>
      {$application_ucp_js}
      {$footer}
        
      </body>
      </html>',
    "sid" => "-1",
    "version" => "1.0",
    "dateline" => TIME_NOW
  );

  foreach ($template as $row) {
    $db->insert_query("templates", $row);
  }

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
    grid-template-columns: 1fr 80%;
    margin: auto;
    gap: 19px 15px;
}

.app_ucp_label {
    font-weight: 600;
    text-align: right;
}

.applucp-con__item.applucp-buttons {
    grid-column: 1 / -1;
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

  if ($db->table_exists("application_ucp_useralerts")) {
    $db->drop_table("application_ucp_useralerts");
  }

  if ($db->field_exists("aucp_extend", "users")) {
    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "users DROP aucp_extend");
  }

  // Einstellungen entfernen
  $db->delete_query("settings", "name LIKE 'application_ucp%'");
  $db->delete_query('settinggroups', "name = 'application_ucp'");

  // Templates löschen
  $db->delete_query("templates", "title LIKE 'application_ucp%'");

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

  //postbit classic 
  find_replace_templatesets("postbit_classic", "#" . preg_quote('{$post[\'user_details\']}') . "#i", '{$post[\'user_details\']}{$post[\'aucp_fields\']}');
  //postbit
  find_replace_templatesets("postbit", "#" . preg_quote('{$post[\'user_details\']}') . "#i", '{$post[\'user_details\']}{$post[\'aucp_fields\']}');

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
  find_replace_templatesets("postbit", "#" . preg_quote('{$post[\'aucp_fields\']}') . "#i", '');
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
function application_ucp_admin_config_menu(&$sub_menu)
{
  global $mybb, $lang;
  $lang->load('application_ucp');

  $sub_menu[] = [
    "id" => "application_ucp",
    "title" => $lang->application_ucp_menu,
    "link" => "index.php?module=config-application_ucp"
  ];
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
    if ($mybb->input['action'] == "" || !isset($mybb->input['action'])) {
      $page->add_breadcrumb_item($lang->application_ucp_name);
      $page->output_header($lang->application_ucp_name);

      // submenü erstellen - dafür wurde eine Funktion gebastelt.
      $sub_tabs = application_ucp_do_submenu();
      $page->output_nav_tabs($sub_tabs, 'application_ucp');
      // fehleranzeige
      if (isset($errors)) {
        $page->output_inline_error($errors);
      }

      //Hier erstellen wir jetzt eine Übersicht über unsere ganzen Felder
      //erst brauchen wir einen Container und ein Formular - für delete, die Sortierung etc.
      $form = new Form("index.php?module=config-application_ucp", "post");
      $form_container = new FormContainer($lang->application_ucp_overview);
      $form_container->output_row_header($lang->application_ucp_overview_appl);
      $form_container->output_row_header($lang->application_ucp_overview_sort);
      $form_container->output_row_header("<div style=\"text-align: center;\">" . $lang->application_ucp_overview_opt . "</div>");
      //Alle existierenden Felder bekommen
      $get_fields = $db->simple_select("application_ucp_fields", "*", "", ["order_by" => 'sorting']);
      while ($field = $db->fetch_array($get_fields)) {
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
        if ($field['dependency']) {
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

        if ($field['postbit'] || $field['profile']) {
          $view = "";
          if ($field['postbit'] && $mybb->settings['application_ucp_postbit_view'] == 0) {
            $view_postbit = "<ul>
            <li><b>Anzeige im Postbit:</b> </li>>
            <li>Label & Value: &#x007B;&dollar;post['labelvalue_{$field['fieldname']}']&#x007D;
            <li>Label: &#x007B;&dollar;post['label_{$field['fieldname']}']&#x007D;
            <li>Value: &#x007B;&dollar;post['value_{$field['fieldname']}']&#x007D;	</ul>";
          } else {
            $view_postbit = "<ul>
            <li><b>Anzeige im Postbit:</b> automatisch</li>
            </uL>";
          }
          if ($field['profile'] && $mybb->settings['application_ucp_profile_view'] == 0) {
            $view_profile = "<ul>
            <li><b>Anzeige im Profil:</b> </li>
            <li>Label & Value: &#x007B;&dollar;fields['labelvalue_{$field['fieldname']}']&#x007D;
            <li>Label: &#x007B;&dollar;fields['label_{$field['fieldname']}']&#x007D;
            <li>Value: &#x007B;&dollar;fields['value_{$field['fieldname']}']&#x007D;	</ul>";
          } else {
            $view_profile = "<ul>
            <li><b>Anzeige im Profile:</b> automatisch</li>
            </ul>";
          }
          $view .= $view_postbit . $view_profile;
        } else {
          $view = "";
        }

        //spalte name und Infos
        $form_container->output_cell($activ_start . "<strong>" . htmlspecialchars_uni($field['label']) . "</strong> <br />
        Typ: {$field['fieldtyp']} | Name (Identifikator): {$field['fieldname']} | Label: {$field['label']} | 
        {$editable} 
        {$options}
        {$mandatory}
        {$dependency}
        {$view}
        <br/>Alle Elemente(textfeld, das zugehörige label etc) bekommen die Klasse \"{$field['fieldname']}\", die zum stylen verwenden werden kann.
        " . $activ_end);

        //spalte reihenfolge
        $form_container->output_cell($form->generate_text_box('sorting', $field['sorting'], array('style' => "width: 25px;")));

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
      $form->end();
      $page->output_footer();
      die();
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
        // Name darf keine Sonderzeichen enthalten
        if (!preg_match("#^[a-zA-Z0-9]+$#", $mybb->input['fieldname'])) {
          $errors[] = $lang->application_ucp_err_name_sonder;
        }
        // Label muss ausgefüllt sein
        if (empty($mybb->input['fieldlabel'])) {
          $errors[] = $lang->application_ucp_err_label;
        }
        // Feldtyp muss ausgewählt sein
        if (empty($mybb->input['fieldtyp'])) {
          $errors[] = $lang->application_ucp_err_fieldtyp;
        }
        // Feldtyp muss ausgewählt sein
        if (empty($mybb->input['fieldtyp'])) {
          $errors[] = $lang->application_ucp_err_fieldtyp;
        }

        // fieldoptions muss bei folgenden ausgefüllt sein
        if (
          $mybb->input['fieldtyp'] == "select" ||
          $mybb->input['fieldtyp'] == "select_multiple" ||
          $mybb->input['fieldtyp'] == "checkbox" ||
          $mybb->input['fieldtyp'] == "radio"
        ) {
          if (empty($mybb->input['fieldoptions'])) {
            $errors[] = $lang->application_ucp_err_fieldoptions;
          }
        }
        // Feldtyp muss ausgewählt sein
        if (empty($mybb->input['fieldtyp'])) {
          $errors[] = $lang->application_ucp_err_fieldtyp;
        }

        // Wurde eine Abhängigkeit ausgewählt?
        if ($mybb->input['dependency'] != "none") {
          //Abhängigkeitswert wurde leer gelasse
          if (empty($mybb->input['dependency_value'])) {
            $errors[] = $lang->application_ucp_err_dependency_value_empty;
          }
          //Falscher Abhängigkeitswert
          //wir brauchen erst die options des Felds von dem es abhängig ist
          $get_dep = $db->fetch_field($db->simple_select("application_ucp_fields", "options", "fieldname = '{$mybb->input['dependency']}'"), "options");
          // wir prüfen ob die Options den angegebenen Wert enthält. 
          if (strpos($get_dep, $mybb->input['dependency_value']) === false) {
            //gibt keine Option mit diesem Wert
            $errors[] = $lang->application_ucp_err_dependency_value_wrong;
          }
        }

        // wenn es keine Fehler gibt, speichern
        if (empty($errors)) {
          $insert = [
            "fieldname" => $db->escape_string($mybb->input['fieldname']),
            "fieldtyp" => $db->escape_string($mybb->input['fieldtyp']),
            "label" => $db->escape_string($mybb->input['fieldlabel']),
            "options" => $db->escape_string($mybb->input['fieldoptions']),
            "editable" => intval($mybb->input['fieldeditable']),
            "mandatory" => intval($mybb->input['fieldmandatory']),
            "dependency" => $db->escape_string($mybb->input['dependency']),
            "dependency_value" => $db->escape_string($mybb->input['dependency_value']),
            "postbit" => intval($mybb->input['fieldpostbit']),
            "profile" => intval($mybb->input['fieldprofile']),
            "template" => $db->escape_string($mybb->input['fieldtemplate']),
            "sorting" => intval($mybb->input['fieldsort']),
            "allow_html" => intval($mybb->input['fieldhtml']),
            "allow_mybb" => intval($mybb->input['fieldmybb']),
            "allow_img" => intval($mybb->input['fieldimg']),
            "allow_video" => intval($mybb->input['fieldvideo']),
          ];
          $db->insert_query("application_ucp_fields", $insert);
          flash_message($lang->application_ucp_success, 'success');
          admin_redirect("index.php?module=config-application_ucp");
          die();
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
      // Welche Auswahlmöglichkeiten an Feldtypen
      $select = array(
        "text" => "Textfeld",
        "textarea" => "Textarea",
        "select" => "Select",
        "select_multiple" => "Select Mehrfachauswahl",
        "checkbox" => "Checkbox",
        "radio" => "Radiobuttons",
        "date" => "Datum",
        "datetime-local" => "Datum und Uhrzeit"
      );

      //Formular bauen 
      $form = new Form("index.php?module=config-application_ucp&amp;action=application_ucp_add", "post", "", 1);
      $form_container = new FormContainer($lang->application_ucp_formname);
      //name des felds
      $form_container->output_row(
        $lang->application_ucp_add_name,
        $lang->application_ucp_add_name_descr,
        $form->generate_text_box('fieldname', "")
      );
      //beschreibung/anzeige des Felds
      $form_container->output_row(
        $lang->application_ucp_add_fieldlabel,
        $lang->application_ucp_add_fieldlabel_descr,
        $form->generate_text_box('fieldlabel', "")
      );
      //Typ des Felds
      $form_container->output_row(
        $lang->application_ucp_add_fieldtyp,
        $lang->application_ucp_add_fieldtyp_descr,
        $form->generate_select_box('fieldtyp', $select, array(), array('id' => 'fieldtype'))
      );
      //Auswahloptionen 
      $form_container->output_row(
        $lang->application_ucp_add_fieldoptions,
        $lang->application_ucp_add_fieldoptions_descr,
        $form->generate_text_box('fieldoptions', "")
      );
      //pflichtfeld
      $form_container->output_row(
        $lang->application_ucp_add_fieldmandatory,
        $lang->application_ucp_add_fieldmandatory_descr,
        $form->generate_yes_no_radio('fieldmandatory', "1")
      );
      //editierbar
      $form_container->output_row(
        $lang->application_ucp_add_fieldeditable,
        $lang->application_ucp_add_fieldeditable_descr,
        $form->generate_yes_no_radio('fieldeditable', "0")
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
        $form->generate_select_box('dependency', $select_dep, array("id" => "sel_dep"))
      );
      //von welchem wert ist die Abhängigkeit abhängig?
      $form_container->output_row(
        $lang->application_ucp_add_fielddependencyval,
        $lang->application_ucp_add_fielddependencyval_descr,
        $form->generate_text_box('dependency_value', "")
      );
      //Anzeige im postbit?
      $form_container->output_row(
        $lang->application_ucp_add_fieldpostbit,
        $lang->application_ucp_add_fieldpostbit_descr,
        $form->generate_yes_no_radio('fieldpostbit', "1")
      );
      //anzeige im profil
      $form_container->output_row(
        $lang->application_ucp_add_fieldprofile,
        $lang->application_ucp_add_fieldprofile_descr,
        $form->generate_yes_no_radio('fieldprofile', "1")
      );
      //Vorlage im Feld? 
      $form_container->output_row(
        $lang->application_ucp_add_fieldtemplate,
        $lang->application_ucp_add_fieldtemplate_descr,
        $form->generate_text_area('fieldtemplate', "")
      );
      //html
      $form_container->output_row(
        $lang->application_ucp_add_fieldhtml,
        $lang->application_ucp_add_fieldhtml_descr,
        $form->generate_yes_no_radio('fieldhtml', "0")
      );
      //mybb code
      $form_container->output_row(
        $lang->application_ucp_add_fieldmybb,
        $lang->application_ucp_add_fieldmybb_descr,
        $form->generate_yes_no_radio('fieldmybb', "0")
      );
      // img
      $form_container->output_row(
        $lang->application_ucp_add_fieldimg,
        $lang->application_ucp_add_fieldimg_descr,
        $form->generate_yes_no_radio('fieldimg', "0")
      );
      // video
      $form_container->output_row(
        $lang->application_ucp_add_fieldvideo,
        $lang->application_ucp_add_fieldvideo_descr,
        $form->generate_yes_no_radio('fieldvideo', "0")
      );
      //anzeige reihenfolge
      $form_container->output_row(
        $lang->application_ucp_add_fieldsort,
        $lang->application_ucp_add_fieldsort_descr,
        $form->generate_numeric_field('fieldsort', "1")
      );

      $form_container->end();
      $buttons[] = $form->generate_submit_button($lang->application_ucp_save);
      $form->output_submit_wrapper($buttons);
      $form->end();
      $page->output_footer();

      die();
    }

    //Steckbriefe der User verwalten
    if ($mybb->input['action'] == "application_ucp_manageusers") {
      $page->add_breadcrumb_item($lang->application_ucp_manageusers);
      $page->output_header($lang->application_ucp_name);
      $sub_tabs = application_ucp_do_submenu();
      $page->output_nav_tabs($sub_tabs, 'application_ucp_manageusers');
      if (isset($errors)) {
        $page->output_inline_error($errors);
      }

      //alle registrierten User bekommen
      $get_users = $db->simple_select("users", "*");
      $form = new Form("index.php?module=config-application_ucp&action=application_ucp_manageusers", "post");
      $form_container = new FormContainer($lang->application_ucp_manageusers_dscr);
      $form_container->output_row_header($lang->application_ucp_manageusers_all);

      //Bewerber oder angenommen?
      while ($user = $db->fetch_array($get_users)) {
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
    if ($mybb->input['action'] == "application_ucp_manageusers_user") {
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
        $get_input = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "uid = {$uid} AND fieldid = {$field['id']}"), "value");
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
            $get_options[$option] = $option;
          }
          $form_container->output_row(
            $label,
            $descr,
            $form->generate_select_box($field['id'], $get_options, $get_input, array('checked' => "bla", 'id' => 'fieldtype'))
          );
        }
        if ($field['fieldtyp'] == "select_multiple") {
          $options = explode(",", $field['options']);
          $get_options = array("keineangabe" => "keine Angabe");
          foreach ($options as $option) {
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
            $get_options[$option] = $option;
          }
          foreach ($options as $option) {
            if (strpos($get_input, trim($option)) !== false) {
              $check = 1;
            } else {
              $check = 0;
            }
            $checkboxes .= $form->generate_check_box($field['id'], $option, $option, array('checked' => $check)) . "<br/>";
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
        //für date gibt es keine mybbfunktion, also bauen wir selber
        if ($field['fieldtyp'] == "date" or $field['fieldtyp'] == "date-local") {
          $form_container->output_row(
            $label,
            $descr,
            "<input type=\"{$field['fieldtyp']}\" name=\"{$field['id']}\" value={$get_input} />"
          );
        }
      }

      $form_container->end();
      $buttons[] = $form->generate_submit_button($lang->application_ucp_save);
      $form->output_submit_wrapper($buttons);
      $form->end();
      $page->output_footer();
    }

    //Editieren eines Felds
    if ($mybb->input['action'] == "application_ucp_edit") {
      //hier wird das speichern des zu editierenden Feldes gemanaged
      //erst wieder Fehler abfangen
      $fieldid = $mybb->input['fieldid'];
      if (empty($mybb->input['fieldname'])) {
        $errors[] = $lang->application_ucp_err_name;
      }
      // Name darf keine Sonderzeichen enthalten
      if (!preg_match("#^[a-zA-Z0-9]+$#", $mybb->input['fieldname'])) {
        $errors[] = $lang->application_ucp_err_name_sonder;
      }
      // Label muss ausgefüllt sein
      if (empty($mybb->input['fieldlabel'])) {
        $errors[] = $lang->application_ucp_err_label;
      }
      // Feldtyp muss ausgewählt sein
      if (empty($mybb->input['fieldtyp'])) {
        $errors[] = $lang->application_ucp_err_fieldtyp;
      }
      // Feldtyp muss ausgewählt sein
      if (empty($mybb->input['fieldtyp'])) {
        $errors[] = $lang->application_ucp_err_fieldtyp;
      }

      // fieldoptions muss bei folgenden ausgefüllt sein
      if (
        $mybb->input['fieldtyp'] == "select" ||
        $mybb->input['fieldtyp'] == "select_multiple" ||
        $mybb->input['fieldtyp'] == "checkbox" ||
        $mybb->input['fieldtyp'] == "radio"
      ) {
        if (empty($mybb->input['fieldoptions'])) {
          $errors[] = $lang->application_ucp_err_fieldoptions;
        }
      }
      // Feldtyp muss ausgewählt sein
      if (empty($mybb->input['fieldtyp'])) {
        $errors[] = $lang->application_ucp_err_fieldtyp;
      }

      // Wurde eine Abhängigkeit ausgewählt?
      if ($mybb->input['dependency'] != "none") {
        //Abhängigkeitswert wurde leer gelasse
        if (empty($mybb->input['dependency_value'])) {
          $errors[] = $lang->application_ucp_err_dependency_value_empty;
        }
        //Falscher Abhängigkeitswert
        //wir brauchen erst die options des Felds von dem es abhängig ist
        $get_dep = $db->fetch_field($db->simple_select("application_ucp_fields", "options", "fieldname = '{$mybb->input['dependency']}'"), "options");
        // wir prüfen ob die Options den angegebenen Wert enthält. 
        if (strpos($get_dep, $mybb->input['dependency_value']) === false) {
          //gibt keine Option mit diesem Wert
          $errors[] = $lang->application_ucp_err_dependency_value_wrong;
        }
      }
      // dependency_value
      // wenn es keine Fehler gibt, speichern
      if (empty($errors)) {
        $update = [
          "fieldname" => $db->escape_string($mybb->input['fieldname']),
          "fieldtyp" => $db->escape_string($mybb->input['fieldtyp']),
          "label" => $db->escape_string($mybb->input['fieldlabel']),
          "options" => $db->escape_string($mybb->input['fieldoptions']),
          "editable" => intval($mybb->input['fieldeditable']),
          "mandatory" => intval($mybb->input['fieldmandatory']),
          "dependency" => $db->escape_string($mybb->input['dependency']),
          "dependency_value" => $db->escape_string($mybb->input['dependency_value']),
          "postbit" => intval($mybb->input['fieldpostbit']),
          "profile" => intval($mybb->input['fieldprofile']),
          "template" => $db->escape_string($mybb->input['fieldtemplate']),
          "sorting" => intval($mybb->input['fieldsort']),
          "allow_html" => intval($mybb->input['fieldhtml']),
          "allow_mybb" => intval($mybb->input['fieldmybb']),
          "allow_img" => intval($mybb->input['fieldimg']),
          "allow_video" => intval($mybb->input['fieldvideo']),
        ];
        $db->update_query("application_ucp_fields", $update, "id = {$fieldid}");
        flash_message($lang->application_ucp_success, 'success');
        admin_redirect("index.php?module=config-application_ucp");
        die();
      }

      //Das Formular erstellen
      $page->add_breadcrumb_item($lang->application_ucp_editfieldtype);
      //Header und Navigation
      $page->output_header($lang->application_ucp_editfieldtype);
      $sub_tabs = application_ucp_do_submenu();
      $page->output_nav_tabs($sub_tabs, 'application_ucp');
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

      $select = array(
        "text" => "Textfeld",
        "textarea" => "Textarea",
        "select" => "Select",
        "select_multiple" => "Select Mehrfachauswahl",
        "checkbox" => "Checkbox",
        "radio" => "Radiobuttons",
        "date" => "Datum",
        "datetime-local" => "Datum und Uhrzeit"
      );
      $form_container->output_row(
        $lang->application_ucp_add_fieldtyp,
        $lang->application_ucp_add_fieldtyp_descr,
        $form->generate_select_box('fieldtyp', $select, array($field_data['fieldtyp']), array('id' => 'fieldtype'))
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

  $lang->load('application_ucp');

  $thisuser = $mybb->user['uid'];
  if ($mybb->input['action'] != "application_ucp") {
    return false;
  }
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

  //UCP bauen
  // alle aktiven Felder holen
  $get_fields = $db->simple_select("application_ucp_fields", "*", "active = 1", array('order_by' => 'sorting'));

  $fields = "";
  //start für javascript das wir brauchen
  $application_ucp_js = "<script> $(function() {";

  //felder durchgehen
  while ($type = $db->fetch_array($get_fields)) {
    //ist das Feld editierbar? -> wenn mitglied berücksichtigen
    if ($member &&  $type['editable'] == 0) {
      $readonly = "readonly"; //für textfelder/textarea
      $disabled = "disabled"; //selects / checkboxen etc.
    } else { //ist Bewerber, darf alle Felder editieren
      $readonly = "";
      $disabled = "";
    }
    //gibt es schon inhalte für die felder? 
    $get_value = $db->fetch_array($db->simple_select("application_ucp_userfields", "*", "uid = {$thisuser} AND fieldid={$type['id']}"));
    //wenn nein, gibt es eine vorlage für das feld?
    if ($type['template'] != "") {
      if ($get_value['value'] != "") {
        //Es gibt eine Vorlage und der user hat das Feld noch nicht bearbeitet
        $get_value['value'] = $type['template'];
      } else {
        //Es gibt zwar eine Vorlage, aber der user hat schon bearbeitet, dann den wert laden, nicht die vorlage
        $get_value['value'] = $get_value['value'];
      }
    }
    //handelt es sich um ein Pflichtfeld
    if ($type['mandatory']) {
      $requiredstar = "<span class\"app_ucp_star\">" . $lang->application_ucp_mandatory . "</span>"; //markierung mit sternchen ux und so :D
      $required = "required"; //feld muss ausgefüllt werden
    } else { //kein pflichtfeld
      $requiredstar = "";
      $required = "";
    }
    //prüfen ob Feld initial versteckt sein soll -> wenn es von einem anderen abhängig ist
    if ($type['dependency'] != "none") {
      $hide = true;
      //javascript dynamisch zusammen bauen.
      //wenn dependency, von welchem feld und welchem wert? Entsprechend element ein oder ausblenden.
      $application_ucp_js .= "
        $('#" . $type['fieldname'] . "').hide();
        $('#label_" . $type['fieldname'] . "').hide();  
        if($('#" . $type['dependency'] . "').val() == '" . $type['dependency_value'] . "') {
          $('#hideinfo_" . $type['fieldname'] . "').val('true');
          $('#" . $type['fieldname'] . "').show(); 
          $('#label_{$type['fieldname']}').show(); 
        }
        if($('#" . $type['dependency'] . ":checked').val() == '" . $type['dependency_value'] . "') {
          $('#hideinfo_" . $type['fieldname'] . "').val('true');
          $('#" . $type['fieldname'] . "').show(); 
          $('#label_{$type['fieldname']}').show(); 
        }
        $('#" . $type['dependency'] . "').change(function(){
          var inputtyp = $('#" . $type['dependency'] . ":checked').attr('type');
            if( inputtyp == 'checkbox' || inputtyp == 'radio') {
                var checked = ':checked';
            } else {
              var checked = '';
            }
          
            if($('#" . $type['dependency'] . "'+checked+'').val() == '" . $type['dependency_value'] . "') {
              console.log($('#" . $type['dependency'] . "'+checked+'').val());
                $('#hideinfo_" . $type['fieldname'] . "').val('true');
                $('#" . $type['fieldname'] . "').show(); 
                $('#label_{$type['fieldname']}').show(); 
                // if(document.getElementById('label_{$type['fieldname']}').textContent.includes('*')) {
                  // $('#" . $type['fieldname'] . "').addAttr('required');
                // }
            } else {
                $('#hideinfo_" . $type['fieldname'] . "').val('false');
                $('#" . $type['fieldname'] . "').hide(); 
                $('#label_" . $type['fieldname'] . "').hide(); 
                // $('#" . $type['fieldname'] . "').removeAttr('required');
            } 
        });
      ";
    } else { //keine abhängigkeit
      $hidden = "";
      $hide = false;
    }
    //was für einen feldtyp haben wir
    $typ = $type['fieldtyp'];

    //Felder bauen
    //Das Feld ist initial versteckt, das brauchen wir um vorm speichern zu prüfen ob der inhalt gespeichert werden soll
    if ($hide == true) {
      $fields .= "<input type=\"hidden\" id=\"hideinfo_{$type['fieldname']}\" name=\"hideinfo_{$type['id']}\" value=\"false\" />";
    }

    //Feld ist einfaches Textfeld, Datum oder Datum mit Zeit
    if ($typ == "text" || $typ == "date" || $typ == "datetime-local") {
      $fields .= "<label  class=\"app_ucp_label\" for=\"{$type['fieldname']}\" style=\"{$hidden}\" id=\"label_{$type['fieldname']}\">{$type['label']}{$requiredstar}:</label> 
      <input type=\"{$typ}\" class=\"{$type['fieldname']}\" value=\"{$get_value['value']}\" name=\"{$type['id']}\" id=\"{$type['fieldname']}\" style=\"{$hidden}\" {$required} {$readonly}/>
      ";
    }
    //Feld ist Textarea
    else if ($typ == "textarea") {
      $fields .= "<label for=\"{$type['fieldname']}\" class=\"app_ucp_label\" style=\"{$hidden}\" id=\"label_{$type['fieldname']}\">{$type['label']}{$requiredstar}:</label>
      <textarea class=\"{$type['fieldname']}\" name=\"{$type['id']}\"  id=\"{$type['fieldname']}\" rows=\"4\" cols=\"50\" style=\"{$hidden}\" {$readonly} {$required} >{$get_value['value']}</textarea>";
    }
    //Feld ist Select
    else if ($typ == "select" || $typ == "select_multiple") {
      //auswählbare Optionen holen und in array speichern
      $options = explode(",", $type['options']);
      $selects = "";

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
        if ($mult_flag) {
          if (in_array($option, $getselects)) {
            $selected = "selected=\"selected\"";
          } else {
            $selected = "";
          }
        } else {
          if ($option == $getselects) {
            $selected = "selected=\"selected\"";
          } else {
            $selected = "";
          }
        }
        $option = trim($option); //leertasten vorne und hinten rauswerfen
        $selects .= "<option value=\"{$option}\" {$selected} >{$option}</option>";
      }

      //hier bauen wir das feld und packen die optionen rein
      $fields .= " <label class=\"app_ucp_label {$type['fieldname']}\" for=\"{$type['fieldname']}\"  style=\"{$hidden}\" id=\"label_{$type['fieldname']}\">
      {$type['label']}{$requiredstar}:</label>
      <select name=\"{$type['id']}[]\" id=\"{$type['fieldname']}\" style=\"{$hidden}\"  {$multiple} {$required} {$disabled}>
      {$selects} 
      </select>";
      // Variable leeren
      $selects = "";
    }
    //Feld ist Checkbox oder Radio
    else if ($typ == "checkbox" || $typ == "radio") {

      $inner = "";
      $options = explode(",", $type['options']);

      $getval = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "fieldid = {$type['id']} AND uid = {$thisuser}"), "value");

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
        <input type=\"{$typ}\" class=\"{$type['fieldname']}_check\" id=\"{$type['fieldname']}\" name=\"{$type['id']}[]\" value=\"{$option}\" {$checked} {$required} {$disabled} \> 
        <label for=\"{$type['fieldname']}\">{$option}</label><br/>";
      }
      // dann hier das außenrum
      $fields .= "
      <label class=\"app_ucp_label {$type['fieldname']}\" style=\"{$hidden}\" id=\"label_{$type['fieldname']}\">
      {$type['label']}{$requiredstar}:</label>
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
        checkboxes.change(function(){
        if($('." . $type['fieldname'] . "_check:checked').length > 0) {
          checkboxes.removeAttr('required');
        } else {
          checkboxes.attr('required', 'required');
        }
        });
        ";
      }
    }
  }
  //ende Javascript
  $application_ucp_js .= "});</script>";

  //admin einstellungen - Felder für Steckbrief thread
  $setting_wanted = $mybb->settings['application_ucp_stecki_wanted'];
  $setting_affected = $mybb->settings['application_ucp_stecki_affected'];

  //Es soll asugewählt werden können, ob es sich um ein Gesuch handelt
  if ($setting_wanted) {
    //Die Angabe ist Pflicht
    $requiredstar = "<span class=\"app_ucp_star\">" . $lang->application_ucp_mandatory . "</span>";
    //testen ob schon einmal ausgefüllt und entsprechend die Checkbox vorauswählen oder nicht
    $get_checked = $db->simple_select("application_ucp_userfields", "*", "uid = {$mybb->user['uid']} AND fieldid = -1");
    $get_checked_row = $db->num_rows($get_checked);
    $get_checked_data = $db->fetch_array($get_checked);
    if ($get_checked_row > 0) {
      if ($get_checked_data['value'] == "1") {
        $checked_yes = "CHECKED";
        $checked_no = "";
      } else {
        $checked_no = "CHECKED";
        $checked_yes = "";
      }
    } else {
      $checked = "";
    }
    //Daten für URL
    $get_url = $db->simple_select("application_ucp_userfields", "*", "uid = {$mybb->user['uid']} AND fieldid = -2");
    $get_url_data = $db->fetch_array($get_url);
    $wantedurl = $get_url_data['value'];

    //Die Checkboxen
    $inner .= "
        <input type=\"radio\" class=\"wanted_check\" id=\"wanted\" name=\"-1\" value=\"1\" {$checked_yes} \> 
        <label for=\"wanted\">Ja</label><br/>
        <input type=\"radio\" class=\"wanted_check\" id=\"wanted\" name=\"-1\" value=\"0\" {$checked_no} \> 
        <label for=\"wanted\">Nein</label><br/>";
    //dann hier das außenrum
    $fields .= "
      <label class=\"app_ucp_label wanted\"  id=\"label_wanted\">
      Ist der Charakter ein Gesuch?{$requiredstar}</label>
      <div class=\"application_ucp_checkboxes\"  id=\"boxwanted\">
        {$inner}
        <input type=\"url\" class=\"wanted_url\" placeholder=\"url zum Gesuch\" id=\"wanted_url\" name=\"-2\" value=\"{$wantedurl}\" \>
      </div>
      ";
  }
  if ($setting_affected) {
    //Wenn eingetragen werden soll ob andere Mitglieder betroffen sind
    $requiredstar = "";
    //input basteln
    //Daten für affected
    $get_affected = $db->simple_select("application_ucp_userfields", "*", "uid = {$mybb->user['uid']} AND fieldid = '-3'");
    $get_affected_data = $db->fetch_array($get_affected);
    $affected_data = $get_affected_data['value'];
    $fields .= " 
    <label class=\"app_ucp_label\" for=\"affected\" id=\"label_affected\">Betroffene Mitglieder{$requiredstar}:</label> 
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
      $extend_button = "<input type=\"submit\" class=\"button\" name=\"application_ucp_extend\" value=\"" . $lang->application_ucp_extbtn . "\"/>";
    }
  }

  //Steckbrief speichern, aber nicht abgeben
  if ($mybb->input['application_ucp_save']) {
    //Hier speichern wir, was eingetragen wurde
    //wir bekommen ein array mit allen werten
    $fields = $mybb->input;
    //Hilfsunktion - wir übergeben den input und handeln da alles, weil wir das gleiche so oft machen müssen
    application_ucp_savefields($fields, $mybb->user['uid']);
    redirect("usercp.php?action=application_ucp");
  }

  //Steckbrief speichern und zur Korrektur geben.
  if ($mybb->input['application_ucp_ready']) {
    // alle Inputs
    $fields = $mybb->input;

    //Schauen ob es schon einen eintrag im managenent gibt
    $fetch_management = $db->simple_select("application_ucp_management", "*", "uid = {$mybb->user['uid']}");

    //wenn ja, ist es die Verbesserung nach einer Korrektur
    if ($db->num_rows($fetch_management) > 0) {
      //die daten
      $managmentdata = $db->fetch_array($fetch_management);
      //Alte betroffene User suchen
      $old_affected = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "uid = {$mybb->user['uid']} AND fieldid='-3' AND value !=''"), "");
      //wurde was am feld geändert?
      if ($fields['-3'] != $old_affected) {
        //es wurden neue Betroffene hinzugefügt und müssen noch informiert werden
        //wir brauchen arrays
        $array_new = explode(",", $fields['-3']);
        $old_field = explode(",", $old_affected);
        foreach ($array_new as $user) {
          //user war nicht im alten, also muss er informiert werden
          if (!in_array($user, $old_field)) {
            $touid = get_user_by_username($user);
            application_ucp_affected_alert($fetch_management['uid'], $touid, $fetch_management['tid'], 1);
          }
        }
      }
      // speichern
      application_ucp_savefields($fields, $mybb->user['uid']);
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
      redirect("usercp.php?action=application_ucp");
    } else { //Der Steckbrief wird das erste Mal eingereicht


      //felder abspeichern
      application_ucp_savefields($fields, $mybb->user['uid']);

      //Wir wollen einen Thread erstellen, wenn der Stecki fertig ist und nutzen dafür den Posthandler von MyBB

      //Steckbriefarea holen
      $steckbriefarea = $mybb->settings['application_ucp_steckiarea'];

      //Nachricht zusammenbauen
      //Wir schauen erst noch, ob angegeben wurde, ob der Charakter ein Gesuch ist. 
      $get_wanted = $db->simple_select("application_ucp_userfields", "*", "uid = {$mybb->user['uid']} AND fieldid = -1 AND value = '1'");
      $get_wanted_row = $db->num_rows($get_wanted);
      if ($get_wanted_row) {
        //Daten für URL des Gesuchs
        $get_url_data = $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "uid = {$mybb->user['uid']} AND fieldid = -2"), "value");
        $wanted = "<a href=\"" . $get_url_data . "\">Charakter ist ein Gesuch</a>";
      }

      //Gibt es betroffene User?
      $get_affected = $db->simple_select("application_ucp_userfields", "*", "uid = {$mybb->user['uid']} AND fieldid = -3 AND value != ''");
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
        $affected = $lang->application_ucp_affected . " <br/> {$affectedusers}";
      }

      //Die admin cp message holen und die variable $wanted ersetzen
      $message = str_replace("\$wanted", $wanted, $mybb->settings['application_ucp_stecki_message']);

      //Die Variable affected ersetzen
      $message = str_replace("\$affected", $affected, $message);

      //Den usernamen ersetzen
      $message = str_replace("\$username", $mybb->user['username'], $message);

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
        "subject" => $db->escape_string($mybb->user['username']),
        "uid" => $mybb->user['uid'],
        "username" => $db->escape_string($mybb->user['username']),
        "message" => $message,
        "ipaddress" => $session->packedip,
        "posthash" => $mybb->get_input('posthash')
      );

      if ($pid != '') {
        $new_thread['pid'] = $pid;
      }

      $new_thread['savedraft'] = 0;

      $posthandler->set_data($new_thread);

      // Now let the post handler do all the hard work.
      $valid_thread = $posthandler->validate_thread();

      $post_errors = array();
      // Fetch friendly error messages if this is an invalid thread
      if (!$valid_thread) {
        $post_errors = $posthandler->get_friendly_errors();
      }

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
      //Bis hier (abschnittsweise) kopiert 

      //user informieren
      foreach ($get_affected_names as $name) {
        // Daten des Users bekommen
        $user = get_user_by_username($name);
        //betroffene user informieren
        application_ucp_affected_alert($mybb->user['uid'], $user['uid'], $tid, 0);
      }

      //und jetzt noch einen eintrag in der Management Tabelle
      $insert = array(
        "uid" => $mybb->user['uid'],
        "tid" => $tid
      );
      $db->insert_query("application_ucp_management", $insert);
      //uns zum Thread weiterleiten
      redirect(get_thread_link($tid));
    }
  }

  //Steckbrief speichern und zur Korrektur geben.
  if ($mybb->input['application_ucp_extend']) {
    $update = array(
      "aucp_extend" => '+1',
    );
    $db->write_query("users", $update, "uid = {$mybb->user['uid']}");
  }

  eval("\$application_ucp_ucp_main =\"" . $templates->get("application_ucp_ucp_main") . "\";");
  output_page($application_ucp_ucp_main);
}

/**
 * automatische Anzeige von den Feldern im Profil
 * + Export Steckbrief
 */
$plugins->add_hook("member_profile_end", "application_ucp_showinprofile");
function application_ucp_showinprofile()
{
  global $db, $mybb, $memprofile, $templates, $aucp_fields, $exportbtn, $lang;
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
  //Export des Steckbriefes
  if ($mybb->settings['application_ucp_export'] && $mybb->user['uid'] != 0) {
    $exportbtn = "
    <form action=\"misc.php?action=exp_app\" method=\"post\">
    <input type=\"hidden\" name=\"uid\" value=\"{$mybb->input['uid']}\" id=\"uid\" />
    <input type=\"submit\" name=\"exp_app\" value=\"" . $lang->application_ucp_export . "\" id=\"exp_app\" />
    </form>";
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

$plugins->add_hook("showthread_start", "application_ucp_showthread");
function application_ucp_showthread()
{
  global $lang, $db, $mybb, $templates, $thread, $give_wob;
  //Sprachvariable laden
  $lang->load('application_ucp');

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

  // application_ucp_wobbutton
  eval("\$give_wob .= \"" . $templates->get("application_ucp_wobbutton") . "\";");
}

/**
 * Exportfunktion für Steckbrief
 */
$plugins->add_hook("misc_start", "application_ucp_misc");
function application_ucp_misc()
{
  global $mybb, $db, $templates, $header, $footer, $theme, $headerinclude, $application_ucp_mods, $application_ucp_mods_readybit;

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

    if ($_POST['additionalgroups'] != '') {
      $additionalgroups_string = implode(', ', $mybb->input['additionalgroups']);
    }
    $new_record = array(
      "usergroup" => $newusergroup,
      "additionalgroups" => $additionalgroups_string,
    );
    $db->update_query("users", $new_record, "uid = '$threadauthor'");

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
      // $insert_array = $db->update_query("forums", $new_record, "fid = '$fid'");
      $db->update_query("forums", $new_record, "fid = '$fid'");
    }
    redirect("showthread.php?tid={$posttid}");
  }

  //Steckbrief übernehmen
  if ($mybb->input['action'] == "take_application") {
    //modcorrection time aktualisieren
    $uid = intval($mybb->input['uid']);
    $update = array(
      "modcorrection_time" => date('Y-m-d H:i:s'),
      "uid_mod" => $mybb->user['uid']
    );
    $db->update_query("application_ucp_management", $update, "uid = {$uid}");
    redirect('index.php');
  }

  //Steckbrieffrist verlängern
  if ($mybb->input['action'] == " ext_app") {
    //Steckbrief speichern und zur Korrektur geben.
    $update = array(
      "aucp_extend" => '+1',
    );
    $db->write_query("users", $update, "uid = {$mybb->user['uid']}");
  }

  // Steckbrief als PDF speichern
  if ($mybb->input['action'] == "exp_app" && $mybb->user['uid'] != 0) {
    //Userinformationen bekommen
    $uid = (int)$mybb->input['uid'];
    $user = get_user($uid);

    require(MYBB_ROOT . 'inc/3rdparty/tfpdf.php');
    //PDF aufsetzen
    class FPDF extends tFPDF
    {
      // Footer vom PDF
      function Footer()
      {
        $this->SetY(-15);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 10, 'Seite ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
      }
    }

    $title =  $user['username'];
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->AliasNbPages();
    $pdf->AddFont('Arial', '', 'ARIAL.TTF', true);
    $pdf->SetFont('Arial', 'B', 16);

    $pdf->MultiCell(0, 5, $user['username'], 0, 'C');

    //alle Felder holen:
    $fields = application_ucp_build_view($uid, "profile", "array");

    foreach ($fields as $key => $field) {
      if (substr($key, 0, 10) == "labelvalue") {
        //Label und Value auslesen und in PDF packen
        $y = $y + 15;
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetX(20);
        //die html codes rauswerfen
        $clean = html_entity_decode($field);
        $clean = strip_tags($clean);
        $pdf->Cell(20, $y, $clean);
      }
    }
    $pdf->Output('I', $title . '.pdf');
  }
}

$plugins->add_hook("misc_start", "application_ucp_modoverview");
function application_ucp_modoverview()
{
  global $mybb, $db, $templates, $header, $footer, $theme, $headerinclude, $application_ucp_mods, $application_ucp_mods_readybit;
  if ($mybb->get_input('action', MyBB::INPUT_STRING) == "aplication_mods") {
    // get settings
    $applicantgroup = $mybb->settings['application_ucp_applicants'];
    $app_deadline = $mybb->settings['application_ucp_applicationtime'];
    $app_corr_deadline = $mybb->settings['application_ucp_correctiontime'];
    $mods = $mybb->settings['application_ucp_stecki_mods'];

    // Nur Moderatoren haben Zugriff auf die Seite.
    if (!is_member($mods, $mybb->user['uid'])) {
      error_no_permission();
    }

    // fertige Steckbriefe bekommen (alle wo der Mod quasi etwas tun muss)
    $ready_for_mod = $db->simple_select("application_ucp_management", "*", "(modcorrection_time < usercorrection_time)");
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
    $aucp_mod_steckilink = "";
    $aucp_mod_profillink = "";
    $aucp_mod_modlink = "";
    $aucp_mod_date = "";
    $correction = "";

    // Steckbriefe die vom User korrigiert werden müssen
    $round_two = $db->simple_select("application_ucp_management", "*", "submission_time < modcorrection_time AND usercorrection_time < modcorrection_time");
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
    $aucp_mod_steckilink = "";
    $aucp_mod_profillink = "";
    $aucp_mod_modlink = "";
    $aucp_mod_date = "";
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

      $regdate = date("d.m.Y", $user['regdate']);
      $lastactiv = date("d.m.Y", $user['lastactive']);
      //hier registierungsdatum statt steckilink
      $aucp_mod_steckilink =  $regdate;
      //Link zum Profil des users
      $aucp_mod_profillink = "<a href=\"" . get_profile_link($user['uid']) . "\">" . $user['username'] . "</a>";
      //hier statt link zum mod, letzte aktivität des users
      $aucp_mod_modlink = $lastactiv;
      $aucp_mod_date = date("d.m.Y", strtotime($data['regdate'] . " + {$app_deadline} days"));
      if ($mybb->settings['application_ucp_extend'] > 0) {
        //wie oft wurde verlängert
        $extend_cnt = $db->fetch_field($db->simple_select("users", "aucp_extend", "uid = {$user['uid']}"), "aucp_extend");
        if ($extend_cnt > 0) {
          $to_add = $mybb->settings['application_ucp_extend'] * $extend_cnt;
          $add_extend = strtotime("+{$to_add} days", $aucp_mod_date);
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
$plugins->add_hook("newreply_do_newreply_end", "application_ucp_do_reply");
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
  global $templates, $db, $mybb, $application_ucp_index;

  $applicants = $mybb->settings['application_ucp_applicants'];
  $mods = $mybb->settings['application_ucp_stecki_mods'];
  $friststecki = $mybb->settings['application_ucp_applicationtime'];
  $fristkorrektur = $mybb->settings['application_ucp_correctiontime'];

  $alertflag = 0;
  $uid = $mybb->user['uid'];
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
        //wie oft wurde verlänger
        $extend_cnt = $db->fetch_field($db->simple_select("users", "aucp_extend", "uid = {$mybb->user['uid']}"), "aucp_extend");
        if ($extend_cnt < $mybb->settings['application_ucp_extend_cnt']) {
          $extend_button = "<a href=\"misc.php&action=ext_app\" class=\"aucp extbtn\">Verlängern</a>";
        }
        if ($extend_cnt > 0) {
          $to_add = $mybb->settings['application_ucp_extend'] * $extend_cnt;
          $add_extend = strtotime("+{$to_add} days", $frist);
          $addtext = " Du hast {$extend_cnt} Mal verlängert.";
        }
      }
      $deadline = date("d.m.Y", $add_extend);
      $message = "Du hast noch bis zum {$deadline} Zeit deinen Steckbrief zu vervollständigen.{$addtext}";
      eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
    } else {
      //Steckbrief wurde eingereicht
      while ($alert = $db->fetch_array($get_managment)) {
        // Noch kein verantwortlicher Moderator
        if ($alert['uid_mod'] == "0") {
          $alertflag = 1;
          $message = "Dein Steckbrief wurde noch von keinem Moderator übernommen.";
          eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
        } else {
          //Moderator hat übernommen
          $mod = get_user($alert['uid_mod']);
          //noch in Korrektur
          if (strtotime($alert['modcorrection_time']) <= strtotime($alert['usercorrection_time'])) {
            //Info: XY hat deinen Steckbrief übernommen
            $alertflag = 1;
            $message = "Dein Steckbrief wurde von " . build_profile_link($mod['username'], $mod['uid']) . " übernommen.";
            eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
          } elseif (strtotime($alert['modcorrection_time']) > strtotime($alert['usercorrection_time'])) {
            //Dein Steckbrief ist fertig korrigiert Zeit zur kontrolle bis
            $alertflag = 1;
            $frist = strtotime("+{$fristkorrektur} days", strtotime($mybb->user['modcorrection_time']));
            $deadline = date("d.m.Y", $frist);
            $message = "Dein Steckbrief wurde von " . build_profile_link($mod['username'], $mod['uid']) . " korrigiert. <br/> 
            Du hast für die Korrektur Zeit bis zum {$deadline}";
            eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
          }
        }
      }
    }
  }

  $get_alerts = $db->simple_select("application_ucp_management", "*");
  while ($alert = $db->fetch_array($get_alerts)) {
    if (is_member($mods, $uid)) {
      //Steckbrief wurde abgegeben
      if (strtotime($alert['submission_time']) > strtotime($alert['modcorrection_time'])) {
        $about = get_user($alert['uid']);
        $aboutuserlink = build_profile_link($about['username'], $about['uid'], "_blank");
        //Noch kein Mod zugeteilt
        if ($alert['uid_mod'] == "0") {
          $alertflag = 1;
          $message = "{$aboutuserlink} ist mit dem Steckbrief fertig<br />
	        <a href=\"misc.php?action=take_application&uid={$alert['uid']}\">Korrektur übernehmen</a>";

          eval("\$application_ucp_index_bit .= \"" . $templates->get("application_ucp_index_bit") . "\";");
        } else {
        }
      }
    }
  }
  if ($alertflag) {
    eval("\$application_ucp_index = \"" . $templates->get("application_ucp_index") . "\";");
  }
}

/***
 * 
 * Hilfsfunktionen
 *
 */

/**
 * Anzeige der Felder im Profil und im Postbit
 * Wir sind faul und wollen das ganze nicht mehrmals schreiben :D 
 * @param uid von wem sollen die Felder angezeigt werden
 * @return html oder array 
 */

function application_ucp_build_view($uid, $location, $kind)
{
  global $db, $mybb;
  require_once MYBB_ROOT . "inc/class_parser.php";
  $parser = new postParser;

  if ($kind == "html") {
    $buildhtml = "<div class=\"aucp_fieldContainer aucp_{$location}\">";
    $fieldquery = $db->write_query("
        SELECT * FROM `" . TABLE_PREFIX . "application_ucp_userfields` uf 
          inner JOIN 
        " . TABLE_PREFIX . "application_ucp_fields f 
        ON f.id = uf.fieldid 
        and uid = {$uid} AND {$location} = 1 
        AND fieldid > 0 AND active = 1");

    while ($field = $db->fetch_array($fieldquery)) {
      //parser options
      $parser_options = array(
        "allow_html" => $field['allow_html'],
        "allow_mycode" => $field['allow_mybb'],
        "allow_smilies" => 0,
        "allow_imgcode" => $field['allow_img'],
        "allow_videocode" => $field['allow_video']
      );
      if ($field['fieldtyp'] == "date") {
        $fieldvalue = date("d.m.Y", strtotime($field['value']));
      } else {
        $fieldvalue = $field['value'];
      }
      $buildhtml .= "<div class=\"aucp_fieldContainer__item\"><div class=\"aucp_fieldContainer__field label\">{$field['label']}:</div>
    <div class=\"aucp_fieldContainer__field field {$field['fieldname}']}\">" . $parser->parse_message($fieldvalue, $parser_options) . "</div>
    </div>
    ";
    }
    $buildhtml .= "</div>";
    return $buildhtml;
  }
  if ($kind == "array") {
    $array = array();

    $fieldquery = $db->write_query("
      SELECT * FROM `" . TABLE_PREFIX . "application_ucp_userfields` uf 
        inner JOIN 
      " . TABLE_PREFIX . "application_ucp_fields f 
      ON f.id = uf.fieldid 
      and uid = {$uid} 
      AND {$location} = 1 
      AND fieldid > 0");

    while ($field = $db->fetch_array($fieldquery)) {
      $parser_options = array(
        "allow_html" => $field['allow_html'],
        "allow_mycode" => $field['allow_mybb'],
        "allow_smilies" => 0,
        "allow_imgcode" => $field['allow_img'],
        "allow_videocode" => $field['allow_video']
      );
      if ($field['fieldtyp'] == "date") {
        $fieldvalue = date("d.m.Y", strtotime($field['value']));
      } else {
        $fieldvalue = $field['value'];
      }
      //   Label & Value: {$application['labelvalue_vorname']}
      $arrayfieldlabelvalue = "labelvalue_{$field['fieldname']}";
      $array[$arrayfieldlabelvalue] = $field['label'] . ": " . $parser->parse_message($fieldvalue, $parser_options);

      // Label: {$application['label_vorname']}
      $arraylabel = "label_{$field['fieldname']}";
      $array[$arraylabel] = $field['label'];

      // Value: {$application['value_vorname']}
      $arraylabel = "value_{$field['fieldname']}";
      $array[$arraylabel] = $parser->parse_message($fieldvalue, $parser_options);
    }

    return $array;
  }
}

/**
 * Felder abspeichern
 */
function application_ucp_savefields($fields, $uid)
{
  global $db, $mybb;
  foreach ($fields as $key => $value) {
    //key -> id des felds  //Value -> der wert
    //checkboxen kriegen wir als array, wir müssen es erst in einen string umwandeln, den wir speichern können
    if (is_array($value)) {
      $value = implode(",", $value);
    }
    //Weil wir nur infofelder haben, wir wollen nur die Felder mit einem numerischen wert, also einer ID und somit einem Steckbrieffeld absspeichern
    if (is_numeric($key)) {
      //Füge den Wert neu ein, wenn er noch nicht existiert
      $db->write_query("
        INSERT INTO " . TABLE_PREFIX . "application_ucp_userfields (uid, value, fieldid) 
        VALUES('{$uid}', '{$value}', {$key}) ON 
        DUPLICATE KEY UPDATE value='{$value}'");
    }
  }
}
/***
 * PN an user, der betroffen ist.
 */
function application_ucp_affected_alert($charakter, $touid, $tid, $editflag)
{
  global $mybb;
  $alerttype = $mybb->settings['application_ucp_stecki_affected_alert'];
  if ($alerttype == 0) { //private message
    $user = get_user($charakter);
    $userprofil = build_profile_link($user['username'], $charakter);
    $steckilink = get_thread_link($tid);
    $message = "Der Steckbrief({$steckilink}) von {$userprofil} betrifft dich. Bitte gib dein Okay.";
    $pm = array(
      'subject' => "Charakter der dich betrifft",
      'message' => $message,
      'touid' => $touid,
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
  } else if ($alerttype == 3) { //noting
    //ja nichts halt, eh?
  }
}

/**************************** 
 * 
 *  My Alert Integration
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
      return $this->lang->sprintf(
        $this->lang->application_ucp_affected,
        $outputAlert['from_user'],
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
