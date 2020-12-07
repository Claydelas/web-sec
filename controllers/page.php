<?php

class Page extends Controller {

	function display($f3) {
		$pagename = urldecode($f3->get('PARAMS.3'));
		// return early if page doesn't exist
		if(!array_key_exists(strtolower($pagename), array_change_key_case($this->Model->Pages->fetchAll())))
			return $f3->error(404);
		$page = $this->Model->Pages->fetch($pagename);
		$pagetitle = ucfirst(str_replace("_"," ",str_replace(".html","",$pagename)));
		$f3->set('pagetitle',$pagetitle);
		$f3->set('page',$page);
	}

}

?>
