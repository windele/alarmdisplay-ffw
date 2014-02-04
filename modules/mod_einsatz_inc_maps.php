<?php 

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2012 Stefan Windele

Version 1.0.0

Dieses Skript stellt den Einsatzort im Detail auf einer Google-Maps-Karte dar. 
Abhängig vom Ort werden verschiedene Zoom-Stufen verwendet.

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
 var geocoder;
 var map;
 var latlng;

  function initialize() {
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(48.55453, 12.162039999999934);
    var myOptions = {
      zoom: <?php  echo $parameter['MAPZOOMSTADT']; ?>,
      center: latlng,
      disableDefaultUI: true,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
    codeAddress();
    zoome(); 
  }

  function codeAddress() {
    // ACHTUNG: Adresse muss UTF-8-codiert von PHP übergeben werden!!!! Sonst Umlaute kaputt.
    var address = "<?php  echo $_GET['strasse']." ".$_GET['hausnr'].", ".$_GET['ort'] ?>";
    geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        map.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
            map: map, 
            position: results[0].geometry.location
        });
      } else {
        // alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }

function zoome() 
{ 
	// Feststellen, ob wir noch in unserem Ortsgebiet sind
	var ortaufkarte = document.getElementById("ortaufkarte").value;
	var ergebnis = ortaufkarte.search(/<?php  echo $parameter['MAPUMFELD']; ?>/i);
	var zoomlevel = <?php  echo $parameter['MAPZOOMSTADT']; ?>;
	if (ergebnis != -1)
	{
		// Wir sind im Ortsgebiet
		zoomlevel = <?php  echo $parameter['MAPZOOMSTADT']; ?>;
	} else {
		// Wir sind auf dem Land
		zoomlevel = <?php  echo $parameter['MAPZOOMLAND']; ?>;
	}
	
	map.setZoom(zoomlevel); 
}

</script>

</head>

<body onload="initialize()">
  <input type="hidden" id="ortaufkarte" value="<?php  echo $_GET['ort']; ?>">
  <div id="map_canvas" style="width:100%; height:100%"></div>

</body>
</html>

