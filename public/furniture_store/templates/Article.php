<?php
use App\Models\WidgetModel;
?>
  <!-- Breadcrumbs -->
  <section class="breadcrumbs-custom-inset">
    <div class="breadcrumbs-custom context-dark">
      <div class="container">
        <h2 class="breadcrumbs-custom-title"><?php echo $tpl['arr']['article_name'];?></h2>
        <?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_BREADCUM, null, [
            'page_type' => 'article'
        ]);?>
      </div>
      <div class="box-position" style="background-image: url(public/asago/assets/images/bg-breadcrumbs.jpg);"></div>
    </div>
  </section>
  <!-- Blog Post-->
  <section class="section section-xl bg-default text-left">
    <div class="container">
      <div class="row row-70">
        <div class="col-lg-8">
          <div class="blog-post">
            <!-- Post Classic-->
            <article class="post post-classic">
              <h4 class="post-classic-title"><?php echo $tpl['arr']['article_name'];?></h4>
				<?php if($controller->isAdmin()):?>
                 <a href="<?php echo $controller->baseUrl();?>index.php?controller=AdminArticle&action=Update&id=<?php echo $tpl['arr']['id'];?>" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
            	<?php endif;?>
			  <?php echo $tpl['arr']['article_content'];?>
            	<div class="blog-post-bottom-panel group-md group-justify">
                <div class="group-md group-middle"><span class="social-title">Follow US</span>
                  <div>
                    <?php $controller->renderWidgetByName('footer_follow_we_on_social');?>
                  </div>
                </div>
              </div>
             </article>
            </div>
         </div>
        <div class="col-lg-4">
          <!-- Post Sidebar-->
          <div class="post-sidebar post-sidebar-inset">
            <div class="row row-lg row-60">
              <div class="col-sm-6 col-lg-12">
                <div class="post-sidebar-item">
                  <!-- RD Search Form-->
                  <form class="rd-search form-search form-post-search" action="<?php echo BASE_URL?>articles" method="GET">
                    <div class="form-wrap">
                      <label class="form-label" for="search-form"><?php __i18n('search')?></label>
                      <input class="form-input" id="search-form" type="text" name="q" autocomplete="off">
                      <button class="button-search fl-bigmug-line-search74" type="submit"></button>
                    </div>
                  </form>
                </div>
                <div class="post-sidebar-item">
                  <h5><?php __i18n('categories')?></h5>
                  <div class="post-sidebar-item-inset">
                    <?php $controller->renderWidgetByName('news_right_menu');?>
                  </div>
                </div>
                <div class="post-sidebar-item">
                  <h5><?php __i18n('featured_posts');?></h5>
                  <div class="post-sidebar-item-inset">
                   <?php $controller->renderWidgetByName('featured_news_right');?>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-lg-12">
                <div class="post-sidebar-item">
                  <h5>Newsletter</h5>
                  <div class="post-sidebar-item-inset">
                    <!-- RD Mailform-->
                    <form class="rd-form rd-mailform" data-form-output="form-output-global" data-form-type="subscribe" method="post" action="bat/rd-mailform.php">
                      <div class="form-wrap">
                        <input class="form-input" id="subscribe-form-4-email" type="email" name="email" data-constraints="@Email @Required">
                        <label class="form-label" for="subscribe-form-4-email">Enter Your E-mail</label>
                      </div>
                      <div class="form-button">
                        <button class="button button-block button-primary button-pipaluk" type="submit">Subscribe</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>