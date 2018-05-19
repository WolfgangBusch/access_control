# access_control
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
