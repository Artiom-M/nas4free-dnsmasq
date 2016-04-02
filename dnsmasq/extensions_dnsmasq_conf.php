<?php
/*
extensions_dnsmasq_conf.php
*/
ob_start();
require("auth.inc");
require("guiconfig.inc");
if (!isset($config['dnsmasq']) || !is_array($config['dnsmasq'])) $config['dnsmasq']=array();
if (!isset($config['dnsmasq']['rootfolder']) ) {
		if(file_exists( '/tmp/dnsmasq.tmp' )  ) {
			$pconfig['rootfolder'] = rtrim( file_get_contents('/tmp/dnsmasq.tmp') );
			if ( $pconfig['rootfolder'][strlen($pconfig['rootfolder'])-1] != "/")  { 
				$pconfig['rootfolder'] = $pconfig['rootfolder'] . "/";	
				}
		}	else { 		
		$input_errors[] = "Install procedure filed, please reinstall"; 	
		} 
	} else { $pconfig['rootfolder'] = $config['dnsmasq']['rootfolder'] ;}
	
if ($_POST) {
	
	unset($input_errors);
	$pconfig = $_POST;
	if ( $pconfig['Submit'] && $pconfig['Submit'] =="Remove") {
		
		// we want to remove dnsmasq
		
		$i = 0;
 		if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
 			for ($i; $i < count($config['rc']['postinit']['cmd']); $i++) {
 			if (true == ($cnid = preg_match('/start_dnsmasq/', $config['rc']['postinit']['cmd'][$i]))) unset($config['rc']['postinit']['cmd'][$i]);	else {}
				} 
		}
		foreach ( glob( "{$config['dnsmasq']['rootfolder']}conf/ext/dnsmasq/*.php" ) as $file ) {
 			$file = str_replace("{$config['dnsmasq']['rootfolder']}conf/ext/dnsmasq", "/usr/local/www", $file);
 			if ( is_link( $file ) ) { unlink( $file ); 	} 
 		} 
		foreach ( glob( "/usr/local/www/ext/dnsmasq/*" ) as $file ) { unlink( $file ); 	}
		unlink_if_exists( "/usr/local/www/ext/dnsmasq" );
		unlink_if_exists( "/usr/local/sbin/dnsmasq" ); 
		unlink_if_exists("/etc/rc.d/dnsmasq" );
		unset ($config['dnsmasq']);
		write_config();
				// Browse back to the main page
		header("Location: /");
		exit;
	}
	else { 
		if ( $pconfig['rootfolder'][strlen($pconfig['rootfolder'])-1] != "/")  { $pconfig['rootfolder'] = $pconfig['rootfolder'] . "/"; } else {}
		if ( !is_dir( $pconfig['rootfolder'] ) && !isset($pconfig['remove']) ) {  $input_errors[] = "Not existent folder"; 	}
		else if ( !is_writable( $pconfig['rootfolder'] ) && !isset($pconfig['remove']) ){ $input_errors[] = "Not writible folder"; 	}
	
	
	if ( !$input_errors ){ 
	$config['dnsmasq']['rootfolder'] = $pconfig['rootfolder'];
	// Add startup command
	$i = 0;
	if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
		for ($i; $i < count($config['rc']['postinit']['cmd']); $i++) {
			if (false == ($cnid = preg_match('/start_dnsmasq/', $config['rc']['postinit']['cmd'][$i]))) {} else { break;}			
		 	}
			$config['rc']['postinit']['cmd'][$i] = "/usr/local/bin/php-cgi {$config['dnsmasq']['rootfolder']}sbin/start_dnsmasq.php";
		} 
	write_config();
	unlink_if_exists("/tmp/dnsmasq.tmp");
	if ( !is_link( "/etc/rc.d/dnsmasq" )) {symlink ( $config['dnsmasq']['rootfolder']."sbin/dnsmasq.d","/etc/rc.d/dnsmasq");} else {}
	if ( !is_link ( "/usr/local/sbin/dnsmasq") ) symlink ( $config['dnsmasq']['rootfolder']."sbin/dnsmasq","/usr/local/sbin/dnsmasq"); else {}
	if (!is_dir ( $config['dnsmasq']['rootfolder']."tftproot") ) mkdir ( $config['dnsmasq']['rootfolder']."tftproot", 0777); else {}
	if (!is_dir ( $config['dnsmasq']['rootfolder']."conf") ) mkdir ( $config['dnsmasq']['rootfolder']."conf", 0777); else {}
	header ("Location: /extensions_dnsmasq_server.php");
	}
}
}
$pgtitle = array(gettext("Extensions"),gettext("DNSMASQ"), gettext("config"));
include("fbegin.inc"); ?>
<script language="JavaScript">

