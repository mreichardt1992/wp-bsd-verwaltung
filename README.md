=== BSD Verwaltung ===
Contributors: mreichardt1992
Donate link: http://www.bsd-verwaltung.de/donate
Tags: Feuerwehr, BSD, Rettung, Rettungsdienst, THW, HiOrg, Wasserwacht, Bergrettung, Dienstverwaltung, Dienst, Verwaltung
Requires at least: 3.4.0
Tested up to: 4.8
Stable tag: 0.1.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Darstellung, Verwaltung und Vergabe von (Brandsicherheits)Diensten der Feuerwehr an die Mannschaft. Auch für andere Hilfsorganisationen geeignet.

== Description ==

Dieses Plugin f&uuml;gt WordPress eine neue Beitragsart "BSDs" hinzu. Im Adminbereich von WordPress lassen sich damit Dienste zu Veranstaltungen mit wichtigen zugehörigen Informationen wie der Ort, das Datum, die Startzeit, die Anzahl der benötigten Dienst-Teilnehmer und sonstige Informationen zur Veranstaltung anlegen.

Die prim&auml;re Zielgruppe des Plugins sind Feuerwehren im deutschsprachigen Raum, es ist aber genauso geeignet f&uuml;r Rettungsdienste, die Wasserwacht, das THW und sonstige Hilfsorganisationen, die ihre Dienste zentral und koordiniert an ihre Mannschaft verteilen möchten.

Funktionen im &Uuml;berblick:

* Anlegen der Dienste in gewohnter Art und Weise, wie Beiträge und Seiten
* Anzeigen aller zukünftigen Dienste auf der Homepage per Shortcode "BSD_Panel"
* Zugriff auf die veröffentlichten Dienste nur für angemeldete User
* Markieren von Wachführern (Verantwortlichen) über das User-Profil
* E-Mail Benachrichtigungen bei Vergabe der Dienste an betreffende Personen sowie bei Rückzug vom Dienst
* Responsive Design (Mobil-fähig)

== Installation ==

Das Plugin kann entweder aus WordPress heraus aus dem [Pluginverzeichnis](https://wordpress.org/plugins/bsd-verwaltung/) installiert werden oder aber durch Hochladen der Plugindateien in das Verzeichnis `/wp-content/plugins/`.

In beiden F&auml;llen muss das Plugin erst aktiviert werden, bevor es benutzt werden kann.

__Es wird PHP 5.3.0 oder neuer ben&ouml;tigt__

== Frequently Asked Questions ==

= Wie erstelle ich einen neuen Dienst? =

Nach Aktivierung des Plugins erscheint im Adminbereich ein neuer Menüpunkt "BSDs". Dort kann ein neuer Dienst erstellt werden.

= Wie zeige ich die Dienste auf der Homepage an? =

Erstellt eine neue Seite und fügt dort den Shortcode `[BSD_Panel]` ein.

= Ich habe einen Fehler im Plugin gefunden oder einen Verbesserungsvorschlag =

Ich freue mich immer über Feedback. Entweder ihr erstellt ein neues Topic im Support-Bereich auf der [Plugin-Seite](https://wordpress.org/plugins/bsd-verwaltung/), erstellt einen Issue auf [GitHub](https://github.com/mreichardt1992/wp-bsd-verwaltung/issues) oder schickt mir eine Nachricht über das [Kontaktformular](http://bsd-verwaltung.de) auf meiner Homepage.

== Screenshots ==

1. Auflistung der Dienste im Backend
2. Auflistung der Dienste auf der Homepage

== Changelog ==

= 0.1.0 =
* Erste Version
* Verwaltung von Diensten als eigener Beitragstyp
* Einbinden der zukünfigen Dienste per Shortcode
* E-Mail Benachrichtigungen bei Zu-/Absagen zu Diensten

== Upgrade Notice ==

= 0.1.0 =
Erste Version