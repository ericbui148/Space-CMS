<a class="brand" href="/"><img src="<?php echo $logo; ?>"
	alt="Asago" width="90"></a>
<?php if($this->isAdmin()):?>
<a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminLogos&action=Index" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
<?php endif;?>