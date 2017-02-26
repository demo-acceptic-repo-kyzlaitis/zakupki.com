#!/bin/bash

commands[0]="pr,restart php,php"
commands[1]="nr,restart nginx,nginx"
commands[2]="dr,restart postgres,db-mysql"
commands[3]="ci,run composer install,php"
commands[4]="cu,run composer update,php"
commands[5]="ld,load dump to database,db-mysql"
commands[6]="mu,migrations up,php"
commands[7]="cm,load dump and run migrations"
commands[8]="start,start environment(docker-compose up -d),start,php"
commands[9]="stop,stop containers,stop,php"
commands[10]="fr,first run - migrations up and load dump,php"
commands[11]="ca,delete all project containers and images. Be carefully!,php"
commands[12]="sa,stop all containers,php"
commands[13]="cr,remove all containers,php"
commands[14]="ir,remove all images,php"
commands[15]="rdc,remove database container,php"
commands[16]="name,echo docker project name,php"
commands[17]="user,user name"
commands[18]="fcm,access for logs and cache folders"
commands[19]="bi,build docker images and push them to amazon elastic container storage"
commands[20]="gac,request and apply amazon access key"
commands[21]="sr,rotate sphinx indexs"

options[0]="-h ,this help"
options[1]="--help,this help"
options[2]="-v,current version"
options[3]="--version,current version"

command=$1
option=$1

array=''

version=0.5.0

user() {
    echo $USER
}

name() {
    echo "$name"
}

start() {
    docker-compose up -d
    docker-compose ps
}

stop() {
    docker-compose stop
    docker-compose ps
}

ci(){
    docker exec $(name)_php_1 composer install
    docker exec -i $(name)_php_1 bash -c 'composer install'
}

cu(){
    docker exec $(name)_php_1 composer update
    docker exec -i $(name)_php_1 bash -c 'composer update'
}

mu() {
     docker exec $(name)_php_1 php artisan migrate
     echo "run migration dummy"
}

ld() {
    docker exec -i $(name)_db-mysql_1 mysql -uroot -proot ts_zakupki < docker/db-mysql/dump/zakupki.sql
}

fcm() {
    docker exec $(name)_php_1 chmod -R 777 storage/
}

pr() {
    docker exec $(name)_php_1 service php restart
}

dr() {
    docker exec $(name)_php_1 service mysql restart
}

nr() {
    echo "$(name)_nginx_1 service nginx reload"
    docker exec $(name)_nginx_1 service nginx reload
}

ca() {
    #docker rm -f $(docker ps -a -q)
    docker ps -a | awk '{ print $1,$2 }' | grep $(name)_ | awk '{print $1 }' | xargs -I {} docker rm {}
    #docker rmi -f $(docker images -a -q)
    docker images | awk '{ print $1,$2 }' | grep $(name)_ | awk '{print $1 }' | xargs -I {} docker rmi {}
}

sa() {
    echo 'stop all containers'
    docker stop $(docker ps -a -q)
}

cr() {
    #docker rm $(docker ps -a -q)
    docker ps -a | awk '{ print $1,$2 }' | grep $(name)_ | awk '{print $1 }' | xargs -I {} docker rm {}
}

rdc() {
    #docker rm $(docker ps -a -q)
    docker ps -a | awk '{ print $1,$2 }' | grep $(name)_db-mysql | awk '{print $1 }' | xargs -I {} docker rm {}
}

ir() {
    #docker rmi $(docker images -a -q)
    docker images | awk '{ print $1,$2 }' | grep $(name)_ | awk '{print $1 }' | xargs -I {} docker rmi {}
}

gac() {
    docker="$( bash <<EOF
    aws ecr get-login --region eu-central-1
EOF
)"
    eval $docker

}

sr() {
    #docker exec -i $(name)_php_1 tail /var/log/schedule.out.log
    docker exec -i $(name)_sphinx_1 indexer -c /etc/sphinxsearch/sphinxy.conf --all --rotate  
}

bi() {
    gac
    echo "build docker images and push them to amazon elastic container storage"

    docker build -t zakupki_supervisor docker/sphinx
    docker tag zakupki_supervisor:latest 513518751793.dkr.ecr.eu-central-1.amazonaws.com/zakupki_sphinx:latest
    docker push 513518751793.dkr.ecr.eu-central-1.amazonaws.com/zakupki_sphinx:latest

    docker build -t zakupki_application docker/application
    docker tag zakupki_application:latest 513518751793.dkr.ecr.eu-central-1.amazonaws.com/zakupki_application:latest
    docker push 513518751793.dkr.ecr.eu-central-1.amazonaws.com/zakupki_application:latest

    docker build -t zakupki_nginx docker/nginx
    docker tag zakupki_nginx:latest 513518751793.dkr.ecr.eu-central-1.amazonaws.com/zakupki_nginx:latest
    docker push 513518751793.dkr.ecr.eu-central-1.amazonaws.com/zakupki_nginx:latest

    docker build -t zakupki_php docker/php-fpm
    docker tag zakupki_php:latest 513518751793.dkr.ecr.eu-central-1.amazonaws.com/zakupki_php:latest
    docker push 513518751793.dkr.ecr.eu-central-1.amazonaws.com/zakupki_php:latest

    docker build -t zakupki_supervisor docker/supervisor
    docker tag zakupki_supervisor:latest 513518751793.dkr.ecr.eu-central-1.amazonaws.com/zakupki_supervisor:latest
    docker push 513518751793.dkr.ecr.eu-central-1.amazonaws.com/zakupki_supervisor:latest
}

cm() {
    ld
    mu
}

fr(){
    user
    gac
    start
    fcm
    ci
    sleep 5
    ld
    mu
    sleep 11
    sr
}

source `dirname $0`/command.sh




