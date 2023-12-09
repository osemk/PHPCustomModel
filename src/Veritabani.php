<?
	/*
 * This file is a part of the php-custom-model project.
 *
 * Copyright (c) 2023-present osemk - Onur Erginer <onurerginer@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */
 
	namespace CustomModel;
class Veritabani{
var $baglan;
var $sorgu;
	function __construct(){
		$this->baglanti();
	// mysqli_close($this->baglan);
	}
	private function baglanti(){
            $this->baglan=mysqli_connect(VT_HOST, VT_KULLANICI, VT_SIFRE, VT_ADI);
			mysqli_select_db($this->baglan, VT_ADI) or die ("no database");  
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		
	$this->sorgu("SET NAMES 'utf8'",true);
	$this->sorgu("SET CHARACTER SET utf8",true);
	$this->sorgu("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'",true);
	}
	public function sorgu($a,$b=false){
		if(!$b){
			$this->sorgu =  mysqli_query($this->baglan, $a);
		return $this->sorgu;
		}else{
		   return mysqli_query($this->baglan, $a);
		}
	}
	public function getir($a=false){
		if(isset($a))
		return mysqli_fetch_array($a);
	else
		return mysqli_fetch_array($this->sorgu);
	}
	public function nesnegetir($a){
		if(isset($this->sorgu))
			return mysqli_fetch_assoc($this->sorgu);
		else
			return mysqli_fetch_assoc($a);
	}
	public function kac_adet($a=false){
		if(isset($this->sorgu))
			return mysqli_num_rows($this->sorgu);
		else
			return mysqli_num_rows($a);
	}
	public function son_kayit_id(){
		return mysqli_insert_id($this->baglan);
	}
	public function sonucu_sakla(){
		return mysqli_store_result($this->baglan);
	}
	public function hata(){
		return mysqli_error($this->baglan);
	}
	public function __get($var){
		if($var == "kac_adet")
			return $this->kac_adet();
	}
}