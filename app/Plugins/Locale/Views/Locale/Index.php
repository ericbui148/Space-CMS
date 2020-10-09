<?php
use App\Controllers\Components\UtilComponent;

if (isset($_GET['err']))
{
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
}
?>
<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Locale&action=Index&tab=1"><i class="fa fa-list" aria-hidden="true"></i> Danh sách bản dịch</a></li>
		<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Locale&action=CreateTranslation"><i class="fa fa-plus-circle" aria-hidden="true"></i> Thêm mới</a></li>
	</ul>
</div>
<?php
UtilComponent::printNotice(__('plugin_locale_titles_title', true), __('plugin_locale_titles_body', true), false);
?>
<br/>
<fieldset class="fieldset white">
	<legend><?php __('plugin_locale_lbl_show_id'); ?></legend>
	<form id="frmUpdateShowID" action="<?php echo $controller->baseUrl(); ?>index.php?controller=Locale&amp;action=Index" method="post" class="form form b5">
		<input type="hidden" name="lang_show_id" value="1" />
		<br/>
		<p>
			<label for="show_id" class="title t5"><?php __('plugin_locale_lbl_show_id'); ?></label>
			<span class="inline_block">
				<label class="block float_left t10 r15">
					<input type="checkbox" name="show_id" id="show_id" class="form-field"<?php echo (isset($_SESSION['lang_show_id']) && (int) $_SESSION['lang_show_id'] == 1) ? ' checked="checked"' : null;?>/>
				</label>
				<label class="block float_left">
					<input type="button" value="<?php __('btnSave'); ?>" class="button show-id-save" />
				</label>
			</span>
		</p>
	</form>	
</fieldset>
<br/>
<form action="<?php echo $controller->baseUrl(); ?>index.php" method="get" class="float_left form frm-filter b5">
	<input type="hidden" name="controller" value="Locale" />
	<input type="hidden" name="action" value="Index" />
	<input type="hidden" name="tab" value="1" />
	<input type="text" name="q" class="form-field form-field-search w150" placeholder="<?php __('btnSearch'); ?>" value="<?php echo isset($_GET['q']) && !empty($_GET['q']) ? htmlspecialchars($_GET['q']) : NULL; ?>" />
</form>

