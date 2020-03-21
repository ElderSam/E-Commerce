<?php

namespace Eldersam\Model;

use \Eldersam\DB\Sql;
use \Eldersam\Model;
use \Eldersam\Mailer;


class User extends Model{

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret"; //here your key (secret)
    const SECRET_IV = "HcodePhp7_Secret_IV"; //here your key 2 (secret)

    public static function getFromSession(){

        if(isset($_SESSION[User::SESSION]) && ((int)$_SESSION[User::SESSION]['iduser'] > 0)){

            $user = new User();

            $user->setData($_SESSION[User::SESSION]);
         
        }

        return $user;
    }

    public static function checkLogin($inadmin = true){

        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0 //se é um usuário. obs: se for vazia, transforma em 0  
        ){
            return false;

        }else{

            if($inadmin == true && (bool)$_SESSION[User::SESSION]['inadmin'] == true){

                return true;

            }else{

                return false;
            }          
            
        }
    }

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

    public static function verifyLogin($inadmin = true){

        if(User::checkLogin($inadmin)){
            
            header("Location: /admin/login/");
            exit;

        }
    }

    public static function logout(){
        
        $_SESSION[User::SESSION] = NULL;
    }

    public static function listAll(){

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    public function save(){
        
        $sql = new Sql();

        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()

        ));

        $this->setData($results[0]);
    }

    public function get($iduser){

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));

        $this->setData($results[0]);
    }

    public function update(){
                
        $sql = new Sql();

        //chama a procedure update do banco de dados
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()

        ));

        $this->setData($results[0]);
    }

    public function delete(){

        $sql = new Sql();

        $sql->query("CALL sp_users_delete(:iduser)", array(
            "iduser"=>$this->getiduser()
        ));
    }

    public static function getForgot($email){

        $sql = new Sql();

        $results = $sql->select("
        SELECT * 
        FROM tb_persons a 
        INNER JOIN tb_users b USING(idperson) 
        WHERE a.desemail = :desemail;
        ", array(
            ":desemail"=>$email
        ));

        if(count($results) == 0){ //se não retornou nenhum email

            throw new \Exception("Não foi possível recuperar a senha.");
        
        }else{

            $data = $results[0];
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));

            if(count($results2) === 0){

                throw new \Exception("Não foi possível recuperar a senha.");
            
            }else{

                $dataRecovery = $results2[0];

                $code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
                
                base64_encode($code);

                $link = "http://www.ecommerce.com.br/admin/forgot/reset?code=$code";

                //obs: O quarto arqumento do contrutor do Mailer, é a página que vai enviar para o e-mail, e está em /view/email/forgot.html
                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Green Store", "forgot",
                array(
                    "name"=>$data["desperson"],
                    "link"=>$link
                ));

                $mailer->send();

                return $data; //retorna com os dados do usuário que foi recuperado
            }
        }
    }

    public function validForgotDecrypt($code){

        $sql = new Sql();
        
        base64_decode($code); //decodifica o get recebido para recuperar a senha do usuário

        $idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
       

        //OBS: lembre-se que nesse caso, na query abaixo está definido o INTERVAL de tempo para recuperar a senha em até 1 hora
        $results = $sql->select("
            SELECT *
            FROM tb_userspasswordsrecoveries a
            INNER JOIN tb_users b USING(iduser)
            INNER JOIN tb_persons c USING(idperson)
            WHERE
                a.idrecovery = :idrecovery
                AND
                a.dtrecovery IS NULL
                AND
                DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
        ", array(
            ":idrecovery"=>$idrecovery
        ));

        if (count($results) === 0) //não retornou nada
        {
            throw new \Exception("Não foi possível recuperar a senha.");
        }
        else
        {

            return $results[0];

        }    
    }


    public static function setForgotUsed($idrecovery){

        $sql = new Sql();

        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
            ":idrecovery"=>$idrecovery
        ));
    }

    public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));

	}
}
