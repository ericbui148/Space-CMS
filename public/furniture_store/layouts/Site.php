<?php
use App\Models\WidgetModel;
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">
<head>
<meta name="format-detection" content="telephone=no">
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<base href="<?php echo $controller->baseUrl();?>" />
<title><?php if(@$tpl['page'] == 'home_page') {$controller->renderWidget(WidgetModel::WIDGET_TYPE_META, 'title');} else {echo $controller->title;}?></title>	
<meta name="description" content="<?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_META, 'meta_description');?>"/>
<meta name="keywords" content="<?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_META, 'meta_keywords');?>"/>
<meta name="author" content="<?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_META, 'title');?>"/>
<meta property="og:title" content="<?php if(@$tpl['page'] == 'home_page') {$controller->renderWidget(WidgetModel::WIDGET_TYPE_META, 'title');} else {echo $controller->title;}?>"/>
<meta property="og:description" name="description" content="<?php echo @$controller->og_description;?>"/>
<meta property="og:image" itemprop="thumbnailUrl" content=""/>
<meta property="og:type" content="article" />
<meta property="og:site_name" content="<?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_META, 'title');?>"/>
<?php $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";?>
<meta property="og:url" itemprop="url" content="<?php echo $actual_link;?>"/>
<?php $css_arr = $controller->getFrontPageCss();?>
<?php if($css_arr):?>
    <?php foreach ($css_arr[$tpl['page']] as $script):?>
        <link href="<?php echo $controller->baseUrl().'public/'.THEME_NAME.'/assets/'.$script;?>?version=4" rel="stylesheet" async>
    <?php endforeach;?>
<?php endif;?>


<style>
.ie-panel {
	display: none;
	background: #212121;
	padding: 10px 0;
	box-shadow: 3px 3px 5px 0 rgba(0, 0, 0, .3);
	clear: both;
	text-align: center;
	position: relative;
	z-index: 1;
}

