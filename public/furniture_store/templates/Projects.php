<?php
use App\Models\WidgetModel;
?>
<!-- Breadcrumbs -->
<section class="breadcrumbs-custom-inset">
<div class="breadcrumbs-custom context-dark">
  <div class="container">
    <h2 class="breadcrumbs-custom-title">
    <?php echo $tpl['category']['name'];?>
        <?php if($controller->isAdmin()):?>
         <a href="<?php echo $controller->baseUrl();?>index.php?controller=AdminArticleCategories&action=Update&id=<?php echo $tpl['arr']['id'];?>" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
    	<?php endif;?>
    </h2>
    <?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_BREADCUM, null, [
        'page_type' => 'article_category'
    ]);?>
  </div>
  <div class="box-position" style="background-image: url(public/asago/assets/images/bg-breadcrumbs.jpg);"></div>
</div>
</section>
<!-- Grid Gallery-->
<section class="section section-xl bg-default text-center isotope-wrap">
<div class="container">
  <div class="isotope-filters isotope-filters-horizontal">
    <button class="isotope-filters-toggle button button-md button-icon button-icon-right button-default-outline button-wapasha" data-custom-toggle="#isotope-1" data-custom-toggle-hide-on-blur="true"><span class="icon fa fa-caret-down"></span>Filter</button>
    <?php $controller->renderWidgetByName('projects_menu');?>
  </div>
  <?php if (!empty($tpl['article_arr'])):?>
  <div class="row row-50 isotope" data-lightgallery="group">
  	<?php foreach ($tpl['article_arr'] as $project):?>
    <div class="col-md-6 col-lg-4 isotope-item" data-filter="Type 3">
      <!-- Thumbnail Modern-->
      <article class="thumbnail thumbnail-modern thumbnail-sm">
      <a href="<?php echo $project['url'];?>"><img src="<?php echo $project['avatar_file'];?>" alt="<?php echo $project['article_name'];?>" width="370" height="303"/></a>
        <div class="thumbnail-modern-caption">
          <h5 class="thumbnail-modern-title"><a href="<?php echo $project['url'];?>"><?php echo $project['article_name'];?></a></h5>
          <p class="thumbnail-modern-subtitle"><?php echo $project['short_description'];?></p>
        </div>
      </article>
    </div>
    <?php endforeach;?>
  </div>
  <?php endif;?>
</div>
</section>