<?php

namespace Eldersam;
use Rain\Tpl; //para saber que quando usarmos a classe Tpl ela está no namespace Rain


class PageAdmin extends Page{ //cria a classe PageAdmin herdando de Page

    public function __construct($opts = array(), $tpl_dir = "/views/admin/"){
       
        parent::__construct($opts, $tpl_dir); //chama o construtor do pai
    }

}