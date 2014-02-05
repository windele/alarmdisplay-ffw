<?php 

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2012-2014 Stefan Windele

Version 1.0.0

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



// Konfigurationsdatei einbinden
require "/var/www/alarmdisplay/config.inc.php";


// Reset der Variablen
$alarmzeit="";
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

if (!($alarmfax = file($_SERVER['argv'][1], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))) {die;};
$faxlaenge = count($alarmfax);


//Hilfsfunktion zum Zerlegen der Zeilen
function zeile_zerlegen($n)
{
global $zeile_zerlegt, $alarmfax;
$zeile_zerlegt = explode(":", $alarmfax[$n]);
}
 



// Bekannte Probleme der Texterkennung lösen und durch richtige Wörter ersetzen

for ($zeile=0; $zeile<$faxlaenge; $zeile++) 
{
	// Leerzeichenprobleme vor Doppelpunkt
	$searching = " :";
	$replacement = ":";
	$alarmfax[$zeile] = str_replace($searching, $replacement, $alarmfax[$zeile]);

	// Ortsname sollte richtig erkannt werden
	$patterns = array();
	$patterns[0] = '/P(\s)?i(\s)?f(\s)?l(\s)?a(\s)?s/';
	$patterns[1] = '/PifIas/';
	$patterns[2] = '/Pfilas/';
	$patterns[3] = '/PfiIas/';
	$replacement = 'Piflas';
	$alarmfax[$zeile] = preg_replace($patterns, $replacement, $alarmfax[$zeile]);

	// Entfernen der von der Texterkennung erkannten Trennlinien
	$pattern = '/[-‒\s]{5,}(?=\w{5,})|(?<=\w)[-‒\s]{5,}/'; 
	$replacement = '';
	$alarmfax[$zeile] = preg_replace($pattern, $replacement, $alarmfax[$zeile]);

	// Leerzeichen bei den 2.1.x Kennungen
	$patterns = array();
	$patterns[0] = '/(?<=[0-9])[,.] (?=[0-9])/';
	$replacement = '.';
	$alarmfax[$zeile] = preg_replace($patterns, $replacement, $alarmfax[$zeile]);

	// Ziffernkombinationen zur besseren Lesbarkeit
	$patterns = array();
	$patterns[0] = "2.1.1";
	$patterns[1] = "2.1.2";
	$patterns[2] = "2.1"; 
	$patterns[3] = "LA-S ";
	$patterns[4] = "LA/S ";
	$patterns[5] = "LA-L ";
	$patterns[6] = "LA/L "; 
	$replacement = "";
	$alarmfax[$zeile] = str_replace($patterns, $replacement, $alarmfax[$zeile]);

	// Brandmelderkennungen
	$searching = '/[0-9]{8}\-01:/';
	$replacement = "";
	$alarmfax[$zeile] = preg_replace($searching, $replacement, $alarmfax[$zeile]);

}


// Analyse der Datei anhand der Faxstruktur ILS Landshut
// Zeilenweises durchgehen und Ermittlung relevanter Infos.
// Feststellen, ab wann die Dispoliste beginnt.

