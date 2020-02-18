<?php 

namespace Eldersam\Model;

use \Eldersam\DB\Sql;
use \Eldersam\Model;
use \Eldersam\Mailer;

class Product extends Model {

	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}

	public function save()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));

		$this->setData($results[0]);

	}

	public function get($idproduct)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
			':idproduct'=>$idproduct
		]);

		$this->setData($results[0]);

	}

	public function delete()
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
			':idproduct'=>$this->getidproduct()
		]);

	}

	public function checkPhoto(){

		if(file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
		"res" . DIRECTORY_SEPARATOR . 
		"site" . DIRECTORY_SEPARATOR . 
		"img" . DIRECTORY_SEPARATOR . 
		"products" . DIRECTORY_SEPARATOR . 
		$this->getidproduct() . ".jpg"
		)){

			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
		
		}else{ //producto sem foto
			$url = "/res/site/img/product.jpg"; //retorna foto padrão

		}

		return $this->setdesphoto($url);
	}
	
	public function getValues(){

		$this->checkPhoto();

		$values = parent::getValues();

		return $values;
	}

	public function setPhoto($file)
	{

		$extension = explode('.', $file['name']); //pega o nome do meu arquivo, onde tem ponto e faz um array
		$extension = end($extension); //pega a extensao (3 últimos dígitos)

		switch ($extension) {

			case "jpg":
			case "jpeg":
			$image = imagecreatefromjpeg($file["tmp_name"]);
			break;

			case "gif":
			$image = imagecreatefromgif($file["tmp_name"]);
			break;

			case "png":
			$image = imagecreatefrompng($file["tmp_name"]);
			break;

			default:
			$imageExists = true;
			break;

		}

		$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
		"res" . DIRECTORY_SEPARATOR . 
		"site" . DIRECTORY_SEPARATOR . 
		"img" . DIRECTORY_SEPARATOR . 
		"products" . DIRECTORY_SEPARATOR . 
		$this->getidproduct() . ".jpg";


		if(!isset($imageExists)){
			
			imagejpeg($image, $dist);

			imagedestroy($image);
	
			$this->checkPhoto(); //va para a memória do desphoto
	
		}


	}


    
}