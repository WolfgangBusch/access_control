# access_control
<h3>Zugriffsschutz für Artikel und Mediendateien</h3>

<div>Dieses AddOn ermöglicht einen Zugriffsschutz für ausgewählte
Bereiche von Artikeln beziehungsweise Mediendateien. Es ist
komplett zweisprachig eingerichtet (deutsch, englisch).<br/>
Eine Autorisierung für den Zugriff erfolgt nach erfolgreicher
Authentifizierung.<br/>
Es ist nur eine einfache Rewrite-Regel erforderlich.</div>

<div><br/><b>Geschützter Bereich:</b></div>
<div>Geschützt werden können alle Artikel und Mediendateien
im Pfad unterhalb einer speziellen Kategorie bzw. Medienkategorie.
Entsprechende Kategorien werden in der Konfiguration festgelegt.
Es bedeutet keine Einschränkung, nur genau eine geschützte
Kategorie bzw. Medienkategorie ("geschützter Bereich") vorzusehen,
weil jeder Artikel und jede Mediendatei durch geeignetes
Verschieben geschützt werden kann.</div>

<div><br/><b>Gemeinschafts-Benutzer:</b></div>
<div>Das AddOn bietet einen Modul für ein LogIn-Formular an,
mit dem Besucher sich für den Zugriff auf geschützte Bereiche
authentifizieren können. Ein entsprechender Benutzername
und das zugehörige Passwort können konfiguriert werden.
Durch Kenntnis dieser Zugangsdaten wird der Besucher zum
Mitglied einer Gemeinschaft.<br/>
Das LogIn ist Session-basiert.<br/>
Ein eingeloggter Redaxo-Redakteur ist immer entsprechend
zugriffsberechtigt.</div>

<div><br/><b>Verbotener Bereich:</b></div>
<div>Der Site-Administrator kann darüber hinaus in der
Konfiguration eine Kategorie festlegen, unterhalb der nur
er allein Artikel ansehen kann, sobald er eingeloggt ist
("verbotener Bereich").</div>

<div><br/><b>Überprüfung der Zugriffsberechtigung:</b></div>
<div>Bei fehlender Zugriffsberechtigung wird anstelle
der angeforderten Mediendatei automatisch ein Fehlerbild
angezeigt. Die Überprüfung, ob ein angeforderter Artikel
frei, geschützt oder verboten ist, kann mithilfe einer
AddOn-Funktion vorgenommen werden, sinnvollerweise im
Seiten-Template. An dieser Stelle kann eine entsprechende
Fehlermeldung anstelle des Artikelinhalts angezeigt
werden.</div>