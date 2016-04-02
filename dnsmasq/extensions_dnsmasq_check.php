<?php
require("auth.inc");
require("guiconfig.inc");

$pgtitle = array(_THEBRIG_EXTN , _THEBRIG_TITLE);

//=========================================================================================================================================================
// The entirety of this next section (all the way to the /head) is copied out of the fbegin.inc file
// normally used to construct the larger portion of the nas4free framing, including all the title bars and whatnot
//=========================================================================================================================================================
function gentitle($title) {
	$navlevelsep = "|"; // Navigation level separator string.
	return join($navlevelsep, $title);
}

function genhtmltitle($title) {
	return system_get_hostname() . " - " . gentitle($title);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=system_get_language_code();?>" lang="<?=system_get_language_code();?>">
<head>
	<title><?=htmlspecialchars(genhtmltitle($pgtitle));?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=system_get_language_codeset();?>" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<?php if (isset($pgrefresh) && $pgrefresh):?>
	<meta http-equiv="refresh" content="<?=$pgrefresh;?>" />
	<?php endif;?>
	<link href="gui.css" rel="stylesheet" type="text/css" />
	<link href="navbar.css" rel="stylesheet" type="text/css" />
	<link href="tabs.css" rel="stylesheet" type="text/css" />	
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/gui.js"></script>
<?php
	if (isset($pglocalheader) && !empty($pglocalheader)) {
		if (is_array($pglocalheader)) {
			foreach ($pglocalheader as $pglocalheaderv) {
		 		echo $pglocalheaderv;
				echo "\n";
			}
		} else {
			echo $pglocalheader;
			echo "\n";
		}
	}
	
	//=========================================================================================================================================================
	// nearly the end of the borrowed bits
	//=========================================================================================================================================================
	?>
</head>
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

<body>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
								
								<tr>
									
								
									<td width="5%" class="listhdrlr">&nbsp;</td>
									
									<td width="12%" class="<?php if (is_file("/var/run/dnsmasq.stamp")) echo "listhdrr1_dnsmasq_good"; else echo "listhdrr1_dnsmasq_bad"?>"><?=gettext("MAC");?></td>
									<td width="12%" class="<?php if (is_file("/var/run/dnsmasq.stamp")) echo "listhdrr1_dnsmasq_good"; else echo "listhdrr1_dnsmasq_bad"?>"><?=gettext("IP adress");?></td>
									<td width="12%" class="<?php if (is_file("/var/run/dnsmasq.stamp")) echo "listhdrr1_dnsmasq_good"; else echo "listhdrr1_dnsmasq_bad"?>"><?=gettext("Hostname");?></td>
									<td width="12%" class="<?php if (is_file("/var/run/dnsmasq.stamp")) echo "listhdrr1_dnsmasq_good"; else echo "listhdrr1_dnsmasq_bad"?>"><?=gettext("TFTP server");?></td>
									<td width="12%" class="<?php if (is_file("/var/run/dnsmasq.stamp")) echo "listhdrr1_dnsmasq_good"; else echo "listhdrr1_dnsmasq_bad"?>"><?=gettext("Lease time");?></td>
									<td width="10%" class="<?php if (is_file("/var/run/dnsmasq.stamp")) echo "listhdrr1_dnsmasq_good"; else echo "listhdrr1_dnsmasq_bad"?>"><?=gettext("Router");?></td>
									<td width="15%" class="<?php if (is_file("/var/run/dnsmasq.stamp")) echo "listhdrr1_dnsmasq_good"; else echo "listhdrr1_dnsmasq_bad"?>"><?=gettext("DNS server");?></td>

									<td  class="list"></td>
								
								</tr>
								
								
								<?php if (is_array($config['dnsmasq']['hosts'])) {
array_sort_key($config['dnsmasq']['hosts'], "hostno");
$a_hosts = &$config['dnsmasq']['hosts'];
foreach ($a_hosts as $host):?>
								<?php $notificationmode = updatenotify_get_mode("dnsmasq", $host['uuid']);?>							
								<tr>
									<td width="5%" class="listr"><?=htmlspecialchars($host['hostno']);?></td>
									<td width="12%" class="listr"><?=htmlspecialchars ( $host['macaddr']);?></td>
									<td width="12%" class="listr"><?=htmlspecialchars($host['ipadress']);?></td>
									<td width="12%" class="listr"><?=htmlspecialchars($host['hostname']);?></td>
									<td width="12%" class="listrd"><?php if ( isset ($config['dnsmasq']['enabletftp']) ||  isset ($config['tftpd']['enable'])) echo $config['interfaces']['lan']['ipaddr']; else echo "Not enabled";?>&nbsp;</td>
									<td width="12%" class="listrd"><?=htmlspecialchars($host['leasetime']);?></td>
									<td width="10%" class="listrd"><?=htmlspecialchars($config['interfaces']['lan']['gateway']);?></td>
									<td width="15%" class="listrd"><?=implode(" ", $config['system']['dnsserver']);?></td>

									<?php if (UPDATENOTIFY_MODE_DIRTY != $notificationmode):?>
											
									<td valign="middle" nowrap="nowrap" class="list">
										<a href="extensions_dnsmasq_hosts.php?act=edit&amp;uuid=<?=$host['uuid'];?>"><img src="edit.png" title="<?=gettext("Edit host");?>" border="0" alt="<?=gettext("Edit host");?>" /></a>&nbsp;
										<a href="extensions_dnsmasq_hosts.php?act=del&amp;uuid=<?=$host['uuid'];?>" onclick="return confirm('<?=gettext("Do you really want to delete this entry?");?>')"><img src="delete.png" title="<?=gettext("Delete host");?>" border="0" alt="<?=gettext("Delete host");?>" /></a>
										</td>	
									<?php else:?>									
								</tr>
<?php endif;?>
<?php endforeach; } ?>
								<tr>
									<td class="list" colspan="8"></td>
									<td class="list">
										<a href="extensions_dnsmasq_hosts.php?act=new"><img src="add.png" title="<?=gettext("Add host");?>" border="0" alt="<?=gettext("Add host");?>" /></a>
									</td>

								</tr>

						</table>
</body>
</html>
