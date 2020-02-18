<?php

use \Eldersam\PageAdmin;
use \Eldersam\Model\User;

/* rota página de Usuários -------------------------------------*/
$app->get('/admin/users/', function() {
	
	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array( //mostra o conteúdo de users.html
		"users"=>$users
	));

});

/* rota página de criação de Usuários (formulário) -------------------------------------*/
$app->get('/admin/users/create', function() {
	
	User::verifyLogin();
	
	$page = new PageAdmin();
	
	$page->setTpl("users-create"); //mostra o conteúdo de users-crete.html

});

/* rota Deletar Usuários -------------------------------------*/
$app->get("/admin/users/:iduser/delete", function($iduser){
	
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser); //carrega o usuário, para ter certeza que ainda existe no banco

	$user->delete();

	header("Location: /admin/users");
	exit;
	
});

/* rota página atualizar Usuário (formulário) -------------------------------------*/
$app->get('/admin/users/:iduser', function($iduser) {
	
	User::verifyLogin();
	
	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	//mostra o conteúdo de users-update.html
	$page->setTpl("users-update", array( 
		"user"=>$user->getValues()
	)); 

});

/* rota criar Usuários -------------------------------------*/
$app->post("/admin/users/create", function(){
	
	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	//criptografa a senha
	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;
	
});

/* rota Atualizar Usuário pelo formulário (UPDATE) -------------------------------------*/
$app->post("/admin/users/:iduser", function($iduser){
	
	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
	
	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;
	
});

/* Rota FORGOT (página com formulário com email) ---------------- */
$app->get("/admin/forgot", function(){

	$page = new PageAdmin([
		//desabilita o header e footer padroes
		"header"=>false,
		"footer"=>false
	]);
	
	$page->setTpl("forgot"); //mostra o conteúdo de forgot.html
});


/* Rota FORGOT (envia email) -------------------------*/
$app->post("/admin/forgot", function(){

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;

});

/* Rota FORGOT/SENT (página de email enviado com sucesso) */
$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		//desabilita o header e footer padroes
		"header"=>false,
		"footer"=>false
	]);
	
	$page->setTpl("forgot-sent"); //mostra o conteúdo de forgot-sent.html

});


$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		//desabilita o header e footer padroes
		"header"=>false,
		"footer"=>false
	]);
	
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"] //o código criptografado, para impedir o hacker acessar a página e recuperar
	)); //mostra o conteúdo de forgot-reset-success.html

});


$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]); //Para segurança

	User::setForgotUsed($forgot["idrecovery"]);

	//Trocar a senha de fato -------------------------------
	$user = new User();

	$user->get((int)$forgot["iduser"]); //pega o id do usuário que vai recuperar

	//https://www.php.net/manual/en/function.password-hash.php
	$password =  password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12 //quanto maior o número, mais seguro vai ser o hash, porém mais vai usar do processamento do servidor, o que poder fazer ele 'cair' se tiver muitas requisições disso ao mesmo tempo
	]);

	$user->setPassword($password); //muda a senha recebida pelo formulário de recuperação

	$page = new PageAdmin([
		//desabilita o header e footer padroes
		"header"=>false,
		"footer"=>false
	]);
	
	$page->setTpl("forgot-reset-success"); //mostra o conteúdo de forgot-reset.html

}); //fim rota /admin/forgot/reset
