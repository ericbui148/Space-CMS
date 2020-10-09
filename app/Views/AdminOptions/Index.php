<?php
use App\Controllers\Components\UtilComponent;

if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			UtilComponent::printNotice(NULL, $status[2]);
			break;
	}
} else {
	$titles = __('error_titles', true);
	$bodies = __('error_bodies', true);
	if (isset($_GET['err']))
	{
		UtilComponent::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	
	switch (@$_GET['tab'])
	{
		case 6:
			UtilComponent::printNotice(@$titles['AO26'], @$bodies['AO26']);
			include dirname(__FILE__) . '/elements/terms.php';
			break;
		case 5:
			UtilComponent::printNotice(@$titles['AO25'], @$bodies['AO25'], false);
			include dirname(__FILE__) . '/elements/confirmation.php';
			break;
		case 4:
			UtilComponent::printNotice(@$titles['AO24'], @$bodies['AO24']);
			include dirname(__FILE__) . '/elements/taxes.php';
			break;
		case 3:
			UtilComponent::printNotice(@$titles['AO23'], @$bodies['AO23']);
			include dirname(__FILE__) . '/elements/tab.php';
			break;
		case 2:
			UtilComponent::printNotice(@$titles['AO22'], @$bodies['AO22']);
			include dirname(__FILE__) . '/elements/tab.php';
			break;
		case 1:
			UtilComponent::printNotice($titles['AO21'], @$bodies['AO21']);
			echo 
			include dirname(__FILE__) . '/elements/tab.php';
			break;
		default:
			include dirname(__FILE__) . '/elements/tab.php';
	}
}
?>