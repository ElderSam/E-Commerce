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

        return $cart;
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

    public function addProduct(Product $product){

        $sql = new Sql();

        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [
            'idcart'=>$this->getidcart(),
            'idproduct'=>$product->getidproduct()
        ]);

    }

    public function removeProduct(Product $product, $all = false){

        $sql = new Sql();

        if($all){

            //remote todos
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE (idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL)", [
                'idcart'=>$this->getidcart(),
            'idproduct'=>$product->getidproduct()
            ]);

        }else{
            //remote apenas um
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE (idcart = :idcart AND idproduct = :idproduct 
                        AND dtremoved IS NULL LIMIT 1)", [
                'idcart'=>$this->getidcart(),
                'idproduct'=>$product->getidproduct()
            ]);
        }
    }

    public function getProducts(){

        $sql = new Sql();

        $rows = $sql->select("SELECT b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
            FROM tb_cartsproducts a 
            INNER JOIN tb_products b ON (a.idproduct = b.idproduct) 
            WHERE (a.idcart = :idcart AND a.dtremoved IS NULL) 
            GROUP BY b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
            ORDER BY b.desproduct", 
            [
                ':idcart'=>$this->getidcart()
            ]);
 
        return Product::checkList($rows);
    }

    
}