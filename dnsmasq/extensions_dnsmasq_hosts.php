<?php
/*
extensions_dnsmasq_host.php
*/
unset($warning_mess);
require("auth.inc");
require("guiconfig.inc");
include_once ($config['dnsmasq']['rootfolder']."dnsmasq/function.inc");
// $dhcpd_conf = read_dhcpconf($config['dhcplight']['homefolder']."conf/dhcpd.conf");
if (isset($_GET['uuid']))
	$uuid = $_GET['uuid'];
if (isset($_POST['uuid']))
	$uuid = $_POST['uuid'];

if ( !isset($config['dnsmasq']['hosts']) || !is_array($config['dnsmasq']['hosts']) )
	$config['dnsmasq']['hosts'] = array();

array_sort_key($config['dnsmasq']['hosts'], "hostno");
$a_hosts = &$config['dnsmasq']['hosts'];
if ($_GET) {
if (isset($_GET['act']) && ($_GET['act'] === "del")) {
		$number = $_GET['uuid'];
$cnid = array_search_ex($number, $config['dnsmasq']['hosts'], "uuid");
			if (false !== $cnid) {
			unset($config['dnsmasq']['hosts'][$cnid]);
				write_config();
				mwexec ("touch /var/run/dnsmasq.reload");
			}
		
		// write_dhcpconf($dhcpd_conf, $config['dhcplight']['homefolder']."conf/dhcpd.conf");
		header("Location: extensions_dnsmasq_server.php");
		}
if (isset($_GET['act']) && ($_GET['act'] === "edit")) { $uuid = $_GET['uuid'];

if (FALSE !== ($cnid = array_search_ex($uuid, $a_hosts, "uuid"))) {
	$pconfig['uuid'] = $a_hosts[$cnid]['uuid'];
	$pconfig['macaddr'] = $a_hosts[$cnid]['macaddr'];
	$pconfig['ipadress'] = $a_hosts[$cnid]['ipadress'];
	$pconfig['hostname'] = $a_hosts[$cnid]['hostname'];
	$pconfig['leasetime'] = $a_hosts[$cnid]['leasetime'];
	$pconfig['mode'] = "edit";
	$pconfig['hostno'] = $a_hosts[$cnid]['hostno'];
	}
	}
else {
	$pconfig['uuid'] = uuid();
	$pconfig['macaddr'] = "";
	$pconfig['ipadress'] = "";
	$pconfig['hostname'] = "";
	$pconfig['leasetime'] = "60";
	$pconfig['mode'] = "new";
	$pconfig['hostno'] = dnsmasq_get_next_hostno();
	}
}	
If ($_POST) {

	unset($input_errors);
	if (isset($_POST['Submit']) && ($_POST['Submit']=== "Cancel" )) { 	header("Location: extensions_dnsmasq_server.php"); }
	if (isset($_POST['Submit']) && ($_POST['Submit'] === "Save")) { 
$pconfig = $_POST;
	// Input validation
// All defined
	if ( ! empty($_POST['macaddr']) && ! empty($_POST['ipadress']) && ! empty($_POST['hostname'] )) { 
		$reqdfields = explode(" ", "macaddr ipadress hostname");
		$reqdfieldsn = array(gettext("MAC adress"), gettext("IP adress"), gettext("hostname"));
		$reqdfieldst = explode(" ", "macaddr ipaddr hostname");
		
		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
		if ($_POST['mode'] == "new") {
		if (is_macaddr($_POST['macaddr'])) {
			if ( is_array ($a_hosts)) {  
			$index = array_search_ex($_POST['macaddr'], $a_hosts, "macaddr");	
			if ( FALSE !==  ($index = array_search_ex($_POST['macaddr'], $a_hosts, "macaddr"))) { $input_errors[] = "MAC adress exist. It must be unique"; goto out;} else {} } else {} } } 
		$subnet = $config['interfaces']['lan']['ipaddr']."/".$config['interfaces']['lan']['subnet'];
		if (is_ipaddr ($_POST['ipadress'])) { if (false == ($cnif =ip_in_subnet($_POST['ipadress'] ,$subnet))) {$input_errors[] = "Value \"IP address\" is not belongs to the subnet LAN"; goto out;} else {} }
		$nas4frehosts = &$config['system']['hosts'];
		if (false !==($cnin = array_search_ex($_POST['hostname'],$nas4frehosts,"name"))) { $warning_mess="Host defined on <a href=system_hosts.php>/etc/hosts</a>. I clear entries MAC and IP adress and make leasetime <b>infinite</b>";
		if (  $_POST['hostname'] == $config['system']['hostname'] ) {$input_errors[] = "You can not define main host as DHCP client"; goto out;} else {
		$pconfig['ipadress'] ="";
		$pconfig['leasetime'] ="infinite";
		$pconfig['macaddr'] ="";
		} }
		else { 
			if (  $_POST['hostname'] == $config['system']['hostname'] ) {$input_errors[] = "You can not define main host as DHCP client"; goto out;} else {
		  
$warning_mess="Host NOT defined on <a href=system_hosts.php>/etc/hosts</a>, If you want  define it you can do it  now.";}
		  }
		}
//All empty
	elseif  ( empty($_POST['macaddr']) &&  empty($_POST['ipadress']) &&  empty($_POST['hostname'] )) {
		  $input_errors[] = "Need define anything"; goto out;}
// only IP defined
	elseif   ( empty($_POST['macaddr']) &&  !empty($_POST['ipadress']) &&  empty($_POST['hostname'] )) {
		    $input_errors[] = "Need define Hostname or MAC addres "; goto out; }
// Defined ip and hostname.  Need entry to /etc/host
	elseif   ( empty($_POST['macaddr']) &&  !empty($_POST['ipadress']) &&  !empty($_POST['hostname'] )) {
		      $reqdfields = explode(" ", "ipadress hostname");
		      $reqdfieldsn = array(gettext("IP adress"), gettext("hostname"));
		      $reqdfieldst = explode(" ", "ipaddr hostname");
		
		      do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		      do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors); 
		      
			$nas4frehosts = &$config['system']['hosts'];
			if (false !==($cnif = array_search_ex($_POST['hostname'],$nas4frehosts,"name"))) { 
				if (  $_POST['hostname'] == $config['system']['hostname'] ) {$input_errors[] = "You can not define main host as DHCP client"; goto out;} else {
					$pconfig['ipadress'] ="";
					$warning_mess="Host defined on <a href=system_hosts.php>/etc/hosts</a>. I  clear IP entry "; }
					      }
				else { 
					if (  $_POST['hostname'] == $config['system']['hostname'] ) {$input_errors[] = "You can not define main host as DHCP client"; goto out;} else {
						$pconfig['ipadress'] ="";
						$warning_mess="Host NOT defined on <a href=system_hosts.php>/etc/hosts</a>, please define it. I clear IP entry";	}
			}
	 }
// MAC without IP,. May be work, but I deny this.	
	elseif   ( !empty($_POST['macaddr']) &&   empty($_POST['ipadress'])) { 	    
		       $input_errors[] = "If you define MAC addres, you  must define IP address for it."; goto out; }
// MAC + Ip.
	elseif   ( !empty($_POST['macaddr']) && ! empty($_POST['ipadress']) && empty($_POST['hostname'] )) { 
		   $reqdfields = explode(" ", "macaddr ipadress");
		   $reqdfieldsn = array(gettext("MAC adress"), gettext("IP adress"));
		   $reqdfieldst = explode(" ", "macaddr ipaddr");
		
		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
		if ($_POST['mode'] == "new") {
		if (is_macaddr($_POST['macaddr'])) {
			if ( is_array ($a_hosts)) {  
			$index = array_search_ex($_POST['macaddr'], $a_hosts, "macaddr");	
			if ( FALSE !==  ($index = array_search_ex($_POST['macaddr'], $a_hosts, "macaddr"))) { $input_errors[] = "MAC adress exist. It must be unique"; goto out;} else {} } else {} }  }
		$subnet = $config['interfaces']['lan']['ipaddr']."/".$config['interfaces']['lan']['subnet'];
		if (is_ipaddr ($_POST['ipadress'])) { 
				if (false == ($cnif =ip_in_subnet($_POST['ipadress'],$subnet))) {$input_errors[] = "Value \"IP address\" is not belongs to the subnet LAN"; goto out;} else {} }
		} 
// Only hostname.  Need entry to /etc/hosts
      	elseif   ( empty($_POST['macaddr']) &&  empty($_POST['ipadress']) && !empty($_POST['hostname'] )) { 
		  if (FALSE == is_hostname($_POST['hostname'])) {$input_errors[] = "Wrong Host name."; goto out;} 
		  else {
			$pconfig['leasetime'] ="60";
			$nas4frehosts = &$config['system']['hosts'];
			if (false !==($cnin = array_search_ex($_POST['hostname'],$nas4frehosts,"name"))) { $warning_mess="Host defined on <a href=system_hosts.php>/etc/hosts</a>";}
			else { 
			      if (  $_POST['hostname'] == $config['system']['hostname'] ) {$input_errors[] = "You can not define main host as DHCP client"; goto out;} else {
			      $warning_mess="Host NOT defined on <a href=system_hosts.php>/etc/hosts</a>, please define it";	} 
			} }
}
// Make unknown error for all other combinations
	else 	{$input_errors[] = "Unknown error"; goto out;}

	
	if (empty($input_errors)) {
	$cnid = array_search_ex($pconfig['uuid'], $config['dnsmasq']['hosts'], "uuid");
	///  PROBLEM
		$index = array_search_ex($pconfig['hostno'], $a_hosts, "hostno");	
	if ( FALSE !== $index ) {
			if (isset($uuid) && (FALSE !== $cnid )){
				if ( $cnid < $index ){  for ( $i = $cnid; $i <= $index ; $i++ ){ $a_hosts[$i]['hostno'] -= 1; } 	} 
				elseif ( $cnid > $index ) {  for ( $i = $index; $i < $cnid ; $i++ ){	$a_hosts[$i]['hostno'] += 1;	} } 
			} 
			else {  for ( $i = $index; $i < count( $a_hosts ); $i++ ){ $a_hosts[$i]['hostno'] += 1; } } 
		} 
	$hosts = array();
	
	$hosts['uuid'] = $pconfig['uuid'];
	$hosts['hostno'] = $pconfig['hostno'];	
	$hosts['macaddr'] = $pconfig['macaddr'];
	$hosts['ipadress'] = $pconfig['ipadress'];
	$hosts['hostname'] = $pconfig['hostname'];
	
	$hosts['leasetime'] = $pconfig['leasetime'];
	



	if (isset($uuid) && (FALSE !== $cnid)) { $a_hosts[$cnid] = $hosts; $mode = UPDATENOTIFY_MODE_MODIFIED; } 
	else {	$a_hosts[] = $hosts; $mode = UPDATENOTIFY_MODE_NEW;	}
	updatenotify_set("dnsmasq", $mode, $hosts['uuid']);
		write_config();
		mwexec ("touch /var/run/dnsmasq.reload");
		file_put_contents("/var/run/dnsmasq.reload", $warning_mess);
		header("Location: extensions_dnsmasq_server.php");
		exit;
	}
}	
}


