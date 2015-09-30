-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               5.6.14 - MySQL Community Server (GPL)
-- Server Betriebssystem:        Win64
-- HeidiSQL Version:             8.0.0.4396
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Exportiere Datenbank Struktur für alarmdisplay
DROP DATABASE IF EXISTS `alarmdisplay`;
CREATE DATABASE IF NOT EXISTS `alarmdisplay` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `alarmdisplay`;


-- Exportiere Struktur von Tabelle alarmdisplay.tbl_adm_params
DROP TABLE IF EXISTS `tbl_adm_params`;
CREATE TABLE IF NOT EXISTS `tbl_adm_params` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `parameter` varchar(50) DEFAULT NULL,
  `wert` varchar(200) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `tab` int(4) DEFAULT NULL,
  `acc` int(4) DEFAULT NULL,
  `line` int(4) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `beschreibung` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8 COMMENT='Speicherort der Einstellungen für das Alarmdisplay';

-- Exportiere Daten aus Tabelle alarmdisplay.tbl_adm_params: 0 rows
/*!40000 ALTER TABLE `tbl_adm_params` DISABLE KEYS */;
REPLACE INTO `tbl_adm_params` (`id`, `parameter`, `wert`, `type`, `tab`, `acc`, `line`, `title`, `beschreibung`) VALUES
	(1, 'ADMINUSER', 'admin', 'text', 1, 1, 1, 'Admin-User', 'Benutzernamen des Administrators'),
	(2, 'ADMINPASS', 'admin', 'password', 1, 1, 2, 'Admin-Passwort', 'Passwort des Administrators'),
	(3, NULL, NULL, NULL, 1, 1, 0, 'Zugangsdaten', 'Konfigurieren Sie hier Ihre Zugangsdaten zur Administrationsoberfläche.'),
	(4, NULL, NULL, NULL, 1, 0, 0, 'Allgemein', 'Allgemeine Einstellungen'),
	(5, 'AKTUALISIERUNGSZEIT', '10000', 'text', 1, 2, 2, 'Aktualisierungsprüfung in ms', 'Geben Sie hier den Abstand in Millisekunden (ms) an, nachdem das Display nach Aktualisierungen sucht. Standardwert: 10000 = 10 Sekunden'),
	(6, NULL, NULL, NULL, 2, 0, 0, 'Einsatzmodul', 'Hier können Sie alle Daten des Modules "Einsatz" konfigurieren.'),
	(7, NULL, NULL, NULL, 2, 1, 0, 'Zeiten', 'Welche Zeiten gelten für das Einsatzmodul?'),
	(8, 'EINSATZANZEIGEDAUER', '3600', 'text', 2, 1, 1, 'Anzeigedauer Einsatz', 'Geben Sie hier den Zeitraum in Sekunden an, wie lange nach einem Einsatz das Einsatzmodul angezeigt wird. Standardwert: 3.600 = 1 Stunde'),
	(9, NULL, NULL, NULL, 2, 2, 0, 'Farben', 'Hier können Sie Farben und Verhalten der Fahrzeuglampen einstellen.'),
	(10, 'FARBEZUHAUSE', '#DDD', 'text', 2, 2, 1, 'Hintergrundfarbe Normalfall', 'Welche Hintergrundfarbe soll die Fahrzeuglampe haben, wenn kein Alarm anliegt? Angabe in HTML-Notation.'),
	(11, 'FARBEALARM', '#F00', 'text', 2, 2, 2, 'Hintergrundfarbe Alarmfall', 'Welche Hintergrundfarbe haben die Lampen im Alarmfall? Angabe in HTML-Notation.'),
	(12, 'SCHRIFTFARBELAMPE', '#FFF', 'text', 2, 2, 3, 'Schriftfarbe der Fahrzeuglampen', 'Welche Schriftfarbe sollen die Fahrzeuglampen haben? Angabe in HTML-Notation.'),
	(13, 'BLINKALARM', 'true', 'boolean', 2, 2, 4, 'Fahrzeuge / Hinweise blinken?', 'Sollen die Schrift der alarmierten Fahrzeuge sowie der Hinweistext im Alarmfall blinken?'),
	(14, NULL, NULL, NULL, 3, 0, 0, 'Uhrzeitmodul', 'Konfigurieren Sie hier das Verhalten des Uhrzeitmodules. Die Uhrzeit stellt sich nach der Systemzeit des Servers.'),
	(15, NULL, NULL, NULL, 3, 1, 0, 'Farben / Hintergrund', 'Konfigurieren Sie die Farben der Uhrzeitdarstellung.'),
	(16, 'UHRHINTERGRUND', '#000', 'text', 3, 1, 1, 'Hintergrundfarbe Uhr', 'Welche Hintergrundfarbe soll die Uhr haben? Angabe in HTML-Notation.'),
	(17, 'UHRFARBE', '#D0D0D0', 'text', 3, 1, 2, 'Schriftfarbe der Uhr', 'Welche Schriftfarbe sollen die Uhr haben? Angabe in HTML-Notation.'),
	(18, 'MAPZOOMSTADT', '18', 'text', 2, 3, 3, 'Zoomlevel Karte in naher Umgebung', 'Wie groß soll die Karte bei Einsätzen in naher Entfernung vom Feuerwehrhaus (z.B. Stadtgebiet) vergrößtert werden? Angabe in Zahlen.'),
	(19, 'ROUTEZOOMSTADT', '15', 'text', 2, 3, 5, 'Zoomlevel Route in naher Umgebung', 'Wie stark soll die Anfahrtsroute bei Einsätzen in naher Entfernung vom Feuerwehrhaus (z.B. Stadtgebiet) vergrößert werden? Angabe in Zahlen.'),
	(20, 'MAPZOOMLAND', '14', 'text', 2, 3, 4, 'Zoomlevel Karte in weiter Entfernung', 'Wie groß soll die Karte bei Einsätzen in weiter Entfernung vom Feuerwehrhaus (z.B. Landkreisgebiet) vergrößtert werden? Angabe in Zahlen.'),
	(21, 'ROUTEZOOMLAND', '12', 'text', 2, 3, 6, 'Zoomlevel Route bei weiter Anfahrt', 'Wie stark soll die Anfahrtsroute bei Einsätzen in weiter Entfernung vom Feuerwehrhaus (z.B. Landkreisgebiet) vergrößert werden? Angabe in Zahlen.'),
	(22, NULL, NULL, NULL, 2, 3, 0, 'Routenberechnung und Zoomlevel', 'Hier können Sie die Parameter zur Routenberechnung sowie die Vergrößerungsstufen der Einsatzkarten einstellen.'),
	(23, NULL, NULL, NULL, 1, 2, 0, 'Allgemein', 'Allgemeine Einstellungen zum Alarmdisplay'),
	(24, 'NAMEFEUERWEHR', 'Feuerwehr Musterstadt', 'text', 1, 2, 1, 'Name der Feuerwehr', 'Geben Sie hier den Namen Ihrer Feuerwehr ein'),
	(25, NULL, NULL, NULL, 3, 2, 0, 'Hinweistext', 'Hier können Sie einstellen, ob unter der Uhr ein Hinweistext angezeigt werden soll.'),
	(26, 'UHRTEXT', 'Herzlich Willkommen!', 'text', 3, 2, 1, 'Hinweistext', 'Welcher Text soll unter der Uhr stehen? Zum Deaktiveren Feld leer lassen. Max. 200 Zeichen.'),
	(27, 'UHRTEXTBGCOLOR', '#FF0000', 'text', 3, 2, 2, 'Hintergrundfarbe', 'Welche Farbe soll der Hintergrund des Hinweistextes haben? Angabe in HTML-Notation.'),
	(28, 'UHRTEXTFGCOLOR', '#FFF', 'text', 3, 2, 3, 'Schriftfarbe', 'Welche Schriftfarbe soll der Hinweisext haben? Angabe in HTML-Notation.'),
	(29, 'UHRTEXTHEIGHT', '600', 'text', 3, 2, 4, 'Höhe', 'Wie hoch soll der Hinweis sein? Angabe in Zahlen.'),
	(30, NULL, NULL, NULL, 4, 0, 0, 'Hinweistexte', 'Hier können Sie Hinweistexte auf dem Display konfigurieren. Zum Deaktivieren lassen Sie das Textfeld leer. Wenn ein Text konfiguriert ist, hat die Anzeige des Textes Vorrang vor der Uhr.'),
	(31, NULL, NULL, NULL, 4, 1, 0, 'Allgemeines', 'Allgemeine Einstellungen für die Lauftexte'),
	(32, NULL, NULL, NULL, 4, 2, 0, 'Text 1', 'Einstellungen des erstenTextes'),
	(33, NULL, NULL, NULL, 4, 3, 0, 'Text 2', 'Einstellungen des zweiten Textes'),
	(34, NULL, NULL, NULL, 4, 4, 0, 'Text 3', 'Einstellungen des dritten Textes'),
	(35, NULL, NULL, NULL, 4, 5, 0, 'Text 4', 'Einstellungen des vierten Textes'),
	(36, 'HINWEISTEXTTEXT1', '', 'text', 4, 2, 1, 'Text 1', 'Erster Hinweistext, max. 200 Zeichen. Zum Deaktivieren Text löschen.'),
	(37, 'HINWEISTEXTTEXT2', '', 'text', 4, 3, 1, 'Zweiter Hinweistext', 'Text 2, max. 200 Zeichen. Zum Deaktivieren Text löschen.'),
	(38, 'HINWEISTEXTTEXT3', '', 'text', 4, 4, 1, 'Dritter Hinweistext', 'Text 3, max. 200 Zeichen. Zum Deaktivieren Text löschen.'),
	(39, 'HINWEISTEXTTEXT4', '', 'text', 4, 5, 1, 'Vierter Hinweistext', 'Text 4, max. 200 Zeichen. Zum Deaktivieren Text löschen.'),
	(40, 'HINWEISTEXTTEXT5', '', 'text', 4, 6, 1, 'Fünfter Hinweistext', 'Text 5, max. 200 Zeichen. Zum Deaktivieren Text löschen.'),
	(41, NULL, NULL, NULL, 4, 6, 0, 'Text 5', 'Einstellungen des fünften Textes'),
	(42, 'HINWEISTEXTVON1', '2013-09-01', 'date', 4, 2, 2, 'Anzeigen ab', 'Ab wann soll der Text angezeigt werden? Eingabe in folgendem Muster:  "2012-12-01 12:00" -> YYYY-MM-DD HH-MM'),
	(43, 'HINWEISTEXTVON2', '2013-09-01', 'date', 4, 3, 2, 'Anzeigen ab', 'Ab wann soll der Text angezeigt werden? Eingabe in folgendem Muster:  "2012-12-01 12:00" -> YYYY-MM-DD HH-MM'),
	(44, 'HINWEISTEXTVON3', '2013-09-01', 'date', 4, 4, 2, 'Anzeigen ab', 'Ab wann soll der Text angezeigt werden? Eingabe in folgendem Muster:  "2012-12-01 12:00" -> YYYY-MM-DD HH-MM'),
	(45, 'HINWEISTEXTVON4', '2013-09-01', 'date', 4, 5, 2, 'Anzeigen ab', 'Ab wann soll der Text angezeigt werden? Eingabe in folgendem Muster:  ""2012-12-01 12:00" -> YYYY-MM-DD HH-MM'),
	(46, 'HINWEISTEXTVON5', '2013-09-01', 'date', 4, 6, 2, 'Anzeigen ab', 'Ab wann soll der Text angezeigt werden? Eingabe in folgendem Muster:  "2012-12-01 12:00" -> YYYY-MM-DD HH-MM'),
	(47, 'MAILSCREENSHOT', 'false', 'boolean', 2, 6, 2, 'Sreenshot in Mail einfügen', 'Soll der E-Mail ein Screenshot als Bildanhang beigefügt werden?'),
	(48, 'SMTPSENDER', 'Feuerwehr Musterstadt', 'text', 2, 6, 4, 'Absender der E-Mail', 'Geben Sie hier den Absendernamen der E-Mail ein. '),
	(49, 'HINWEISAKTUALISIERUNG', '10000', 'text', 4, 1, 3, 'Anzeigezeit Hinweise', 'Bei Anzeige von mehr als einem Hinweistext scrollen diese durch. Wie lange sollen diese angezeigt werden? Angabe in ms.'),
	(50, 'HINWEISSCHRIFT', '#FFF', 'text', 4, 1, 2, 'Schriftfarbe Hinweismodul', 'Welche Schriftfarbe Hinweismodul haben? Angabe in HTML-Notation'),
	(51, 'HINWEISHINTERGRUND', '#000', 'text', 4, 1, 1, 'Hintergrundfarbe Hinweistexte', 'Welche Hintergrundfarbe soll das Hinweismodul haben? Angabe in HTML-Notation'),
	(52, 'HINWEISTEXTBIS1', '2014-11-11 16:45', 'date', 4, 2, 3, 'Anzeigen bis', 'Bis wann soll der Text angezeigt werden? Eingabe in folgendem Muster:  "2012-12-01 12:00" -> YYYY-MM-DD HH-MM'),
	(53, 'HINWEISTEXTBIS2', '2014-11-11 18:30', 'date', 4, 3, 3, 'Anzeigen bis', 'Bis wann soll der Text angezeigt werden? Eingabe in folgendem Muster:  "2012-12-01 12:00" -> YYYY-MM-DD HH-MM'),
	(54, 'HINWEISTEXTBIS3', '2014-11-11 19:15', 'date', 4, 4, 3, 'Anzeigen bis', 'Bis wann soll der Text angezeigt werden? Eingabe in folgendem Muster:  "2012-12-01 12:00" -> YYYY-MM-DD HH-MM'),
	(55, 'HINWEISTEXTBIS4', '2014-10-01 00:00', 'date', 4, 5, 3, 'Anzeigen bis', 'Bis wann soll der Text angezeigt werden? Eingabe in folgendem Muster: "2012-12-01 12:00" -> YYYY-MM-DD HH-MM'),
	(56, 'HINWEISTEXTBIS5', '2014-10-01 00:00', 'date', 4, 6, 3, 'Anzeigen bis', 'Bis wann soll der Text angezeigt werden? Eingabe in folgendem Muster:"2012-12-01 12:00" -> YYYY-MM-DD HH-MM'),
	(57, NULL, NULL, NULL, 2, 4, 0, 'Hinweistext', 'Hier können Sie einen Hinweistext hinterlegen, der auf dem Einsatzmodul dargestellt wird.'),
	(58, 'EINSATZHINWEIS', '', 'text', 2, 4, 1, 'Hinweistext Einsatzmodul', 'Welcher Hinweistext soll auf dem Einsatzmodul dargestellt werden? Deaktivieren durch löschen.'),
	(59, NULL, NULL, NULL, 2, 5, 0, 'SMS-Funktion', 'Das Alarmdisplay kann bei Faxeingang SMS verschicken. Einzelheiten dazu können hier eingestellt werden.'),
	(60, 'SMSENABLED', 'true', 'boolean', 2, 5, 1, 'SMS-Funktion aktiv? ', 'Soll die SMS-Funktion aktiviert sein? Die SMS-Funktion wird über den Dienstleister Rettalarm abgewickelt und benötigt dort eine Registrierung. Durch den SMS-Versand entstehen Kosten; eine Abrechnung erfolgt durch Rettalarm.'),
	(61, 'SMSUSER', 'feuerwehr.musterstadt', 'text', 2, 5, 3, 'Username', 'Zugang für den SMS-Provider'),
	(62, 'SMSPASS', 'alarm!sirenegeht', 'password', 2, 5, 4, 'Passwort', 'Zugang für den SMS-Provider'),
	(63, 'SMSFLASH', 'true', 'boolean', 2, 5, 5, 'Flash-SMS', 'Soll die SMS direkt auf den Displays der User angezeigt werden? Sog. Flash-SMS? Dies ist nur bei den Alarm-SMS möglich.'),
	(64, NULL, NULL, NULL, 2, 6, 0, 'E-Mail-Funktion', 'Bei Faxeingang kann das Display eine E-Mail an die konfigurierten User versenden. '),
	(65, 'MAILENABLED', 'false', 'boolean', 2, 6, 1, 'Mailfunktion aktiv?', 'Soll die Mailfunktion eingeschalten sein? Alle in der Usertabelle eingetragenen Empfänger werden bei Faxeingang benachrichtigt. Zur Funktion ist die Angabe eines korrekten Zugangs zu einem SMTP-Server notwendig.'),
	(66, 'SMTPSERVER', 'smtp.musterserver.de', 'text', 2, 6, 6, 'SMTP-Server', 'Geben Sie hier Ihren SMTP-Server an, über den die Mail verschickt wird.'),
	(67, 'SMTPUSER', 'alarm@feuerwehr-musterstadt.de', 'text', 2, 6, 7, 'Username für SMTP', 'Geben Sie hier den Usernamen zur Anmeldung am SMTP-Server ein.'),
	(68, 'SMTPPASS', 'alarm!sirenegeht', 'password', 2, 6, 8, 'Passwort für SMTP', 'Geben Sie hier Ihr Passwort für den SMTP-Server ein.'),
	(69, 'SMTPSENDERMAIL', 'alarm@feuerwehr-musterstadt.de', 'text', 2, 6, 5, 'Absendermail-Adresse', 'Von welcher E-Mail-Adresse soll die Mail abgesendet werden? Diese Adresse erhält ebenfalls automatisch eine Kopie der Alarm-E-Mail.'),
	(70, 'SMSALARMGRUPPEN', '1', 'text', 2, 5, 2, 'Per SMS zu alarmierende Gruppen/Listen', 'Tragen Sie hier die Gruppen und Listen ein, die über das SMS-System zu alarmieren sind. Für Liste 1 tragen Sie nur die 1 ein, für eine Gruppe setzen Sie ein \'+\' voran. Listen werden mit \'-\' getrennt, Gruppen mit \'+\'. Befragen Sie bei Unsicherheiten den Adminstrator oder das Rettalarm-Handbuch.'),
	(71, 'EIGENESLOGO', 'false', 'boolean', 1, 2, 3, 'Eigenes Logo / Hintergrundbild', 'Sollen die Dialoge zum Uploaden von einem eigenen Logo bzw. einem Hintergrundbild im Admin-Menü angezeigt werden?'),
	(72, NULL, NULL, NULL, 2, 7, 0, 'Druckfunktion', 'Der Inhalt des Alarmdisplays kann auch auf einem Drucker ausgegeben werden.'),
	(73, 'DRUCKENSCREEN', 'false', 'boolean', 2, 7, 1, 'Druckfunktion aktiv?', 'Soll der Inhalt des Bildschirmes auf dem installierten Standarddrucker ausgegeben werden?'),
	(74, 'DRUCKENSCREENWIEOFT', '3', 'text', 2, 7, 2, 'Wieviele Exemplare?', 'Wieviele Exemplare sollen gedruckt werden?'),
	(75, 'DRUCKENFAX', 'false', 'boolean', 2, 7, 4, 'Originalfax auch ausdrucken?', 'Soll das Originalfax  ausgedruckt werden? Funktion unabhängig vom Ausdruck des Screenshots.'),
	(76, 'DRUCKENFAXWIEOFT', '1', 'text', 2, 7, 5, 'Exemplare Originalfax?', 'Wieviele Exemplare des Originalfaxes sollen gedruckt werden?'),
	(77, 'FMSSTATUSUHR', 'false', 'boolean', 3, 3, 1, 'FMS-Status anzeigen?', 'Soll der FMS-Status bei Anzeige der Uhrzeit für die eigenen Fahrzeuge angezeigt werden?'),
	(78, 'DRUCKENANFAHRT', 'true', 'boolean', 2, 7, 3, 'Anfahrtsplan an Screenhot anhängen?', 'Soll dem Screenshot-Ausdruck ein Google-Anfahrtsplan beigefügt werden? (nicht möglich auf Autobahn!)'),
	(79, NULL, NULL, NULL, 2, 8, 0, 'FMS-Status', 'Hier kann eingestellt werden, ob der FMS-Status im Alarmmodul angezeigt werden soll. Die Anzeige setzt ein System voraus, dass den Status in die  Datenbank speichern kann. Sprechen Sie hierzu mit dem Administrator.'),
	(80, 'FMSSTATUSALARM', 'false', 'boolean', 2, 8, 1, 'FMS-Status anzeigen?', 'Soll der FMS-Status im Einsatzmodul für die eigenen Fahrzeuge angezeigt werden?'),
	(81, NULL, NULL, NULL, 3, 3, 0, 'FMS-Status', 'Hier kann eingestellt werden, ob der FMS-Status bei der Uhrzeitanzeige angezeigt werden soll. Die Anzeige setzt ein System voraus, dass den Status in die  Datenbank speichern kann. Sprechen Sie hierzu mit dem Administrator.'),
	(82, 'HINWEISBACKGROUND', 'true', 'boolean', 4, 1, 4, 'Hintergrundbild anzeigen?', 'Soll das hochgeladene Hintergrundbild im Hinweismodul verwendet werden?'),
	(83, 'UHRBACKGROUND', 'false', 'boolean', 3, 1, 3, 'Hintergrundbild anzeigen?', 'Soll das hochgeladene Hintergrundbild im Uhrzeitmodul verwendet werden?'),
	(84, 'SMTPPORT', '587', 'text', 2, 6, 9, 'SMTP-Port Ihres Mail-Servers', 'Geben Sie hier den Port Ihres Mail-Servers ein, z.B. \'587\' für TLS'),
	(85, 'SMTPSECURE', 'tls', 'text', 2, 6, 10, 'E-Mail-Sicherheit', 'Geben Sie hier die Sicherheitsstufe für den SMTP-Server ein: Möglich Eingaben sind \'\' (leer für keine), \'ssl\' und \'tls\''),
	(86, 'MAPUMFELD', 'Ergolding|Landshut|Piflas', 'text', 2, 3, 2, 'Ortskonfiguration', 'Konfigurieren Sie hier das Stadtgebiet bzw. die Orte rund um Ihr Feuerwehrhaus. Das Display passt für diese Orte die Zoomstufen der Kartendarstellung besonders an. Mehrere Orte trennen Sie durch das Pipe-Zeichen (\'|\'),'),
	(87, 'MAPFFWHAUS', 'Gartenstrasse 1, 84030 Ergolding', 'text', 2, 3, 1, 'Anschrift Feuerwehrhaus', 'Diese Anschrift wird für die Routenberechnung verwendet. Tipp: Straßennamen mit Doppel-S schreiben. Angabe: Strasse Hausnr, PLZ Ort ');
  (47, 'MAILFAX', 'false', 'boolean', 2, 6, 3, 'Alarmfax in Mail einfügen', 'Soll der E-Mail das Alarmfax als Bildanhang beigefügt werden?'),
	/*!40000 ALTER TABLE `tbl_adm_params` ENABLE KEYS */;


