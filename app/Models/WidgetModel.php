<?php
namespace App\Models;

class WidgetModel extends AppModel
{
	const WIDGET_TYPE_HTML = 1;
	const WIDGET_TYPE_MENU = 2;
	const WIDGET_TYPE_SLIDER = 3;
	const WIDGET_TYPE_BEST_SELLING_PRODUCTS = 4;
	const WIDGET_TYPE_FEATURED_PRODUCT = 5;
	const WIDGET_TYPE_DAILY_PRODUCT = 6;
	const WIDGET_TYPE_LASTEST_PRODUCT = 7;
	const WIDGET_TYPE_RELATED_PRODUCT = 8;
	const WIDGET_TYPE_LASTEST_ARTICLES = 9;
	const WIDGET_TYPE_FEATURED_NEWS = 10;
	const WIDGET_TYPE_CONTACT_FORM = 11;
	const WIDGET_TYPE_SOCIAL_LINK = 12;
	const WIDGET_TYPE_LINK = 13;
	const WIDGET_TYPE_VIDEO = 14;
	const WIDGET_TYPE_ADDRESS = 15;
	const WIDGET_TYPE_SUBSCRIBER = 16;
	const WIDGET_TYPE_GALLERY = 17;
	const WIDGET_TYPE_IMAGE = 18;
	const WIDGET_TYPE_LOGO = 19;
	const WIDGET_TYPE_TESTIMONIAl = 20;
	const WIDGET_TYPE_LIVECHAT = 21;
	const WIDGET_TYPE_CART = 22;
	const WIDGET_TYPE_POPUP_FORM = 23;
	const WIDGET_TYPE_SURVEY_FORM = 24;
	const WIDGET_TYPE_LANGUAGE = 25;
	const WIDGET_TYPE_SUPPORT_ONLINE = 26;
	const WIDGET_TYPE_MARQUEEN = 27;
	const WIDGET_TYPE_IFRAME = 29;
	const WIDGET_TYPE_TAG = 30;
	const WIDGET_TYPE_ADVERTISEMENT = 31;
	const WIDGET_TYPE_LASTEST_NEWS_HOME_PAGE = 32;
	const WIDGET_TYPE_PARTNER = 33;
	const WIDGET_TYPE_META = 34;
	const WIDGET_TYPE_ARTICLE_CAT = 35;
	const WIDGET_TYPE_PRODUCT_CAT = 36;
	const WIDGET_TYPE_PRODUCT_PROMOTION = 37;
	const WIDGET_TYPE_SECTION_CATEGORY_LIST = 38;
	const WIDGET_TYPE_BREADCUM = 39;
	const WIDGET_TYPE_NEW_WEBSITE = 40;
	const WIDGET_TYPE_HEADING_SEO = 41;
	const WIDGET_TYPE_SAME_MENU_LEVEL = 42;
	const WIDGET_TYPE_SECTION_SAME_CATEGORY = 43;
	const WIDGET_TYPE_MENU_LEFT = 44;
	const WIDGET_TYPE_MENU_RIGHT = 45;
	const WIDGET_TYPE_MULTIPLE_LANGUAGE = 46;
	const WIDGET_TYPE_APPOINTMENT_FORM = 47;
	const WIDGET_TYPE_MENU_MOBILE = 48;
	const WIDGET_TYPE_BREADCUM_SHOP = 49;
	const WIDGET_TYPE_SEARCH_PROPERTIES = 50;
	const WIDGET_TYPE_NEW_PRODUCTS = 51;
	const WIDGET_TYPE_FEATURED_PRODUCTS = 52;
	const WIDGET_TYPE_MENU_IN_BODY = 53;
	const WIDGET_TYPE_SLIDER_IN_BODY = 54;
	const WIDGET_TYPE_SUB_CATEGORY = 55;
	const WIDGET_TYPE_LASTEST_PAGES = 56;
	const WIDGET_TYPE_VIEWED_PRODUCTS = 57;
	const WIDGET_TYPE_GOOLE_MAP = 58;
	const WIDGET_TYPE_FORM_SEARCH = 59;
	const WIDGET_TYPE_SALE_PRODUCTS = 60;
	const WIDGET_TYPE_SECTION_CATEGORIES = 61;
	const WIDGET_TYPE_FEATURED_FORUM_QUESTIONS = 62;
	const WIDGET_TYPE_FORUM_MEMBER_STATISTIC = 63;
	const WIDGET_TYPE_FORUM_STATISTIC = 64;
	const WIDGET_TYPE_AD_BANNER_LEFT = 65;
	const WIDGET_TYPE_AD_BANNER_BOTTOM = 66;
	const WIDGET_TYPE_FORUM_BREADCRUM = 67;
	const WIDGET_TYPE_FORUM_TERM_CONDITION = 68;
	const WIDGET_TYPE_PAGE_CAT = 69;

	protected $primaryKey = 'id';
	
	protected $table = 'widgets';
	
	protected $schema = array(
		array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'name', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'theme', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'order', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'area_id', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'value', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'type', 'type' => 'int', 'default' => ':NULL'),
		array('name' => 'status', 'type' => 'enum', 'default' => ':NULL'),			
		array('name' => 'created', 'type' => 'datetime', 'default' => ':NOW()'),			
		array('name' => 'modified', 'type' => 'datetime', 'default' => ':NOW()'),
		array('name' => 'uuid_code', 'type' => 'varchar', 'default' => ':NULL'),
		array('name' => 'params', 'type' => 'text', 'default' => ':NULL'),
		array('name' => 'template', 'type' => 'varchar', 'default' => ':NULL')		
	);
	
	public $i18n = array('content');
	
	public static function factory($attr=array())
	{
		return new WidgetModel($attr);
	}
}
?>