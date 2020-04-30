<?php

use \Eldersam\Model\User;

//formatar o preço
function formatPrice($vlprice){

    if(!$vlprice > 0) $vlprice = 0;

    return number_format((float)$vlprice, 2, ",", "."); //obs: formata com 2 casas decimais, com o separador vírgula para décimo, e separador ponto para milhares
}

function checkLogin($inadmin = true){

    return User::checkLogin($inadmin);

}

function getUserName(){

    $user = User::getFromSession();

    return $user->getdesperson();

}