<?php

use Respect\Validation\Validator as v;

class CategoriesModel extends GenericModel {

	public function rules(){
		return v::key('title', v::StringType()->notOptional()->not(v::startsWith(' ')));
	}

}

?>
