**Work in progress**    

# application_ucp
**RPG Steckbriefsystem für MyBB.**
* Erstellen von Steckbrieffeldern im ACP
* Ausfüllen der Felder im UCP (speichern oder einreichen)
* Export von Steckbriefen im Profil
* Anzeige der Felder im Profil und Postbit (entweder gesammelt, oder einzeln)
* Verwaltung der ausgefüllten Steckbriefe im ACP
* WoB im Thread erteilen und in Gruppe einteilen
* Optional WoB automatische Antwort

**Einstellungen ACP**
* Bewerbergruppe
* Steckbrief Frist
* Korrektur Frist
* Korrektur Frist
* Fristverlängerung (Tage)
* Exportfunktion
* Area für die Steckbriefe
* Steckbriefthread (Vorlage)
* Charakter ein Gesuch? 
* Charakter betrifft andere?
* Moderatoren Gruppen
* Anzeige im Profil
* Anzeige im Postbit
* Antworttext WoB 
* Antworttext WoB Inhalt

**Templates**
* application_ucp_index
* application_ucp_index_bit
* application_ucp_mods
* application_ucp_mods_bit
* application_ucp_wobbutton
* application_ucp_ucp_main

**eingefügte Variablen**
* index: {$application_ucp_index} (anzeige alert)
* member_profile: {$aucp_fields} (anzeige Felder profil)
* member_profile: {$exportbtn} (anzeige Export button)
* showthread: {$give_wob} (wob form)
* postbit: {$post['aucp_fields']} (anzeige Felder)
* postbit_classic: {$post['aucp_fields']} (anzeige Felder)

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
* **Reihenfolge:** 
    * Hier bestimmt ihr an welcher Stelle das Feld angezeigt werden soll (0 ganz oben).
* **Aktiv?** 
    * Hier stellt ihr ein, ob das Feld aktiv sein soll. Deaktiviert ihr es, wird es nicht mehr angezeigt, die DB Einträge werden aber anders als beim Löschen behalten     
