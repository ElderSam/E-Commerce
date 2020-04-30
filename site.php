<?php

use \Eldersam\Page;
use \Eldersam\Model\Category;
use \Eldersam\Model\Product;
use \Eldersam\Model\Cart;
use \Eldersam\Model\Address;
use \Eldersam\Model\User;

/*rota principal -----------------------------------------------------------*/
$app->get('/', function() {
	
	$products = Product::listAll();

	$page = new Page(); //cria uma nova página
	
	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]); //mostra o conteúdo de index.html

});

$app->get("/categories/:idcategory", function($idcategory){

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for($i=1; $i <= $pagination['pages']; $i++){

		array_push($pages, [
			'link'=>'/categories/' . $category->getidcategory().'?page='.$i,
			'page'=>$i
		]);
	}
	$page = new Page();

	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination["data"], //retorna os dados
		'pages'=>$pages		
	]);
});

//rota de detalhes do produto
$app->get("/products/:desurl", function($desurl){
	
	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);

});

//rota para a página do carrinho
$app->get("/cart", function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);
});

//rota para adicionar produto ao carrinho
$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	//recupera o carrinho
	$cart = Cart::getFromSession();

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for($i=0; $i<$qtd; $i++){

		$cart->addProduct($product);
	}

	header("Location: /cart");
	exit;
});

//rota para remover uma quantidade de um produto do carrinho
$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	//recupera o carrinho
	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});


//rota para remover todos os produto do mesmo tipo (todas as quantidades relacionadas ao mesmo produto) do carrinho
$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	//recupera o carrinho
	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true); //true para remover todos

	header("Location: /cart");
	exit;
});

$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;
});

$app->get("/checkout", function(){

	User::verifyLogin(false);
	
	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);
});


$app->get("/login", function(){

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);
});


$app->post("/login", function(){

	try{
		User::login($_POST['login'], $_POST['password']);

	}catch(Exception $e){

		User::setError($e->getMessage());
	}

	header("Location: /checkout");
	exit;

});


$app->get("/logout", function(){
	
	User::logout();

	header("Location: /login");
	exit;
});

$app->post("/register", function(){
	
	//guarda os valores dos campos digitados
	$_SESSION['registerValues'] = $_POST;

	if(!isset($_POST['name']) || $_POST['name'] == ''){

		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}

	if(!isset($_POST['email']) || $_POST['email'] == ''){

		User::setErrorRegister("Preencha o seu e-mail.");
		header("Location: /login");
		exit;
	}

	if(!isset($_POST['password']) || $_POST['password'] == ''){

		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;
	}

	if(User::checkLoginExists($_POST['email']) === true){

		User::setErrorRegister("Este endereo de e-mail já está sendo usado por outro usuário.");
		header("Location: /login");
		exit;
	}

	$user = new User();

	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone'],
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header('Location: /checkout');
	exit;
});

/* ---------------------------- Rotas de Recuperação de senha ------------------------ */
/* Rota FORGOT (página com formulário com email) ---------------- */
$app->get("/forgot", function(){

	$page = new Page();
	
	$page->setTpl("forgot"); //mostra o conteúdo de forgot.html
});


/* Rota FORGOT (envia email) -------------------------*/
$app->post("/forgot", function(){

	User::getForgot($_POST["email"], false);

	header("Location: /forgot/sent");
	exit;

});

/* Rota FORGOT/SENT (página de email enviado com sucesso) */
$app->get("/forgot/sent", function(){

	$page = new Page();
	
	$page->setTpl("forgot-sent"); //mostra o conteúdo de forgot-sent.html

});


$app->get("/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();
	
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"] //o código criptografado, para impedir o hacker acessar a página e recuperar
	)); //mostra o conteúdo de forgot-reset-success.html

});

$app->post("/forgot/reset", function(){

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

	$page = new Page();
	
	$page->setTpl("forgot-reset-success"); //mostra o conteúdo de forgot-reset.html

}); //fim rota /forgot/reset

$app->get("/profile", function(){

	User::verifyLogin(false);

	$user = User::getFromSession();

	$page = new Page();

	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);
});

$app->post("/profile", function(){

	User::verifyLogin(false);

	if(!isset($_POST['desperson']) || $_POST['desperson'] == ''){

		User::setError("Preencha o seu nome");
		header('Location: /profile');
		exit;
	}

	if(!isset($_POST['desemail']) || $_POST['desemail'] == ''){

		User::setError("Preencha o seu e-mail");
		header('Location: /profile');
		exit;
	}

	$user = User::getFromSession();

	if($_POST['desemail'] !== $user->getdesemail()){
		
		if(User::checkLoginExists($_POST['desemail']) === true){

			User::setError("Este endereço de e-mail já está cadastrado");
			header('Location: /profile');
			exit;
		}
	}

	//mantêm o valor do campo admin, e a senha. Dessa forma evita Command Injection
	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->update();

	User::setSuccess("Dados alterados com sucesso!");

	header('Location: /profile');
	exit;
});
