<?php

use \Eldersam\Page;

/*rota principal -----------------------------------------------------------*/
$app->get('/', function() {
	
	$page = new Page(); //cria uma nova página

	$page->setTpl("index"); //mostra o conteúdo de index.html

});
