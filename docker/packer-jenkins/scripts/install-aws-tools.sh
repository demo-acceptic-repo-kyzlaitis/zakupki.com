#!/bin/bash
#set -x

echo -e "\n\nInstalling AWS Tools...\n\n"
#sudo apt-add-repository ppa:awstools-dev/awstools -y
sudo apt-get install -y awscli ec2-api-tools

mkdir ~/.aws
mv /tmp/aws/config ~/.aws/
mv /tmp/aws/credentials ~/.aws/
