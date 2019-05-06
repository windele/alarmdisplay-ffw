#!/bin/bash
while :
do
	rsync -av --delete /media/fritzbox/faxbox/ /var/www/alarmdisplay-ffw/fritzbox/faxbox/
	sleep 7
done
