<?php 
session_start();

require_once("vendor/autoload.php");

//Namespaces que vou usar
use \Slim\Slim;

$app = new Slim(); //chama nova aplicação do Slim

//configura o modo Debug para explicar cada erro 
$app->config('debug', true);

require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");
//executa
$app->run();

 ?>