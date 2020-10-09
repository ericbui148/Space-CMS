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
	if (isset($_GET['err']))
	{
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	$import_sources = __('import_sources', true);
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSubscribers&amp;action=Index"><?php __('menuSubscribers'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSubscribers&amp;action=Import"><?php __('lblImport'); ?></a></li>
		</ul>
	</div>
	<?php
	UtilComponent::printNotice(__('infoImportTitle', true), __('infoImportBody', true),false);
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSubscribers&amp;action=Import" method="post" id="frmImportSubscriber" class="form form" autocomplete="off" enctype="multipart/form-data">
		<input type="hidden" name="subscriber_import" value="1" />
		<p>
			<label class="title"><?php __('lblGroup'); ?></label>
			<select name="group_id" id="group_id" class="form-field w200">
				<option value="">-- <?php __('lblChoose'); ?> --</option>
				<?php
				foreach ($tpl['group_arr'] as $v)
				{
					?><option value="<?php echo $v['id']; ?>"<?php echo isset($_GET['group_id']) ? ($_GET['group_id'] == $v['id'] ? ' selected="selected"' : null) : null; ?>><?php echo stripslashes($v['group_title']); ?></option><?php
				}
				?>
			</select>
		</p>
		
		<p>
			<label class="title"><?php __('lblSource'); ?></label>
			<span class="inline_block">
				<span class="block float_left t5 r10">
					<input type="radio" name="source" id="csv" value="csv" checked="checked" class="block float_left r5"/><label for="csv"><?php echo $import_sources['csv'] ;?></label>
				</span>
				<span class="block float_left t5 r10">
					<input type="radio" name="source" id="excel" value="excel" class="block float_left r5"/><label for="excel"><?php echo $import_sources['excel'] ;?></label>
				</span>
			</span>
		</p>
		<p class="nlSource nlSourceCSV">
			<label class="title"><?php __('lblCSVFile'); ?></label>
			<span class="inline_block">
				<input id="csv" name="csv" type="file" class="form-field"/>
			</span>
		</p>
		<p class="nlSource nlSourceExcel">
			<label class="title"><?php __('lblSubscribers'); ?></label>
			<span class="inline_block">
				<textarea name="subscribers" class="form-field w500 h300"></textarea>
			</span>
		</p>
		<p>
			<label class="title"><?php __('lblUpdateSubscribers'); ?></label>
			<span class="inline_block">
				<label class="content">
					<input type="checkbox" name="update_subscribers" id="update_subscribers" value="T" />
				</label>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnImport'); ?>" class="button" />
		</p>
	</form>
	<script type="text/javascript">
		var myLabel = myLabel || {};
		myLabel.csv_allowed = "<?php __('csv_allowed'); ?>";
	</script>
	<?php
}
?>