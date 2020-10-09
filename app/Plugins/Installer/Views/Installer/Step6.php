<?php
include dirname(__FILE__) . '/elements/progress.php';
$STORAGE = &$_SESSION[$controller->defaultInstaller];
?>
<div class="i-wrap">

	<div class="i-status i-status-error" style="display: none">
		<div class="i-status-icon"><abbr></abbr></div>
		<div class="i-status-txt">
			<h2>Installation error!</h2>
			<p class="t10"></p>
		</div>
	</div>
		
	<table cellpadding="0" cellspacing="0" class="i-table t20">
		<thead>
			<tr>
				<th>Installation Progress</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Generate Option file</td>
				<td><span class="i-option i-option-ready"></span></td>
			</tr>
			<tr>
				<td>Create MySQL tables</td>
				<td><span class="i-option i-option-ready"></span></td>
			</tr>
		</tbody>
	</table>
	
	<div class="t20">
		<p class="float_left pt5">Need help? <a href="http://www.phabbers.com/contact.php" target="_blank">Contact us</a></p>
		<form action="index.php?controller=Installer&amp;action=Step7&amp;install=1" method="post" id="frmStep6" class="i-form float_right">
			<input type="hidden" name="step6" value="1" />
			<input type="button" tabindex="2" value="&laquo; Back" class="button" onclick="window.location='index.php?controller=Installer&amp;action=Step5'" />
			<input type="submit" tabindex="1" value="Install &raquo;" class="button" />
		</form>
		<br class="clear_both" />
	</div>
	
	<img src="http://www.stivasoft.com/trackInstall.php?version=<?php echo SCRIPT_VERSION; ?>&build=<?php echo SCRIPT_BUILD; ?>&script=<?php echo SCRIPT_ID; ?>&license_key=<?php echo @$STORAGE['license_key']; ?>" style="display: none" />
	
</div>