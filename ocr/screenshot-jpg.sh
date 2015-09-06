#!/bin/sh
export DISPLAY=:0
wkhtmltoimage --height 1280 --width 1920 --javascript-delay 12500 --quality 100 http://localhost/alarmdisplay /tmp/alarm/screenshot.jpg
convert /tmp/alarm/screenshot.jpg -resize 50% /tmp/alarm/screenmail.jpg
chmod 644 /tmp/screen*
