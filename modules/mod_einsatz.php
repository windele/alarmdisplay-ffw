<?php

/*
 ALARMDISPLAY FEUERWEHR PIFLAS
 Copyright 2012-2019 Stefan Windele

 Version 2.0.0

 Dieses Script stellt das Einsatzfax auf dem Display dar.

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

// Zu Beginn fragen wir den letzten Einsatz ab und werten ihn aus.
$db -> set_charset("utf8");
$result = $db -> query("SELECT *, UNIX_TIMESTAMP(alarmzeit) AS time FROM tbl_einsaetze ORDER BY id DESC LIMIT 1");
$row = $result -> fetch_array();
$result -> close();

// Erst mal die Lampen der Fahrzeuge zurücksetzen. Dazu basteln wir uns einen Teil der HTML-Tags neu.
$resthtmltag = "\"><h3 style=\"color:" . $parameter['SCHRIFTFARBELAMPE'];
$fahrzeuge = array(
	"RW" => $parameter['FARBEZUHAUSE'] . $resthtmltag, 
	"TLF" => $parameter['FARBEZUHAUSE'] . $resthtmltag, 
	"LF" => $parameter['FARBEZUHAUSE'] . $resthtmltag, 
	"MZF" => $parameter['FARBEZUHAUSE'] . $resthtmltag, 
	"VSA" => $parameter['FARBEZUHAUSE'] . $resthtmltag,
);

// Soll der Text bei Alarm blinken?
if ($parameter['BLINKALARM'] == "true") {
	$resthtmltag = "\"><h3 style=\"animation: blink 1s steps(5, start) infinite;font-weight:bolder;color:" . $parameter['SCHRIFTFARBELAMPE'];
} else {
	$resthtmltag = "\"><h3 style=\"font-weight:bolder;color:" . $parameter['SCHRIFTFARBELAMPE'];
}
$dispoliste = json_decode($row['dispo']);
// Dispoliste prüfen, welche unserer Autos alarmiert wurden und Lampe rot schalten, Text blinken lassen
for ($i = 0; $i < count($dispoliste); $i++) {
	switch (html_entity_decode($dispoliste[$i])) {
		case "Musterstadt 62/1" :
			$fahrzeuge["RW"] = $parameter['FARBEALARM'] . $resthtmltag;
			break;

		case "Musterstadt 21/1" :
			$fahrzeuge["TLF"] = $parameter['FARBEALARM'] . $resthtmltag;
			break;

		case "Musterstadt 47/1" :
			$fahrzeuge["LF"] = $parameter['FARBEALARM'] . $resthtmltag;
			break;

		case "Musterstadt 11/1" :
			$fahrzeuge["MZF"] = $parameter['FARBEALARM'] . $resthtmltag;
			break;

		case "VSA FF Musterstadt" :
			$fahrzeuge["VSA"] = $parameter['FARBEALARM'] . $resthtmltag;
			$fahrzeuge["MZF"] = $parameter['FARBEALARM'] . $resthtmltag;
			break;
	}
}
?>

<div class="container">

<?php  // Absatz mit den blinkenden Lampen - HTML-Tags werden automatisch aus Array $fahrzeuge gesetzt. ?>
<div class="span-20 last">
<table align="center" border="1">
<tr>
<td bgcolor="<?php  echo $fahrzeuge["RW"] ?>">RW<br>62/1</h3></td>
<td bgcolor="<?php  echo $fahrzeuge["TLF"] ?>">TLF<br>21/1</h3></td>
<td bgcolor="<?php  echo $fahrzeuge["LF"] ?>">LF
<br>
47/1</h3></td>
<td bgcolor="<?php  echo $fahrzeuge["MZF"] ?>">MZF
<br>
11/1</h3></td>
<td bgcolor="<?php  echo $fahrzeuge["VSA"] ?>">VSA</h3></td>
</tr>
</table>
</div>
<div class='span-10'>
<div class="error">
<h1>
<?php  // Schlagwort
	echo $row[8];
?>
</h1>
</div>

<div class="error">
<h1>
<?php  // Anschrift, Objekt und Station
	echo $row[3] . " " . $row[4];
	if ($row[5] != "")
		echo "<br />" . $row[5];
	if ($row[6] != "")
		echo "<br /><h2>" . $row[6] . "</h2>";
	if ($row[7] != "")
		echo "<br />" . $row[7];
?>
</h1>
</div>

<div class="notice">
<h2 align="center">
<?php  // Bemerkung
	echo $row[10];
?>
</h2>
</div>

<?php
if ($parameter['EINSATZHINWEIS'] != "") {
	if ($parameter['BLINKALARM'] == "true") {
		$style = "style='animation: blink 1s steps(5, start) infinite;'";
	} else {
		$style = "";
	}
	echo "<div class='error' " . $style . "><h2 align='center'>";
	echo $parameter['EINSATZHINWEIS'];
	echo "</h2></div>";

}
?>

<div class="info">
<legend>Priorit&auml;t:
<?php  // Prio
	echo $row[9] . " &bull; ";
?>Mitteiler:
<?php  // Mitteiler
	echo $row[2]. " &bull; ";
?>
Alarmzeit: 
<?php  // Alarmzeit
	echo date("H:i", $row['time']);
?>
</legend>

<?php
// Wir prüfen mal, ob wir denn den FMS Status anzeigen sollen
if ($parameter['FMSSTATUSALARM'] == "true") {
	echo "<iframe height='32' width='100%' src='fms/status-alarm.php'></iframe>";
}
?>

</div>

<div class="notice"><h1 align="center">
<span id="datum">DATUM</span> - <b><span id="uhr">JavaScript aktivieren!</span></b></h1>
</div>

<div class="success">
<legend><span style="font-size:200%; line-height:1.1;">Alarmiert:&nbsp;
<?php
echo implode(', ',$dispoliste);
?>
</span></legend>
</div>
<div align="center"><img src="images/logo.png"></div>
</div>

<?php

	echo "<div class='span-10 last'>";
	echo "<iframe class='span-10' src='modules/mod_einsatz_inc_maps.php?rw=" . urlencode($row['rw']) . "&hw=" . $row['hw'] . "'  height='500' name='Einsatzort' style='border:1px solid #777;'>Einsatzort</iframe>";
	echo "<iframe class='span-10' src='modules/mod_einsatz_inc_route_zoom.php?rw=" . urlencode($row['rw']) . "&hw=" . $row['hw'] . "' height='500' name='Route' style='border:1px solid #777;'>Route</iframe>";
	echo "</div>";

?>

</div>

