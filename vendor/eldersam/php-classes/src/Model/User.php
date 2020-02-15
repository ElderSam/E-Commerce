<?php

namespace Eldersam\Model;
use \Eldersam\DB\Sql;
use \Eldersam\Model;

class User extends Model{

    const SESSION = "User";

    public static function login($login, $password){

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login 
        ));

        if(count($results) == 0) //se não encontrou o login
        {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }

        $data = $results[0];

        if(password_verify($password, $data["despassword"])){ //se a senha digitada é equivalente ao Hash do banco

            $user = new User();

            //$user->setiduser($data["iduser"]);
            $user->setData($data);

            /*var_dump($user);
            exit;*/
            

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
            

        }else {
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }

    }

    public static function verifyLogin(){
       // $inadmin = true;

      /* echo "socorro";
       echo "<br>" . $_SESSION[User::SESSION]["inadmin"] . "<br>";
       if(!(bool)$_SESSION[User::SESSION]["inadmin"]) {
        echo "false";
       }else{
           
           echo "true";
       }
       
       exit;*/
        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0 //se é um usuário. obs: se for vazia, transforma em 0
            ||
            !(bool)$_SESSION[User::SESSION]["inadmin"] //se é um ADMINISTRADOR
        ){
            
            header("Location: /admin/login/");
            exit;

        }
    }

    public static function logout(){
        
        $_SESSION[User::SESSION] = NULL;
    }
}