<?php if(!empty($article_arr)):?>
<?php foreach ($article_arr as $post):?>
    <!-- Post Minimal-->
    <article class="post post-minimal"><a class="post-minimal-figure" href="<?php echo $post['url'];?>"><img src="<?php echo $post['avatar_file'];?>" alt="<?php echo $post['name'];?>" width="232" height="138"/></a>
      <p class="post-minimal-title"><a href="<?php echo $post['url'];?>"><?php echo $post['name'];?></a></p>
    </article>
<?php endforeach;?>
<?php endif;?>