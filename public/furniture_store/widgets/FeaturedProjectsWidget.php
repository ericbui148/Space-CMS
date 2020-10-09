<?php if(!empty($article_arr)):?>
<!-- Blog Post Loop -->
<?php foreach ($article_arr as $project):?>
    <div class="col-sm-6 col-lg-4">
    	<div class="oh-desktop">
    		<!-- Thumbnail Classic-->
    		<article
    			class="thumbnail thumbnail-mary thumbnail-sm wow slideInLeft"
    			data-wow-delay="0s">
    			<div class="thumbnail-mary-figure">
    				<a href="<?php echo $project['url'];?>"><img src="<?php echo $project['avatar_file'];?>" alt="<?php echo $project['name'];?>" width="370"></a>
    			</div>
    			<div class="thumbnail-mary-caption">
    				<h5 class="thumbnail-mary-title">
    					<?php echo $project['name'];?>
    				</h5>
    			</div>
    		</article>
    	</div>
    </div>
<?php endforeach;?>

<?php endif;?>