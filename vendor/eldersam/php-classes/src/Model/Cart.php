<?php 

namespace Eldersam\Model;

use \Eldersam\DB\Sql;
use \Eldersam\Model;
use \Eldersam\Model\User;
use \Eldersam\Mailer;

class Cart extends Model {

    const SESSION = "Cart";

    public static function getFromSession(){

        $cart = new Cart();

        //var_dump($_SESSION[Cart::SESSION]);

        //se o carrinho foi inserindo no banco de o usuário está na sessão
        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){

            //carrega o carrinho
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

        }else{
            //cria um novo carrinho
            $cart->getFromSessionID();

            if(!(int)$cart->getidcart() > 0){

                $data = [
                    'dessessionid'=>session_id()
                ];

               
                if(User::checkLogin(false)){  //passo false pois não estou na administração e sim no carrinho de compras

                    $user = User::getFromSession();

                    //para criar um carrinho no BD já informando quem é o usuário
                    $data['iduser'] = $user->getiduser();

                }
                
                $cart->setData($data);

                //salva o carrinho no BD
                $cart->save();

                //coloca o meu carrinho na sessão
                $cart->setToSession();
                
            }

        }
    }

    public function setToSession(){

        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    
    public function getFromSessionID(){

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
            ':dessessionid'=>session_id()
        ]);

        if(count($results)){
            $this->setData($results[0]);
        }
    }


    public function get(int $idcart){

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
            ':idcart'=>$idcart
        ]);

        if(count($results)){
            $this->setData($results[0]);
        }
        
    }

    public function save(){

        $sql = new Sql();

        $results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcod, :vlfreight, :nrdays)", [
            'idcart'=>$this->getidcart(),
            'dessessionid'=>$this->getdessessionid(),
            'iduser'=>$this->getiduser(),
            'deszipcod'=>$this->getdeszipcod(),
            'vlfreight'=>$this->getvlfreight(),
            'nrdays'=>$this->getnrdays()
        ]);

        $this->setData($results[0]);
    }

    
}