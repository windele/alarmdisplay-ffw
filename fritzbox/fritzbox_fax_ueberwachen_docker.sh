#!/bin/sh

# start apache
apachectl start

# variables
TMPFAX=/tmp/alarm
FAXTIFF=/tmp/alarm/aktuellesfax.tif

# program

echo "Alarmdisplay startet."

# erst mal warten, damit die alten Faxe synchronisiert werden und keinen Alarm auslösen
sleep 5

echo "Beginne mit Ordnerüberwachung..."


watchnames=''
[ -d /var/www/html/alarmdisplay-ffw/fritzbox/faxbox/ ] && watchnames="$watchnames /var/www/html/alarmdisplay-ffw/fritzbox/faxbox/"

inotifywait -mrq -e create --format %w%f $watchnames | while read FILE
do
    echo "Neue Faxdatei $FILE eingetroffen. Wir legen los..."
    sleep 2
    echo "...temporäres Verzeichnis erstellen"
    mkdir -p $TMPFAX

    echo "...Alarmfax drucken."
    # lp -o media=A4 -o fit-to-page $FILE

    echo "...Datei konvertieren"
    # convert -density 400 -depth 8 -monochrome -append $FILE $FAXTIFF
    gs -o $FAXTIFF -sDEVICE=tiffg4 $FILE

    echo "...Fertig. Erkennung starten"
    # cuneiform --singlecolumn --fax -l ger -o $TMPFAX/latest-fax.txt $FILE
    tesseract -l deu --psm 6 $FAXTIFF $TMPFAX/latest-fax

    echo "...Daten an Alarmdisplay übergeben"

    cd /var/www/html/alarmdisplay-ffw/ocr
    php readfile.php $TMPFAX/latest-fax.txt $FAXTIFF

    echo "...aufräumen. Ende."
    rm -rf /media/fritzbox/faxbox/*.pdf
    rm -rf $TMPFAX
    rm $FILE

done
