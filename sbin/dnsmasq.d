#!/bin/sh

# $FreeBSD: head/dns/dnsmasq/files/dnsmasq.in 302141 2012-08-05 23:19:36Z dougb $
#
# PROVIDE: dnsmasq
# REQUIRE: SERVERS
# BEFORE:  DAEMON named
# KEYWORD: shutdown
#RCVAR: dnsmasq
#
# Start before named so as not to break named_wait if named is
# enabled and /etc/resolv.conf points to ourselves (dnsmasq).
#
#
# Please add the following line to /etc/rc.conf.local or /etc/rc.conf to
# enable the dnsmasq service(s):
#
# dnsmasq_enable (bool):  Set to "NO" by default.
#                         Set it to "YES" to enable dnsmasq at boot.
#
# Further settings you can change in /etc/rc.conf if desired:
#
# dnsmasq_conf (path):    Set to /usr/local/etc/dnsmasq.conf by default.
#                         Set it to another configuration file if you want.
#
# dnsmasq_flags (string): Empty by default. Set it to additional command
#                         line arguments if desired.
#
# dnsmasq_restart (bool): Set to "YES" by default.
#                         If "YES", a "reload" action will trigger a "restart"
#                         if the configuration file has changed, to work
#                         around a dnsmasq(8) limitation.
#
#
# Additional actions supported by this script:
#
# reload        Reload database files by sending SIGHUP and SIGUSR2.
#               However, if dnsmasq_restart is true (see above) and the
#               configuration file has changed since this rc script has
#               started dnsmasq, restart it instead.
#
# logstats      Dump statistics information to where dnsmasq is configured to
#               log (syslog by default). This sends SIGUSR1 to dnsmasq.
#

. /etc/rc.subr

name=dnsmasq
rcvar=dnsmasq_enable

command="/usr/local/sbin/${name}"
pidfile="/var/run/${name}.pid"
# timestamp (below) is used to check if "reload" should be a "restart" instead
timestamp="/var/run/${name}.stamp"

load_rc_config "${name}"

: ${dnsmasq_enable="NO"}
: ${dnsmasq_conf="/etc/${name}.conf"}
: ${dnsmasq_restart="YES"}

command_args="-x $pidfile -C $dnsmasq_conf"

required_files="${dnsmasq_conf}"
extra_commands="reload logstats"

reload_precmd="reload_pre"
reload_postcmd="reload_post"
start_postcmd="timestampconf"
stop_precmd="rmtimestamp"
logstats_cmd="logstats"

reload_pre() {
        if [ "$dnsmasq_conf" -nt "${timestamp}" ] ; then
                if checkyesno dnsmasq_restart ; then
                        info "restart: $dnsmasq_conf changed"
                        exec "$0" restart
                else
                        warn "restart required, $dnsmasq_conf changed"
                fi
        fi
}

reload_post() {
        kill -USR2 ${rc_pid}
}

logstats() {
        kill -USR1 ${rc_pid}
}

timestampconf() {
        touch -r "${dnsmasq_conf}" "${timestamp}"
}

rmtimestamp() {
        rm -f "${timestamp}"
}

run_rc_command "$1"
