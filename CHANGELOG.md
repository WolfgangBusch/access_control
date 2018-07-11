# access_control
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
