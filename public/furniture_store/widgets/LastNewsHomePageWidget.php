<?php 
use App\Controllers\Components\UtilComponent;
?>
<?php if(!empty($article_arr)):?>
<!--=======  blog post slider wrapper  =======-->
                        
<div class="blog-post-slider-wrapper">
    <div class="ht-slick-slider"
    data-slick-setting='{
        "slidesToShow": 3,
        "slidesToScroll": 1,
        "dots": false,
        "autoplay": false,
        "autoplaySpeed": 5000,
        "speed": 1000,
        "arrows": true,
        "prevArrow": {"buttonClass": "slick-prev", "iconClass": "ion-ios-arrow-left" },
        "nextArrow": {"buttonClass": "slick-next", "iconClass": "ion-ios-arrow-right" }
    }'
    data-slick-responsive='[
        {"breakpoint":1501, "settings": {"slidesToShow": 3} },
        {"breakpoint":1199, "settings": {"slidesToShow": 3} },
        {"breakpoint":991, "settings": {"slidesToShow": 2} },
        {"breakpoint":767, "settings": {"slidesToShow": 2, "arrows": false} },
        {"breakpoint":575, "settings": {"slidesToShow": 2, "arrows": false} },
        {"breakpoint":479, "settings": {"slidesToShow": 2, "arrows": false} }
    ]'
    >
		<?php foreach ($article_arr as $article):?>
        <!--=======  single blog post  =======-->
        
        <div class="slider-single-post">
            <div class="slider-single-post__image">
                <a href="<?php echo $article['url'];?>">
                    <img src="<?php echo $article['avatar_file'];?>" class="img-fluid" alt="<?php echo $article['name'];?>" >
                </a>
            </div>
            <div class="slider-single-post__content">
                <h3 class="post-title"><a href="<?php echo $article['url'];?>"><?php echo $article['name'];?></a></h3>
                <div class="post-meta">
                    <p class="post-date"><?php echo date('d-m-Y', strtotime($article['on_date']));?></p>
                </div>
            </div>
        </div>
        
        <!--=======  End of single blog post  =======-->
        <?php endforeach;?>

    </div>
</div>

<!--=======  End of blog post slider wrapper  =======-->
<?php endif;?>