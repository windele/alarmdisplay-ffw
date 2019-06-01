#!/bin/bash
while :
do
	rsync -a --exclude=.* --delete /media/fritzbox/faxbox/ /var/www/html/alarmdisplay-ffw/fritzbox/faxbox/
	sleep 7
done
