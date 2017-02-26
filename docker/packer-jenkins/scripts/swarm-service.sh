#!/bin/bash

echo "Download swarm client, move it to the right place and set correct permissions"
wget https://repo.jenkins-ci.org/releases/org/jenkins-ci/plugins/swarm-client/2.2/swarm-client-2.2-jar-with-dependencies.jar
sudo mv swarm-client-2.2-jar-with-dependencies.jar /usr/local/bin
sudo chown root:root /usr/local/bin/swarm-client-2.2-jar-with-dependencies.jar
sudo chmod +x /usr/local/bin/swarm-client-2.2-jar-with-dependencies.jar

echo "Move jenkins-slave.sh init script service to correct location and set permissions"
sudo mv /tmp/jenkins-slave.sh /etc/init.d/jenkins-slave
sudo chmod +x /etc/init.d/jenkins-slave
sudo chown root:root /etc/init.d/jenkins-slave
sudo update-rc.d jenkins-slave defaults
sudo update-rc.d jenkins-slave enable

