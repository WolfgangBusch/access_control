# access_control
<h3>Zugriffsschutz für Artikel und Mediendateien</h3>

<div>Dieses AddOn ermöglicht einen Zugriffsschutz für
ausgewählte Bereiche von Artikeln und/oder Mediendateien.
Der Zugriff wird über die Authentifizierung von Redaxo-Benutzern
kontrolliert, denen die entsprechenden Kategorien zugeordnet
sind. Die erfolgte Autorisierung wird Session-basiert
gespeichert.</div>
<div>Es ist nur eine einfache Rewrite-Regel erforderlich.</div>
<div>Das AddOn ist komplett zweisprachig eingerichtet (deutsch,
englisch).</div>

<div><br/><b>Geschützte Bereiche:</b></div>
<div>In Rollen für Redaxo-Benutzer werden normalerweise Kategorien
und Medienkategorien markiert, um den Verantwortungsbereich
(Schreibzugriff) eines Redakteurs für alle Artikel und
Mediendateien im zugehörigen Pfad festzulegen. In diesem AddOn
können entsprechend definierte Kategorien und Top-Medienkategorien
als "geschützte Bereiche" interpretiert werden, auf die Besucher
erst nach Authentifizierung im Frontend mit Name und Passwort
des zugehörigen Redaxo-Benutzers Lesezugriff erhalten.</div>

<div><br/><b>Bewacher-Benutzer:</b></div>
<div>Redaxo-Benutzer mit solchen Rollen können als "Bewacher-Benutzer"
für ihre zugehörigen Bereiche eingerichtet werden. Auf diese Weise
kann auch eine Kategorie festgelegt werden, in deren Pfad nur der
im Backend eingeloggte Site-Administrator Lesezugriff als Besucher hat
("verbotener Bereich").</div>
<div>Im Unterschied zu Redakteuren ist ein Bewacher-Benutzer als
inaktiv zu definieren, und das Feld "Beschreibung" muss den Wert
"Protector" bekommen, im Falle des Bewachers für den verbotenen
Bereich den Wert "Guardian".</div>
<div>Ein im Backend eingeloggter Redaxo-Redakteur hat als Besucher
auch dann Lesezugriff auf seine Seiten, wenn diese in geschützten
Bereichen liegen.</div>

<div><br/><b>Überprüfung der Zugriffsberechtigung:</b></div>
<div>Bei fehlender Zugriffsberechtigung wird anstelle der
angeforderten Mediendatei automatisch ein Fehlerbild angezeigt.
Die Überprüfung, ob ein angeforderter Artikel öffentlich,
geschützt oder verboten ist, kann mithilfe einer AddOn-Funktion
vorgenommen werden, sinnvollerweise im Seiten-Template.
An dieser Stelle kann eine entsprechende Fehlermeldung anstelle
des Artikelinhalts angezeigt werden.</div>