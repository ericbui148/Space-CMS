<?php
use App\Models\WidgetModel;
?>
<!-- Breadcrumbs -->
  <section class="breadcrumbs-custom-inset">
    <div class="breadcrumbs-custom context-dark">
      <div class="container">
        <h2 class="breadcrumbs-custom-title"><?php echo $tpl['arr']['page_name'];?></h2>
        <?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_BREADCUM, null, [
            'page_type' => 'page'
        ]);?>
      </div>
      <div class="box-position" style="background-image: url(images/bg-breadcrumbs.jpg);"></div>
    </div>
  </section>
  <!-- Blog Post-->
  <section class="section section-xl bg-default text-left">
    <div class="container">
      <div class="row row-70">
        <div class="col-lg-12">
          <div class="blog-post">
            <!-- Post Classic-->
            <article class="post post-classic">
              <h4 class="post-classic-title"><?php echo $tpl['arr']['page_name'];?></h4>
            <?php if($controller->isAdmin()):?>
            <a href="<?php echo $controller->baseUrl();?>index.php?controller=AdminPage&action=Update&id=<?php echo $tpl['arr']['id']?>" data-rte-cmd="removeElement" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
            <?php endif;?>
			  <?php echo $tpl['arr']['page_content'];?>
            <div class="blog-post-bottom-panel group-md group-justify">
                <div class="group-md group-middle"><span class="social-title">Follow US</span>
                  <div>
                    <?php $controller->renderWidgetByName('footer_follow_we_on_social');?>
                  </div>
                </div>
              </div>
            </div>
          </div>
            
        </div>
      </div>
    </div>
  </section>