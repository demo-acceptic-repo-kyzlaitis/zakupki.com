#!/bin/sh

### BEGIN INIT INFO
# Provides:          hudsonvmfarm
# Required-Start:    $remote_fs $syslog
# Required-Stop:     $remote_fs $syslog
# Should-Start:      $named
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: hudsonswarm build slave
# Description:       hudsonswarm build slave assigned to a Hudson master
### END INIT INFO
set -e

. /lib/lsb/init-functions

USER=jenkins
USER_HOME="/home/${USER}"
JAR="/usr/local/bin/swarm-client-2.2-jar-with-dependencies.jar"
LOG="/var/log/jenkins-swarm.log"
MASTER="http://35.156.67.36:8080"

# Swarm client option
DESCRIPTION="zakupki jenkins slave"
EXECUTORS=1
FSROOT="${USER_HOME}"
LABELS="Swarm"
USERNAME="ec2-slave"
PASSWORD="gUb+8W6zesT="

OPTS="-description \"${DESCRIPTION}\" \
      -executors ${EXECUTORS} \
      -fsroot ${FSROOT} \
      -labels \"${LABELS}\" \
      -master ${MASTER} \
      -logFile ${LOG} \
      -name `hostname` \
      -username ${USERNAME} \
      -password ${PASSWORD}"


PIDFILE="/var/run/hudsonON
case $1 in
   start)
       log_daemon_msg "Starting hudsonswarm"
       start-stop-daemon --start --quiet --chuid $USER --background --make-pidfile --pidfile $PIDFILE --startas $DAEMON -- $ARGS
       log_end_msg $?
       ;;
   stop)
       if [ -e $PIDFILE ]; then
          log_daemon_msg "Stopping hudsonswarm"
          start-stop-daemon --stop --quiet --pidfile $PIDFILE
          log_end_msg $?
          rm -f $PIDFILE
       fi
       ;;
   restart)
        $0 stop
        sleep 2
        $0 start
        ;;
   status)
        status_of_proc -p $PIDFILE "$DAEMON" hudsonswarm
  RETVAL=$?
	;;

   *)
        echo "Usage: $0 {start|stop|restart|status}"
        exit 1

esac