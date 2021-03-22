<?php

/*
 ALARMDISPLAY FEUERWEHR PIFLAS
 Copyright 2012-2019 Stefan Windele

 Version 2.0.0
 
 Dieses Script liest die von der Texterkennung übergebene Textdatei ein,
 findet die relevanten Wörter und speichert diese in die Datenbank.

 Dieses Programm ist Freie Software: Sie können es unter den Bedingungen
 der GNU General Public License, wie von der Free Software Foundation,
 Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren
 veröffentlichten Version, weiterverbreiten und/oder modifizieren.

 Dieses Programm wird in der Hoffnung, dass es nützlich sein wird, aber
 OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
 Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
 Siehe die GNU General Public License für weitere Details.

 Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.

 */

// Einstellungen für Zeichensatz
setlocale(LC_ALL, 'de_DE');

// Konfigurationsdatei einbinden
require "../config.inc.php";

// Einstellungen für den Mailversand

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Reset der Variablen
$alarmzeit = "";
$zeile_zerlegt = array();
$mitteiler = "";
$split_strasse = array();
$strasse = "";
$hausnr = "";
$ort = "";
$objekt = "";
$station = "";
$einsatzgrund = "";
$prio = 0;
$rw = "";
$hw = "";
$bemerkung = "";
$dispoliste = array();

// Wir lesen die Datei ein und ermitteln die Länge; der Name der Datei wird als erstes Argument an das PHP-Skript beim Aufruf übergeben.
if (isset($_GET['debug'])) {
    $alarmfax = file($_GET['file'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
} else {
    if (!($alarmfax = file_get_contents($_SERVER['argv'][1]))) {
        die ;
    };
}


//bekannte Probleme der Texterkennung umgehen
	
//dazu den Musterabsatz kopieren und anpassen.
	/*
	$patterns=array();
	$replacement='';
	$patterns[0] = '/falsches Wort/i';
	$patterns[1] = '/falsches Wort/i';
	$patterns[2] = '/falsches Wort/i';
	$replacement = 'richtiges Wort';
        $alarmfax = preg_replace($patterns, $replacement, $alarmfax);
	*/

//ungeliebte Abkürzungen definieren die mit Leerzeichen ersetzt werden
	   $patterns=array();
	   $replacement='';
	   $patterns[0] = '/FL\\s/i';
	   $patterns[1] = '/LA\\W[L,S]\\s/u';
	   $patterns[2] = '/\s\d\.\d(\.*|\s*)\d*\s/i';
	   $patterns[3] = "/2\.1\.1/";
           $patterns[4] = "/2\.1\.2/";
	   $patterns[5] = "/2\.1/";
	   $replacement = ' ';
           $alarmfax = preg_replace($patterns, $replacement, $alarmfax);	

	   $patterns=array();
	   $patterns[0] = '/uttrag/i';
	   $replacement = 'uftrag';
           $alarmfax = preg_replace($patterns, $replacement, $alarmfax);	
           
           
    // Spezialfall
        $patterns = array();
        $patterns[0] = '/Sch1aqw/';
        $patterns[1] = '/Sch1agw/';
        $patterns[2] = '/Schlaqw/';
        $replacement = "Schlagw";
        $alarmfax = preg_replace($patterns, $replacement, $alarmfax);

        // Koordinaten l=1
        $patterns = array();
        $patterns[0] = '/(?<=[0-9,.])ll(?=[0-9,.])*/';
        $replacement = '11';
        $alarmfax = preg_replace($patterns, $replacement, $alarmfax);


        // Koordinaten l=1
        $patterns = array();
        $patterns[0] = '/(?<=[0-9,.])l(?=[0-9,.])*/';
        $replacement = '1';
        $alarmfax = preg_replace($patterns, $replacement, $alarmfax);



	// Ortsname sollte richtig erkannt werden
	$patterns=array();
	$patterns[0] = '/P(\s)?i(\s)?f(\s)?l(\s)?a(\s)?s/';
	$patterns[1] = '/PifIas/';
	$patterns[2] = '/Pfilas/';
	$patterns[3] = '/PfiIas/';
	$replacement = 'Piflas';
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);	

	// Leerzeichen bei den 2.1.x Kennungen
	$patterns = array();
	$patterns[0] = '/(?<=[0-9])[,.] (?=[0-9])/';
	$replacement = '.';
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);
	
	// Brandmelderkennungen
	$patterns = array();
	$patterns[0] = '/[0-9]{8}\-01:/';
	$replacement = "";
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);

	// Texterkennung .z = .:
	$patterns = array();
	$patterns[0] = '/\.z/';
	$replacement = ".:";
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);
	
	// Texterkennung 1 = l
	$patterns = array();
	$patterns[0] = '/(?<=[a-zA-Z])1(?=[a-z])/';
	$replacement = "l";
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);
	
	// Texterkennung 11 = ll
	$patterns = array();
	$patterns[0] = '/(?<=[a-zA-Z])11(?=[a-z])/';
	$replacement = "ll";
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);
	
	// Texterkennung /1 = /l
	$patterns = array();
	$patterns[0] = ':/l:';
	$replacement = "/1";
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);
	$patterns = array();
	$patterns[0] = ':l/:';
	$replacement = "1/";
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);
	
	// Texterkennung ecall
	$patterns = array();
	$patterns[0] = '/e0a11/';
	$replacement = "eCall";
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);
	
	// Texterkennung ßaum - scharfes s am Wortanfang
	$patterns = array();
	$patterns[0] = '/(?<=\s)(ß)(?=[a-z]{2,})/';
	$replacement = "B";
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);
	
	// Koordinaten 7=? 
	$patterns = array();
	$patterns[0] = '/(?<=[\d\.\?])\?(?=[\d\.])/';
	$patterns[1] = '/(?<=\d.\d)\?/';
	$patterns[2] = '/(?<=[\d\.\?])T(?=[\d\.])/';
	$patterns[3] = '/(?<=\d.\d)T/';
	$replacement = "7";
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);
	$alarmfax = preg_replace($patterns, $replacement, $alarmfax);



