<?php

use \Eldersam\Page;
use \Eldersam\Model\Product;

/*rota principal -----------------------------------------------------------*/
$app->get('/', function() {
	
	$products = Product::listAll();

	$page = new Page(); //cria uma nova página
	
	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]); //mostra o conteúdo de index.html

});
