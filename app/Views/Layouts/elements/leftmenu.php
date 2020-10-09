<?php
use Core\Framework\Objects;

if (Objects::getPlugin('OneAdmin') !== NULL)
{
	$controller->requestAction(array('controller' => 'OneAdmin', 'action' => 'Menu'));
}
?>

<div class="leftmenu-top"></div>
<div class="leftmenu-middle">
	<ul class="menu mainmenu">
		<li>
            <a href="#"
               class="<?php echo $controller->request->controller == 'AdminForum' && $controller->request->action == 'Index' ? 'menu-focus' : NULL; ?>"><span><i
                            class="fa fa-home" aria-hidden="true"></i>&nbsp;</span><?php __('menuDashboard'); ?> <i
                        class="fa fa-caret-down"></i></a>
            <ul class="submenu">
                <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&amp;action=ShopcartDashboard">Bán hàng</a></li>
                <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&amp;action=ContentDashboard">Nội dung</a></li>
            </ul>
        </li>
		<li>
		    <a href="#" class="<?php echo $controller->request->controller == 'AdminArticle' ? 'menu-focus' : NULL; ?>"><span><i class="fa fa-file"></i>&nbsp;</span><?php __('menuCms'); ?> <i class="fa fa-caret-down"></i></a>
		    <ul class="submenu">
		        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminArticle&amp;action=Index"><?php __('cms_article'); ?></a></li>
		        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminPage&amp;action=Index"><?php __('cms_page'); ?></a></li>
		        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminWidgets&amp;action=Index"><?php __('cms_widget'); ?></a></li>
		        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMenus&amp;action=Index"><?php __('cms_menu'); ?></a></li>
		        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSliders&amp;action=Index"><?php __('cms_gallery_slider'); ?></a></li>
		        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminFiles&amp;action=Index"><?php __('cms_file_manager'); ?></a></li>
		        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminLogos&amp;action=Index"><?php __('cms_logo'); ?></a></li>
		    </ul>
		</li>
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOrders&amp;action=Index" class="<?php echo $_GET['controller'] == 'AdminOrders' ? 'menu-focus' : NULL; ?>"><span><i class="fa fa-file-text" aria-hidden="true"></i></span><?php __('menuOrders'); ?></a></li>
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Invoice&action=Invoices" class="<?php echo ($_GET['controller'] == 'Invoice' && $_GET['action'] == 'Invoices') ? 'menu-focus' : NULL; ?>"><span><i class="fa fa-credit-card" aria-hidden="true"></i></span>Hoá đơn</a></li>
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminClients&amp;action=Index" class="<?php echo $_GET['controller'] == 'AdminClients' ? 'menu-focus' : NULL; ?>"><span><i class="fa fa-users" aria-hidden="true"></i></span><?php __('menuClients'); ?></a></li>
		<li>
		    <a href="#" class="<?php echo $controller->request->controller == 'AdminProduct' ? 'menu-focus' : NULL; ?>"><span><i class="fa fa-product-hunt" aria-hidden="true"></i>&nbsp;</span><?php __('menuProducts'); ?> <i class="fa fa-caret-down"></i></a>
		    <ul class="submenu">
		    	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminProducts&amp;action=Index">Danh sách sản phẩm</a></li>
		    	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminProducts&amp;action=Create">Thêm sản phẩm</a></li>
		        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminCategories&amp;action=Index">Danh mục</a></li>
		        <li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminProducts&amp;action=Stock">Kho</a></li>
		    </ul>
		</li>
		<li>
		    <a href="#" class="<?php echo $controller->request->controller == 'AdminForms' ? 'menu-focus' : NULL; ?>"><span><i class="fa fa-columns" aria-hidden="true"></i>&nbsp;</span><?php __('menuForms'); ?> <i class="fa fa-caret-down"></i></a>
		    <ul class="submenu">
		    	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminForms&amp;action=Index"><?php __('menuForms'); ?></a></li>
		    	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSubmissions&amp;action=Index">Submittions</a></li>
		    </ul>
		</li>
		<li>
		    <a href="#" class="<?php echo $controller->request->controller == 'AdminSubmissions' ? 'menu-focus' : NULL; ?>"><span><i class="fa fa-newspaper-o" aria-hidden="true"></i>&nbsp;</span>News Letters<i class="fa fa-caret-down"></i></a>
		    <ul class="submenu">
		    	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Send">Send</a></li>
		    	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminGroups&amp;action=Index">Groups</a></li>
		    	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminSubscribers&amp;action=Index">Subscribers</a></li>
		    	<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminMessages&amp;action=Index">Messages</a></li>
		    </ul>
		</li>			
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminReports&amp;action=Index" class="<?php echo $_GET['controller'] == 'AdminReports' ? 'menu-focus' : NULL; ?>"><span><i class="fa fa-file-text-o" aria-hidden="true"></i></span><?php __('menuReport'); ?></a></li>
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminVouchers&amp;action=Index" class="<?php echo $_GET['controller'] == 'AdminVouchers' ? 'menu-focus' : NULL; ?>"><span><i class="fa fa-gift" aria-hidden="true"></i></span><?php __('menuVouchers'); ?></a></li>
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminTags&amp;action=Index" class="<?php echo $_GET['controller'] == 'AdminTags' ? 'menu-focus' : NULL; ?>"><span><i class="fa fa-tags" aria-hidden="true"></i></span> Tags</a></li>
		<?php if ($controller->isAdmin()):?>
		<li>
			<a href="#"><span><i class="fa fa-cogs"></i>&nbsp;</span>Cài đặt <i class="fa fa-caret-down"></i></a>
			<ul class="submenu">
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminCmsSettings&amp;action=Index">Cài đặt chung</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOptions&amp;action=Index&tab=1">Cài đặt web</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOptions&action=Index&tab=2">Shop</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOptions&action=Index&tab=3">Form thanh toán</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminOptions&action=Index&tab=4">Vận chuyển & Thuế</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Invoice&action=Index">Hoá đơn</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Locale&action=Index&tab=1">Bản dịch</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Locale&action=Locales">Ngôn ngữ</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Country&action=Index">Quốc gia</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Backup&action=Index">Khôi phục dữ liệu</a></li>
				<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=AdminUsers&amp;action=Index"><?php __('menuUsers'); ?></a></li>
			</ul>
		</li>
		<?php endif;?>			
		<li><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=Admin&amp;action=Logout"><span><i class="fa fa-power-off"></i>&nbsp;</span><?php __('menuLogout'); ?></a></li>
	</ul>
</div>
<div class="leftmenu-bottom"></div>