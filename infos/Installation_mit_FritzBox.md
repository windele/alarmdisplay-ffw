## Stand Mai 2019 - Die Dokumentation ist noch in Arbeit und daher unvollständig


# Installation
## Grundsystem
Für die komplette Funktion wird ein laufendes Linux-System mit grafischer 
Oberfläche angenommen. Konfigurieren Sie das System so, dass ein Benutzer 
automatisch angemeldet wird.
Es sollte ein Internet-Browser installiert sein, der den Vollbildmodus 
unterstützt. Konfigurieren Sie den Browser als Autostart-Programm, der beim 
Systemstart automatishc ausgeführt wird.
Stellen Sie u.U. auch im BIOS des Rechners ein, dass das System nach 
Stromausfall automatisch wieder startet - sonst ist Ihr System nach Stromausfall 
nicht betriebsbereit. Besser wäre es natürlich, das System mit einer USV 
abzusichern, damit dieser Fall gar nicht erst passiert.
Die weiteren Beschreibungen gehen davon aus, dass ein Ubuntu- bzw. 
Debian-System installiert wurde.


## Installation weiterer Tools
### Serverkomponenten
Für den Betrieb des Alarmdisplays brauchen Sie auf Ihrem Rechnereinen mySQL-Datenbankserver 
und einen Webserver. Installieren Sie die Pakete mySQL und Apache bzw. lighttpd nach. 
Dies können Sie über die Paketverwaltung Ihrer Distribution oder mit dem Befehl

`sudo apt-get install mysql-server lighttpd`

erledigen. Alternative zu lighttpd ist Apache.
Merken Sie sich das bei der Installation gesetzte Datenbank-Master-Passwort!


### Weitere Tools
Installieren Sie auch folgende Softwarepakete:

`sudo apt-get install openssl build-essential libssl-dev libxrender-dev libqt4-dev`
`sudo apt-get install qt4-dev-tools motion imagemagick cuneiform xdotool php5 curl`


