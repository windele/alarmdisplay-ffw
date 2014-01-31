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

$result = $db->query("SELECT strasse, hausnr, ort FROM tbl_einsaetze ORDER BY id DESC LIMIT 1");
$anschrift = array();
while ($row=$result->fetch_row())
	{
	$strasse=$row[0];
	$hausnr=$row[1];
	$ort=$row[2];
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

    #directions_panel {
        height: 100%;
        float: right;
        width: 690px;
        overflow: auto;
	font-size:
      }

.adp-substep {
font-size: 200%;
}
	

      #map_canvas {
	height: 1260px;
	width: 1230px;
	margin-right: 700px;
      }

    
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
  // google.maps.event.addListener(map, 'bounds_changed', function() {setTimeout(zentriere,1000);});
 // google.maps.event.addListener(map, 'center_changed', function() {setTimeout(zoome,1000);});
 
  directionsDisplay.setMap(map);
  directionsDisplay.setPanel(document.getElementById('directions_panel'));
  
  // Standard-Marker ausblenden
  directionsDisplay.setOptions({suppressMarkers:true});
  
  calcRoute();
 }

function calcRoute() {


  var request = {
    origin:"<? echo $parameter['MAPFFWHAUS']; ?>",
    destination: "<? echo utf8_decode($strasse)." ".utf8_decode($hausnr).", ".utf8_decode($ort); ?>",
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
    <h3>Achtung: Karte unbedingt mit Einsatzort abgleichen und auf Richtigkeit pr&uuml;fen! Die Karte kann nur dann korrekt dargestellt werden, wenn Adresse vollst&auml;ndig von der ILS &uuml;bermittelt wird.<br>
Routenvorschlag f&uuml;r schnellste Route // Achtung: PKW-Route, Durchfahrtsh&ouml;hen, Gewichtsbeschr&auml;nkungen oder tempor&auml;re Sperrungen sind nicht ber&uuml;cksichtigt!</h3>
  <div id="directions_panel"></div>
  <div id="map_canvas"></div>

</body>
</html>

