<?

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2012 Stefan Windele

Version 1.0.0

Dieses Script ist  für die Anzeige der Uhr (bildschirmfüllend) zuständig.

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



// Wir prüfen mal, ob wir denn den FMS Status anzeigen sollen
if ($parameter['FMSSTATUSUHR'] == "true")
{
	echo "<iframe height='75' width='100%' src='fms/status-uhr.php'></iframe>";
	echo "<table width='90%' height='985' align='center'>";
}
else
{
	echo "<table width='90%' height='1060' align='center'>";
}

?>

<tr height="100%">
<td align="center" valign="middle">
<span id="uhr" style="color: <? echo $parameter['UHRFARBE']; ?>; font-size: 2800%; font-weight:bolder; margin:0;"><br />JavaScript aktivieren!</span><br />
<span id="datum" class="datum" style="color: <? echo $parameter['UHRFARBE']; ?>; font-size: 600%; font-weight: bold; margin-bottom:1em;">JavaScript aktivieren!</span><br><br>

<?
// Wir prüfen mal, ob wir einen Hinweistext haben.
if ($parameter['UHRTEXT'] != "")
{
	echo "</td></tr><tr><td valign='middle' bgcolor='".$parameter['UHRTEXTBGCOLOR'].">";
	echo "<span width='100%' style='color:".$parameter['UHRTEXTFGCOLOR']."; font-size: ".$parameter['UHRTEXTHEIGHT']."%; font-weight: bold;'>".$parameter['UHRTEXT']."</span>";


}
?>
</td>
</tr>

</table>


