#!/usr/bin/env bash

#
# Run the version update script.
#

GIT_DIR_="$(git rev-parse --git-dir)"

currentBranch=$(git rev-parse --symbolic-full-name --abbrev-ref HEAD)
updateVersionProgram=$GIT_DIR_/../bin/build.sh


# Updates and changes the files if the flag file exits, if and only if we are on the 'develop'
# branch.
# '-C HEAD' do not prompt for a commit message, use the HEAD as commit message.
# '--no-verify' do not call the pre-commit hook to avoid infinity loop.
if [ -f $updateFlagFilePath ]
then
    if [[ $currentBranch == "master" ]]
    then
        if sh $updateVersionProgram
        then
            echo "Successfully ran '$updateVersionProgram'."
        else
            echo "Could not run the update program '$updateVersionProgram' properly!"
            cleanUpdateFlagFile
            exit 1
        fi
        echo "Amending commits..."
        git commit --amend -C HEAD --no-verify
    else
        echo "It is not time to amend, as we are not on the 'develop' branch."
    fi
else
    echo "It is not time to amend, as the file '$updateFlagFilePath' does not exist."
fi


# Exits the program using a successful exit status code.
exit 0





