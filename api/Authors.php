<?php
   $_PUT = Array();
   
	class Author{
		public $id = 0;
		public $name = '';
		
		public function assign(Author $author){
			$this->id = $author->id;
			$this->name = $author->name;
		}
		
		public function toArray(){
			$array = Array('id' => $this->id,
					'title' => $this->name,);
				
			return $array;
		}
		
		public function toJSON(){
			return json_encode($this->toArray());
		}
	}
	
	class AuthorList{
		private $list;
		
		public function __construct(){
			$this->list = Array();
		}
		
		public function exists($id){
			return array_key_exists($id, $this->list);
		}
		
		public function add(Author $author){
			if (! $this->exists($author->id)){
				$this->list[$author->id] = $author;
			}
			else{
				throw new Exception("Livro com id '$author->id' ja existe.");
			} 
		}
		
		public function edit(Author $author){
			if ($this->exists($author->id)){
				$theBook = $this->list[$author->id];
				$theBook->assign($author);
			}
			else{
				throw new Exception("Autor com id '$author->id' nao pode ser editado porque nao existe.");
			} 
		}
		
		public function get($id){
			if (array_key_exists($id, $this->list)){
				return $this->list[$id];
			} else {
				return null;
			}
		}
		
		public function del($id){
			if (array_key_exists($id, $this->list)){
				unset($this->list[$id]);
			}
			else{
				throw new Exception("Author '$id' nao existe para excluir.");
			}
		}
		
		public function getAll() {			
			$itens = Array();
			foreach ($this->list as $author){
				$item = Array('id' => $author->id, 'name' => $author->name);
				$itens[] = $item; //Adiciona
			}//for
			return $itens;
		}
		
	}
	
	const AUTHORS_LIST = 'AUTHORS_LIST';

	class AuthorRest{
		private $method = '';
		
		public function getAuthorList(){
			if (isset($_SESSION[AUTHORS_LIST])){
				$authorList = $_SESSION[AUTHORS_LIST];
				return $authorList;
			}
			else{
				$authorList = new AuthorList();
				$_SESSION[AUTHORS_LIST] = $authorList;
				return $authorList;
			}
		}
		
		
		public function __construct(){
			$this->method = $this->getVal('kind');			
		}
		
		public function run(){
			switch ($this->method){
				case 'GET':	$this->restGet(); break;
				case 'POST': $this->restPost(); break;
				case 'PUT': $this->restPut(); break;
				case 'DELETE': $this->restDelete(); break;
				default: $this->restError(); break;
			}//switch
		}
		
		
		public function restGet(){
			$id = $this->getVal('id');
			$authorList = $this->getAuthorList();
			$author = $authorList->get($id);		
		}
		
		public function restPost(){
			$author = $this->getData();			
			$authorList = $this->getAuthorList();
			$authorList->add($author);
		}
		
		public function restPut(){
			$author = $this->getData();
			
			$authorList = $this->getAuthorList();
			$authorList->edit($author);
			$author = $authorList->get($author->id);
				
		}
		
		public function restDelete(){
			$id = $this->getVal('id');
			$authorList = $this->getAuthorList();
			$authorList->del($id);
		}
				
		private function sendError(Exception $error){
			$result['status'] = 'ERROR';
			$result['message'] = $error->getMessage();
			$json = json_encode($result);
			$this->sendJson($json);
		}
		
		public function restError(){
			try{
				throw new Exception("Operaчуo invсlida '$this->method'.");	
			}	
			catch(Excption $e){
				$this->sendError($e);
			}
		}
		
		public function getData(){
			$author = new Author();
			$author->id = $this->getVal('id');
			$author->name =$this->getVal('name');
			return $author;
		}
		
		private function sendJson($json){
			header('Content-type: application/json; charset=UTF-8');
			echo $json;
		}
		
		private function getVal($key){
			if (isset($_GET[$key])){
				return trim(strip_tags($_GET[$key]));
			}
			else{
				if (isset($_POST[$key])){
					return trim(strip_tags($_POST[$key]));
				}
				else
					return null;
			}//else
		}
		
	}
	
	
	//********************************************************************
	
	
	
	//session_start();
	//Instancia um livro e executa
	//$bookRest = new BookRest();
	//$bookRest->run();
	
?>