for ($zeile=0; $zeile<$faxlaenge; $zeile++) 
{

// Sonderzeichen rauslöschen
$alarmfax[$zeile]=trim($alarmfax[$zeile]);


// Aber jetzt gehts los mit der Analyse

	switch($alarmfax[$zeile])
	{
	case "MITTEILER":
		zeile_zerlegen($zeile+1);
		$mitteiler=(count($zeile_zerlegt)>1) ? ltrim($zeile_zerlegt[1]) : "";
		break;

	case "EINSATZORT":
		zeile_zerlegen($zeile+1);
		$split_strasse = (count($zeile_zerlegt)>1) ? explode(" ",ltrim($zeile_zerlegt[1])) : "";
		
		//Unterscheidung, ob Autobahn oder nicht.
		if ($split_strasse[1] == "A92")
		{		
			// Zusammenbasteln des Autobahn-Strings
			for ($ctr=1; $ctr<(count($split_strasse)-1); $ctr++)
			{
			// Ersatz des Wortes "Fahrtrichtung" - deaktiviert
			//if ($split_strasse[$ctr] == "Fahrtrichtung") {$split_strasse[$ctr] = "Richt.";}
			$strasse .= $split_strasse[$ctr]." ";
			}
			// Bei Autobahn ist die Hausnummer die Kilometerzahl
			$hausnr = "bei km ".ltrim($zeile_zerlegt[2]);
		}
		else
			{
			// Wir haben keine Autobahn. Alles läuft normal. Straße und Hausnummer zusammensetzen.

			$j = count($split_strasse);
			for ($i=0; $i<$j-1; $i++)
			{	
			$strasse .= ltrim($split_strasse[$i])." ";
			}
			// Hausnummer ermitteln. Für Google Maps Ergänzungen wegstreichen.
			$hausnr = explode(" ",ltrim($zeile_zerlegt[2]));
			$hausnr = $hausnr[0];
		}

		//Einsatzort feststellen
		$zeile_zerlegt = explode(" ", trim($alarmfax[$zeile+2]));
		$ortsteile = count ($zeile_zerlegt);
		// DefaultORT entfernen, stört auf Display
		if ($ortsteile > 2) 
		{
		
		$ort = $zeile_zerlegt[1]." ".$zeile_zerlegt[2];

		/* Ursprüngliche Schleife, für Test auskommentiert

		for ($i=0; $i<$ortsteile-1; $i++)
			{
			$ort .= $zeile_zerlegt[$i+1]." ";
			$ort = ($ort=="Default ORT ") ? " " : $ort;
			}
		*/

		}else{
		$ort = ($zeile_zerlegt[1]=="DefaultORT")? "" : $zeile_zerlegt[1];
		}

		// für Autobahnen die Lokation ermitteln, geht nur wenn km bekannt
		if ((substr($strasse, 0, 3)=="A92") && (substr($hausnr, 7) != ""))
		{
			$km = floatval(substr($hausnr, 7)); echo $km;

			// Fahrtrichtung ermitteln
			if (substr($strasse, 18, 3)=="Deg")
			{
				// Es geht nach Deggendorf
				switch ($km)
				{
					case (48<=$km) && ($km<=57):
					$ort = "Strecke Moosburg-Nord / Landshut-West";
					break;
					case (57<$km) && ($km<=61):
					$ort = "Strecke Landshut-West / Altdorf";
					break;
					case (61<$km) && ($km<=64):
					$ort = "Strecke Altdorf / Landshut-Nord";
					break;
					case (64<$km) && ($km<69):
					$ort = "Strecke Landshut-Nord / Essenbach";
					break;
					case (69<=$km) && ($km<80):
					$ort = "Strecke Essenbach / Wörth";
					break;	
					case (69<=$km) && ($km<92):
					$ort = "Strecke Wörth / Dingolfing";
					break;	
				}
			 } else {
					// Wir fahren nach München.
				switch ($km)
				{
					case (48<=$km) && ($km<=57):
					$ort = "Strecke Landshut-West / Moosburg-Nord";
					break;
					case (57<$km) && ($km<=61):
					$ort = "Strecke Altdorf / Landshut-West";
					break;
					case (61<$km) && ($km<=64):
					$ort = "Strecke Landshut-Nord / Altdorf";
					break;
					case (64<$km) && ($km<69):
					$ort = "Strecke Essenbach / Landshut-Nord";
					break;
					case (69<=$km) && ($km<80):
					$ort = "Strecke Wörth / Essenbach";
					break;	
					case (69<=$km) && ($km<92):
					$ort = "Strecke Dingolfing / Wörth";
					break;	
				}
			}
		}

		// Ermittlung Objekt
		zeile_zerlegen($zeile+3);
		$objekt = (count($zeile_zerlegt)>1) ? $zeile_zerlegt[1] : "";


		break;

	case "EINSATZGRUND":
		zeile_zerlegen($zeile+1);
		$einsatzgrund = (count($zeile_zerlegt)>1) ? $zeile_zerlegt[1] : "";
		zeile_zerlegen($zeile+2);
		$prio=(count($zeile_zerlegt)>1) ? intval($zeile_zerlegt[1]) : "";
		break;

	case "BEMERKUNG":
		//Wir müssen Fälle ohne Bemerkung oder mit zwei Zeilen abfangen.
		$bemerkungszeile=$zeile+1;
		while (trim($alarmfax[$bemerkungszeile]) != "DISPOLISTE") 
		{
		$bemerkung .= $alarmfax[$bemerkungszeile];
		$bemerkungszeile++;
		}
		break;

	case "DISPOLISTE":
		$beginndispoliste=$zeile+1;
		break;

	}
}

// Wir werten die Dispoliste aus

