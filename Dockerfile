FROM ubuntu:20.04

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get install --no-install-recommends -y \
    apache2 \
    inotify-tools \
    ghostscript \
    php-common \
    php-cgi \
    php \
    php-curl \
    php-mysql \
    tesseract-ocr \
    tesseract-ocr-deu \
    watchdog \
    wkhtmltopdf \
    xdotool

COPY . /var/www/html/alarmdisplay-ffw

COPY . /var/www/html/alarmdisplay-ffw

COPY configuration/ils.traineddata /usr/share/tesseract-ocr/4.00/tessdata/
COPY configuration/deu.traineddata /usr/share/tesseract-ocr/4.00/tessdata/

RUN mkdir /var/www/html/alarmdisplay-ffw/fritzbox/faxbox/

EXPOSE 80
CMD /var/www/html/alarmdisplay-ffw/fritzbox/fritzbox_fax_ueberwachen_docker.sh
