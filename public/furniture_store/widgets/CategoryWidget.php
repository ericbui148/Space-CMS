<?php
use App\Controllers\Components\UtilComponent;

?>
<div class="mpin">
    <div class="leadsb1"><?php __('lblStockProductsList')?></div>
    <ul class="menuleft">
        <?php if(!empty($categories)):?>
            <?php foreach ($categories as $cat):?>
                <li><a href="<?php echo $this->getBaseUrl().__('prefix_product_category', true, false).'/'. UtilComponent::post_slug($cat['name']).'-'.$cat['id'].'.html';?>"><?php echo $cat['name'];?></a></li>
            <?php endforeach;?>
        <?php endif;?>
    </ul>
</div>