<?php
/* 
extensions_dnsmasq_server.php
Version 0.2
*/
ob_start();
require("auth.inc");
require("guiconfig.inc");
include_once ($config['dnsmasq']['rootfolder']."dnsmasq/function.inc");
if (!isset($config['dnsmasq']) || !is_array($config['dnsmasq'])) header("Location: extensions_dnsmasq_conf.php");
if (is_file("/var/run/dnsmasq.reload")) $warnmess = file_get_contents("/var/run/dnsmasq.reload");
if ($_POST) {
	if (isset($_POST['Submit']) && !empty($_POST['Submit'])) {  $pconfig = $_POST;
		unset($input_errors);
		if (isset($_POST['enable'])) { 	$config['dnsmasq']['enable'] = TRUE; } else { unset($config['dnsmasq']['enable']); }
			
			if (isset($_POST['extconfig']) ) { $config['dnsmasq']['extconfig'] = true;  } else { unset($config['dnsmasq']['extconfig']); }
			$config['dnsmasq']['logging'] =$_POST['logging'];
			
			if (empty($_POST['startadr']) || empty($_POST['endadr'])) { 
					$warningmess = "Dnsmasq will work as DNS forvarder only, without DHCP and netboot"; 
					unset($config['dnsmasq']['startadr']); unset($config['dnsmasq']['endadr']); 
					}
			    else {
					// Input validation
					$reqdfields = explode(" ", "startadr endadr leasecount");
					$reqdfieldsn = array(gettext("DHCP range - start"), gettext("DHCP range - end"), gettext("How leases allow"));
					$reqdfieldst = explode(" ", "ipaddr ipaddr numeric");
					do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);
					do_input_validation_type($_POST, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);
					$subnet = $config['interfaces']['lan']['ipaddr']."/".$config['interfaces']['lan']['subnet'];
					if (is_ipaddr ($_POST['startadr'])) { 
						if (false == ($cnif =ip_in_subnet($_POST['startadr'],$subnet))) {$input_errors[] = "Value \"DHCP range - start\" is not belongs to the subnet LAN"; goto out;} else {} }
					if (is_ipaddr ($_POST['endadr'])) { 
						if (false == ($cnif =ip_in_subnet($_POST['endadr'],$subnet))) {$input_errors[] = "Value \"DHCP range - end\" is not belongs to the subnet LAN"; goto out;} else {} }
					}
					if (empty($input_errors)) { 
					if ( isset($_POST['startadr']) &&  ($_POST['endadr'])) {
					$config['dnsmasq']['startadr'] =$_POST['startadr']; $config['dnsmasq']['endadr'] =$_POST['endadr']; $config['dnsmasq']['leasecount'] =$_POST['leasecount'];} else {}
			if (isset($_POST['enabletftp']) ) $config['dnsmasq']['enabletftp'] = TRUE; else unset ($config['dnsmasq']['enabletftp']);		
			if (isset($_POST['noresolv']) ) $config['dnsmasq']['noresolv'] = TRUE; else unset ($config['dnsmasq']['noresolv']);
			 $config['dnsmasq']['tftproot'] =  $_POST['tftproot'];
			  if ( !empty($_POST['tftpboot']) && strlen ($_POST['tftpboot']) > 3 ) { $config['dnsmasq']['tftpboot'] =$_POST['tftpboot'];} else {unset ( $config['dnsmasq']['tftpboot']);}
			  write_config();
			  // restart dnsmasq  
			 if (isset ($config['dnsmasq']['enable'])) { $savemsg = ""; $warnmess =""; dnsmasq_config(); rc_update_rcconf("dnsmasq", "enable"); rc_restart_service("dnsmasq");	}	else { 	$savemsg = ""; $warnmess =""; rc_stop_service("dnsmasq");}
			}
	}
	if (isset($_POST['apply']) && ($_POST['apply'] === "Apply changes")) { 
		$savemsg = "";
		$warnmess ="";
		dnsmasq_config();
		if (isset ($config['dnsmasq']['enable'])) { rc_update_rcconf("dnsmasq", "enable"); rc_restart_service("dnsmasq"); }	else { 	 rc_stop_service("dnsmasq"); rc_update_rcconf("dnsmasq", "disable");}
		unlink ("/var/run/dnsmasq.reload");
	}
}
$pconfig['enable'] = isset($config['dnsmasq']['enable']) ? true : false;
$pconfig['extconfig'] = isset ($config['dnsmasq']['extconfig']) ? true : false;
$pconfig['logging'] = $config['dnsmasq']['logging'];
$pconfig['noresolv'] = isset ($config['dnsmasq']['noresolv']) ? true : false;
$pconfig['startadr'] = $config['dnsmasq']['startadr'];
$pconfig['endadr'] = $config['dnsmasq']['endadr'];
$pconfig['leasecount'] = $config['dnsmasq']['leasecount'];
//$pconfig['leasetime'] = $config['dnsmasq']['leasetime'];
$pconfig['tftproot'] = $config['dnsmasq']['tftproot'];
$pconfig['tftpboot'] = $config['dnsmasq']['tftpboot'];
$pconfig['enabletftp'] = isset ($config['dnsmasq']['enabletftp']) ? true : false;
out:
$pgtitle = array(gettext("Extensions"),gettext("DNSMASQ"));
include("fbegin.inc");?>
<style>
.listhdrr1_dnsmasq_good {
	background: #BBBBBB url("ext/dnsmasq/gradient_listhdr1_good.png") repeat-x scroll left top;
	}
