#!/bin/bash

sudo apt-get install -y unzip python

echo "It takes some time for jenkins to come up so we need to look through this a few times"
wget "http://`hostname -f`:8080/jnlpJars/jenkins-cli.jar"
while [ $? -ne 0 ]
do
  echo "Jenkins not up yet. Sleeping for 5 sec"
  sleep 5
  wget "http://`hostname -f`:8080/jnlpJars/jenkins-cli.jar"
done
echo "Jenkins is up! Downloading plugins" 

curl -L https://raw.githubusercontent.com/hgomez/devops-incubator/master/forge-tricks/batch-install-jenkins-plugins.sh -o batch-install-jenkins-plugins.sh
chmod +x batch-install-jenkins-plugins.sh
sudo ./batch-install-jenkins-plugins.sh --plugins /tmp/jenkins_plugins.txt --plugindir /var/lib/jenkins/pluginss

#curl http://`hostname -f`:8080/reload

