<?php
namespace App\Controllers\Components\Widgets;

use App\Models\WidgetModel;

class WidgetFactory
{
	/**
	 * Get widget instance
	 * @param Widget $type
	 */
	public static function getInstance($type) 
	{
		$object = null;
		$widgetClass = [
				WidgetModel::WIDGET_TYPE_HTML => 'HtmlWidget',
				WidgetModel::WIDGET_TYPE_MENU => 'MenuWidget',
				WidgetModel::WIDGET_TYPE_SLIDER => 'SliderWidget',
				WidgetModel::WIDGET_TYPE_LASTEST_ARTICLES => 'LastestArticlesWidget',
				WidgetModel::WIDGET_TYPE_LOGO => 'LogoWidget',
				WidgetModel::WIDGET_TYPE_META => 'MetaWidget',
				WidgetModel::WIDGET_TYPE_ARTICLE_CAT => 'NewsCategoryWidget',
		        WidgetModel::WIDGET_TYPE_NEW_PRODUCTS => 'NewProductsWidget',
		        WidgetModel::WIDGET_TYPE_FEATURED_PRODUCTS => 'FeaturedProductsWidget',
		        WidgetModel::WIDGET_TYPE_PRODUCT_PROMOTION => 'PromotionProductsWidget',
				WidgetModel::WIDGET_TYPE_BREADCUM => 'BreadcumWidget',
		        WidgetModel::WIDGET_TYPE_BREADCUM_SHOP => 'BreadcumShopWidget',
				WidgetModel::WIDGET_TYPE_HEADING_SEO => 'HeadingSeoWidget',
				WidgetModel::WIDGET_TYPE_MULTIPLE_LANGUAGE => 'MultipleLanguageWidget',
				WidgetModel::WIDGET_TYPE_LASTEST_PAGES => 'LastPagesWidget',
				WidgetModel::WIDGET_TYPE_GOOLE_MAP => 'GoogleMapWidget',
				WidgetModel::WIDGET_TYPE_SOCIAL_LINK => 'SocialLinkWidget',
		        WidgetModel::WIDGET_TYPE_CART => 'CartWidget'
		];
		if (isset($widgetClass[$type])) {
			$class = __NAMESPACE__.'\\'.$widgetClass[$type];
			$object = new $class();
		}
		
		return $object;
	}
}