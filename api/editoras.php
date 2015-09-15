<?php
   $_PUT = Array();
   
	class Publisher{
		public $id = 0;
		public $name = '';
		
		public function assign(Publisher $editora){
			$this->id = $editora->id;
			$this->name = $editora->name;
		}
		
		public function toArray(){
			$array = Array('id' => $this->id,
					'name' => $this->name);
				
			return $array;
		}
		
		public function toJSON(){
			return json_encode($this->toArray());
		}
	}
	
	class PublisherList{
		private $list;
		
		public function __construct(){
			$this->list = Array();
		}
		
		public function exists($id){
			return array_key_exists($id, $this->list);
		}
		
		public function add(Publisher $editora){
			if (! $this->exists($editora->id)){
				$this->list[$editora->id] = $editora;
			}
			else{
				throw new Exception("Publisher com id '$editora->id' ja existe.");
			} 
		}
		
		public function edit(Publisher $publisher){
			if ($this->exists($publisher->id)){
				$thePublisher = $this->list[$publisher->id];
				$thePublisher->assign($publisher);
			}
			else{
				throw new Exception("Publisher com id '$publisher->id' nao pode ser editado porque nao existe.");
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
				throw new Exception("Publisher '$id' nao existe para excluir.");
			}
		}
		
		public function getAll() {			
			$itens = Array();
			foreach ($this->list as $editora){
				$item = Array('id' => $editora->id, 'name' => $editora->name);
				$itens[] = $item; //Adiciona
			}//for
			return $itens;
		}
		
	}
	
	const EDITORA_LIST = 'EDITORA_LIST';

	class PublisherRest{
		private $method = '';
		
		public function getPublisherList(){
			if (isset($_SESSION[EDITORA_LIST])){
				$editoraList = $_SESSION[EDITORA_LIST];
				return $editoraList;
			}
			else{
				$editoraList = new PublisherList();
				$_SESSION[EDITORA_LIST] = $editoraList;
				return $editoraList;
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
			$editoraList = $this->getPublisherList();
			$editora = $editoraList->get($id);		
		}
		
		public function restPost(){
			$editora = $this->getData();			
			$editoraList = $this->getPublisherList();
			$editoraList->add($editora);
		}
		
		public function restPut(){
			$editora = $this->getData();
			
			$bookList = $this->getPublisherList();
			$bookList->edit($editora);
			$editora = $bookList->get($editora->id);
				
		}
		
		public function restDelete(){
			$id = $this->getVal('id');
			$editoraList = $this->getPublisherList();
			$editoraList->del($id);
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
			$editora = new Publisher();
			$editora->id = $this->getVal('id');
			$editora->name =$this->getVal('name');
			return $editora;
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
	
	session_start();
	//Instancia um livro e executa
	//$bookRest = new BookRest();
	//$bookRest->run();
	
?>