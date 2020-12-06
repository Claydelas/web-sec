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
		if(!$u) return $f3->reroute('/admin/user');
		if($this->request->is('post')) {
			$post = $this->request->data;
			//dangerous
			//$u->copyfrom('POST');
			$u->username = $post['username'];
			$u->displayname = $post['displayname'];
			// hash new password if such is provided
			if(!empty($post['password'])) $u->setPassword($post['password']);
			$u->level = $post['level'];
			$u->bio = \HTMLPurifier::instance()->purify($post['bio']);
			$u->avatar = $post['avatar'];
			
			$u->save();
			\StatusMessage::add('User updated successfully','success');
			return $f3->reroute('/admin/user');
		}
		$_POST = $u->cast();			
		$f3->set('u',$u);
	}

	public function delete($f3) {
		if($this->request->is('post')) {
			$id = $f3->get('PARAMS.3');
			$u = $this->Model->Users->fetchById($id);
			if(!$u) return $f3->reroute('/admin/user');

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
		$f3->reroute('/admin/user');
	}


}

?>
