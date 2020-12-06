<?php

use Respect\Validation\Validator as v;

class PostsModel extends GenericModel {

	public function fetchPublished() {
		return $this->fetchAll(array('published' => 'IS NOT NULL'),array('order' => 'published DESC'));
	}

	public function rules(){
		return v::key('user_id', v::intVal()->notOptional()->noWhitespace())
			->key('created', v::dateTime('Y-m-d H:i:s')->notOptional()->between(null, new DateTime('now')))
			->key('modified', v::dateTime('Y-m-d H:i:s')->notOptional()->between(null, new DateTime('now')))
			->key('published', v::optional(v::dateTime('Y-m-d H:i:s')->between(null, new DateTime('now'))))
			->key('title', v::StringType()->notOptional())
			->key('summary', v::StringType()->notOptional())
			->key('content', v::StringType()->notOptional());
	}

}

?>
