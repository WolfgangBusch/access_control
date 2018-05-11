# access_control
<h3>Zugriffsschutz f�r Artikel und Mediendateien</h3>

<div>Dieses AddOn erm�glicht einen Zugriffsschutz f�r ausgew�hlte
Bereiche von Artikeln beziehungsweise Mediendateien. Es ist
komplett zweisprachig eingerichtet (deutsch, englisch).<br/>
Eine Autorisierung f�r den Zugriff erfolgt nach erfolgreicher
Authentifizierung auf einer AddOn-spezifischen Login-Seite.
Alternativ ist auch ein authentifizierter YCom-User (Community-AddOn)
zugriffsberechtigt.<br/>
Es ist nur eine einfache Rewrite-Regel erforderlich.</div>

<div><br/><b>Gesch�tzter Bereich:</b></div>
<div>Gesch�tzt werden k�nnen alle Artikel und Mediendateien
im Pfad unterhalb einer speziellen Kategorie bzw. Medienkategorie.
Entsprechende Kategorien werden in der Konfiguration festgelegt.
Es bedeutet keine Einschr�nkung, nur genau eine gesch�tzte
Kategorie bzw. Medienkategorie ("gesch�tzter Bereich") vorzusehen,
weil jeder Artikel und jede Mediendatei durch geeignetes
Verschieben gesch�tzt werden kann.</div>

<div><br/><b>Gemeinschafts-Benutzer:</b></div>
<div>Das AddOn bietet einen Modul f�r eine Login-Seite an,
auf der Besucher sich f�r den Zugriff auf gesch�tzte Bereiche
authentifizieren k�nnen. Ein entsprechender Benutzername
und das zugeh�rige Passwort k�nnen konfiguriert werden.
Durch Kenntnis dieser Zugangsdaten wird der Besucher zum
Mitglied einer Gemeinschaft. - Das Login ist Session-basiert.<br/>
Falls das Community-AddOn YCom installiert ist, hat jeder
eingeloggte YCom-Benutzer Zugriff auf die gesch�tzten Bereiche.
In dem Fall kann man auf den Mitglieds-Benutzer verzichten. -
Ein eingeloggter Redaxo-Redakteur ist immer entsprechend
zugriffsberechtigt.</div>

<div><br/><b>Verbotener Bereich:</b></div>
<div>Der Site-Administrator kann dar�ber hinaus in der
Konfiguration eine Kategorie festlegen, unterhalb der nur
er allein Artikel ansehen kann, sobald er eingeloggt ist
("verbotener Bereich").</div>

<div><br/><b>�berpr�fung der Zugriffsberechtigung:</b></div>
<div>Bei fehlender Zugriffsberechtigung wird anstelle
der angeforderten Mediendatei automatisch ein Fehlerbild
angezeigt. Die �berpr�fung, ob ein angeforderter Artikel
frei, gesch�tzt oder verboten ist, kann mithilfe einer
AddOn-Funktion vorgenommen werden, sinnvollerweise im
Seiten-Template. An dieser Stelle kann eine entsprechende
Fehlermeldung anstelle des Artikelinhalts angezeigt
werden.</div>