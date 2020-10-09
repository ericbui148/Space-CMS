<?php if(!empty($node_arr)):?>
    <?php foreach ($node_arr as $node):?>
		<div class="col-sm-6 col-lg-4">
			<div class="oh">
				<!-- box Spotlight-->
				<article class="box-sportlight wow slideInDown"
					data-wow-delay="0s">
					<a class="box-sportlight-figure" href="<?php echo $node['data']['link']?>"><img
						src="<?php echo $node['data']['avatar'];?>" alt="<?php echo $node['data']['name'];?>" width="370"></a>
					<div class="box-sportlight-caption">
						<h5 class="box-sportlight-title">
							<a href="<?php echo $node['data']['link']?>"><?php echo $node['data']['name'];?></a>
						</h5>
						<div class="box-sportlight-arrow"></div>
					</div>
				</article>
			</div>
		</div>
    <?php endforeach;?>
    <?php if($this->isAdmin()):?>
         <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminMenus&action=Update&id=<?php echo $menu_id?>" data-rte-cmd="removeElement" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
    <?php endif;?>
<?php endif;?>