<?php

	class AuthHelper {

		/** Construct a new Auth helper */
		public function __construct($controller) {
			$this->controller = $controller;
		}

		/** Attempt to resume a previously logged in session if one exists */
		public function resume() {
			$f3=Base::instance();				

			//Ignore if already running session	
			if($f3->exists('SESSION.user.id')) return;

			//Log user back in from cookie
			if($f3->exists('COOKIE.RobPress_User')) {
				//split cookie into selector and validator
				list($selector, $validator) = explode(':', $f3->get('COOKIE.RobPress_User'));
				//fetch info from last login
				$auth = $this->controller->Model->Auth->fetch(['selector' => $selector]);
				//if cookie identifies a user log them in
				if($auth && hash_equals($auth->validator, hash('sha256', base64_decode($validator)))){
					//prevents users from authenticating with expired(modified) cookie
					if(new DateTime() > new DateTime($auth->expires)) return;
					$user = $this->controller->Model->Users->fetch(['id' => $auth->user_id]);
					$this->forceLogin($user);
				}
			}
		}		

		/** Perform any checks before starting login */
		public function checkLogin($username,$password,$request,$debug) {

			//DO NOT check login when in debug mode
			if($debug == 1) { return true; }

			if($request->is('post')){
				$user = $this->controller->Model->Users->fetch(['username' => $username]);

				$validinfo = $user && password_verify($password,$user->password);
				$invalidcaptcha = array_key_exists('captcha',$request->data)
									&& Base::instance()->get('SESSION.captcha') != $request->data['captcha'];

				if(!$validinfo || $invalidcaptcha){
					$model = $this->controller->Model->Login_Attempts->fetch(['ip' => $_SERVER['REMOTE_ADDR']]);
					if(!$model) $model = $this->controller->Model->Login_Attempts;
					$model->ip = $_SERVER['REMOTE_ADDR'];
					$model->attempts = $model->attempts + 1;
					$model->last_attempt = mydate();
					$model->save();
					return false;
				}
			}
			return true;	
		}

		/** Look up user by username and password and log them in */
		public function login($username,$password) {
			// uses SQL mapped object instead of direct db access to fetch a single user with matching u:p
			$user = $this->controller->Model->Users->fetch(['username' => $username]);
			if($user && password_verify($password,$user->password)){
				// cast to array
				$user = $user->cast();
				$this->setupSession($user);
				return $this->forceLogin($user);
			}
			return false;
		}

		/** Log user out of system */
		public function logout() {
			$f3=Base::instance();
			//Stop logout process on CSRF attempt
			//Logout uses GET request despite it changing state, because site locations with
			//other existing POST forms will override the token for logout
			//Furthermore, there wasn't a good way to implement a form in the navbar menu
			//while maintaining the current look and feel
			//TODO: change to post request
			if(!defined('DEBUG') && ($f3->get('SESSION.logout_token') != $f3->get('GET.token'))){
				StatusMessage::add('CSRF attack detected.','danger');
				return $f3->reroute('/');
			}
			//Kill the session
			session_destroy();

			//Kill the cookie
			setcookie('RobPress_User','',time()-3600,'/');
		}

		/** Set up the session for the current user */
		public function setupSession($user) {

			$f3=Base::instance();	

			//Remove previous session
			session_destroy();

			// Setup new session
			// Session ID automatically generated by php, no need to hash user info
			// session_id(password_hash($user['id'],PASSWORD_DEFAULT));

			// Setup cookie for storing user details and for relogging in
			// replaced previous base64 encoding of the entire user object
			// +fix expiry time, +httponly cookies, +samesite Strict
			// secure cookies (https) don't work on local machine so false for now
			$selector = base64_encode(random_bytes(9));
			$validator = bin2hex(random_bytes(20));
			$expires = new DateTime('+1 days');
			
			$cookie = $selector.':'.base64_encode($validator);
			setcookie('RobPress_User', $cookie, [
				'expires' => $expires->format('U'),
				'path' => '/',
				'domain' => "",
				'secure' => true,
				'httponly' => true,
				'samesite' => 'Strict',
			]);

			$auth = $this->controller->Model->Auth->fetch(["user_id" => $user['id']]);
			if(!$auth) $auth = $this->controller->Model->Auth;
			$auth->selector = $selector;
			$auth->validator = hash('sha256', $validator);
			$auth->user_id = $user['id'];
			$auth->expires = $expires->format('Y-m-d H:i:s');
			$auth->save();

			//And begin!
			new Session();
		}

		/** Not used anywhere in the code, for debugging only */
		public function specialLogin($username) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3 = Base::instance();
			$user = $this->controller->Model->Users->fetch(array('username' => $username));
			$array = $user->cast();
			return $this->forceLogin($array);
		}

		/** Not used anywhere in the code, for debugging only */
		public function debugLogin($username,$password='admin',$admin=true) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$user = $this->controller->Model->Users->fetch(array('username' => $username));

			//Create a new user if the user does not exist
			if(!$user) {
				$user = $this->controller->Model->Users;
				$user->username = $user->displayname = $username;
				$user->email = "$username@robpress.org";
				$user->setPassword($password);
				$user->created = mydate();
				$user->bio = '';
				if($admin) {
					$user->level = 2;
				} else {
					$user->level = 1;
				}
				$user->save();
			}

			//Update user password
			$user->setPassword($password);

			//Move user up to administrator
			if($admin && $user->level < 2) {
				$user->level = 2;
				$user->save();
			}

			//Log in as new user
			return $this->forceLogin($user);			
		}

		/** Force a user to log in and set up their details */
		public function forceLogin($user) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3=Base::instance();					

			if(is_object($user)) { $user = $user->cast(); }

			$f3->set('SESSION.user',$user);
			return $user;
		}

		/** Get information about the current user */
		public function user($element=null) {
			$f3=Base::instance();
			if(!$f3->exists('SESSION.user')) { return false; }
			if(empty($element)) { return $f3->get('SESSION.user'); }
			else { return $f3->get('SESSION.user.'.$element); }
		}

	}

?>
