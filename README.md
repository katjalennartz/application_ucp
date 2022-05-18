# application_ucp
RPG Steckbriefsystem für MyBB. Im ACP können Felder für einen Steckbrief definiert werden, die der Benutzer dann in einem extra Reiter in seinem UCP ausfüllen kann.    

# Felder im ACP und ihre Funktionen    
Hier werden die verschiedenen Einstellungen der Felder im ACP erklärt

**Feldname:** Diese Bezeichnund wird in der Datenbank gespeichert, um das Feld später ansprechen zu können. Keine Sonderzeichen, keine Leertasten, am besten alles kleingeschrieben. Diese Bezeichnung dient nur als Identifikator    
**Label:** Das Label ist das, was auch dargestellt wird. Also zum Beispiel 'Vorname'. 

**Feldtyp:** Was für ein Inputfeld soll das Feld sein. Ein einfaches Textfeld, eine Textarea, Select, Multiple-Select, Date, Radio oder Checkbox..
**Options:** Dieses Feld erscheint nur, wenn ihr als Typ einen ausgewählt habt, der eine Auswahl stellt. Mit Komma getrennt, tragt ihr hier die Auswahlmöglichkeiten ein, die der Charakter haben soll.   
**Editierbar:** Hier bestimmt ihr ob dieses Feld nach der Annahme, bearbeitet werden darf oder nicht.  
**Pflichtfeld:** Hier bestimmt ihr ob dieses Feld ein Pflichtfeld ist oder nicht.  
**Abhängigkeit:** Soll das Feld nur erscheinen, wenn in einem vorherigen Select (oder ähnlichem) ein bestimmter Wert (z.B. Schule = Hogwarts) ausgewählt ist, wählt ihr hier das Feld aus.
**Abhängigkeits Wert:** Hier bestimmt ihr, von welchem Wert (also z.B. Hogwarts) das Feld genau abhängt.
**Anzeige im Profil:** Soll das Feld im Profil angezeigt werden?    
**Anzeige im Postbit:** Soll das Feld im Postbit angezeigt werden?    
**Vorlage:** Soll das Feld eine Vorlage enthalten. Also z.B. Ein HTML code.      
**Reihenfolge:** Hier bestimmt ihr an welcher Stelle das Feld angezeigt werden soll (0 ganz oben).
**Aktiv?** Hier stellt ihr ein, ob das Feld aktiv sein soll. Deaktiviert ihr es, wird es nicht mehr angezeigt, die DB Einträge werden aber anders als beim Löschen behalten     