-- Exportiere Struktur von Tabelle alarmdisplay.tbl_alarm_user
DROP TABLE IF EXISTS `tbl_alarm_user`;
CREATE TABLE IF NOT EXISTS `tbl_alarm_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(60) NOT NULL,
  `email` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Exportiere Daten aus Tabelle alarmdisplay.tbl_alarm_user: 0 rows
/*!40000 ALTER TABLE `tbl_alarm_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_alarm_user` ENABLE KEYS */;


-- Exportiere Struktur von Tabelle alarmdisplay.tbl_einsaetze
DROP TABLE IF EXISTS `tbl_einsaetze`;
CREATE TABLE IF NOT EXISTS `tbl_einsaetze` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alarmzeit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `mitteiler` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `strasse` varchar(65) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hausnr` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ort` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  `objekt` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `station` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schlagw` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `prio` int(2) DEFAULT NULL,
  `bemerkung` varchar(400) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dispo` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Tabelle für Einsatzdaten Alarmdisplay Feuerwehr Piflas';

-- Exportiere Daten aus Tabelle alarmdisplay.tbl_einsaetze: 0 rows
/*!40000 ALTER TABLE `tbl_einsaetze` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_einsaetze` ENABLE KEYS */;


-- Exportiere Struktur von Tabelle alarmdisplay.tbl_fms
DROP TABLE IF EXISTS `tbl_fms`;
CREATE TABLE IF NOT EXISTS `tbl_fms` (
  `fzgid` int(11) NOT NULL AUTO_INCREMENT,
  `fzgname` varchar(30) NOT NULL,
  `fzgstatus` int(11) NOT NULL,
  `lststatus` varchar(2) NOT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `aenderungdurch` varchar(3) NOT NULL,
  PRIMARY KEY (`fzgid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='FMS-Statustabelle';

-- Exportiere Daten aus Tabelle alarmdisplay.tbl_fms: 4 rows
/*!40000 ALTER TABLE `tbl_fms` DISABLE KEYS */;
REPLACE INTO `tbl_fms` (`fzgid`, `fzgname`, `fzgstatus`, `lststatus`, `datum`, `aenderungdurch`) VALUES
	(1, 'RW 1', 2, '', '2013-12-22 06:26:13', 'Fzg'),
	(2, 'TLF 16/25', 2, '', '2013-12-22 06:23:52', 'Fzg'),
	(3, 'LF 8/6', 2, '', '2013-12-21 21:52:53', 'Fzg'),
	(4, 'MZF', 2, '', '2013-12-03 16:55:38', 'Fzg');
/*!40000 ALTER TABLE `tbl_fms` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
