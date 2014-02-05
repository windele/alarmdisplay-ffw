<?php 
     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      session_start();

      $username = $_POST['username'];
      $passwort = $_POST['passwort'];

      $hostname = $_SERVER['HTTP_HOST'];
      $path = dirname($_SERVER['PHP_SELF']);

require('../config.inc.php');

// Verbindung zur Datenbank herstellen und an Handle $db übergeben
$db = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME) or die ('Verbindung zur Datenbank fehlgeschlagen.');
$db->set_charset("utf8");

// Benutzername und Passwort abfragen
$adminuser = $db->query("SELECT wert FROM tbl_adm_params WHERE parameter='ADMINUSER'");
$adminpass = $db->query("SELECT wert FROM tbl_adm_params WHERE parameter='ADMINPASS'");

$adminuser = $adminuser->fetch_row();
$adminpass = $adminpass->fetch_row();


      // Benutzername und Passwort werden überprüft
      if ($username == $adminuser[0] && $passwort == $adminpass[0]) {
       $_SESSION['angemeldet'] = true;

$db->close();

       // Weiterleitung zur geschützten Startseite
       if ($_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1') {
        if (php_sapi_name() == 'cgi') {
         header('Status: 303 See Other');
         }
        else {
         header('HTTP/1.1 303 See Other');
         }
        }

       header('Location: http://'.$hostname.($path == '/' ? '' : $path).'/index.php');
       exit;
       }
      }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
 <head>
  <title>Admin-Panel</title>
 </head>
 <body><table align="center" height="100%" width="100%"><tr><td align="center">
  <img src="../images/logo.png"><br>
<h2>Login AdminPanel Alarmdisplay</h2>
  <form action="login.php" method="post">
   Username: <input type="text" name="username" /><br />
   Passwort: <input type="password" name="passwort" /><br />
   <input type="submit" value="Anmelden" />
  </form>
</td></tr></table>
 </body>
</html>
