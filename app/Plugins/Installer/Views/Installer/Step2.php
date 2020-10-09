<?php
include dirname(__FILE__) . '/elements/progress.php';
$STORAGE = &$_SESSION[$controller->defaultInstaller];
?>
<div class="i-wrap">
	<?php
	if (isset($_GET['err']) && !empty($_GET['err']) && isset($_SESSION[$controller->defaultErrors][$_GET['err']]))
	{
		?>
		<div class="i-status i-status-error">
			<div class="i-status-icon"><abbr></abbr></div>
			<div class="i-status-txt">
				<h2>Installation error!</h2>
				<p class="t10"><?php echo @$_SESSION[$controller->defaultErrors][$_GET['err']]; ?></p>
			</div>
		</div>
		<?php
	}
	?>
		
	<p>Please enter MYSQL login details for your server. If you do not know these please contact your hosting company and ask them to provide you with correct details.</p>
	<p>Alternatively, you can send us access to your hosting account control panel (the place where you manage your hosting account) and we can create MySQL database and user for you.</p>
	
	<form action="index.php?controller=Installer&amp;action=Step3&amp;install=1" method="post" id="frmStep2" class="i-form">
		<input type="hidden" name="step2" value="1" />
		<table cellpadding="0" cellspacing="0" class="i-table t20">
			<thead>
				<tr>
					<th>MySQL Login Details</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<p>
							<label class="i-title">Hostname <span class="i-red">*</span></label>
							<input type="text" tabindex="1" name="hostname" class="form-field w200 required" value="<?php echo isset($STORAGE['hostname']) ? htmlspecialchars($STORAGE['hostname']) : 'localhost'; ?>" />
						</p>
						<p>
							<label class="i-title">Username <span class="i-red">*</span></label>
							<input type="text" tabindex="2" name="username" class="form-field w200 required" value="<?php echo isset($STORAGE['username']) ? htmlspecialchars($STORAGE['username']) : NULL; ?>" />
						</p>
						<p>
							<label class="i-title">Password</label>
							<input type="text" tabindex="3" name="password" class="form-field w200" value="<?php echo isset($STORAGE['password']) ? htmlspecialchars($STORAGE['password']) : NULL; ?>" />
						</p>
						<p>
							<label class="i-title">Database <span class="i-red">*</span></label>
							<input type="text" tabindex="4" name="database" class="form-field w200 required" value="<?php echo isset($STORAGE['database']) ? htmlspecialchars($STORAGE['database']) : NULL; ?>" />
						</p>
						<p>
							<label class="i-title">Table prefix</label>
							<input type="text" tabindex="5" name="prefix" class="form-field w200" value="<?php echo isset($STORAGE['prefix']) ? htmlspecialchars($STORAGE['prefix']) : NULL; ?>" />
							<span style="display: block;text-indent: 120px;">* you can leave that blank or enter table prefix which will be added to all MySQL tables names</span>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
	
		<div class="t20">
			<p class="float_left pt5">Need help? <a href="http://www.phabbers.com/contact.php" target="_blank">Contact us</a></p>
			<input type="submit" tabindex="6" value="Continue &raquo;" class="button float_right l5" />
			<input type="button" tabindex="7" value="&laquo; Back" class="button float_right" onclick="window.location='index.php?controller=Installer&amp;action=Step1'" />
			<br class="clear_both" />
		</div>
	</form>
</div>