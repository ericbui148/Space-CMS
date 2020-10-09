<?php
use App\Controllers\Components\UtilComponent;
use Core\Framework\Components\SanitizeComponent;
use Core\Framework\Components\TimeComponent;

if (isset($tpl['status']))
{
	// $status = __('status', true);
	// switch ($tpl['status'])
	// {
	// 	case 1:
	// 		UtilComponent::printNotice($status[1]);
	// 		break;
	// 	case 2:
	// 		UtilComponent::printNotice($status[2]);
	// 		break;
	// 	case 9:
	// 		UtilComponent::printNotice($status[9]);
	// 		break;
	// }
} else {
	// if (isset($_GET['err']))
	// {
	// 	$errors = __('errors', true);
	// 	$titles = __('titles', true);
	// 	if($_GET['err'] == 'AP05')
	// 	{
	// 		UtilComponent::printNotice(@$errors[$_GET['err']], @$titles[$_GET['err']]);
	// 	}else{
	// 		UtilComponent::printNotice(@$titles[$_GET['err']], @$errors[$_GET['err']]);
	// 	}
	// }
	$info = __('info', true);
	?>
    <style type="text/css">
    .mce-tinymce{
		float: left;
	}
	.status{
		width: 83px !important;
	}
	.status-1{
		background-position: 70px 3px !important;
	}
	.extraBox .multilang-input img {
		vertical-align: baseline;
	}
	</style>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminProducts&amp;action=Update" method="post" id="frmUpdateProduct" class="form form frmProduct" enctype="multipart/form-data">
		<input type="hidden" name="product_update" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id']; ?>" />
		<input type="hidden" name="tab" value="<?php echo isset($_GET['tab']) ? (int) $_GET['tab'] : 0; ?>" />
	    
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1">Chi tiết</a></li>
				<li><a href="#tabs-2">Sản phẩm số</a></li>
				<li><a href="#tabs-3">Thuộc tính</a></li>
				<li><a href="#tabs-4">Ảnh</a></li>
				<li><a href="#tabs-5">Kho</a></li>
				<li><a href="#tabs-6">Sản phẩm theo kèm</a></li>
				<li><a href="#tabs-7">Sản phẩm tương tự</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminProducts&amp;action=GetHistory&amp;id=<?php echo $tpl['arr']['id']; ?>">Lịch sử</a></li>
			</ul>
			<div id="tabs-1">
				<?php UtilComponent::printNotice($info['product_details_title'], $info['product_details_body']); ?>
				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
				<div class="multilang"></div>
				<?php endif; ?>
				
				<p><label class="title">Trạng thái</label><select name="status" id="status" class="form-field w200">
					<?php
					foreach (__('product_statuses', true) as $k => $v)
					{
						?><option value="<?php echo $k; ?>"<?php echo $tpl['arr']['status'] == $k ? ' selected="selected"' : NULL; ?>><?php echo $v; ?></option><?php
					}
					?>
				</select></p>
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<label class="title">Tên sản phẩm</label>
						<span class="inline_block">
							<input type="text" name="i18n[<?php echo $v['id']; ?>][name]" class="form-field w400<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" value="<?php echo SanitizeComponent::html(@$tpl['arr']['i18n'][$v['id']]['name']); ?>" />
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</span>
					</p>
					<?php
				}
				?>
				<p><label class="title">Danh mục</label>
					<select name="category_id[]" id="category_id" class="form-field w400" multiple="multiple">
					<?php
					foreach ($tpl['category_arr'] as $category)
					{
						?><option value="<?php echo $category['data']['id']; ?>"<?php echo in_array($category['data']['id'], $tpl['pc_arr']) ? ' selected="selected"' : NULL; ?>><?php echo str_repeat("-----", $category['deep']) . " " . SanitizeComponent::html($category['data']['name']); ?></option><?php
					}
					?>
					</select>
				</p>
				<p>
					<label class="title">SKU</label>
					<span class="inline_block"><input type="text" name="sku" id="sku" class="form-field w300" value="<?php echo SanitizeComponent::html($tpl['arr']['sku']); ?>" data-msg-remote="<?php __('product_v_sku', false, true); ?>" /></span>
				</p>
				<?php
            		foreach ($tpl['lp_arr'] as $v)
            		{
            		?>
            			<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
            			<label class="title">URL</label>
            			<span class="inline_block">
            				<?php $url = !empty($tpl['arr']['i18n'][$v['id']]['url'])? $tpl['arr']['i18n'][$v['id']]['url'] : '';?>
            				<label style="font-size: 14px;"><?php echo $controller->baseUrl();?></label><input type="text"  name="i18n[<?php echo $v['id']; ?>][url]" class="form-field w350" value="<?php echo $url;?>" lang="<?php echo $v['id']; ?>" />
            				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
            				<span class="multilang-input"><img src="<?php echo $controller->baseUrl() . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
            				<a target="_blank" href="<?php echo $controller->baseUrl() . $url;?>"><i class="fa fa-external-link-square" aria-hidden="true"></i></a>
            				<?php endif; ?>
            			</span>
            			</p>			
            			<?php
            		}
            		?>
				<?php
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<label class="title">Miêu tả ngắn</label>
						<span class="inline_block">
							<textarea name="i18n[<?php echo $v['id']; ?>][short_desc]" class="form-field w500 h100 selector-short-desc<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>"><?php echo SanitizeComponent::html(@$tpl['arr']['i18n'][$v['id']]['short_desc']); ?></textarea>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
						</span>
					</p>
					<?php
				}
				foreach ($tpl['lp_arr'] as $v)
				{
					?>
					<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
						<label class="title">Miêu tả</label>
						<span class="block overflow">
							<textarea name="i18n[<?php echo $v['id']; ?>][full_desc]" class="form-field w500 h200 selector-full-desc<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>"><?php echo SanitizeComponent::html(@$tpl['arr']['i18n'][$v['id']]['full_desc']); ?></textarea>
							<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
							<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
							<?php endif; ?>
							<br class="clear_both" />
						</span>
					</p>
					<?php
				}
				?>
				<p><label class="title">Sản phẩm nổi bật</label><span class="left"><input type="checkbox" name="is_featured" id="is_featured" value="1"<?php echo (int) $tpl['arr']['is_featured'] === 1 ? ' checked="checked"' : NULL; ?> /></span></p>
				<p><label class="title">Tags</label>
					<select name="tag_id[]" id="tag_id" class="form-field w400" multiple="multiple">
					<?php
					foreach ($tpl['tag_arr'] as $tag)
					{
					    ?><option value="<?php echo $tag['id']; ?>"<?php echo in_array($tag['id'], $tpl['mc_tag_arr']) ? ' selected="selected"' : NULL; ?>><?php echo $tag['name'];?></option><?php
					}
					?>
					</select>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
					<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminProducts&action=Index';" />
				</p>
			</div>
			<div id="tabs-2">
				<?php UtilComponent::printNotice($info['product_digital_title'], $info['product_digital_body']); ?>
				<p><label class="title">Cái này là sản phẩm số</label><span class="left"><input type="checkbox" name="is_digital" id="is_digital" value="1"<?php echo (int) $tpl['arr']['is_digital'] === 1 ? ' checked="checked"' : NULL; ?> /></span></p>
				<div id="boxDigitalOuter" style="display:<?php echo (int) $tpl['arr']['is_digital'] === 1 ? 'block' : 'none';?>">
					<div id="boxDigital">
						<?php
						if (!empty($tpl['arr']['digital_file']))
						{
							?>
							<p>
								<label class="title">File sản phẩm</label>
								<a href="<?php echo BASE_URL; ?>index.php?controller=AdminProducts&amp;action=OpenDigital&amp;id=<?php echo $tpl['arr']['id']; ?>" target="_blank"><?php echo SanitizeComponent::html($tpl['arr']['digital_name']); ?></a>
								<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="icon-delete align_middle btnDigitalDelete" rel="<?php echo $tpl['arr']['id']; ?>"></a>
							</p>
							<?php
						} else {
							?>
							<p>
								<label class="title">&nbsp;</label>
								<label class="r5"><input type="radio" name="digital_choose" value="1" checked="checked" /> Upload file</label>
								<label><input type="radio" name="digital_choose" value="2" /> Đường dẫn file</label>
							</p>
							<p class="digitalFile"><label class="title">Upload file</label><input type="file" name="digital_file" /></p>
							<p class="digitalPath" style="display: none"><label class="title">Đường dẫn tới file</label><input type="text" name="digital_file" class="form-field w300" maxlength="255" /></p>
							<?php
						}
						?>
					</div>
					<p><label class="title">Hết hạn sau</label>
						<?php
						$h = $m = NULL;
						if (!empty($tpl['arr']['digital_expire']))
						{
							list($h, $m,) = explode(":", $tpl['arr']['digital_expire']);
						}
						?>
						<?php echo TimeComponent::factory()->prop('selected', $h)->attr('name', 'hour')->attr('id', 'hour')->attr('class', 'form-field')->hour(); ?>
						<?php echo TimeComponent::factory()->prop('selected', $m)->attr('name', 'minute')->attr('id', 'minute')->attr('class', 'form-field')->prop('step', 5)->minute(); ?>
						HH:MM
					</p>
				</div>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSave'); ?>" class="button" />
					<input type="button" value="<?php __('btnCancel'); ?>" class="button" onclick="window.location.href='<?php echo BASE_URL; ?>index.php?controller=AdminProducts&action=Index';" />
				</p>
			</div>
			<div id="tabs-3">
				<?php UtilComponent::printNotice($info['product_attr_title'], $info['product_attr_body']); ?>
				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
				<div class="multilang"></div>
				<?php endif; ?>
				<?php include_once dirname(__FILE__) . '/elements/attributes.php'; ?>
			</div>
			<div id="tabs-4">
				<?php UtilComponent::printNotice($info['product_photos_title'], $info['product_photos_body']); ?>
				<div id="gallery"></div>
			</div>
			<div id="tabs-5">
			<?php UtilComponent::printNotice($info['product_stock_title'], $info['product_stock_body']); ?>
			<?php include_once dirname(__FILE__) . '/elements/stocks.php'; ?>
			</div>
			<div id="tabs-6">
				<?php UtilComponent::printNotice($info['product_extras_title'], $info['product_extras_body']); ?>
				<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
				<div class="multilang"></div>
				<?php endif; ?>
				<?php include_once dirname(__FILE__) . '/elements/extras.php'; ?>
			</div>
			<div id="tabs-7">
				<?php UtilComponent::printNotice($info['product_similar_title'], $info['product_similar_body']); ?>
				<p>
					<input type="text" name="similar_id" id="similar_id" class="form-field w400" placeholder="<?php __('btnSearch'); ?>" />
				</p>
				<div id="boxSimilar"></div>
			</div>
		</div>
	</form>
	
	<div id="dialogDeleteDigital" style="display: none" title="<?php __('product_digital_delete_title'); ?>">
	<?php __('product_digital_delete_desc'); ?>
	</div>
	<?php
	include_once dirname(__FILE__) . '/elements/attributes_other.php';
	include_once dirname(__FILE__) . '/elements/stocks_other.php';
	include_once dirname(__FILE__) . '/elements/extras_other.php';
	?>
	<script type="text/javascript">
	var myGallery = myGallery || {};
	myGallery.foreign_id = "<?php echo $tpl['arr']['id']; ?>";
	myGallery.hash = "";
	var Locale = Locale || {};
	Locale.langs = <?php echo $tpl['locale_str']; ?>;
	Locale.flagPath = "<?php echo FRAMEWORK_LIBS_PATH; ?>/img/flags/";
	(function ($) {
		$(function() {
			$(".multilang").multilang({
				langs: Locale.langs,
				flagPath: Locale.flagPath,
				tooltip: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sit amet faucibus enim.",
				select: function (event, ui) {
					// Callback, e.g. ajax requests or whatever
				}
			});
		});
	})(jQuery_1_8_2);

	var Grid = Grid || {};
	var myLabel = myLabel || {};
	myLabel.name = "<?php __('lblName'); ?>";
	myLabel.sku = "<?php __('product_sku'); ?>";
	myLabel.exported = "<?php __('lblExport'); ?>";
	myLabel.delete_selected = "<?php __('delete_selected'); ?>";
	myLabel.delete_confirmation = "<?php __('delete_confirmation'); ?>";
	myLabel.status = "<?php __('lblStatus'); ?>";
	myLabel.no_extras = "<?php echo __('lblNoExtrasFound', true) . '<br/><br/>'; ?>";
	myLabel.no_attrs = "<?php echo __('lblNoAttributesFound', true) . '<br/><br/>'; ?>";
	</script>
	<?php
}
?>