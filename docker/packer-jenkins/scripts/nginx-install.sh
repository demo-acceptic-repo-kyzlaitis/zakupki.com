#!/bin/bash
#set -x

echo -e "\n\nInstalling Nginx...\n\n"
#sudo apt-add-repository ppa:awstools-dev/awstools -y
sudo apt-get install -y nginx
sudo cp /tmp/jenkins-nginx.conf /etc/nginx/site-available/jenkins
sudo ln -s /etc/nginx/sites-available/jenkins /etc/nginx/sites-enabled
sudo service nginx restart

