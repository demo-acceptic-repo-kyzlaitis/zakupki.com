
AWS_CONFIG=aws_config.json
PACKER=packer
#PFLAGS=-debug -var-file=$(AWS_CONFIG)
PFLAGS=-var-file=$(AWS_CONFIG)

#SOURCE = ubuntu-jenkins-ami.json
SOURCE = ubuntu-prozoro-slave-ami.json
TARGETS = $(basename $(SOURCE))

all: $(TARGETS)

$(TARGETS):
	$(PACKER) build $(PFLAGS) $@.json
