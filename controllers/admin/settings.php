<?php

namespace Admin;

use Base;

class Settings extends AdminController {

	public function index($f3) {
		$settings = $this->Model->Settings->fetchAll();
		if($this->request->is('post')) {
			foreach($settings as $setting) {
				if(isset($this->request->data[$setting->setting])) {
					$setting->value = $this->request->data[$setting->setting];
					if(!$setting->check()) return $f3->reroute('/admin/settings');
					$setting->save();
				} else {
					$setting->value = 0;
					$setting->save();
				}
			}
			\StatusMessage::add('Settings updated','success');
		}
		$f3->set('settings',$settings);
	}

	public function clearcache($f3) {
		if(!defined('DEBUG')) return Base::instance()->reroute('/admin/settings'); 
		$cache = isset($this->request->data['cache']) ? getcwd() . '/' . $this->request->data['cache'] : getcwd() . '/tmp/cache';
		$cache = str_replace(".","",$cache);
		$this->delTree($cache);
	}

	public function delTree($dir) {
		if(!defined('DEBUG')) return Base::instance()->reroute('/admin/settings'); 
		$files = array_diff(scandir($dir), array('.','..')); 
		foreach ($files as $file) {
			(is_dir("$dir/$file") && !is_link($dir)) ? $this->delTree("$dir/$file") : unlink("$dir/$file"); 
		}
		return rmdir($dir); 
	} 

}


?>
