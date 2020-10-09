<?php
use App\Controllers\Components\UtilComponent;
?>
<?php if (!empty($product_arr)):?>
<div class="list-popular-product">
	<?php foreach ($product_arr as $product):?>
    <div class="list-popular-product-item">
      <!-- Product Minimal-->
      <article class="product-minimal unit unit-spacing-md">
      <?php $imagePath = !empty($product['pic'])? $product['pic'] : 'app/web/img/frontend/noimg.jpg';?>
        <div class="unit-left"><a class="product-minimal-figure" href="<?php echo $product['url'];?>"><img src="<?php echo $imagePath;?>" alt="<?php echo $product['name'];?>" width="108" height="100"/></a></div>
        <div class="unit-body">
          <h6 class="product-minimal-title"><a href=<?php echo $product['url'];?>"><?php echo $product['name'];?></a></h6>
        </div>
      </article>
    </div>
    <?php endforeach;?>
</div>
<?php if($this->isAdmin()):?>
  <center>
  <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminWidgets&action=Update&id=<?php echo $widget_id?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
  </center>
<?php endif;?>
<?php endif;?>