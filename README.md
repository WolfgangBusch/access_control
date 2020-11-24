# access_control
<h3>Zugriffsschutz für Artikel und Mediendateien</h3>

<div>Dieses AddOn ermöglicht einen Zugriffsschutz für
ausgewählte Bereiche von Artikeln und/oder Mediendateien.
Der Zugriff wird über die Authentifizierung von Redaxo-Benutzern
kontrolliert, denen über ihre Rollen die entsprechenden Kategorien
zugeordnet sind. Die erfolgte Autorisierung wird Session-basiert
gespeichert.</div>
<div>Es ist nur eine einfache Rewrite-Regel erforderlich.</div>
<div>Das AddOn ist komplett zweisprachig eingerichtet (deutsch,
englisch).</div>

<div><br/><b>Geschützte Bereiche:</b></div>
<div>In Rollen für Redaxo-Benutzer werden normalerweise Kategorien
und Medienkategorien markiert, um den Verantwortungsbereich
(Schreibzugriff) eines Redakteurs für alle Artikel und Mediendateien
im zugehörigen Pfad festzulegen. In diesem AddOn werden entsprechend
definierte Kategorien und Top-Medienkategorien mit allen im Pfad
darunter liegenden Artikeln bzw. Dateien als "geschützte Bereiche"
interpretiert. Auf diese Bereiche erhalten Besucher erst nach
Authentifizierung im Frontend mit Name und Passwort des zugehörigen
Redaxo-Benutzers Lesezugriff.</div>

<div><br/><b>Bewacher-Benutzer:</b></div>
<div>Redaxo-Benutzer mit solchen Rollen können als "Bewacher-Benutzer"
für ihre zugehörigen Bereiche eingerichtet werden. Auf diese Weise
kann auch eine Kategorie festgelegt werden, in deren Pfad nur der im
Backend eingeloggte Site-Administrator als Besucher Lesezugriff hat
("verbotener Bereich"). Ein im Backend eingeloggter Redaxo-Redakteur
hat auch dann Lesezugriff auf seine Seiten, wenn diese in geschützten
Bereichen liegen.</div>

<div><br/><b>Überprüfung der Zugriffsberechtigung:</b></div>
<div>Ob ein angeforderter Artikel öffentlich, geschützt oder
verboten ist, kann mithilfe einer AddOn-Funktion festgestellt werden,
sinnvollerweise im Seiten-Template. Dort kann ggf. ein Hinweis oder
ein Link auf eine Login-Seite anstelle des Artikelinhalts angezeigt
werden. Die Überprüfung, ob eine Mediendatei öffentlich oder geschützt
ist, erfolgt in der Boot-Datei. Bei fehlender Zugriffsberechtigung
wird anstelle der angeforderten Mediendatei ein Standard-Fehlerbild
angezeigt.</div>