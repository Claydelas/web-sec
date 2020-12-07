<?php

use Respect\Validation\Validator as v;

class SettingsModel extends GenericModel {

	public function getSetting($key) {
		$setting = $this->fetch(array('setting' => $key));
		return $setting->value;
	}

	public function setSetting($key,$value) {
		$setting = $this->fetch(array('setting' => $key));
		$setting->value = $value;
		return $setting->save();			  
	}

	public function rules(){
		switch ($this->setting) {
			case "name":
				return v::key('value', v::StringType()->notOptional());
			case "front_title":
				return v::key('value', v::StringType()->notOptional());
			case "comments":
				return v::key('value', v::intVal()->notOptional()->between(0, 1));
			case "moderate":
				return v::key('value', v::intVal()->notOptional()->between(0, 1));
			case "subtitle":
				return v::key('value', v::StringType()->notOptional());
			case "email":
				return v::key('value', v::oneOf(v::email(),v::regex('/.+@.+/')));
			case "debug":
				return v::key('value', v::intVal()->notOptional()->between(0, 1));
			default:
				return v::key('value', v::StringType());
		}
	}

}

?>
