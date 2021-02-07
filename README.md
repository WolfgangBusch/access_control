# access_control
<h3>Zugriffsschutz für Artikel und Mediendateien</h3>

<div>Dieses AddOn ermöglicht eine Zugriffskontrolle für
ausgewählte Bereiche von Artikeln und/oder Mediendateien.
Damit ist gemeint, dass ein Besucher im Front-End eine
Authentifizierung benötigt, um bestimmte Seiten oder
Mediendateien sehen zu dürfen. Auf diese Weise wird auch
eine Besuchergruppe eingerichtet, die von der allgemeinen
Öffentlichkeit abgegrenzt ist.</div>
<div>Der Zugriff wird über die Authentifizierung von
Redaxo-Benutzern kontrolliert, denen über ihre Rollen die
entsprechenden Bereiche zugeordnet sind.</div>
<div>Die erfolgte Autorisierung wird Session-basiert
gespeichert.</div>
<div>Es ist nur eine einfache Rewrite-Regel erforderlich.</div>
<div>Das AddOn ist komplett zweisprachig eingerichtet (deutsch,
englisch).</div>

<div><br/><b>Geschützte Bereiche:</b></div>
<div>Die Zugriffskontrolle kann für jede beliebige Kategorie
eingerichtet werden ("geschützter Bereich"). Der Schutz
erstreckt sich dann auf alle Artikel, die in dieser Kategorie
und in ihren Unterkategorien liegen. Ggf. muss der inhaltlich
aufgebaute Kategorienbaum zugunsten des Datenschutzes
umstrukturiert werden, indem schutzwürdige Artikel in einen
geschützten Bereich verschoben werden.</div>
<div>Analog kann eine vorhandene oder eine neue
Top-Medienkategorie als geschützter Bereich für Mediendateien
eingerichtet werden.</div>
<div>Darüber hinaus kann bei Bedarf auch eine Kategorie als
"verbotener Bereich" eingerichtet werden. Auf diesen hat nur
der Site-Administrator Lesezugriff als Besucher, wenn er im
Backend eingeloggt ist.</div>

<div><br/><b>Login-Seite:</b></div>
<div>Die Authentifizierung erfolgt über eine Login-Seite,
die an geeigneter Stelle einzurichten ist. Ein entsprechendes
Formular ist verfügbar.</div>

<div><br/><b>Bewacher-Benutzer:</b></div>
<div>Auf der Login-Seite muss der Login-Name und das zugehörige
Passwort eines Redaxo-Benutzers verifiziert werden. Die
Zuordnung der geschützten Kategorie bzw. Medienkategorie zu
diesem Benutzer ("Bewacher-Benutzer") wird im Rahmen der
Redaxo-Benutzerverwaltung über entsprechende Rollen
realisiert.</div>
<div>Einem Bewacher-Benutzer können mehrere geschützte
Bereiche zugeordnet werden, sowohl Kategorien als auch
Medienkategorien.</div>

<div><br/><b>Überprüfung der Zugriffsberechtigung:</b></div>
<div>Ob ein angeforderter Artikel öffentlich, geschützt oder
verboten ist, kann mithilfe einer AddOn-Funktion festgestellt
werden, sinnvollerweise im Seiten-Template. Dort kann ggf.
ein Hinweis oder ein Link auf die Login-Seite anstelle des
Artikelinhalts angezeigt werden. Ein exemplarischer
PHP-Code-Schnipsel ist verfügbar.</div>
<div>Die Überprüfung, ob eine Mediendatei öffentlich oder
geschützt ist, erfolgt in der Boot-Datei. Bei fehlender
Zugriffsberechtigung wird anstelle der angeforderten
Mediendatei ein Standard-Fehlerbild angezeigt.</div>