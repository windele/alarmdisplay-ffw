<?php  

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2012 Stefan Windele

Version 0.1.1

Dieses Script liest die von der Texterkennung übergebene Textdatei ein, 
zerlegt die Struktur und speichert diese in die Datenbank.


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


require('auth.php');
require('../config.inc.php');





// Verbindung zur Datenbank herstellen und an Handle $db übergeben
$db = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME) or die ('Verbindung zur Datenbank fehlgeschlagen.');
$db->set_charset("utf8");

// Header an den Client senden
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Datum in der Vergangenheit
	header("Content-type: text/html; charset=utf-8"); // Hier kommt Text
	
// Kümmern wir uns mal um ein bisschen HTML.

 ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
 <head>
  <title>Administration Alarmdisplay</title>
 <link type="text/css" href="../css/screen.css" rel="stylesheet" />
 <link type="text/css" href="../css/blitzer/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../js/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript">
			$(function(){

				// Accordion
				$("#accordion").accordion({ header: "h3" });

				// Tabs
				$('#tabs').tabs();

				// Dialog
				$('#dialog').dialog({
					autoOpen: false,
					width: 600,
					buttons: {
						"Ok": function() {
							$(this).dialog("close");
						},
						"Cancel": function() {
							$(this).dialog("close");
						}
					}
				});

				// Dialog Link
				$('#dialog_link').click(function(){
					$('#dialog').dialog('open');
					return false;
				});

				// Datepicker
				$('#datepicker').datepicker({
					inline: true
				});

				// Slider
				$('#slider').slider({
					range: true,
					values: [17, 67]
				});

				// Progressbar
				$("#progressbar").progressbar({
					value: 20
				});

				//hover states on the static widgets
				$('#dialog_link, ul#icons li').hover(
					function() { $(this).addClass('ui-state-hover'); },
					function() { $(this).removeClass('ui-state-hover'); }
				);



			});

jQuery(function(jQuery)  
    {  
     jQuery.datepicker.regional['de'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',  
                closeText: 'schließen', closeStatus: 'ohne änderungen schließen',  
                prevText: '<zurück', prevStatus: 'letzten Monat zeigen',  
                nextText: 'Vor>', nextStatus: 'nächsten Monat zeigen',  
                currentText: 'heute', currentStatus: '',  
                monthNames: ['Januar','Februar','März','April','Mai','Juni',  
                'Juli','August','September','Oktober','November','Dezember'],  
                monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',  
                'Jul','Aug','Sep','Okt','Nov','Dez'],  
                monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',  
                weekHeader: 'Wo', weekStatus: 'Woche des Monats',  
                dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],  
                dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],  
                dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],  
                dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',  
                dateFormat: 'dd.mm.yy', firstDay: 1,   
                initStatus: 'Wähle ein Datum', isRTL: false};  
     jQuery.datepicker.setDefaults(jQuery.datepicker.regional['de']);  
    });  


		</script>

</head>



<?php 

// Wird das Formular zum Speichern aufgerufen?
if (isset($_POST['submit']))
{
	// Ja, wir bekommen Daten. Die müssen wir an die Datenbank melden.

	foreach ($_POST as $k=>$v)
	{
	if ($k != "submit") 
	{
	$result=$db->query("UPDATE tbl_adm_params SET wert = '".$v."' WHERE parameter = '".$k."'");
	}  
	}


	echo "<body onload='javascript:alert(\"Daten gespeichert!\")'>";
	
} else {
	// nur Body ausgeben
	echo "<body>";

}

// Erste Datenbank-Abfrage - Wie heisst unser Laden eigentlich?

$nameff = $db->query("SELECT wert FROM tbl_adm_params WHERE parameter = 'NAMEFEUERWEHR'");
$nameff = $nameff->fetch_row();
$nameff = $nameff[0];


// Aber jetzt gehts los mit dem Dokument.

echo "<div><img src='../images/logo.png' align='right'><br /><a href='logout.php'>Logout</a><br><h2>Administration Alarmdisplay ".$nameff."</h2></div>";

echo "";

echo "<form action='http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."' method='POST'>";
echo "<div id='tabs'>\n\n<ul>";


// Abruf aus der Datenbank, wie die Tabs heissen.
$tabs = $db->query("SELECT tab, title FROM tbl_adm_params WHERE (acc=0 AND line=0) GROUP BY tab");

// Schreiben der Tabinfos
while ($row = $tabs->fetch_row())
{
	echo "<li><a href='#tabs-".$row[0]."'>".$row[1]."</a></li>\n";
}


echo "</ul>\n\n";

// Nochmals Abruf aus der Datenbank, wie die Tabs heissen inkl. Beschreibung.
$tabs = $db->query("SELECT tab, title, beschreibung FROM tbl_adm_params WHERE (acc=0 AND line=0) GROUP BY tab");