### Screenshot-Tool
Für den korrekten Ausdruck ist ein Screenshot-Tool notwendig - ich empfehle 
wkhtmltopdf. Diese Software können Sie einfach herunterladen: 
[wkhtmltopdf](http://code.google.com/p/wkhtmltopdf/) bzw. aus ihrer Distribution
installieren (z.B. `sudo apt-get install wkhtmltopdf`).
Sie finden auf der angegebenen Seite auch Hilfestellung, wie Sie den 
heruntergeladenen Quelltext kompilieren und die fertigen Programme installieren 
können.

Damit die Software korrekt funktioniert, müssen Sie dem Benutzer des FaxServers 
den Zugriff auf den X-Server erlauben. Kopieren Sie dazu die Datei 
`.xsessionsrc` in das Home-Verzeichnis des Benutzers, der bei Betrieb des 
Displays angemeldet ist:  `cp /var/www/alarmdisplay/configuration/.xsessionrc 
~`. Beim nächsten Neustart sollte das Screenshot-Tool reibungslos funktionieren.

Als Alternative, falls der Zugriff auf den XServer nicht klappt, wäre der 
Einsatz des Tools vfb denkbar (Anleitung siehe 
[hier](http://ciaranmcnulty.com/converting-html-to-pdf-using-wkhtmltopdf)), 
allerdings müssen dann einige der Shellscripte angepasst werden.


### Installieren von nützlichen Tools
Installieren Sie PHPmyAdmin - erstens ist es ein nützliches Tool und es werden 
durch die Paketabhängigkeiten die Softwarepakete so eingerichtet, dass das 
Display laufen sollte.

`sudo apt-get install phpmyadmin`


### Richten Sie sich einen Standarddrucker ein
Konfiguriere Sie einen Drucker so, dass er über das `lp`-Kommando angesprochen 
werden kann. Diese Aufgabe können Sie einfach über die grafische 
Benutzeroberfläche Ihrer Distribution erledigen.


### Konfigurieren Sie die Synchronisation mit der FritzBox
Text fehlt

### Installieren Sie eine Texterkennungssoftware
Im Pilotprojekt brachte die Software `cuneiform` die besten Ergebnisse - Sie 
können aber auch `tesseract-ocr` verwenden.
Installation von cuneiform: `sudo apt-get install cuneiform`

Installation von Tesseract: `sudo apt-get install tesseract-ocr tesseract-ocr-deu`
Bei der Verwendung von Tesseract können Sie die enthaltene Trainingsdatei 
`configuration/ils.traineddata` in den Ordner 
`/usr/share/tesseract-ocr/tessdata/` kopieren - dies hilft Ihnen, die ILS-Faxe 
schneller einzulesen.


## Installieren der Alarmdisplay-Software
### Dateien kopieren
Erstellen Sie im Wurzelverzeichnis des Webservers (meist `/var/www`) ein 
Verzeichnis `alarmdisplay-ffw` und entpacken Sie darin das ZIP-Archiv der Software.
Sollte das Wurzelverzeichnis nicht `/var/www` sein, müssen Sie alle Pfade innerhalb 
der Programme manuell anpassen.


### Legen Sie eine Datenbank an
Legen Sie auf Ihrem Datenbankserver mit der vorgegebenen Datei 
`configuration/alarmdisplay_database.sql` die für den Betrieb des Systems 
notwendige Datenbank an. Dies können Sie z.B. mit der in PHPmyAdmin enthaltenen 
Import-Funktion bzw. den mySQL-Bordmitteln erledigen:

`shell> mysql -u username -p`
`mysql> source configuration/alarmdisplay_database.sql`


### Schaffen Sie die Brücke zwischen FritzBox und Verarbeitung
Text fehlt noch


### Konfigurieren Sie Benutzernamen und Passwörter für den Datenbankzugriff
Editieren Sie die Datei `config.inc.php` und stellen Sie dort die Benutzernamen 
und Passwörter für den Datenbankzugriff ein. Wir empfehlen dringend die 
Absicherung der Datenbank mit Benutzernamen und Passwort!


### Anzeige konfigurieren 
Rufen Sie mit einem Browser auf der grafischen Benutzeroberfläche die Webseite 
`http://IP-Adresse des Servers/alarmdisplay-ffw/` auf. Sie sollten jetzt eigentlich 
die Uhr sehen.
Konfigurieren Sie diese Seite als Startseite und stellen Ihr Linux-System so 
ein, dass der Browser beim Systemstart immer gestartet wird.


### Anzeige testen
Text fehlt


## Konfigurieren und Anpassen der Software
- **Konfiguration:** Die Konfiguration können Sie bequem über das 
  Administrationsmenü vornehmen. Dieses können Sie nach erfolgreicher 
  Installation mit dem Browser aufrufen. Wenn Sie die Installation wie in der 
  Anleitung beschrieben durchgeführt haben, erreichen Sie den 
  Administrationsbereich unter `http://IP-Adresse des 
  Servers/alarmdisplay/administrator`. Der Benutzername lautet `admin`, das 
  Passwort `admin`. Es wird aus Sicherheitsgründen empfohlen, das Passwort zu 
  ändern (möglich via Admin-Menü).
- **Anpassen der Software**. Mit Sicherheit werden Sie die ein oder andere 
  Anpassung an der Software vornehmen wollen. Meistens handelt es sich hierbei 
  um Anpassungen an ihr Alarmfax (wegen Aufbau und Struktur, der Texterkennung 
  oder Autobahnabschnitten) bzw. wegen Anpassungen bei der Weitergabe von 
  Informationen per Mail oder SMS. Diese Anpassungen können Sie alle im Skript 
  `ocr/readfile.php` vornehmen. Im Alarmfall leuchten die alarmierten Fahrzeuge 
  der eigenen Feuerwehr über der Einsatzanzeige rot auf. Wenn Sie die 
  angegebenen Fahrzeuge ändern wollen, nehmen Sie die Änderung in der 
  `modules/mod_einsatz.php` vor.
- **Reguläre Ausdrücke:** in der Datei `ocr/readfile.php` werden in den Zeilen 
  69 bis 100 durch Reguläre Ausdrücke Informationen wie Mitteiler, Straße und 
  Hausnummer gesammelt. Diese Ausdrucke müssen an das Alarmfax der Örtlichen 
  Rettungsleitstelle angepasst werden. Da die Informationen aus einem OCR 
  Vorgang stammen müssen mit Fehler gerechnet werden, dass zum Beispiel statt 
  einem `ö` ein `o` erkannt wird. Dies sollte bei dem Entwerfen der Regulären 
  Ausdrücke beachtet werden.


## Tipps und Tricks
Weitere Tipps und Tricks sowie die FAQ finden sie im [Repository-Wiki auf 
GitHub](https://github.com/windele/alarmdisplay-ffw/wiki)



# Haftungsausschluss und Datenschutz
Ich übernehme keine Haftung für die Funktion der Software vor Ort.
Da über diese Software sensible personenbezogene Daten verarbeitet werden, ist 
der Datenschutz vor Ort insbesondere zu beachten. Vor allem sollte ein Augenmerk 
auf die *Datensparsamkeit* gelegt werden. Damit verbunden ist die strenge 
Entscheidung, wer welche Daten per E-Mail oder SMS weitergeleitet bekommt. Im 
Zweifel ist der Programmcode entsprechend anzupassen.
