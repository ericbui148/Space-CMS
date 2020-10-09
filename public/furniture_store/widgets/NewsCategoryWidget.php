<?php if(!empty($article_arr)):?>
<!-- Blog Post Loop -->
<div class="blog-posts">
	<?php foreach ($article_arr as $post):?>
		<div class="post post-loop  col-md-4">
		<div class="post-thumbs">
			<a href="<?php echo $post['url'];?>"><img alt="<?php echo $post['name'];?>" src="<?php echo $post['avatar_file'];?>"></a>
		</div>
		<div class="post-entry">
			<div class="post-meta"><span class="pub-date"><?php echo date('d-m-Y',strtotime($post['on_date']));?></span></div>
			<h2><a href="<?php echo $post['url'];?>"><?php echo $post['name'];?></a></h2>
			<p><?php echo $post['short_description'];?></p>
			<a class="btn btn-alt" href="<?php echo $post['url'];?>">Chi tiết</a>
		</div>
		</div>
	<?php endforeach;?>

</div>
<?php endif;?>