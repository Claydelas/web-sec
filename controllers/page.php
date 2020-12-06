<?php

class Page extends Controller {

	function display($f3) {
		$pagename = urldecode($f3->get('PARAMS.3'));
		$page = $this->Model->Pages->fetch($pagename);
		//if page doesn't exist return instead of displaying blank page
		if(!$page) return $f3->error(404);
		$pagetitle = ucfirst(str_replace("_"," ",str_replace(".html","",$pagename)));
		$f3->set('pagetitle',$pagetitle);
		$f3->set('page',$page);
	}

}

?>
