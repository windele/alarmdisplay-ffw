#!/bin/sh
## Achtung: User motion und www-run muessen mit dem
## xhost +SI:localuser:motion - CMD berechtigt sein!
xset -display :0 dpms force on 
export DISPLAY=":0"
xdotool key ctrl
