<?php

class Page extends Controller {

	function display($f3) {
		$pagename = urldecode($f3->get('PARAMS.3'));
		$page = $this->Model->Pages->fetch($pagename);
		// XSS sanitising
		$pagetitle = ucfirst(str_replace("_"," ",str_replace(".html","",h($pagename))));
		$f3->set('pagetitle',$pagetitle);
		$f3->set('page',$page);
	}

}

?>
