<?php

/*
 ALARMDISPLAY FEUERWEHR PIFLAS
 Copyright 2012-2014 Stefan Windele

 Version 1.0.1
 
 Dieses Script liest die von der Texterkennung übergebene Textdatei ein,
 zerlegt die Struktur und speichert diese in die Datenbank.
 Als Texterkennung wurde die Software Cuneiform verwendet.
 Wenn eine andere Texterkennung eingesetzt wird, so ist das Programm
 entsprechend anzupassen.

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
$beginndispoliste = 0;
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
$faxlaenge = count($alarmfax);

//Hilfsfunktion zum Zerlegen der Zeilen
function zeile_zerlegen($n) {
    global $zeile_zerlegt, $alarmfax;
    $zeile_zerlegt = explode(":", $alarmfax[$n]);
}

// Erkennung des Alarmfax
if (preg_match("/Absender[^I]*ILS.Donau.Iller/i", $alarmfax)) {
    // Mitteiler
    if (preg_match("/Name\\W+(.*)\\W+Rufnummer/i", $alarmfax, $treffer)) {
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
    // Einsatzgrund
    if (preg_match("/Schlagw\\W*(.*)/i", $alarmfax, $treffer)) {
        $einsatzgrund = trim($treffer[1]);
        if (preg_match("/Stichwort.B[^\\w\\n]*(.*)/i", $alarmfax, $treffer)) {
            $einsatzgrund .= " " . trim($treffer[1]);
        }
    }
    // Bemerkung
    if (preg_match("/\\W*BEMERKUNG\\W*\\n(?s)(.*)/im", $alarmfax, $treffer)) {
        $bemerkung = trim($treffer[1]);
    }
    // Einsatzmittel
    if (preg_match_all("/Name\\W+[0-9.]+\\W+\\w+\\W+\\w+\\W+([^0-9\\n]+)([0-9\\/]*)/i", $alarmfax, $treffer)) {
        for ($i = 0; $i < count($treffer[0]); $i++) {
            $dispoliste[] = trim($treffer[1][$i]) . " " . trim($treffer[2][$i]);
        }
    }
}


// DEBUGGING FÜR ENTWICKLUNG
echo "Mitteiler: " . $mitteiler . "\n";
echo "Strasse: " . $strasse . "\n";
echo "Haus-Nr: " . $hausnr . "\n";
echo "Ort: " . $ort . "\n";
echo "Einsatzgrund: " . $einsatzgrund . "\n";
echo "Prio: " . $prio . "\n";
echo "Bemerkung: " . $bemerkung . "\n";
// echo "Beginn der Dispoliste in Zeile: " . $beginndispoliste . "\n";
for ($i = 0; $i < count($dispoliste); $i++) {
    echo "Dispo " . $i . ": " . $dispoliste[$i] . "\n";
}

/* Variablen für SQL

 $mitteiler
 $strasse
 $hausnr
 $ort
 $einsatzgrund
 $prio
 $bemerkung
 TIMESTAMP
 $dispoliste

 */

// Verbindung zur Datenbank herstellen und an Handle $db übergeben
$db = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME) or die('Verbindung zur Datenbank fehlgeschlagen.');
$db -> set_charset("utf8");

// SQL-Abfrage zusammensetzen
$sqlinsert = "INSERT INTO tbl_einsaetze (`mitteiler`, `strasse`, `hausnr`, `ort`, `objekt`, `station`, `schlagw`, `prio`, `bemerkung`, `dispo`) VALUES ('" . $mitteiler . "', '" . $strasse . "', '" . $hausnr . "', '" . $ort . "', '" . $objekt . "', '" . $station . "', '" . $einsatzgrund . "', '" . $prio . "', '" . $bemerkung . "', '" . json_encode($dispoliste) . "')";

