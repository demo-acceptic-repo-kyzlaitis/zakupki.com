#!/bin/bash
#
# If run inside a git repository will return a valid semver based on
# the semver formatted tags. For example if the current HEAD is tagged
# at 0.0.1, then the version echoed will simply be 0.0.1. However, if
# the tag is say, 3 patches behind, the tag will be in the form
# `0.0.1+build.3.0ace960`. This is basically, the current tag a
# monotonically increasing commit (the number of commits since the
# tag, and then a git short ref to identify the commit.
#
# You may also pass this script the a `release` argument. If that is
# the case it will exit with a non-zero value if the current head is
# not tagged.
#
set -e

commands[0]="gvc,build git version candidate,git"
commands[1]="gcc,calculate git commit count,git"
commands[2]="giv,increment git version,git"
commands[3]="gbv,build git version,git"
commands[4]="gtv,tag new version and push to remote repos,git"
commands[5]="gblv,build git lite version,git"
commands[6]="gtlv,tag new lite version and push to remote repos,git"
commands[7]="gabv,auto build git version,git"
commands[8]="gatv,tag new auto build version and push to remote repos,git"
commands[9]="guvf,update version file,git"
commands[10]="gluvf,update lite version file,git"
commands[11]="gauvf,update auto build version file,git"
commands[12]="grprev,rollback to previous tag version,git"

options[0]="-h ,this help"
options[1]="--help,this help"
options[2]="-v,current version"
options[3]="--version,current version"

command=$1
command2=$2
option=$1

array=''
commit_count=
original_version=
version_candidate=
version_tag=
original_vsn=
vsn=

version=0.5.0

grprev() {
    latest_tag=`git describe --tags \`git rev-list --tags --max-count=1\``
    previous_tag=`git describe --abbrev=0 --tags ${latest_tag}^`
    echo "Rollback to previous tag $previous_tag"
    `git reset --hard ${previous_tag}`
}

gvc() {
    get-version-candidate
}

gcc() {
    get-commit-count
}

giv() {
    increment-version
}

gbv() {
    if [ -z "$command2" ]
    then
        build-version
    else
        increment-version
        build-version
    fi
}

gblv() {
    if [ -z "$command2" ]
    then
        get-version-candidate
    else
        get-version-candidate
        increment-version
    fi
    original_vsn=$original_version
    vsn=$version_candidate
}

gtv() {
    if [ -z "$command2" ]
    then
        git-tag-new-version
    else
        increment-version
        git-tag-new-version
    fi
}

gtlv() {
    gblv

    if [ -z "$command2" ]
    then
        git-tag-new-version
    else
        increment-version
        git-tag-new-version
    fi
}

gabv() {
    if [ -z "$command2" ]
    then
        get-version-candidate-auto-patch
    else
        get-version-candidate-auto-patch
        increment-version
    fi
    original_vsn=$original_version
    vsn=$version_candidate
}

gatv() {
    gabv

    if [ -z "$command2" ]
    then
        git-tag-new-version
    else
        increment-version
        git-tag-new-version
    fi
}

guvf() {
    gbv
    git-update-version-file
}

gluvf() {
    gblv
    git-update-version-file
}

gauvf() {
    gabv
    git-update-version-file
}

#----------------------------------------------------------------------------------------
#----------------------------------------------------------------------------------------
#----------------------------------------------------------------------------------------

get-commit-count()
{
    if [[ $version_tag = "" ]]; then
        commit_count=`git rev-list HEAD | wc -l`
    else
        commit_count=`git rev-list ${version_tag}..HEAD | wc -l`
    fi
    commit_count=`echo $commit_count | tr -d ' 't`
}

