var vUrlAPI = 'http://localhost:8078/exampleHATEOAS/api/api.php';
var vTemplate = null;
var opDelete = null;
var opSelect = null;
var opSelectAll = null;
var opUpdate = null;
var type = null/

$(document).ready(function(){
	type = $('#type').val();
	console.log(type);
	mountForm();	
	$('#console').attr('style', 'border: 1px solid red');
	$('#btnDel').click(deletar);
	$('#btnSelect').click(selecionar);
	$('#btnNew').click(btnNewClick);
	$('#btnSelectAll').click(btnSelectAll);
	$('#btnSave').click(save);
	$('#btnEdit').click(update);
});

function msg(text){
	$('#msg').removeClass( "alert alert-danger");
	$('#msg').html(text);
	$('#msg').addClass( "alert alert-success");
	showMsg();
}

function error(text){
	$('#msg').removeClass( "alert alert-success");
	$('#msg').html(text);
	$('#msg').addClass( "alert alert-danger");
	showMsg();
}
 
function showMsg(){
	$('#msg').show();
}

function hideMsg(){
	$('#msg').hide();
}

function showLoading(){
	$('#msg').html('<img src="loading.gif" alt="Carregando..." style="display:block">');	
	showMsg();
}

function btnNewClick(){
	mountForm();
}

function log(texto){
	var now = new Date;
	var DataHora = now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds() + ":" + now.getMilliseconds();
	texto = DataHora + ' - ' + texto + '<br>';
	if (texto.indexOf('ERROR') > -1) {
		error(texto);
	} else {
		msg(texto);
	};
}

function fail(jqXHR, textStatus){
	log('Erro: ' + textStatus);
}

function mountForm(){
	$.ajax({
		url: vUrlAPI,
		dataType: 'json',
		type: 'GET'
	})
	.done(function(data){
		var i;
		var obj = null;
		for (i = 0; i < data.length; i++){
			obj = data[i];
			if (obj.object == type)
				break;
			
		}//for
		
		if(obj == null){
			log('Não encontrou o objeto '+type+'.');
			return;
		}
		
		mountForm2(obj);
	})
	.fail(fail);
}

function mountForm2(obj){
	$.ajax({
		url: obj.uri,
		dataType: 'json',
		type: obj.method
	})
	.done(function(data){
		var i;
		var operations = data.operations;
		var op = null;
		for (i = 0; i < operations.length; i++){
			if (operations[i].operation == 'new' 
					&& operations[i].type == 'template') {
				op = operations[i];
			}
			
			if (operations[i].operation == 'delete') {
				opDelete = operations[i];
			}
			
			if (operations[i].operation == 'select') {
				opSelect = operations[i];
			}
			
			if (operations[i].operation == "selectAll") {
				opSelectAll = operations[i];
			}
			
			if (operations[i].operation == "update") {
				opUpdate = operations[i];
			}			
				
			if (op != null && opDelete != null && opUpdate != null 
					&& opSelect != null && opSelectAll != null) {
				break;
			}
			
		}//for
		
		if(op == null){
			log('Não encontrou operação "new" no template do objeto "Livro".');
			return;
		}
		
		mountForm3(op);
	})
	.fail(fail);
}

function mountForm3(op){
	$.ajax({
		url: op.uri,
		dataType: 'json',
		type: op.method
	})
	.done(function(data){
		vTemplate = data;
		carregaFormulario(data.fields, false);
	})
	.fail(fail);
}

function carregaFormulario(fields, showValue) {
	var form = $('#frmLivro');
	var html = '';
	var i;
	for(i = 0; i < fields.length; i++){
		var field = fields[i];
		html += '<p><label class="col-sm-2 control-label" for="' + field.name + '" >' + field.description + ':</label>';
		html += '<input class="form-control" type="text" placeholder="'+ field.description +'" id="' + field.name + '" name="' + field.name + '" ';
		if (showValue) {
			html+= 'value="' + field.value +'"';
		}
		html +=' >';
		html += ' </p>';
	}
	form.html(html);	
}

function save(){
	var xData = new FormData();
	var fields = vTemplate.fields;
	var i;
	for(i = 0; i < fields.length; i++){
		var field = fields[i];
		var val = $('#' + field.name).val();
		xData.append(field.name, val);
	}//for
	
	$.ajax({
		url: vTemplate.uri,
		dataType: 'json',
		type: vTemplate.method,
		
		//Para funciotnar POST com FormData
		processData: false,
	    contentType: false,
		data: xData
	})
	.done(function(data){
		log(data.result + ' - ' + data.message);
	})
	.fail(fail);	
}

