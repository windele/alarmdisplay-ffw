<?php 

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2012 Stefan Windele

Version 0.1.5

Dieses Script ist für die Anzeige des richtigen Bildschirmes am Display zuständig. 
Dazu bedient es sich verschiedener Module (Einsatz, Uhr, ...), die in Abhängigkeit
von der Anzeigelogik beim Seitenabruf an das Display/Client ausgeliefert werden.
Zusätzlich fragen die Displays/Clients in regelmäßigen Zeitabständen via AJAX
bei diesem Skript nach, ob der Bildschirm aktualisiert werden muss.

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


// Einbinden der Konfigurationsdatei
require "config.inc.php";

// Verbindung zur Datenbank herstellen und an Handle $db übergeben
$db = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME) or die ('Verbindung zur Datenbank fehlgeschlagen.');
$db->set_charset("utf8");

// Abfrage der im Skript benötigten Konfigurationseinstellungen aus der Datenbank.
$result = $db->query("SELECT parameter, wert FROM tbl_adm_params WHERE parameter IS NOT NULL");
$parameter = array();
while ($row=$result->fetch_row())
	{
	$parameter[$row[0]] = $row[1];
	}
unset($row);
$result->close();

// Variablendeklaration
$hinweistexte = array();


// Header an den Client senden
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
	header("Content-type: text/html; charset=utf-8"); // Hier kommt Text
	


// Anzeigelogik 
function anzeigebild_feststellen()
{
	// Letzten Einsatz abfragen, ID und Zeit ermitteln
	global $db, $parameter, $hinweistexte;
	$result = $db->query("SELECT id, UNIX_TIMESTAMP(alarmzeit) FROM tbl_einsaetze ORDER BY id DESC LIMIT 1");
	$row = $result->fetch_row();
	$result->close();
		
	// Wie alt ist der Einsatz?
	if(($row[1]+intval($parameter['EINSATZANZEIGEDAUER']))>time())
	{
	// Wir sollten den Einsatz anzeigen.
	return "einsatz-".$row[0];
	} else {
	// Wir prüfen, ob wir die Uhr oder Hinweistexte anzeigen.
	
	// Dazu werten wir erst mal die Texte aus.
	for ($i=1; $i<6; $i++)
	{
	$hinweistext = "HINWEISTEXTTEXT".$i;
	
	// Ist etwas hinterlegt? Sonst ist der Text nicht relevant.

	if ($parameter[$hinweistext] != "")
		{
		// OK, ein Text wurde hinterlegt. Aber muss er auch angezeigt werden? Prüfen wir das Datum
		$von = "HINWEISTEXTVON".$i; 
		$bis = "HINWEISTEXTBIS".$i;
		$von = strtotime($parameter[$von]);
		$bis = strtotime($parameter[$bis]);
		
		if ($von < time() and time() < $bis)
			{
			// Der Text ist aktuell, also merken wir ihn uns
			$hinweistexte[] = $parameter[$hinweistext];
			} 
		} 
	
	}

	

	// Gibt es überhaupt Texte? Ansonsten zeigen wir die Uhrzeit an.
	if (count($hinweistexte)>0)
		{
		// ja, es gibt ein paar Texte drin. Rufen wir das Modul auf.
		
		return "hinweis-".md5(serialize($hinweistexte).$parameter['HINWEISBACKGROUND']);	


		} else {
		
		// Nein, keine Texte hier. Also zeigen wir die Uhr an.
		// Damit das Display bei Änderung des Uhr-Hinweistextes neulädt, hängen wir die Prüfsumme an.
		return "uhrzeit-".md5($parameter['UHRTEXT'].$parameter['FMSSTATUSUHR'].$parameter['UHRBACKGROUND']);
	
		}


	}

}


// Function zur Updateprüfung, die vom Client mit AJAX angesprochen wird

