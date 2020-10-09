<?php

use App\Controllers\Components\UtilComponent;
use App\Controllers\AppController;

function __($key, $return=false, $escape=false)
{
	return UtilComponent::getField($key, $return, $escape);
}


function __encode($key)
{
	echo AppController::jsonEncode(__($key, true));
}

function __i18n($key, $return=false)
{
    if ($return) return UtilComponent::getFrontendTranslation($key);
    echo UtilComponent::getFrontendTranslation($key);
}
