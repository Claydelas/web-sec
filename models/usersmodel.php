<?php

use Respect\Validation\Validator as v;

class UsersModel extends GenericModel {

	/** Update the password for a user account */
	public function setPassword($password) {
		$this->password = password_hash($password, PASSWORD_BCRYPT);
	}		
	public function rules(){
		return v::key('username', v::Alnum('_')->notOptional()->noWhitespace()->length(1,32))
			->key('displayname', v::Alnum()->notOptional()->not(v::startsWith(' '))->length(1,32))
			->key('email', v::email())
			->key('password', v::Alnum(
				'!','"','#','$','%','&','\'','(',')','*','+',
				',','-','.','/',':',';','<','=','>','?','@',
				'[','\\',']','^','_','`','{','|','}','~')->noWhitespace()->notOptional()->length(8,null))
			->key('level', v::intVal()->notOptional()->between(0, 2))
			->key('created', v::dateTime('Y-m-d H:i:s')->notOptional()->between(null, new DateTime('now')))
			->key('bio', v::optional(v::StringType()))
			->key('avatar', v::when(v::notOptional(),v::regex('/\/uploads\/.+/'),v::StringType()));
	}

}

?>
