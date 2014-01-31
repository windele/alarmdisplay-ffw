<?

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2013 Stefan Windele

Version 1.0

Dieses Script ist für die Anzeige des FMS-Status am Display zuständig. 

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
require "../config.inc.php";

// Sollen Statuswechsel von der Leitstelle mit angezeigt werden? true/false
$status_ils = 'true';

// FMS-Schriftzug anzeigen?
$show_fms_logo = 'true';

// Verbindung zur Datenbank herstellen und an Handle $db übergeben
$db = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME) or die ('Verbindung zur Datenbank fehlgeschlagen.');
$db->set_charset("utf8");


// Header an den Client senden
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
	header("Content-type: text/html; charset=utf-8"); // Hier kommt Text
	


// Anzeigelogik 
function anzeigebild_feststellen()
{
	// FMS-Tabelle abfragen, um Änderungen festzustellen
	global $db;
	$result = $db->query("SELECT * from tbl_fms");
	$datumsumme = "";
	while ($row=$result->fetch_row())
		{
			$datumsumme .= $row[4];
		}
	unset($row);
	$result->close();
	return "fms-".md5($datumsumme);
	
		
	
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
<title>Alarmdisplay - FMS-Status</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<script type="text/javascript" src="../js/alarmdisplay.js"></script>
<link rel='stylesheet' href='../css/fms.css' type='text/css' media='screen, projection'>
</head>

<?

// Stellen wir mal das aktuelle FMS-Bild fest.
$bildist = anzeigebild_feststellen();

// Jetzt kanns losgehen, wir starten mit dem Body-Tag
echo "<body onload='start(\"$bildist\", 5000, 0)' class='uhr'>\n";

// Fragen wir mal die Datenbank ab.
$result = $db->query("SELECT *, UNIX_TIMESTAMP(datum) from tbl_fms");

// Wieviele Fahrzeuge stehen in unserer Halle, äh Datenbank?
$fzg_zahl = $result->num_rows;

// Stati ermitteln
$fzg_status = array();
while ($row=$result->fetch_row())
	{
	$fzg_status[$row[0]]['fzgname'] = $row[1];
	$fzg_status[$row[0]]['fzgstatus'] = $row[2];
	$fzg_status[$row[0]]['lststatus'] = $row[3];
	$fzg_status[$row[0]]['datum'] = $row[6];
	$fzg_status[$row[0]]['aenderungdurch'] = $row[5];
	}
unset($row);
$result->close();

// Tabelle für Fahrzeuge basteln.
echo "<table class='table-uhr' cellspacing='0' cellpadding='0'>\n<tr>\n";

if ($show_fms_logo=='true')
{
echo "<td class='td-logo-uhr' rowspan='2'><div class='logo-uhr'>FMS</div></td>\n";
}

// Zeile mit Fahrzeugen und Stati berechnen und darstellen.

for ($i=1; $i<$fzg_zahl+1; $i++)
	{
	
	echo "<td class='fahrzeug-uhr'>".$fzg_status[$i]['fzgname']."</td>\n";

	switch ($fzg_status[$i]['fzgstatus'])
		{
		case "1":
		echo "<td class='fstatus-uhr eins'>".$fzg_status[$i]['fzgstatus']."</td>\n";
		break;
	
		case "2":
		echo "<td class='fstatus-uhr zwei'>".$fzg_status[$i]['fzgstatus']."</td>\n";
		break;

		case "3":
		echo "<td class='fstatus-uhr drei'>".$fzg_status[$i]['fzgstatus']."</td>\n";
		break;
	
		case "4":
		echo "<td class='fstatus-uhr vier'>".$fzg_status[$i]['fzgstatus']."</td>\n";
		break;

		case "5":
		echo "<td class='fstatus-uhr fuenf'>".$fzg_status[$i]['fzgstatus']."</td>\n";
		break;
	
		case "6":
		echo "<td class='fstatus-uhr sechs'>".$fzg_status[$i]['fzgstatus']."</td>\n";
		break;

		case "9":
		echo "<td class='fstatus-uhr neun'>".$fzg_status[$i]['fzgstatus']."</td>\n";
		break;
	
		default:
		echo "<td class='fstatus-uhr default'>".$fzg_status[$i]['fzgstatus']."</td>\n";
		break;
		}

	}

// neue Zeile beginne, alte vorher abschließen
echo "</tr><tr>\n";

// Zeile für die Daten.
for ($i=1; $i<$fzg_zahl+1; $i++)
	{
	
	if ($status_ils == 'true')
		{
		echo "<td class='datum-uhr'>".date("d.m.Y / H:i:s",$fzg_status[$i]['datum'])." (".$fzg_status[$i]['aenderungdurch'].")</td>\n";
		
		switch ($fzg_status[$i]['lststatus'])
			{
			case 'C':
			echo "<td class='lstatus-uhr C'>".$fzg_status[$i]['lststatus']."</td>\n";
			break;

			default:
			echo "<td class='lstatus-uhr default'>".$fzg_status[$i]['lststatus']."</td>\n";
			break;			
			}

		} else {
		echo "<td colspan='2' class='lstatus-uhr'>".date("d.m.Y / H:i:s",$fzg_status[$i]['datum'])."</td>\n";
		}
	
	}

// So, fertig mit der Tabelle
echo "</tr>\n";
echo "</table>\n";




// Verbindung zur Datenbank schließen
$db->close();


//Ende HTML-Body --> ausserhalb PHP
?>
</body>
</html>
