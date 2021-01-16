FROM php:apache

# Locale unicode support
RUN apt-get update && apt-get install -y libicu-dev locales sendmail \
	&& sed -i -e 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen \
	&& sed -i -e 's/# it_IT.UTF-8 UTF-8/it_IT.UTF-8 UTF-8/' /etc/locale.gen \
	&& echo 'LANG="en_US.UTF-8"' > /etc/default/locale \
	&& dpkg-reconfigure --frontend=noninteractive locales \
	&& update-locale LANG=en_US.UTF-8 \
	&& docker-php-ext-install gettext

# MySQL driver
RUN docker-php-ext-install pdo_mysql \
	&& docker-php-ext-install mysqli

# Xdebug extension
RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY ./docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Enable URL rewrite module
RUN a2enmod rewrite

# HTTPS
RUN a2enmod ssl
RUN a2ensite default-ssl

# To enable SSL:
# 1) create root key:
# 	openssl genrsa -des3 -out rootCA.key 4096
# 2) create root certificate:
# 	openssl req -x509 -new -nodes -key rootCA.key -sha256 -days 1024 \
#	-out rootCA.crt
# 3) create localhost private key:
#	openssl genrsa -out localhost.key 2048
# 4) create certificate signing request (Common Name MUST be set to "localhost"):
#	openssl req -new -key localhost.key -out localhost.csr
# 5) create localhost certificate:
#	openssl x509 -req -in localhost.csr -CA rootCA.crt -CAkey rootCA.key \
#	-CAcreateserial -out localhost.crt -days 500 -sha256
# 6) copy localhost.{key,crt} inside the {private,certs} folder (create them)
# 7) add rootCA.crt as a trusted authority in your browser/system
# 8) When the container is up, you can access the HTTPS website at:
#	https://localhost:8443
# The browser should not give any warning/error
