=== BSD Verwaltung ===
Contributors: mreichardt1992
Donate link: http://www.bsd-verwaltung.de/donate
Tags: Feuerwehr, BSD, Rettung, Rettungsdienst, THW, HiOrg, Wasserwacht, Bergrettung, Dienstverwaltung, Dienst, Verwaltung
Requires at least: 3.5.0
Tested up to: 4.8
Stable tag: 1.2.3
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Darstellung, Verwaltung und Vergabe von (Brandsicherheits)Diensten der Feuerwehr an die Mannschaft. Auch f&uuml;r andere Hilfsorganisationen geeignet.

== Description ==

Dieses Plugin f&uuml;gt WordPress eine neue Beitragsart "Brandsicherheitsdienste (BSDs)" hinzu. Im Adminbereich von WordPress lassen sich damit Dienste zu Veranstaltungen mit wichtigen zugeh&ouml;rigen Informationen wie der Ort, das Datum, die Startzeit, die Anzahl der ben&ouml;tigten Dienst-Teilnehmer und sonstige Informationen zur Veranstaltung anlegen.

Neben der Verwaltung spielt auch die Benachrichtigung der User eine wichtige Rolle. Wenn ein User einem Termin fest zugesagt wird, erh&auml;lt dieser eine entsprechende E-Mail mit allen n&ouml;tigen Informationen. Weniger Aufwand und mehr &Uuml;bersicht f&uuml;r die F&uuml;hrung der Organisation sind das Ergebnis.

Die prim&auml;re Zielgruppe des Plugins sind Feuerwehren im deutschsprachigen Raum, es ist aber genauso geeignet f&uuml;r Rettungsdienste, die Wasserwacht, das THW und sonstige Hilfsorganisationen, die ihre Dienste zentral und koordiniert an ihre Mannschaft verteilen m&ouml;chten.

Funktionen im &Uuml;berblick:

* Anlegen der Dienste in gewohnter Art und Weise, wie Beitr&auml;ge und Seiten
* Anzeigen aller zuk&uuml;nftigen Dienste auf der Homepage per Shortcode "BSD_Panel" oder "BSD_Table"
* Zugriff auf die ver&ouml;ffentlichten Dienste nur f&uuml;r angemeldete User
* Markieren von Wachf&uuml;hrern (Verantwortlichen) &uuml;ber das User-Profil
* E-Mail Benachrichtigungen bei Vergabe der Dienste an betreffende Personen sowie bei R&uuml;ckzug vom Dienst
* Regelmäßige Benachrichtigung aller User per E-Mail bei neuen Diensten
* Responsive Design (Mobil-f&auml;hig)

== Installation ==

Das Plugin kann entweder aus WordPress heraus aus dem [Pluginverzeichnis](https://wordpress.org/plugins/bsd-verwaltung/) installiert werden oder aber durch Hochladen der Plugindateien in das Verzeichnis `/wp-content/plugins/`.

In beiden F&auml;llen muss das Plugin erst aktiviert werden, bevor es benutzt werden kann.

__Es wird PHP 5.3.0 oder neuer ben&ouml;tigt__

== Frequently Asked Questions ==

= Wie erstelle ich einen neuen Dienst? =

Nach Aktivierung des Plugins erscheint im Adminbereich ein neuer Men&uuml;punkt "BSDs". Dort kann ein neuer Dienst erstellt werden.

= Wie zeige ich die Dienste auf der Homepage an? =

Erstellt eine neue Seite und f&uuml;gt dort den Shortcode `[BSD_Panel]` ein.

= Ich habe einen Fehler im Plugin gefunden oder einen Verbesserungsvorschlag =

Ich freue mich immer &uuml;ber Feedback. Entweder ihr erstellt ein neues Topic im Support-Bereich auf der [Plugin-Seite](https://wordpress.org/plugins/bsd-verwaltung/), erstellt einen Issue auf [GitHub](https://github.com/mreichardt1992/wp-bsd-verwaltung/issues) oder schickt mir eine Nachricht &uuml;ber das [Kontaktformular](http://bsd-verwaltung.de) auf meiner Homepage.

= Gibt es eine Dokumentation zu diesem Plugin? =

Ja. Auf [bsd-verwaltung.de](http://bsd-verwaltung.de) findet Ihr die Doku.

== Screenshots ==

1. Auflistung der Dienste auf der Homepage
2. Detailansicht eines Dienstes auf der Homepage
3. &Uuml;bersicht der Felder im Admin-Bereich

== Changelog ==

= 1.2.3 =
* Bugfix für Mailversand, einige Platzhalter wurden nicht korrekt ersetzt

= 1.2.2 =
* Bugfix für die Datenbank, nachdem nach dem letzten Update eine Tabellenspalte fehlte

= 1.2.1 =
* Überarbeitung der Liste der gemeldeten User im Backend
* explizite Auswahl eines Wachführers hinzugefügt

= 1.2.0 =
* Abgelaufene BSDs werden nun im Dashboard in den Papierkorb verschoben
* Das Feld "Wachführer" im Userprofil funktionierte nicht
* Eine Option für tägliche Benachrichtigungen bei neuen Diensten wurde eingebaut
* Der Button "Melden" im Frontend ist nun deaktiviert, wenn alle Posten im Dienst besetzt sind
* Das Styling der Buttons im Frontend wurde nochmals überarbeitet
* Es wurde eine neue Art der Auflistung für Dienste im Frontend hinzugefügt (Tabelle)

= 1.1.3 =
* Neues, einheitliches Design für die Buttons im Frontend (Template-unabhängig)

= 1.1.2 =
* Encoding-Fehler korrigiert, wodurch bei der Aktivierung eine Fehlermeldung auftrat

= 1.1.1 =
* Fehler in Version 1.1 beseitigt

= 1.1 =
* Seite für Einstellungen hinzugefügt
* Sichtbarkeit von BSDs für nicht-eingeloggte User einstellbar
* Farben der BSD Panele auf der Homepage einstellbar
* Texte der Benachrichtigungsmails änderbar (mit Platzhaltern für BSD Daten)
* Date- und Timepicker im Backend eingebaut
* Änderungen der Texte in Buttons
* Diverse Kleinigkeiten unter der Haube verbessert

= 1.0 =
* Erste Version
* Verwaltung von Diensten als eigener Beitragstyp
* Einbinden der zuk&uuml;nfigen Dienste per Shortcode
* E-Mail Benachrichtigungen bei Zu-/Absagen zu Diensten