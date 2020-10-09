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
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	?>
	<div id="tabs">
		<ul>
			<li><a href="#tabs-1"><?php __('menuInstall'); ?></a></li>
			<li><a href="#tabs-2"><?php __('menuSeo'); ?></a></li>
		</ul>
		<div id="tabs-1">
			<?php UtilComponent::printNotice(__('lblInstallJs1_title', true), __('lblInstallJs1_body', true), false, false); ?>

			<form action="" method="get" class="form form">
				<fieldset class="fieldset white">
					<?php
					if (count($tpl['locale_arr']) > 1)
					{
						?>
						<legend><?php __('lblInstallConfig'); ?></legend>
						<?php
					}else{
						?>
						<legend><?php __('menuCategories'); ?></legend>
						<?php
					} 
					?>
					<p>
						<label class="title"><?php __('lblInstallCategory'); ?></label>
						<select class="form-field w300" name="install_category">
							<option value="">-- <?php __('lblAllCatgories'); ?> --</option>
							<?php
							foreach ($tpl['category_arr'] as $category)
							{
								?><option value="<?php echo $category['data']['id']; ?>"><?php echo str_repeat("-----", $category['deep']) . " " .SanitizeComponent::html($category['data']['name']); ?></option><?php
							}
							?>
						</select>
					</p>
					<?php if (count($tpl['locale_arr']) > 1) : ?>
					<p>
						<label class="title"><?php __('lblInstallConfigLocale'); ?></label>
						<select class="form-field w200" name="install_locale">
							<option value="">-- <?php __('lblChoose'); ?> --</option>
							<?php
							foreach ($tpl['locale_arr'] as $locale)
							{
								?><option value="<?php echo $locale['id']; ?>"><?php echo SanitizeComponent::html($locale['title']); ?></option><?php
							}
							?>
						</select>
					</p>
					<p>
						<label class="title"><?php __('lblInstallConfigHide'); ?></label>
						<span class="left">
							<input type="checkbox" name="install_hide" value="1" />
						</span>
					</p>
					<?php endif; ?>
			
				</fieldset>
			</form>
			<fieldset class="fieldset white">
				<legend><?php __('lblInstallCode'); ?></legend>
			<p style="margin: 10px 0 7px; font-weight: bold"><?php __('lblInstallJs1_1'); ?></p>
			<textarea class="form-field w700 textarea_install" id="install_code" style="overflow: auto; height:100px">
&lt;link href="<?php echo $controller->baseUrl().FRAMEWORK_LIBS_PATH . '/css/'; ?>.bootstrap.min.css" type="text/css" rel="stylesheet" /&gt;
&lt;link href="<?php echo $controller->baseUrl(); ?>index.php?controller=Front&action=LoadCss" type="text/css" rel="stylesheet" /&gt;
&lt;script type="text/javascript" src="<?php echo $controller->baseUrl(); ?>index.php?controller=Front&action=Load"&gt;&lt;/script&gt;</textarea>

			<p style="margin: 20px 0 7px; font-weight: bold"><?php __('lblInstallJs2_1'); ?></p>
			<textarea class="form-field w700 textarea_install" style="overflow: auto; height:40px">&lt;!doctype html&gt;</textarea>
			
			<p style="margin: 20px 0 7px; font-weight: bold"><?php __('lblInstallJs2_2'); ?></p>
			<textarea class="form-field w700 textarea_install" style="overflow: auto; height:80px">&lt;meta http-equiv="Content-type" content="text/html; charset=utf-8" /&gt;
&lt;meta name="viewport" content="width=device-width"&gt;
</textarea>

			<div style="display:none" id="hidden_code">&lt;link href="<?php echo $controller->baseUrl().FRAMEWORK_LIBS_PATH . '/css/'; ?>.bootstrap.min.css" type="text/css" rel="stylesheet" /&gt;
&lt;link href="<?php echo $controller->baseUrl(); ?>index.php?controller=Front&action=LoadCss" type="text/css" rel="stylesheet" /&gt;
&lt;script type="text/javascript" src="<?php echo $controller->baseUrl(); ?>index.php?controller=Front&action=Load"&gt;&lt;/script&gt;</div>
			</fieldset>
		</div>
		
		<div id="tabs-2">
			<?php UtilComponent::printNotice(@$titles['AO30'], @$bodies['AO30']); ?>
			<p style="margin: 20px 0 7px; font-weight: bold"><?php __('lblInstallSeo_1'); ?></p>
			<input type="text" id="uri_page" class="form-field w700" value="myPage.php" />
			
			<p style="margin: 20px 0 7px; font-weight: bold"><?php __('lblInstallSeo_2'); ?></p>
			<textarea class="form-field w700 textarea_install" style="overflow: auto; height:30px">
&lt;meta name="fragment" content="!"&gt;</textarea>

			<p style="margin: 20px 0 7px; font-weight: bold"><?php __('lblInstallSeo_3'); ?></p>
			<textarea class="form-field w700 textarea_install" id="install_htaccess" style="overflow: auto; height:80px">
RewriteEngine On
RewriteCond %{QUERY_STRING} _escaped_fragment_=(.*)
RewriteRule ^myPage.php <?php echo INSTALL_FOLDER; ?>index.php?controller=FrontPublic&action=Router&_escaped_fragment_=%1 [L,NC]</textarea>

			<div style="display: none" id="hidden_htaccess">RewriteEngine On
RewriteCond %{QUERY_STRING} _escaped_fragment_=(.*)
RewriteRule ^::URI_PAGE:: <?php echo INSTALL_FOLDER; ?>index.php?controller=FrontPublic&action=Router&_escaped_fragment_=%1 [L,NC]</div>

			<p style="margin: 20px 0 7px; font-weight: bold"><?php __('lblInstallSeo_4'); ?></p>
			<textarea class="form-field w700 textarea_install" id="install_htaccess_remote" style="overflow: auto; height:80px">
RewriteEngine On
RewriteCond %{QUERY_STRING} _escaped_fragment_=(.*)
RewriteRule ^myPage.php <?php echo $controller->baseUrl(); ?>index.php?controller=FrontPublic&action=Router&_escaped_fragment_=%1 [L,NC,R=302]</textarea>

			<div style="display: none" id="hidden_htaccess_remote">RewriteEngine On
RewriteCond %{QUERY_STRING} _escaped_fragment_=(.*)
RewriteRule ^::URI_PAGE:: <?php echo $controller->baseUrl(); ?>index.php?controller=FrontPublic&action=Router&_escaped_fragment_=%1 [L,NC,R=302]</div>
		</div>
	</div>
	<?php
}
?>