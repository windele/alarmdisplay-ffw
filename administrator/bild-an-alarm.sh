#!/bin/sh
## Achtung: User motion und www-run muessen mit dem
## xhost +SI:localuser:motion - CMD berechtigt sein!

# Browser-Name
NAME="firefox"
export DISPLAY=":0"

# Prüfen, ob der Firefox läuft
if ( ps aux | grep "$NAME" | grep -v grep)
then
	echo "$NAME is running..."
	WID=`xdotool search firefox | head -n1`
	xdotool windowactivate $WID

else
echo "$NAME NOT running! Restarting..."
firefox &
echo "$NAME restarted"
fi
xset -display :0 dpms force on 
xdotool key ctrl
