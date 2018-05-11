# access_control<h3>Zugriffsschutz für Artikel und Mediendateien</h3>
<div>Dieses AddOn ermöglicht einen Zugriffsschutz für ausgewählteBereiche von Artikeln beziehungsweise Mediendateien. Es istkomplett zweisprachig eingerichtet (deutsch, englisch).<br/>Eine Autorisierung für den Zugriff erfolgt nach erfolgreicherAuthentifizierung auf einer AddOn-spezifischen Login-Seite.Alternativ ist auch ein authentifizierter YCom-User (Community-AddOn)zugriffsberechtigt.<br/>Es ist nur eine einfache Rewrite-Regel erforderlich.</div>
<div><br/><b>Geschützter Bereich:</b></div><div>Geschützt werden können alle Artikel und Mediendateienim Pfad unterhalb einer speziellen Kategorie bzw. Medienkategorie.Entsprechende Kategorien werden in der Konfiguration festgelegt.Es bedeutet keine Einschränkung, nur genau eine geschützteKategorie bzw. Medienkategorie ("geschützter Bereich") vorzusehen,weil jeder Artikel und jede Mediendatei durch geeignetesVerschieben geschützt werden kann.</div>
<div><br/><b>Gemeinschafts-Benutzer:</b></div><div>Das AddOn bietet einen Modul für eine Login-Seite an,auf der Besucher sich für den Zugriff auf geschützte Bereicheauthentifizieren können. Ein entsprechender Benutzernameund das zugehörige Passwort können konfiguriert werden.Durch Kenntnis dieser Zugangsdaten wird der Besucher zumMitglied einer Gemeinschaft. - Das Login ist Session-basiert.<br/>Falls das Community-AddOn YCom installiert ist, hat jedereingeloggte YCom-Benutzer Zugriff auf die geschützten Bereiche.In dem Fall kann man auf den Mitglieds-Benutzer verzichten. -Ein eingeloggter Redaxo-Redakteur ist immer entsprechendzugriffsberechtigt.</div>
<div><br/><b>Verbotener Bereich:</b></div><div>Der Site-Administrator kann darüber hinaus in derKonfiguration eine Kategorie festlegen, unterhalb der nurer allein Artikel ansehen kann, sobald er eingeloggt ist("verbotener Bereich").</div>
<div>Bei fehlender Zugriffsberechtigung wird anstelleder angeforderten Mediendatei automatisch ein Fehlerbildangezeigt. Die Überprüfung, ob ein angeforderter Artikelfrei, geschützt oder verboten ist, kann mithilfe einerAddOn-Funktion vorgenommen werden, sinnvollerweise imSeiten-Template. An dieser Stelle kann eine entsprechendeFehlermeldung anstelle des Artikelinhalts angezeigtwerden.</div>
