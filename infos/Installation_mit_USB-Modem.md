# Installation mit USB-Faxmodem und Hylafax
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
Für den Betrieb des Alarmdisplays brauchen Sie auf Ihrem Rechner einen 
Faxserver, einen mySQL-Datenbankserver und einen Webserver. Installieren Sie die 
Pakete HylaFax, mySQL und Apache bzw. lighttpd nach. Dies können Sie über die 
Paketverwaltung Ihrer Distribution oder mit dem Befehl

`sudo apt-get install mysql-server hylafax-server lighttpd`

erledigen. Alternative zu lighttpd ist Apache.
Merken Sie sich das bei der Installation gesetzte Datenbank-Master-Passwort!


### Weitere Tools
Installieren Sie auch folgende Softwarepakete:

`sudo apt-get install openssl build-essential libssl-dev libxrender-dev libqt-dev`
`sudo apt-get install qt-dev-tools motion imagemagick cuneiform xdotool php curl`


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
Konfigurieren Sie einen Drucker so, dass er über das `lp`-Kommando angesprochen 
werden kann. Diese Aufgabe können Sie einfach über die grafische 
Benutzeroberfläche Ihrer Distribution erledigen.


### Konfigurieren Sie den Faxserver
Schließen Sie das Modem an den PC an und führen Sie `sudo faxsetup`, 
anschließend dann `sudo faxaddmodem` aus. Die Installationsassistenten richten 
dann Modem und Faxserver ein. (Tipp: Wenn Sie ein USB-Modem besitzen, ist die 
richtige Schnittstelle meist `ttyACM0`).

Wenn Sie ein USB-Modem von US-Robotics (6537??) nutzen, könnte die im Paket 
enthaltene Konfigurationsdatei nützlich sein - benennen Sie dazu die 
`configuration/config.ttyACM0_USR_USB_MODEM` in `configuration/config.ttyACM0` 
um und kopieren Sie die Datei in den Hylafax-Konfigurationsordner. 


### Installieren Sie eine Texterkennungssoftware
Im Pilotprojekt brachte die Software `cuneiform` die besten Ergebnisse - Sie 
können aber auch `tesseract-ocr` verwenden. Mit den im Repository enthaltenen 
*traineddata-Dateien liefert Tesseract mittlerweile sehr gute Ergebnisse.

Installation von cuneiform: `sudo apt-get install cuneiform`
Installation von Tesseract: `sudo apt-get install tesseract-ocr tesseract-ocr-deu`
Bei der Verwendung von Tesseract können Sie die enthaltene Trainingsdatei 
`configuration/ils.traineddata` bzw. `configuration/deu.traineddata` in den Ordner 
`/usr/share/tesseract-ocr/tessdata/` kopieren - dies hilft Ihnen, die ILS-Faxe 
schneller einzulesen. Es ist im Vorfeld zu ermitteln, welche Trainingsdatei die besten
Ergebnise liefert - hierzu Tesseract auf der Kommandozeile mit den verschiednenen
Parametern starten.


## Installieren der Alarmdisplay-Software
### Dateien kopieren
Erstellen Sie im Wurzelverzeichnis des Webservers (meist `/var/www/html`) ein 
Verzeichnis `alarmdisplay-ffw` und entpacken Sie darin das ZIP-Archiv der Software.
Sollte das Wurzelverzeichnis nicht `/var/www/html` sein, müssen Sie alle Pfade innerhalb 
der Programme manuell anpassen.


### Legen Sie eine Datenbank an
Legen Sie auf Ihrem Datenbankserver mit der vorgegebenen Datei 
`configuration/alarmdisplay_database.sql` die für den Betrieb des Systems 
notwendige Datenbank an. Dies können Sie z.B. mit der in PHPmyAdmin enthaltenen 
Import-Funktion bzw. den mySQL-Bordmitteln erledigen:

`shell> mysql -u username -p`
`mysql> source configuration/alarmdisplay_database.sql`


### Schaffen Sie die Brücke zwischen Faxserver und Faxverarbeitung
Konfigurieren Sie den Hylafax-Server so, dass er bei jedem eingegangenen Fax 
die Texterkennung anstösst und die Daten an das Einlese-Skript 
`ocr/readfile.php` weitergibt.

Ergänzen Sie dazu die Datei `/var/spool/hylafax/bin/faxrcvd` mit folgenden 
Zeilen:

```bash
#######################################
## start of alarmfax progess
# variables
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
TMPFAX=/tmp/alarm
SHYLAFAX=/var/spool/hylafax

# program
mkdir -p $TMPFAX
cuneiform --singlecolumn --fax -l ger -o $TMPFAX/latest-fax.txt $SHYLAFAX/$FILE
# tesseract $SHYLAFAX/$FILE $TMPFAX/latest-fax.txt -l ils -psm 6
cd /var/www/html/alarmdisplay-ffw/ocr
php readfile.php $TMPFAX/latest-fax.txt $SHYLAFAX/$FILE
rm -rf $TMPFAX
```

Dieses Bash-Skript kann unter `configuration/faxrcvd` gefunden werden.

Abhänig davon welche Texterkennung verwendet wird, muss entweder die Zeile mit 
`cuneiform` oder `tesseract` auskommentiert oder entfernt werden.

Verwenden Sie die Option `-l ils` nur dann, wenn Sie die trainierte 
Sprachdatei wie oben beschrieben verwenden. Wenn Sie Tesseract out-of-the-box 
verwenden, lautet die Option `-l deu` (tesseract-ocr-deu = Deutsches 
Sprachpaket muss aber dann installiert sein).


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
Die Erfahrung zeigt, dass das Faxen eines bestehenden Papierfaxes an das 
Display auch in feinster Auflösung nicht zur gewünschten Texterkennungsrate 
führt, um ausreichende Tests durchzuführen. Deswegen können Sie zum Testen der 
Anzeige das Empfangen des Faxes überspringen und Testdaten händisch in die 
Datenbank einspielen. Hierzu können Sie die in diesem Repository enthaltene 
Alarmfax `configuration/testfax.tif` nutzen. Auf der Konsole können Sie mit 

```bash
/var/spool/hylafax/bin/faxrcvd \
  "configuration/testfax.tif" \
  "ttyACM0" \
  "000000001"
```

die Daten per Hand einspielen. Dieser Befehl simuliert die Übergabe des 
umgewandelten Alarmfax an die Einleseroutine. 
**Achtung:** Prüfen Sie trotzdem vorab durch Testfaxe, ob Ihr Faxserver 
grundsätzlich Faxe richtig annimmt.


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
  durch Reguläre Ausdrücke Informationen wie Mitteiler, Straße und 
  Hausnummer gesammelt. Diese Ausdrucke müssen an das Alarmfax der Örtlichen 
  Rettungsleitstelle angepasst werden. Da die Informationen aus einem OCR 
  Vorgang stammen müssen mit Fehler gerechnet werden, dass zum Beispiel statt 
  einem `ö` ein `o` erkannt wird. Dies sollte bei dem Entwerfen der Regulären 
  Ausdrücke beachtet werden.


## Tipps und Tricks
Weitere Tipps und Tricks sowie die FAQ finden sie im [Repository-Wiki auf 
GitHub](https://github.com/windele/alarmdisplay-ffw/wiki)

# Undokumentierte Funktionen
Die Anzeige der FMS-Status bzw. die Anbindung eines externen Systemes ist noch 
immer nicht dokumentiert. 



