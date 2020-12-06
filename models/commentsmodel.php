<?php

use Respect\Validation\Validator as v;

class CommentsModel extends GenericModel {

    public function rules(){
		return v::key('user_id', v::intVal()->notOptional()->noWhitespace())
			->key('blog_id', v::intVal()->notOptional()->noWhitespace())
			->key('subject', v::StringType()->notOptional())
			->key('message', v::StringType()->notOptional())
			->key('created', v::dateTime('Y-m-d H:i:s')->notOptional()->between(null, new DateTime('now')))
			->key('moderated', v::intVal()->notOptional()->between(0, 1));
	}

}

?>

