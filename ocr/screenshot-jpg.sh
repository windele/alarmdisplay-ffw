#!/bin/sh
export DISPLAY=:0
wkhtmltoimage --height 1280 --width 1920 --javascript-delay 12500 --quality 100 http://localhost/alarmdisplay /tmp/screenshot.jpg
convert /tmp/screenshot.jpg -resize 50% /tmp/screenmail.jpg
chmod 755 /tmp/screen*
