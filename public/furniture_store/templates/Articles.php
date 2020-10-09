<?php
use App\Models\WidgetModel;
?>
  <!-- Breadcrumbs -->
  <section class="breadcrumbs-custom-inset">
    <div class="breadcrumbs-custom context-dark">
      <div class="container">
        <?php if (!empty($tpl['category'])):?>
        <h2 class="breadcrumbs-custom-title"><?php echo !empty($tpl['category']['name'])? $tpl['category']['name'] : __i18n('search');?></h2>
        <?php $controller->renderWidget(WidgetModel::WIDGET_TYPE_BREADCUM, null, [
            'page_type' => 'article_category'
        ]);?>
        <?php else: ?>
         <h2 class="breadcrumbs-custom-title"><?php __i18n('Search results');?></h2>
         <ul class="breadcrumbs-custom-path">
          	<li><a href="/"><?php __i18n('home');?></a> </li>
          	<li><?php __i18n('Search results');?></li>
         </ul>     
        <?php endif;?>
      </div>
      <div class="box-position" style="background-image: url(public/asago/assets/images/bg-breadcrumbs.jpg);"></div>
    </div>
  </section>
  <!-- Classic blog-->
  <section class="section section-xl bg-default text-md-left">
    <div class="container">
      <div class="row row-70">
        <div class="col-lg-8">
        <?php foreach ($tpl['article_arr'] as $article):?>
          <!-- Post Classic-->
          <article class="post post-classic">
            <h4 class="post-classic-title"><a href="<?php echo $article['url'];?>"><?php echo $article['article_name'];?></a></h4>
            <a class="post-classic-figure" href="<?php echo $article['url'];?>"><img src="<?php echo $article['avatar_file'];?>" alt="<?php echo $article['article_name'];?>" width="770" height="430"/></a>
          </article>
         <?php endforeach;?>
       <!--=======  pagination  =======-->
        <?php
        	if (isset($tpl['paginator']))
        	{
        	 	if($tpl['paginator']['count'] > $tpl['paginator']['row_count'])
        	 	{
        			?>
        			<div class="pagination-wrap">
        				<nav aria-label="Page navigation">
    					<ul class="pagination">
    						<?php
    						if ($tpl['paginator']['pages'] > 1 && $tpl['paginator']['page'] > 1)
    						{ 
    							$i = $tpl['paginator']['page'] - 1;
    							?><li class="<?php echo $tpl['paginator']['page'] == $i?'page-item active': 'page-item'; ?>"><a href="<?php echo $tpl['category']['url'];?>?page=<?php echo $i; ?>"><?php echo $tpl['paginator']['page'] == $i?'<span class="page-link">'.$i.'</span>': $i; ?></a></li><?php
    						}
    						for ($i = 1; $i <= $tpl['paginator']['pages']; $i++)
    						{
    						    ?><li class="<?php echo $tpl['paginator']['page'] == $i?'page-item active': 'page-item'; ?>"><a href="<?php echo $tpl['category']['url'];?>?page=<?php echo $i; ?>"><?php echo $tpl['paginator']['page'] == $i?'<span class="page-link">'.$i.'</span>': $i; ?></a></li><?php
    						}
    						if ($tpl['paginator']['pages'] > $tpl['paginator']['page'])
    						{
    							$i = $tpl['paginator']['page'] + 1;
    							?><li class="<?php echo $tpl['paginator']['page'] == $i?'page-item active': 'page-item'; ?>"><a href="<?php echo $tpl['category']['url'];?>?page=<?php echo $i; ?>"><?php echo $tpl['paginator']['page'] == $i?'<span class="page-link">'.$i.'</span>': $i; ?></a></li><?php
    						}
    						?>
    					</ul>
    					</nav>
        			</div>
        			<?php
        	 	}
        	}
        ?>
        <!--=======  End of pagination  =======-->
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
                  <h5>Categories</h5>
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
                  <h5>Popular tags</h5>
                  <div class="post-sidebar-item-inset">
                    <div class="group-xs group-middle justify-content-start"><a class="badge badge-white" href="#">
                        <svg xmlns="https://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="27px" viewbox="0 0 16 27" enable-background="new 0 0 16 27" xml:space="preserve">
                          <path d="M0,0v6c4.142,0,7.5,3.358,7.5,7.5S4.142,21,0,21v6h16V0H0z"></path>
                        </svg>
                        <div>Flooring</div></a><a class="badge badge-white" href="#">
                        <svg xmlns="https://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="27px" viewbox="0 0 16 27" enable-background="new 0 0 16 27" xml:space="preserve">
                          <path d="M0,0v6c4.142,0,7.5,3.358,7.5,7.5S4.142,21,0,21v6h16V0H0z"></path>
                        </svg>
                        <div>Tips</div></a><a class="badge badge-white" href="#">
                        <svg xmlns="https://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="27px" viewbox="0 0 16 27" enable-background="new 0 0 16 27" xml:space="preserve">
                          <path d="M0,0v6c4.142,0,7.5,3.358,7.5,7.5S4.142,21,0,21v6h16V0H0z"></path>
                        </svg>
                        <div>Stone</div></a><a class="badge badge-white" href="#">
                        <svg xmlns="https://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="27px" viewbox="0 0 16 27" enable-background="new 0 0 16 27" xml:space="preserve">
                          <path d="M0,0v6c4.142,0,7.5,3.358,7.5,7.5S4.142,21,0,21v6h16V0H0z"></path>
                        </svg>
                        <div>Trends</div></a><a class="badge badge-white" href="#">
                        <svg xmlns="https://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="27px" viewbox="0 0 16 27" enable-background="new 0 0 16 27" xml:space="preserve">
                          <path d="M0,0v6c4.142,0,7.5,3.358,7.5,7.5S4.142,21,0,21v6h16V0H0z"></path>
                        </svg>
                        <div>News</div></a>
                    </div>
                  </div>
                </div>
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
