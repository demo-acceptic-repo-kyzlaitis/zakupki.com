#!/bin/bash

commands[0]="prepare,prepare project to run static tests"
commands[1]="user,user name"
commands[2]="name,echo docker project name,php"
commands[3]="vendor,run php command throw docker"
commands[4]="pdepend,run pdepend"
commands[5]="phpcpd,run phpcpd"
commands[6]="phpcpd-ci,run phpcpd-ci"
commands[7]="phpcs,run phpcs"
commands[8]="phpcs-ci,run phpcs-ci"
commands[9]="phploc,run phploc"
commands[10]="phploc-ci,run phploc-ci"
commands[11]="phpmd,run phpmd"
commands[12]="phpmd-ci,run phpmd-ci"
commands[13]="phpunit,run phpunit"
commands[14]="phpunit-ci,run phpunit-ci"
commands[15]="static-analysis,run static-analysis"
commands[16]="static-analysis-ci,run static-analysis-ci"
commands[17]="phpdcd,run phpdcd"

options[0]="-h ,this help"
options[1]="--help,this help"
options[2]="-v,current version"
options[3]="--version,current version"

command=$1
command2=${@: 2}

option=$1

array=''

version=0.5.0

user() {
    echo $USER
}

name() {
    echo "$name"
}

vendor() {
    docker exec -i $(name)_php_1 ${illcommnado}
}

prepare() {
    mkdir -p ./build/coverage
    mkdir -p ./build/logs
    mkdir -p ./build/pdepend
    mkdir -p ./build/phpdox
}

clean() {
    rm -r ./build/
}

pdepend() {

    script="vendor/bin/pdepend"
    arg1="--jdepend-xml=build/logs/jdepend.xml"
    arg2="--jdepend-chart=build/pdepend/dependencies.svg"
    arg3="--overview-pyramid=build/pdepend/overview-pyramid.svg"
    arg4="app"

    illcommnado="$script $arg1 $arg2 $arg3 $arg4"

    echo "Calculate software metrics using PHP_Depend and log result in XML format. Intended for usage within a continuous integration environment."
    vendor

}

phpdcd() {

    script="vendor/bin/phpdcd"
    arg1="app"

    illcommnado="$script $arg1"

    echo "Find unused code using PHPDPD and print human readable output. Intended for usage on the command line before committing."
    vendor

}

phpcpd() {

    script="vendor/bin/phpcpd"
    arg1="app"

    illcommnado="$script $arg1"

    echo "Find duplicate code using PHPCPD and print human readable output. Intended for usage on the command line before committing."
    vendor

}

phpcpd-ci() {

    script="vendor/bin/phpcpd"
    arg1="--log-pmd build/logs/pmd-cpd.xml"
    arg2="app"

    illcommnado="$script $arg1 $arg2"

    echo "Find duplicate code using PHPCPD and log result in XML format. Intended for usage within a continuous integration environment."
    vendor

}

phpcs() {

    script="vendor/bin/phpcs"
    arg1="--standard=PSR2"
    arg2="--extensions=php"
    arg3="--ignore=autoload.php"
    arg4="app"

    illcommnado="$script $arg1 $arg2 $arg3 $arg4"

    echo "Find coding standard violations using PHP_CodeSniffer and print human readable output. Intended for usage on the command line before committing."
    vendor

}

phpcs-ci() {

    script="vendor/bin/phpcs"
    arg1="--report-checkstyle=build/logs/phpcs.checkstyle.xml"
    arg2="--report-xml=build/logs/phpcs.xml"
    arg3="--standard=PSR2"
    arg4="--extensions=php"
    arg5="--ignore=autoload.php"
    arg6="app"


    illcommnado="$script $arg1 $arg2 $arg3 $arg4 $arg5 $arg6"

    echo "Find coding standard violations using PHP_CodeSniffer and log result in XML format. Intended for usage within a continuous integration environment."
    vendor

}

phploc() {

    script="vendor/bin/phploc"
    arg1="--count-tests"
    arg2="app"

    illcommnado="$script $arg1 $arg2"

    echo "Measure project size using PHPLOC and print human readable output. Intended for usage on the command line."
    vendor

}

phploc-ci() {

    script="vendor/bin/phploc"
    arg1="--count-tests"
    arg2="--log-csv build/logs/phploc.csv"
    arg3="--log-xml build/logs/phploc.xml"
    arg4="app"

    illcommnado="$script $arg1 $arg2 $arg3 $arg4"

    echo "Measure project size using PHPLOC and log result in CSV and XML format. Intended for usage within a continuous integration environment."
    vendor

}

phpmd() {

    script="vendor/bin/phpmd"
    arg1="app"
    arg2="text"
    arg3="codesize,unusedcode,naming,bin/phpmd.xml"

    illcommnado="$script $arg1 $arg2 $arg3"

    echo "Perform project mess detection using PHPMD and print human readable output. Intended for usage on the command line before committing."
    vendor

}

phpmd-ci() {

    script="vendor/bin/phpmd"
    arg1="app"
    arg2="text"
    arg3="codesize,unusedcode,naming,bin/phpmd.xml"
    arg4="--reportfile build/logs/pmd.xml"

    illcommnado="$script $arg1 $arg2 $arg3 $arg4"

    echo "Perform project mess detection using PHPMD and log result in XML format. Intended for usage within a continuous integration environment."
    vendor

}

phpunit() {

    script="vendor/bin/phpunit"
    arg1="--configuration tests/phpunit.xml"

    illcommnado="$script $arg1"

    echo "Run unit tests with PHPUnit."
    vendor

}

phpunit-ci() {

    script="vendor/bin/phpunit"
    arg1="--configuration tests/phpunit.xml"
    arg3=""

    illcommnado="$script $arg1"

    echo "Run unit tests with PHPUnit."
    vendor

}

static-analysis() {
    clean
    prepare
   # lint
    phploc
    pdepend
    phpmd
    phpcs
    phpcpd
    phpdcd
}

static-analysis-ci() {
    clean
    prepare
   # lint
    phploc-ci
    pdepend
    phpmd-ci
    phpcs-ci
    phpcpd-ci
}

source `dirname $0`/command.sh