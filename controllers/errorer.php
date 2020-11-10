<?php

class Errorer extends Controller {

	public function __construct() {
		parent::__construct();
	}

	public function errorer($f3) {
		$this->template = 500;	
	}	

}

?>
