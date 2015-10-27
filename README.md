# Allgemeines
## Nutzen der Software
Diese Software kann bei Feuerwehren dazu verwendet werden, die Informationen 
des Alarmfax auf einem Bildschirm darzustellen. Insbesondere werden die 
Informationen so aufbereitet, dass der Einsatzort und die Anfahrt dorthin auf 
einer Karte visualisiert werden. 


## Funktionsumfang
- **Digitale Uhr**: Im Ruhezustand zeigt das Alarmdisplay in großen Lettern 
  eine Uhr und das Datum an. 
- **Hinweistexte**: Das Alarmdisplay kann als digitales Schwarzes Brett genutzt 
  werden. Es können bis zu fünf Hinweistexte hinterlegt werden, die abwechselnd 
  den Bildschirm füllen. Jeder Hinweistext kann mit einem Start- und Ende-Datum 
  versehen werden, was eine automatische Anzeige ermöglicht.
- **Einsatzvisualisierung und Einsatzkräfteinfo**: Im Alarmfall zeigt das 
  Alarmdisplay die Einsatzdaten aus dem Alarmfax an. Zusätzlich wird der 
  Einsatzort und die Anfahrt dorthin auf einer Karte visualisiert. Ein Versand 
  der Daten per E-Mail an die Einsatzkräfte, der Versand einer SMS 
  (kostenpflichtig über den Dienstleister [RETTalarm](http://www.rettalarm.de) 
  (RETTalarm ist ein eingetragenes Warenzeichen der ide-tec KG) oder der 
  Ausdruck einer Anfahrtsroutenbeschreibung sind ebenfalls Teil des Programms.
- **Administrationsmenü**: Fast alle Parameter der Software können über ein 
  Administrationsmenü eingestellt werden.


## Historie
Die Software wurde bei der [Freiwilligen Feuerwehr 
Piflas](http://www.feuerwehr-piflas.de) im Jahr 2012 zum ersten Mal eingesetzt. 
Seit Mitte 2012 läuft die Software ohne Komplikationen durch und wurde im Laufe 
der Zeit um weitere Funktionen ergänzt.

Viele der enthaltenen Funktionen sind deswegen speziell auf die Belange dieser 
Feuerwehr bzw. deren Umgebung angepasst. Vielleicht ändert sich weiterhin im 
Laufe der Zeit der Funktionsumfang im Rahmen der Weiterentwicklung.


## Realisation & Systemvoraussetzungen
Die Software wurde in PHP geschrieben; die Visualisierung der Informationen 
erfolgt in Form einer Webseite. Bei der Webseitenanzeige muss im Browser 
JavaScript aktiviert sein, sonst funktionieren Uhr, Hinweistexte und 
Aktualisierung nicht.

Für einzelne Funktionen (z.B. Druck des Anfahrtsplanes, etc.) wird auf weitere 
externe Software zurückgegriffen. Für die optimale Funktion wird ein 
Linux-System vorausgesetzt.

- **Datenquelle**: Als Datenquelle verwenden wir das Alarmfax, welches von der 
  Leitstelle bei Alarmierung an die Feuerwehr übermittelt wird. Auf unserem 
  Server nimmt dies die Faxsoftware *HylaFax* über ein *analoges USB-Faxmodem* 
  an und löst gleichzeitig die nächste Stufe, die Texterkennung aus. Als 
  Texterkennungssoftware haben wir im laufenden System auf *Cuneiform* gesetzt, 
  da dies untrainiert im Test die besten Erkennungsraten lieferte. Aktuell 
  würde ich aber *Tesseract* empfehlen. Aus dem TIF-Faxdokument wird so eine 
  TXT-Datei generiert.
- **Verarbeitung**: Nach der Texterkennung wird das digitalisierte Fax an das 
  PHP-Script `ocr/readfile.php` übergeben. Dieses Skript ist dafür 
  verantwortlich, das Fax zu prüfen (Wurde uns wirklich von der Leitstelle ein 
  Fax geschickt oder ist es Werbung?) und die Inhalte in eine *mySQL-Datenbank* 
  zu schreiben. Da dieses Skript bei jedem Faxeingang aufgerufen wird, 
  übernimmt es auch den Versand der Daten per E-Mail, die Auslösung einer 
  RETTalarm-SMS und die Generierung der Ausdrucke. Die Ausdrucke werden in Form 
  eines Screenshots der Webseite realisiert, dies übernimmt die Software 
  [wkhtmlmtopdf](http://code.google.com/p/wkhtmltopdf/) bzw. deren Teil 
  wkhtmltoimage. Um die Daten in Druckform zu bringen, werden die Screenshots 
  mit *convert* aus dem *ImageMagick*-Paket verarbeitet.
- **Ausgabe**: Die Skripte des Alarmdisplays überprüfen regelmässig durch eine 
  *JavaScript*-Funktion die Datenbank. Wenn ein aktueller Einsatz festgestellt 
  wird, so wechselt die Anzeige von der Uhr bzw. dem digitalen schwarzen Brett 
  zur Einsatzanzeige. Alle Daten des Faxes werden dargestellt und der 
  Einsatzort soweit möglich auf einer Karte mit Google Maps dargestellt. Um 
  diese Visualisierung so darstellen zu können, wird ein installierter 
  Webserver (z.B. *Apache* oder *lighttpd*) sowie eine Internetverbindung (für 
  GoogleMaps) benötigt. Die Darstellung selbst erfolgt mit einem Browser (bei 
  uns *Mozilla Firefox*), der im Kiosk-Modus (Vollbild -> F11) läuft.
- **Hardware**: Die Software läuft erfolgreich im 24/7-Betrieb auf einem 
  lüfterlosen Industrie-PC, der direkt an einen Bildschirm gekoppelt ist. Als 
  Drucker wird ein von Linux unterstützter Netzwerkdrucker eingesetzt. Es 
  sollte aber jeder einigermaßen leistungsfähige PC mit Linux funktionieren. 
  (*RaspberryPi* funktioniert in der Theorie auch, leider ist die 
  Prozessorperformance dem Einsatzzweck als Feuerwehrinformationsmittel nicht 
  gewachsen. Wir experimentieren aktuell mit leistungsfähigeren 
  ARM-Prozessoren.) Die Erfahrung zeigt, dass ein großer Bildschirm die 
  Informationsaufnahme bei den Einatzkräften erhöht.


## Grenzen
- Die Software kann aktuell keine Einsatzorte auf der Autobahn visualisieren 
  bzw. die Anfahrt dorthin berechnen. Für unseren Einsatzbereich wurden die 
  verschiedenen Autobahnabschnitte der A92 im Skript `ocr/readfile.php` 
  codiert, um eine optimiertere Anzeige zu erreichen.
- Die Software kann nur dann eine genaue Anfahrt errechnen, wenn die Adresse 
  einwandfrei von der Leitstelle übermittelt wurde und GoogleMaps diese 
  auflösen kann.
- Die Routenberechnung erfolgt für PKW; temporäre Straßensperrungen, 
  Gewichtsbeschränkungen oder Durchfahrtshöhen sind nicht berücksichtigt. Eine 
  gewisse Ortskenntnis der Einsatzfahrzeugfahrer wird also vorausgesetzt.
- FMS-Status können nur dann dargestellt werden, wenn ein externes System die 
  Status an die Datenbank übermittelt. In einer standalone-Standardinstallation 
  geht dies nicht.


## Danke
Für das Projekt "Alarmdisplay" wurde auf weitere Software zurückgegriffen, bei 
deren Autoren ich mich herzlich bedanken möchte.
Verwendet wurden:
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) zum Versenden der E-Mails
- [PHPMyEdit](http://www.phpmyedit.org/) im Administrationsmenü
- [JQuery](http://www.jquery.com) im Administrationsmenü
- [BluePrintCSS](http://www.blueprintcss.org) für die Ausrichtung 
- [GoogleMaps](http://maps.google.de) für das Routing und die Kartenanzeige.


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
Für den Betrieb des Alarmdisplays brauchen Sie auf Ihrem Rechner einen 
Faxserver, einen mySQL-Datenbankserver und einen Webserver. Installieren Sie die 
Pakete HylaFax, mySQL und Apache bzw. lighttpd nach. Dies können Sie über die 
Paketverwaltung Ihrer Distribution oder mit dem Befehl

`sudo apt-get install mysql-server hylafax-server lighttpd`

erledigen. Alternative zu lighttpd ist Apache.
Merken Sie sich das bei der Installation gesetzte Datenbank-Master-Passwort!


### Weitere Tools
Installieren Sie auch folgende Softwarepakete:

`sudo apt-get install openssl build-essential libssl-dev libxrender-dev libqt4-dev`
`sudo apt-get install qt4-dev-tools motion imagemagick cuneiform xdotool php5 curl`


### Screenshot-Tool
Für den korrekten Ausdruck ist ein Screenshot-Tool notwendig - ich empfehle 
wkhtmltopdf. Diese Software können Sie einfach herunterladen: 
[wkhtmltopdf](http://code.google.com/p/wkhtmltopdf/).
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
Verzeichnis `alarmdisplay` und entpacken Sie darin das ZIP-Archiv der Software.


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
cd /var/www/alarmdisplay/ocr
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
`http://IP-Adresse des Servers/alarmdisplay/` auf. Sie sollten jetzt eigentlich 
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
  69 bis 100 durch Reguläre Ausdrücke Informationen wie Mitteiler, Straße und 
  Hausnummer gesammelt. Diese Ausdrucke müssen an das Alarmfax der Örtlichen 
  Rettungsleitstelle angepasst werden. Da die Informationen aus einem OCR 
  Vorgang stammen müssen mit Fehler gerechnet werden, dass zum Beispiel statt 
  einem `ö` ein `o` erkannt wird. Dies sollte bei dem Entwerfen der Regulären 
  Ausdrücke beachtet werden.


## Tipps und Tricks
Weitere Tipps und Tricks sowie die FAQ finden sie im [Repository-Wiki auf 
GitHub](https://github.com/windele/alarmdisplay-ffw/wiki)


# Support
Gerne können Sie mich per Mail erreichen. Leider kann ich aber aus Zeitgründen 
und der Fülle der Anfragen keinen umfangreichen kostenfreien Support leisten. 
Stellen Sie Ihre Frage trotzdem, vielleicht gibt es eine schnelle und kostenlose 
Lösung.


# Undokumentierte Funktionen
Die Anzeige der FMS-Status bzw. die Anbindung eines externen Systemes ist noch 
nicht dokumentiert. Dies folgt.

# Geplante Änderungen und Erweiterungen
Es ist geplant, die Software so anzupassen, dass sie auch auf Mini-PC's mit 
ARM-Prozessoren läuft. Hierzu ist auch die Anpassung an die Texterkennung 
*Tesseract* geplant, da nur diese auf der ARM-Plattform läuft.

# Referenzen
Ich würde mich freuen, wenn Sie mir ein kurzes Feedback geben, wo die Software 
überall im Einsatz ist. Dies können Sie mir per Mail mitteilen oder direkt in 
das [Repository-Wiki auf 
GitHub](https://github.com/windele/alarmdisplay-ffw/wiki/Einsatzorte-der-
Software-und-Referenzen) schreiben.


# Haftungsausschluss und Datenschutz
Ich übernehme keine Haftung für die Funktion der Software vor Ort.
Da über diese Software sensible personenbezogene Daten verarbeitet werden, ist 
der Datenschutz vor Ort insbesondere zu beachten. Vor allem sollte ein Augenmerk 
auf die *Datensparsamkeit* gelegt werden. Damit verbunden ist die strenge 
Entscheidung, wer welche Daten per E-Mail oder SMS weitergeleitet bekommt. Im 
Zweifel ist der Programmcode entsprechend anzupassen.