.listhdrr1_dnsmasq_bad {
	background: #BBBBBB url("ext/dnsmasq/gradient_listhdr1_bad.png") repeat-x scroll left top;
	}
.listhdrr1_dnsmasq_good .listhdrr1_dnsmasq_bad {
	border-right: 1px solid #999999;
	vertical-align: top;
	padding-right: 16px;
	padding-left: 6px;
	font-weight: bold;
	border-bottom: 1px solid #999999;
	font-size: 11px;
	padding-top: 5px;
	padding-bottom: 5px;
.listhdrr1_dnsmasq_good a .listhdrr1_dnsmasq_bad a {
	color: #000000;
}
</style>
<script type="text/javascript">
<!--
function enable_tftp() {
	switch(document.iform.enabletftp.checked) {
		case false:
			showElementById('tftproot_tr','hide');
			break;
		case true:
			showElementById('tftproot_tr','show');
			break;
	}
}
$(document).ready(function () {
    setInterval(function() {
        $.get("ext/dnsmasq/extensions_dnsmasq_check.php", function (result) {
            $('#loaddiv').html(result);
        });
    }, 2000);
});
//-->
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabact"><a href="extensions_dnsmasq_server.php"><span>Main</span></a></li>
			<li class="tabinact"><a href="extensions_dnsmasq_conf.php"><span>config</span></a></li>
			<li class="tabinact"><a href="extensions_dnsmasq_clients.php"><span>Client table</span></a></li>
			<li id="tabinact" class="tabinact"><a href="extensions_dnsmasq_log.php"><span>Log</span></a></li>
		</ul>
	</td></tr>
	<tr>
	<form action="extensions_dnsmasq_server.php" method="post" name="iform" id="iform">
		<td class="tabcont">
		<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
<?php if ($warnmess) {dnsmasq_warning_box($warnmess); } ?>
<?php if ($savemsg) {print_info_box($savemsg); $savemsg = ""; } ?>
			<?php if (is_file("/var/run/dnsmasq.reload"))  print_config_change_box() ?>
		<table width="100%" border="0" cellpadding="5" cellspacing="0">
		<!-- <table width="100%" border="0" cellpadding="5" cellspacing="0"> -->
		<?php html_titleline_checkbox("enable", gettext("Dynamic Host Configuration Protocol"), $pconfig['enable'], gettext("Enable"), "enable_change(false)");?>
		<?php // html_titleline_checkbox("enable", gettext("Dynamic Host Configuration Protocol"), isset($pconfig['enable']) ? true : false, gettext("Enable"), "enable_change(false)");?>
		<tr>
			<td width="22%" valign="top" class="vncell"><?=gettext("Hosts");?></td>
			<td width="78%" class="vtable">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<div id="loaddiv"> 
			</div>
			</table>
			</td>
		</tr>
		<?php html_inputbox("startadr", gettext("DHCP range - start"), $pconfig['startadr'], gettext("Choice start adress for DHCP hosts"), false, 16,false);?>
		<?php html_inputbox("endadr", gettext("DHCP range - end"), $pconfig['endadr'], gettext("Choice end adress for DHCP hosts"), false, 16,false);?>
		<?php html_inputbox("leasecount", gettext("How leases allow"), !empty($pconfig['leasecount']) ? $pconfig['leasecount'] : "150", gettext("Set the limit on DHCP leases, the default is 150"), false, 16,false);?>
		<?php html_checkbox("extconfig", gettext("Allow external config"), $pconfig['extconfig'], gettext("Allow support for external config files. Config files may have any name, exclude *.bak and placed into <b>".$config['dnsmasq']['rootfolder']."conf</b> folder"),"","","");?>
		<?php html_checkbox("noresolv", gettext("No read /etc/resolv.conf"), $pconfig['noresolv'], gettext("No read resolver file. This option may be checked, if need AD integration or define nameservers over scripts"),"","","");?>
		<?php html_combobox("logging", gettext("Log configuration"), $pconfig['logging'], array("mini" => gettext("System only"), "dhcp" => gettext("System+DHCP queries"), "all" => gettext("DNS+DHCP+Systems")), "", false, false, "" );?>
		<?php html_inputbox("tftpboot", gettext("Boot kernel name"), $pconfig['tftpboot'], gettext("Define first boot kernel name"), false, 60,false);?>
		<?php html_separator(); ?>
		<?php html_titleline_checkbox("enabletftp", gettext("Built-in tftp server"), !empty($pconfig['enabletftp']) ? true : false, gettext("Enable"), "enable_tftp(false)");?>
		<?php html_filechooser("tftproot", gettext("TFTP root folder"), $pconfig['tftproot'], gettext("Use tftp root folder"), !empty( $pconfig['tftproot']) ? $pconfig['tftproot'] : $config['dnsmasq']['rootfolder'] . "tftproot/", true, 60);?>
		<tr><td><div id="submit">
			<input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>" />
			</div>
		</td></tr>
	</table>
	</td>
	<?php include("formend.inc");?>
	</form>
	</tr>
</table>
<script type="text/javascript">
<!--
enable_tftp();
enable_booting();
//-->
</script>
<?php include("fend.inc"); ?>
