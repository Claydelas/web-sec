<?php

use Respect\Validation\Validator as v;

class Post_CategoriesModel extends GenericModel {

	public function rules(){
		return v::key('user_id', v::intVal()->notOptional()->noWhitespace())
			->key('category_id', v::intVal()->notOptional()->noWhitespace());
	}

}

?>
