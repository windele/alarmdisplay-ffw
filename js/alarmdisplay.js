// Variablendeklaration
var aktuellerhinweistext = 1;

// Funktion, um die Uhr zum Laufen zu bekommen - Zeigt lokale Systemzeit des Client an!
function time() {
	var now = new Date();
	hours = now.getHours();
	minutes = now.getMinutes();
	seconds = now.getSeconds();
        dayinweek = now.getDay();
	day = now.getDate();
	month = now.getMonth();
	year = now.getFullYear();

	// Zeitstring erstellen
	thetime = (hours < 10) ? "0" + hours + ":" : hours + ":";
	thetime += (minutes < 10) ? "0" + minutes : minutes;
	// thetime += ":";
	// thetime += (seconds < 10) ? "0" + seconds : seconds;

	// Tag-Datum-String erstellen
        var weekday = new Array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
	thedate = weekday[dayinweek] + " - ";
	thedate += (day < 10) ? "0" + day + "." : day + ".";
	month++; 
	thedate += (month < 10) ? "0" + month + "." : month + ".";
	thedate += year;
	
	// Schreiben
	if (uhr = document.getElementById("uhr")) {uhr.innerHTML = thetime;}
	if (datum = document.getElementById("datum"))  {datum.innerHTML = thedate;}

}

// AJAX-Funktion zur Prüfung im Hintergrund, ob das Bild wechseln soll
function checkupdate(bildist)
{
	// In bildist wird uns das angezeigte Bild übergeben
	
	//Updatepruefung mit AJAX
	var request = false;
	request = new XMLHttpRequest();
	if (!request) 
	{ 
	alert("Keine Aktualisierung möglich. AJAX fehlgeschlagen");
	} else {	
	
	// Wir fragen den Server, ob unser aktuell angezeigtes Bild noch passt
	// sendet er "true" zurück, passt es
	// sendet er aber false, müssen wir die ganze Seite aktualisieren

	var url = location.href + "?bildist=" + bildist;
	request.open('GET', url, 'true');
	request.send();
        request.onreadystatechange=function()
 		 {
  			if (request.readyState==4 && request.status==200)
    		     	{
				// Jetzt wirds ernst. True oder False?
    				if (request.responseText=="true")
				{
					// Nichts tun, wir sind aktuell
					return;
				} else {
					// Seite neu laden und somit Display neu aufbauen
					location.reload(true);
				}
   			}
 		 };

	}
	

}




// Hinweistexte darstellen 

function hinweistexte()
{
zahlhinweistexte = parseInt(document.getElementById("zahlhinweistexte").value);

// Prüfen, ob wir noch einen nächsten Text haben oder wieder von vorne beginnen.
if (aktuellerhinweistext < zahlhinweistexte)
	{ aktuellerhinweistext++ } else {aktuellerhinweistext=0;}


if (zahlhinweistexte > 1)
{
	
	// Nächsten Text anzeigen.

	hinweistext.innerHTML = document.getElementById("text-"+aktuellerhinweistext).value;
	

	//  Fortschrittsbummerl machen
	var fortschritt = '';

	for (var i = 1; i <= zahlhinweistexte; i++)
	{
	if (i==aktuellerhinweistext)
		{
		fortschritt = fortschritt + "<span style='color: #F00;'>&bull;&nbsp;</span>";
		} else {
		fortschritt = fortschritt + "<span style='color: #FFF;'>&bull;&nbsp;</span>";
		}
	}		

	document.getElementById("fortschritt").innerHTML = fortschritt;
	
}


}




// Start-Funktion, wird von jeder Seite mit body.onload() aufgerufen
function start(bildist, aktualisierungszeit, hinweisanzeigedauer)
{
	time();
	window.setInterval("time()", 1000);

	// Updatecheck. Wir übergeben dem Updatechecker auch das aktuelle Bild
	window.setInterval("checkupdate('"+bildist+"')", aktualisierungszeit);

	// Wenn wir das Hinweismodul ausführen, müssen wir die Texte aktualisieren.
	if (hinweistext = document.getElementById("hinweistext")) {window.setInterval("hinweistexte()", hinweisanzeigedauer);}
}

