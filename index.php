<?php 
session_start();

require_once("vendor/autoload.php");

//Namespaces que vou usar
use \Slim\Slim;
use \Eldersam\Page;
use \Eldersam\PageAdmin;
use \Eldersam\Model\User;

$app = new Slim(); //chama nova aplicação do Slim

//configura o modo Debug para explicar cada erro 
$app->config('debug', true);

/*rota principal -----------------------------------------------------------*/
$app->get('/', function() {
	
	$page = new Page(); //cria uma nova página

	$page->setTpl("index"); //mostra o conteúdo de index.html

});


/*rota Admin ---------------------------------------------------------------- */
$app->get('/admin/', function() {
	
	User::verifyLogin();

	$page = new PageAdmin(); //cria uma nova página
	
	$page->setTpl("index"); //mostra o conteúdo de index.html

});


/* rota página de Login -------------------------------------*/
$app->get('/admin/login/', function() {
	
	$page = new PageAdmin([
		//desabilita o header e footer padroes
		"header"=>false,
		"footer"=>false
	]);
	
	$page->setTpl("login"); //mostra o conteúdo de index.html

});

/* rota Logar (envia formulário para o Banco de dados) -------------------------------------*/
$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]); //chama o método estático da Classe User

	header("Location: /admin"); //vai para a área de admin (após logado)
	exit;

});

/* rota Deslogar (sair) -------------------------------------*/
$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;
});

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




//executa
$app->run();

 ?>