// Erkennung des Alarmfax
if (preg_match("/Absender[^I]*ILS.Landshut/i", $alarmfax)) {
    // Mitteiler
    if (preg_match("/Name\\W+(.*)/i", $alarmfax, $treffer)) {
        $mitteiler = trim($treffer[1]);
    }
    // Straße und Hausnummer
    if (preg_match("/Stra\\S+?e(?:\\s[\\s:._]*)(.*?)\\ Haus\\S+[.:\\-\\t\\ ]+(.*)/i", $alarmfax, $treffer)) {
        $strasse = trim($treffer[1]);
        $hausnr = trim($treffer[2]);
    }
    // Ort
    if (preg_match("/Ort[\\W:]*([0-9]{5})\\W*([\\wüöäß-]*)/i", $alarmfax, $treffer)) {
        $ort = trim($treffer[1]) . " " . trim($treffer[2]);
    }

    // Objekt
    if (preg_match("/Objekt\\W*(.*)\n*EPN/i", $alarmfax, $treffer)) 
	{
        $objekt = trim($treffer[1]);
	}

    // Einsatzgrund
    if (preg_match("/Schlagw\\W*(.*)/i", $alarmfax, $treffer)) {
        $einsatzgrund = trim($treffer[1]);
        if (preg_match("/Stichwort.B[^\\w\\n]*(.*)/i", $alarmfax, $treffer)) {
            $einsatzgrund .= " " . trim($treffer[1]);
        }
	// Hashtags entfernen
	$einsatzgrund = str_replace ("#", " ", $einsatzgrund);

	// Kennung hinten anstellen
	if (preg_match("/[A,B,T]\d\d\d\d(.*)/i", $einsatzgrund, $treffer)) {

	switch (substr($treffer[0],0,1)) {
		case "A" :
			$einsatzgrund = "ABC " . trim($treffer[1]) . " (" . substr($treffer[0],0,5) . ")";
			break;
		case "B" :
			$einsatzgrund = "Brand " . trim($treffer[1]) . " (" . substr($treffer[0],0,5) . ")";
			break;
		case "T" :
			$einsatzgrund = "THL " . trim($treffer[1]) . " (" . substr($treffer[0],0,5) . ")";
			break;
	}
           
        }
	
    }
    // Bemerkung
    if (preg_match("/\\W*BEMERKUNG\\W*\\n(?s)(.*)\\n.*DISPO/im", $alarmfax, $treffer)) {
        $bemerkung = trim($treffer[1]);
    }
    // Koordinate Rechtswert
    if (preg_match("/Re[c,o]htswert\\W+(.*)/", $alarmfax, $treffer)) {
        $rw = trim($treffer[1]);
    }

    // Koordinate Hochwert
    if (preg_match("/Ho[c,o]hwert\\W+(.*)/", $alarmfax, $treffer)) {
        $hw = trim($treffer[1]);

    }
	
    // Priorität
    if (preg_match("/rio.\\W+(.*)/i", $alarmfax, $treffer)) {
        $prio= trim($treffer[1]);
    }
    
    // Einsatzmittel
   
    if (preg_match_all("/Name\s\S\s(.*)/im", $alarmfax, $treffer)) {

        for ($i = 0; $i < count($treffer[0]); $i++) 
	{
	   $dispoliste[] =  htmlentities(trim($treffer[1][$i]));
        }
    }
	

}


