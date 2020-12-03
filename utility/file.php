<?php

class File {

	public static function Upload($array,$local=false) {
		$f3 = Base::instance();
		extract($array);

		//Set default file extension whitelist
		$whitelist_ext = array('jpeg','jpg','png','gif');
		//Set default file type whitelist
		$whitelist_type = array('image/jpeg', 'image/jpg', 'image/png','image/gif');

		//Exit early if there is some error
		if($error != 0) {
			return false;
		}

		//Extract file info
		$info = pathinfo($name);
		//Retrieve extension
		$ext = $info['extension'];
		//Check file has the right extension           
		if (!in_array($ext, $whitelist_ext)) {
			return false;
		}
		//Retrieve mime
		$mime = mime_content_type($tmp_name);
		//Check that the file is of the right type
		if (!in_array($type, $whitelist_type) || !in_array($mime, $whitelist_type)) {
			return false;
  		}
		//Rename upload		
		$name = hash_file('sha256', $tmp_name) . '.' . $ext;

		$directory = getcwd() . '/uploads';
		$destination = $directory . '/' . $name;
		$webdest = '/uploads/' . $name;

		//Local files get moved
		if($local) {
			if (copy($tmp_name,$destination)) {
				chmod($destination,0666);
				return $webdest;
			} else {
				return false;
			}
		//POSTed files are done with move_uploaded_file
		} else {
			if (move_uploaded_file($tmp_name,$destination)) {
				chmod($destination,0666);
				return $webdest;
			} else {
				return false;
			}
		}
	}
	private static function validate($mime, $size){
		return null;
	}

}

?>
