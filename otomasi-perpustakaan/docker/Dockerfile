# PHP 8.4.27 = versi yang sudah menambal PHP2Shell (CVE-2026-109618 & CVE-2026-109619).
# JANGAN turunkan versinya. Kalau ada rilis keamanan baru, naikkan angka ini.
FROM php:8.4.27-apache

# Ekstensi mysqli untuk koneksi ke MySQL + modul Apache untuk header keamanan
RUN docker-php-ext-install mysqli \
 && a2enmod headers \
 && printf '<Directory /var/www/html>\n    AllowOverride All\n    Options -Indexes\n</Directory>\nServerTokens Prod\nServerSignature Off\n' \
      > /etc/apache2/conf-available/zz-perpustakaan.conf \
 && a2enconf zz-perpustakaan

COPY php.ini /usr/local/etc/php/conf.d/zz-perpustakaan.ini
