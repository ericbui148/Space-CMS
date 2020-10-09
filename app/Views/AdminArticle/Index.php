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
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	$jqDateFormat = UtilComponent::jqDateFormat($tpl['option_arr']['o_date_format']);
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=Index">Danh sách bài viết</a></li>
			<?php
			if ($controller->isAdmin() || ($controller->isEditor() && $controller->isArticleAllowed()))
			{ 
				?>
				<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=Create"><?php __('lblAddArticle'); ?></a></li>
				<?php
			}
			if($controller->isAdmin())
			{ 
				?>
				<!-- <li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=History"><?php __('lblChanges'); ?></a></li> -->
				<?php
			} 
			if ($controller->isAdmin() || ($controller->isEditor() && $controller->isArticleAllowed()))
			{
				?>
				<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticleCategories&amp;action=Index"><?php __('menuArticleCategories'); ?></a></li>
				<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=History">Lịch sử bài viết</a></li>
				<?php
			}
						
			?>
		</ul>
	</div>
	
	<?php UtilComponent::printNotice("Danh sách bài viêt", "Dưới đây là danh sách tất cả các bài viết. Bạn có thể tìm kiếm bài viết theo tên và tìm kiếm nâng cao ở đây. Để cập nhật thông tin bài viết vui lòng click vào biểu tượng bút chì."); ?>
	<div class="b10">
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="float_left form r10">
			<input type="hidden" name="controller" value="AdminArticle" />
			<input type="hidden" name="action" value="Create" />
			<button type="submit" class="button"><i class="fa fa-plus-circle" aria-hidden="true"></i> <?php __('lblAddArticle'); ?></button>
		</form>
		<form action="" method="get" class="float_left form frm-filter">
			<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch'); ?>" />
			<button type="button" class="button button-detailed"><i class="fa fa-chevron-down" aria-hidden="true"></i></button>
		</form>
		<?php
		$filter = __('filter', true, true);
		?>
		<div class="float_right t5">
			<a href="#" class="button btn-all"><i class="fa fa-list" aria-hidden="true"></i> <?php __('lblAll'); ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="is_active" data-value="T"><i class="fa fa-check" aria-hidden="true"></i> <?php echo $filter['active']; ?></a>
			<a href="#" class="button btn-filter btn-status" data-column="is_active" data-value="F"><i class="fa fa-times-circle" aria-hidden="true"></i> <?php echo $filter['inactive']; ?></a>
		</div>
		<br class="clear_both" />
	</div>
	<div class="form-filter-advanced" style="display: none">
		<span class="menu-list-arrow"></span>
		<form action="" method="get" class="form form form-search frm-filter-advanced">
			<div class="float_left w400">
				<p>
					<label class="title"><?php __('lblArticle'); ?></label>
					<input type="text" name="q" class="form-field w200" value="<?php echo isset($_GET['name']) ? SanitizeComponent::html($_GET['name']) : NULL; ?>" />
				</p>
				<p>
					<label class="title"><?php __('lblCategory'); ?></label>
					<select name="category_id" id="category_id" class="form-field w200">
					<option value="">-- <?php __('lblChoose'); ?> --</option>
					<?php
					foreach ($tpl['category_arr'] as $category)
					{
						?><option value="<?php echo $category['data']['id']; ?>"<?php echo isset($_GET['category_id']) && $_GET['category_id'] == $category['data']['id'] ? ' selected="selected"' : NULL; ?>><?php echo str_repeat("-----", $category['deep']) . " " . SanitizeComponent::html($category['data']['name']); ?></option><?php
					}
					?>
					</select>
				</p>
				<p>
					<?php 
						$statuses = [
							'T' => $filter['active'],
							'F' => $filter['inactive']
						]
					?>
					<label class="title" ><?php __('stock_product_status'); ?></label>
					<select name="status" class="form-field w150">
						<option value="">-- <?php __('lblChoose'); ?> --</option>
						<?php
						foreach ($statuses as $k => $v)
						{
							?><option value="<?php echo $k; ?>"><?php echo SanitizeComponent::html($v); ?></option><?php
						}
						?>
					</select>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<button type="submit" class="button"><i class="fa fa-search" aria-hidden="true"></i> <?php __('btnSearch'); ?></button>
					<button type="reset" class="button"><i class="fa fa-ban" aria-hidden="true"></i> <?php __('btnCancel'); ?></button>
				</p>
			</div>
			<div class="float_right w300">
				<p>
					<label class="title">Từ ngày</label>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" name="fromDate" class="form-field w80 datepick pointer"  rev="<?php echo $jqDateFormat; ?>"/>
						<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>&nbsp;
					</span>
					<span class="form-field-custom form-field-custom-after">
					</span>
				</p>
				<p>
					<label class="title">Đến ngày</label>
					<span class="form-field-custom form-field-custom-after">
						<input type="text" name="toDate" class="form-field w80 datepick pointer"  rev="<?php echo $jqDateFormat; ?>"/>
						<span class="form-field-after"><abbr class="form-field-icon-date"></abbr></span>
					</span>
					<span class="form-field-custom form-field-custom-after">
					</span>
				</p>
				<?php 
					$themePath = THEME_PATH_PUBLIC;
					$showSelectTemplate = is_dir($themePath.'/templates')
				?>	
				<?php if ($showSelectTemplate): ?>
					<?php $templateFiles = UtilComponent::getFileList($themePath.'/templates');?>
					<p>
						<label class="title">Template</label>
						<span class="inline_block">
							<select name="template" id="template" class="form-field w150">
								<option value="">-- <?php __('lblChoose'); ?>--</option>
								<?php
								foreach ($templateFiles as $template)
								{
									?><option value="<?php echo $template; ?>"><?php echo $template; ?></option><?php
								}
								?>
							</select>
						</span>
					</p>	
				<?php endif;?>				
			</div>
			
			<br class="clear_both" />
		</form>
	</div>
	<div id="grid"></div>
	
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.isEditor = <?php echo $controller->isEditor() ? 'true' : 'false'; ?>;
	Grid.isArticleAllowed = <?php echo $controller->isArticleAllowed() ? 'true' : 'false'; ?>;
	var myLabel = myLabel || {};
	myLabel.article = "<?php __('lblArticle'); ?>";
	myLabel.last_changed = "<?php __('lblLastChanged'); ?>";
	myLabel.category = "<?php __('lblCategory'); ?>";
	myLabel.more = "<?php __('lblMore'); ?>";
	myLabel.install = "<?php __('lblInstallCode'); ?>";
	myLabel.duplicate = "<?php __('lblDuplicateArticle'); ?>";
	myLabel.preview = "<?php __('lblPreviewWebPage'); ?>";
	myLabel.view = "<?php __('lblViewChanges'); ?>";
	myLabel.edit = "<?php __('lblEdit'); ?>";
	myLabel.delete = "<?php __('lblDelete'); ?>";
	myLabel.status = "<?php __('lblStatus'); ?>";
	myLabel.active = "<?php echo $filter['active']; ?>";
	myLabel.inactive = "<?php echo $filter['inactive']; ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation'); ?>";
	</script>
	<?php
}
?>