<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
<div class="multilang"></div>
<?php endif; ?>

<form id="frmEmailNotify" action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOptions&amp;action=Update" method="post" class="form form clear_both">
	<input type="hidden" name="options_update" value="1" />
	<input type="hidden" name="next_action" value="Index" />
	<input type="hidden" name="tab" value="<?php echo @$_GET['tab']; ?>" />
	
	<div id="tabs">
		<ul>
			<li><a href="#tabs-1"><?php __('tabEmails'); ?></a></li>
			<li><a href="#tabs-2"><?php __('tabSms'); ?></a></li>
		</ul>
		
		<div id="tabs-1">
			<?php
			$email_types = array();
			$email_types[1] = __('confirmation_client_confirmation', true);
			$email_types[2] = __('confirmation_client_payment', true);
			$email_types[3] = __('confirmation_admin_confirmation', true);
			$email_types[4] = __('confirmation_admin_payment', true);
			$email_types[5] = __('opt_o_email_new_registration', true);
			$email_types[6] = __('opt_o_email_password_reminder', true);
			$email_types[7] = __('opt_o_email_send_to_friend', true);
			?>
			<p class="block t10">
				<label class="title" style="width: 130px"><?php __('lblEmailNotifications'); ?></label>
				<span class="block float_left r5">
					<select name="email_types" class="form-field w300">
						<?php
						foreach($email_types as $k => $v)
						{
							?><option value="<?php echo $k;?>"><?php echo $v;?></option><?php
						} 
						?>
					</select>
				</span>
			</p>
			<div class="emailBox1">
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<div class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_subject'); ?></label>
							<input type="text" name="i18n[<?php echo $v['id']; ?>][confirm_subject_client]" class="form-field w500 b10" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['confirm_subject_client'])); ?>" />
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_body'); ?></label>
							<span class="block float_left r5">
								<textarea name="i18n[<?php echo $v['id']; ?>][confirm_tokens_client]" class="form-field w500 h230 mceSelector"><?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['confirm_tokens_client']); ?></textarea>
							</span>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
							
						</p>
					</div>
					<?php
				}
				?>
			</div>
			<div class="emailBox2">
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<div class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_subject'); ?></label>
							<input type="text" name="i18n[<?php echo $v['id']; ?>][payment_subject_client]" class="form-field w500 b10" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['payment_subject_client'])); ?>" />
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_body'); ?></label>
							<span class="block float_left r5">
								<textarea name="i18n[<?php echo $v['id']; ?>][payment_tokens_client]" class="form-field w500 h230 mceSelector"><?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['payment_tokens_client']); ?></textarea>
							</span>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
					</div>
					<?php
				}
				?>
			</div>
			<div class="emailBox3">
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<div class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_subject'); ?></label>
							<input type="text" name="i18n[<?php echo $v['id']; ?>][confirm_subject_admin]" class="form-field w500 b10" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['confirm_subject_admin'])); ?>" />
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_body'); ?></label>
							<span class="block float_left r5">
								<textarea name="i18n[<?php echo $v['id']; ?>][confirm_tokens_admin]" class="form-field w500 h230 mceSelector"><?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['confirm_tokens_admin']); ?></textarea>
							</span>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
					</div>
					<?php
				}
				?>
			</div>
			<div class="emailBox4">
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<div class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_subject'); ?></label>
							<input type="text" name="i18n[<?php echo $v['id']; ?>][payment_subject_admin]" class="form-field w500 b10" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['payment_subject_admin'])); ?>" />
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_body'); ?></label>
							<span class="block float_left r5">
								<textarea name="i18n[<?php echo $v['id']; ?>][payment_tokens_admin]" class="form-field w500 h230 mceSelector"><?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['payment_tokens_admin']); ?></textarea>
							</span>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
					</div>
					<?php
				}
				?>
			</div>
			<div class="emailBox5">
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<div class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_subject'); ?></label>
							<input type="text" name="i18n[<?php echo $v['id']; ?>][register_subject]" class="form-field w500 b10" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['register_subject'])); ?>" />
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_body'); ?></label>
							<span class="block float_left r5">
								<textarea name="i18n[<?php echo $v['id']; ?>][register_tokens]" class="form-field w500 h230 mceSelector"><?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['register_tokens']); ?></textarea>
							</span>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
					</div>
					<?php
				}
				?>
			</div>
			<div class="emailBox6">
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<div class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_subject'); ?></label>
							<input type="text" name="i18n[<?php echo $v['id']; ?>][forgot_subject]" class="form-field w500 b10" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['forgot_subject'])); ?>" />
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_body'); ?></label>
							<span class="block float_left r5">
								<textarea name="i18n[<?php echo $v['id']; ?>][forgot_tokens]" class="form-field w500 h230 mceSelector"><?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['forgot_tokens']); ?></textarea>
							</span>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
					</div>
					<?php
				}
				?>
			</div>
			<div class="emailBox7">
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<div class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('confirmation_subject'); ?></label>
							<input type="text" name="i18n[<?php echo $v['id']; ?>][send_to_subject]" class="form-field w500 b10" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['i18n'][$v['id']]['send_to_subject'])); ?>" />
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
						<p class="block t5">
							<label class="title" style="width: 130px">
								<?php __('confirmation_body'); ?>
							</label>
							<span class="block float_left r5">
								<textarea name="i18n[<?php echo $v['id']; ?>][send_to_tokens]" class="form-field w500 h230 mceSelector"><?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['send_to_tokens']); ?></textarea>
							</span>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
					</div>
					<?php
				}
				?>
			</div>
			<p>
				<label class="title" style="width: 130px">&nbsp;</label>
				<input type="submit" class="button" value="<?php __('btnSave'); ?>" />
			</p>
		</div>
		
		<div id="tabs-2">
			<fieldset class="fieldset white">
				<legend><?php __('confirm_sms_admin'); ?></legend>
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<div class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('sms_body'); ?></label>
							<textarea name="i18n[<?php echo $v['id']; ?>][confirm_sms_admin]" class="form-field w500 h100"><?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['confirm_sms_admin']); ?></textarea>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
					</div>
					<?php
				}
				?>
			</fieldset>
			<fieldset class="fieldset white">
				<legend><?php __('payment_sms_admin'); ?></legend>
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<div class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<p class="block t5">
							<label class="title" style="width: 130px"><?php __('sms_body'); ?></label>
							<textarea name="i18n[<?php echo $v['id']; ?>][payment_sms_admin]" class="form-field w500 h100"><?php echo stripslashes(@$tpl['arr']['i18n'][$v['id']]['payment_sms_admin']); ?></textarea>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</p>
					</div>
					<?php
				}
				?>
			</fieldset>
			<p>
				<label class="title" style="width: 130px">&nbsp;</label>
				<input type="submit" class="button" value="<?php __('btnSave'); ?>" />
			</p>
		</div>
	</div>
</form>

<?php $locale = isset($_GET['locale']) && (int) $_GET['locale'] > 0 ? (int) $_GET['locale'] : @$tpl['lp_arr'][0]['id']; ?>
<script type="text/javascript">
<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1) : ?>
(function ($) {
	$(function() {
		$(".multilang").multilang({
			langs: <?php echo $tpl['locale_str']; ?>,
			flagPath: "<?php echo FRAMEWORK_LIBS_PATH; ?>/img/flags/",
			select: function (event, ui) {
				//callback
			}
		});
		$(".multilang").find("a[data-index='<?php echo $locale; ?>']").trigger("click");
	});
})(jQuery_1_8_2);
<?php endif; ?>
</script>