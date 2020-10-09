<?php
namespace App\Controllers;

use App\Controllers\Components\UtilComponent;
use App\Controllers\Components\ShortCodeComponent;
use App\Models\RouterModel;
use App\Models\ArticleModel;
use App\Models\ArticleViewerModel;
use App\Models\ACategoryModel;
use App\Models\ACategoryArticleModel;
use App\Models\PageViewerModel;
use App\Models\PageModel;
use App\Models\PCategoryPageModel;
use App\Models\TagModel;
use App\Models\CartModel;
use App\Controllers\Components\ShoppingCartComponent;
use App\Plugins\Gallery\Models\GalleryModel;
use App\Plugins\Locale\Models\LocaleModel;
use App\Models\ItemSortModel;
use App\Controllers\Components\CommonComponent;
use App\Models\PCategoryModel;
use Core\Framework\Components\EmailComponent;
use App\Models\UserModel;

class SiteController extends AppController {

	public $js;
	public $css;
	protected $commonComponent;
	protected $shortCodeComponent;
	protected $emailComponent;
	
	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function __construct() {
		if(!isset($_SESSION[$this->defaultLocale])) {
			$this->setLocaleId(1);
		}
		$this->loadDefaultFrontendLang();
		$this->commonComponent = new CommonComponent();
		$this->shortCodeComponent = new ShortCodeComponent($this);
		$this->emailComponent = new EmailComponent();
		$this->theme = $this->getTheme();
		$this->setLayout ( 'Site');
	}

	public function Index() {
	    $this->checkMaintainMode();
	    $this->set('locale_id', $this->getFrontendLocaleId());
	    $this->set('page','home_page');
	    $this->setModel('Cart', CartModel::factory());
	    if (!empty($_SESSION[$this->defaultHash])) {
	        $this->cart = new ShoppingCartComponent($this->getModel('Cart'), $_SESSION[$this->defaultHash]);
	        $this->set('cart_arr', $this->cart->getAll());
	    }

	}

