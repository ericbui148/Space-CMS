<?php
namespace App\Controllers\Components;

class ShortCodeComponent extends AppComponent 
{
	protected $shortcodeRegex = '/\[[a-zA-Z0-9_]*\]/s';

	protected $widgetRender;

	protected $controller;

	public function __construct($controller)
	{
		$this->controller = $controller;
		$this->widgetRender = new WidgetRenderComponent($this->controller);
	}

	public function doShortCode($content)
	{
		//Get all widget short code from content
		$shortCodes = [];
		preg_match_all($this->shortcodeRegex, $content, $shortCodes);
		$shortCodes = isset($shortCodes[0])? $shortCodes[0] : [];
		if (!empty($shortCodes)) {
			foreach($shortCodes as $shortCode) {
				$widgetName = str_replace(['[', ']'], '', $shortCode);
				$widgetContent = $this->widgetRender->output($widgetName);
				$content = str_replace($shortCode, $widgetContent, $content);
			}
		}
		return $content;
	}
	
}