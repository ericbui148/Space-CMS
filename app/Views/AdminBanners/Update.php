<?php
use App\Controllers\Components\UtilComponent;
use App\Models\BannerModel;

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
	if (isset($_GET['err']))
	{
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);
		$bodies_text = str_replace("{SIZE}", ini_get('post_max_size'), @$bodies[$_GET['err']]);
		UtilComponent::printNotice(@$titles[$_GET['err']], $bodies_text);
	}
	?>
	<?php
	UtilComponent::printNotice("Cập nhật banner", "Sử dụng form dưới đây để cập nhật banner"); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminBanners&amp;action=Update" method="post" id="frmUpdateFile" class="form form" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="file_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id'];?>" />
		<p>
			<label class="title">Tên banner</label>
			<span class="inline_block">
				<input type="text" name="name" class="form-field w500 required" value="<?php echo $tpl['arr']['name'];?>"/>
			</span>
		</p>
		<p>
			<label class="title">Banner:</label>
			<span class="inline_block">
				<img width="250" height="250" src="<?php echo $tpl['arr']['file_path'];?>">
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblFile'); ?></label>
			<span class="inline_block">
				<input name="file" type="file" class="form-field w250"/>
			</span>
		</p>
		<p>
			<label class="title">Link</label>
			<span class="inline_block">
				<input type="text" name="link" class="form-field w500" value="<?php echo @$tpl['arr']['link'];?>"/>
			</span>
		</p>
		<p>
			<?php 
				$position_arr = [
					BannerModel::POSITION_LEFT => 'Trái',
					BannerModel::POSITION_BOTTOM => 'Đáy',
				];
			?>
			<label class="title">Vị trí</label>
			<span class="inline_block">
				<select name="position" id="position" class="form-field required">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach ($position_arr as $k => $v)
					{
						?><option value="<?php echo $k; ?>" <?php echo $k == $tpl['arr']['position']? 'selected' : null;?>><?php echo $v; ?></option><?php
					}
					?>
				</select>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
		</p>
	</form>
	<script type="text/javascript">
		var myLabel = myLabel || {};
		myLabel.select_users = "<?php __('lblSelectUsers');?>";
		myLabel.field_required = "<?php __('lblFieldRequired'); ?>";
		myLabel.extension_message = "<?php __('lblExtensionMessage');?>";
		myLabel.allowed_extension = "<?php echo $tpl['option_arr']['o_extension_allow']; ?>";
	</script>
	<?php
}
?>