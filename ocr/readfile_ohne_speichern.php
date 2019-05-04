<?php

/*
 ALARMDISPLAY FEUERWEHR PIFLAS
 Copyright 2012-2019 Stefan Windele

 Version 2.0.0
 
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
$rw = 0;
$hw = 0;
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


//ungeliebte Abkürzungen definieren zwecks Ersetzen
	   $loeschungen = array();
	   $loeschungen[0] = '/FL\\s/i';
	   $loeschungen[1] = '/LA\\W\\w\\s/i';
	   $loeschungen[2] = '/\\d\\.\\d\\.*\\d*\\s/i';

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
    if (preg_match("/Objekt\\W*(.*)\nEPN/i", $alarmfax, $treffer)) 
	{
        $objekt = trim(preg_replace($loeschungen, ' ', $treffer[1]));
	}

    // Einsatzgrund
    if (preg_match("/Schlagw\\W*(.*)/i", $alarmfax, $treffer)) {
        $einsatzgrund = trim($treffer[1]);
        if (preg_match("/Stichwort.B[^\\w\\n]*(.*)/i", $alarmfax, $treffer)) {
            $einsatzgrund .= " " . trim($treffer[1]);
        }
	// Hashtags entfernen
	$einsatzgrund = str_replace ("#", " ", $einsatzgrund);


	
    }
    // Bemerkung
    if (preg_match("/\\W*BEMERKUNG\\W*\\n(?s)(.*)\\n.*DISPO/im", $alarmfax, $treffer)) {
        $bemerkung = trim($treffer[1]);
    }
    // Koordinate Rechtswert
    if (preg_match("/Rechtswert\\W+(.*)/i", $alarmfax, $treffer)) {
        $rw = trim($treffer[1]);
    }

    // Koordinate Hochwert
    if (preg_match("/Hochwert\\W+(.*)/i", $alarmfax, $treffer)) {
        $hw = trim($treffer[1]);

    }
    
    // Einsatzmittel
   
    if (preg_match_all("/Name\s\S\s(.*)/im", $alarmfax, $treffer)) {

        for ($i = 0; $i < count($treffer[0]); $i++) 
	{
	   $dispoliste[] = trim(preg_replace($loeschungen, ' ', $treffer[1][$i]));
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

?>