// Wir prüfen, ob auch ein Fax von der ILS erkannt wurde. Nur dann wird die Aktion ausgelöst.
if ($einsatzgrund != "") {

    $db -> query($sqlinsert);

    // Weils so schön ist, sollten wir den Bildschirm einschalten; wir nehmen das Alarmskript das den Browser aufmacht.
    // passthru(__DIR__ . "/../administrator/bild-an-alarm.sh");

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
        $url = 'http://www.RA-Server.de/webin.php?log_user=';
        $url .= urlencode($parameter["SMSUSER"]) . '&log_pass=';
        $url .= urlencode($parameter["SMSPASS"]) . '&listcode=';
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

        //Abfrage ausführen
        $result = curl_exec($ch);
        echo '---SMS-RUECKMELDUNG---' . "\n" . $result . "\n\n";

        //Verbindung beenden
        curl_close($ch);

    }

    // Prüfen, ob ein Screenshot gebraucht wird - wenn ja, erstellen wir einen
    if ($parameter["DRUCKENSCREEN"] == "true" || $parameter["MAILSCREENSHOT"] == "true") {
        putenv("DISPLAY=:0");
        passthru("wkhtmltoimage --height 1280 --width 1920 --javascript-delay 12500 --quality 100 http://localhost/alarmdisplay /tmp/alarm/screenshot.jpg");
        passthru("convert /tmp/alarm/screenshot.jpg -resize 50% /tmp/alarm/screenmail.jpg");
    }

    // E-Mail-Funktion
    if ($parameter["MAILENABLED"] == "true") {

        // lasst uns eine Mail schicken
        require ('class.phpmailer.php');
        $mail = new PHPMailer();
        $mail -> IsSMTP();
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

        // Soll ein Screenshot beigefügt werden?
        if ($parameter["MAILSCREENSHOT"] == "true") {
            // Screenshot anhängen
            $mail -> AddAttachment('/tmp/alarm/screenmail.jpg', 'alarmdisplay-screenshot.jpg');
        }

        // Soll der E-Mail das Alarmfax als Bildanhang beigefügt werden?
        if ($parameter["MAILFAX"] == "true") {
            // Alarmfax umwandeln und anhängen
            passthru("convert " . $_SERVER['argv'][2] . " /tmp/alarm/alarmfaxmail.jpg");
            $mail -> AddAttachment("/tmp/alarm/alarmfaxmail.jpg", 'alarmfax.jpg');
        }
        
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
                    $text .= $d . "\n";
            }
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

    // Druckfunktion Fax
    if ($parameter["DRUCKENFAX"] == "true") {
        // Fax drucken
        $exemplare = intval($parameter["DRUCKENFAXWIEOFT"]) - 1;
        passthru("convert -duplicate " . $exemplare . " " . $_SERVER['argv'][2] . " /tmp/alarm/alarmfaxdruck.pdf");
        passthru("lp -o media=A4 -o fit-to-page /tmp/alarm/alarmfaxdruck.pdf");
    }

    // Druckfunktion Screenshot
    if ($parameter["DRUCKENSCREEN"] == "true") {
        $exemplare = intval($parameter["DRUCKENSCREENWIEOFT"]) - 1;

        if ($parameter["DRUCKENANFAHRT"] == "true") {
            $anfahrtskarte = "wkhtmltoimage --height 1280 --width 1920 --javascript-delay 12500 --quality 100 'http://localhost/alarmdisplay/modules/mod_einsatz_inc_route_zoom_panel.php' /tmp/alarm/anfahrt.jpg";

            // Anfahrtskarte erstellen
            putenv("DISPLAY=:0");
            putenv("LANG=de_DE.UTF-8");
            setlocale(LC_ALL, 'de_DE.UTF-8');
            passthru($anfahrtskarte);

            $befehl = "convert -border 40 -bordercolor white -append -density 300x300 -resize 2480x3506 -duplicate " . $exemplare . " /tmp/alarm/screenshot.jpg /tmp/alarm/anfahrt.jpg -gravity center /tmp/alarm/screenshot.pdf";

        } else {
            $befehl = "convert -border 40 -bordercolor white -density 300x300 -resize 2480x3506 -duplicate " . $exemplare . " /tmp/alarm/screenshot.jpg -gravity center /tmp/alarm/screenshot.pdf";
        }

        // PDF erstellen
        passthru($befehl);

        // PDF drucken
        passthru("lp -o media=A4 -o fit-to-page /tmp/alarm/screenshot.pdf");
    }

}

// Verbindung zur Datenbank schließen
$db -> close();
?>