get-version-candidate()
{
    local ver_regex='tag: (v([^,\)]+)|([0-9]+(\.[0-9]+)*))'
    local tag_lines=`git log --oneline --decorate  |  fgrep "tag: "`

    if [[ $tag_lines =~ $ver_regex ]]; then
        if [[ ${BASH_REMATCH[1]:0:1} = "v" ]]; then
            version_tag=${BASH_REMATCH[1]}
            version_candidate=${BASH_REMATCH[2]}
        else
            version_tag=${BASH_REMATCH[3]}
            version_candidate=${BASH_REMATCH[3]}
        fi
    else
        version_tag=""
        version_candidate="0.0.0"
    fi

    major=$(echo $version_candidate | cut -d'.' -f 1)
    minor=$(echo $version_candidate | cut -d'.' -f 2)
    patch=$(echo $version_candidate | cut -d'.' -f 3)

    version_candidate=$major.$minor.$patch
    original_version=$version_candidate
}

get-version-candidate-auto-patch()
{
    if [[ $version_candidate = "" ]]; then
        get-version-candidate
    fi

    if [[ $commit_count = "" ]]; then
        get-commit-count
    fi

    original_version=$major.$minor.$patch
    patch=$commit_count
    version_candidate=$major.$minor.$patch
}

increment-version()
{
    if [[ $version_candidate = "" ]]; then
        get-version-candidate
    fi

    # 'cut' Print selected parts of lines from each FILE to standard output
    #
    # '-d' use another delimiter instead of TAB for field delimiter.
    # '-f' select only these fields.
    #

    if [ -z "${major}" ] || [ -z "${minor}" ] || [ -z "${patch}" ]
    then
        echo "VAR <$major>.<$minor>.<$patch> is bad set or set to the empty string"
        exit 1
    fi

    if [ -z "$command2" ]
      then
        echo "Set build type into second argument [major|minor|patch]"
        exit 1
    fi

    case "$command2" in
        major )
            major=$(expr $major + 1)
            minor=0
            patch=0
            ;;

        minor )
            minor=$(expr $minor + 1)
            patch=0
            ;;

        patch )
            patch=$(expr $patch + 1)
            ;;

        * )
            echo "Error - argument must be 'major', 'minor' or 'patch'"
            echo "Usage: updateVersion [major | minor | patch ]"
            exit 1
            ;;
    esac

    version_candidate=$major.$minor.$patch
}

build-version()
{
    if [[ $version_candidate = "" ]]; then
        get-version-candidate
    fi

    if [[ $commit_count = "" ]]; then
        get-commit-count
    fi

    if [[ $commit_count = 0 ]]; then
        vsn=$version_candidate
        original_vsn=$original_version
    else
        local ref=`git log -n 1 --pretty=format:'%h'`
        vsn="${version_candidate}+build.${commit_count}.${ref}"
        original_vsn="${original_version}+build.${commit_count}.${ref}"
    fi
}

git-tag-new-version()
{
    if [[ $vsn = "" ]]; then
        build-version
    fi

    `git shortlog ${vsn} ..`
    `git tag ${vsn}`
    `git push origin ${vsn}`
    echo `${vsn} has been tagged`

}

git-update-version-file()
{
    if [[ $original_vsn = "" ]]; then
        build-version
    fi

    originalVersion=$original_vsn
    currentVersion=$vsn
    filePathToUpdate='../VERSION'

    if ! grep -Fq "v$originalVersion" "$filePathToUpdate"
    then
        echo "Error! Could not find v$originalVersion and update the file '$filePathToUpdate'."
        echo "The current version number must be v$originalVersion!"
        exit 1
    fi


    if sed -i -- "s/v$originalVersion/v$currentVersion/g" $filePathToUpdate
    then
        echo "Replacing the version v$originalVersion -> v$currentVersion in '$filePathToUpdate'"

        # Replace the file with the $versionFilePath with the $currentVersion.
        echo "v$currentVersion" > $filePathToUpdate
    else
        echo "ERROR! Could not replace the version v$originalVersion -> v$currentVersion in '$filePathToUpdate'"
        exit 1
    fi
}

source `dirname $0`/command.sh
