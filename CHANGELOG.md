# access_control
<h4>Version 2.2</h4>
Durch Nutzung des AddOn-Caches sowie zusätzliche Session-Variable werden
etliche Zugriffe auf die Redaxo-Tabellen rex_user und rex_user_role
vermieden, um eine bessere Performance des AddOns zu erreichen.
<ul>
    <li>Die Daten der Bewacher-Benutzer werden jetzt im AddOn-Cache
        abgelegt, und zwar über die neue install.php. Daher muss nach
        Einrichtung und nach jeder Änderung der Bewacher-Benutzer eine
        Re-Installation des AddOns durchgeführt werden. Der Cache wird
        bei der De-Installation wieder gelöscht. Wird der Cache auf
        anderem Wege (z. B. in den System-Einstellungen) gelöscht,
        ist ebenfalls ein re-install nötig, um den AddOn-Cache neu
        aufzubauen.</li>
    <li>Nach Authentifizierung eines Besuchers werden jetzt alle für
        die Zugriffskontrolle notwendigen Daten des zugehörigen
        Bewacher-Benutzers in Session-Variablen gespeichert, nicht
        nur dessen Login-Name.</li>
    <li>Solange ein Besucher als Redakteur im Backend eingeloggt ist,
        hat er Zugriff auf die Kategorien und Medienkategorien, die
        seiner Rolle zugeordnet sind. Dazu werden jetzt auch seine
        für die Zugriffskontrolle notwendigen Daten im Session-Array
        gespeichert, analog zu einem authentifizierten Besucher.</li>
    <li>Wenn mehrere Bewacher-Benutzer den Zugriff auf eine Kategorie
        kontrollieren, kann sich ein Besucher jetzt über jeden dieser
        Bewacher authentifizieren, nicht nur (wie bisher) über den
        zufällig letzten.</li>
</ul>
<h4>Version 2.1.1</h4>
<ul>
    <li>Kleinere Korrekturen am Programmcode zur Vermeidung von PHP-Warnungen.</li>
</ul>
<h4>Version 2.1</h4>
<ul>
    <li>Der Quellcode ist redaktionell und mit Blick auf bessere
        Performance überarbeitet.</li>
</ul>
<h4>Version 2.0</h4>
<ul>
    <li>Der bisherige 'Gemeinschaftsbenutzer' wird ersetzt durch einen
        Redaxo-Benutzer (rex_user). Darüber hinaus kann der Zugriff auf
        verschiedene Kategorien und/oder Medienkategorien individuell
        über mehrere Redaxo-Benutzer kontrolliert werden. Damit entfällt
        auch die gesamte Konfiguration.</li>
    <li>Die Funktion protected_or_forbidden() gibt jetzt andere Werte
        zurück. Die entsprechende Passage im Seiten-Template muss
        geändert werden!!!</li>
    <li>An die Login-Seite muss nun die Redaxo-User-Id des jeweiligen
        Bewacher-Benutzer übergeben werden (per URL-Parameter
        <code>uid=user_id</code>). Für die Seite wird kein Modul mehr
        zur Verfügung gestellt (Einzeiler, kann per copy & paste aus
        der Beschreibung übernommen werden).</li>
    <li>Falls Kategorien und/oder Medienkategorien geschützt werden,
        für die ein Redaxo-Redakteur zuständig ist, ist dieser auch
        authentifiziert, wenn er im Backend eingeloggt ist.</li>
</ul>
<h4>Version 1.8</h4>
<ul>
    <li>Ergänzung einer weiteren Formularseite zur Authentifizierung
        eines beliebigen weiteren Benutzers (für mögliche andere
        Anwendungen), unter Nutzung einer weiteren Session-Variablen.</li>
    <li>Die Passwörter werden jetzt verschlüsselt abgelegt
        (<code>rex_login::passwordHash($pwd)</code>) und in
        verschlüsselter Form abgeprüft
        (<code>rex_login::passwordVerify($pwd,$encr_pwd)</code>).</li>
