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
				$user = unserialize(base64_decode($f3->get('COOKIE.RobPress_User')));
				$this->forceLogin($user);
			}
		}		

		/** Perform any checks before starting login */
		public function checkLogin($username,$password,$request,$debug) {

			//DO NOT check login when in debug mode
			if($debug == 1) { return true; }

			return true;	
		}

		/** Look up user by username and password and log them in */
		public function login($username,$password) {
			// uses SQL mapped object instead of direct db access to fetch a single user with matching u:p
			$user = $this->controller->Model->Users->fetch(['username' => $username, 'password' => $password]);
			if($user){
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

			//Kill the session
			session_destroy();

			//Kill the cookie
			setcookie('RobPress_User','',time()-3600,'/');
		}

		/** Set up the session for the current user */
		public function setupSession($user) {

			//Remove previous session
			session_destroy();

			// Setup new session
			// md5 hashing can be reversed, so sessions use bcrypt + salt
			session_id(password_hash($user['id'],PASSWORD_DEFAULT));

			//Setup cookie for storing user details and for relogging in
			// TODO replace base64 encoding
			// +fix expiry time, +httponly cookies, +samesite Strict
			// secure cookies (https) don't work on local machine so false
			setcookie('RobPress_User', base64_encode(serialize($user)), [
				'expires' => time() + 86400,
				'path' => '/',
				'domain' => "",
				'secure' => false,
				'httponly' => true,
				'samesite' => 'Strict',
			]);
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
