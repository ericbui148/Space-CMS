<?php
return array(
	array('GET','', array('controller' => 'Site', 'action' => 'Index')),
    array('GET','dang-ky-tai-khoan', array('controller' => 'Site', 'action' => 'UserRegister')),
    array('GET','admin', array('controller' => 'Admin', 'action' => 'Index')),
    array('GET','sitemap.xml', array('controller' => 'Site', 'action' => 'SiteMap')),
    array('GET','404', array('controller' => 'Site', 'action' => 'Page404')),
	array('GET','page/[a][*]?-[i:id].html', array('controller' => 'Site', 'action' => 'Page')),
	array('GET','trang/[a][*]?-[i:id].html', array('controller' => 'Site', 'action' => 'Page')),
	array('GET','article/[a][*]?-[i:id].html', array('controller' => 'Site', 'action' => 'Article')),
	array('GET','baiviet/[a][*]?-[i:id].html', array('controller' => 'Site', 'action' => 'Article')),
	array('GET','danh-muc-bai-viet/[a][*]?-[i:id]', array('controller' => 'Site', 'action' => 'ArticleCategory')),
	array('GET','article-category/[a][*]?-[i:id]', array('controller' => 'Site', 'action' => 'ArticleCategory')),
	array('GET','danh-muc-trang/[a][*]?-[i:id]', array('controller' => 'Site', 'action' => 'PageCategory')),
	array('GET','page-category/[a][*]?-[i:id]', array('controller' => 'Site', 'action' => 'PageCategory')),
    array('GET','products', array('controller' => 'ShopCart', 'action' => 'Products')),
    array('GET','giohang', array('controller' => 'ShopCart', 'action' => 'Cart')),
    array('GET','thanhtoan', array('controller' => 'ShopCart', 'action' => 'Checkout')),
    array('POST','thanhtoan', array('controller' => 'ShopCart', 'action' => 'Checkout')),
    array('GET','thanhtoan', array('controller' => 'ShopCart', 'action' => 'Checkout')),
    array('GET','xemlaidonhang', array('controller' => 'ShopCart', 'action' => 'Preview')),
    array('GET','lienhe', array('controller' => 'Site', 'action' => 'Contact')),
    array('POST','lienhe', array('controller' => 'Site', 'action' => 'Contact')),
    array('GET','articles', array('controller' => 'Site', 'action' => 'Articles')),
    array('GET','projects', array('controller' => 'Site', 'action' => 'Articles')),
    array('GET','xac-nhan-don-hang', array('controller' => 'ShopCart', 'action' => 'OrderConfirm')),
);
?>