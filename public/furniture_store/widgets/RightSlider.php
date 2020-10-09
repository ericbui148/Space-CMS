<?php if($gallery_arr):?>
<div class="well ">
<div style="border: 0px !important;">
<div id="djslider-loader133" class="djslider-loader djslider-loader-default" data-animation='{"auto":"1","looponce":"0","transition":"easeInOutExpo","css3transition":"cubic-bezier(1.000, 0.000, 0.000, 1.000)","duration":400,"delay":3400}' data-djslider='{"id":"133","slider_type":"0","slide_size":233,"visible_slides":"3","direction":"left","show_buttons":"0","show_arrows":"0","preload":"800","css3":"1"}' tabindex="0">
    <div id="djslider133" class="djslider djslider-default" style="height: 279px; width: 694px; max-width: 694px !important;">
        <div id="slider-container133" class="slider-container">
            <ul id="slider133" class="djslider-in">
                <?php foreach ($gallery_arr as $gallery):?>
                <li style="margin: 0 5px 0px 0 !important; height: 279px; width: 228px;">
                    <img class="dj-image" src="<?php echo $gallery['source_path'];?>" alt="<?php echo $gallery['alt'];?>"  style="width: auto; height: 100%;"/>														
                </li>
                <?php endforeach;?>
            </ul>
        </div>
    </div>
</div>
</div>
<div class="djslider-end" style="clear: both" tabindex="0"></div></div>
<?php endif;?>
<?php if($this->isAdmin()):?>
<a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminSliders&action=Update&id=<?php echo $slider_id?>"><i
	class="fa fa-pencil" style="color: red;"></i></a>
<?php endif;?>