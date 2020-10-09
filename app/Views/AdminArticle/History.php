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
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=Index">Danh sách bài viết</a></li>
			<?php
			if ($controller->isAdmin() || ($controller->isEditor() && $controller->isArticleAllowed()))
			{ 
				?>
				<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=Create"><?php __('lblAddArticle'); ?></a></li>
				<?php
			} 
			?>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=History">Lịch sử bài viêt</a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticleCategories&amp;action=Index"><?php __('menuStockCategories'); ?></a></li>
		</ul>
	</div>
	
	<?php 
	UtilComponent::printNotice("Lịch sử bài viết", "Dưới đây là lịch sử bài viết. Bạn có thể tìm kiếm, khôi phục nội dung cũ của bài viết ở đây");
	
	if(!empty($tpl['arr']))
	{ 
		?>
		<p class="b10">
			<span class="inline-block">
				<label class="content r10">Chọn bài viết:</label>
				<select id="article_id" name="article_id" class="form-field w300">
					<option value="">-- <?php __('lblAll'); ?> --</option>
					<?php
					foreach($tpl['arr'] as $v)
					{
						?>
						<option value="<?php echo $v['id']?>"<?php echo isset($_GET['article_id']) ? ($_GET['article_id'] == $v['id'] ? ' selected="selected"' : null) : null ;?>><?php echo SanitizeComponent::html($v['article_name']);?></option>
						<?php
					} 
					?>
				</select>
			</span>
		</p>
		<?php
	} 
	?>
	
	<div id="history_grid"></div>
	
	<div id="dialogView" style="display: none" title="" data-title="<?php __('lblArticleContent');?>"></div>
	<div id="dialogRestore" style="display: none" title="<?php __('lblRestoreArticle');?>"><?php __('lblRestoreConfirmation');?></div>
	
	<script type="text/javascript">
	var Grid = Grid || {};
	Grid.queryString = "";
	<?php
	if (isset($_GET['article_id']) && (int) $_GET['article_id'] > 0)
	{
		?>Grid.queryString += "&article_id=<?php echo (int) $_GET['article_id']; ?>";<?php
	}
	?>
	var myLabel = myLabel || {};
	myLabel.article = "<?php __('lblArticle'); ?>";
	myLabel.datetime = "<?php __('lblDateTime'); ?>";
	myLabel.user = "<?php __('lblUser'); ?>";
	myLabel.ip = "<?php __('lblIpAddress'); ?>";
	myLabel.view = "<?php __('lblView'); ?>";
	myLabel.restore = "<?php __('lblRestore'); ?>";
	myLabel.edit = "<?php __('lblEdit'); ?>";
	myLabel.delete = "<?php __('lblDelete'); ?>";
	myLabel.preview = "<?php __('lblPreviewNewInWindow'); ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation'); ?>";
	</script>
	<?php
}
?>