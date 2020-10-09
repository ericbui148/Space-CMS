<?php
$map = array(
	'Step0' => 0,
	'Step1' => 1,
	'Step2' => 2,
	'Step3' => 3,
	'Step4' => 4,
	'Step5' => 5,
	'Step6' => 6,
	'Step7' => 7
);
?>
<div class="progress-wrap">
	<div class="_ps _ps1<?php echo $map[$_GET['action']] == 1 ? '_cur' : ($map[$_GET['action']] > 1 ? '_pas' : NULL); ?>"></div>
	<div class="_ps _ps2<?php echo $map[$_GET['action']] == 2 ? '_cur' : ($map[$_GET['action']] > 2 ? '_pas' : NULL); ?>"></div>
	<div class="_ps _ps3<?php echo $map[$_GET['action']] == 3 ? '_cur' : ($map[$_GET['action']] > 3 ? '_pas' : NULL); ?>"></div>
	<div class="_ps _ps4<?php echo $map[$_GET['action']] == 4 ? '_cur' : ($map[$_GET['action']] > 4 ? '_pas' : NULL); ?>"></div>
	<div class="_ps _ps5<?php echo $map[$_GET['action']] == 5 ? '_cur' : ($map[$_GET['action']] > 5 ? '_pas' : NULL); ?>"></div>
	<div class="_ps _ps6<?php echo $map[$_GET['action']] == 6 ? '_cur' : ($map[$_GET['action']] > 6 ? '_pas' : NULL); ?>"></div>
	<div class="_ps _ps7<?php echo $map[$_GET['action']] == 7 ? '_cur' : ($map[$_GET['action']] > 7 ? '_pas' : NULL); ?>"></div>
	<div class="progress-back"></div>
	<div class="progress-front" style="width: <?php echo 165.166 * ($map[$_GET['action']] - 1); ?>px"></div>
</div>