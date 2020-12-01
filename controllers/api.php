<?php

class Api extends Controller {
	
	public function display($f3) {
		extract($f3->get('PARAMS'));
		extract($f3->get('GET'));

		// Check for authentication token and fail without
		// !isset($var) returns false when there is a GET param called token, but is empty,
		// meaning an empty token could bypass this authorisation check
		// $token == NULL is a more comprehensive check, which returns false only when
		// variable token is set and is non-empty
		if($token == NULL || $token != $f3->get('apikey')) {
			// Deny access when DEBUG mode is off (and token matches apikey)
			if(!defined('DEBUG')){
				echo json_encode(array('error' => '403'));
				die();
			}
		}

		//Provide API access
		if(!isset($id)) {
			$results = array();
			$result = $this->Model->$model->fetchAll();
			foreach($result as $r) {
				$results[] = $r->cast();
			}
		} else {
			$result = $this->Model->$model->fetchById($id);
			$results = $result->cast();
		}

		//File not found
		if(empty($results)) { 
			echo json_encode(array('error' => '404')); die();
		}
		
		echo json_encode($results);
		exit();
	}

}

?>
