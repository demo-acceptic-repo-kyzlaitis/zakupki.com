FROM nginx:latest

#RUN apt-get update && apt-get install -y mc nano

RUN apt-get update -y
RUN apt-get install -y nginx-module-geoip

RUN usermod -u 1000 www-data

RUN echo "127.0.0.1  zakupki.api" >> /etc/hosts

#CMD ["nginx"]

RUN mkdir -p /etc/service/nginx
ADD start.sh /etc/service/nginx/run
RUN chmod +x /etc/service/nginx/run

RUN mkdir -p /var/www
RUN mkdir -p /var/lib/nginx/cache

RUN mkdir -p /opt/geoip

RUN apt-get install -y wget
RUN wget -P /opt/geoip http://geolite.maxmind.com/download/geoip/database/GeoLiteCountry/GeoIP.dat.gz
RUN gunzip /opt/geoip/GeoIP.dat.gz

RUN wget -P /opt/geoip http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
RUN gunzip /opt/geoip/GeoLiteCity.dat.gz

EXPOSE 80
# EXPOSE 443

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*