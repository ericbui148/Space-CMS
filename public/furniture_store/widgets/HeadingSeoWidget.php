<?php if(!empty($keywords)):?>
	<div class="hidden">
		<?php 
		foreach($keywords as $word) {
			echo "<h1>".$word."</h1>";
		}
		?>
	</div>
<?php endif;?>