#!/bin/bash

get_options () {
        for index in "${!options[@]}"
        do
            IFS='_' read -r -a array <<< "${options[index]}"
            size=${#array[0]}
            local test=" "
            for (( c=1; c<=10-$size; c++ ))
            do
                test=${test}" "
            done
            echo '       '${array[0]}"$test"${array[1]}
        done
}


get_commands () {
        for index in "${!commands[@]}"
        do
            IFS=',' read -r -a array <<< "${commands[index]}"
            size=${#array[0]}
            local test=" "
            for (( c=1; c<=10-$size; c++ ))
            do
                test=${test}" "
            done
            echo '       '${array[0]}"$test"${array[1]}
        done
}

name () {

        IFS=', /' read -r -a array <<< "$PWD"
        for index in "${!array[@]}"
        do
            i=$index
        done
        IFS='-_' read -r -a array <<< "${array[index]}"
        for index in "${!array[@]}"
        do
            str="$str${array[index]}"
        done
        echo "${str}"
}

run_commands () {
        for index in "${!commands[@]}"
        do
            IFS=',' read -r -a array <<< "${commands[index]}"
                if [ ${array[0]}"" == $command ]; then
                    $command

                    # Exits the program using a successful exit status code.
                    exit 0;
                fi
        done
}

run_options () {
        for index in "${!options[@]}"
        do
            IFS=',' read -r -a array <<< "${options[index]}"
                if [ ${array[0]}"" == $option ]; then
                    echo "options: "${array[0]}" "${array[1]}
                    exit 1;
                fi
        done
}

name=$1
log_file="Logone.txt"

if [[ -n "$name" ]]; then

         #echo   $(name)$(run_commands)
        run_commands

        for index in "${!array[@]}"
        do
            i=$index
        done


    case $1 in
    php)
       # echo enter into php container

       #exec docker exec $(name) echo hello

       ;;
    esac

else

t="$(cat <<-EOF
Usage: zakupki [OPTIONS] COMMAND [arg...]

       zakupki [ --help | -v | --version ]

Help script for zakupki project.

Options:

EOF
)"




b="$(cat <<-EOF

Run 'zakupki COMMAND --help' for more information on a command.
EOF
)"

echo "$t"
               # if [ ${array[0]}"" == $option ]; then
               #     echo "options: "${array[0]}" "${array[1]}
               #     exit 1;
               # fi
get_options
echo "Commands:"
get_commands
echo "$b"

fi