function message(obj) {
	
		alert('If you want to uninstall the DNSMASQ, please make sure that all files have been removed');

		return true;
}
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">

	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="extensions_dnsmasq_server.php"><span>Main</span></a></li>
			
			<li class="tabact"><a href="extensions_dnsmasq_conf.php"><span>config</span></a></li>
			<li class="tabinact"><a href="extensions_dnsmasq_clients.php"><span>Client table</span></a></li>
			<li class="tabinact"><a href="extensions_dnsmasq_log.php"><span>Log</span></a></li>
		</ul>
	</td></tr>
	<tr><td class="tabcont">
	<?php
if ( $input_errors ) { 	print_input_errors( $input_errors ); }
// This will alert the user to unsaved changes, and prompt the changes to be saved.
else if ($savemsg) { print_info_box($savemsg); }
?>	
		<form action="extensions_dnsmasq_conf.php" method="post" name="iform" id="iform">
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<?php html_titleline(gettext("Extension dnsmasq basic setting"));?>
		<?php html_inputbox("rootfolder", "Extension working folder", $pconfig['rootfolder'], "folder where extension sets", true, 50);
		html_separator();
		if (!isset($config['dnsmasq']['rootfolder']) ) { ?>
		<tr><td width="22%" valign="top" class="vncell">&nbsp;Action</td>
			<td width="78%" class="vtable">
			 	<input name="Submit" type="submit" class="formbtn" value="Confirm">
			</td>
		</tr>
		<?php } else { ?>
			<tr><td width="22%" valign="top" class="vncell">&nbsp;Action</td>
			<td width="78%" class="vtable">
				<input name="Submit" type="submit" class="formbtn" value="Remove" onclick="return message(this);">
			</td>
		</tr>
		<?php html_separator();?>
		</table>
	<?php include("formend.inc");	?>
</form>	
		<form action="exec.php" method="post" name="iform" id="iform">
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
		
		
		<?php 
		$connected = @fsockopen("www.github.com", 80); 
		if ( $connected ) {
			fclose($connected);
			$ctx = stream_context_create(['ssl' => ["verify_peer"=>false,"verify_peer_name"=>false,]]);
			//$arrContextOptions=array( "ssl"=>array( "verify_peer"=>false, "verify_peer_name"=>false ),);  
			$gitserverfile = file_get_contents("https://raw.githubusercontent.com/alexey1234/nas4free-dnsmasq/master/dnsmasq_install.sh", false, $ctx);
			file_put_contents("/tmp/dnsmasq_install.sh", $gitserverfile);
			if (1==preg_match("/^#Ver.+/m", $gitserverfile,$matched)) {
				$gitversion = preg_split ( "/\s/", $matched[0]);
			} else {
				$gitversion[] = "ERROR connect to github";
			}
		$localfile = file_get_contents($config['dnsmasq']['rootfolder']."dnsmasq_install.sh");
		if (1==preg_match("/^#Ver.+/m", $localfile,$matched1)) {
				$localversion = preg_split ( "/\s/", $matched1[0]);
			} else {
				$localversion[] = "ERROR find local installator";
			}
			
			?> 
	
			<tr><td width="22%" valign="top" class="vncell">&nbsp;Current status</td>
			<td width="78%" class="vtable" >The latest version on GitHub is: <?=$gitversion[1];?><br />Your version is: <?=$localversion[1];?><br />
	<input name="txtCommand" type="hidden" value="<?="sh /tmp/dnsmasq_install.sh &";?>" />
			 	<input name="Submit" type="submit" class="formbtn" value="Update">
			</td>
		</tr>
		<?php } } ?>
	</table>
	<?php include("formend.inc");	?>
</form>


</td></tr>
</table>
