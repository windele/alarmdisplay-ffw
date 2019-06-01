# Inhalt
Diese Datei beschreibt die Installation der Display-Software auf einem Raspberry-Pi. 
Es wird angenommen, dass die Faxdateien von einer FritzBox bereitgestellt werden. 
Die Installation des Faxservers Hylafax auf dem Raspberry wurde nicht getestet.
Es wird nicht jeder Schritt im Detail dargestellt, es wird ein gewisser Umgang mit dem Raspberry-System vorausgesetzt.

# Grundinstallation
Installieren Sie auf der SD-Karte des Raspberry Pi ein Linux-Image, z.b. Raspbian. 
Ich verwende das offizielle Lite-Image.

## Erster Start des Raspberry
Der User 'pi' kann sich mit dem Passwort 'raspberry' anmelden - Achtung: Tasturlayout ist noch englisch, also Passwort 'raspberrz' eingeben.
Nach dem ersten Start des Raspberry wird der Befehl `sudo raspi-config` ausgeführt, um die Grundeinstellungen des Systems zu tätigen:
Es werden die Lokalisation (Deutschland), die Zeitzone, das Tastaturlayout, der Hostname, und "warten auf Netzwerk beim booten" eingestellt sowie das Dateisystem auf die komplette Größe der SD-Karte expandiert.

Danach folgt ein Update der Software mit `rpi-update`.

Anschließend installieren wir die Updates des Betriebssystems:
````bash
sudo apt-get update
sudo apt-get upgrade
sudo apt-get dist-upgrade
````

Dann folgt schon der erste Schwung an Software:
`sudo apt-get install chromium-browser lxde cups tesseract-ocr tesseract-ocr-deu unclutter`

Nach der Installation kann wiederum mit `sudo raspi-config` der Autologin in den graphischen Desktop eingestellt werden.

Es wird wieder Software installiert:

```bash
sudo apt-get install php-common php-cgi php php-curl mysql-server mysql-client php-mysql wkhtmltopdf
sudo apt-get install lighttpd xdotool
sudo apt-get install phpmyadmin watchdog inotify-tools
````

Wir fügen den User pi der Gruppe lpadmin hinzu, damit Drucker eingerichtet werden können:
`sudo usermod -aG lpadmin pi`

Anschließend ändern wir das Passwort für den mySQL-Administrator:
````bash
sudo su
mysql
update mysql.user set password = password('admin') where user = 'root';
update mysql.user plugin = '' where user = 'root'
flush privileges;
```` 

## Installieren der Alarmdisplay-Software
### Dateien kopieren
Erstellen Sie im Wurzelverzeichnis des Webservers (meist `/var/www/html`) ein 
Verzeichnis `alarmdisplay-ffw` und entpacken Sie darin das ZIP-Archiv der Software.
Sollte das Wurzelverzeichnis nicht `/var/www/html` sein, müssen Sie alle Pfade innerhalb 
der Programme manuell anpassen.


Für die Anbindung an die FritzBox werden folgende Verzeichnisse angelegt: 

`mkdir /media/fritzbox`  --> hier wird das faxbox-Verzeichnis der Fritzbox gemountet
`mkdir /home/pi/faxarchiv`  --> hier wird jedes Alarmfax nach dem Einlesen abgelegt

Da bei den Tests mit aktuellen Fritzboxen beim Eintreffen eines Alarmfax sporadisch auch das vom vorherigen Einsatz nochmals eingelesen wurde, löscht das Überwachungsskript nach dem Einlesen das Fax von der FritzBox und archiviert es im Homeverzeichnis. 

Folgende Dateien müssen kopiert werden:
````bash
cp /var/www/html/alarmdisplay-ffw/configuration/.smbcredentials /home/pi
cp /var/www/html/alarmdisplay-ffw/configuration/.xsessionrc /home/pi
cp /var/www/html/alarmdisplay-ffw/configuration/autostart /home/pi/.config/lxsession/LXDE-pi/
cp /var/www/html/alarmdisplay-ffw/configuration/ils.traineddata /usr/share/tesseract-ocr/tessdata/
cp /var/www/html/alarmdisplay-ffw/configuration/deu.traineddata /usr/share/tesseract-ocr/tessdata/
````

Passen Sie in der Datei `/home/pi/.smbcredentials` das Passwort der FritzBox an.

Fügen Sie der Datei `/etc/fstab` als Administrator folgende Zeile hinzu:
`//192.168.178.1/FRITZ.NAS/FRITZ/	/media/fritzbox	cifs	credentials=/home/pi/.smbcredentials,uid=1000,gid=1000,vers=1.0	0	0`