for ($zeile=0; $zeile<50; $zeile++) 
{
	//Behelfslösung: Wir haben 50 Dispolistenspeicherplätze, aber die Textdatei ist mit Sicherheit nicht so lange
	//also vermeiden wir Fehlermeldungen
	
	if (($zeile+$beginndispoliste)>$faxlaenge-1)
	{
	$dispozeile = "";
	}else{
	$dispozeile = $alarmfax[$zeile+$beginndispoliste];
	}


	// aber jetzt werten wir endlich aus.

	if ($dispozeile!="")
	{
		// Wir brauchen nur die hinteren drei Wörter, deshalb ermitteln wir sie
		$zeile_zerlegt = explode(" ", $dispozeile);
       		$zeilenteile = count($zeile_zerlegt); 
		
		// Wir lassen doofe Abkürzungen weg. Stört auch nur in der Datenbank. VSA-Weiche!
		if ($zeile_zerlegt[$zeilenteile-3]=="" || $zeile_zerlegt[$zeilenteile-3]=="FL")
		{$dispoliste[$zeile]=$zeile_zerlegt[$zeilenteile-2]." ".$zeile_zerlegt[$zeilenteile-1];
		} else {
		$dispoliste[$zeile]=$zeile_zerlegt[$zeilenteile-3]." ".$zeile_zerlegt[$zeilenteile-2]." ".$zeile_zerlegt[$zeilenteile-1];

		}
	
	}
	else
	{
	$dispoliste[$zeile]="";
	}

} 



// DEBUGGING FÜR ENTWICKLUNG


echo "Mitteiler: ".$mitteiler."\n";
echo "Strasse: ". $strasse."\n";
echo "Haus-Nr: ".$hausnr."\n";
echo "Ort: ".$ort."\n";
echo "Einsatzgrund: ".$einsatzgrund."\n";
echo "Prio: ".$prio."\n";
echo "Bemerkung: ".$bemerkung."\n";
echo "Beginn der Dispoliste in Zeile: ".$beginndispoliste."\n";
for ($i=0; $i<50; $i++)
{
if ($dispoliste[$i]!="") echo "Dispo ".$i.": ".$dispoliste[$i]."| \n";
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
$dispoliste[0-24]

*/

// Verbindung zur Datenbank herstellen und an Handle $db übergeben
$db = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME) or die ('Verbindung zur Datenbank fehlgeschlagen.');
$db->set_charset("utf8");


// SQL-Abfrage zusammensetzen
$sqlinsert = "INSERT INTO tbl_einsaetze (`mitteiler`, `strasse`, `hausnr`, `ort`, `objekt`, `station`, `schlagw`, `prio`, `bemerkung`, `dispo0`, `dispo1`, `dispo2`, `dispo3`, `dispo4`, `dispo5`, `dispo6`, `dispo7`, `dispo8`, `dispo9`, `dispo10`, `dispo11`, `dispo12`, `dispo13`, `dispo14`, `dispo15`, `dispo16`, `dispo17`, `dispo18`, `dispo19`, `dispo20`, `dispo21`, `dispo22`, `dispo23`, `dispo24`, `dispo25`, `dispo26`, `dispo27`, `dispo28`, `dispo29`, `dispo30`, `dispo31`, `dispo32`, `dispo33`, `dispo34`, `dispo35`, `dispo36`, `dispo37`, `dispo38`, `dispo39`, `dispo40`, `dispo41`, `dispo42`, `dispo43`, `dispo44`, `dispo45`, `dispo46`, `dispo47`, `dispo48`, `dispo49`) VALUES ('".$mitteiler."', '".$strasse."', '".$hausnr."', '".$ort."', '".$objekt."', '".$station."', '".$einsatzgrund."', '".$prio."', '".$bemerkung."', '".$dispoliste[0]."', '".$dispoliste[1]."', '".$dispoliste[2]."', '".$dispoliste[3]."', '".$dispoliste[4]."', '".$dispoliste[5]."', '".$dispoliste[6]."', '".$dispoliste[7]."', '".$dispoliste[8]."', '".$dispoliste[9]."', '".$dispoliste[10]."', '".$dispoliste[11]."', '".$dispoliste[12]."', '".$dispoliste[13]."', '".$dispoliste[14]."', '".$dispoliste[15]."', '".$dispoliste[16]."', '".$dispoliste[17]."', '".$dispoliste[18]."', '".$dispoliste[19]."', '".$dispoliste[20]."', '".$dispoliste[21]."', '".$dispoliste[22]."', '".$dispoliste[23]."', '".$dispoliste[24]."', '".$dispoliste[25]."', '".$dispoliste[26]."', '".$dispoliste[27]."', '".$dispoliste[28]."', '".$dispoliste[29]."', '".$dispoliste[30]."', '".$dispoliste[31]."', '".$dispoliste[32]."', '".$dispoliste[33]."', '".$dispoliste[34]."', '".$dispoliste[35]."', '".$dispoliste[36]."', '".$dispoliste[37]."', '".$dispoliste[38]."', '".$dispoliste[39]."', '".$dispoliste[40]."', '".$dispoliste[41]."', '".$dispoliste[42]."', '".$dispoliste[43]."', '".$dispoliste[44]."', '".$dispoliste[45]."', '".$dispoliste[46]."', '".$dispoliste[47]."', '".$dispoliste[48]."', '".$dispoliste[49]."')";