// Wir basteln die Inhalte der Tabs.
while ($row = $tabs->fetch_row())
{
	echo "<div id='tabs-".$row[0]."'>\n";
	echo $row[2]."<br /><br />\n";
	
	// Inhalts-Daten des Tabs abfragen.
	$accordion = $db->query("SELECT acc, title, beschreibung FROM tbl_adm_params WHERE tab=".$row[0]." AND line=0 AND acc>0 ORDER BY title");
	
	while ($row2 = $accordion->fetch_row())
	{
		echo "<fieldset style='background-color: #FFCC99;'>";
		echo "<legend>".$row2[1]."</legend>\n".$row2[2]."<br><br><div>\n";

	
		// Zugehörige Lines abfragen
		$lines = $db->query("SELECT parameter, wert, type, title, beschreibung FROM tbl_adm_params WHERE (tab=".$row[0]." AND acc=".$row2[0]." AND line>0) ORDER BY line");
		
		while ($row3 = $lines->fetch_row())
		{

		// Ausgabe der einzelnen Datenfelder. Jetzt müssen wir den Datentyp unterscheiden.


		switch ($row3[2])
		{
			case "text":
			echo "<b>".$row3[4]."</b><br>".$row3[3].": <input type='text' name='".$row3[0]."' value='".$row3[1]."' maxlength='200' /><br><br>";
			break;

			case "password":
			echo "<b>".$row3[4]."</b><br>".$row3[3].": <input type='password' name='".$row3[0]."' value='".$row3[1]."' /><br><br>";
			break;

			case "date":
			echo "<b>".$row3[4]."</b><br>".$row3[3].": <input type='text' name='".$row3[0]."' value='".$row3[1]."' maxlength='16'/><br><br>";
			break;

			case "boolean":
			echo "<b>".$row3[4]."</b><br>".$row3[3].": ";

			if ($row3[1]=="true")
			{ echo "<input type='radio' name='".$row3[0]."' value='true' checked> ja  "; 
			} else {
			echo "<input type='radio' name='".$row3[0]."' value='true'> ja   "; }

			if ($row3[1]=="false")
			{ echo "<input type='radio' name='".$row3[0]."' value='false' checked> nein<br><br><br>"; 
			} else {
			echo "<input type='radio' name='".$row3[0]."' value='false'> nein<br><br><br>"; }



			//echo $row3[4]."<br>".$row3[3].": <input type='text' name='".$row3[0]."' value='".$row3[1]."' maxlength='5'/><br><br>";

			
			break;



		}
		
	
		}

		$lines->close();
		echo "</div></fieldset><br />\n";
	}

	$accordion->close();

	// Userverwaltung auf den ersten Tab bringen.

	if ($row[0]==1)
	{echo "<fieldset>
	<legend>User für E-Mail-Benachrichtigung einstellen</legend>
	Falls die E-Mail-Funktion aktiviert ist, wird bei Eingang eines Alarmfaxes an jede eingetragene Adresse eine E-Mail geschickt.<br/>
	<iframe width='90%' height='250' src='useredit.php'></iframe>
	</fieldset>";
	
	




	}

	echo "</div>\n";


}
$tabs->close();




?>

<div align="center">	 
	<input type="submit" name="submit" value="Speichern"/>&nbsp;<input type="reset" value=" Abbrechen">
</div>

</div>
</form><br /><br />


<?php 

// Sollen wir die Upload-Funktion für Bilder anzeigen? Lasst uns nachsehen.
$result = $db->query("SELECT wert FROM tbl_adm_params WHERE parameter='EIGENESLOGO'");
$row = $result->fetch_row();

if ($row[0]=="true") {
echo "<div align='center'><hr>	
<h3>Individuelles Logo für das Alarmdisplay</h3><br>
Hier können Sie eine Grafik hochladen, die dann auf dem Alarmdisplay angezeigt wird. Aktuell ist die Grafik, die auf dieser Seite rechts oben angezeigt wird. <br><br>
<form enctype='multipart/form-data' action='logo-upload.php' method='POST'>
<input type='hidden' name='MAX_FILE_SIZE' value='30000'>
(max. 200x300px, erlaubtes Format: PNG).<br>Bitte speichern Sie vorher Ihre Einstellungen, da sie sonst verloren gehen. <input name='datei' type='file'>
<input type='submit' value='Hochladen'></form></div><br><br>";
echo "<div align='center'><hr>	
<h3>Hintergrundbild für Uhrzeit- und Hinweistext-Modul</h3><br>
Hier können Sie eine Grafik hochladen, die dann auf dem Alarmdisplay im Uhrzeit- bzw. Hinweismodul angezeigt wird. <br><br>
<form enctype='multipart/form-data' action='background-upload.php' method='POST'>
<input type='hidden' name='MAX_FILE_SIZE' value='10000000'>
(erlaubtes Format: JPG).<br>Bitte speichern Sie vorher Ihre Einstellungen, da sie sonst verloren gehen. <input name='datei' type='file'>
<input type='submit' value='Hochladen'></form></div><br><br>";
}


$db->close();
?>



<br><br>
Programmiert 2012 von <a href="mailto:Stefan Windele">Stefan Windele</a> für <a href="http://www.feuerwehr-piflas.de" target="_new">Freiwillige Feuerwehr Piflas</a>.<br><br>



</body>
</html>