//Excluir
function deletar(){
	$.ajax({
		url: opDelete.uri,
		dataType: 'json',
		type: opDelete.method,
	})
	.done(function(data){
		delete2(data);
	})
	.fail(fail);
}

function delete2(data) {
	var xData = new FormData();
	var fields = vTemplate.fields;
	var t, v;
	for (t = 0; t < data.fields.length; t++) {
		for(v = 0; v < fields.length; v++){
			if (fields[v].name == data.fields[t].name) {					
				var val = $('#' + fields[v].name).val();
				xData.append(data.fields[t].name, val);
			}
		}		
	}//for
		
	$.ajax({
		url: data.uri,
		dataType: 'json',
		type: data.method,		
		//Para funciotnar POST com FormData
		processData: false,
	    contentType: false,
		data: xData
	}).done(function(data){
		if (data.message != null) {
			log(data.result + ' - ' + data.message);
		}
		mountForm();
    })
    .fail(fail);
}

//Seleção
function selecionar(){
	$.ajax({
		url: opSelect.uri,
		dataType: 'json',
		type: opSelect.method,
	})
	.done(function(data){
		select2(data);
	})
	.fail(fail);
}

function select2(data) {
	var xData = new FormData();
    var fields = vTemplate.fields;
    var i;
    for(i = 0; i < fields.length; i++){
        var field = fields[i];
        if (field.name == 'id') {
        	xData.append(field.name, $('#' + field.name).val());
            break;
        }
    }//for	

	$.ajax({
		url: data.uri,
		dataType: 'json',
		type: data.method,
		//Para funciotnar POST com FormData
		processData: false,
	    contentType: false,		
		data: xData
	}).done(function(data) {
		if (data.message != null) {
			log(data.result + ' - ' + data.message);
		} else {
	        carregaFormulario(data.fields, true);
	        $("#id").prop('disabled', true);
		}
	})
	.fail(fail);	
}


//Select Todos
function btnSelectAll(){
	$.ajax({
		url: opSelectAll.uri,
		dataType: 'json',
		type: opSelectAll.method,
	})
	.done(function(data){
		selectAll2(data);
	})
	.fail(fail);
}

function selectAll2(data) {
	$.ajax({
		url: data.uri,
		dataType: 'json',
		type: data.method,
	}).done(function(data) {
		if (data.message != null) {
			log(data.result + ' - ' + data.message);
		} else {
			$('#dados').empty();
			var html = '<table class="table table-striped"><thead><tr>';
			$.each(data.headers, function(i, obj1) {
				html += '<th>'+obj1.name+'</th>';
			});//fim for each
			html += '<tbody>';
			if (type == 'Livro') {
				geraTabelaLivro(data, html);
			} 
			if (type == 'Editora' || type == 'Autor') {
				geraTabelaEditora(data, html);
			}
		}
	})
	.fail(fail);	
}

function geraTabelaLivro(data, html) {
	$.each(data.fields, function(t, obj2){				
		html += '<tr>' +
		'<td>' + obj2.id + '</td>' +
		'<td>' + obj2.title + '</td>' +
		'<td>' + obj2.author + '</td>' +
		'<td>' + obj2.price + '</td>' +
		'<td>' + obj2.site + '</td>' +
		'</tr>';
	});//for each	
	html += '</tbody></table>';
	$('#dados').append(html);
}

function geraTabelaEditora(data, html) {
	$.each(data.fields, function(t, obj2){				
		html += '<tr>' +
		'<td>' + obj2.id + '</td>' +
		'<td>' + obj2.name + '</td>' +
		'</tr>';
	});//for each	
	html += '</tbody></table>';
	$('#dados').append(html);
}

//Update
function update(){
	$.ajax({
		url: opUpdate.uri,
		dataType: 'json',
		type: opUpdate.method,
	})
	.done(function(data){
		update2(data);
	})
	.fail(fail);
}

function update2(data) {
	var xData = new FormData();
	$.each(data.fields, function(i, obj) {
		$.each(vTemplate.fields, function(i, obj2) {
			if (obj.name == obj2.name) {
				xData.append(obj.name, $('#' + obj.name).val());
			}
		});//fim for each		
	});//fim for each
	$.ajax({
		url: data.uri,
		dataType: 'json',
		type: data.method,
		//Para funciotnar POST com FormData
		processData: false,
	    contentType: false,		
		data: xData
	}).done(function(data) {
		log(data.result + ' - ' + data.message);
	})
	.fail(fail);	
}