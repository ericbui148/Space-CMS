<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;

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
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminUsers&amp;action=Index"><?php __('menuUsers'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminUsers&amp;action=Create"><?php __('lblAddUser'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminUsers&amp;action=Update&amp;id=<?php echo $tpl['arr']['id']; ?>"><?php __('lblUpdateUser'); ?></a></li>
		</ul>
	</div>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminUsers&amp;action=Update" method="post" id="frmUpdateUser" class="form form">
		<input type="hidden" name="user_update" value="1" />
		<input type="hidden" name="id" value="<?php echo (int) $tpl['arr']['id']; ?>" />
		<p>
			<label class="title"><?php __('lblRole'); ?></label>
			<?php
			if ((int) $tpl['arr']['id'] !== 1)
			{
				?>
				<span class="inline_block">
					<select name="role_id" id="role_id" class="form-field required">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach ($tpl['role_arr'] as $v)
						{
							?><option value="<?php echo $v['id']; ?>"<?php echo $tpl['arr']['role_id'] == $v['id'] ? ' selected="selected"' : NULL; ?>><?php echo stripslashes($v['role']); ?></option><?php
						}
						?>
					</select>
				</span>
				<?php
			} else {
				?>
				<span class="left">
				<?php
				foreach ($tpl['role_arr'] as $v)
				{
					if ($tpl['arr']['role_id'] == $v['id'])
					{
						echo stripslashes($v['role']);
						break;
					}
				}
				?>
				</span>
				<input type="hidden" name="role_id" value="1" />
				<?php
			}
			?>
		</p>
		<p>
			<label class="title"><?php __('email'); ?></label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-email"></abbr></span>
				<input type="text" name="email" id="email" class="form-field required email w200" value="<?php echo SanitizeComponent::html($tpl['arr']['email']); ?>" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('pass'); ?></label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-password"></abbr></span>
				<input type="text" name="password" id="password" class="form-field required w200" value="<?php echo SanitizeComponent::html($tpl['arr']['password']); ?>" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblName'); ?></label>
			<span class="inline_block">
				<input type="text" name="name" id="name" value="<?php echo SanitizeComponent::html($tpl['arr']['name']); ?>" class="form-field w250 required" />
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblPhone'); ?></label>
			<span class="form-field-custom form-field-custom-before">
				<span class="form-field-before"><abbr class="form-field-icon-phone"></abbr></span>
				<input type="text" name="phone" id="phone" value="<?php echo SanitizeComponent::html($tpl['arr']['phone']); ?>" class="form-field w200" placeholder="(123) 456-7890"/>
			</span>
		</p>		
		<p>
			<label class="title"><?php __('lblStatus'); ?></label>
			<?php
			if ((int) $tpl['arr']['id'] !== 1)
			{
				?>
				<span class="inline_block">
					<select name="status" id="status" class="form-field required">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach (__('u_statarr', true) as $k => $v)
						{
							?><option value="<?php echo $k; ?>"<?php echo $k == $tpl['arr']['status'] ? ' selected="selected"' : NULL; ?>><?php echo $v; ?></option><?php
						}
						?>
					</select>
				</span>
				<?php
			} else {
				$status = __('u_statarr', true)
				?>
				<span class="left"><?php echo @$status[$tpl['arr']['status']]; ?></span>
				<input type="hidden" name="status" value="T" />
				<?php
			}
			?>
		</p>
		<p>
			<label class="title"><?php __('lblUserCreated'); ?></label>
			<span class="left"><?php echo date($tpl['option_arr']['o_date_format'], strtotime($tpl['arr']['created'])); ?>, <?php echo date("H:i", strtotime($tpl['arr']['created'])); ?></span>
		</p>
		<p>
			<label class="title"><?php __('lblIp'); ?></label>
			<span class="left"><?php echo $tpl['arr']['ip']; ?></span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave', false, true); ?>" class="button" />
		</p>
	</form>
	
	<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.email_taken = "<?php __('email_taken', false, true); ?>";
	</script>
	<?php
}
?>