<?php

class Contact extends Controller {

	public function index($f3) {
		if($this->request->is('post')) {
			$data = $this->request->data;
			$email = $this->Auth->user('email');
			$from = 'From: '.($email ? $email : 'Unregistered ('.$data['from'].')');
			$to = $f3->get('site.email');
			mail($to,$data['subject'],$data['message'],$from);

			StatusMessage::add('Thank you for contacting us');
			return $f3->reroute('/');
		}	
	}

}

?>
