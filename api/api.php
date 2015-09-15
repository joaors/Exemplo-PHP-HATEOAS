<?php
include 'Authors.php';
include 'books.php';
include 'editoras.php';

define('API_URI', 'http://localhost:8078/exampleHATEOAS/api/api.php');

//Objetos
const cObject = 'object';
const cAutor = 'Autor';
const cEditora = 'Editora';
const cLivro = 'Livro';

//Métodos
const cURI = 'uri';
const cMethod = 'method';
const cGET = 'GET';
const cPOST = 'POST';

//Operações
const cOperations = 'operations';
const cOperation = 'operation';
const cNew = 'new';
const cUpdate = 'update';
const cDelete = 'delete';
const cSelect = 'select';
const cSelectAll = 'selectAll';  


//Template
const cType = 'type';
const cTemplate = 'template';
const cFields = 'fields';
const cField_Name = 'name';
const cField_Kind = 'kind';
const cField_Description = 'description';
const cField_Required = 'required';
const cField_Value = 'value';
const cTrue = true;
const cFalse = false;
const cHeaders= 'headers';

//Tipos de campos
const cString = 'string';
const cInteger = 'integer';
const cDouble = 'double';

//Result
const cResult = 'result';
const cResult_OK = 'OK';
const cResult_Error = 'ERROR';
const cResult_Message = 'message';

// ************** Funções genéricas ******************
function sendJson($json){
	header('Content-type: application/json; charset=UTF-8');
	echo $json;
}

