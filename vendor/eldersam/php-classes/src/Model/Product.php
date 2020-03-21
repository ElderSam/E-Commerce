<?php 

namespace Eldersam\Model;

use \Eldersam\DB\Sql;
use \Eldersam\Model;
use \Eldersam\Mailer;

class Product extends Model {

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}

	//converte um array de dados de produtos em um array de objetos Product, para que seja mais fácil renderizá-los na página HTML
	public static function checkList($list){ 

		foreach($list as &$row){ //obs: observe que manda o endereço da row na list

			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();
		}

		return $list; //retorna o array list já formatado (com desphoto)
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

	public function getFromURL($desurl){

		$sql = new Sql();

		$rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl", [
			':desurl'=>$desurl
		]);

		$this->setData($rows[0]);
	}

	public function getCategories(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories a 
		INNER JOIN tb_productscategories b ON (a.idcategory = b.idcategory)
		WHERE b.idproduct = :idproduct", [
			':idproduct'=>$this->getidproduct()
		]);
	}

	public function addProduct(Product $product){

		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);
	}

	public function removeProduct(Product $product){

		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE (idcategory = :idcategory AND idproduct = :idproduct)", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);
	}






    
}