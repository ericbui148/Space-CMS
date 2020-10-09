<?php
use App\Controllers\Components\UtilComponent;
?>
<?php if(!empty($article_arr)):?>
<ul class="simple-post-list">
	<?php foreach ($article_arr as $post):?>
	<li>
		<div class="post-image">
			<div class="img-thumbnail">
				<a href="<?php echo $this->getBaseUrl().__('prefix_article_detail', true, false).'/'.UtilComponent::post_slug($post['name']).'-'.$post['id'].'.html';?>">
					<img width="50" height="50" src="<?php echo $post['avatar_file'];?>" alt="<?php echo $post['name'];?>">
				</a>
			</div>
		</div>
		<div class="post-info">
			<a href="<?php echo $this->getBaseUrl().__('prefix_article_detail', true, false).'/'.UtilComponent::post_slug($post['name']).'-'.$post['id'].'.html';?>"><?php echo $post['name'];?></a>
			<div class="post-meta">
				 <i class="fa fa-eye"></i> <?php echo $post['num_view'];?> <?php __('iconView')?></p>
			</div>
		</div>
	</li>
	<?php endforeach;?>
</ul>
<?php endif;?>