if (@$_GET["bildist"]) 
{
	// Aktuelles Bild feststellen
	$bildsoll = anzeigebild_feststellen();

	// Wenn der Client die komplette Seite aktualisieren soll, schicken wir ihm ein FALSE
	if ($bildsoll == $_GET["bildist"])
	{
	echo "true";  
	} else {
	echo "false"; 
	}

	//Datenbank schließen und beenden
	$db->close();
	exit();
}


###############################################
# Eigentliche Bilddarstellung startet ab hier #
###############################################

// Geben wir mal den Start des HTML-Scriptes aus
?>
<!DOCTYPE html>
<html>
<head>
<title>Alarmdisplay - <?php echo $parameter['NAMEFEUERWEHR'];?></title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<script type="text/javascript" src="js/alarmdisplay.js"></script>
<link rel='stylesheet' href='css/screen.css' type='text/css' media='screen, projection'>
</head>

<?php 

// Was sollen wir überhaupt anzeigen?
$bildist = anzeigebild_feststellen();

// Jetzt kanns losgehen

// Welches Bild zeigen wir denn an? Je nachdem senden wir einen anderen Body-Tag.
switch (substr($bildist,0,7))
{	
	case "uhrzeit":
	// Prüfen, ob Hintergrundbildeinstellung aktiv:
	if ($parameter['UHRBACKGROUND']=='true')
	{
	echo "<body onload='start(\"$bildist\", ".$parameter['AKTUALISIERUNGSZEIT'].", ".$parameter['HINWEISAKTUALISIERUNG'].")' style='background-color:".$parameter['UHRHINTERGRUND']."; margin: 0 0 0 0; overflow: hidden;background-image:url(images/background.jpg);'>\n";
	}	
	else
	{
	echo "<body onload='start(\"$bildist\", ".$parameter['AKTUALISIERUNGSZEIT'].", ".$parameter['HINWEISAKTUALISIERUNG'].")' style='background-color:".$parameter['UHRHINTERGRUND']."; margin: 0 0 0 0; overflow: hidden;'>\n";
	}
	break;

	case "einsatz":
	echo "<body onload='start(\"$bildist\", ".$parameter['AKTUALISIERUNGSZEIT'].", ".$parameter['HINWEISAKTUALISIERUNG'].")' style='margin: 0 0 0 0; overflow: hidden;'>\n";
	break;

	case "hinweis":
	// Prüfen, ob Hintergrundbildeinstellung aktiv:
	if ($parameter['HINWEISBACKGROUND']=='true')
	{
	echo "<body onload='start(\"$bildist\", ".$parameter['AKTUALISIERUNGSZEIT'].", ".$parameter['HINWEISAKTUALISIERUNG'].")' style='background-color:".$parameter['HINWEISHINTERGRUND']."; margin: 0 0 0 0; overflow: hidden;background-image:url(images/background.jpg);'>\n";
	}	
	else
	{
	echo "<body onload='start(\"$bildist\", ".$parameter['AKTUALISIERUNGSZEIT'].", ".$parameter['HINWEISAKTUALISIERUNG'].")' style='background-color:".$parameter['HINWEISHINTERGRUND']."; margin: 0 0 0 0; overflow: hidden;'>\n";
	}

	break;

}


// Wg. der sich ändernden Einsatz-ID checken wir nur die ersten 7 Buchstaben
// Der Rest ist egal, da wir sowieso die Seite neu laden.

switch (substr($bildist,0,7))
{
case "uhrzeit":
	require "modules/mod_uhrzeit.php";
	break;

case "einsatz":
	require "modules/mod_einsatz.php";
	break;

case "hinweis":
	require "modules/mod_hinweis.php";
	break;


default:
	echo "<body onload='start(\"$bildist\", ".$parameter['AKTUALISIERUNGSZEIT'].")' style='background-color: #fff; margin: 0 0 0 0;'>\n";
	echo "<h1 align='center'>Unbekanntes Modul, bitte verständigen Sie Ihren Systemadministrator.</h1>";
	break;
}






// Verbindung zur Datenbank schließen
$db->close();


//Ende HTML-Body --> ausserhalb PHP
?>
</body>
</html>
