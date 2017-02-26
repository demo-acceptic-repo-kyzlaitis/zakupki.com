#!/bin/bash

PACKER_VERSION=`git ls-remote -t https://github.com/mitchellh/packer | grep refs/tags | grep -oP "[0-9]+\.[0-9]+\.[0-9]+$"  | sort -b -t . -k 1,1nr -k 2,2nr -k 3,3r -k 4,4r -k 5,5r | uniq | head -n 1`

echo "Installing Packer $PACKER_VERSION version ... "
wget https://releases.hashicorp.com/packer/$PACKER_VERSION/packer_${PACKER_VERSION}_linux_amd64.zip
sudo unzip packer_${PACKER_VERSION}_linux_amd64.zip -d /usr/local/bin/


