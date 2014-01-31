<?

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2012 Stefan Windele

Version 1.0.0

Dieses Script stellte die Route zum Einsatzort auf einer Google-Map-Karte dar.
Abhängig vom Einsatzort werden verschiedene Zoom-Stufen verwendet.

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
  #map_canvas { height: 100% }
</style>

<script type="text/javascript"
    src="https://maps.google.com/maps/api/js?sensor=false&region=DE">
</script>


<script type="text/javascript">


var directionsDisplay;
var directionsService = new google.maps.DirectionsService();
var map;
var einsatzort = "";

function initialize() {
 geocoder = new google.maps.Geocoder();
  directionsDisplay = new google.maps.DirectionsRenderer();
  var feuerwehrhaus = new google.maps.LatLng(48.55453, 12.162039999999934);
  var myOptions = {
    zoom:13,
    disableDefaultUI: true,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    center: feuerwehrhaus
  }
  map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
  google.maps.event.addListener(map, 'bounds_changed', function() {setTimeout(zentriere,1000);});
 google.maps.event.addListener(map, 'center_changed', function() {setTimeout(zoome,1000);});

  directionsDisplay.setMap(map);
  // Standard-Marker ausblenden
  directionsDisplay.setOptions({suppressMarkers:true});
  calcRoute();
 }

function calcRoute() {


  var request = {
    origin:"<? echo $parameter['MAPFFWHAUS']; ?>",
    destination: "<? echo $_GET['strasse']." ".$_GET['hausnr'].", ".$_GET['ort'] ?>",
    travelMode: google.maps.TravelMode.DRIVING
  };

  directionsService.route(request, function(result, status) 
  {
    if (status == google.maps.DirectionsStatus.OK) 
	{
	directionsDisplay.setDirections(result);
   	
	//Einsatzort markieren 
	var lastMile = result.routes[0].legs[0].steps.length - 1; 
	einsatzort = result.routes[0].legs[0].steps[lastMile].end_point;
	var marker = new google.maps.Marker ({ position: einsatzort, map: map});
	}
   });
}


function zentriere() { map.setCenter(einsatzort); }


function zoome() 
{ 
	// Feststellen, ob wir noch in unserem Ortsgebiet sind
	var ortaufkarte = document.getElementById("ortaufkarte").value;
	var ergebnis = ortaufkarte.search(/<? echo $parameter['MAPUMFELD']; ?>/i);
	var zoomlevel = <? echo $parameter['ROUTEZOOMSTADT']; ?>;
	if (ergebnis != -1)
	{
		// Wir sind im Ortsgebiet
		zoomlevel = <? echo $parameter['ROUTEZOOMSTADT']; ?>;
	} else {
		// Wir sind auf dem Land
		zoomlevel = <? echo $parameter['ROUTEZOOMLAND']; ?>;
	}
	
	map.setZoom(zoomlevel); 
}

</script>

</head>

<body onload="initialize()">
  <input type="hidden" id="ortaufkarte" value="<? echo $_GET['ort']; ?>">
  <div id="map_canvas" style="width:100%; height:100%"></div>

</body>
</html>

