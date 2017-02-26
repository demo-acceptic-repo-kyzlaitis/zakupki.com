FROM ubuntu:14.04.4

ENV DEBIAN_FRONTEND=noninteractive

CMD ["/sbin/my_init"]

RUN apt-get update -y
RUN apt-get dist-upgrade -y

RUN apt-get install sudo git software-properties-common -y

RUN apt-get install -y php5-redis \
                        php5-mysql \
                        php5-common \
                        php5-cli \
                        php5-dev \
                        php5-fpm \
                        php5-mcrypt \
                        php5-gd \
                        php5-curl \
                        php5-memcache \
                        php5-xdebug \
                        php5-geoip \
                        php5-xsl \
                        php5-json && \

                        php --version && \
                        php -m

ADD application.ini /etc/php5/fpm/conf.d/
ADD application.ini /etc/php5/cli/conf.d/

RUN rm /etc/php5/fpm/php.ini
ADD php.ini /etc/php5/fpm/php.ini

ADD application.pool.conf /etc/php5/fpm/pool.d/

RUN usermod -u 1000 www-data

RUN mkdir -p /etc/service/php5-fpm
ADD start.sh /etc/service/php5-fpm/run
RUN chmod +x /etc/service/php5-fpm/run

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

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

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

EXPOSE 80

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
