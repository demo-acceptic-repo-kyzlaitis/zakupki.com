FROM ubuntu:xenial

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update -y
RUN apt-get dist-upgrade -y

RUN apt-get install software-properties-common -y

RUN apt-get install -y sudo git

RUN apt-get install -y php7.0-mysql \
                      php7.0-common \
                      php7.0-cli \
                      php7.0-dev \
                      php7.0-fpm \
                      php7.0-mcrypt \
                      php7.0-mbstring \
                      php7.0-gd \
                      php7.0-curl \
                      php7.0-xsl \
                      php7.0-json

# install phpredis
RUN git clone https://github.com/phpredis/phpredis.git  \
    && cd phpredis \
    && git checkout php7 \
    && phpize && ./configure \
    && make \
    && make install
    && cd .. \
    && rm -rf phpredis

RUN echo "extension=redis.so" > /etc/php/7.0/mods-available/redis.ini && \
    ln -sf /etc/php/7.0/mods-available/redis.ini /etc/php/7.0/fpm/conf.d/20-redis.ini && \
    ln -sf /etc/php/7.0/mods-available/redis.ini /etc/php/7.0/cli/conf.d/20-redis.ini

ADD application.ini /etc/php/7.0/fpm/conf.d/
ADD application.ini /etc/php/7.0/cli/conf.d/

ADD application.pool.conf /etc/php/7.0/fpm/pool.d/

RUN usermod -u 1000 www-data

RUN mkdir -p /run/php
RUN mkdir -p /etc/service/php7.0-fpm
ADD start.sh /etc/service/php7.0-fpm/run
RUN chmod +x /etc/service/php7.0-fpm/run

RUN apt-get install -y supervisor cron
RUN mkdir -p /etc/supervisor/conf.d/
ADD supervisor.conf /etc/supervisor/conf.d/main.conf

# Add crontab file in the cron directory
ADD cron.conf /etc/cron.d/cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/cron

# Create the log file to be able to run tail
RUN touch /var/log/cron.log

# Run the command on container startup
CMD cron && tail -f /var/log/cron.log

RUN cd /

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \

RUN mkdir -p /var/www

EXPOSE 9000

# Make ssh dir
RUN mkdir -p /var/www/.ssh

# Copy over private key, and set permissions
ADD ssh/id_rsa      /var/www/.ssh/id_rsa
ADD ssh/id_rsa.pub  /var/www/.ssh/id_rsa.pub
RUN chmod 700       /var/www/.ssh
RUN chown -R www-data:www-data /var/www/.ssh

# Create known_hosts
RUN touch /var/www/.ssh/known_hosts

# Remove host checking
RUN echo "Host bitbucket.org\n\tStrictHostKeyChecking no\n" >> /var/www/.ssh/config

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
