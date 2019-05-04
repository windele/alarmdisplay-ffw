<?php 

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2012-2019 Stefan Windele

Version 2.0.0

Dieses Script stell eine Übersichtskarte zum Einsatzort im BayernAtlas dar. 
Das Ziel wird mit den von der Leitstelle übergebenen Koordinaten ermittelt.
Kartenlayer und Zoomstufen sind über das Administrationsmenü einstellbar.
Diese Karte wird im Alarmmodul unten eingeblendet.

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

// Abfrage der im Skript benötigten Konfigurationseinstellungen aus der Datenbank.
// Abfrage der im Skript benötigten Konfigurationseinstellungen aus der Datenbank.
$result = $db->query("SELECT parameter, wert FROM tbl_adm_params WHERE parameter IS NOT NULL");
$parameter = array();
while ($row=$result->fetch_row())
	{
	$parameter[$row[0]] = $row[1];
	}
unset($row);
$result->close();
$db->close();
?>


<html>
<head>

<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

<style type="text/css">
  html { height: 100% }
  body { height: 100%; margin: 0px; padding: 0px }
</style>



</head>

<body>


  <iframe src='https://geoportal.bayern.de/bayernatlas/embed.html?N=<?php echo $_GET['hw']."&E=".$_GET['rw'] ." &zoom=". $parameter['MAPZOOMUNTEN'] ."&lang=de&topic=ba&bgLayer=". $parameter['LAYERKARTEUNTEN'] ?>&crosshair=marker&catalogNodes=122' width='100%' height='100%' frameborder='0' style='border:0'></iframe>

</body>
</html>

