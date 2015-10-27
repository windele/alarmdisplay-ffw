<?php 

/*
ALARMDISPLAY FEUERWEHR PIFLAS
Copyright 2012-2014 Stefan Windele

Version 1.0.0

In diesem Script werden die Zugänge zur Datenbank konfiguriert.

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



// Verbindung zur mySQL-Datenbank einstellen

define("DBHOST", "127.0.0.1");		// Der Server
define("DBNAME", "alarmdisplay");	// Datenbankname
define("DBUSER", "benutzer");		// Datenbank-Benutzer - "root" für den Admin
define("DBPASS", "meinpasswort");	// Datenbank-Passwort

