#!/bin/sh
#
# PROVIDE: dnsmasq
# REQUIRE: SERVERS
# BEFORE:  DAEMON named
# KEYWORD: shutdown
# XQUERY: -i "count(//dnsmasq/enable) > 0" -o "0" -b
# RCVAR: dnsmasq

. /etc/rc.subr
. /etc/util.subr
. /etc/configxml.subr

name=dnsmasq
rcvar=dnsmasq_enable


pidfile="/var/run/${name}.pid"
# timestamp (below) is used to check if "reload" should be a "restart" instead
timestamp="/var/run/${name}.stamp"

load_rc_config "${name}"

# Custom commands
mkconf_cmd="dnsmasq_mkconf"
start_precmd="dnsmasq_mkconf"
reload_precmd="reload_pre"
reload_postcmd="reload_post"
start_postcmd="timestampconf"
stop_precmd="rmtimestamp"
logstats_cmd="logstats"
extra_commands="mkconf reload logstats"


# Defaults
dnsmasq_enable=${dnsmasq_enable="NO"}
dnsmasq_conf_dir=${dnsmasq_conf_dir-"/var/etc"}
dnsmasq_conf=${dnsmasq_conf-"${dnsmasq_conf_dir}/${name}.conf"}

: ${dnsmasq_restart="YES"}
command="/usr/local/sbin/${name}"

_sambaad=`/usr/local/bin/xml sel -t -v "count(//sambaad/enable)" /conf/config.xml`

if [ 0 -eq "${_sambaad}" ]; then
	dnsmasqport=""
else
	dnsmasqport="-p 5354"
fi
command_args="-x $pidfile -C $dnsmasq_conf ${dnsmasqport}"

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

dnsmasq_mkconf()
{
	local _listenadress _interface _router
	
	_rootfolder=`configxml_get "//dnsmasq/rootfolder"`
	_listenadress=`configxml_get "//interfaces/lan/ipaddr"`
	_interface=`configxml_get "//interfaces/lan/if"`
	_router=`configxml_get "//interfaces/lan/gateway"`
	_startaddr=`configxml_get "//dnsmasq/startadr"`
	_endaddr=`configxml_get "//dnsmasq/endadr"`
	_leasemax=`configxml_get "//dnsmasq/leasecount"`
	_logging=`configxml_get "//dnsmasq/logging"`
	_broadcast=`/sbin/ifconfig ${_interface} |  grep broadcast | awk '{print \$6}'`

	cat << EOF > ${dnsmasq_conf}
# Defaults
log-facility=/var/log/dnsmasq.log
dhcp-leasefile=/var/db/dnsmasq.leases
user=nobody
group=nobody
domain-needed
bogus-priv
domain=local
expand-hosts
local=/local/
dhcp-option=23,50
dhcp-authoritative
# Build from main config
listen-address=${_listenadress}
interface=${_interface}
dhcp-option=option:router,${_router}
dhcp-option=42,0.0.0.0
dhcp-option=28,${_broadcast}
# Setting over NAS4Free webGUI
EOF
_noresolv=`/usr/local/bin/xml sel -t -v "count(//dnsmasq/noresolv)" /conf/config.xml`

if [ 0 -ne "${_noresolv}" ]; then
	echo "no-resolv" >> ${dnsmasq_conf};
	echo "no-poll" >> ${dnsmasq_conf};
fi
if [ -n "${_startaddr}" ] &&  [ -n "${_endaddr}" ] ; then 
		echo 'dhcp-range='${_startaddr}','${_endaddr}',10m'  >> ${dnsmasq_conf}
		echo 'dhcp-lease-max='${_leasemax}  >> ${dnsmasq_conf}
fi
case ${_logging} in
		all)
			echo "log-queries" >> ${dnsmasq_conf};
			echo "log-dhcp" >> ${dnsmasq_conf};
			;;
		dhcp)
			echo "log-dhcp" >> ${dnsmasq_conf};
			;;
		mini)
			
			;;
esac
/usr/local/bin/xml sel -t \
	-i "count(//dnsmasq/extconfig) > 0" -o "conf-dir=" -v "//dnsmasq/rootfolder" -o "conf" -n -b \
	${configxml_file} | /usr/local/bin/xml unesc >> ${dnsmasq_conf}
/usr/local/bin/xml sel -t \
	-i "string-length(//dnsmasq/tftpboot) > 3" -o "dhcp-boot=" -v "//dnsmasq/tftpboot" -n -b \
	${configxml_file} | /usr/local/bin/xml unesc >> ${dnsmasq_conf}
/usr/local/bin/xml sel -t \
	-i "count(//dnsmasq/enabletftp) > 0" -o "enable-tftp" -n -b \
	${configxml_file} | /usr/local/bin/xml unesc >> ${dnsmasq_conf}

/usr/local/bin/xml sel -t \
	-i "string-length(//dnsmasq/tftproot) > 3" -o "tftp-root=" -v "//dnsmasq/tftproot" -n -b \
	${configxml_file} | /usr/local/bin/xml unesc >> ${dnsmasq_conf}

_index=`configxml_get_count "//dnsmasq/hosts"`
while [ ${_index} -gt 0 ]
	do
	
	/usr/local/bin/xml sel -t -m "//dnsmasq/hosts[position()=${_index}]" \
	-o "dhcp-host="  \
	-i "string-length(macaddr) > 3" -v "concat(macaddr,',')" -b \
	-i "string-length(ipadress) > 3" -v "concat(ipadress,',')" -b \
	-i "string-length(hostname) > 3" -v "concat(hostname,',')" -b \
	-i "string-length(leasetime) > 0" -v "leasetime" --else -o "15m" -b -n \
	-b \
	${configxml_file} | /usr/local/bin/xml unesc >> ${dnsmasq_conf}
	_index=$(( ${_index} - 1 ))
	
	done
}	
run_rc_command "$1"
