#!/bin/sh

# variables
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
TMPFAX=/tmp/alarm
FAXTIFF=/tmp/alarm/aktuellesfax.tif

# program

echo "Alarmdisplay startet." | logger -i

# erst mal warten, damit die alten Faxe synchronisiert werden und keinen Alarm auslösen
sleep 25

echo "Beginne mit Ordnerüberwachung..." | logger -i


watchnames=''
[ -d /var/www/html/alarmdisplay-ffw/fritzbox/faxbox/ ] && watchnames="$watchnames /var/www/html/alarmdisplay-ffw/fritzbox/faxbox/"

inotifywait -mrq -e moved_to --format %w%f $watchnames | while read FILE
do
    echo "Neue Faxdatei $FILE eingetroffen. Wir legen los..." | logger -i

	echo "...temporäres Verzeichnis erstellen" | logger -i
	mkdir -p $TMPFAX

	echo "...Datei konvertieren" | logger -i
	convert -density 400 -depth 8 -monochrome -append $FILE $FAXTIFF

	echo "...Fertig. Erkennung starten" | logger -i
	# cuneiform --singlecolumn --fax -l ger -o $TMPFAX/latest-fax.txt $FILE
	tesseract -l deu -psm 6 $FAXTIFF $TMPFAX/latest-fax 

	echo "...Daten an Alarmdisplay übergeben" | logger -i
	
	cd /var/www/html/alarmdisplay-ffw/ocr
	php readfile.php $TMPFAX/latest-fax.txt $FAXTIFF

	echo "...Alarmfax drucken." | logger -i
	# lpr $FILE


	echo "...aufräumen. Ende." | logger -i
	# rm -rf $TMPFAX


done
