**Work in progress**    
Installieren funktioniert schon, aber es kann sein das noch noch nicht alles optimimal funktioniert, oder noch verändert/ optimiert wird.
      
In Progress: durchsuchbarkeit in der Mitgliederliste     

# application_ucp    
Das Plugin ermöglicht es, unabhängig von Profilfeldern, Felder für den Steckbrief anzulegen. Diese werden in einem gesonderten Bereich im UCP angezeigt und können hier bearbeitet werden. Das Mitglied kann den Steckbrief bearbeiten und speichern und schließlich als fertig markieren und einreichen. Es wird automatisch ein Thread in der Steckbriefarea erstellt.       

**Wichtiger Hinweis.**
Um die Funktion nutzen zu können, dass die Mitgliederliste durchsuchbar ist, sind Änderungen in der memberlist.php nötig. Entweder die patches importieren (hier im gitlab) oder ganz am ende den Anweisungen folgen.         

**RPG Steckbriefsystem für MyBB.**
* Erstellen von Steckbrieffeldern im ACP
* Ausfüllen der Felder im UCP (speichern oder einreichen)
* Export von Steckbriefen im Profil
* Anzeige der Felder im Profil und Postbit (entweder gesammelt, oder einzeln)
* Verwaltung der ausgefüllten Steckbriefe im ACP
* WoB im Thread erteilen und in Gruppe einteilen
* Optional bei WoB automatische Antwort
* Optional durchsuchen/filtern der Mitgliederliste nach den ausgefüllten Feldern

**Einstellungen ACP**
* Bewerbergruppe
* Steckbrief Frist
* Korrektur Frist
* Fristverlängerung (Tage)
* Fristverlängerung (Häufigkeit)
* Exportfunktion
* Area für die Steckbriefe
* Steckbriefthread (Vorlage)
* Charakter ein Gesuch? 
* Charakter betrifft andere?
* Art der Benachrichtigung
* Moderatoren Gruppen
* Anzeige im Profil
* Anzeige im Postbit
* Anzeige in der Memberlist
* Antworttext WoB 
* Antworttext WoB Inhalt
* Sollen Felder durchsuchbar sein (Mitgliederliste)

**Templates**
* application_ucp_index
* application_ucp_index_bit
* application_ucp_mods
* application_ucp_mods_bit
* application_ucp_wobbutton
* application_ucp_ucp_main
* application_ucp_filtermemberlist

**eingefügte Variablen**
* index: {$application_ucp_index} (anzeige alert)
* member_profile: {$aucp_fields} (anzeige Felder profil)
* member_profile: {$exportbtn} (anzeige Export button)
* showthread: {$give_wob} (wob form)
* postbit: {$post['aucp_fields']} (anzeige Felder)
* postbit_classic: {$post['aucp_fields']} (anzeige Felder)
* memberlist: $applicationfilter

**Links**
* ACP Übersicht der Felder: boardadresse/admin/index.php?module=config-application_ucp
* Feld erstellen: boardadresse/admin/index.php?module=config-application_ucp&action=application_ucp_add
* Steckbriefe verwalten: boardadresse/admin/index.php?module=config-application_ucp&action=application_ucp_manageusers
* UCP: boardadresse/usercp.php?action=application_ucp
* Mod Übersicht: boardadresse/misc.php?action=aplication_mods
# Felder im ACP und ihre Funktionen    
Hier werden die verschiedenen Einstellungen der Felder im ACP erklärt

* **Name des Felds:** 
    * Diese Bezeichnund wird in der Datenbank gespeichert, um das Feld später ansprechen zu können. Keine Sonderzeichen, keine Leertasten, am besten alles kleingeschrieben. Diese Bezeichnung dient nur als Identifikator    
* **Label des Felds:** 
    * Das Label ist das, was auch dargestellt wird. Also zum Beispiel 'Vorname'. 
* **Beschreibung des Felds** 
    * Die Beschreibung für das Feld.
