<?php
use Core\Framework\Components\SanitizeComponent;
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
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminForms&amp;action=Update" method="post" id="frmUpdateForm" class="form form">
		<input type="hidden" name="form_update" value="1" />
		<input type="hidden" id="id" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
		<input type="hidden" name="tab_id" value="<?php echo isset($_GET['tab_id']) && !empty($_GET['tab_id']) ? $_GET['tab_id'] : 'tabs-1'; ?>" />		
		<div id="tabs">
		
			<ul>
				<li><a href="#tabs-1">Form fields</a></li>
				<li><a href="#tabs-2"><?php __('lblFormDetails'); ?></a></li>
				<li><a href="#tabs-3"><?php __('lblFormStyles'); ?></a></li>
				<li><a href="#tabs-4"><?php __('lblAutoResponder'); ?></a></li>
			</ul>
		
			<div id="tabs-1">
				<?php
				UtilComponent::printNotice(__('infoFormFieldTitle', true, false), "Click on field type in the box on the right and it will be added to your form. You can mouse over each of the fields and edit, move or delete selected field."); 
				?>
				<div id="designer_panel" class="designer-panel">
					<?php
					include VIEWS_PATH . 'AdminForms/elements/form.php'; 
					?>
				</div>
				<div id="toolbox_panel" class="toolbox-panel">
					<?php
					include VIEWS_PATH . 'AdminForms/elements/toolbox.php'; 
					?>
				</div>
				<div id="property_panel" class="property-panel">
					<?php
					include VIEWS_PATH . 'AdminForms/elements/property.php'; 
					?>
				</div>
			</div><!-- tabs-1 -->
			<div id="tabs-2">
				<?php
				UtilComponent::printNotice(__('infoFormDetailTitle', true, false), __('infoFormDetailBody', true, false)); 
				?>
				<p>
					<label class="title"><?php __('lblFormName', false, true); ?></label>
					<span class="inline_block">
						<input type="text" name="form_title" id="form_title" value="<?php echo SanitizeComponent::html($tpl['arr']['form_title']);?>" class="form-field w500 required" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblDateFormat', false, true); ?></label>
					<span class="inline_block">
						<select name="date_format" id="date_format" class="form-field w200">
							<?php
							foreach (UtilComponent::getDateFormat() as $k => $v)
							{
								?><option value="<?php echo $k; ?>" <?php echo $tpl['arr']['date_format'] == $k ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
							}
							?>
						</select>
						<a href="#" class="form-langbar-tip listing-tip" title="<?php __('lblDateFormatTip', false, true); ?>"></a>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblSendTo'); ?></label>
					<span class="inline_block">
						<select id="user_id" name="user_id[]" class="form-field w400" data-placeholder="--<?php __('lblChoose'); ?>--" multiple="multiple">
							<?php
							foreach ($tpl['user_arr'] as $k => $v)
							{
								?><option value="<?php echo $v['id']; ?>" <?php echo in_array($v['id'], $tpl['user_id_arr']) ? 'selected="selected"' : null; ?>><?php echo $v['name']; ?></option><?php
							}
							?>
						</select>
						<a href="#" class="form-langbar-tip center-langbar-tip" title="<?php __('lblSendToTip'); ?>"></a>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblEmailSubject', false, true); ?></label>
					<span class="inline_block">
						<input type="text" name="subject" id="subject" value="<?php echo SanitizeComponent::html($tpl['arr']['subject']);?>" class="form-field w400" />
						<a href="#" class="form-langbar-tip center-langbar-tip" title="<?php __('lblEmailSubjectTip'); ?>"></a>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblEmailFormat', false, true); ?></label>
					<span class="inline_block">
						<select name="email_type" id="email_type" class="form-field w100">
							<?php
							foreach (__('email_format', true) as $k => $v)
							{
								?><option value="<?php echo $k; ?>" <?php echo $tpl['arr']['email_type'] == $k ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
							}
							?>
						</select>
						<a href="#" class="form-langbar-tip listing-tip" title="<?php __('lblEmailFormatTip', false, true); ?>"></a>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblConfirmationOptions', false, true); ?></label>
					<span class="inline_block">
						<select name="confirm_options" id="confirm_options" class="form-field w200">
							<?php
							foreach (__('confirm_options', true) as $k => $v)
							{
								?><option value="<?php echo $k; ?>" <?php echo $tpl['arr']['confirm_options'] == $k ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
							}
							?>
						</select>
						<a href="#" class="form-langbar-tip listing-tip" title="<?php __('lblConfirmationOptionsTip', false, true); ?>"></a>
					</span>
				</p>
				<p class="confirm-message">
					<label class="title"><?php __('lblConfirmationMessage', false, true); ?></label>
					<span class="inline_block">
						<input type="text" name="confirm_message" id="confirm_message" value="<?php echo SanitizeComponent::html($tpl['arr']['confirm_message']);?>" class="form-field w400" />
					</span>
				</p>
				<p class="confirm-redirect">
					<label class="title"><?php __('lblRedirectURL', false, true); ?></label>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-url"></abbr></span>
						<input type="text" name="thankyou_page" id="thankyou_page" class="form-field w400" value="<?php echo !empty($tpl['arr']['thankyou_page']) ? htmlspecialchars(stripslashes($tpl['arr']['thankyou_page'])) : 'http://'; ?>" placeholder="http://www.domain.com"  />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblCaptchaType', false, true); ?></label>
					<span class="inline_block">
						<select name="captcha_type" id="captcha_type" class="form-field w150">
							<?php
							foreach (__('captcha_types', true) as $k => $v)
							{
								?><option value="<?php echo $k; ?>" <?php echo $tpl['arr']['captcha_type'] == $k ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblRejectLinks', false, true); ?></label>
					<span class="inline_block">
						<select name="reject_links" id="reject_links" class="form-field w100">
							<?php
							foreach (__('_yesno', true) as $k => $v)
							{
								?><option value="<?php echo $k; ?>" <?php echo $tpl['arr']['reject_links'] == $k ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
							}
							?>
						</select>
						<a href="#" class="form-langbar-tip listing-tip" title="<?php __('lblRejectLinksTip', false, true); ?>"></a>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBlockWords', false, true); ?></label>
					<span class="inline_block">
						<textarea id="block_words" name="block_words" class="form-field w400 h120"><?php echo SanitizeComponent::clean($tpl['arr']['block_words'])?></textarea>
						<a href="#" class="form-langbar-tip center-langbar-tip" title="<?php __('lblBlockWordsTip'); ?>"></a>
					</span>
				</p>				
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSave', false, true); ?>" class="button" />
				</p>
			</div><!-- tabs-2 -->
			<div id="tabs-3">
				<?php
				UtilComponent::printNotice(__('infoFormStylesTitle', true, false), __('infoFormStylesBody', true, false)); 
				
				$label_position = __('label_position', true); 
				?>
				<p>
					<label class="title"><?php __('lblLabelPosition', false, true); ?></label>
					<span class="inline_block">
						<select name="label_position" id="label_position" class="form-field w200">
							<?php
							foreach ($label_position as $k => $v)
							{
								?><option value="<?php echo $k; ?>" <?php echo $tpl['arr']['label_position'] == $k ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblFontFamily', false, true); ?></label>
					<span class="inline_block">
						<select name="font_family" id="font_family" class="form-field">
							<option value="">-- <?php __('lblChoose', false, true); ?>--</option>
							<?php
							foreach (UtilComponent::getFonts() as $k => $v)
							{
								?><option value="<?php echo $k; ?>" <?php echo $tpl['arr']['font_family'] == $k ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblFontSize', false, true); ?></label>
					<span class="inline_block">
						<select name="font_size" id="font_size" class="form-field">
							<option value="">-- <?php __('lblChoose', false, true); ?>--</option>
							<?php
							foreach (UtilComponent::getFontSizes() as $k => $v)
							{
								?><option value="<?php echo $k; ?>" <?php echo $tpl['arr']['font_size'] == $k ? 'selected="selected"' : null; ?>><?php echo $v; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblFontColor', false, true); ?></label>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" id="font_color" name="font_color" class="form-field field-color w60" value="<?php echo SanitizeComponent::html($tpl['arr']['font_color']);?>"  />
						<span class="form-field-after"></span>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBackgroundColor', false, true); ?></label>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" id="background_color" name="background_color" class="form-field field-color w60" value="<?php echo SanitizeComponent::html($tpl['arr']['background_color']);?>"  />
						<span class="form-field-after"></span>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblFieldBackgroundColor', false, true); ?></label>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" id="field_background_color" name="field_background_color" class="form-field field-color w60" value="<?php echo SanitizeComponent::html($tpl['arr']['field_background_color']);?>"  />
						<span class="form-field-after"></span>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblButtonBackgroundColor', false, true); ?></label>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" id="button_background_color" name="button_background_color" class="form-field field-color w60" value="<?php echo SanitizeComponent::html($tpl['arr']['button_background_color']);?>"  />
						<span class="form-field-after"></span>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblButtonHoverBackgroundColor', false, true); ?></label>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" id="button_hover_background_color" name="button_hover_background_color" class="form-field field-color w60" value="<?php echo SanitizeComponent::html($tpl['arr']['button_hover_background_color']);?>"  />
						<span class="form-field-after"></span>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblButtonBorderColor', false, true); ?></label>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" id="button_border_color" name="button_border_color" class="form-field field-color w60" value="<?php echo SanitizeComponent::html($tpl['arr']['button_border_color']);?>"  />
						<span class="form-field-after"></span>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblButtonHoverBorderColor', false, true); ?></label>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" id="button_hover_border_color" name="button_hover_border_color" class="form-field field-color w60" value="<?php echo SanitizeComponent::html($tpl['arr']['button_hover_border_color']);?>"  />
						<span class="form-field-after"></span>
					</span>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSave', false, true); ?>" class="button float_left r5" />
					<a href="preview.php?id=<?php echo $tpl['arr']['id'];?>" target="_blank" class="no-decor CF-preview-form"><?php __('btnPreviewForm', false, true); ?></a>
				</p>
			</div><!-- tabs-3 -->
			<div id="tabs-4">
				<?php
				UtilComponent::printNotice(__('infoAutoResponseTitle', true, false), __('infoAutoResponseBody', true, false)); 
				?>
				<p>
					<label class="title"><?php __('lblAutoSubject', false, true); ?></label>
					<span class="inline_block">
						<input type="text" name="auto_subject" id="auto_subject" value="<?php echo SanitizeComponent::html($tpl['arr']['auto_subject']);?>" class="form-field w500" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblAutoMessage', false, true); ?></label>
					<span class="inline_block">
						<textarea name="auto_message" id="auto_message" class="form-field mceEditor" ><?php echo SanitizeComponent::html($tpl['arr']['auto_message']);?></textarea>
					</span>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSave', false, true); ?>" class="button" />
				</p>
			</div><!-- tabs-3 -->
			
		</div> <!-- #tabs -->
	</form>
	
	<div id="dialogDeleteField" style="display: none" title="<?php __('lblDeleteField');?>"><?php __('lblDeleteFieldConfirm');?></div>
	<div id="dialogView" style="display: none" title="<?php echo $tpl['arr']['form_title'];?>"></div>
	<div id="record_id" style="display: none"></div>
	<div id="form_id" style="display: none"><?php echo $tpl['arr']['id']; ?></div>
	<div id="field_type" style="display: none"></div>
	<script type="text/javascript">
		var Grid = Grid || {};
		Grid.roleId = <?php echo (int) $_SESSION[$controller->defaultUser]['role_id']; ?>;
		var myLabel = myLabel || {};
		myLabel.form_id = "<?php echo $tpl['arr']['id']; ?>";
		myLabel.valid_url = "<?php __('lblInvalidUrl', false, true); ?>";
		myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
		myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
	</script>
	
	<?php
	if (isset($_GET['tab_id']) && !empty($_GET['tab_id']))
	{
		$tab_id = explode("-", $_GET['tab_id']);
		$tab_id = (int) $tab_id[1] - 1;
		$tab_id = $tab_id < 0 ? 0 : $tab_id;
		?>
		<script type="text/javascript">
		(function ($) {
			$(function () {
				$("#tabs").tabs("option", "selected", <?php echo $tab_id; ?>);
			});
		})(jQuery_1_8_2);
		</script>
		<?php
	}
}
?>