function getVal($key){
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
//**************************************************

abstract class ApiObject{
	
	abstract public function run();
	abstract public function getEntryPoints();
	
} 
//**********************************************************

class Autor extends ApiObject{

	public function getEntryPoints(){
		$operations = Array(cNew, cUpdate, cDelete, cSelect, cSelectAll);
		$data = Array(cObject => cAutor);

		$itens = Array();
		foreach ($operations as $op){
			$item = Array(cURI => API_URI . '?' . cObject . '=' . cAutor . '&' . cOperation . '=' . $op . '&' . cType . '=' . cTemplate,
					cOperation => $op, cType => cTemplate, cMethod => cGET);
			$itens[] = $item; //Adiciona
		}//for
		$data[cOperations] = $itens;

		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doNewTemplate($operation){
		$data = Array(cObject => cAutor,
				cFields => Array(
						Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => ''),
						Array(cField_Name => 'name', cField_Description => 'Nome', cField_Kind => cString, cField_Required => cTrue, cField_Value => '')
				),
				cURI => API_URI . '?' . cObject . '=' . cAutor . '&' . cOperation . '=' . $operation,
				cMethod => cPOST
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doDeleteTemplate(){
		$data = Array(cObject => cAutor,
				cFields => Array(
						Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => ''),
				),
				cURI => API_URI . '?' . cObject . '=' . cAutor . '&' . cOperation . '=' . cDelete,
				cMethod => cPOST
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doConsultaRegistroTemplate(){
		$data = Array(cObject => cAutor,
				cFields => Array(
						Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => ''),
				),
				cURI => API_URI . '?' . cObject . '=' . cAutor . '&' . cOperation . '=' . cSelect,
				cMethod => cPOST
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doSelectAllTemplate() {
		$data = Array(cObject => cAutor, cURI => API_URI . '?' . cObject . '=' . cAutor . '&' . cOperation . '=' . cSelectAll, cMethod => cGET);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doSelect(){
		$id = getVal('id');
		$errors = Array();
		try {
			if ($id == '')
				throw new Exception('Id deve ser informado.');

			$autorRest = new AuthorRest();
			$autor = $autorRest->getAuthorList()->get($id);

			if ($autor == null) {
				throw new Exception('Autor nao encontrado.');
			}

			$data = Array(cObject => cAutor,
					cFields => Array(
							Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => $autor->id),
							Array(cField_Name => 'name', cField_Description => 'Nome', cField_Kind => cString, cField_Required => cTrue, cField_Value => $autor->name)
					)
			);
		} catch (Exception $e) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e->getMessage());
		}
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doSelectAll(){
		$autorRest = new AuthorRest();
		$data = Array(cObject => cAutor,
				cHeaders => Array(
						Array( cField_Name => 'Identificador'),
						Array(cField_Name => 'Nome')
				),
				cFields => $autorRest->getAuthorList()->getAll()
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doDelete(){
		$id = getVal('id');
		try {
			if ($id == '') {
				throw new Exception("Id deve ser informado.");
			}
			$autorRest = new AuthorRest();
			$autorRest->restDelete();
			$data = Array(cResult => cResult_OK, cResult_Message => 'Autor ' . $id . ' Excluido com sucesso');
		} catch (Exception $e) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e->getMessage());
		}
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doNew(){
		$id = getVal('id');
		$name = getVal('name');

		try {
			if ($id == '')
				throw new Exception('Id deve ser informado.');

			if ($name == '')
				throw new Exception('Nome deve ser informado.');


			$autorRest = new AuthorRest();
			$autorRest->restPost();
			$data = Array(cResult => cResult_OK, cResult_Message => 'Autor ' . $name . ' cadastrado com sucesso!');
		} catch (Exception $e1) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e1->getMessage());
		}
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doUpdate(){
		$id = getVal('id');
		$name = getVal('name');

		try {
			if ($id == '')
				throw new Exception('Id deve ser informado.');

			if ($name == '')
				throw new Exception('Nome deve ser informado.');

			$autorRest = new AuthorRest();
			$autorRest->restPut();
			$data = Array(cResult => cResult_OK, cResult_Message => 'Autor ' . $name . ' Alterado com sucesso!');
		} catch (Exception $e1) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e1->getMessage());
		}
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doNewError(){
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Editora&operation=new"
		$data = Array(cResult => cResult_Error, cResult_Message => 'O nome do Autor deve ser informado.');
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	public function run(){
		$operation = getVal(cOperation);
		$type = getVal(cType);
		switch($operation){
			case cNew:
				if ($type == cTemplate){
					$this->doNewTemplate(cNew);
				}
				else{
					$this->doNew();
				}
				break;
			case cDelete:
				if ($type == cTemplate) {
					$this->doDeleteTemplate();
				} else {
					$this->doDelete();
				}
				break;
			case cSelect:
				if ($type == cTemplate) {
					$this->doConsultaRegistroTemplate();
				} else {
					$this->doSelect();
				}
				break;
			case cSelectAll:
				if ($type == cTemplate) {
					$this->doSelectAllTemplate();
				} else {
					$this->doSelectAll();
				}
				break;
			case cUpdate:
				if ($type == cTemplate) {
					$this->doNewTemplate(cUpdate);
				} else {
					$this->doUpdate();
				}
				break;
			case cNew . '_error':
				$this->doNewError();
				break;

			default:
				$this->getEntryPoints();
				break;
		}
			
	}
}



//**********************************************************************
class Editora extends ApiObject{

	public function getEntryPoints(){
		$operations = Array(cNew, cUpdate, cDelete, cSelect, cSelectAll);
		$data = Array(cObject => cEditora);

		$itens = Array();
		foreach ($operations as $op){
			$item = Array(cURI => API_URI . '?' . cObject . '=' . cEditora . '&' . cOperation . '=' . $op . '&' . cType . '=' . cTemplate,
					cOperation => $op, cType => cTemplate, cMethod => cGET);
			$itens[] = $item; //Adiciona
		}//for
		$data[cOperations] = $itens;

		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doNewTemplate($operation){
		$data = Array(cObject => cEditora,
				cFields => Array(
						Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => ''),
						Array(cField_Name => 'name', cField_Description => 'Nome', cField_Kind => cString, cField_Required => cTrue, cField_Value => '')
				),
				cURI => API_URI . '?' . cObject . '=' . cEditora . '&' . cOperation . '=' . $operation,
				cMethod => cPOST
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doDeleteTemplate(){
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Editora&operation=new_template"
		$data = Array(cObject => cEditora,
				cFields => Array(
						Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => ''),
				),
				cURI => API_URI . '?' . cObject . '=' . cEditora . '&' . cOperation . '=' . cDelete,
				cMethod => cPOST
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doConsultaRegistroTemplate(){
		$data = Array(cObject => cEditora,
				cFields => Array(
						Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => ''),
				),
				cURI => API_URI . '?' . cObject . '=' . cEditora . '&' . cOperation . '=' . cSelect,
				cMethod => cPOST
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doSelectAllTemplate() {
		$data = Array(cObject => cEditora, cURI => API_URI . '?' . cObject . '=' . cEditora . '&' . cOperation . '=' . cSelectAll, cMethod => cGET);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
		/*
		$data = Array(cObject => cEditora,
				cFields => Array(
						Array(cField_Description => 'identificado'),
						Array(cField_Description => 'Nome'),
				),
				cURI => API_URI . '?' . cObject . '=' . cEditora . '&' . cOperation . '=' . cSelectAll,
				cMethod => cPOST
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
		*/
	}

	private function doSelect(){
		$id = getVal('id');
		$errors = Array();
		try {
			if ($id == '')
				throw new Exception('Id deve ser informado.');

			$editoraRest = new PublisherRest();
			$editora = $editoraRest->getPublisherList()->get($id);
				
			if ($editora == null) {
				throw new Exception('Editora nao encontrado.');
			}

			$data = Array(cObject => cEditora,
					cFields => Array(
							Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => $editora->id),
							Array(cField_Name => 'name', cField_Description => 'Nome', cField_Kind => cString, cField_Required => cTrue, cField_Value => $editora->name)
					)
			);
		} catch (Exception $e) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e->getMessage());
		}
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doSelectAll(){
		$editoraRest = new PublisherRest();
		$data = Array(cObject => cEditora,				
				cHeaders => Array( 
									Array( cField_Name => 'Identificador'),
									Array(cField_Name => 'Nome')
								),
				cFields => $editoraRest->getPublisherList()->getAll()
		);				
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doDelete(){
		$id = getVal('id');
		try {
			if ($id == '') {
				throw new Exception("Id deve ser informado.");
			}
			$editoraRest = new PublisherRest();
			$editoraRest->restDelete();
			$data = Array(cResult => cResult_OK, cResult_Message => 'Editora ' . $id . ' Excluido com sucesso');
		} catch (Exception $e) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e->getMessage());
		}

		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doNew(){
		$id = getVal('id');
		$name = getVal('name');

		try {
			if ($id == '')
				throw new Exception('Id deve ser informado.');

			if ($name == '')
				throw new Exception('Nome deve ser informado.');

				
			$editoraRest = new PublisherRest();
			$editoraRest->restPost();
			$data = Array(cResult => cResult_OK, cResult_Message => 'Editora ' . $name . ' cadastrado com sucesso!');
		} catch (Exception $e1) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e1->getMessage());
		}
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doUpdate(){
		$id = getVal('id');
		$name = getVal('name');

		try {
			if ($id == '')
				throw new Exception('Id deve ser informado.');

			if ($name == '')
				throw new Exception('Nome deve ser informado.');

			$editoraRest = new PublisherRest();
			$editoraRest->restPut();
			$data = Array(cResult => cResult_OK, cResult_Message => 'Editora ' . $name . ' Alterado com sucesso!');
		} catch (Exception $e1) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e1->getMessage());
		}
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	private function doNewError(){
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Editora&operation=new"
		$data = Array(cResult => cResult_Error, cResult_Message => 'O nome do Editora deve ser informado.');
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	public function run(){
		$operation = getVal(cOperation);
		$type = getVal(cType);
		switch($operation){
			case cNew:
				if ($type == cTemplate){
					$this->doNewTemplate(cNew);
				}
				else{
					$this->doNew();
				}
				break;
			case cDelete:
				if ($type == cTemplate) {
					$this->doDeleteTemplate();
				} else {
					$this->doDelete();
				}
				break;
			case cSelect:
				if ($type == cTemplate) {
					$this->doConsultaRegistroTemplate();
				} else {
					$this->doSelect();
				}
				break;
			case cSelectAll:
				if ($type == cTemplate) {
					$this->doSelectAllTemplate();
				} else {
					$this->doSelectAll();
				}
				break;
			case cUpdate:
				if ($type == cTemplate) {
					$this->doNewTemplate(cUpdate);
				} else {
					$this->doUpdate();
				}
				break;
			case cNew . '_error':
				$this->doNewError();
				break;

			default:
				$this->getEntryPoints();
				break;
		}
			
	}
}
//**********************************************************************
class Livro extends ApiObject{
	
	public function getEntryPoints(){
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Livro",
		
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Autor&operation=new&type=template",
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Autor&operation=edit"
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Autor&operation=select"
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Autor&operation=selectAll"
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Autor&operation=delete"
		
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Autor&operation=new",
		$operations = Array(cNew, cUpdate, cDelete, cSelect, cSelectAll);
		$data = Array(cObject => cLivro);
		
		$itens = Array();
		foreach ($operations as $op){
			$item = Array(cURI => API_URI . '?' . cObject . '=' . cLivro . '&' . cOperation . '=' . $op . '&' . cType . '=' . cTemplate, 
				  cOperation => $op, cType => cTemplate, cMethod => cGET);
			$itens[] = $item; //Adiciona
		}//for
		$data[cOperations] = $itens;
		
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}
	
	private function doNewTemplate($operation){
		$data = Array(cObject => cLivro, 
		cFields => Array(
			Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => ''),
			Array(cField_Name => 'title', cField_Description => 'Titulo', cField_Kind => cString, cField_Required => cTrue, cField_Value => ''),		
			Array(cField_Name => 'author', cField_Description => 'Autor', cField_Kind => cString, cField_Required => cTrue, cField_Value => ''),
			Array(cField_Name => 'price', cField_Description => 'Preco', cField_Kind => cDouble, cField_Required => cTrue, cField_Value => ''),
			Array(cField_Name => 'site', cField_Description => 'Site', cField_Kind => cString, cField_Required => cFalse, cField_Value => '')
		),
		cURI => API_URI . '?' . cObject . '=' . cLivro . '&' . cOperation . '=' . $operation,
		cMethod => cPOST
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}
	
	private function doDeleteTemplate(){
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Livro&operation=new_template"
		$data = Array(cObject => cLivro,
				cFields => Array(
						Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => ''),
				),
				cURI => API_URI . '?' . cObject . '=' . cLivro . '&' . cOperation . '=' . cDelete,
				cMethod => cPOST
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}
	
	private function doConsultaRegistroTemplate(){
		$data = Array(cObject => cLivro,
				cFields => Array(
						Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => ''),
				),
				cURI => API_URI . '?' . cObject . '=' . cLivro . '&' . cOperation . '=' . cSelect,
				cMethod => cPOST
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}
	
	private function doSelectAllTemplate() {		
		$data = Array(cObject => cLivro, cURI => API_URI . '?' . cObject . '=' . cLivro . '&' . cOperation . '=' . cSelectAll, cMethod => cGET);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));		
	}
	
	public function doSelect(){
		$id = getVal('id');
		$errors = Array();
		try {
			if ($id == '')
				throw new Exception('Id deve ser informado.');
				
			$boookRest = new BookRest();
			$book = $boookRest->getBookList()->get($id);
			
			if ($book == null) {
				throw new Exception('Livro nao encontrado.');
			}
				
			$data = Array(cObject => cLivro,
					cFields => Array(
							Array(cField_Name => 'id', cField_Description => 'Identificador', cField_Kind => cInteger, cField_Required => cTrue, cField_Value => $book->id),
							Array(cField_Name => 'title', cField_Description => 'Titulo', cField_Kind => cString, cField_Required => cTrue, cField_Value => $book->title),
							Array(cField_Name => 'author', cField_Description => 'Autor', cField_Kind => cString, cField_Required => cTrue, cField_Value => $book->author),
							Array(cField_Name => 'price', cField_Description => 'Preco', cField_Kind => cDouble, cField_Required => cTrue, cField_Value => $book->price),
							Array(cField_Name => 'site', cField_Description => 'Site', cField_Kind => cString, cField_Required => cFalse, cField_Value => $book->site)
					)
			);
		} catch (Exception $e) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e->getMessage());
		}
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}
	
	public function doSelectAll(){
		$boookRest = new BookRest();
		$data = Array(cObject => cEditora,
				cHeaders => Array(
							Array(cField_Name => 'id'),
							Array(cField_Name => 'title'),
							Array(cField_Name => 'author'),
							Array(cField_Name => 'price'),
							Array(cField_Name => 'site')
						),
				cFields => $boookRest->getBookList()->getAll()
		);
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));		
	}	

	public function doDelete(){
		$id = getVal('id');			
		try {
			if ($id == '') {
				throw new Exception("Id deve ser informado.");
			}
			$boookRest = new BookRest();
			$boookRest->restDelete();
			$data = Array(cResult => cResult_OK, cResult_Message => 'Livro ' . $id . ' Excluido com sucesso');
		} catch (Exception $e) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e->getMessage());
		}

		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}	
	
	public function doNew(){
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Livro&operation=new"
		$id = getVal('id');
		$title = getVal('title');
		$author = getVal('author');
		$price = getVal('price');
		$site = getVal('site');
		
		try {
			if ($id == '')
				throw new Exception('Id deve ser informado.');
				
			if ($title == '')
				throw new Exception('Titulo deve ser informado.');
				
			if ($author == '')
				throw new Exception('Autor deve ser informado.');
				
			if ($price == '')
				throw new Exception('Preco deve ser informado.');				
			
			$boookRest = new BookRest();
			$boookRest->restPost();
			$data = Array(cResult => cResult_OK, cResult_Message => 'Livro ' . $title . ' cadastrado com sucesso!');				
		} catch (Exception $e1) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e1->getMessage());
		}
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}
	
	public function doUpdate(){
		$id = getVal('id');
		$title = getVal('title');
		$author = getVal('author');
		$price = getVal('price');
		$site = getVal('site');
	
		try {
			if ($id == '')
				throw new Exception('Id deve ser informado.');
	
			if ($title == '')
				throw new Exception('Titulo deve ser informado.');
	
			if ($author == '')
				throw new Exception('Autor deve ser informado.');
	
			if ($price == '')
				throw new Exception('Preco deve ser informado.');
				
			$boookRest = new BookRest();
			$boookRest->restPut();
			$data = Array(cResult => cResult_OK, cResult_Message => 'Livro ' . $title . ' Alterado com sucesso!');
		} catch (Exception $e1) {
			$data = Array(cResult => cResult_Error, cResult_Message => $e1->getMessage());
		}
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}	
	
	public function doNewError(){
		//"uri": "http://localhost/exampleHATEOAS/api/api.php?object=Livro&operation=new"
		$data = Array(cResult => cResult_Error, cResult_Message => 'O nome do livro deve ser informado.');
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}
	
	public function run(){
		$operation = getVal(cOperation);
		$type = getVal(cType);
		switch($operation){
			case cNew: 
				if ($type == cTemplate){
					$this->doNewTemplate(cNew);
				}
				else{
					$this->doNew();
				}
				break;
			case cDelete:
				if ($type == cTemplate) {
					$this->doDeleteTemplate();
				} else {
					$this->doDelete();
				}
				break;
			case cSelect:
				if ($type == cTemplate) {
					$this->doConsultaRegistroTemplate();
				} else {
					$this->doSelect();
				}
				break;
			case cSelectAll:
				if ($type == cTemplate) {
					$this->doSelectAllTemplate();
				} else {
					$this->doSelectAll();
				}				
				break;
				case cUpdate:
					if ($type == cTemplate) {
						$this->doNewTemplate(cUpdate);
					} else {
						$this->doUpdate();
					}
					break;				
			case cNew . '_error':  				
				$this->doNewError();
				break;

			default:
				$this->getEntryPoints();
				break;
		}
			
	}
}

class Api extends ApiObject{
	
	
	public function getEntryPoints(){
		$data = Array(
					Array(cObject => cAutor, cURI => API_URI . '?' . cObject . '=' . cAutor, cMethod => cGET),
					Array(cObject => cEditora, cURI => API_URI . '?' . cObject . '=' . cEditora, cMethod => cGET),
					Array(cObject => cLivro, cURI => API_URI . '?' . cObject . '=' . cLivro, cMethod => cGET),
				);
		
		sendJson(stripslashes(json_encode($data, JSON_PRETTY_PRINT)));
	}

	
	
	public function run(){
		$object = getVal(cObject);
		if ($object != ''){
			(new $object)->run();
		}
		else	
  			$this->getEntryPoints();
	}
}


$api = new Api();
$api->run();
