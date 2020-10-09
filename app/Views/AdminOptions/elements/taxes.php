<?php 
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;
?>

<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
<div class="multilang"></div>
<?php endif; ?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOptions&amp;action=Update" method="post" class="form form clear_right">
	<input type="hidden" name="options_update" value="1" />
	<input type="hidden" name="tab" value="<?php echo @$_GET['tab']; ?>" />
	<input type="hidden" name="next_action" value="Index" />
	
	<table cellpadding="0" cellspacing="0" class="table b10" style="width: 100%" id="tblShipping">
		<thead>
			<tr>
				<th><?php __('tax_location'); ?></th>
				<th class="w120"><?php __('tax_shipping'); ?></th>
				<th class="w120"><?php __('tax_free'); ?></th>
				<th class="w120"><?php __('tax_tax'); ?></th>
				<th class="w30"></th>
			</tr>
		</thead>
		<tbody>
	<?php
	if (isset($tpl['tax_arr']) && !empty($tpl['tax_arr']))
	{
		foreach ($tpl['tax_arr'] as $item)
		{
			?>
			<tr>
				<td>
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
				?>
					<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<span class="inline_block">
							<input type="text" name="i18n[<?php echo $v['id']; ?>][location][<?php echo $item['id']; ?>]" class="form-field w250" value="<?php echo SanitizeComponent::html(@$item['i18n'][$v['id']]['location']); ?>" />
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</span>
					</p>
					<?php
				}
				?>
				</td>
				<td>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(null, $tpl['option_arr']['o_currency']); ?></abbr></span>
						<input type="text" name="shipping[<?php echo $item['id']; ?>]" value="<?php echo (float) $item['shipping']; ?>" class="form-field field-float w60">
					</span>
				</td>
				<td>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(null, $tpl['option_arr']['o_currency']); ?></abbr></span>
						<input type="text" name="free[<?php echo $item['id']; ?>]" value="<?php echo (float) $item['free']; ?>" class="form-field field-float w60">
					</span>
				</td>
				<td>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-text">%</abbr></span>
						<input type="text" name="tax[<?php echo $item['id']; ?>]" value="<?php echo (float) $item['tax']; ?>" class="form-field field-float w60">
					</span>
				</td>
				<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOptions" class="table-icon-delete btnDeleteShipping" data-id="<?php echo $item['id']; ?>"></a></td>
			</tr>
			<?php
		}
	} else {
		?>
		<tr>
			<td>
			<?php
			$rand = rand(1, 99999);
			foreach ($tpl['lp_arr'] as $v)
			{
			?>
				<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
					<span class="inline_block">
						<input type="text" name="i18n[<?php echo $v['id']; ?>][location][new_<?php echo $rand; ?>]" class="form-field w250" />
						<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
						<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
						<?php endif; ?>
					</span>
				</p>
				<?php
			}
			?>
			</td>
			<td>
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(null, $tpl['option_arr']['o_currency']); ?></abbr></span>
					<input type="text" name="shipping[new_<?php echo $rand; ?>]" value="" class="form-field field-float w60">
				</span>
			</td>
			<td>
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(null, $tpl['option_arr']['o_currency']); ?></abbr></span>
					<input type="text" name="free[new_<?php echo $rand; ?>]" value="" class="form-field field-float w60">
				</span>
			</td>
			<td>
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-text">%</abbr></span>
					<input type="text" name="tax[new_<?php echo $rand; ?>]" value="" class="form-field field-float w60">
				</span>
			</td>
			<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOptions" class="table-icon-delete btnRemoveShipping"></a></td>
		</tr>
		<?php
	}
	?>
		</tbody>
	</table>
	
	<p>
		<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
		<input type="button" value="<?php __('btnAdd'); ?>" class="button btnAddShipping" />
	</p>
</form>

<div id="tmplShipping" style="display: none">
	<table>
		<tbody>
			<tr>
				<td>
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
				?>
					<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<span class="inline_block">
							<input type="text" name="i18n[<?php echo $v['id']; ?>][location][{INDEX}]" class="form-field w250" />
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</span>
					</p>
					<?php
				}
				?>
				</td>
				<td>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(null, $tpl['option_arr']['o_currency']); ?></abbr></span>
						<input type="text" name="shipping[{INDEX}]" class="form-field field-float w60">
					</span>
				</td>
				<td>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(null, $tpl['option_arr']['o_currency']); ?></abbr></span>
						<input type="text" name="free[{INDEX}]" class="form-field field-float w60">
					</span>
				</td>
				<td>
					<span class="form-field-custom form-field-custom-before">
						<span class="form-field-before"><abbr class="form-field-icon-text">%</abbr></span>
						<input type="text" name="tax[{INDEX}]" class="form-field field-float w60">
					</span>
				</td>
				<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOptions" class="table-icon-delete btnRemoveShipping"></a></td>
			</tr>
		</tbody>
	</table>
</div>

<div id="dialogDeleteShipping" style="display: none" title="<?php __('tax_del_title'); ?>"><?php __('tax_del_body'); ?></div>

<script type="text/javascript">
(function ($, undefined) {
	$(function() {
		if ($.fn.multilang !== undefined) {
			$(".multilang").multilang({
				langs: <?php echo $tpl['locale_str']; ?>,
				flagPath: "<?php echo FRAMEWORK_LIBS_PATH; ?>/img/flags/",
				tooltip: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sit amet faucibus enim.",
				select: function (event, ui) {
					// Callback, e.g. ajax requests or whatever
				}
			});
		}
	});
})(jQuery_1_8_2);
var myLabel = myLabel || {};
myLabel.btn_delete = "<?php __('btnDelete'); ?>";
myLabel.btn_cancel = "<?php __('btnCancel'); ?>";
</script>