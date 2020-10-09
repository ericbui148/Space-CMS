<?php
use App\Controllers\Components\UtilComponent;

if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			UtilComponent::printNotice(NULL, $status[2]);
			break;
	}
} else {
	$jqDateFormat = UtilComponent::jqDateFormat($tpl['option_arr']['o_date_format']);
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSubscribers&amp;action=Index"><?php __('menuSubscribers'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSubscribers&amp;action=Import"><?php __('lblImport'); ?></a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice(__('infoUpdateSubscriberTitle', true), __('infoUpdateSubscriberBody', true)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSubscribers&amp;action=Update" method="post" id="frmUpdateSubscriber" class="form form" autocomplete="off">
		<input type="hidden" name="subscriber_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
		<p>
			<label class="title"><?php __('lblCreatedDateTime'); ?></label>
			<span class="inline_block">
				<label class="content"><?php echo UtilComponent::formatDate(date('Y-m-d', strtotime($tpl['arr']['created'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . UtilComponent::formatTime(date('H:i:s', strtotime($tpl['arr']['created'])), 'H:i:s', $tpl['option_arr']['o_time_format']); ?></label>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblModifiedDateTime'); ?></label>
			<span class="inline_block">
				<label class="content"><?php echo !empty($tpl['arr']['modified']) ? UtilComponent::formatDate(date('Y-m-d', strtotime($tpl['arr']['modified'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . UtilComponent::formatTime(date('H:i:s', strtotime($tpl['arr']['modified'])), 'H:i:s', $tpl['option_arr']['o_time_format']) : __('lblNA', true); ?></label>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblIp'); ?></label>
			<span class="inline_block">
				<label class="content"><?php echo $tpl['arr']['ip']; ?></label>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblMessageSent'); ?></label>
			<span class="inline_block">
				<label class="content">
					<?php 
					if($tpl['messages_sent'] > 0)
					{
						?>
						<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminQueues&amp;action=Index&amp;subscriber_id=<?php echo $tpl['arr']['id'];?>"><?php echo $tpl['messages_sent'];?></a>
						<?php
					}else{
						echo $tpl['messages_sent'];
					}
					?>
				</label>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblGroup'); ?></label>
			<span class="inline_block">
				<select id="group_id" name="group_id[]" class="form-field w300 required" data-placeholder="--<?php __('lblChoose'); ?>--" multiple="multiple">
					<?php
					foreach ($tpl['group_arr'] as $k => $v)
					{
						?><option value="<?php echo $v['id']; ?>" <?php echo in_array($v['id'], $tpl['group_id_arr']) ? 'selected="selected"' : null; ?>><?php echo $v['group_title']; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblFirstName'); ?></label>
			<span class="inline_block">
				<input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['first_name'])); ?>" class="form-field w200 required" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblLastName'); ?></label>
			<span class="inline_block">
				<input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['last_name'])); ?>" class="form-field w200" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblEmail'); ?></label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-email"></abbr></span>
				<input type="text" name="email" id="email" class="form-field w300 email required" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['email'])); ?>" placeholder="info@domain.com" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblPhone'); ?></label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-phone"></abbr></span>
				<input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['phone'])); ?>" class="form-field w150" placeholder="(123) 456-7890" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblWebsite'); ?></label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-url"></abbr></span>
				<input type="text" name="website" id="website" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['website'])); ?>" class="form-field w300 url" placeholder="http://www.domain.com"  />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblCompanyName'); ?></label>
			<span class="inline_block">
				<input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['company_name'])); ?>" class="form-field w350" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblGender'); ?></label>
			<span class="inline_block">
				<select name="gender" id="gender" class="form-field">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach (__('genderarr', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>" <?php echo $k == $tpl['arr']['gender'] ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblAge'); ?></label>
			<span class="inline_block">
				<input type="text" name="age" id="age" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['age'])); ?>" class="form-field field-int w60" value="" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblBirthday'); ?></label>
			<span class="inline_block">
				<span class="form-field-custom form-field-custom-after">
					<input type="text" name="birthday" id="birthday" class="form-field pointer w100 datepick-birthday" value="<?php echo !empty($tpl['arr']['birthday']) ? UtilComponent::formatDate($tpl['arr']['birthday'], "Y-m-d", $tpl['option_arr']['o_date_format']) : null; ?>"  readonly="readonly" rel="1" rev="<?php echo $jqDateFormat; ?>" />
					<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
				</span>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblAddress'); ?></label>
			<span class="inline_block">
				<textarea id="address" name="address" class="textarea h80 w450"><?php echo htmlspecialchars(stripslashes($tpl['arr']['address'])); ?></textarea>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblCity'); ?></label>
			<span class="inline_block">
				<input type="text" name="city" id="city" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['city'])); ?>" class="form-field w200" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblState'); ?></label>
			<span class="inline_block">
				<input type="text" name="state" id="state" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['state'])); ?>" class="form-field w200" />
			</span>
		</p>
		
		<p>
			<label class="title"><?php __('lblCountry'); ?></label>
			<span class="inline_block">
				<select id="country_id" name="country_id" class="form-field w350">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach ($tpl['country_arr'] as $k => $v)
					{
						?><option value="<?php echo $v['id']; ?>" <?php echo $v['id'] == $tpl['arr']['country_id'] ? 'selected="selected"' : null; ?>><?php echo $v['country_title']; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		
		<p>
			<label class="title"><?php __('lblZip'); ?></label>
			<span class="inline_block">
				<input type="text" name="zip" id="zip" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['zip'])); ?>" class="form-field w150" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblStatus'); ?></label>
			<span class="inline_block">
				<select name="subscribed" id="subscribed" class="form-field required">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach (__('subscribed_arr', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>" <?php echo $k == $tpl['arr']['subscribed'] ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminSubscribers&action=Index';" />
		</p>
	</form>
	
	<script type="text/javascript">
		var myLabel = myLabel || {};
		myLabel.email_taken = "<?php __('email_taken'); ?>";
		myLabel.group_required = "<?php __('group_required'); ?>";
	</script>
	<?php
}
?>