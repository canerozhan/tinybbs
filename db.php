<?php
class Database{
	private static $instance;
	private $handle;
	private function __construct(){
		$this->handle=new PDO('mysql:host=localhost;port=3306;dbname=tinybbs','root','');
		$this->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	public static function getInstance(){
		if (is_null(self::$instance))
			self::$instance=new self();
		return self::$instance;
	}
	public function getHandle(){return $this->handle;}
	public function fetchPosts($parent=0){
		if(!is_numeric($parent) || is_float($parent) || $parent < 0)
			throw new Exception("Can't assign parent to non-numeric value", '100');
		$query=$this->handle->prepare("SELECT * FROM posts WHERE parent_id=$parent ORDER BY date ".($parent==0?'DESC':'ASC'));
		$query->execute();
		$posts=Array();
		while($obj=$query->fetchObject())
			array_push($posts,new Post($obj->message,$obj->parent_id,$obj->id,$obj->date));
		return $posts;
	}
	public function addPost(Post $post){
		$query=$this->handle->prepare('INSERT INTO posts (parent_id,message) VALUES (?,?);');
		$query->bindParam(1,$post->getParent(),PDO::PARAM_INT);
		$query->bindParam(2,$post->getMessage(),PDO::PARAM_STR);
		$query->execute();
		return $this->handle->lastInsertId();
	}
}