// Wir prüfen, ob auch ein Fax von der ILS erkannt wurde. Nur dann wird die Aktion ausgelöst.
if ($einsatzgrund != "") 
{
	
	$db->query($sqlinsert);
		

	// Weils so schön ist, sollten wir den Bildschirm einschalten; wir nehmen das Alarmskript das den Browser aufmacht.
	passthru(__DIR__."/../administrator/bild-an-alarm.sh");

	// Setzen der Umgebung
	setlocale(LC_ALL, 'de_DE.utf8');
	
	// Abfrage der Konfigurationsparameter

	$result = $db->query("SELECT parameter, wert FROM tbl_adm_params WHERE parameter IS NOT NULL");
	$parameter = array();
	while ($row=$result->fetch_row())
	{
		$parameter[$row[0]] = $row[1];
	}
	unset($row);
	$result->close();
	
	// SMS-Funktion
	if($parameter["SMSENABLED"]=="true")
	{
				
		// SMS-Inhalt bauen
		$smstext = "ALARM (".date("H:i") . "): ". $einsatzgrund . "- ".$objekt." ". $strasse . " ". $hausnr . " ". $ort ."/ ". $bemerkung; 



		//URL zusammenbauen
		$url = 'http://www.RA-Server.de/webin.php?log_user='; 
		$url .= urlencode($parameter["SMSUSER"]).'&log_pass=';
		$url .= urlencode($parameter["SMSPASS"]).'&listcode=';
		$url .= urlencode($parameter["SMSALARMGRUPPEN"]);

		if ($parameter["SMSFLASH"] == "true")
		{
				//SMS als Flash-SMS senden
				$url .= '&flash=1';
		}	 

		$url .= '&free='.urlencode($smstext);

	
		//Verbindung aufbauen und an Handle übergeben
		$ch = curl_init();
	
		//Optionen setzen
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_TIMEOUT, '3');
		
		//Abfrage ausführen
		$result = curl_exec($ch);
		echo '---SMS-RUECKMELDUNG---'."\n".$result."\n\n";
		
	
		//Verbindung beenden
		curl_close($ch);

		
	}

	// Prüfen, ob ein Screenshot gebraucht wird - wenn ja, erstellen wir einen 
	if($parameter["DRUCKENSCREEN"]=="true" || $parameter["MAILSCREENSHOT"]=="true")
	{
	passthru(__DIR__."/screenshot-jpg.sh");
	}



	// E-Mail-Funktion
	if($parameter["MAILENABLED"]=="true")
	{

		// lasst uns eine Mail schicken
		require('class.phpmailer.php');
		$mail = new PHPMailer();
		$mail->IsSMTP(); 
		$mail->Host = $parameter["SMTPSERVER"]; 
		$mail->SMTPAuth = true;
		$mail->Port = $parameter["SMTPPORT"]; 
		$mail->SMTPSecure = $parameter["SMTPSECURE"];      
		$mail->Username = $parameter["SMTPUSER"]; 
		$mail->Password = $parameter["SMTPPASS"]; 
		$mail->From = $parameter["SMTPSENDERMAIL"];
		$mail->FromName = $parameter["SMTPSENDER"];
		$mail->AddAddress($parameter["SMTPSENDERMAIL"]);
		$mail->Subject = "Alarm: ".utf8_decode(trim($einsatzgrund));

		// Soll ein Screenshot beigefügt werden?
		if ($parameter["MAILSCREENSHOT"]=="true")
		{
		// Screenshot anhängen.
		$mail->AddAttachment('/tmp/screenmail.jpg', 'alarmdisplay-screenshot.jpg');
		}

		$mail->Priority = 1;
		$mail->WordWrap = 70;

		// Adressen ermitteln
		$result = $db->query("SELECT email FROM tbl_alarm_user WHERE email IS NOT NULL");
		
		//Fehlermeldung abfangen
		if($result->num_rows>0)
		{

		while ($row=$result->fetch_row())
		{
			$mail->AddBCC($row[0]);
		}
		unset($row);
		$result->close();
		

		// Text der Mail zusammenbauen

		$text = "Alarm für die ".$parameter["NAMEFEUERWEHR"]."\n";
		$text .= "---------------------------------------------------\n";
		$text .= "EINSATZDATEN:\n";		
		$text .= "---------------------------------------------------\n";
		$text .= "Stichwort: ".$einsatzgrund."\n";
		$text .= "Einsatzort: ".$strasse." ".$hausnr.", ".$ort."\n";
		$text .= "Einsatzobjekt: ".$objekt."\n";
		$text .= "---------------------------------------------------\n";
		$text .= "BEMERKUNGEN:\n";		
		$text .= "---------------------------------------------------\n";
		$text .= "Bemerkung ILS: ".$bemerkung."\n";
		$text .= "Bemerkung Kdt: ";
		($parameter["EINSATZHINWEIS"]!="") ? $text.= $parameter["EINSATZHINWEIS"]."\n" : $text .= "-keine-\n";
		$text .= "---------------------------------------------------\n";
		$text .= "DISPONIERTE FAHRZEUGE:\n";
		$text .= "---------------------------------------------------\n";

		for ($i=0; $i<50; $i++)
		{
			if ($dispoliste[$i]!="") $text.= $dispoliste[$i]."\n";
		}
		$text .= "---------------------------------------------------\n";
		$text .= "ERGÄNZUNGEN:\n";		
		$text .= "---------------------------------------------------\n";
		$text .= "Zeitstempel Faxeingang: ".strftime("%A, %d.%m.%Y // %H:%M")."\n";	
		
		// Link auf Google Maps und Navigationssoftware, falls wir nicht auf der Autobahn sind.
		if (substr($strasse, 0, 3)!="A92") 
		{
			$text .= "Karte: \n";
			$text .= "http://maps.google.de/maps?q=".str_replace(" ", "+", trim($strasse))."+".str_replace(" ", "+", trim($hausnr)).",".str_replace(" ", "+", trim($ort))."\n";
			$text .= "Handy-Navigation für Telekom-Smartphones: \n";
			$text .= "navigon://address/Einsatzstelle/DEU/".str_replace("%26%26", "/",rawurlencode(str_replace(" ", "&&", trim($ort))))."/".rawurlencode(trim($strasse))."/".rawurlencode(trim($hausnr))."\n";
		}
		$text .= "---------------------------------------------------\n";
		$text .= "Automatisch generiert durch Alarmdisplay FF Piflas \n\n";
		$text .= "-ENDE-\n";



		$mail->Body = utf8_decode($text);
		$mail->Send();
		
		}

	}


	// Druckfunktion Fax
	if($parameter["DRUCKENFAX"]=="true")
	{
	$exemplare = intval($parameter["DRUCKENFAXWIEOFT"]) - 1;
	$datei = trim(shell_exec("ls -tr /var/spool/hylafax/recvq | tail -1"));
	$befehl = 'convert /var/spool/hylafax/recvq/'.$datei.' -duplicate '.$exemplare.' -page a4 -rotate 180 -density 72x72 /tmp/fax.pdf';
	
	// PDF erstellen
	passthru($befehl);

	// Fax drucken
	passthru("lpr -# 1 /tmp/fax.pdf");

	}



	// Druckfunktion Screenshot
	if($parameter["DRUCKENSCREEN"]=="true")
	{
	$exemplare = intval($parameter["DRUCKENSCREENWIEOFT"]) - 1;
	
	if(($parameter["DRUCKENANFAHRT"]=="true")&&(substr($strasse, 0, 3)!="A92"))
		{
		$anfahrtskarte =  "wkhtmltoimage --height 1280 --width 1920 --javascript-delay 12500 --quality 100 'http://localhost/alarmdisplay/modules/mod_einsatz_inc_route_zoom_panel.php' /tmp/anfahrt.jpg";
		
		// Anfahrtskarte erstellen
		putenv("DISPLAY=:0");
		putenv("LANG=de_DE.UTF-8");
		setlocale (LC_ALL, 'de_DE.UTF-8');
		passthru ($anfahrtskarte);


		$befehl = "convert -border 40 -bordercolor white -size A4 -append -density 300x300 -resize 2480x3506 -duplicate ".$exemplare." /tmp/screenshot.jpg /tmp/anfahrt.jpg -gravity center /tmp/screenshot.pdf";

		} else {
		$befehl = "convert -border 40 -bordercolor white -size A4 -density 300x300 -resize 2480x3506 -duplicate ".$exemplare." /tmp/screenshot.jpg -gravity center /tmp/screenshot.pdf";
		}
	
	// PDF erstellen
	passthru($befehl);

	// PDF drucken
	passthru("lpr -# 1 /tmp/screenshot.pdf");
	}	
	

}



// Verbindung zur Datenbank schließen
$db->close();



?>
