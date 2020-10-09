<?php
use App\Controllers\Components\UtilComponent;
?>
<div id="boxClone" style="display: none">
	<div class="extraBox">
	
		<table class="tblExtras">
			<tbody>
				<tr>
					<td style="width: 85px;">Kiểu</td>
					<td style="width: 90px">Bắt buộc</td>
					<td style="width: 350px">
						<span class="boxSingle">Tên</span>
						<span class="boxMulti" style="display: none">Tiêu đề</span>
					</td>
					<td class=""><span class="boxSingle">Giá</span></td>
				</tr>
				<tr>
					<td class="align_top">
						<select name="extra_type[{INDEX}]" class="form-field">
							<?php
							$product_extra_types = [
							    'single' => 'Đơn',
							    'multi' => 'Nhóm'
							];
							krsort($product_extra_types);
							foreach ($product_extra_types as $k => $v)
							{
								?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
							}
							?>
						</select>
					</td>
					<td class="align_top"><label><input type="checkbox" name="extra_is_mandatory[{INDEX}]" value="1" /></label></td>
					<td class="align_top tdExtrasClean" colspan="2">
						<div class="boxSingle">
							<table style="width: 100%">
								<tbody>
									<tr>
										<td class="align_top">
										<?php
										foreach ($tpl['lp_arr'] as $v)
										{
											?>
											<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
												<span class="inline_block">
													<input type="text" name="i18n[<?php echo $v['id']; ?>][extra_name][{INDEX}]" class="form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" />
													<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
													<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
													<?php endif; ?>
												</span>
											</p>
											<?php
										}
										?>
										</td>
										<td class="align_top">
											<span class="form-field-custom form-field-custom-before">
												<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
												<input type="text" name="extra_price[{INDEX}]" class="form-field w80 align_right required number" />
											</span>
										</td>
										<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="icon-delete btnDeleteExtraTmp" title="Xoá"></a></td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="boxMulti" style="display: none">
							<table style="width: 100%">
								<tbody>
									<tr>
										<td style="width: 342px">
										<?php
										foreach ($tpl['lp_arr'] as $v)
										{
											?>
											<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
												<span class="inline_block">
													<input type="text" name="i18n[<?php echo $v['id']; ?>][extra_title][{INDEX}]" class="form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" disabled="disabled" />
													<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
													<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
													<?php endif; ?>
												</span>
											</p>
											<?php
										}
										?>
										</td>
										<td></td>
										<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="icon-delete btnDeleteExtraTmp" title="Xoá"></a></td>
									</tr>
									<tr>
										<td><?php __('product_extra_name'); ?></td>
										<td><?php __('product_extra_price'); ?></td>
										<td></td>
									</tr>
									<tr>
										<td class="align_top">
										<?php
										foreach ($tpl['lp_arr'] as $v)
										{
											?>
											<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
												<span class="inline_block">
													<input type="text" name="i18n[<?php echo $v['id']; ?>][extra_name][{INDEX}][{X}]" class="form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" disabled="disabled" />
													<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
													<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
													<?php endif; ?>
												</span>
											</p>
											<?php
										}
										?>
										</td>
										<td class="align_top">
											<span class="form-field-custom form-field-custom-before">
												<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
												<input type="text" name="extra_price[{INDEX}][{X}]" class="form-field w80 align_right required number" disabled="disabled" />
											</span>
										</td>
										<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="icon-delete btnRemoveExtraItem"></a></td>
									</tr>
								</tbody>
							</table>
							<input type="button" class="button btnAddExtraItem l5" data-index="{INDEX}" value="Thêm" />
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	
	</div>
</div>

<table id="boxCloneTbl" style="display: none">
	<tbody>
		<tr>
			<td class="align_top">
			<?php
			foreach ($tpl['lp_arr'] as $v)
			{
				?>
				<p class="multilang-wrap" data-index="<?php echo $v['id']; ?>" style="display: <?php echo (int) $v['is_default'] === 0 ? 'none' : NULL; ?>">
					<span class="inline_block">
						<input type="text" name="i18n[<?php echo $v['id']; ?>][extra_name][{INDEX}][{X}]" class="form-field w300<?php echo (int) $v['is_default'] === 0 ? NULL : ' required'; ?>" />
						<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
						<span class="multilang-input"><img src="<?php echo BASE_URL . FRAMEWORK_LIBS_PATH . '/img/flags/' . $v['file']; ?>" alt="" /></span>
						<?php endif; ?>
					</span>
				</p>
				<?php
			}
			?>
			<?php /*<input type="text" name="extra_name[{INDEX}][{X}]" class="form-field w250" />*/ ?>
			</td>
			<td class="align_top">
				<span class="form-field-custom form-field-custom-before">
					<span class="form-field-before"><abbr class="form-field-icon-text"><?php echo UtilComponent::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
					<input type="text" name="extra_price[{INDEX}][{X}]" class="form-field w80 align_right required number" />
				</span>
			</td>
			<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="icon-delete btnRemoveExtraItem"></a></td>
		</tr>
	</tbody>
</table>

<div id="dialogDeleteExtra" style="display: none" title="Xoá">
<?php __('product_extra_delete_desc'); ?>
</div>
<div id="dialogCopyExtra" style="display: none" title="Xoá"></div>