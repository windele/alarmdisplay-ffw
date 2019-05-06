#!/bin/sh

# variables
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
TMPFAX=/tmp/alarm
FAXTIFF=/tmp/alarm/aktuellesfax.tif

# program

echo "Alarmdisplay startet." | logger -i
echo "Beginne mit Ordnerüberwachung..." | logger -i


watchnames=''
[ -d /var/www/alarmdisplay-ffw/fritzbox/faxbox/ ] && watchnames="$watchnames /var/www/alarmdisplay-ffw/fritzbox/faxbox/"

inotifywait -mrq -e moved_to --format %w%f $watchnames | while read FILE
do
    echo "Neue Faxdatei $FILE eingetroffen. Wir legen los..." | logger -i

	echo "...temporäres Verzeichnis erstellen" | logger -i
	mkdir -p $TMPFAX

	echo "...Datei konvertieren" | logger -i
	convert -density 300 -depth 8 -monochrome -append $FILE $FAXTIFF

	echo "...Fertig. Erkennung starten" | logger -i
	# cuneiform --singlecolumn --fax -l ger -o $TMPFAX/latest-fax.txt $FILE
	tesseract -l ils -psm 6 $FAXTIFF $TMPFAX/latest-fax 

	echo "...Daten an Alarmdisplay übergeben" | logger -i
	
	cd /var/www/alarmdisplay/ocr
	php readfile.php $TMPFAX/latest-fax.txt $FILE

	echo "...Alarmfax drucken." | logger -i
	lpr $FILE


	echo "...aufräumen. Ende." | logger -i
	# rm -rf $TMPFAX


done
