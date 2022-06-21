<?php

/**
 * Bwerbung im UCP  - by risuena
 * https://github.com/katjalennartz
 * 
 * Adds an Area in the UCP for the application
 * define the fields in the admin acp 
 */
// error_reporting(-1);
// ini_set('display_errors', true);

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
  die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function application_ucp_info()
{
  return array(
    "name" => "Bwerbung im UCP von Risuena",
    "description" => "Bebwerbung/Steckbrief direkt im Profil",
    "website" => "https://github.com/katjalennartz",
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
  `uid_mod` int(10) NOT NULL,
  `submission_time` datetime NOT NULL DEFAULT NOW(),
  `modcorrection_time` datetime,
  `usercorrection_time` datetime,
  `correctioncnt` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

  $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "application_ucp_useralerts` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `to_uid` int(10) NOT NULL,
  `about_uid` int(10) NOT NULL,
  `type` int(10) NOT NULL,
  `message` varchar(500) NOT NULL,
  `read` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

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
    'application_ucp_export' => array(
      'title' => 'Exportfunktion',
      'description' => 'Können Mitglieder ihre Steckbriefe als PDFS exportieren?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 4
    ),
    'application_ucp_steckiarea' => array(
      'title' => 'Area für die Steckbriefe',
      'description' => 'Wie ist die ID für eure Steckbriefarea?',
      'optionscode' => 'numeric',
      'value' => '2', // Default
      'disporder' => 5
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
      'disporder' => 5
    ),
    'application_ucp_stecki_wanted' => array(
      'title' => 'Gesuch',
      'description' => 'Soll abgefragt werden ob es sich um ein Gesuch handelt?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 5
    ),
    'application_ucp_stecki_affected' => array(
      'title' => 'Betroffene Mitglieder',
      'description' => 'Soll abgefragt werden, ob weitere Mitglieder betroffen sind und ihr Okay zu geben?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 5
    ),
    'application_ucp_stecki_mods' => array(
      'title' => 'Moderatoren Gruppen',
      'description' => 'Welche Gruppen sollen informiert werden, wen ein neuer Steckbrief erstellt wurde?',
      'optionscode' => 'groupselect',
      'value' => '4', // Default
      'disporder' => 5
    ),
    'application_ucp_profile_view' => array(
      'title' => 'Anzeige im Profil',
      'description' => 'Soll die Anzeige automatisch gebaut werden? Wenn ja, werden alle Felder mit einer Variable ($aucp_fields) ausgegeben. Wenn nein, müssen die Variablen für die Felder selbst eingefügt werden. (Bennenung findet ihr in der Übersicht).',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 5
    ),
    'application_ucp_postbit_view' => array(
      'title' => 'Anzeige im Postbit',
      'description' => 'Soll die Anzeige automatisch gebaut werden? Wenn ja, werden alle Felder mit einer Variable ($aucp_fields) ausgegeben. Wenn nein, müssen die Variablen für die Felder selbst eingefügt werden. (Bennenung findet ihr in der Übersicht).',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 5
    ),
  );

  foreach ($setting_array as $name => $setting) {
    $setting['name'] = $name;
    $setting['gid'] = $gid;
    $db->insert_query('settings', $setting);
  }
  rebuild_settings();
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

  // Einstellungen entfernen
  $db->delete_query("settings", "name LIKE 'application_ucp%'");
  $db->delete_query('settinggroups', "name = 'application_ucp'");
}


function application_ucp_activate()
{
  global $db, $mybb, $cache;
}

