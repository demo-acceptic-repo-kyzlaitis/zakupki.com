#!/usr/bin/env bash

# For Deb-based distros (Debian / Ubuntu):
add-apt-repository "deb http://archive.canonical.com/ $(lsb_release -sc) partner"
apt-get update
apt-get install -y chromium-browser firefox libgconf-2-4 openjdk-8-jre wget xfonts-{75dpi,100dpi,base,cyrillic,scalable} xvfb
apt-get install -y python-minimal python2.7-dev git gcc libyaml-dev libjpeg-dev libz-dev
apt-get install -y default-jre

# -- This part is for systemd-enabled distros only --
# The command below will create a systemd unit file (you need to copy and paste the whole thing, beginning with `cat` and ending with `__EOF__` 27 lines below
cat > /etc/systemd/system/jenkins-slave.service << __EOF__
[Unit]
Description=Jenkins slave for testing.openprocurement.org
Documentation=http://testing.openprocurement.org/computer/
Wants=network-online.target

[Service]
Type=simple

StandardOutput=journal
StandardError=journal
PrivateTmp=true

Restart=always
RestartSec=1min
KillSignal=SIGINT
SuccessExitStatus=130

User=jenkins-slave
Group=jenkins-slave

WorkingDirectory=/home/jenkins-slave/
EnvironmentFile=/etc/default/jenkins-slave
ExecStart=/usr/bin/java -jar /usr/local/lib/jenkins-slave/slave.jar -jnlpUrl \${JENKINS_URL}/computer/\${SLAVE_NAME}/slave-agent.jnlp -secret \${SLAVE_SECRET}

[Install]
WantedBy=multi-user.target
__EOF__

# Another file that should be created
# For Deb-based distros, `/etc/default` is okay
cat > /etc/default/jenkins-slave << __EOF__
SLAVE_NAME=
SLAVE_SECRET=
JENKINS_URL=http://testing.openprocurement.org
__EOF__

# Make it readable by root only
chmod 600 /etc/default/jenkins-slave
# -- End of systemd part --

# Then, download slave.jar and put it in a place where it can be readable, but not modifiable by unprivileged user, e.g. /usr/local/lib
mkdir -p /usr/local/lib/jenkins-slave && chmod 755 /usr/local/lib/jenkins-slave

# Download slave.jar
cd /usr/local/lib/jenkins-slave
wget http://testing.openprocurement.org/jnlpJars/slave.jar

# Create a new user
useradd -r -l -m -U -s /bin/bash jenkins-slave
# Optionally, specify the nondefault home directory (e.g., /var/local/lib/jenkins-slave) using the following option
# `-d /var/local/lib/jenkins-slave/`

# Go to user's home directory and add a config for zc.buildout that will enable caching
# Remember to replace /home/jenkins-slave with something else if you used `-d` option in the previous step
cd /home/jenkins-slave
mkdir -p .buildout/
cat > .buildout/default.cfg << __EOF__
[buildout]
eggs-directory = /home/jenkins-slave/.buildout/eggs
download-cache = /home/jenkins-slave/.buildout/download-cache
__EOF__

# Fix permissions
chown -R jenkins-slave: .buildout/

# Basically, that's it. Now, tell systemd to reload unit files, enable autostart of the service and try to start it
systemctl daemon-reload
systemctl enable jenkins-slave
systemctl start jenkins-slave