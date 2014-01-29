***BAUSTELLE***

Aktuell beschäfigte ich mich mit der Dokumentation der Software.
Wenn diese abgeschlossen ist, wird der Quelltext hier freigegeben.

# Generelle Beschreibung

## Nutzen der Software
Diese Software kann bei Feuerwehren dazu verwendet werden, die Informationen des Alarmfax auf einem Bildschirm darzustellen. Insbesondere werden die Informationen so aufbereitet, dass der Einsatzort und die Anfahrt dorthin auf einer Karte visualisiert werden. 

## Funktionsumfang

- **Digitale Uhr**: Im Ruhezustand zeigt das Alarmdisplay in großen Lettern eine Uhr und das Datum an. 
- **Hinweistexte**: Das Alarmdisplay kann als digitales Schwarzes Brett genutzt werden. Es können bis zu fünf Hinweistexte hinterlegt werden, die abwechselnd den Bildschirm füllen. Jeder Hinweistext kann mit einem Start- und Ende-Datum versehen werden, was eine automatische Anzeige ermöglicht.
- **Einsatzanzeige**: Im Alarmfall zeigt das Alarmdisplay die Einsatzdaten aus dem Alarmfax an. Zusätzlich wird der Einsatzort und die Anfahrt dorthin auf einer Karte visualisiert. Ein Versand der Daten per E-Mail an die Einsatzkräfte, der Versand einer SMS (kostenpflichtig über den Partner [RettAlarm](http://www.rettalarm.de) oder der Ausdruck einer Anfahrtsroutenbeschreibung sind ebenfalls Teil des Programms.
- **Administrationsmenü**: Fast alle Parameter der Software können über ein Administrationsmenü eingestellt werden.

## Historie
Die Software wurde bei der [Freiwilligen Feuerwehr Piflas](http://www.feuerwehr-piflas.de) zum ersten Male eingesetzt. Viele der enthaltenen Funktionen sind deswegen speziell auf die Belange dieser Feuerwehr bzw. deren Umgebung angepasst. Vielleicht ändert sich im Laufe der Zeit der Funktionsumfang im Rahmen der Weiterbildung.

## Realisation & Systemvoraussetzungen
Die Software wurde in PHP geschrieben; die Visualisierung der Informationen erfolgt in Form einer Webseite. Bei der Webseitenanzeige muss im Browser JavaScript aktiviert sein, sonst funktionieren Uhr, Hinweistexte und Aktualisierung nicht.
Für einzelne Funktionen (z.B. Druck des Anfahrtsplanes, etc.) wird auf weitere externe Software zurückgegriffen. Für die optimale Funktion wird ein Linux-System vorausgesetzt.

- **Datenquelle**: Als Datenquelle verwenden wir das Alarmfax, welches von der Leitstelle bei Alarmierung an die Feuerwehr übermittelt wird. Auf unserem Server nimmt dies die Faxsoftware *HylaFax* über ein *analoges USB-Faxmodem* an und löst gleichzeitig die nächste Stufe, die Texterkennung aus. Als Texterkennungssoftware haben wir im laufenden System auf *Cuneiform* gesetzt, da dies untrainiert im Test die besten Erkennungsraten lieferte. Aktuell würde ich aber *Tesseract* empfehlen. Aus dem TIF-Faxdokument wird so eine TXT-Datei generiert.

- **Verarbeitung**: Nach der Texterkennung wird das digitalisierte Fax an das PHP-Script `ocr/readfile.php` übergeben. Dieses Skript ist dafür verantwortlich, das Fax zu prüfen (Wurde uns wirklich von der Leitstelle ein Fax geschickt oder ist es Werbung?) und die Inhalte in eine *mySQL-Datenbank* zu schreiben. Da dieses Skript bei jedem Faxeingang aufgerufen wird, übernimmt es auch den Versand der Daten per E-Mail, die Auslösung einer RettAlarm-SMS und die Generierung der Ausdrucke. Die Ausdrucke werden in Form eines Screenshots der Webseite realisiert, dies übernimmt die Software [wkhtmlmtopdf](http://code.google.com/p/wkhtmltopdf/) bzw. deren Teil wkhtmltoimage. Um die Daten in Druckform zu bringen, werden die Screenshots mit *convert* aus dem *ImageMagick*-Paket verarbeitet.

- **Ausgabe**: Die Skripte des Alarmdisplays überprüfen regelmässig durch eine *JavaScript*-Funktion die Datenbank. Wenn ein aktueller Einsatz festgestellt wird, so wechselt die Anzeige von der Uhr bzw. dem digitalen schwarzen Brett zur Einsatzanzeige. Alle Daten des Faxes werden dargestellt und der Einsatzort soweit möglich auf einer Karte mit Google Maps dargestellt. Um diese Visualisierung so darstellen zu können, wird ein installierter Webserver (z.B. *Apache* oder *lighttpd*) sowie eine Internetverbindung (für GoogleMaps) benötigt. Die Darstellung selbst erfolgt mit einem Browser (bei uns *Mozilla Firefox*), der im Kiosk-Modus (Vollbild -> F11) läuft.

- **Hardware**: Die Software läuft erfolgreich im 24/7-Betrieb auf einem lüfterlosen Industrie-PC, der direkt an einen Bildschirm gekoppelt ist. Als Drucker wird ein von Linux unterstützter Netzwerkdrucker eingesetzt. Es sollte aber jeder einigermaßen leistungsfähige PC mit Linux funktionieren. (*RaspberryPi* funktioniert in der Theorie auch, leider ist die Prozessorperformance dem Einsatzzweck als Feuerwehrinformationsmittel nicht gewachsen. Wir experimentieren aktuell mit leistungsfähigeren ARM-Prozessoren.) Die Erfahrung zeigt, dass ein großer Bildschirm die Informationsaufnahme bei den Einatzkräften erhöht.

## Grenzen
- Die Software kann aktuell keine Einsatzorte auf der Autobahn visualisieren bzw. die Anfahrt dorthin berechnen. Für unseren Einsatzbereich wurden die verschiedenen Autobahnabschnitte der A92 im Skript `ocr/readfile.php` codiert, um eine optimiertere Anzeige zu erreichen.
- Die Software kann nur dann eine genaue Anfahrt errechnen, wenn die Adresse einwandfrei von der Leitstelle übermittelt wurde und GoogleMaps diese auflösen kann.
- Die Routenberechnung erfolgt für PKW; temporäre Straßensperrungen, Gewichtsbeschränkungen oder Durchfahrtshöhen sind nicht berücksichtigt. Eine gewisse Ortskenntnis der Einsatzfahrzeugfahrer wird also vorausgesetzt.

## Danke
Für das Projekt "Alarmdisplay" wurde auf weitere Software zurückgegriffen, bei deren Autoren ich mich herzlich bedanken möchte.
Verwendet wurden:
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) zum Versenden der E-Mails
- [PHPMyEdit](http://www.phpmyedit.org/) im Administrationsmenü
- [JQuery](http://www.jquery.com) im Administrationsmenü
- [BluePrintCSS](http://www.blueprintcss.org) für die Ausrichtung 
- [GoogleMaps](http://maps.google.de) für das Routing und die Kartenanzeige.


# Installation

***Baustelle*** 




# Konfigurieren und Anpassen
- **Konfiguration:** Die Konfiguration können Sie bequem über das Administrationsmenü vornehmen. Dieses können Sie nach erfolgreicher Installation mit dem Browser aufrufen. Wenn Sie die Installation wie in der Anleitung beschrieben durchgeführt haben, erreichen Sie den Administrationsbereich unter http://IP-Adresse des Servers/alarmdisplay/administrator. Der Benutzername lautet `admin`, das Passwort `admin`. Es wird aus Sicherheitsgründen empfohlen, das Passwort zu ändern (möglich via Admin-Menü).

- **Anpassen der Software**. Mit Sicherheit werden Sie die ein oder andere Anpassung an der Software vornehmen wollen. Meistens handelt es sich hierbei um Anpassungen an ihr Alarmfax (wegen Aufbau und Struktur, der Texterkennung oder Autobahnabschnitten) bzw. wegen Anpassungen bei der Weitergabe von Informationen per Mail oder SMS. Diese Anpassungen können Sie alle im Skript `ocr/readfile.php` vornehmen.


## Tipps und Tricks
Weitere Tipps und Tricks sowie die FAQ finden sie im [Repository-Wiki auf GitHub](https://github.com/windele/alarmdisplay-ffw/wiki)

# Support
Gerne können Sie mich per Mail erreichen. Leider kann ich aber aus Zeitgründen und der Fülle der Anfragen keinen umfangreichen kostenfreien Support leisten. Stellen Sie Ihre Frage trotzdem, vielleicht gibt es eine schnelle und kostenlose Lösung.

# Referenzen
Ich würde mich freuen, wenn Sie mir ein kurzes Feedback geben, wo die Software überall im Einsatz ist. Dies können Sie mir per Mail mitteilen oder direkt in das [Repository-Wiki auf GitHub](https://github.com/windele/alarmdisplay-ffw/wiki/Einsatzorte-der-Software-und-Referenzen) schreiben.

# Haftungsausschluss und Datenschutz
Ich übernehme keine Haftung für die Funktion der Software vor Ort.
Da über diese Software sensible Personenbezogene Daten verarbeitet werden, ist der Datenschutz vor Ort insbesondere zu beachten. Vor allem sollte ein Augenmerk auf die *Datensparsamkeit* gelegt werden. Damit verbunden ist die strenge Entscheidung, wer welche Daten per E-Mail oder SMS weitergeleitet bekommt. Im Zweifel ist der Programmcode entsprechend anzupassen.