html.ie-10 .ie-panel, html.lt-ie-10 .ie-panel {
	display: block;
}
</style>
</head>
<body>
	<div class="ie-panel">
		<a href="http://windows.microsoft.com/en-US/internet-explorer/"><img
			src="public/asago/assets/images/ie8-panel-warning_bar_0000_us.jpg" height="42"
			width="820"
			alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today."></a>
	</div>
	<div class="preloader">
		<div class="preloader-body">
			<div class="cssload-container">
				<span></span><span></span><span></span><span></span>
			</div>
		</div>
	</div>
	<div class="page">
		<!-- Page Header-->
		<header class="section page-header">
			<!-- RD Navbar-->
			<div class="rd-navbar-wrap<?php echo @$tpl['page'] == 'home_page'?'rd-navbar-modern-wrap' : null;?>">
				<nav class="rd-navbar rd-navbar-modern"
					data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed"
					data-md-layout="rd-navbar-fixed"
					data-md-device-layout="rd-navbar-fixed"
					data-lg-layout="rd-navbar-static"
					data-lg-device-layout="rd-navbar-fixed"
					data-xl-layout="rd-navbar-static"
					data-xl-device-layout="rd-navbar-static"
					data-xxl-layout="rd-navbar-static"
					data-xxl-device-layout="rd-navbar-static"
					data-lg-stick-up-offset="46px" data-xl-stick-up-offset="46px"
					data-xxl-stick-up-offset="70px" data-lg-stick-up="true"
					data-xl-stick-up="true" data-xxl-stick-up="true">
					<div class="rd-navbar-main-outer">
						<div class="rd-navbar-main">
							<!-- RD Navbar Panel-->
							<div class="rd-navbar-panel">
								<!-- RD Navbar Toggle-->
								<button class="rd-navbar-toggle"
									data-rd-navbar-toggle=".rd-navbar-nav-wrap">
									<span></span>
								</button>
								<!-- RD Navbar Brand-->
								<div class="rd-navbar-brand">
									<?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_LOGO, 'Logo');?>
								</div>
							</div>
							<div class="rd-navbar-main-element">
								<div class="rd-navbar-nav-wrap">
									<!-- RD Navbar Search-->
									<div class="rd-navbar-search">
										<button class="rd-navbar-search-toggle"
											data-rd-navbar-toggle=".rd-navbar-search">
											<span></span>
										</button>
										<form class="rd-search" action="<?php echo BASE_URL?>products" method="GET">
											<div class="form-wrap">
												<label class="form-label" for="rd-navbar-search-form-input"><?php __i18n("Product Search..")?></label>
												<input class="rd-navbar-search-form-input form-input"
													id="rd-navbar-search-form-input" type="text" name="q"
													autocomplete="off">
												<div class="rd-search-results-live"
													id="rd-search-results-live"></div>
												<button
													class="rd-search-form-submit fl-bigmug-line-search74"
													type="submit"></button>
											</div>
										</form>
									</div>
									<!-- RD Navbar Nav-->
									<?php $controller->renderWidgetByName('Main menu');?>
								</div>
								<div class="rd-navbar-project-hamburger"
									data-multitoggle=".rd-navbar-main"
									data-multitoggle-blur=".rd-navbar-wrap"
									data-multitoggle-isolate="data-multitoggle-isolate">
									<div class="project-hamburger">
										<span class="project-hamburger-arrow-top"></span><span
											class="project-hamburger-arrow-center"></span><span
											class="project-hamburger-arrow-bottom"></span>
									</div>
									<div class="project-close">
										<span></span><span></span>
									</div>
								</div>
							</div>
							
							<div>
								<a class="scSelectorLocale" title="Vietnamese" href="" data-id="1"><img src="app/web/libs/framework/libs/wp/img/flags/vn.png" alt="Vietnamese"></a>&nbsp;
								<a class="scSelectorLocale" title="English - UK" href="" data-id="2"><img src="app/web/libs/framework/libs/wp/img/flags/gb.png" alt="English - UK"></a>
							</div>
						</div>
					</div>
				</nav>
			</div>
		</header>
		<?php include $content_tpl;?>		
		<!-- Page Footer-->
		<footer class="section footer-variant-2 footer-modern context-dark">
			<div class="footer-variant-2-content">
				<div class="container">
					<div class="row row-40 justify-content-between">
						<div class="col-sm-6 col-lg-4 col-xl-3">
							<div class="oh-desktop">
								<div class="wow slideInRight" data-wow-delay="0s">
									<div class="footer-brand">
										<?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_LOGO, 'Logo');?>
									</div>
									<p><?php $controller->renderWidgetByName('footer_company_short_desc');?></p>
									<ul class="footer-contacts d-inline-block d-md-block">
										<li>
											<div class="unit unit-spacing-xs">
												<div class="unit-left">
													<span class="icon fa fa-phone"></span>
												</div>
												<div class="unit-body">
													<?php $controller->renderWidgetByName('footer_company_phone');?>
												</div>
											</div>
										</li>
										<li>
											<div class="unit unit-spacing-xs">
												<div class="unit-left">
													<span class="icon fa fa-clock-o"></span>
												</div>
												<div class="unit-body">
													<p><?php $controller->renderWidgetByName('footer_company_working_time');?></p>
												</div>
											</div>
										</li>
										<li>
											<div class="unit unit-spacing-xs">
												<div class="unit-left">
													<span class="icon fa fa-location-arrow"></span>
												</div>
												<div class="unit-body">
													<?php $controller->renderWidgetByName('footer_company_address');?>
												</div>
											</div>
										</li>
									</ul>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-lg-4 col-xl-4">
							<div class="oh-desktop">
								<div class="inset-top-18 wow slideInDown" data-wow-delay="0s">
									<h5 class="text-spacing-75"><?php $controller->renderWidgetByName('footer_company_newsletter_title');?></h5>
									<p><?php $controller->renderWidgetByName('footer_company_newsletter_desc');?></p>
									<form class="rd-form rd-mailform"
										data-form-output="form-output-global"
										data-form-type="subscribe" method="post"
										action="bat/rd-mailform.php">
										<div class="form-wrap">
											<input class="form-input" id="subscribe-form-5-email"
												type="email" name="email"
												data-constraints="@Email @Required"><label
												class="form-label" for="subscribe-form-5-email">Enter Your
												E-mail</label>
										</div>
										<button
											class="button button-block button-secondary button-ujarak"
											type="submit">Subscribe</button>
									</form>
									<div class="group-lg group-middle">
										<p class="footer-social-list-title">Follow Us</p>
										<div>
											<?php $controller->renderWidgetByName('footer_follow_we_on_social');?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-3 col-xl-3">
							<div class="oh-desktop">
								<div class="inset-top-18 wow slideInLeft" data-wow-delay="0s">
									<h5 class="text-spacing-75"><?php $controller->renderWidgetByName('footer_gallery_title');?></h5>
									<?php $controller->renderWidgetByName('home_gallery');?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="footer-variant-2-bottom-panel">
				<div class="container">
					<!-- Rights-->
					<div class="group-sm group-sm-justify">
						<p class="rights">
							<span>&copy;&nbsp;</span><span class="copyright-year"></span> <?php $controller->renderWidgetByName('footer_copyright');?>
						</p>
						<p class="rights">
							<a href="/">Privacy Policy.</a>
						</p>
					</div>
				</div>
			</div>
		</footer>
	</div>
	<!-- Global Mailform Output-->
	<div class="snackbars" id="form-output-global"></div>
	<!-- Javascript-->
	<!--=============================================
    =            JS files        =
    =============================================-->
    <?php $js_arr = $controller->getFrontPageJs();?>
    <?php if($js_arr):?>
    <?php foreach ($js_arr[$tpl['page']] as $script):?>
        <script type="text/javascript" src="<?php echo $controller->baseUrl().'public/'.THEME_NAME.'/assets/'.$script;?>?version=1"></script>
    <?php endforeach;?>
    <?php endif;?>
     <?php if (!empty($controller->option_arr ['o_disable_copy']) && $controller->option_arr ['o_disable_copy'] == 1):?>
    <script type="text/javascript">
        $(document).ready(function(){
        $('*').bind('cut copy paste contextmenu', function (e) {
            e.preventDefault();
        })});
    </script>
    <?php endif;?>   
    <!--=====  End of JS files ======-->
	<?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_META, 'support_widget');?>
</body>