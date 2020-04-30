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

	if(User::checkLoginExist($_POST['email']) === true){

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


