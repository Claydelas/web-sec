<?php

class Controller {

	protected $layout = 'default';
	protected $template;

	public function __construct() {
		$f3=Base::instance();
		$this->f3 = $f3;

		// Connect to the database
		$this->db = new Database();
		$this->Model = new Model($this);

		//Load helpers
		$helpers = array('Auth');
		foreach($helpers as $helper) {
			$helperclass = $helper . "Helper";
			$this->$helper = new $helperclass($this);
		}
	}

	public function beforeRoute($f3) {
		$this->request = new Request();
		// if not in debug mode and the request is post
		if (!defined('DEBUG') && $f3->VERB=='POST') {
			// retrieve form token
			$token = $f3->get('POST.token');
			// retrieve expected token
			$csrf = $f3->get('SESSION.token');
			// perform token validation and reroute to index if token mismatch
			if (empty($token) || empty($csrf) || $token!==$csrf) {
				StatusMessage::add('CSRF attack detected.','danger');
				$f3->reroute('/');
			}
		}

		//Check user
		$this->Auth->resume();

		//Load settings
		$settings = Settings::getSettings();
		$settings['base'] = $f3->get('BASE');
		
		//Append debug mode to title
		if($settings['debug'] == 1) { $settings['name'] .= ' (Debug Mode)'; }

		$settings['path'] = $f3->get('PATH');
		$this->Settings = $settings;
		$f3->set('site',$settings);

                //Enable backwards compatability
                if($f3->get('PARAMS.*')) { $f3->set('PARAMS.3',$f3->get('PARAMS.*')); }

		//Extract request data
		extract($this->request->data);

		//This looks spooky, eval() on user input? probably shouldn't be in here
		//Also not used anywhere
		//Process before route code
		//if(isset($beforeCode)) {
			//Settings::process($beforeCode);
		//}
	}

	public function afterRoute($f3) {	
		//Set page options
		$f3->set('title',isset($this->title) ? $this->title : get_class($this));

		//Prepare default menu	
		$f3->set('menu',$this->defaultMenu());

		//Setup user
		$f3->set('user',$this->Auth->user());

		//Check for admin
		$admin = false;
		if(stripos($f3->get('PARAMS.0'),'admin') !== false) { $admin = true; }

		//Identify action
		$controller = get_class($this);
		if($f3->exists('PARAMS.action')) {
			$action = $f3->get('PARAMS.action');	
		} else {
			$action = 'index';
		}

		//Handle admin actions
		if ($admin) {
			$controller = str_ireplace("Admin\\","",$controller);
			$action = "admin_$action";
		}

		//Handle errors
		if ($controller == 'Error') {
			$action = $f3->get('ERROR.code');
		}

		//Handle custom view
		if(isset($this->action)) {
			$action = $this->action;
		}

		//Extract request data
		extract($this->request->data);

		//Generate content
		$template = "$controller/$action.htm";
		if(isset($this->template)) {
			if(preg_match('!/!',$this->template)) {
				$template = $this->template . ".htm";
			} else {
				$template = "$controller/" . $this->template . ".htm";
			}
		}
		$content = View::instance()->render($template);
		$f3->set('content',$content);

		//This looks spooky, eval() on user input? probably shouldn't be in here
		//Also not used anywhere
		//Process before route code
		//if(isset($afterCode)) {
			//Settings::process($afterCode);
		//}

		/* Because f3 ESCAPE is true to begin with (from global config), everything  is
		escaped properly while rendering $template and stored in $content.
		However, when layout is rendered, $content is escaped automatically, as it is
		included in the body of every layout. This means that instead of a view with
		individually escaped fields, we end up with a layout with a string as its content.
		Normally, one would use raw() on fields where escaping is unneeded, but it doesn't
		work as expected, because it returns the whole input unescaped, which if used on
		$content, would cancel out any previouslly done escaping inside $content.

		To avoid this, a suitable approach is to temporary disable auto-escaping
		to avoid the entire $content being escaped in the first place.
		The result is when rendering layout, $content is escaped appropriately.*/
		$f3->set('ESCAPE', false);

		//Render template
		echo View::instance()->render($this->layout . '.htm');
	}

	public function defaultMenu() {
		$menu = array(
			array('label' => 'Search', 'link' => 'blog/search'),
			array('label' => 'Contact', 'link' => 'contact'),
		);

		//Load pages
		$pages = $this->Model->Pages->fetchAll();
		foreach($pages as $pagetitle=>$page) {
			$pagename = str_ireplace(".html","",$page);
			$menu[] = array('label' => $pagetitle, 'link' => 'page/display/' . $pagename);
		}

		//Add admin menu items
		if ($this->Auth->user('level') > 1) {
			$menu[] = array('label' => 'Admin', 'link' => 'admin');
		}

		return $menu;
	}

}

?>
