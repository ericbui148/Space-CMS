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
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']],  @$bodies[$_GET['err']]);
	}
	?>
	<form action="<?php echo $controller->baseUrl(); ?>index.php?controller=AdminCmsSettings&amp;action=Index" id="frmCmsSetting" method="post" class="form form" enctype="multipart/form-data">
		<input type="hidden" name="cms_setting_post" value="1" />
		<input type="hidden" name="id" value="<?php echo @$tpl['arr']['id'];?>" />
			<?php
			UtilComponent::printNotice($titles['CMSSETTING001'], $bodies['CMSSETTING001'], false);
			?>
			<p>
				<label class="title">Favicon</label>
				<span class="left" id="box_favicon">
					<?php
					if (!empty($tpl['arr']['favicon']))
					{
						?><img src="<?php echo $tpl['arr']['favicon']; ?>" alt="" class="align_middle" />
						<input type="button" class="button delete-favicon" value="<?php __('lblDelete'); ?>" /><?php
					} else {
						?><input type="file" name="favicon" id="y_favicon" class="form-field w350"/><?php
					}
					?>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblCmsSettingTitle'); ?></label>
				<span class="inline-block">
				<input type="text" name="title" id="title" class="form-field w500 required" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['title'])); ?>"/>
			</span>
			</p>
			<p>
				<label class="title"><?php __('lblMetaDescription'); ?></label>
				<span class="inline-block">
					<textarea name="meta_description" id="meta_description" class="form-field w500 h100"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['meta_description'])); ?></textarea>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblMetaKeywords'); ?></label>
				<span class="inline-block">
					<textarea name="meta_keywords" id="meta_keywords" class="form-field w500 h100"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['meta_keywords'])); ?></textarea>
				</span>
			</p>
			<p>
				<label class="title"><?php __('support_widget'); ?></label>
				<span class="inline-block">
					<textarea name="support_widget" id="support_widget" class="form-field w500 h100"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['support_widget'])); ?></textarea>
				</span>
			</p>
			<p>
				<label class="title"><?php __('google_site_map'); ?></label>
				<span class="inline-block">
					<textarea name="google_site_map" id="google_site_map" class="form-field w500 h100"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['google_site_map'])); ?></textarea>
				</span>
			</p>
			<p>
				<label class="title"><?php __('google_verify'); ?></label>
				<span class="inline-block">
					<textarea name="google_verify" id="google_verify" class="form-field w500 h100"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['google_verify'])); ?></textarea>
				</span>
			</p>
			<p>
				<label class="title"><?php __('google_analytics'); ?></label>
				<span class="inline-block">
					<textarea name="google_analytics" id="google_analytics" class="form-field w500 h100"><?php echo htmlspecialchars(stripslashes(@$tpl['arr']['google_analytics'])); ?></textarea>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblMaintain'); ?></label>
				<span class="inline-block">
				<?php $isChecked = isset($tpl['arr']['is_maintain']) && $tpl['arr']['is_maintain'] == 'T';?>
				<input type="checkbox" id="is_maintain" name="is_maintain" <?php if ($isChecked) {echo 'checked="checked"';}?> value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['is_maintain'])); ?>">
				&nbsp;Link &nbsp;
				<?php if ($isChecked):?>
				<input type="text" id="maintain_url" name="maintain_url" id="title" class="form-field w450" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['maintain_url'])); ?>"/>
				<?php else :?>
				<input type="text" id="maintain_url" name="maintain_url" style="display: none;" id="title" class="form-field w450" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['maintain_url'])); ?>"/>
				<?php endif;?>
				</span>	
			</p>
			<p>
				<label class="title">Copyright</label>
				<span class="inline-block">
				<input type="text" name="copyright" id="copyright" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['copyright'])); ?>"/>
				</span>
			</p>	
			<p>
				<label class="title">Logo text</label>
				<span class="inline-block">
				<input type="text" name="logo_text" id="copyright" class="form-field w500" value="<?php echo htmlspecialchars(stripslashes(@$tpl['arr']['logo_text'])); ?>"/>
				</span>
			</p>																			
			<p>
				<input type="submit" value="<?php __('btnSave'); ?>" class="button float_left align_middle" />
			</p>
	</form>
	<div id="dialogDeleteFavicon"></div>
		<script type="text/javascript">
	var myLabel = myLabel || {};
	myLabel.btn_cancel = "<?php __('btnCancel'); ?>";
	myLabel.btn_delete = "<?php __('lblDelete'); ?>";
	myLabel.delete_image_confirm = "<?php __('lblDeleteImageConfirm'); ?>";
	</script>
<?php }?>