// DEBUGGING FÜR ENTWICKLUNG
echo "Mitteiler: " . $mitteiler . "\n";
echo "Strasse: " . $strasse . "\n";
echo "Haus-Nr: " . $hausnr . "\n";
echo "Ort: " . $ort . "\n";
echo "Objekt: " . $objekt . "\n";
echo "Einsatzgrund: " . $einsatzgrund . "\n";
echo "Prio: " . $prio . "\n";
echo "Rechtswert: " . $rw . "\n";
echo "Hochwert: " . $hw . "\n";
echo "Bemerkung: " . $bemerkung . "\n";
for ($i = 0; $i < count($dispoliste); $i++) {
    echo "Dispo " . $i . ": " . $dispoliste[$i] . "\n";
}

echo "Alarm: " . trim($einsatzgrund); 



// Verbindung zur Datenbank herstellen und an Handle $db übergeben
$db = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME) or die('Verbindung zur Datenbank fehlgeschlagen.');
$db -> set_charset("utf8");

// SQL-Abfrage zusammensetzen
$sqlinsert = "INSERT INTO tbl_einsaetze (`mitteiler`, `strasse`, `hausnr`, `ort`, `objekt`, `station`, `schlagw`, `prio`, `bemerkung`, `dispo`, `rw`, `hw`) VALUES ('" . $mitteiler . "', '" . $strasse . "', '" . $hausnr . "', '" . $ort . "', '" . $objekt . "', '" . $station . "', '" . $einsatzgrund . "', '" . $prio . "', '" . $bemerkung . "', '" . json_encode($dispoliste)  . "', '" . $rw . "', '" . $hw . "')";

echo "SQL: " . $sqlinsert . "\n";