function application_ucp_deactivate()
{
  global $mybb;
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
 * Menü einfügen
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
          if ($field['postbit']) {
            $view_postbit = "<ul>
            <li><b>Anzeige im Postbit:</b> <br/>
            <li>Label & Value: &#x007B;&dollar;post['labelvalue_{$field['fieldname']}']&#x007D;
            <li>Label: &#x007B;&dollar;post['label_{$field['fieldname']}']&#x007D;
            <li>Value: &#x007B;&dollar;post['value_{$field['fieldname']}']&#x007D;	</ul>";
          } else {
            $view_postbit = "";
          }
          if ($field['profile']) {
            $view_profile = "<ul>
            <li><b>Anzeige im Profil:</b> <br/>
            <li>Label & Value: &#x007B;&dollar;fields['labelvalue_{$field['fieldname']}']&#x007D;
            <li>Label: &#x007B;&dollar;fields['label_{$field['fieldname']}']&#x007D;
            <li>Value: &#x007B;&dollar;fields['value_{$field['fieldname']}']&#x007D;	</ul>";
          } else {
            $view_profile = "";
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
        // dependency_value
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
      $page->add_breadcrumb_item($lang->application_ucp_createquesttype);
      $page->output_header($lang->application_ucp_name);
      $sub_tabs = application_ucp_do_submenu();
      $page->output_nav_tabs($sub_tabs, 'application_ucp_add');

      if (isset($errors)) {
        $page->output_inline_error($errors);
      }

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

      //Formular bauen 
      $form = new Form("index.php?module=config-application_ucp&amp;action=application_ucp_add", "post", "", 1);
      $form_container = new FormContainer($lang->application_ucp_formname);
      $form_container->output_row(
        $lang->application_ucp_add_name,
        $lang->application_ucp_add_name_descr,
        $form->generate_text_box('fieldname', "")
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldlabel,
        $lang->application_ucp_add_fieldlabel_descr,
        $form->generate_text_box('fieldlabel', "")
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldtyp,
        $lang->application_ucp_add_fieldtyp_descr,
        $form->generate_select_box('fieldtyp', $select, array(), array('id' => 'fieldtype'))
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldoptions,
        $lang->application_ucp_add_fieldoptions_descr,
        $form->generate_text_box('fieldoptions', "")
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldmandatory,
        $lang->application_ucp_add_fieldmandatory_descr,
        $form->generate_yes_no_radio('fieldmandatory', "1")
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldeditable,
        $lang->application_ucp_add_fieldeditable_descr,
        $form->generate_yes_no_radio('fieldeditable', "0")
      );

      $select_dep_query = $db->simple_select("application_ucp_fields", "fieldname, label", "");
      $select_dep = array("none" => "keine Abhängigkeit");
      while ($deps = $db->fetch_array($select_dep_query)) {
        $name = $deps['fieldname'];
        $select_dep[$name] = $deps['label'];
      }
      $form_container->output_row(
        $lang->application_ucp_add_fielddependency,
        $lang->application_ucp_add_fielddependency_descr,
        $form->generate_select_box('dependency', $select_dep, array("id" => "sel_dep"))
      );

      $form_container->output_row(
        $lang->application_ucp_add_fielddependencyval,
        $lang->application_ucp_add_fielddependencyval_descr,
        $form->generate_text_box('dependency_value', "")
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldpostbit,
        $lang->application_ucp_add_fieldpostbit_descr,
        $form->generate_yes_no_radio('fieldpostbit', "1")
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldprofile,
        $lang->application_ucp_add_fieldprofile_descr,
        $form->generate_yes_no_radio('fieldprofile', "1")
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldtemplate,
        $lang->application_ucp_add_fieldtemplate_descr,
        $form->generate_text_area('fieldtemplate', "")
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldhtml,
        $lang->application_ucp_add_fieldhtml_descr,
        $form->generate_yes_no_radio('fieldhtml', "0")
      );
      $form_container->output_row(
        $lang->application_ucp_add_fieldmybb,
        $lang->application_ucp_add_fieldmybb_descr,
        $form->generate_yes_no_radio('fieldmybb', "0")
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldimg,
        $lang->application_ucp_add_fieldimg_descr,
        $form->generate_yes_no_radio('fieldimg', "0")
      );

      $form_container->output_row(
        $lang->application_ucp_add_fieldvideo,
        $lang->application_ucp_add_fieldvideo_descr,
        $form->generate_yes_no_radio('fieldvideo', "0")
      );

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

  //Steckbriefe anlegen
  $sub_tabs['application_ucp_add'] = [
    "title" => $lang->application_ucp_createfieldtype,
    "link" => "index.php?module=config-application_ucp&amp;action=application_ucp_add",
    "description" => $lang->application_ucp_createfieldtype_dscr
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
    //habdelt es sich um ein Pflichtfeld
    if ($type['mandatory']) {
      $requiredstar = "<span class\"app_ucp_star\">*</span>"; //markierung mit sternchen ux und so :D
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
      //array mit optionen durchgehen und auswahl abuen
      foreach ($options as $option) {
        $option = trim($option); //leertasten vorne und hinten rauswerfen
        $selects .= "<option value=\"{$option}\">{$option}</option>";
      }
      //Select mit Merhfach auswahl 
      if ($typ == "select_multiple") {
        $multiple = "multiple";
      } else {
        $multiple = "";
      }
      //hier bauen wir das feld und packen die optionen rein
      $fields .= " <label class=\"app_ucp_label {$type['fieldname']}\" for=\"{$type['fieldname']}\"  style=\"{$hidden}\" id=\"label_{$type['fieldname']}\">
      {$type['label']}{$requiredstar}:</label>
      <select name=\"{$type['id']}\" id=\"{$type['fieldname']}\"  style=\"{$hidden}\" {$multiple} {$required} {$disabled}>
      {$selects} 
      </select>";
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
      //dann hier das außenrum
      $fields .= "
      <label class=\"app_ucp_label {$type['fieldname']}\" style=\"{$hidden}\" id=\"label_{$type['fieldname']}\">
      {$type['label']}{$requiredstar}:</label>
      <div class=\"application_ucp_checkboxes\"  style=\"{$hidden}\" id=\"{$type['fieldname']}\">
        {$inner}
      </div>
      ";
      //Das Javascript das wir benötigen, wenn mindestens eine Checkbox ausgewählt sein muss.  (also wenn es Pflichtfeld ist). Erst einmal sind alle boxen auf Required gestellt, wird eine box ausgewählt,
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
  $wanted = $mybb->settings['application_ucp_stecki_wanted'];
  $affected = $mybb->settings['application_ucp_stecki_affected'];
  //Es soll asugewählt werden können, ob es sich um ein Gesuch handelt
  if ($wanted) {
    //Die Angabe ist Pflicht
    $requiredstar = "<span class=\"app_ucp_star\">*</span>";
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
  if ($affected) {
    //Wenn eingetragen werden soll ob andere Mitglieder betriffen sind
    $requiredstar = "";
    //input basteln
    //Daten für affected
    $get_affected = $db->simple_select("application_ucp_userfields", "*", "uid = {$mybb->user['uid']} AND fieldid = -3");
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

  $submission_button = "<input type=\"submit\" class=\"button\" name=\"application_ucp_ready\" value=\"Speichern & zur Kontrolle\"/>";

  //Steckbrief speichern, aber nicht abgeben
  if ($mybb->input['application_ucp_save']) {
    //Hier speichern wir, was eingetragen wurde
    //wir bekommen ein array mit allen werten
    $fields = $mybb->input;
    //Hilfsunktion - wir übergeben den input und handeln da alles, weil wir das gleiche so oft machen müssen
    application_ucp_savefields($fields);
    redirect("usercp.php?action=application_ucp");
  }

  //Steckbrief speichern und zur Korrektur geben.
  if ($mybb->input['application_ucp_ready']) {
    // //alle Inputs
    $fields = $mybb->input;

    //Erst mal änderungen speichern
    application_ucp_savefields($fields);

    //Schauen ob es schon einen eintrag im managenent gibt
    $fetch_management = $db->simple_select("application_ucp_management", "*", "uid = {$mybb->user['uid']}");

    //wenn ja, ist es die Verbesserung nach einer Korrektur
    if ($db->num_rows($fetch_management) > 0) {

      $managmentdata = $db->fetch_array($fetch_management);

      // Wenn die Korrekturzeit vom Mod kleiner ist, als heute ist es eine Korrektur des Users
      if (strtotime($managmentdata['modcorrection_time']) <= time()) {
        $add = $db->fetch_field($db->simple_select("application_ucp_management", "correctioncnt", "uid = {$mybb->user['uid']}"), "correctioncnt");
        $add++;
        $update = array(
          "usercorrection_time" => date('Y-m-d H:i:s'),
          "correctioncnt" => $add,
        );
        $db->update_query("application_ucp_management", $update, "uid = {$mybb->user['uid']}");
        //zuständigen Moderator informieren
        $modtoinform = $db->fetch_field($db->simple_select("application_ucp_management", "uid_mod", "uid = {$mybb->user['uid']}"), "uid_mod");
        application_ucp_informuser($modtoinform, "correction", $mybb->user['uid']);
      }
      redirect("usercp.php?action=application_ucp");
    } else {
      //Der Steckbrief wird das erste Mal eingereicht
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
      if ($get_affected_row) {
        // Welche Mitglieder sind betroffen?
        $get_affected_names = explode(",", $db->fetch_field($db->simple_select("application_ucp_userfields", "value", "uid = {$mybb->user['uid']} AND fieldid = -3"), "value"));
        var_dump($get_affected_names);
        $affectedusers = "";
        //Zu den betroffenen den Link bauen
        foreach ($get_affected_names as $name) {
          // Daten des Users bekommen
          $user = get_user_by_username($name);
          $affectedusers .= " <a href=\"member.php?action=profile&uid={$user['uid']}\">{$name}</a>, ";
          // user informieren wenn nötig
          application_ucp_informuser($user['uid'], "isaffected", $mybb->user['uid']);
        }
        // das letzte Komma und leertase entfernen
        $affectedusers = (substr($affectedusers, 0, -2));
        $affected = "Betroffene Charaktere: <br/> {$affectedusers}";
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

      //und jetzt noch einen eintrag in der Management Tabelle
      $insert = array(
        "uid" => $mybb->user['uid'],
        "tid" => $tid
      );
      $db->insert_query("application_ucp_management", $insert);

      // Info an Mods 
      $modgroups = explode(",", $mybb->settings['application_ucp_stecki_mods']);
      if ($modgroups == "") {
        $modgroups = array("4");
      }
      foreach ($modgroups as $modgroup) {
        $mods = $db->simple_select("users", "uid", "usergroup = '{$modgroup}' OR concat(',',additionalgroups,',') LIKE ',%" . $modgroup . "%,'");
        while ($informmod = $db->fetch_array($mods)) {
          application_ucp_informuser($informmod['uid'], "newapplication", $mybb->user['uid']);
        }
      }
      redirect(get_thread_link($tid));
    }
  }

  eval("\$application_ucp_ucp_main =\"" . $templates->get("application_ucp_ucp_main") . "\";");
  output_page($application_ucp_ucp_main);
}

/**
 * automatische Anzeige von den Feldern im Profil
 */
$plugins->add_hook("member_profile_end", "application_ucp_showinprofile");
function application_ucp_showinprofile()
{
  global $db, $mybb, $memprofile, $templates, $aucp_fields;
  $userprofil = $memprofile['uid'];
  // Sollen die Felder automatisch zusammengebaut werden
  if ($mybb->settings['application_ucp_profile_view']) {
    //wir kriegen einen String mit html zurück der alles baut.
    $aucp_fields = application_ucp_build_view($userprofil, "profile", "html");
  } else {
    //Wir stellen uns ein Array zusammen
    $fields = application_ucp_build_view($userprofil, "profile", "array");
  }
  // var_dump($fields);
}

/**
 * automatische Anzeige von den Feldern im Postbit
 */
$plugins->add_hook("postbit", "application_ucp_postbit");
function application_ucp_postbit(&$post)
{
  global $db, $mybb, $templates;
  $uid = $post['uid'];
  // Sollen die Felder automatisch zusammengebaut werden
  if ($mybb->settings['application_ucp_postbit_view']) {
    $post['aucp_fields'] = application_ucp_build_view($uid, "postbit", "html");
  } else {
    // Wir stellen uns ein Array zusammen
    $fields = application_ucp_build_view($uid, "postbit", "array");
    $post = array_merge($post, $fields);
  }
  // var_dump($post);
}
$plugins->add_hook("misc_start", "application_ucp_modoverview");
function application_ucp_modoverview()
{
  global $mybb, $db, $templates, $header, $footer, $theme, $headerinclude, $application_ucp_mods;

  eval("\$application_ucp_mods = \"" . $templates->get("application_ucp_mods") . "\";");
  output_page($application_ucp_mods);
}
/** 
 * INDEX MELDUNGEN:
 * Member:
 * TODO: Steckbrieffrist läuft aus
 * TODO: Zeit zur Korrektur läuft ab
 * TODO: Steckbrief wurde fertig korrigiert (von mod)-> erneute Frist
 * TODO: du musst ein Okay zu einem Stecki geben
 * MOD:
 * TODO: Steckbrief wurde als fertig markiert
 * TODO: THREAD erstellen wenn fertig
 * TODO: Steckbrief wurde von Mitglied fertig korrigiert 
 * 
 * MODVERWALTUNG
 * TODO: Übersicht eingereichte Steckbriefe
 * TODO: Datum hinterlegen wenn fertig korrigiert
 * 
 * PROFIL
 * TODO: Export wenn eigenes Profil
 * 
 * 
 */

/***
 * 
 * Hilfsfunktionen
 *
 */

/**
 * Anzeige der Felder im Profil und im Postbit
 * Wir sind faul und wollen das ganze nicht zweimal schreiben :D 
 * @param uid von wem sollen die Felder angezeigt werden
 * @return html 
 */

function application_ucp_build_view($uid, $location, $kind)
{
  global $db, $mybb;
  if ($kind == "html") {
    $buildhtml = "<div class=\"aucp_fieldContainer aucp_{$location}\">";
    $fieldquery = $db->write_query("SELECT * FROM `mybb_application_ucp_userfields` uf inner JOIN mybb_application_ucp_fields f ON f.id = uf.fieldid and uid = {$uid} AND {$location} = 1 AND fieldid > 0");
    while ($field = $db->fetch_array($fieldquery)) {
      $buildhtml .= "<div class=\"aucp_fieldContainer__item\">{$field['label']}:</div>
    <div class=\"aucp_fieldContainer__item {$field['fieldname}']}\">{$field['value']}</div>
    ";
    }
    $buildhtml .= "</div>";
    return $buildhtml;
  }
  if ($kind == "array") {
    $array = array();

    $fieldquery = $db->write_query("SELECT * FROM `mybb_application_ucp_userfields` uf inner JOIN mybb_application_ucp_fields f ON f.id = uf.fieldid and uid = {$uid} AND {$location} = 1 AND fieldid > 0");
    while ($field = $db->fetch_array($fieldquery)) {

      //   Label & Value: {$application['labelvalue_vorname']}
      $arrayfieldlabelvalue = "labelvalue_{$field['fieldname']}";
      $array[$arrayfieldlabelvalue] = $field['label'] . ": " . $field['value'];

      // Label: {$application['label_vorname']}
      $arraylabel = "label_{$field['fieldname']}";
      $array[$arraylabel] = $field['label'];

      // Value: {$application['value_vorname']}
      $arraylabel = "value_{$field['fieldname']}";
      $array[$arraylabel] = $field['value'];
    }

    return $array;
  }
}

/**
 * Felder abspeichern
 */
function application_ucp_savefields($fields)
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
        VALUES('{$mybb->user['uid']}', '{$value}', {$key}) ON 
        DUPLICATE KEY UPDATE value='{$value}'");
    }
  }
}

function application_ucp_informuser($uid, $type, $aboutuser)
{
  global $db, $mybb;
  $get_alert = $db->simple_select("application_ucp_useralerts", "*", "to_uid = {$uid} AND about_uid = {$aboutuser} AND type = '{$type}'");
  if ($db->num_rows($get_alert) > 0) {
    //eintrag existiert schon
  } else {
    //eintragen
    $insert = array(
      "to_uid" => $uid,
      "type" => $type,
      "about_uid" =>  $aboutuser
    );
    $db->insert_query("application_ucp_useralerts",  $insert);
  }
}
