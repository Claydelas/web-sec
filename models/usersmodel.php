<?php

use Respect\Validation\Validator as v;

class UsersModel extends GenericModel {

	/** Update the password for a user account */
	public function setPassword($password) {
		$this->password = password_hash($password, PASSWORD_BCRYPT);
	}		
	public function rules(){
		return v::key('username', v::Alnum('_')->NotBlank()->noWhitespace()->length(1,32))
			->key('displayname', v::Alnum()->NotBlank()->length(1,32))
			->key('email', v::email())
			->key('password', v::Alnum(
				'!','"','#','$','%','&','\'','(',')','*','+',
				',','-','.','/',':',';','<','=','>','?','@',
				'[','\\',']','^','_','`','{','|','}','~')->noWhitespace()->NotBlank()->length(8,null))
			->key('level', v::intVal()->NotBlank()->between(0, 2))
			->key('created', v::dateTime('Y-m-d H:i:s')->NotBlank()->between(null, new DateTime('now')))
			->key('bio', v::StringType())
			->key('avatar', v::when(v::notEmpty(),v::regex('/\/uploads\/.*/'),v::StringType()));
	}

}

?>
