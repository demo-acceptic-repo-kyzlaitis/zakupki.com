#!/bin/bash
#set -x

# Export version variables.
COMPOSE_VERSION=`git ls-remote https://github.com/docker/compose | grep refs/tags | grep -oP "[0-9]+\.[0-9]+\.[0-9]+$"  | sort -b -t . -k 1,1nr -k 2,2nr -k 3,3r -k 4,4r -k 5,5r | uniq | head -n 1`

# Timeout for the agent start.
# Increase the timeout if the script does not get to `Bamboo agent 'Elastic Agent on <ec2-instance-id>' ready to receive builds.`.
AGENT_TIMEOUT="3m"

echo -e "\n\n\Installing other packages...\n\n"
sudo apt-get install -y mc unzip htop nodejs npm

echo -e "\n\nInstalling Docker, Docker Compose...\n\n"
curl -sSL https://get.docker.com/ | sh
sudo sh -c "curl -L https://github.com/docker/compose/releases/download/${COMPOSE_VERSION}/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose"
sudo chmod +x /usr/local/bin/docker-compose
sudo sh -c "curl -L https://raw.githubusercontent.com/docker/compose/${COMPOSE_VERSION}/contrib/completion/bash/docker-compose > /etc/bash_completion.d/docker-compose"

sudo docker version
sudo docker-compose --version

echo -e "\n\nConfiguring users..\n\n"
sudo useradd -m jenkins --shell /bin/bash
sudo usermod -aG docker ubuntu
#sudo usermod -aG docker jenkins