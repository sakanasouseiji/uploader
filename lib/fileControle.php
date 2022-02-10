<?php
//fileControleクラス
//fileControle
//


class fileControle{
	private $PDO;
	private $error;
	private $table;
	function __construct(){
		//接続
		require("./lib/dbConfig.php");
		try{
			$this->table=$table;
			$this->PDO=new PDO('mysql:host='.$host.';dbname='.$db,$user,$password);


			//テーブル捜索、なければ作る
			if(	!($stmt=$this->PDO->query("SHOW TABLES LIKE '".$table."'"))	){

				//フラグまたはメッセージ立てて戻る(query失敗)
				$this->error="show tables query error";
				$this->PDO=null;
				print $this->error."\r\n";
				exit();
			}
			$die=$stmt->fetch();
			if(	$die==null	){
				$stmt=null;

				//テーブル作成
				$queryBase="CREATE TABLE ".$table." (no int AUTO_INCREMENT PRIMARY KEY,hashName varchar(40),clientName varchar(255),mime varchar(40),comment varchar(50),downloadPassword varchar(255),size int(7),datetime datetime) ENGINE=innoDB";
				if(	!($stmt=$this->PDO->query($queryBase))	){
					//フラグまたはメッセージ立てて戻る(query失敗)
					$this->error="CREATE TABLE query error";

					print $this->error."\r\n";
					exit();
				}
			}else{
				$stmt=null;
			}





		}catch(PDOException $e){
			$this->error="connect error";
			print $this->error."";
			exit();
		}
	}
//	function __destruct(){
//		$this->mysqli->close();
//	}

	//getAllData-取得したデータをすべて配列で返す
	function getAllData(){
		$array=array();

		//テーブルがregistrationTableで作られたものか照合

		//カラム確認
		$p="SHOW COLUMNS FROM ".$this->table." WHERE FIELD IN ('no','hashName','clientName','mime','datetime')";
		if(	!($stmt=$this->PDO->query($p))	){
			//フラグまたはメッセージ立てて戻る(query失敗)
			$this->error="SHOW COLUMNS query error";
			$this->PDO=null;
			return false;
		}
		//カラムの個数確認
		$fetchAll=$stmt->fetchAll();
		$num=count($fetchAll);
		if(	!($num==5)	){
			//フラグまたはメッセージ立てて戻る(テーブルはあったがカラムの個数が足りない)
			//hogehoge
			$this->error="COLMNUS num error:".$num;
			$this->PDO=null;
			return false;
		}
		$stmt=null;


		//データ全取り出し(select)
		$string="SELECT * FROM ".$this->table.";";
		if(	!($stmt=$this->PDO->query($string))	){
			$this->error="SELECT query error:".$string;
			return false;
		}
		$fetchAll=$stmt->fetchAll();
		$num=count($fetchAll);

		/*result0は無視する
		if(	$num<=0	){
			$this->error="result 0 error";
			return false;
		}
		 */
		//print_r($fetchAll);
		return $fetchAll;
	}
	//delete
	//指定されたデータをdbとファイルそのものを削除する
	function delete($hashName,$file){
		/*
		//データの確認
		$result=$this->hashNameSearch($hashName);
		if($result==false){
			return false;
		}
		*/
		//db削除
		$string="DELETE FROM ".$this->table." where hashName='".$hashName."'";
		if(	!($stmt=$this->PDO->query($string))	){
			//フラグまたはメッセージ立てて戻る(query失敗)
			$this->error="db delete error".$string;
			$this->PDO=null;
			return false;
		}
		//file削除
		if(	!(unlink($file))	){
			$this->error="file delete error".$file;
			$this->PDO=null;
			return false;
		}
		return true;
	}
	//hashNameSearch-指定した値に等しいhashNameだけselectした返す()
	function hashNameSearch($searchName){
		$array=array();

		//テーブルがregistrationTableで作られたものか照合

		//カラム確認
		$p="SHOW COLUMNS FROM ".$this->table." WHERE FIELD IN ('no','hashName','clientName','mime','datetime')";
		if(	!($stmt=$this->PDO->query($p))	){
			//フラグまたはメッセージ立てて戻る(query失敗)
			$this->error="SHOW COLUMNS query error:".$p;
			$this->PDO=null;
			return false;
		}
		//カラムの個数確認
		$fetchAll=$stmt->fetchAll();
		$num=count($fetchAll);
		if(	!($num==5)	){
			//フラグまたはメッセージ立てて戻る(テーブルはあったがカラムの個数が足りない)
			//hogehoge
			$this->error="COLMNUS num error:".$num;
			$this->PDO=null;
			return false;
		}
		$fetchAll=null;
		$stmt=null;

		//データ取り出し(select)

/*
		//query版
		$string="SELECT * FROM ".$this->table." WHERE hashName = '".$searchName."' ";
		if(	!($result=$this->mysqli->query($string))	){
			$this->error="SELECT query error:".$string;
			return false;
		}

*/
		//prepare版
		$string="SELECT * FROM ".$this->table." WHERE hashName=?";
		if(	!($stmt=$this->PDO->prepare($string))	){
			$this->error="SELECT prepare error:".$string;
			$this->PDO=null;
			
			return false;
		}
		$stmt->bindValue(1,$searchName);
		if(	!($stmt->execute())	){
			$this->error="SELECT execute error:".$string;
			$this->PDO=null;
			
			return false;
		}
		$A=$stmt->fetchAll(PDO::FETCH_ASSOC);
		$num=count($A);
		if(	$num==0	){
			$this->error="result 0 error:".$string.":".$searchName;
			$this->PDO=null;
			
			return false;
		}
		if(	$num>1	){
			$this->error="many result error:".$string.":".$searchName;
			$this->PDO=null;
			
			return false;
		}

		//$this->PDO=null;
		
		return $A[0];
	}



	//エラーメッセージを取り出す($this->error)
	function getError(){

		return $this->error;
	}
}
?>
