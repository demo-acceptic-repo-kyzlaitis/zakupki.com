FROM phusion/baseimage

RUN apt-get update && \
    apt-get -y install software-properties-common && \
    apt-get update && \
    add-apt-repository -y ppa:builds/sphinxsearch-beta && \
    apt-get update && \
    apt-get -y install sphinxsearch && \
    mkdir /var/lib/sphinx && \
    mkdir /var/lib/sphinx/data && \
    mkdir /var/log/sphinx && \
    mkdir /var/run/sphinx

ADD indexandsearch.sh /etc/sphinxsearch/
RUN chmod a+x /etc/sphinxsearch/indexandsearch.sh
ADD sphinxy.conf /etc/sphinxsearch/
RUN chmod 766 /etc/sphinxsearch/sphinxy.conf
ADD searchd.sh /
RUN chmod a+x searchd.sh
ADD lordsearchd.sh /
RUN chmod a+x lordsearchd.sh
ADD crontab /etc/cron.d/rotate-cron
RUN chmod 0644 /etc/cron.d/rotate-cron
RUN touch /var/log/cron.log

ADD cron.sh /
RUN chmod a+x cron.sh

CMD cron && tail -f /var/log/cron.log
