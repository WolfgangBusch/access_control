# access_control
<h3>Zugriffsschutz für Artikel und Mediendateien</h3>

<div>Dieses AddOn ermöglicht eine Zugriffskontrolle für
ausgewählte Bereiche von Artikeln und/oder Mediendateien.
Damit ist gemeint, dass ein Besucher im Front-End eine
Authentifizierung benötigt, um bestimmte Seiten oder
Mediendateien sehen zu dürfen. Auf diese Weise werden
Besuchergruppen eingerichtet, die von der allgemeinen
Öffentlichkeit abgegrenzt sind.</div>
<div>Der Zugriff wird über die Authentifizierung von
Redaxo-Benutzern kontrolliert, denen über ihre Rollen die
entsprechenden Bereiche zugeordnet sind.</div>
<div>Die erfolgte Autorisierung wird Session-basiert
gespeichert.</div>
<div>Das AddOn ist komplett zweisprachig eingerichtet
(deutsch, englisch).</div>

<div><br/><b>Geschützte Bereiche:</b></div>
<div>Die Zugriffskontrolle kann für jede beliebige Kategorie
eingerichtet werden ("geschützter Bereich"). Der Schutz
erstreckt sich dann auf alle Artikel, die in dieser
Kategorie und in ihren Unterkategorien liegen. Ggf. muss
der inhaltlich aufgebaute Kategorienbaum zugunsten des
Datenschutzes umstrukturiert werden, indem schutzwürdige
Artikel in einen geschützten Bereich verschoben werden.</div>
<div>Analog können vorhandene oder neue Top-Medienkategorien
als geschützte Bereiche für Mediendateien angelegt werden. -
Auch Mediendateien in Unterordnern des Medienordners können
auf diese Weise geschützt werden. Dazu wird für jeden
Top-Unterordner des Medienordners automatisch eine
Top-Medienkategorie erzeugt.</div>
<div>Darüber hinaus kann bei Bedarf auch eine Kategorie als
"verbotener Bereich" definiert werden. Auf diesen hat nur der
Site-Administrator Lesezugriff als Besucher, wenn er im Backend
eingeloggt ist.</div>

<div><br/><b>Bewacher-Benutzer:</b></div>
<div>Die Zuordnung einer geschützten Kategorie bzw.
Medienkategorie zu einem Redaxo-Benutzer ("Bewacher-Benutzer")
wird im Rahmen der Redaxo-Benutzerverwaltung über entsprechende
Rollen realisiert. Einem Bewacher-Benutzer können mehrere
geschützte Bereiche zugeordnet werden, sowohl Kategorien als
auch Medienkategorien.</div>

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

<div><br/><b>Login-Seite:</b></div>
<div>Um auf einen geschützten Artikel oder eine geschützte
Mediendatei zugreifen zu können, muss sich ein Besucher vorher
durch Angabe des Login-Namens und des zugehörigen Passworts
eines Bewacher-Benutzers authentifizieren. Ein Artikel mit
Login-Formular kann an passender Stelle als PHP-Einzeiler
angelegt werden.</div>