<?php

namespace Admin;

class User extends AdminController {

	public function index($f3) {
		$users = $this->Model->Users->fetchAll();
		$f3->set('users',$users);
	}

	public function export($f3) {
		$users = $this->Model->Users->fetchAll();
		$fp = fopen('export.csv', 'w');
		foreach($users as $user) {			
			$fields = [$user->id,$user->username,$user->displayname,$user->email,$user->password,$user->level,$user->created];
			fputcsv($fp,$fields);
		}
		fclose($fp);
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=file.csv");
		header("Pragma: no-cache");
		header("Expires: 0");

		echo file_get_contents('export.csv');
		exit();
	}

	public function edit($f3) {	
		$id = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetchById($id);
		if($this->request->is('post')) {
			$oldpass = $u->password;
			$u->copyfrom('POST');
			// hash new password if such is provided
			empty($u->password) ? $u->password = $oldpass : $u->setPassword($this->request->data['password']);
			$u->save();
			\StatusMessage::add('User updated successfully','success');
			return $f3->reroute('/admin/user');
		}			
		$_POST = $u->cast();
		$f3->set('u',$u);
	}

	public function delete($f3) {
		$id = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetchById($id);

		if($id == $this->Auth->user('id')) {
			\StatusMessage::add('You cannot remove yourself','danger');
			return $f3->reroute('/admin/user');
		}

		//Remove all posts and comments
		$posts = $this->Model->Posts->fetchAll(array('user_id' => $id));
		foreach($posts as $post) {
			$post_categories = $this->Model->Post_Categories->fetchAll(array('post_id' => $post->id));
			foreach($post_categories as $cat) {
				$cat->erase();
			}
			$post->erase();
		}
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $id));
		foreach($comments as $comment) {
			$comment->erase();
		}
		$u->erase();

		\StatusMessage::add('User has been removed','success');
		return $f3->reroute('/admin/user');
	}


}

?>
