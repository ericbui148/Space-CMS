<?php
namespace App\Controllers;

use App\Controllers\Components\UtilComponent;
use App\Models\AppModel;
use App\Models\UserModel;
use App\Models\AHistoryModel;
use Core\Framework\Components\ValidationComponent;
use Core\Framework\Components\ImageComponent;
use Core\Framework\Components\EmailComponent;
use Core\Framework\Objects;
use App\Models\ArticleModel;
use App\Models\ProductModel;
use App\Models\ClientModel;
use App\Models\OrderModel;
use App\Models\OrderStockModel;
use App\Models\StockModel;
use App\Models\VoucherModel;
use App\Models\CategoryModel;
use App\Models\ProductCategoryModel;
use App\Models\CmsSettingModel;

class AdminController extends AppController
{
	public $defaultUser = 'admin_user';
	public $requireLogin = true;
	public function __construct($requireLogin = null) {
		$this->setLayout ( 'Admin' );
		if (! is_null ( $requireLogin ) && is_bool ( $requireLogin )) {
			$this->requireLogin = $requireLogin;
		}
		if ($this->requireLogin) {
			if (! $this->isLoged () && ! in_array ( @$_GET ['action'], array (
					'Login',
					'Forgot',
					'Preview' 
			) )) {
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Login" );
			}
		}
	}

	public function afterFilter() {
		parent::afterFilter ();
		if ($this->isLoged () && ! in_array ( @$_GET ['action'], array (
				'Login' 
		) )) {
			$this->appendJs ( 'index.php?controller=Admin&action=Messages', $this->baseUrl(), true );
		}
	}

	public function beforeRender() {
	}

	public function Index() {
		$this->checkLogin ();
		UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=ShopcartDashboard" );
		exit;
	}

