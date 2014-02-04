<?php 

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2013 Stefan Windele

Version 1.0

Dieses Script ist für das Update des FMS-Status zuständig. Das Script wird 
von einer FMS-Decoder-Software über HTTP aufgerufen und speichert den Status
mit einem Zeitstempel in der Datenbank.

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

// Verbindung zur Datenbank herstellen und an Handle $db übergeben
$db = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME) or die ('Verbindung zur Datenbank fehlgeschlagen.');
$db->set_charset("utf8");

// SQL-Statement zusammensetzen
// Vorab entscheiden wir aber, in welche Richtung ein Signal gesendet wird.
switch (mysql_real_escape_string($_GET['ri']))
	{
	case 'lst':
	// Sobald das Fahrzeug einen Status sendet, löschen wir den Leitstellen-Status wieder.
	$sqlupdate = "UPDATE tbl_fms SET fzgstatus ='".mysql_real_escape_string($_GET['status'])."', aenderungdurch='Fzg', lststatus='' WHERE fzgid=".mysql_real_escape_string($_GET['fzg']);
	break;
	
	case 'fzg':
	// Da FMS-Status von der ILS als Zahlen gesendet werden, müssen wir sie umrechnen.
	switch (mysql_real_escape_string($_GET['status']))
	{
	case '1':
	$lststatus="A";
	break;

	case '2':
	$lststatus="E";
	break;

	case '3':
	$lststatus="C";
	break;

	case '4':
	$lststatus="F";
	break;

	case '5':
	$lststatus="H";
	break;

	case '6':
	$lststatus="J";
	break;

	case '7':
	$lststatus="L";
	break;


	default:
	$lststatus="-";
	break;		
	}
	
	

	$sqlupdate = "UPDATE tbl_fms SET lststatus ='".$lststatus."', aenderungdurch='ILS' WHERE fzgid=".mysql_real_escape_string($_GET['fzg']);
	break;

	default:
	echo "Keine Richtung angegeben.\n\n";
	echo "Parameter fuer Skript: \n";
	echo "<b>ri</b> [fzg | lst] - Status in welche Richtung?\n";
	echo "<b>status</b> [Zahl | Buchstabe] - Status\n";
	echo "<b>fzg</b> [0-9] - Fahrzeugnummer\n";
	break;
	}

// Update durchführen
if ($db->query($sqlupdate))
{echo date("Y-m-d H:i:s")." OK! Status ".mysql_real_escape_string($_GET['status'])." von Fahrzeug ".mysql_real_escape_string($_GET['fzg']). " Richtung ".$_GET['ri']." aktualisiert.";}


// Verbindung zur Datenbank schließen
$db->close();