* **Typ des Felds:** 
    * Was für ein Inputfeld soll das Feld sein. Ein einfaches Textfeld, eine Textarea, Select, Multiple-Select, Date, Radio oder Checkbox..
* **Auswahloptionen des Felds:** 
    * Dieses Feld ist nur auszufüllen, wenn ihr als Typ einen ausgewählt habt, der eine Auswahl stellt. Mit Komma getrennt, tragt ihr hier die Auswahlmöglichkeiten ein, die der Charakter haben soll.   
* **Pflichtfeld:** 
    * Hier bestimmt ihr ob dieses Feld ein Pflichtfeld ist oder nicht.  
* **Editierbar:** 
    * Hier bestimmt ihr ob dieses Feld nach der Annahme, bearbeitet werden darf oder nicht.  
* **Abhängigkeit:**
    * Soll das Feld nur erscheinen, wenn in einem vorherigen Select, Multi-Select, Checkbox, Radiobutton ein bestimmter Wert (z.B. Schule = Hogwarts) ausgewählt ist, wählt ihr hier das Feld aus.
* **Abhängigkeitswert:** 
    * Hier bestimmt ihr, von welchem Wert (also z.B. Hogwarts) das Feld genau abhängt. (Muss genau(!) der Option entsprechen)
* **Anzeige im Profil:** 
    * Soll das Feld im Profil angezeigt werden? (wenn nein, können die Felder individuell dargestellt werden, Variablen dafür sind in der ACP-Übersicht zu finden)
* **Anzeige im Postbit:** 
    * Soll das Feld im Postbit angezeigt werden?  (wenn nein, können die Felder individuell dargestellt werden, Variablen dafür sind in der ACP-Übersicht zu finden)
* **Vorlage:** 
    * Soll das Feld eine Vorlage enthalten. Also z.B. einen HTML code.
* **HTML erlaubt?**
  * Soll HTML erlaubt sein
* **MyBB-Code erlaubt?**
  * Soll MyBB-Code erlaubt sein
* **IMG-tag erlaubt?**
  * Sind Bilder erlaubt?
* **Videos erlaubt?**
  * Dürfen Videos eingefügt werden?
* **Durchsuchbar:** 
    * Hier könnt ihr angeben, ob in der Mitgliederliste nach dem Feld gesucht werden soll.
* **Reihenfolge:** 
    * Hier bestimmt ihr an welcher Stelle das Feld angezeigt werden soll (0 ganz oben).
* **Aktiv?** 
    * Hier stellt ihr ein, ob das Feld aktiv sein soll. Deaktiviert ihr es, wird es nicht mehr angezeigt, die DB Einträge werden aber anders als beim Löschen behalten     


**Mitgliederliste bearbeiten für Durchsuchbarkeit.**        
ich empfehle einfach die patches zu importieren             
ansonten          
suche:            
``` $query = $db->simple_select("users u", "COUNT(*) AS users", "{$search_query}"); ``` 

ersetzen mit:           

```  $query = $db->query("
	SELECT count(*) as users
	FROM ".TABLE_PREFIX."users u
	LEFT JOIN ".TABLE_PREFIX."userfields f ON (f.ufid=u.uid)
	{$selectstring}
	WHERE {$search_query}
"); ```            
            
suche nach:  


``` $query = $db->query("
SELECT u.*, f.*
FROM ".TABLE_PREFIX."users u
LEFT JOIN ".TABLE_PREFIX."userfields f ON (f.ufid=u.uid)
WHERE {$search_query}
ORDER BY {$sort_field} {$sort_order}
LIMIT {$start}, {$per_page}
");```          

ersetzen mit:  

``` $query = $db->query("
		SELECT u.*, f.*
		{$selectfield}
		FROM ".TABLE_PREFIX."users u
		LEFT JOIN ".TABLE_PREFIX."userfields f ON (f.ufid=u.uid)
		{$selectstring}
		WHERE {$search_query}
		ORDER BY {$sort_field} {$sort_order}
		LIMIT {$start}, {$per_page}
	");```            