function dnsmasq_get_next_hostno() {
	global $config;
	$hostno = 1;
	$a_hosts = $config['dnsmasq']['hosts'];
	if (false !== array_search_ex(strval($hostno), $a_hosts, "hostno")) {
		do {
			$hostno += 1; 
		} while (false !== array_search_ex(strval($hostno), $a_hosts, "hostno"));
	}
	return $hostno;
}

out:
$pgtitle = array(gettext("Extensions"),gettext("Dnsmasq|Host"));
include("fbegin.inc");

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
	<form action="extensions_dnsmasq_hosts.php" method="post" name="iform" id="iform">
		<td class="tabcont">
		<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
			<table width="100%" border="0" cellpadding="5" cellspacing="0">
				<tr>
					<?php html_titleline(gettext("Dynamic Host Configuration Protocol - hosts"));?>
					
					<?php html_inputbox("macaddr", gettext("MAC adress"), $pconfig['macaddr'], gettext("Define MAC adress for host"), false, 26,false);?>
					<?php html_inputbox("ipadress", gettext("IP adress"), $pconfig['ipadress'], gettext("Define IP  adress for host"), false, 16,false);?>
					<?php html_inputbox("hostname", gettext("Hostname"), $pconfig['hostname'], gettext("Define hostname for host"), false, 16,false);?>
					<?php html_inputbox("leasetime", gettext("Lease time"), $pconfig['leasetime'], gettext("Define lease time for this host, seconds"), false, 16,false);?>
					
					
		
				</tr>	
				
				<tr>
				</tr>
				<tr><td>
					<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="Save" />
					<input name="hostno" type="hidden" value="<?=$pconfig['hostno'];?>" />
					<input name="mode" type="hidden" value="<?=$pconfig['mode'];?>" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
					<input name="Submit" type="submit" class="formbtn" value="Cancel" />
					</div>
				    </td>
				</tr>
			
			</table>
		</td>
		<?php include("formend.inc");?>
		</form>
	</tr>

	
</table>
<?php include("fend.inc"); ?>
