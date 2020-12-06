<?php

	namespace Admin;

	class Blog extends AdminController {

		public function index($f3) {
			$posts = $this->Model->Posts->fetchAll();
			$blogs = $this->Model->map($posts,'user_id','Users');
			$blogs = $this->Model->map($posts,array('post_id','Post_Categories','category_id'),'Categories',false,$blogs);
			$f3->set('blogs',$blogs);
		}

		public function delete($f3) {
			if($this->request->is('post')) {
				$postid = $f3->get('PARAMS.3');
				$post = $this->Model->Posts->fetchById($postid);
				if(!$post) return $f3->reroute('/admin/blog');
				$post->erase();

				//Remove from categories
				$cats = $this->Model->Post_Categories->fetchAll(array('post_id' => $postid));
				foreach($cats as $cat) {
					$cat->erase();
				}	
				
				\StatusMessage::add('Post deleted successfully','success');
				return $f3->reroute('/admin/blog');
			}
			$f3->reroute('/admin/blog');
		}

		public function add($f3) {
			if($this->request->is('post')) {
				$post = $this->Model->Posts;
				$data = $this->request->data;

				$post->title = $data['title'];
				$post->summary = $data['summary'];
				$post->content = \HTMLPurifier::instance()->purify($data['content']);
				$post->created = mydate();
				$post->modified = $post->created;
				$post->user_id = $this->Auth->user('id');
				
				//Determine whether to publish or draft
				if(!isset($data['Publish'])) {
					$post->published = null;
				} else {
					$post->published = mydate($data['published']);
				} 
				
				if(!$post->check()) return $f3->reroute('/admin/blog/add');

				//Save post
				$post->save();
				$postid = $post->id;
				//Now assign categories
				$link = $this->Model->Post_Categories;
				if(!isset($data['categories'])) { $data['categories'] = array(); }
				foreach($data['categories'] as $category) {
					$link->reset();
					$link->category_id = $category;
					$link->post_id = $postid;
					$link->save();
				}
				\StatusMessage::add('Post added successfully','success');
				return $f3->reroute('/admin/blog');
			}
			$categories = $this->Model->Categories->fetchList();
			$f3->set('categories',$categories);
		}

		public function edit($f3) {
			$postid = $f3->get('PARAMS.3');
			$post = $this->Model->Posts->fetchById($postid);
			if(!$post) return $f3->reroute('/admin/blog');
			$blog = $this->Model->map($post,array('post_id','Post_Categories','category_id'),'Categories',false);
			if ($this->request->is('post')) {
				$data = $this->request->data;
				//dangerous
				//$post->copyfrom('POST');
				$post->title = $data['title'];
				$post->summary = $data['summary'];
				$post->content = \HTMLPurifier::instance()->purify($data['content']);
				$post->modified = mydate();
				$post->user_id = $this->Auth->user('id');
				
				//Determine whether to publish or draft
				if(!isset($data['Publish'])) {
					$post->published = null;
				} else {
					$post->published = mydate($data['published']);
				} 

				if(!$post->check()) return $f3->reroute("/admin/blog/edit/$postid");

				//Save changes
				$post->save();

				$link = $this->Model->Post_Categories;
				//Remove previous categories
				$old = $link->fetchAll(array('post_id' => $postid));
				foreach($old as $oldcategory) {
					$oldcategory->erase();
				}
				
				//Now assign new categories				
				if(!isset($data['categories'])) { $data['categories'] = array(); }
				foreach($data['categories'] as $category) {
					$link->reset();
					$link->category_id = $category;
					$link->post_id = $postid;
					$link->save();
				}

				\StatusMessage::add('Post updated successfully','success');
				return $f3->reroute('/admin/blog');
			} 
			$_POST = $post->cast();		
			foreach($blog['Categories'] as $cat) {
				if(!$cat) continue;
				$_POST['categories'][] = $cat->id;
			}
	
			$categories = $this->Model->Categories->fetchList();
			$f3->set('categories',$categories);
			$f3->set('post',$post);
		}


	}

?>