	public function Page() {
		$this->checkMaintainMode();
		$id = $this->request->params['id'];
		$arr = PageModel::factory()->select('t1.*, t2.content as `page_name`, t3.content as `page_content`, t4.content as `meta_keyword`, t5.content as `meta_description`, t6.content as `meta_title`, t7.content as `sub_title`')
		->join('MultiLang', "t2.model='Page' AND t2.foreign_id=t1.id AND t2.field='page_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->join('MultiLang', "t3.model='Page' AND t3.foreign_id=t1.id AND t3.field='page_content' AND t3.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->join('MultiLang', "t4.model='Page' AND t4.foreign_id=t1.id AND t4.locale='" . $this->getFrontendLocaleId () . "' AND t4.field='meta_keyword'", 'left outer')
		->join('MultiLang', "t5.model='Page' AND t5.foreign_id=t1.id AND t5.locale='" . $this->getFrontendLocaleId () . "' AND t5.field='meta_description'", 'left outer')
		->join('MultiLang', "t6.model='Page' AND t6.foreign_id=t1.id AND t6.locale='" . $this->getFrontendLocaleId () . "' AND t6.field='meta_title'", 'left outer')
		->join('MultiLang', "t7.model='Page' AND t7.foreign_id=t1.id AND t7.locale='" . $this->getFrontendLocaleId () . "' AND t7.field='sub_title'", 'left outer')
		->find($id)->getData();
		
		$arr['page_content'] = $this->shortCodeComponent->doShortCode($arr['page_content']);

		if (!empty($arr['template'])) {
			$this->setTemplate('Site', $arr['template']);
		}
		if (!empty($arr['layout'])) {
			$this->setLayout($arr['layout']);
		}
		if(!empty($arr)){
			$pageId = $arr['id'];
			$ip = UtilComponent::getClientIp();
			$viewer = PageViewerModel::factory()->where('t1.page_id', $pageId)->where('t1.ip', $ip)->limit(1)->findAll()->getData();
			if(empty($viewer[0])) {
				PageViewerModel::factory(array(
						'page_id' => $pageId,
						'ip' => $ip
				))->insert()->getInsertId();
			}

			$this->title = UtilComponent::html2txt($arr['meta_title']);
			if (empty($this->title)) {
				$this->title = UtilComponent::html2txt($arr['page_name']);
			}
			$this->meta_description = UtilComponent::html2txt($arr['meta_description']);
			$this->meta_keywords = UtilComponent::html2txt($arr['meta_keyword']);
			$this->og_description =  $this->meta_description;
			$this->og_image = $arr['avatar_file'];

			$Shortcode = new ShortCodeComponent($this);
			$arr['page_content'] = $Shortcode->doShortCode($arr['page_content']);
			$this->set('arr', $arr);
		}
		
		$this->set('page','page');
		
	}
	
	
	public function Article() {
		$this->checkMaintainMode();
		$id = $this->request->params['id'];
		$arr = ArticleModel::factory()->select('t1.*, t2.content as `article_name`, t3.content as `article_content`, t4.content as `meta_keyword`, t5.content as `meta_description`, t6.content as `meta_title`, t7.content as `sub_title`')
		->join('MultiLang', "t2.model='Article' AND t2.foreign_id=t1.id AND t2.field='article_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->join('MultiLang', "t3.model='Article' AND t3.foreign_id=t1.id AND t3.field='article_content' AND t3.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->join('MultiLang', "t4.model='Article' AND t4.foreign_id=t1.id AND t4.locale='" . $this->getFrontendLocaleId () . "' AND t4.field='meta_keyword'", 'left outer')
		->join('MultiLang', "t5.model='Article' AND t5.foreign_id=t1.id AND t5.locale='" . $this->getFrontendLocaleId () . "' AND t5.field='meta_description'", 'left outer')
		->join('MultiLang', "t6.model='Article' AND t6.foreign_id=t1.id AND t6.locale='" . $this->getFrontendLocaleId () . "' AND t6.field='meta_title'", 'left outer')
		->join('MultiLang', "t7.model='Article' AND t7.foreign_id=t1.id AND t7.locale='" . $this->getFrontendLocaleId () . "' AND t7.field='sub_title'", 'left outer')
		->find($id)->getData();
	
		if (!empty($arr['template'])) {
			$this->setTemplate('Site', $arr['template']);
		}
		if (!empty($arr['layout'])) {
			$this->setLayout($arr['layout']);
		}
		if(!empty($arr)){
			$articleId = $arr['id'];
			$ip = UtilComponent::getClientIp();
			$viewer = ArticleViewerModel::factory()->where('t1.article_id', $articleId)->where('t1.ip', $ip)->limit(1)->findAll()->getData();
			if(empty($viewer[0])) {
				ArticleViewerModel::factory(array(
				    'article_id' => $articleId,
					'ip' => $ip
				))->insert()->getInsertId();
			}
	
			$this->title = UtilComponent::html2txt($arr['meta_title']);
			if (empty($this->title)) {
				$this->title = UtilComponent::html2txt($arr['article_name']);
			}
			$this->meta_description = UtilComponent::html2txt($arr['meta_description']);
			$this->meta_keywords = UtilComponent::html2txt($arr['meta_keyword']);
			$this->og_description =  $this->meta_description;
			$this->og_image = $arr['avatar_file'];
	
			$arr['article_content'] = $this->shortCodeComponent->doShortCode($arr['article_content']);
			$this->set('arr', $arr);
			$lastestArticles = ArticleModel::factory()
			->select('t1.*, t2.content as `article_name`, (select count(id) from  `article_viewers`as fv where fv.article_id  = t1.id) as `num_view`')
			->join('MultiLang', "t2.model='Article' AND t2.foreign_id=t1.id AND t2.field='article_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
			->orderBy('t1.id desc')->limit(10)->findAll()->getData();
			if (!empty($lastestArticles)) {
			    $lastestArticles = UtilComponent::attachCustomLinks($lastestArticles, RouterModel::TYPE_ARTICLE_CATEGORY, $this->getFrontendLocaleId());
			    $this->set('lastest_articles', $lastestArticles);
			}
			$category = ACategoryArticleModel::factory()->select('t1.*')->where('t1.article_id', $arr['id'])->orderBy('id DESC')->limit(1)->findAll()->first();
			$relateArticles = ArticleModel::factory()
			->select('t1.*, t2.content as `article_name`, (select count(id) from  `article_viewers`as fv where fv.article_id  = t1.id) as `num_view`')
			->join('MultiLang', "t2.model='Article' AND t2.foreign_id=t1.id AND t2.field='article_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
			->where(sprintf ( "t1.id IN (SELECT `article_id` FROM `%s` WHERE `category_id` = ".$category['category_id'].")", ACategoryArticleModel::factory ()->getTable ()))
			->orderBy('t1.id desc')->limit(6)->findAll()->getData();
			if (!empty($relateArticles)) {
			    $relateArticles = UtilComponent::attachCustomLinks($relateArticles, RouterModel::TYPE_ARTICLE, $this->getFrontendLocaleId());
			    $this->set('relate_articles', $relateArticles);
			}
			$galleryArr = GalleryModel::factory()
			->select("t1.*, t2.content as `title`, t3.content as `description`")
			->join ( 'MultiLang', "t2.model='Gallery' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='" . $this->getFrontendLocaleId () . "'", 'left outer' )
			->join ( 'MultiLang', "t3.model='Gallery' AND t3.foreign_id=t1.id AND t3.field='description' AND t3.locale='" . $this->getFrontendLocaleId () . "'", 'left outer' )
			->where('t1.foreign_id', (int)$arr['slider_id'])
			->orderBy('t1.sort asc')
			->findAll()
			->getData();
			$this->set('gallery_arr', $galleryArr);
			$tags = TagModel::factory()->select('t1.*, t2.content as name')
			->join ('MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Tag' AND t2.locale = '" . $this->getFrontendLocaleId () . "' AND t2.field = 'name'", 'left' )
			->join('ArticleTag',"t3.tag_id = t1.id")
			->findAll()
			->getData();
			if (!empty($tags)) {
			    $tags = UtilComponent::attachCustomLinks($tags, RouterModel::TYPE_TAG, $this->getFrontendLocaleId());
			    $this->set('tag_arr', $tags);
			}
			$category_arr = ACategoryModel::factory ()->getNode ( $this->getFrontendLocaleId(), 1);
			foreach ($category_arr as &$cat) {
			    $cat['data'] = UtilComponent::attachSingleCustomLink($cat['data'], RouterModel::TYPE_ARTICLE_CATEGORY, $this->getFrontendLocaleId());
			}
			$this->set('category_arr', $category_arr);
		}
	
		$this->set('page','page');
	
	}

	public function Pages() {
	    $this->checkMaintainMode();
	    $this->set('page', 'pages');
	    $category_id = @$this->request->params['category_id'];
	    $q = @$this->request->params['q'];
	    $page = @$this->request->params['page'];
	    $limit = @$this->request->params['limit'];
	    
	    $pageModel = PageModel::factory()
	    ->join('MultiLang', "t2.model='Page' AND t2.foreign_id=t1.id AND t2.field='page_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
	    ->join('MultiLang', "t3.model='Page' AND t3.foreign_id=t1.id AND t3.field='page_short_description' AND t3.locale='".$this->getFrontendLocaleId()."'", 'left outer')
	    ->join('MultiLang', "t4.model='Page' AND t4.foreign_id=t1.id AND t4.field='sub_title' AND t4.locale='".$this->getFrontendLocaleId()."'", 'left outer');
	    if (!empty($category_id)) {
	        $pageModel->join('ItemSort', 't5.foreign_id = t1.id AND t5.type ='.ItemSortModel::TYPE_PAGE_CATEGORY.' AND t5.foreign_type_id = '.$category_id, 'left');
	    }
	    
	    if (isset($category_id) && (int) $category_id > 0) {
	        $category = PCategoryModel::factory()
	        ->select('t1.*, t2.content AS `name`')
	        ->join('MultiLang', "t2.foreign_id = t1.id AND t2.model = 'PCategory' AND t2.locale = '".$this->getFrontendLocaleId()."' AND t2.field = 'name'", 'left outer')
	        ->find($category_id)
	        ->getData();
	        
	        if (!empty($category['template'])) {
	            $this->setTemplate('Site', $category['template']);
	        }
	        $this->set('category', $category);
	        $this->title = $category['name'];
	        
	        $pageModel->where(sprintf ( "t1.id IN (SELECT `page_id` FROM `%s` WHERE `category_id` = ".$category['id'].")", PCategoryPageModel::factory ()->getTable ()));
	    }
	    
	    if (isset($q) && ! empty($q)) {
	        $q = str_replace(array(
	            '_',
	            '%'
	        ), array(
	            '\_',
	            '\%'
	        ), $pageModel->escapeStr(trim(urldecode($q))));
	        $pageModel->where("(t2.content LIKE '%$q%' OR t3.content LIKE '%$q%')");
	        $this->title = "Kết quả tìm kiếm";
	    }
	    $page = isset($page) && (int) $page > 0 ? intval($page) : 1;
	    $row_count = $limit? $limit : 7;
	    $offset = ((int) $page - 1) * $row_count;
	    $count = $pageModel->findCount()->getData();
	    $pages = ceil($count / $row_count);
	    if (!empty($category_id)) {
	        $page_arr = $pageModel->select('t1.*, t2.content as `page_name`, t3.content as `short_description`, t4.content as `sub_title`, (select count(id) from  `page_viewers`as fv where fv.page_id  = t1.id) as `num_view`, t5.sort as `sort`')
	        ->limit($row_count, $offset)->orderBy('t5.sort asc, t1.id desc')->findAll()->getData();
	    } else {
	        $page_arr = $pageModel->select('t1.*, t2.content as `page_name`, t3.content as `short_description`, t4.content as `sub_title`, (select count(id) from  `page_viewers`as fv where fv.page_id  = t1.id) as `num_view`')
	        ->limit($row_count, $offset)->orderBy('t1.id desc')->findAll()->getData();
	    }
	    
	    if (!empty($page_arr)) {
	        $page_arr = UtilComponent::attachCustomLinks($page_arr, RouterModel::TYPE_PAGE, $this->getFrontendLocaleId());
	    }
	    if (!empty($page_arr)) {
	        $this->commonComponent->updateSortItems(ItemSortModel::TYPE_PAGE_CATEGORY, $page_arr, $category_id);
	    }
	    $this->set('page_arr', $page_arr);
	    $category_arr = PCategoryModel::factory ()->getNode ( $this->getFrontendLocaleId(), 1);
	    foreach ($category_arr as &$cat) {
	        $cat['data'] = UtilComponent::attachSingleCustomLink($cat['data'], RouterModel::TYPE_PAGE_CATEGORY, $this->getFrontendLocaleId());
	    }
	    $this->set('category_arr', $category_arr);
	    
	    $lastestPages = PageModel::factory()
	    ->select('t1.*, t2.content as `page_name`, (select count(id) from  `page_viewers`as fv where fv.page_id  = t1.id) as `num_view`')
	    ->join('MultiLang', "t2.model='Page' AND t2.foreign_id=t1.id AND t2.field='page_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
	    ->orderBy('t1.id desc')->limit(4)->findAll()->getData();
	    if (!empty($lastestPages)) {
	        $lastestPages = UtilComponent::attachCustomLinks($lastestPages, RouterModel::TYPE_PAGE_CATEGORY, $this->getFrontendLocaleId());
	        $this->set('lastest_pages', $lastestPages);
	    }
	    $tags = TagModel::factory()->select('t1.*, t2.content as name')
	    ->join ('MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Tag' AND t2.locale = '" . $this->getFrontendLocaleId () . "' AND t2.field = 'name'", 'left' )
	    ->join('PageTag',"t3.tag_id = t1.id")
	    ->findAll()
	    ->getData();
	    
	    if (!empty($tags)) {
	        $tags = UtilComponent::attachCustomLinks($tags, RouterModel::TYPE_TAG, $this->getFrontendLocaleId());
	        $this->set('tag_arr', $tags);
	    }
	    
	    $this->set('paginator', compact('pages', 'page', 'count', 'row_count', 'offset'));
	}
	
	public function Articles() {
		$this->checkMaintainMode();
		$this->set('page', 'articles');
		$category_id = (int)@$this->request->params['category_id'];
		$q = @$this->request->params['q'];
		$page = @$this->request->params['page'];
		$limit = @$this->request->params['limit'];

		$articleModel = ArticleModel::factory()
		->join('MultiLang', "t2.model='Article' AND t2.foreign_id=t1.id AND t2.field='article_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->join('MultiLang', "t3.model='Article' AND t3.foreign_id=t1.id AND t3.field='article_short_description' AND t3.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->join('MultiLang', "t4.model='Article' AND t4.foreign_id=t1.id AND t4.field='sub_title' AND t4.locale='".$this->getFrontendLocaleId()."'", 'left outer');
		if (!empty($category_id)) {
		    $articleModel->join('ItemSort', 't5.foreign_id = t1.id AND t5.type ='.ItemSortModel::TYPE_ARTICLE_CATEGORY.' AND t5.foreign_type_id = '.$category_id, 'left');
		}
		if (isset($category_id) && (int) $category_id > 0) {
		    $category = ACategoryModel::factory()
		    ->select('t1.*, t2.content AS `name`')
		    ->join('MultiLang', "t2.foreign_id = t1.id AND t2.model = 'ACategory' AND t2.locale = '".$this->getFrontendLocaleId()."' AND t2.field = 'name'", 'left outer')
		    ->find($category_id)
		    ->getData();
		    
		    if (!empty($category['template'])) {
		        $this->setTemplate('Site', $category['template']);
		    }
		    $category = UtilComponent::attachSingleCustomLink($category, RouterModel::TYPE_ARTICLE_CATEGORY, $this->getFrontendLocaleId());
		    $this->set('category', $category);
		    $this->title = $category['name'];
		    
		    $articleModel->where(sprintf ( "t1.id IN (SELECT `article_id` FROM `%s` WHERE `category_id` = ".$category['id'].")", ACategoryArticleModel::factory ()->getTable ()));
		}
		
		if (isset($q) && ! empty($q)) {
		    $q = str_replace(array(
		        '_',
		        '%'
		    ), array(
		        '\_',
		        '\%'
		    ), $articleModel->escapeStr(trim(urldecode($q))));
		    $articleModel->where("(t2.content LIKE '%$q%' OR t3.content LIKE '%$q%')");
		    $this->title = "Kết quả tìm kiếm";
		}
		$page = isset($page) && (int) $page > 0 ? intval($page) : 1;
		$row_count = $limit? $limit : 15;
		$offset = ((int) $page - 1) * $row_count;
		$count = $articleModel->findCount()->getData();
		$pages = ceil($count / $row_count);
	    $article_arr = $articleModel->select('t1.*, t2.content as `article_name`, t3.content as `short_description`, t4.content as `sub_title`, (select count(id) from  `article_viewers`as fv where fv.article_id  = t1.id) as `num_view`')
	    ->limit($row_count, $offset)->orderBy('t1.on_date desc')->findAll()->getData();

		if (!empty($article_arr)) {
		    $article_arr = UtilComponent::attachCustomLinks($article_arr, RouterModel::TYPE_ARTICLE, $this->getFrontendLocaleId());
		}
		if (!empty($article_arr)) {
		    $this->commonComponent->updateSortItems(ItemSortModel::TYPE_ARTICLE_CATEGORY, $article_arr, $category_id);
		}
		$this->set('article_arr', $article_arr);
		$category_arr = ACategoryModel::factory ()->getNode ( $this->getFrontendLocaleId(), 1);
		foreach ($category_arr as &$cat) {
		    $cat['data'] = UtilComponent::attachSingleCustomLink($cat['data'], RouterModel::TYPE_ARTICLE_CATEGORY, $this->getFrontendLocaleId());
		}
		$this->set('category_arr', $category_arr);
		
		$lastestArticles = ArticleModel::factory()
		->select('t1.*, t2.content as `article_name`, (select count(id) from  `article_viewers`as fv where fv.article_id  = t1.id) as `num_view`')
		->join('MultiLang', "t2.model='Article' AND t2.foreign_id=t1.id AND t2.field='article_name' AND t2.locale='".$this->getFrontendLocaleId()."'", 'left outer')
		->orderBy('t1.id desc')->limit(4)->findAll()->getData();
		if (!empty($lastestArticles)) {
		    $lastestArticles = UtilComponent::attachCustomLinks($lastestArticles, RouterModel::TYPE_ARTICLE_CATEGORY, $this->getFrontendLocaleId());
		    $this->set('lastest_articles', $lastestArticles);
		}
		$tags = TagModel::factory()->select('t1.*, t2.content as name')
		->join ('MultiLang', "t2.foreign_id = t1.id AND t2.model = 'Tag' AND t2.locale = '" . $this->getFrontendLocaleId () . "' AND t2.field = 'name'", 'left' )
		->join('ArticleTag',"t3.tag_id = t1.id")
		->findAll()
		->getData();
		
		if (!empty($tags)) {
		    $tags = UtilComponent::attachCustomLinks($tags, RouterModel::TYPE_TAG, $this->getFrontendLocaleId());
		    $this->set('tag_arr', $tags);
		}
		
		$this->set('paginator', compact('pages', 'page', 'count', 'row_count', 'offset'));
		
	}

	public function Page404() {
		$this->checkMaintainMode();
		$this->title = '404';
		$this->set('page', '404');
	}
	
	public function Contact() {
	    $this->checkMaintainMode();
	    if (!empty($_POST['send_contact'])) {
	        $errorMessages = $this->validateContactForm();
	        if (!empty($errorMessages)) {
	            $this->set('error_messages', $errorMessages);
	        } else {
	            $this->sendContactEmail($_POST);
	            $this->set('send_contact_success', 1);
	        }
	        
	    }
	    $this->title = __i18n('contact', true);
	    $this->set('page', 'contact');
	}
	
	protected  function validateContactForm()
	{
	    $messages = [];
	    if (empty($_POST['name'])) {
	        $messages[] = __i18n("please enter your name", true);
	    }
	    if (empty($_POST['email'])) {
	        $messages[] = __i18n("please enter your email", true);
	    }
	    if (empty($_POST['subject'])) {
	        $messages[] = __i18n("please enter subject", true);
	    }
	    if (empty($_POST['message'])) {
	        $messages[] = __i18n("please enter content", true);
	    }
	    
	    return $messages;
	}
	
	protected function sendContactEmail($data)
	{
	    $this->emailComponent->setContentType ( 'text/html' );
	    $this->emailComponent->setTransport('smtp')
	    ->setSmtpHost($this->tpl['option_arr']['o_smtp_host'])
	    ->setSmtpPort($this->tpl['option_arr']['o_smtp_port'])
	    ->setSmtpUser($this->tpl['option_arr']['o_smtp_user'])
	    ->setSmtpPass($this->tpl['option_arr']['o_smtp_pass'])
	    ->setSmtpSecure($this->tpl['option_arr']['o_smtp_secure']);
	    $users = UserModel::factory()->select("t1.*")->where('role_id', 1)->findAll()->getData();
	    $toEmail = '';
	    foreach($users as $user) {
	        $toEmail .= empty($toEmail)? $user['email'] : ';'.$user['email'];
	    }
	    $title = __i18n('contact', true).'-'.$data['name'];
	    $emailBody = '<p>';
	    $emailBody .= '<strong>Họ tên:</strong> '.@$data['name']. '<br/>';
	    $emailBody .= '<strong>Email:</strong> '.@$data['email']. '<br/>';
	    $emailBody .= '<strong>Title:</strong> '.@$data['subject']. '<br/>';
	    $emailBody .= '<strong>Message:</strong> '.@$data['message']. '<br/>';
	    $emailBody .= '<p>';
	    
	    $this->emailComponent->setTo ( $toEmail )->setFrom ("no-reply@vietnhat.net")->setSubject ( $title )->send ($emailBody);
	}
	
	public function ChangeLocale()
	{
	    $this->setAjax ( true );
	    $localeId = @$_POST['locale_id'];
	    if ($localeId) {
	        $locale = LocaleModel::factory()->find($localeId)->getData();
	        if ($locale) {
	            $_SESSION[FONTEND_TRANS_DICT]['locale_id'] = $localeId;
	            $_SESSION[FONTEND_TRANS_DICT]['lang'] = $locale['language_iso'];
	        }
	        
	    }
	    exit ();
	}


}