</ul>
<h4>Version 1.7.2</h4>
<ul>
    <li>Die Abmeldung nach erfolgreicher Authentifizierung funktioniert
        jetzt vorschriftsmäßig.</li>
</ul>
<h4>Version 1.7.1</h4>
<ul>
    <li>Ergänzung für den Verweis auf eine Mediadatei in der Form
        'index.php?rex_media_file=FILE&rex_media_type=TYPE'.
        Die Datei wird auch gefunden und angezeigt, wenn sie außerhalb
        des Ordners media liegt und TYPE über den Effekt mediapath
        ('Datei: Pfad anpassen') den zugehörigen Ordner liefert.</li>
</ul>
<h4>Version 1.7</h4>
<ul>
    <li>Verzicht auf eine eigene Funktion 'sendFile', stattdessen wird
        'rex_response::sendFile' verwendet.</li>
    <li>Parameter- und CSS-Klassennamen werden jetzt als benannte
        Konstanten abgelegt.</li>
    <li>Im LogIn-Formular und im Modul werden die sprachabhängigen
        Texte jetzt anhand der Sprach-Id ausgewählt, nicht mehr
        anhand des Sprach-Codes. Außerdem wird nach erfolgreicher
        Authentifizierung ein Abmelde-Button angezeigt.</li>
    <li>Anstatt leerer oder nicht vorhandener Media-Dateien wird die
        Datei 'warning.jpg' des AddOns media_manager angezeigt.</li>
</ul>
<h4>Version 1.6.2</h4>
<ul>
    <li>Verbesserung der Dokumentation im Bereich der Konfiguration und
        Verlegung aller Styles in die Stylesheet-Datei.</li>
</ul>
<h4>Version 1.6.1</h4>
<ul>
    <li>Ein dummer Fehler im Installations-Script ist behoben.</li>
</ul>
<h4>Version 1.6.0</h4>
<ul>
    <li>Der gesamte Source-Code ist jetzt auf UTF-8 umgestellt.</li>
    <li>Der Code ist mit 'error_reporting(E_ALL);' überprüft.</li>
    <li>Der AddOn-Modul zur Erzeugung eines LogIn-Formulars ist jetzt
        komplett zweisprachig und kann gleichermaßen im deutschen
        wie auch im englischen Zweig verwendet werden.</li>
    <li>Die Überprüfung, ob ein Besucher als ycom-User eingeloggt ist,
        ist ausgebaut.</li>
</ul>
<h4>Version 1.5.0</h4>
<ul>
    <li>Schlüssel von neu definierten assoziativen Arrays werden jetzt als
        Konstanten (in Apostrophs) behandelt.</li>
</ul>
<h4>Version 1.4.0</h4>
<ul>
    <li>Jetzt werden bei der Ausgabe von Bildern auch die Medientypen gemäß
        Redaxo Media Manager berücksichtigt.</li>
</ul>
<h4>Version 1.3.0</h4>
<ul>
    <li>Jetzt lassen sich alle Konfigurationsparameter einzeln zurücksetzen
        bzw. löschen.</li>
</ul>
<h4>Version 1.2.0</h4>
<ul>
    <li>Alle Funktionen der Klassen access_control und access_control_install
        werden jetzt vorschriftsmäßig als "public static function ..."
        deklariert.</li>
    <li>Bei der De-Installation werden die Konfigurationsvariablen jetzt
        aus der Tabelle rex_config entfernt.</li>
</ul>
<h4>Version 1.1.0</h4>
<ul>
    <li>Bei großen Dateien (> 250 MB) produzierte rex_managed_media::sendMedia()
        einen Speicherüberlauf. Die Ausgabe der Mediendateien erfolgt auf
        diesem Wege jetzt nur noch für Bilder, PDF-Dokumente und Plaintext. -
        Andere Dokumente werden mit einer modifizierten Version von
        rex_response::sendFile(...) ausgegeben (rex_response::sendFile
        selbst liefert Dateien aus dem Browser-Cache, d.h. nach einem LogIn
        würde weiterhin 'protected.gif' angezeigt).</li>
</ul>