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
<!-- Single Project-->
<section class="section section-sm section-first bg-default">
<div class="container">
  <div class="row row-50 justify-content-center align-items-xl-center">
    <div class="col-md-10 col-lg-6 col-xl-7">
      <div class="offset-right-xl-15">
      	<?php if (!empty($tpl['gallery_arr'])):?>
        <!-- Owl Carousel-->
        <div class="owl-carousel owl-dots-white" data-items="1" data-dots="true" data-autoplay="true" data-animation-in="fadeIn" data-animation-out="fadeOut">
        	<?php foreach ($tpl['gallery_arr'] as $gallery):?>
        	<img src="<?php echo $gallery['large_path'];?>" alt="<?php echo $gallery['title'];?>" width="655" height="496"/>
        	<?php endforeach;?>
        </div>
      </div>
      <?php endif;?>
    </div>
    <div class="col-md-10 col-lg-6 col-xl-5">
      <div class="single-project">
        <h4><?php echo $tpl['arr']['article_name'];?></h4>
    	<?php if($controller->isAdmin()):?>
         <a href="<?php echo $controller->baseUrl();?>index.php?controller=AdminArticle&action=Update&id=<?php echo $tpl['arr']['id'];?>" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
    	<?php endif;?>
        <?php echo $tpl['arr']['article_content'];?>
        <div class="divider divider-30"></div>
        <div class="group-md group-middle justify-content-sm-start"><span class="social-title">Follow Us</span>
          <div>
            <?php $controller->renderWidgetByName('footer_follow_we_on_social');?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</section>
<?php if (!empty($tpl['relate_articles'])):?>
<?php 
$previousProject = null;
$nextProject = null;
for($i = 0; $i <= count($tpl['relate_articles']) - 1; $i++) {
    if ($tpl['arr']['id'] == $tpl['relate_articles'][$i]['id']) {
        if ($i > 0) $previousProject = $tpl['relate_articles'][$i - 1];
        if ($i <  count($tpl['relate_articles']) - 1) $nextProject = $tpl['relate_articles'][$i + 1];
    }
}
?>
<?php if ($previousProject || $nextProject):?>
<!-- Project Links-->
<section class="section section-sm section-last bg-default">
<div class="container">
  <div class="project-navigation">
    <div class="row row-30">
      <?php if ($previousProject):?>
      <div class="col-sm-6">
        <div class="project-minimal">
          <div class="unit unit-spacing-lg align-items-center flex-column flex-lg-row text-lg-left">
            <div class="unit-left"><a class="project-minimal-figure" href="#"><img src="<?php echo $previousProject['avatar_file'];?>" alt="" width="168" height="139"/></a></div>
            <div class="unit-body">
              <div class="project-minimal-title"><a href="<?php echo $previousProject['url'];?>"><?php echo $previousProject['article_name'];?></a></div>
            </div>
          </div>
        </div>
      </div>
      <?php endif;?>
      <?php if ($nextProject):?>
      <div class="col-sm-6">
        <div class="project-minimal">
          <div class="unit unit-spacing-lg align-items-center flex-column flex-lg-row-reverse text-lg-right">
            <div class="unit-left"><a class="project-minimal-figure" href="#"><img src="<?php echo $nextProject['avatar_file'];?>" alt="" width="168" height="139"/></a></div>
            <div class="unit-body">
               <div class="project-minimal-title"><a href="<?php echo $nextProject['url'];?>"><?php echo $nextProject['article_name'];?></a></div>
            </div>
          </div>
        </div>
      </div>
      <?php endif;?>
    </div>
    <?php if ($previousProject):?>
    <a class="project-navigation-arrow-prev" href="<?php echo $previousProject['url'];?>"></a>
    <?php endif;?>
    <?php if ($nextProject):?>
    <a class="project-navigation-arrow-next" href="<?php echo $nextProject['url'];?>"></a>
    <?php endif;?>
  </div>
</div>
</section>
<?php endif;?>
<?php endif;?>