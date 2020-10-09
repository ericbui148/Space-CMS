<?php
echo $html;
?>
<?php if($this->isAdmin()):?>
  <center>
  <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminWidgets&action=Update&id=<?php echo $widget_id?>" style="color:red!important; font-size: 14px !important;"><i class="fa fa-pencil" aria-hidden="true"></i></a>
  </center>
<?php endif;?>