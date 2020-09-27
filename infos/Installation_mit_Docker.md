# ACHTUNG! Diese Anleitung eignet sich noch nicht zum Produktivbetrieb
Folgende offene Punkte sind noch zu klären: 
- mariadb Container nicht mit root-Benutzer benutzen

Des Weiteren ist die Dokumentation leider noch nicht vollständig. Orientieren Sie sich für das Erste an der Datei [Installation Raspberry Pi](Installation_auf_Raspberry_Pi.md).

# Inhalt
Diese Datei beschreibt die Installation der Display-Software in einem Docker Container. 
Es wird nicht jeder Schritt im Detail dargestellt, es wird ein gewisser Umgang mit Docker und docker-compose vorausgesetzt.

# Grundinstallation
Docker und docker-compose ist installiert und lauffähig.  
Alle benötigten Pakete werden über das Dockerfile in einen Docker Container installiert.  
Als Datenbank wird ein mariadb Container verwendet, der beim Starten des Containers entsprechend initialisiert wird.  

### Konfigurieren Sie Benutzernamen und Passwörter für den Datenbankzugriff
Editieren Sie die Datei `config.inc.php` und stellen Sie dort die Host Benutzernamen und Passwörter für den Datenbankzugriff ein. Folgende Parameter müssen gesetzt werden: 
- DBHOST = mariadb
- DBUSER = root
- DBPASS= example
Für den Produktivbetrieb sollte nicht root als Benutzer verwendet werden! 

### Anzeige konfigurieren 
Rufen Sie mit einem Browser auf der grafischen Benutzeroberfläche die Webseite 
`http://IP-Adresse des Servers:8888/alarmdisplay-ffw/` bzw. direkt auf der Maschine `http://localhost:8888/alarmdisplay-ffw/` auf. Sie sollten jetzt eigentlich 
die Uhr sehen.

## Testfax
Unter `configuration/testfax.pdf` ist eine einfache Textdatei als PDF zu finden, die ein Testfax simulieren soll. Um das Testfax im Docker Container testen zu können, kann man in dem Container eine interaktiver Shell mit `docker exec -it [container_name] bash` aufmachen. Darin kann man anschließend mit dem Befehl `cp /var/www/html/alarmdisplay-ffw/configuration/testfax.pdf /var/www/html/alarmdisplay-ffw/fritzbox/faxbox/testfax.pdf` das Testfax in den entsprechenden Ordner kopieren. 

## Dateien außerhalb des Contianers ablegen 
Für einen Produktivbetrieb könnte man die Dateien als `volume` in den Container an die stelle `/var/www/html/alarmdisplay-ffw/fritzbox/faxbox/` mounten. 