	public function ContentDashboard() {
		$this->checkLogin ();
		$this->title = "Bảng điều khiển - Nội dung";
		$info_arr = AppModel::factory ()->prepare ( sprintf ( "SELECT 1,  (SELECT COUNT(*) FROM `%1\$s` WHERE DATE(`created`) = CURDATE() LIMIT 1) AS `today_article`, (SELECT COUNT(*) FROM `%1\$s` WHERE YEARWEEK(`created`, 1) = YEARWEEK(CURDATE(), 1) LIMIT 1) AS `week_article`, (SELECT COUNT(*) FROM `%1\$s` LIMIT 1) AS `total_article`", ArticleModel::factory()->getTable() ) )->exec ( array () )->getData ();
		$article_arr = ArticleModel::factory()->select('t1.id,t1.created, t4.content as name, t3.name as author')->join('UserArticle', 't2.article_id = t1.id', 'left')->join('User', 't3.id = t2.user_id', 'left')->join ( 'MultiLang', "t4.foreign_id = t1.id AND t4.model = 'Article' AND t4.locale = '" . $this->getLocaleId () . "' AND t4.field = 'article_name'", 'left' )->orderBy('t1.created desc')->limit(NUMBER_RECORD_DASHBOARD)->findAll()->getData();
		$userIds = UserModel::factory()->select('t1.id')->findAll()->getDataPair ( null, 'id' );
		$topview_article_arr = ArticleModel::factory()->select('t1.*, t2.content as `name`,  (select count(id) from  `article_viewers`as fv where fv.article_id  = t1.id) as `num_view`')
		->join('MultiLang', "t2.model='Article' AND t2.foreign_id=t1.id AND t2.field='article_name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
		->orderBy('`num_view` desc')->limit(NUMBER_RECORD_DASHBOARD)->findAll()->getData();
		$article_history_arr = AHistoryModel::factory()->select('t1.*, t2.content as `article_name`, t3.name as `user_name`')->join ( 'MultiLang', "t2.foreign_id = t1.article_id AND t2.model = 'Article' AND t2.locale = '" . $this->getLocaleId () . "' AND t2.field = 'article_name'", 'left' )->join('User', 't1.user_id = t3.id', 'left')->join('Article', 't1.article_id = t4.id', 'left')
		->whereIn('t1.user_id', $userIds)
		->orderBy('t1.modified desc')->limit(NUMBER_RECORD_DASHBOARD)->findAll()->getData();
		$this->set ( 'info_arr', $info_arr )->set('article_arr', $article_arr)->set('topview_article_arr', $topview_article_arr)->set('article_history_arr', $article_history_arr);
	}
	
	public function ShopcartDashboard()
	{
	    $this->checkLogin();
	    if ($this->isAdmin()) {
	        $ProductModel = ProductModel::factory();
	        $ClientModel = ClientModel::factory();
	        $OrderModel = OrderModel::factory()->select("t1.*, t2.client_name")
	        ->join("Client", "t1.client_id=t2.id")
	        ->orderBy('t1.created DESC')
	        ->limit(6)
	        ->findAll();
	        $order_arr = $OrderModel->getData();
	        $product_ordered_arr = OrderStockModel::factory()->select('t1.product_id, t2.uuid, t2.id as order_id, t3.content as name')
	        ->join('Order', "t1.order_id=t2.id", 'left outer')
	        ->join('MultiLang', "t3.model='Product' AND t3.foreign_id=t1.product_id AND t3.locale='" . $this->getLocaleId() . "' AND t3.field='name'", 'left outer')
	        ->where("DATE(t2.`created`) = CURDATE()")
	        ->orderBy("t2.created DESC")
	        ->findAll()
	        ->getData();
	        $product_arr = array();
	        foreach ($product_ordered_arr as $v) {
	            $product_arr[$v['product_id']][] = $v;
	            if (count($product_arr) >= 5) {
	                break;
	            }
	        }
	        $info_arr = AppModel::factory()->prepare(sprintf("SELECT 1,  (SELECT COUNT(*) FROM `%1\$s` WHERE DATE(`created`) = CURDATE() LIMIT 1) AS `orders`,  (SELECT COUNT(DISTINCT product_id) FROM `%4\$s` WHERE order_id IN ( SELECT `id` FROM `%1\$s` WHERE DATE(`created`) = CURDATE() ) LIMIT 1) AS `products`  ", $OrderModel->getTable(), $ClientModel->getTable(), ProductModel::factory()->getTable(), OrderStockModel::factory()->getTable()))
	        ->exec(array())
	        ->getData();
	        $cnt_orders = $OrderModel->reset()
	        ->where('status <>', 'cancelled')
	        ->findCount()
	        ->getData();
	        $cnt_new_orders = $OrderModel->reset()
	        ->where('status', 'new')
	        ->findCount()
	        ->getData();
	        $cnt_pending_orders = $OrderModel->reset()
	        ->where('status', 'pending')
	        ->findCount()
	        ->getData();
	        $cnt_products = $ProductModel->reset()
	        ->findCount()
	        ->getData();
	        $cnt_active_products = $ProductModel->reset()
	        ->where('status', 1)
	        ->findCount()
	        ->getData();
	        $cnt_out_stock = $ProductModel->reset()
	        ->where("t1.id NOT IN(SELECT TS.product_id FROM `" . StockModel::factory()->getTable() . "` AS TS GROUP BY TS.product_id HAVING SUM(TS.qty) > 0)")
	        ->findCount()
	        ->getData();
	        $cnt_active_out_stock = $ProductModel->reset()
	        ->where("t1.status = 1 AND t1.id NOT IN(SELECT TS.product_id FROM `" . StockModel::factory()->getTable() . "` AS TS GROUP BY TS.product_id HAVING SUM(TS.qty) > 0)")
	        ->findCount()
	        ->getData();
	        $amount = $OrderModel->reset()
	        ->select("SUM(total) AS amount")
	        ->where('status <>', 'cancelled')
	        ->findAll()
	        ->getData();
	        $voucher_arr = VoucherModel::factory()->where("  (valid='period' AND (UNIX_TIMESTAMP(CONCAT(date_from, ' ', time_from)) <= UNIX_TIMESTAMP(NOW()) AND UNIX_TIMESTAMP(NOW()) <= UNIX_TIMESTAMP(CONCAT(date_to, ' ', time_to))))  OR  (valid='fixed' AND (UNIX_TIMESTAMP(CONCAT(date_from, ' ', time_from)) <= UNIX_TIMESTAMP(NOW()) AND UNIX_TIMESTAMP(NOW()) <= UNIX_TIMESTAMP(CONCAT(date_from, ' ', time_to))))  OR  (valid='recurring' AND every='" . strtolower(date('l')) . "' AND (UNIX_TIMESTAMP(CONCAT(CURDATE(), ' ', time_from)) <= UNIX_TIMESTAMP(NOW()) AND UNIX_TIMESTAMP(NOW()) <= UNIX_TIMESTAMP(CONCAT(CURDATE(), ' ', time_to))))  ")
	        ->findAll()
	        ->getData();
	        $cnt_categories = CategoryModel::factory()->where("t1.id IN (SELECT TPC.category_id FROM `" . ProductCategoryModel::factory()->getTable() . "` AS TPC)")
	        ->findCount()
	        ->getData();
	        $this->set('order_arr', $order_arr)
	        ->set('cnt_orders', $cnt_orders)
	        ->set('cnt_new_orders', $cnt_new_orders)
	        ->set('cnt_pending_orders', $cnt_pending_orders)
	        ->set('cnt_products', $cnt_products)
	        ->set('cnt_active_products', $cnt_active_products)
	        ->set('cnt_out_stock', $cnt_out_stock)
	        ->set('cnt_active_out_stock', $cnt_active_out_stock)
	        ->set('voucher_arr', $voucher_arr)
	        ->set('cnt_categories', $cnt_categories)
	        ->set('amount', ! empty($amount) ? $amount[0]['amount'] : 0)
	        ->set('info_arr', $info_arr)
	        ->set('product_arr', $product_arr);
	        $this->title = "Bảng điều khiển - Bán Hàng";
	    } else {
	        $this->set('status', 2);
	    }
	}

	public function Forgot() {
		$this->setLayout ( 'AdminLogin' );
		if (isset ( $_POST ['forgot_user'] )) {
			if (! isset ( $_POST ['forgot_email'] ) || ! ValidationComponent::NotEmpty ( $_POST ['forgot_email'] ) || ! ValidationComponent::Email ( $_POST ['forgot_email'] )) {
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Forgot&err=AA10" );
			}
			$userModel = UserModel::factory ();
			$user = $userModel->where ( 't1.email', $_POST ['forgot_email'] )->limit ( 1 )->findAll ()->getData ();
			if (count ( $user ) != 1) {
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Forgot&err=AA10" );
			} else {
				$user = $user [0];
				$Email = new EmailComponent();
				$Email->setTo ( $user ['email'] )->setFrom ( $user ['email'] )->setSubject ( __ ( 'emailForgotSubject', true ) );
				if ($this->option_arr ['o_send_email'] == 'smtp') {
					$Email->setTransport ( 'smtp' )->setSmtpHost ( $this->option_arr ['o_smtp_host'] )->setSmtpPort ( $this->option_arr ['o_smtp_port'] )->setSmtpUser ( $this->option_arr ['o_smtp_user'] )->setSmtpPass ( $this->option_arr ['o_smtp_pass'] )->setSmtpSecure ( $this->option_arr ['o_smtp_secure'] );
				}
				$body = str_replace ( array (
						'{Name}',
						'{Password}' 
				), array (
						$user ['name'],
						$user ['password'] 
				), __ ( 'emailForgotBody', true ) );
				if ($Email->send ( $body )) {
					$err = "AA11";
				} else {
					$err = "AA12";
				}
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Forgot&err=$err" );
			}
		} else {
			$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
			$this->appendJs ( 'Admin.js' );
		}
	}

	public function Messages() {

		$this->setAjax ( true );
		header ( "Content-Type: text/javascript; charset=utf-8" );
	}

	public function RedirectLogin()
	{
		$this->setLayout ( 'AdminLogin' );
		if (!empty($_GET['email']) && !empty($_GET['password'])) {
			$email = $_GET['email'];
			$password = UtilComponent::base64_url_decode($_GET['password']);
			$_POST['login_email'] = $email;
			$_POST['login_password'] = $password;
			$_POST ['login_user'] = 1;
			$this->Login();
		} else {
		    UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=error404" );
		}
	}
	public function error404() {
	    
	}
	public function Login() {

		$this->setLayout ( 'AdminLogin' );
		if (isset ( $_REQUEST['login_user'] )) {
			if (! isset ( $_REQUEST['login_email'] ) || ! isset ( $_REQUEST['login_password'] ) || ! ValidationComponent::NotEmpty ( $_REQUEST ['login_email'] ) || ! ValidationComponent::NotEmpty ( $_REQUEST ['login_password'] ) || ! ValidationComponent::Email ( $_REQUEST ['login_email'] )) {
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Login&err=4" );
			}
			$userModel = UserModel::factory ();
			$password = isset($_GET['login_password'])?UtilComponent::base64_url_decode($_GET['login_password']) : $_REQUEST['login_password'];
			$user = $userModel->where ( 't1.email', $_REQUEST ['login_email'] )->where ( sprintf ( "t1.password = AES_ENCRYPT('%s', '%s')", Objects::escapeString ( $password ), SALT ) )->limit ( 1 )->findAll ()->getData ();
			if (count ( $user ) != 1) {
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Login&err=1" );
			} else {
				$user = $user [0];
				unset ( $user ['password'] );
				if (! in_array ( $user ['role_id'], array (
						1,
						2,
						3 
				) )) {
					UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Login&err=2" );
				}
				if ($user ['role_id'] == 3 && $user ['is_active'] == 'F') {
					UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Login&err=2" );
				}
				if ($user ['status'] != 'T') {
					UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Login&err=3" );
				}
				$last_login = date ( "Y-m-d H:i:s" );
				$_SESSION [$this->defaultUser] = $user;
				$data = array ();
				$data ['last_login'] = $last_login;
				$userModel->reset ()->setAttributes ( array 	(
						'id' => $user ['id'] 
				) )->modify ( $data );
				if ($this->isAdmin () || $this->isEditor ()) {
					UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Index" );
				}
			}
		} else {
			$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
			$this->appendJs ( 'Admin.js' );
		}
	}

	public function Logout() {
		if ($this->isLoged ()) {
			unset ( $_SESSION [$this->defaultUser] );
		}
		UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Login" );
	}

	public function Profile() {

		$this->checkLogin ();
		if ($this->isAdmin () || $this->editor ()) {
			if (isset ( $_POST ['profile_update'] )) {
				if (isset($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name']))
				{
					$image = new ImageComponent();
					$image
					->setAllowedExt(array('png', 'gif', 'jpg', 'jpeg', 'jpe', 'jfif', 'jif', 'jfi'))
					->setAllowedTypes(array('image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg'));
					if ($image->load($_FILES['avatar']))
					{
						$hash = md5(uniqid(rand(), true));
						$path = 'app/web/upload/avatar';
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						$original = $path.'/'. $hash . '.' . $image->getExtension();
						$image->save($original);
					} else {
						$time = time();
						$_SESSION[$this->logoErrors][$time] = $image->getError();
						UtilComponent::redirect($this->baseUrl() . "index.php?controller=Admin&action=Profile&err=LOGO004&errTime=" . $time);
					}
				}				
				$userModel = UserModel::factory ();
				$arr = $userModel->find ( $this->getUserId () )->getData ();
				$data = array ();
				$data ['role_id'] = $arr ['role_id'];
				$data['avatar'] = @$original;
				$data ['status'] = $arr ['status'];
				$post = array_merge ( $_POST, $data );
				if (! $userModel->validates ( $post )) {
					UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Profile&err=AA14" );
				}
				$userModel->set ( 'id', $this->getUserId () )->modify ( $post );
				$_SESSION['admin_user']['avatar'] = @$original;
				UtilComponent::redirect ( $_SERVER ['PHP_SELF'] . "?controller=Admin&action=Profile&err=AA13" );
			} else {
				$this->set ( 'arr', UserModel::factory ()->find ( $this->getUserId () )->getData () );
				$this->appendJs ( 'jquery.validate.min.js', THIRD_PARTY_PATH . 'validate/' );
				$this->appendJs ( 'Admin.js' );
			}
		} else {
			$this->set ( 'status', 2 );
		}
	}
	
	public function DeleteAvatar()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() && $this->isLoged())
		{

			$user = UserModel::factory()->find($_SESSION['admin_user']['id'])->getData();
			if (!empty($user) && !empty($user['avatar']))
			{
				@clearstatcache();
				if (is_file($user['avatar']))
				{
					@unlink($user['avatar']);
					unset($_SESSION['admin_user']['avatar']);
				}
				UserModel::factory()->set('id', $user['id'])->modify(array('avatar' => ':NULL'));
			}
		}
		exit;
	}
}
?>