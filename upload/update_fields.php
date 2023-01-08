<?php
define("IN_MYBB", 1);
require("global.php");
// error_reporting(E_ERROR | E_PARSE);
// ini_set('display_errors', true);

global $db, $mybb, $lang;

if (!$db->field_exists("guest", "application_ucp_fields")) {
  $db->add_column("application_ucp_fields", "guest", "int(1) NOT NULL DEFAULT 1");
  echo "guest hinzugefügt.<br>";
}
if (!$db->field_exists("guest_content", "application_ucp_fields")) {
  $db->add_column("application_ucp_fields", "guest_content", "varchar(500) NOT NULL DEFAULT ''");
  echo "guest_content hinzugefügt. <br>Datei jetzt löschen!";
}

if ($db->field_exists("guest", "application_ucp_fields")) {
  echo "Die Felder existieren schon, bitte die Datei löschen";
}