Leeren Sie jetzt das Verzeichnis faxbox auf der Fritzbox - beim nächsten Systemstart wird jede neue Datei in das Alarmdisplay eingelesen.
Starten Sie deswegen mit einem leeren Verzeichnis.

Fügen Sie der Datei `/etc/rc.local` als Administrator vor der Zeile `exit 0` folgende Zeilen hinzu:
````
sudo -u pi /var/www/html/alarmdisplay-ffw/fritzbox/fritzbox_fax_synchron.sh&
sudo -u pi /var/www/html/alarmdisplay-ffw/fritzbox/fritzbox_fax_ueberwachen.sh&
````

Achtung: in der `/var/www/html/alarmdisplay-ffw/fritzbox/fritzbox_fax_ueberwachen.sh` muss bei einem Einsatz mit einer neueren Tesseract-Version der Parameter `-psm 6` in `--psm 6` geändert werden. Es ist auch zu prüfen, welche der *.traineddata-Dateien auf dem Zielsystem die beste Erkennung bietet. Dazu in der Kommandozeile den tesseract-Befehl mit diversen Sprachen (`-l deu` oder `-l ils`) durchführen und die Ergebnisse vergleichen.


Zur Optimierung des stabilen Betriebes kann noch folgende Webseite besucht werden: www.datenreise.de/raspberry-pi-stabiler-24-7-dauerbetrieb

Für den convert-Befehl existiert derzeit ein Fehler, der durch Ändern der Zeile
````
<policy domain="coder" rights="none" pattern="PDF" />
````
in
````
<policy domain="coder" rights="read | write" pattern="PDF" />
````
in der Datei `/etc/ImageMagick-7/policy.xml`behoben werden kann.




### Richten Sie sich einen Standarddrucker ein
Konfigurieren Sie einen Drucker so, dass er über das `lp`-Kommando angesprochen 
werden kann. Diese Aufgabe können Sie einfach über die grafische 
Benutzeroberfläche Ihrer Distribution erledigen bzw. über die Webseite von CUPS: http://localhost:631

### Legen Sie eine Datenbank an
Legen Sie auf Ihrem Datenbankserver mit der vorgegebenen Datei 
`configuration/alarmdisplay_database.sql` die für den Betrieb des Systems 
notwendige Datenbank an. Dies können Sie z.B. mit der in PHPmyAdmin enthaltenen 
Import-Funktion bzw. den mySQL-Bordmitteln erledigen:

`shell> mysql -u username -p`
`mysql> source configuration/alarmdisplay_database.sql`


### Konfigurieren Sie Benutzernamen und Passwörter für den Datenbankzugriff
Editieren Sie die Datei `config.inc.php` und stellen Sie dort die Benutzernamen 
und Passwörter für den Datenbankzugriff ein. Wir empfehlen dringend die 
Absicherung der Datenbank mit Benutzernamen und Passwort!


### Anzeige konfigurieren 
Rufen Sie mit einem Browser auf der grafischen Benutzeroberfläche die Webseite 
`http://IP-Adresse des Servers/alarmdisplay-ffw/` bzw. direkt auf der Maschine `http://localhost/alarmdisplay-ffw/` auf. Sie sollten jetzt eigentlich 
die Uhr sehen.
Konfigurieren Sie diese Seite als Startseite und stellen Ihr Linux-System so 
ein, dass der Browser beim Systemstart immer gestartet wird.


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



