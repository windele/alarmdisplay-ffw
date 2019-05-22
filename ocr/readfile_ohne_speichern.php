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
    if (preg_match("/Rechtswert\\W+(.*)/", $alarmfax, $treffer)) {
        $rw = trim($treffer[1]);
    }

    // Koordinate Hochwert
    if (preg_match("/Hochwert\\W+(.*)/", $alarmfax, $treffer)) {
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


/*

    // Druckfunktion Screenshot


 	putenv("DISPLAY=:0");
        passthru("wkhtmltoimage --height 1280 --width 1920 --javascript-delay 11000 --quality 100 http://localhost/alarmdisplay-ffw /tmp/screenshot.jpg");
       

        $exemplare = 0;

 
           $anfahrtskarte = "wkhtmltoimage --height 1280 --width 1920 --javascript-delay 11000 --quality 100 'http://localhost/alarmdisplay-ffw/modules/mod_einsatz_inc_route_zoom_panel.php' /tmp/anfahrt.jpg";

			// Anfahrtskarte erstellen
			putenv("DISPLAY=:0");
			putenv("LANG=de_DE.UTF-8");
			setlocale(LC_ALL, 'de_DE.UTF-8');
			passthru($anfahrtskarte);

			$befehl = "convert -border 40 -bordercolor white -page A4 -density 300x300 -resize 2480x3506 -append -duplicate " . $exemplare . " /tmp/screenshot.jpg /tmp/anfahrt.jpg /tmp/screenshot.pdf";
		
 

        // PDF erstellen
        passthru($befehl);

        // PDF drucken
        passthru("lp -o media=A4 -o fit-to-page /tmp/screenshot.pdf");
*/

?>


