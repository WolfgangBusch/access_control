# access_control
<h4>Version 1.1.0</h4>
<ul>
    <li>Bei gro�en Dateien (> 250 MB) produzierte rex_managed_media::sendMedia()
        einen Speicher�berlauf. Die Ausgabe der Mediendateien erfolgt auf
        diesem Wege jetzt nur noch f�r Bilder, PDF-Dokumente und Plaintext. -
        Andere Dokumente werden mit einer modifizierten Version von
        rex_response::sendFile(...) ausgegeben (rex_response::sendFile
        selbst liefert Dateien aus dem Browser-Cache, d.h. nach einem LogIn
        w�rde weiterhin 'protected.gif' angezeigt).</li>
</ul>
<h4>Version 1.2.0</h4>
<ul>
    <li>Alle Funktionen der Klassen access_control und access_control_install
        werden jetzt vorschriftsm��ig als "public static function ..."
        deklariert.</li>
    <li>Bei der De-Installation werden die Konfigurationsvariablen jetzt
        aus der Tabelle rex_config entfernt.</li>
</ul>
