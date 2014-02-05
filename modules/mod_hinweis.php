<?php 

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2012 Stefan Windele

Version 1.0.0

Dieses Script stellt das Modul zur Anzeige der Hinweistexte dar.

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



// Wir übergeben an die Datei die Texte.
echo "<input type='hidden' id='zahlhinweistexte' value='".count($hinweistexte)."'>";



for ($i=0; $i<count($hinweistexte); $i++)
{
echo "<input type='hidden' id='text-".($i+1)."' value='".$hinweistexte[$i]."'>";
}



?>



<table width="100%" height="1060" align="center">
<tr height="90%">
<td align="center" valign="middle" style="height:600px; text-align:center;" colspan="3">

<span id="hinweistext" style="color: <?php  echo $parameter['HINWEISSCHRIFT']; ?>; line-height:1.2;font-size: 550%; font-weight: bold; margin-bottom:1em;"><?php  echo $hinweistexte[0];?></span>

</td></tr>

<tr>
<td style='text-align:left;' valign='bottom' width='33%'><span id="datum" style="color: <?php  echo $parameter['UHRFARBE']; ?>; font-size: 300%; font-weight: bold; margin-bottom:1em;">JavaScript aktivieren!</span></td>
<td style='text-align:center;' valign='bottom' width='33%'><span id="fortschritt" style="color: <?php  echo $parameter['UHRFARBE']; ?>; font-size: 300%; font-weight: bold; margin-bottom:1em;"></span></td>
<td style='text-align:right;'  valign='bottom' width='33%'><span id="uhr" style="color: <?php  echo $parameter['UHRFARBE']; ?>; font-size: 300%; font-weight:bold; margin:0;">JavaScript aktivieren!</span></td></tr>


</table>
