# Allgemeines
## Nutzen der Software
Diese Software kann bei Feuerwehren dazu verwendet werden, die Informationen 
des Alarmfax auf einem Bildschirm darzustellen. Insbesondere werden die 
Informationen so aufbereitet, dass der Einsatzort auf einer Karte visualisiert 
wird. 


## Funktionsumfang
- **Digitale Uhr**: Im Ruhezustand zeigt das Alarmdisplay in großen Lettern 
  eine Uhr und das Datum an. 
- **Hinweistexte**: Das Alarmdisplay kann als digitales Schwarzes Brett genutzt 
  werden. Es können bis zu fünf Hinweistexte hinterlegt werden, die abwechselnd 
  den Bildschirm füllen. Jeder Hinweistext kann mit einem Start- und Ende-Datum 
  versehen werden, was eine automatische Anzeige ermöglicht.
- **Einsatzvisualisierung und Einsatzkräfteinfo**: Im Alarmfall zeigt das 
  Alarmdisplay die Einsatzdaten aus dem Alarmfax an. Zusätzlich wird der 
  Einsatzort auf einer Karte visualisiert. Ein Versand 
  der Daten per E-Mail an die Einsatzkräfte, der Versand einer SMS 
  (kostenpflichtig über den Dienstleister [RETTalarm](http://www.rettalarm.de) 
  (RETTalarm ist ein eingetragenes Warenzeichen der ide-tec KG) bzw. Divera, Prowl
  oder Telegram sowie der Ausdruck einer Karte sind ebenfalls Teil des Programms.
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

Zu Beginn wurde GoogleMaps zur Kartendarstellung genutzt. Da inzwischen die 
Leitstelle Koordinaten mitschickt und so auch Einsatzorte auf der Autobahn 
dargestellt werden können, wird seit Mai 2019 der BayernAtlas verwendet.

Ausführliches Versionsinfo siehe: [Versionsinfo](infos/Versionsinfo.md)


## Realisation & Systemvoraussetzungen
Die Software wurde in PHP geschrieben; die Visualisierung der Informationen 
erfolgt in Form einer Webseite. Bei der Webseitenanzeige muss im Browser 
JavaScript aktiviert sein, sonst funktionieren Uhr, Hinweistexte und 
Aktualisierung nicht.

Für einzelne Funktionen (z.B. Druck der Karte, etc.) wird auf weitere 
externe Software zurückgegriffen. Für die optimale Funktion wird ein 
Linux-System vorausgesetzt.

- **Datenquelle**: Als Datenquelle verwenden wir das Alarmfax, welches von der 
  Leitstelle bei Alarmierung an die Feuerwehr übermittelt wird. Auf unserem 
  Server nimmt dies die Faxsoftware *HylaFax* über ein *analoges USB-Faxmodem* 
  an und löst gleichzeitig die nächste Stufe, die Texterkennung aus. Als 
  Texterkennungssoftware haben wir im laufenden System auf *Cuneiform* gesetzt, 
  da dies untrainiert im Test die besten Erkennungsraten lieferte. Aktuell 
  würde ich aber *Tesseract* empfehlen. Aus dem TIF-Faxdokument wird so eine 
  TXT-Datei generiert. Mittlerweile ist es auch möglich, das Fax von einer
  FritzBox entgegen nehmen zu lassen, siehe [Installation FritzBox](infos/Installation_mit_FritzBox.md)
- **Verarbeitung**: Nach der Texterkennung wird das digitalisierte Fax an das 
  PHP-Script `ocr/readfile.php` übergeben. Dieses Skript ist dafür 
  verantwortlich, das Fax zu prüfen (Wurde uns wirklich von der Leitstelle ein 
  Fax geschickt oder ist es Werbung?) und die Inhalte in eine *mySQL-Datenbank* 
  zu schreiben. Da dieses Skript bei jedem Faxeingang aufgerufen wird, 
  übernimmt es auch den Versand der Daten per E-Mail, u.U.die Auslösung einer 
  RETTalarm-SMS, weiterer externer Alarmierungen und die Generierung der Ausdrucke. 
  Die Ausdrucke werden in Form eines Screenshots der Webseite realisiert, dies 
  übernimmt die Software [wkhtmlmtopdf](http://code.google.com/p/wkhtmltopdf/) bzw. deren Teil 
  wkhtmltoimage. Um die Daten in Druckform zu bringen, werden die Screenshots 
  mit *convert* aus dem *ImageMagick*-Paket verarbeitet.
- **Ausgabe**: Die Skripte des Alarmdisplays überprüfen regelmässig durch eine 
  *JavaScript*-Funktion die Datenbank. Wenn ein aktueller Einsatz festgestellt 
  wird, so wechselt die Anzeige von der Uhr bzw. dem digitalen schwarzen Brett 
  zur Einsatzanzeige. Alle Daten des Faxes werden dargestellt und der 
  Einsatzort soweit möglich auf einer Karte durch den BayernAtlas dargestellt. Um 
  diese Visualisierung so darstellen zu können, wird ein installierter 
  Webserver (z.B. *Apache* oder *lighttpd*) sowie eine Internetverbindung (für 
  BayernAtlas) benötigt. Die Darstellung selbst erfolgt mit einem Browser (bei 
  uns *Mozilla Firefox*), der im Kiosk-Modus (Vollbild -> F11) läuft.
- **Hardware**: Die Software läuft erfolgreich im 24/7-Betrieb auf einem 
  lüfterlosen Industrie-PC, der direkt an einen Bildschirm gekoppelt ist. Als 
  Drucker wird ein von Linux unterstützter Netzwerkdrucker eingesetzt. Es 
  sollte aber jeder einigermaßen leistungsfähige PC mit Linux funktionieren. 
  (*RaspberryPi* funktioniert ab Version 2 mit zufriedenstellender Performance.) 

  Die Erfahrung zeigt, dass ein großer Bildschirm die Informationsaufnahme bei 
  den Einatzkräften erhöht.

  Um den Bildschirm nicht unnötig zu belasten und Strom zu sparen, wurde bei einer
  Feuerwehr ein Zwischenstecker mit Bewegungsmelder (ca. 15 €) erfolgreich getestet.
  Der Stecker schaltet den Strom für den Monitor dann ein, wenn sich Personen
  vor dem Display aufhalten. So wird dauch die Lebensdauer erhöht.


## Grenzen
- Die Software kann nur dann eine genaue Karte darstellen, wenn die Koordinaten 
  einwandfrei von der Leitstelle übermittelt wurde und BayernAtlas diese 
  auflösen kann.
- FMS-Status können nur dann dargestellt werden, wenn ein externes System die 
  Status an die Datenbank übermittelt. In einer standalone-Standardinstallation 
  geht dies nicht - die FMS-Funktion wurde auch wegen der Digitalfunk-Einführung
  nicht dokumentiert.


## Danke
Für das Projekt "Alarmdisplay" wurde auf weitere Software zurückgegriffen, bei 
deren Autoren ich mich herzlich bedanken möchte.
Verwendet wurden:
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) zum Versenden der E-Mails
- [PHPMyEdit](http://www.phpmyedit.org/) im Administrationsmenü
- [JQuery](http://www.jquery.com) im Administrationsmenü
- [BluePrintCSS](http://www.blueprintcss.org) für die Ausrichtung 
- [GoogleMaps](http://maps.google.de) für das Routing und die Kartenanzeige
  --> GoogleMaps wurde in Version 2 durch den BayernAtlas ersetzt
- [BayernAtlas](https://geodaten.bayern.de) für die Kartenanzeige ab V2.

Bedanken möchte ich mich auch bei allen, die mit Code und Anregungen zur Verbesserung
der Software beigetragen haben.


# Installation
Es ist bei der Installation zu unterscheiden, wie das Fax angenommen wird. 
Bei der zunehmenden Zahl der All-IP-Anschlüsse kann das Fax statt von einem eigenen
Faxserver auch von einer FritzBox entgegen genommen werden. Dementsprechend unterschiedlich
sind die Vorgehensweisen. 

Die Installation auf einem Rechner mit eingenem Faxserver ist unter 
[Installation mit USB-Modem](infos/Installation_mit_USB-Modem.md) beschrieben.

Möchten Sie dagegen das Fax von einer FritzBox entgegen nehmen lassen, ist der Abschnitt
[Installation FritzBox](infos/Installation_mit_FritzBox.md) zu beachten.

Erprobt ist auch das Zusammenspiel von FritzBox und RaspberryPi, siehe hierzu die Datei [Installation Raspberry Pi](infos/Installation_auf_Raspberry_Pi.md).

Es ist geplant, zukünftig ein lauffähiges RaspberryPi-Image zur Verfügung zu stellen.


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
nicht dokumentiert. Ob dies noch folgt, ist unklar.

# Geplante Änderungen und Erweiterungen
Ich freue mich auf Ideen und Verbesserungen.

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
Entscheidung, wer welche Daten per E-Mail, SMS oder externen Systemen weitergeleitet 
bekommt. Im Zweifel ist der Programmcode entsprechend anzupassen.
