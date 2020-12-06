<?php
class User extends Controller {

	public function view($f3) {
		$userid = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetchById($userid);
		if(!$u) return $f3->reroute('/');

		$articles = $this->Model->Posts->fetchAll(array('user_id' => $userid, 'published' => 'IS NOT NULL'));
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $userid, 'moderated' => 1));

		$f3->set('u',$u);
		$f3->set('articles',$articles);
		$f3->set('comments',$comments);
	}

	public function add($f3) {
		if($this->request->is('post')) {
			$data = $this->request->data;
			$check = $this->Model->Users->fetch(['username' => $data['username']]);
			if (!empty($check)) {
				StatusMessage::add('User already exists','danger');
				return;
			}
			if($this->Model->Users->fetch(['displayname' => $data['displayname']])){
				StatusMessage::add('Display name already exists','danger');
				return;
			}
			if($data['password'] != $data['password2']) {
				StatusMessage::add('Passwords must match','danger');
				return;
			}
			
			$user = $this->Model->Users;
			$user->username = $data['username'];
			if(empty($data['displayname']) && !$this->Model->Users->fetch(['displayname' => $data['username']])) {
				$user->displayname = $data['username'];
			} else {
				$user->displayname = $data['displayname'];
			}
			$user->email = $data['email'];
			$user->setPassword($data['password']);
			$user->level = 1;
			$user->created = mydate();
			$user->bio = '';
			$user->avatar = '';

			$user->save();	
			StatusMessage::add('Registration complete','success');
			return $f3->reroute('/user/login');
		}
	}
	

	public function login($f3) {
		/** YOU MAY NOT CHANGE THIS FUNCTION - Make any changes in Auth->checkLogin, Auth->login and afterLogin() (AuthHelper.php) */
		if ($this->request->is('post')) {

			//Check for debug mode
			$settings = $this->Model->Settings;
			$debug = $settings->getSetting('debug');

			//Either allow log in with checked and approved login, or debug mode login
			list($username,$password) = array($this->request->data['username'],$this->request->data['password']);
			if (
				($this->Auth->checkLogin($username,$password,$this->request,$debug) && ($this->Auth->login($username,$password))) ||
				($debug && $this->Auth->debugLogin($username))) {

					$this->afterLogin($f3);

			} else {
				StatusMessage::add('Invalid username or password','danger');
			}
		}		
	}

	/* Handle after logging in */
	private function afterLogin($f3) {
				StatusMessage::add('Logged in successfully','success');

				//Redirect to where they came from
				if(isset($_GET['from']) && $_GET['from'] != '/user/login') {
					$f3->reroute($_GET['from']);
				} else {
					$f3->reroute('/');	
				}
	}

	public function logout($f3) {
		$this->Auth->logout();
		StatusMessage::add('Logged out successfully','success');
		$f3->reroute('/');	
	}


	public function profile($f3) {	
		$id = $this->Auth->user('id');
		$u = $this->Model->Users->fetchById($id);
		if($this->request->is('post')) {
			$post = $this->request->data;
			//dangerous
			//$u->copyfrom('POST');
			$u->displayname = $post['displayname'];
			// hash new password if such is provided
			if(!empty($post['password'])) $u->setPassword($post['password']);
			$u->bio = \HTMLPurifier::instance()->purify($post['bio']);

			//Handle avatar upload
			if(isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])) {
				$url = File::Upload($_FILES['avatar']);
				if(!$url) {
					\StatusMessage::add('Upload failed, profile not updated.','danger');
					return $f3->reroute('/user/profile');
				} else {
					$u->avatar = $url;
				}
			} else if(isset($reset)) {
				$u->avatar = '';
			}

			$u->save();
			\StatusMessage::add('Profile updated successfully','success');
			return $f3->reroute('/user/profile');
		}			
		$_POST = $u->cast();
		$f3->set('u',$u);
	}

	public function promote($f3) {
		// only usable in DEBUG mode, alternatively remove this code block as it is not used
		if(!defined('DEBUG')) return $f3->reroute('/');
		$id = $this->Auth->user('id');
		$u = $this->Model->Users->fetchById($id);
		$u->level = 2;
		$u->save();
		return $f3->reroute('/');
	}

}
?>