<form action="<?php echo $controller->baseUrl(); ?>index.php?controller=Locale&amp;action=SaveFields" method="post" class="form">
	<input type="hidden" name="next_action" value="Index" />
	<input type="hidden" name="page" value="<?php echo isset($_GET['page']) && (int) $_GET['page'] > 0 ? (int) $_GET['page'] : 1; ?>" />
	<input type="hidden" name="locale" value="<?php echo isset($_GET['locale']) && (int) $_GET['locale'] > 0 ? (int) $_GET['locale'] : @$tpl['lp_arr'][0]['id']; ?>" />
	<input type="hidden" name="q" value="<?php echo isset($_GET['q']) && !empty($_GET['q']) ? htmlspecialchars(stripslashes($_GET['q'])) : NULL; ?>" />
	
	<div class="clear_both"></div>
	<?php
	$table_width = ((count($tpl['lp_arr']) * 270) + 300);
	?>
	<div class="plugin_locale_container">
		<div class="plugin_locale_wrapper_top">
	    	<div class="pluign_locale_wrapper_scroll" style="width:<?php echo $table_width;?>px"></div>
		</div>
		<div class="plugin_locale_wrapper_bottom">
			<table class="table plugin_locale_table" cellpadding="0" cellspacing="0" style="width: <?php echo $table_width;?>px;">
				<thead>
					<tr>
						<th><input type="checkbox" class="table-toggle-rows"></th>
						<th class="dHeadcol"><?php __('plugin_locale_lbl_id'); ?></th>
						<th class="dField"><?php __('plugin_locale_lbl_field'); ?></th>
						<th class="dField">Key</th>
						<?php
						foreach($tpl['lp_arr'] as $locale)
						{ 
							?>
							<th class="dHead"><?php echo $locale['title']; ?><?php echo $locale['is_default'] == 1 ? ' ('.__('plugin_locale_default', true).')' : null;?></th>
							<?php
						} 
						?>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ($tpl['arr'] as $k => $field_arr)
				{
					?>
					<tr class="table-row-<?php echo $k % 2 === 0 ? 'odd' : 'even'; ?>">
						<td><input type="checkbox" name="record[]" value="<?php echo $field_arr['id'];?>" class="table-select-row"></td>
						<td class="dHeadcol">:<?php echo $field_arr['id'];?>:</td>
						<td class="dField"><?php echo stripslashes($field_arr['label']); ?></td>
						<td class="dField"><?php echo stripslashes($field_arr['key']); ?></td>
						<?php
						foreach ($field_arr['i18n'] as $locale_id => $locale_arr)
						{ 
							?>	
							<td class="dHead"><input type="text" name="i18n[<?php echo $locale_id; ?>][<?php echo $locale_arr['foreign_id']; ?>][title]" value="<?php echo htmlspecialchars(stripslashes(@$locale_arr['content'])); ?>" class="form-field w250" data-key="<?php echo $field_arr['key']; ?>" /></td>
							<?php
						} 
						?>

					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
	$row_opts = array(10, 15, 20, 30, 40, 50, 75, 100);
	$row_count = isset($_GET['row_count']) && in_array($_GET['row_count'], $row_opts) ? (int) $_GET['row_count'] : 15;
	$column = isset($_GET['column']) ? $_GET['column'] : 'id';
	$direction = isset($_GET['direction']) ? $_GET['direction'] : 'ASC';
	if (isset($tpl['paginator']) && (int) $tpl['paginator']['pages'] > 0)
	{
		?>
		<ul class="paginator">
		<?php
		foreach (range(1, $tpl['paginator']['pages']) as $i)
		{
			?><li><a href="<?php echo $controller->baseUrl(); ?>index.php?controller=Locale&amp;action=<?php echo $_GET['action']; ?>&amp;tab=1&amp;q=<?php echo isset($_GET['q']) && !empty($_GET['q']) ? urlencode($_GET['q']) : NULL; ?>&amp;locale=<?php echo isset($_GET['locale']) && (int) $_GET['locale'] > 0 ? (int) $_GET['locale'] : NULL; ?>&amp;column=<?php echo urlencode($column); ?>&amp;direction=<?php echo urlencode($direction); ?>&amp;row_count=<?php echo $row_count; ?>&amp;page=<?php echo $i; ?>"<?php echo $i == @$_GET['page'] || (!isset($_GET['page']) && $i == 1) ? ' class="focus"' : NULL; ?>><?php echo $i; ?></a></li><?php
		}
		?>
		</ul>
		<?php
	}
	?>
	<p>&nbsp;</p>
	<p class="overflow">
		<input type="submit" value="<?php __('btnSave'); ?>" class="button float_left locale-save" />
		<span class="inline_block float_right">
			<?php __('plugin_locale_lbl_rows'); ?>
			<select name="row_count" class="form-field">
			<?php
			foreach ($row_opts as $i)
			{
				?><option value="<?php echo $i; ?>"<?php echo !isset($_GET['row_count']) || (int) $_GET['row_count'] !== $i ? NULL : ' selected="selected"'; ?>><?php echo $i; ?></option><?php
			}
			?>
			</select>
		</span>
		<br class="clear_both" />
	</p>
</form>

<div id="dialogShowID" style="display: none" title="<?php __('plugin_locale_showid_dialog_title');?>"><?php __('plugin_locale_showid_dialog_desc');?></div>

<script type="text/javascript">
var myLabel = myLabel || {};
myLabel.btnConfirm = "<?php __('plugin_locale_button_confirm');?>";
myLabel.btnCancel = "<?php __('plugin_locale_button_cancel');?>";
</script>