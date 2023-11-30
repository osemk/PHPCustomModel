<? 
/*
 * This file is a part of the php-custom-model project.
 *
 * Copyright (c) 2023-present osemk - Onur Erginer <onurerginer@gmail.com>
 *
 * This file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

	
	class CustomModel extends Veritabani {
		public $veri =[];
		private $cachedveri = [];
		private $tbl;
		private $columns=[];
		private $insertable =false;
		private $updateable =false;
		private $deleteable =false;
		private $hasIDs = false;
		function __construct($tbl_name,$id = 0){
			parent::__construct();
			$this->tbl = $tbl_name;
			if($this->tbl_kontrol($id)){
				$this->sorgula($id);
			}
		}
		
		private function sorgula($a){
			$t= $this->sorgu("Select * from ".$this->tbl." where ".$this->createWhereClause($a)." Limit 1");
			if($t){
				if($this->kac_adet()>0){
					while($a=$this->getir($t)){
						foreach($this->columns as $i=>$v){
							$this->cachedveri[$i] = $a[$i];
							$this->veri[$i] = $a[$i];
						}
					}
					$this->deleteable = true;
				}
				else{
					$this->insertable = true;
				}
			}
		}
		
		private function tbl_kontrol($b){
			$sorgu= $this->sorgu("DESCRIBE ".$this->tbl."");
			if($sorgu){
				while($a=$this->getir($sorgu)){
					$this->columns [$a["Field"]]= $a;
					$this->cachedveri[$a["Field"]] = $this->demonstrateDefValue($a["Type"]);
					$this->veri[$a["Field"]] = $this->demonstrateDefValue($a["Type"]);
				}
				$this->detectIdentifier($b);
				return true;
			}
			else {
				throw new Exception("The table that is '".$this->tbl."' doesn't exist");
			}
			if(empty($this->columns)){
				throw new Exception("The table that is '".$this->tbl."' doesn't exist");
				return false;
			}
		}
		
		private function detectIdentifier($b){
			$found = false;
			foreach($this->columns as $i => $k){
				if($k['Key']=="PRI" && $k['Extra'] == "auto_increment" && !$found){
					$this->columns[$i]['identifier'] = true;
					$this->columns[$i]['2ndidentifier'] = false;
					$found = true;
				}
				elseif($k['Key']=="PRI" && !$found){
					$this->columns[$i]['identifier'] = true;
					$this->columns[$i]['2ndidentifier'] = false;
					$found = true;
				}
				elseif($k['Key']=="PRI" && $found){
					$this->columns[$i]['identifier'] = false;
					$this->columns[$i]['2ndidentifier'] = true;
					$found = true;
				}
				elseif($k['Key']=="MUL" && !$found){
					$this->columns[$i]['identifier'] = true;
					$this->columns[$i]['2ndidentifier'] = false;
					$found = true;
				}
				elseif($k['Key']=="MUL" && $found){
					$this->columns[$i]['identifier'] = false;
					$this->columns[$i]['2ndidentifier'] = true;
					$found = true;
				}
				elseif($k['Key']=="UNI" && !$found){
					$this->columns[$i]['identifier'] = true;
					$this->columns[$i]['2ndidentifier'] = false;
					$found = true;
				}
				elseif($k['Key']=="UNI" && $found){
					$this->columns[$i]['identifier'] = false;
					$this->columns[$i]['2ndidentifier'] = true;
					$found = true;
				}
				else{
					$this->columns[$i]['identifier'] = false;
					$this->columns[$i]['2ndidentifier'] = false;
				}
			}
			if(!$found && is_array($b)){
				foreach($b as $a=>$c){
					if(array_key_exists($a,$this->columns) && !$found){
						$this->columns[$a]['identifier'] =true;
						$found = true;
						}else if(array_key_exists($a,$this->columns) && $found){
						$this->columns[$a]['2ndidentifier'] =true;
					}
				}
			}
			if($found)
			return $this->hasIDs = true;
			else
			return $this->hasIDs = false;
		}
		
		public function sql_column_type_is_string($a){
			if(strstr($a,"varchar")!=FALSE || strstr($a,"text")!=FALSE)
			return true;
			else
			return false;
		}
		
		public function handlenull(){
			foreach($this->columns as $t=>$v){
				if(is_null($this->veri[$t]) || empty($this->veri[$t]) && (!$this->sql_column_type_is_string($v['Type']) && $this->veri[$t]=="") ){
					$this->veri[$t]=0;
				}
			}
		}
		
		private function createWhereClause($id){
			if(is_array($id)){
				$where = "1 = 1";
				foreach($id as $k=>$v){
					if(array_key_exists($k,$this->columns) && is_int($v)){
						$where .= " AND `".$k."` = ".$v;
					}
					elseif(array_key_exists($k,$this->columns) && !is_int($v)){
						$where .= " AND `".$k."` = '".$v."'";
					}
					else if($k == "orderby"){
						if(is_array($v) && array_key_exists($v[0],$this->columns))
						{
							$where .= " ORDER BY `".$v[0]. "` ".$v[1];
						}
					}
				}
				return $where;
			}
			else{
				$id = intval($id);
				if($this->hasIDs){
					foreach($this->columns as $i=>$j){
						if($j['identifier'])
						return $i." = $id";
					}
				}
				else{
					if(array_key_exists("id",$this->columns) || array_key_exists("Id",$this->columns) || array_key_exists("ID",$this->columns) || array_key_exists("iD",$this->columns)){
						return "ID = $id";
					}
				}
			}
		}
		
		private function demonstrateDefValue($a){
			if(stristr($a,"int") !==FALSE){
				return 0;
			}
			elseif(stristr($a,"text") !==FALSE || stristr($a,"char") !==FALSE ){
				return "";
			}
			elseif(stristr($a,"float") !==FALSE || stristr($a,"double") !==FALSE ){
				return 0.0;
			}
			else return 0;
		}
		
		public function __get($varName){
			if (!array_key_exists($varName,$this->veri)){
				throw new Exception('This attribute not here');
			}
			else return $this->veri[$varName];
		} 
		
		public function __set($varName,$value){
			if($this->veri[$varName]!=$value && !$this->insertable){
				$this->updateable = true;
				
			}
			$this->veri[$varName] = $value;
		}
		
		private function isDateTime($c){
			if(preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $c, $matches) 
			&& checkdate($matches[2], $matches[3], $matches[1]))
			return true;
			else 
			return false;
		}
		
		private function isUnixTimestamp($c){
			if(is_int($c) && ($c <= PHP_INT_MAX)
			&& ($c >= ~PHP_INT_MAX))
			return true;
			else
			return false;
		}
		
		private function isDate($c){
			if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$c))
			return true;
			else
			return false;
		}
		
		private function createInsertValues($a,$b,$c,$d){
			if(stristr($a,"text") || stristr($a,"char"))
			return "'".$c."',";
			else if(stristr($a,"datetime")){
				if($this->isDateTime($c)){
					return "'".$c."',";
				}
				else if($this->isUnixTimestamp($c)){
					return "'".date('Y-m-d H:i:s', $c)."',";
				}
				else 
				throw new Exception( $b." variable type is wrong must be a ".$a. " or unix timestamp");
			}
			else if(stristr($a,"date")){
				if($this->isDate($c)){
					return "'".$c."',";
				}
				else if($this->isUnixTimestamp($c)){
					return "'".date('Y-m-d', $c)."',";
				}
				else 
				throw new Exception( $b." variable type is wrong must be a ".$a. " or unix timestamp");
			}
			else{
				if((is_null($c) || empty($c)) && $d =="auto_increment")
				return 'null,';
				else
				return $c.',';
			}
		}
		
		public function insert($showerror=false){
			if($this->insertable){
				$sorgu = "INSERT INTO ".$this->tbl." values (";
				foreach($this->columns as $i => $j){
					$sorgu .= $this->createInsertValues($j['Type'],$j['Field'],$this->veri[$i],$j['Extra']);
				}
				$sorgu = mb_substr($sorgu,0,-1).')';
				if($this->sorgu($sorgu)){
					$this->insertable = false;
					$this->deleteable = true;
					foreach($this->columns as $i=>$k){
						if($k['identifier']){
							$this->veri[$i] = $this->son_kayit_id();
						}
					}
					$this->cachedveri = $this->veri;
					return true;
				}
				else{
					throw new Exception($this->hata(). " ".($showerror?'<br>Sorgu: '.$sorgu:''));
				}
			}
			else{
				throw new Exception("The object isn't insertable");
			}
		}
		
		public function save(){
			
			$this->checkUpdateable();
			if($this->updateable){
				return $this->update(true);
				}else{
				if($this->insertable){
					return $this->insert(true);
				}
				else return false;
			}
		}
		
		private function checkUpdateable(){
			if(!$this->updateable && !$this->insertable){
				if($this->veri !== $this->cachedveri){
					$this->updateable=true;
				}
			}
		}
		
		private function createUpdateValues(){
			$sorgu = "";
			foreach($this->veri as $i => $k){
				if($this->veri[$i] !== $this->cachedveri[$i]){
					if(stristr($this->columns[$i]['Type'],"text") || stristr($this->columns[$i]['Type'],"char"))
					$sorgu .= "`".$i."`='".$k."',";
					else if(stristr($this->columns[$i]['Type'],"datetime")){
						if($this->isDateTime($k)){
							$sorgu .= "`".$i. "`='".$k."',";
						}
						else if($this->isUnixTimestamp($k)){
							$sorgu .= "`".$i. "`='".date('Y-m-d H:i:s', $k)."',";
						}
						else 
						throw new Exception( $i." variable type is wrong must be a ".$this->columns[$i]['Type']. " or unix timestamp");
					}
					else if(stristr($this->columns[$i]['Type'],"date")){
						if($this->isDate($k)){
							$sorgu .= "`".$i."`='".$k."',";
						}
						else if($this->isUnixTimestamp($k)){
							$sorgu .= "`".$i."`='".date('Y-m-d', $k)."',";
						}
						else 
						throw new Exception( $i." variable type is wrong must be a ".$this->columns[$i]['Type']. " or unix timestamp");
					}
					else
					$sorgu .= "`".$i.'`='. $k.',';
				}
			}
			$sorgu = mb_substr($sorgu,0,-1)." ";
			return $sorgu;
		}
		
		private function detectStringValue($i,$k){
			if(stristr($this->columns[$i]['Type'],"text") || stristr($this->columns[$i]['Type'],"char"))
			$sorgu = "'".$k."'";
			else if(stristr($this->columns[$i]['Type'],"datetime")){
				if($this->isDateTime($k)){
					$sorgu = "'".$k."'";
				}
				else if($this->isUnixTimestamp($k)){
					$sorgu = "'".date('Y-m-d H:i:s', $k)."'";
				}
				else 
				throw new Exception( $i." variable type is wrong must be a ".$this->columns[$i]['Type']. " or unix timestamp");
			}
			else if(stristr($this->columns[$i]['Type'],"date")){
				if($this->isDate($k)){
					$sorgu = "'".$k."'";
				}
				else if($this->isUnixTimestamp($k)){
					$sorgu = "'".date('Y-m-d', $k)."'";
				}
				else 
				throw new Exception( $i." variable type is wrong must be a ".$this->columns[$i]['Type']. " or unix timestamp");
			}
			else
			$sorgu = $k;
			return $sorgu;
		}
		
		private function createIdentifierForUpdating(){
			$sorgu= "WHERE 1=1";
			if($this->hasIDs){
				foreach($this->columns as $i =>$k){
					if($k['identifier'])
					$sorgu .= ' AND `'.$i.'`='.$this->veri[$i];
				}
			}
			else{
				foreach($this->veri as $k => $v){
					if($b === $this->cachedveri[$k]){
						$sorgu .=" AND `".$k.'`='.$this->detectStringValue($k,$this->veri[$k]);
					}
				}
			}
			return $sorgu;
		}
		
		public function hasRecord(){
			if($this->deleteable)
			return true;
			else
			return false;
		}
		
		public function update($hatasorgugoster=false){
			$this->checkUpdateable();
			if($this->updateable){
				$sorgu = "UPDATE ".$this->tbl." set ";
				$sorgu .= $this->createUpdateValues();
				$sorgu .= $this->createIdentifierForUpdating();
				if($this->sorgu($sorgu)){
					$this->updateable = false;
					return true;
				}
				else
				throw new Exception("Error while updating Mysql Error: ".$this->hata(). ($hatasorgugoster?"<br> Sorgu: ".$sorgu:""));
			}
			else{
				if(!$hatasorgugoster) // bu hatasorgu göster hem sorgu göstermek için hem de updateable değilse bir şey yapma
				throw new Exception("Nothing changed ,the object isn't updateable");
				else
					return true;
			}
		}
		
		public function delete(){
			if($this->deleteable){
				$this->cachedveri = $this->veri;
				$sorgu = "Delete from ".$this->tbl." ". $this->createIdentifierForUpdating();
				if($this->sorgu($sorgu)){
					$this->destroy();
					return true;
					}else{
					throw new Exception("Error while deleting");
				}				
			}else
			throw new Exception("The object isn't deleteable");
		}
		
		private function destroy(){
			$this->veri =[];
			$this->cachedveri = [];
			$this->tbl;
			$this->columns=[];
			$this->insertable =false;
			$this->updateable =false;
			$this->deleteable =false;
			$this->hasIDs = false;
		}
		
		public function setProperties($tbl, $id=0){
			self::__construct($tbl, $id);
		}
	}																								