// Wir prüfen, ob auch ein Fax von der ILS erkannt wurde. Nur dann wird die Aktion ausgelöst.
if ($einsatzgrund != "") {

    $db -> query($sqlinsert);

    // Weils so schön ist, sollten wir den Bildschirm einschalten
    // passthru(__DIR__ . "/../administrator/bild-an.sh");

    // Setzen der Umgebung
    setlocale(LC_ALL, 'de_DE.utf8');

    // Abfrage der Konfigurationsparameter

    $result = $db -> query("SELECT parameter, wert FROM tbl_adm_params WHERE parameter IS NOT NULL");
    $parameter = array();
    while ($row = $result -> fetch_row()) {
        $parameter[$row[0]] = $row[1];
    }
    unset($row);
    $result -> close();

    // SMS-Funktion
    if ($parameter["SMSENABLED"] == "true") {

        // SMS-Inhalt bauen
        $smstext = "ALARM (" . date("H:i") . "): " . $einsatzgrund . "- " . $objekt . " " . $strasse . " " . $hausnr . " " . $ort . "/ " . $bemerkung;
        $smstext= substr($smstext, 0, 159);
        
        //URL zusammenbauen
        $url = 'https://www.groupalarm.de/webin.php?log_user='; 
        $url .= urlencode($parameter["SMSUSER"]) . '&log_epass=';
        $url .= urlencode($parameter["SMSPASS"]) . '&xlistcode=';
        $url .= urlencode($parameter["SMSALARMGRUPPEN"]);

        if ($parameter["SMSFLASH"] == "true") {
            //SMS als Flash-SMS senden
            $url .= '&flash=1';
        }

        $url .= '&free=' . urlencode(utf8_decode($smstext));

        //Verbindung aufbauen und an Handle übergeben
        $ch = curl_init();

        //Optionen setzen
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, '3');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //Abfrage ausführen
        $result = curl_exec($ch);
        echo '---SMS-RUECKMELDUNG---' . "\n" . $result . "\n\n";

        //Verbindung beenden
        curl_close($ch);

    }

    // E-Mail-Funktion
    if ($parameter["MAILENABLED"] == "true") {

        // lasst uns eine Mail schicken

	// Klassen laden

	require 'Exception.php';
	require 'PHPMailer.php';
	require 'SMTP.php';

	
        $mail = new PHPMailer();
        $mail -> IsSMTP();
        //$mail -> CharSet = 'utf-8';  
        $mail -> Host = $parameter["SMTPSERVER"];
        $mail -> SMTPAuth = true;
        $mail -> Port = $parameter["SMTPPORT"];
        $mail -> SMTPSecure = $parameter["SMTPSECURE"];
        $mail -> Username = $parameter["SMTPUSER"];
        $mail -> Password = $parameter["SMTPPASS"];
        $mail -> From = $parameter["SMTPSENDERMAIL"];
        $mail -> FromName = $parameter["SMTPSENDER"];
        $mail -> AddAddress($parameter["SMTPSENDERMAIL"]);
        $mail -> Subject = "Alarm: " . utf8_decode(trim($einsatzgrund)); 
        $mail -> Priority = 1;
        $mail -> WordWrap = 70;

        // Adressen ermitteln
        $result = $db -> query("SELECT email FROM tbl_alarm_user WHERE email IS NOT NULL");

        //Fehlermeldung abfangen
        if ($result -> num_rows > 0) {

            while ($row = $result -> fetch_row()) {
                $mail -> AddBCC($row[0]);
            }
            unset($row);
            $result -> close();

            // Text der Mail zusammenbauen

            $text = "Alarm für die " . $parameter["NAMEFEUERWEHR"] . "\n";
            $text .= "---------------------------------------------------\n";
            $text .= "EINSATZDATEN:\n";
            $text .= "---------------------------------------------------\n";
            $text .= "Stichwort: " . $einsatzgrund . "\n";
            $text .= "Einsatzort: " . $strasse . " " . $hausnr . ", " . $ort . "\n";
            $text .= "Einsatzobjekt: " . $objekt . "\n";
            $text .= "---------------------------------------------------\n";
            $text .= "BEMERKUNGEN:\n";
            $text .= "---------------------------------------------------\n";
            $text .= "Bemerkung ILS: " . $bemerkung . "\n";
            $text .= "Bemerkung Kdt: ";
            ($parameter["EINSATZHINWEIS"] != "") ? $text .= $parameter["EINSATZHINWEIS"] . "\n" : $text .= "-keine-\n";
            $text .= "---------------------------------------------------\n";
            $text .= "DISPONIERTE FAHRZEUGE:\n";
            $text .= "---------------------------------------------------\n";

            foreach($dispoliste as $d) {
                    $text .= html_entity_decode($d) . "\n";
            }
	    $text .= "---------------------------------------------------\n";
            $text .= "KOORDINATEN:\n";
            $text .= "---------------------------------------------------\n";	
            $text .= "Rechtswert: " . $rw . "\n";
            $text .= "Hochwert: " . $hw . "\n";
            $text .= "---------------------------------------------------\n";
            $text .= "Bayernatlas: https://geoportal.bayern.de/bayernatlas/?N=" . $hw . "&E=" . $rw ." &zoom=" . $parameter['MAPZOOMOBEN'] ."&lang=de&topic=ba&bgLayer=". $parameter['LAYERKARTEOBEN'] . "&crosshair=marker&catalogNodes=122\n";	
            $text .= "---------------------------------------------------\n";
            $text .= "ERGÄNZUNGEN:\n";
            $text .= "---------------------------------------------------\n";
            $text .= "Zeitstempel Faxeingang: " . strftime("%A, %d.%m.%Y // %H:%M") . "\n\n";

            // Link auf Google Maps und Navigationssoftware, falls wir nicht auf der Autobahn sind.
            if (substr($strasse, 0, 3) != "A92") {
                $text .= "Karte: \n";
                $text .= "http://maps.google.de/maps?q=" . urlencode(trim($strasse) . " " . trim($hausnr) . ", " . trim($ort)) . "\n\n";
                $text .= "Google-Maps-Navigation: \n";
                $text .= "http://maps.google.de/maps?daddr=" . urlencode(trim($strasse) . " " . trim($hausnr) . ", " . trim($ort)) . "\n\n";
                $text .= "Handy-Navigation für Telekom-Smartphones: \n";
                $text .= "navigon://address/Einsatzstelle/DEU/" . str_replace("%26%26", "/", rawurlencode(str_replace(" ", "&&", trim($ort)))) . "/" . rawurlencode(trim($strasse)) . "/" . rawurlencode(trim($hausnr)) . "\n";
            }
            $text .= "---------------------------------------------------\n";
            $text .= "Automatisch generiert durch Alarmdisplay FF Piflas \n\n";
            $text .= "-ENDE-\n";

            $mail -> Body = utf8_decode($text);
            $mail -> Send();

        }

    }


   // Externer Alarm: Divera
    if ($parameter["DIVERAENABLED"] == "true") {

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "www.divera247.com/api/alarm?accesskey=" . $parameter['DIVERAKEY']);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "type=$einsatzgrund&address=$strasse $hausnr, $ort&text=$bemerkung");

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec ($ch);

	curl_close ($ch);

   
    }



   // Externer Alarm: Prowl
    if ($parameter["PROWLENABLED"] == "true") {
		$api = $parameter["PROWLKEY"];
		$title = "Alarm für die " . $parameter["NAMEFEUERWEHR"];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,"http://prowl.weks.net/publicapi/add");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,"apikey=$api&application=$title&priority=0&event=$einsatzgrund&description=Bemerkung: $bemerkung\n-------------------------------------------\nEinsatzort: $strasse $hausnr, $ort\n-------------------------------------------\n\nFaxeingang: " . strftime("%A, %d.%m.%Y // %H:%M") . "\nAlarmdisplay FF Piflas");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec ($ch);

		curl_close ($ch);

		print_r($server_output);
			  
   
    }


   // Externer Alarm: Telegram
    if ($parameter["TELEGRAMENABLED"] == "true") {
		// Text für TELEGRAM
		$text = "Alarm für die " . $parameter["NAMEFEUERWEHR"] . "\n";
		$text .= "----------------------------------\n";
		$text .= "EINSATZDATEN:\n";
		$text .= "----------------------------------\n";
		$text .= "Stichwort: \n" . $einsatzgrund . "\n";
		$text .= "Einsatzort: \n" . $strasse . " " . $hausnr . ", " . $ort . "\n";
		$text .= "Einsatzobjekt: \n" . $objekt . "\n";
		$text .= "----------------------------------\n";
		$text .= "BEMERKUNGEN:\n";
		$text .= "----------------------------------\n";
		$text .= "Bemerkung ILS: \n" . $bemerkung . "\n";
		$text .= "Bemerkung Kdt: \n";
		($parameter["EINSATZHINWEIS"] != "") ? $text .= $parameter["EINSATZHINWEIS"] . "\n" : $text .= "-keine-\n";
		$text .= "----------------------------------\n";
		$text .= "DISPONIERTE FAHRZEUGE:\n";
		$text .= "----------------------------------\n";

		foreach($dispoliste as $d) {
                    $text .= html_entity_decode($d) . "\n";
                }  
		$text .= "----------------------------------\n";
		$text .= "ERGÄNZUNGEN:\n";
		$text .= "----------------------------------\n";
		$text .= "Zeitstempel Faxeingang: " . strftime("%A, %d.%m.%Y // %H:%M") . "\n\n";

		// Link auf Google Maps und Navigationssoftware, falls wir nicht auf der Autobahn sind.
		if (substr($strasse, 0, 3) != "A92") {
		$text .= "Karte: \n";
		$text .= "http://maps.google.de/maps?q=" . urlencode(trim($strasse) . " " . trim($hausnr) . ", " . trim($ort)) . "\n\n";
		$text .= "Google-Maps-Navigation: \n";
		$text .= "http://maps.google.de/maps?daddr=" . urlencode(trim($strasse) . " " . trim($hausnr) . ", " . trim($ort)) . "\n\n";
		}
		$text .= "----------------------------------\n";
		$text .= "Automatisch generiert durch Alarmdisplay FF Piflas \n\n";
		$text .= "-ENDE-\n";

		// Übergabe an TELEGRAM

		$botToken=$parameter["TELEGRAMBOTTOKEN"];
		$website="https://api.telegram.org/bot".$botToken;
		$chatId=$parameter["TELEGRAMCHATID"];  //Receiver Chat Id
		$params=[
		'chat_id'=>$chatId,
		'text'=>$text,
		];
		$ch = curl_init($website . '/sendMessage');
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);
   
    }


    // Druckfunktion Fax
    if ($parameter["DRUCKENFAX"] == "true") {
        // Fax drucken
        $exemplare = intval($parameter["DRUCKENFAXWIEOFT"]) - 1;
        passthru("convert -density 300 -duplicate " . $exemplare . " " . $_SERVER['argv'][2] . " /tmp/alarmfaxdruck.pdf");
        passthru("lp -o media=A4 -o fit-to-page /tmp/alarmfaxdruck.pdf");
    }


    // Druckfunktion Screenshot
    if ($parameter["DRUCKENSCREEN"] == "true") {

 	     putenv("DISPLAY=:0");
        passthru("wkhtmltoimage --height 1280 --width 1920 --javascript-delay 14000 --quality 100 http://localhost/alarmdisplay-ffw /tmp/screenshot.jpg");
       

        $exemplare = intval($parameter["DRUCKENSCREENWIEOFT"]) - 1;

 
            if ($parameter["DRUCKENANFAHRT"] == "true") {
			$anfahrtskarte = "wkhtmltoimage --height 1280 --width 1920 --javascript-delay 14000 --quality 100 http://localhost/alarmdisplay-ffw/modules/mod_einsatz_inc_route_zoom_panel.php /tmp/anfahrt.jpg";

			// Anfahrtskarte erstellen
			putenv("DISPLAY=:0");
			putenv("LANG=de_DE.UTF-8");
			setlocale(LC_ALL, 'de_DE.UTF-8');
			passthru($anfahrtskarte);

			$befehl = "convert -border 40 -bordercolor white -page A4 -density 300x300 -resize 2480x3506 -append -duplicate " . $exemplare . " /tmp/screenshot.jpg /tmp/anfahrt.jpg /tmp/screenshot.pdf";
		echo $befehl;

		} else {
			$befehl = "convert -border 40 -bordercolor white -page A4 -density 300x300 -resize 2480x3506 -duplicate " . $exemplare . " /tmp/screenshot.jpg /tmp/screenshot.pdf";
		}
 

        // PDF erstellen
        passthru($befehl);

        // PDF drucken
        passthru("lp -o media=A4 -o fit-to-page /tmp/screenshot.pdf");

	// Rechte bereinigen
	passthru("chmod 766 /tmp/*.pdf; chmod 766 /tmp/*.jpg");
    }

}

// Verbindung zur Datenbank schließen
$db -> close();
?>


