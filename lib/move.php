<?php
//moveクラス
//メソッド
//
//$move=new move;
//$move->gene($path,$himo);
//$path=ファイルの最終的な置き場所、
//$himo=ファイルに紐付けられるコメントの記述されるinputのname array("myFile"=>"myComment")
//
//move->gene()
//move->registrationFile()

//$_FILESからアップロードされたtmpファイルを調べ
//名前をファイルからmd5ハッシュで作成したものに直して、$pathで指定した場所に保存する
//同時にファイル情報をmysqlで保存する
//保存情報
//no		int		(auto_increment)
//hashName	varchar(40)
//clienthName	varchar(255)
//mime		varchar()
//comment	varcher(50)
//downloadPassword	varcher(255)
//size		int(7)
//datetime	datetime

class move{
	private $error=null;
	private $PDO;
	function __construct(){
	}// __construct end

//tableLock/tableUnlock
//テーブルまるごとのロック/アンロックを行う
//テーブルの存在確認はしない
//データベースの接続もしない
//RegistrationFileからの呼び出しのみ正常動作する
	function tableLock($table){
		$query="commit";
		if(	!($this->PDO->query($query))	){
			print $query;
			$this->error="commit lock error";
			return false;
		}
		$query="begin";
		if(	!($this->PDO->query($query))	){
			print $query;
			$this->error="begin lock error";
			return false;
		}
		$query="SELECT * FROM {$table} FOR UPDATE";

		$stmt=$this->PDO->query($query);
		if(	!($stmt)	){
			print $query;
			$this->error="SELECT lock error";
			return false;
		}
		return true;
	}
	function tableUnlock(){
		$query="commit";
		if(	!($this->PDO->query($query))	){
			$this->error="unlock error";
			print $query;
			return false;
		}
		return true;
	}


//RegistrationFile
//引数で与えられたファイルの情報をMysqlに登録する
//使い方・仕様
//データベース内にテーブルを探し、あればそれを使い、なければ作成する
//引数は配列で渡す
//
//

//テーブルに
	function registrationTable($serverName,$tmpFile,$mime,$comment,$downloadPassword){
		require("./lib/dbConfig.php");
		//接続
		try{
			$this->PDO=new PDO('mysql:host='.$host.';dbname='.$db,$user,$password);

			//テーブル捜索、なければ作る
			if(	!($stmt=$this->PDO->query("SHOW TABLES LIKE '".$table."'"))	){

				//フラグまたはメッセージ立てて戻る(query失敗)
				$this->error="show tables query error";
				$this->PDO=null;
				return false;
			}
			$die=$stmt->fetch();
			if(	$die==null	){
				$stmt=null;

				//テーブル作成
				$queryBase="CREATE TABLE ".$table." (no int AUTO_INCREMENT PRIMARY KEY,hashName varchar(40),clientName varchar(255),mime varchar(40),comment varchar(50),downloadPassword varchar(255),size int(7),datetime datetime) ENGINE=innoDB";
				if(	!($stmt=$this->PDO->query($queryBase))	){
					//フラグまたはメッセージ立てて戻る(query失敗)
					$this->error="CREATE TABLE query error";
					$this->PDO=null;
					return false;
				}
			}else{
				$stmt=null;
			}

			//テーブルがregistrationTableで作られたものか照合

			//カラム確認
			$p="SHOW COLUMNS FROM ".$table." WHERE FIELD IN ('no','hashName','clientName','mime','comment','downloadPassword','size','datetime')";
			if(	!($stmt=$this->PDO->query($p))	){
				//フラグまたはメッセージ立てて戻る(query失敗)
				$this->error="SHOW COLUMNS query error";
				$this->PDO=null;
				return false;
			}
			//カラムの個数確認
			$fetchAll=$stmt->fetchAll();
			$num=count($fetchAll);
			if(	!($num==8)	){
				//フラグまたはメッセージ立てて戻る(テーブルはあったがカラムの個数が足りない)
				//hogehoge
				$this->error="COLMNUS num error:".$num;
				$this->PDO=null;
				return false;
			}
			$stmt=null;

			//データを収める

			//テーブルロック
			if(	!($this->tableLock($table))	){
				print "ロック失敗";
				$this->PDO=null;
				return false;
			}

			if(	!($stmt=$this->PDO->prepare("INSERT INTO ".$table." (hashName,clientName,mime,comment,downloadPassword,size,datetime) VALUE(?,?,?,?,?,?,now());"))	){
				//フラグまたはメッセージ立てて戻る(insert prepare失敗)
				$this->error="INSERT DATA prepare error";
				$this->PDO=null;
				return false;
			}
			$downloadPassword=password_hash($downloadPassword,PASSWORD_DEFAULT);
			$stmt->bindValue(1,$serverName);
			$stmt->bindValue(2,$tmpFile["name"]);
			$stmt->bindValue(3,$mime);
			$stmt->bindValue(4,$comment);
			$stmt->bindValue(5,$downloadPassword);
			$stmt->bindValue(6,$tmpFile["size"]);
			$stmt->execute();
			if(	$stmt==false	){
				//フラグまたはメッセージ立てて戻る(データの追加失敗)
				$this->error="INSERT DATA error:".$tmpFile["name"];
				$stmt->num;
				return false;
			}
			//テーブルアンロック
			if(	!($this->tableUnlock())	){
				print "アンロック失敗";
				$this->PDO=null;
				return false;
			}


			$this->PDO=null;
			return true;
		}//
		catch(PDOException $e){
			//フラグまたはメッセージ立てて戻る(接続エラー)
			$this->error="PDO connect error";
			return false;
		}	
	}//registrationTable end


//gene
///$_FILESの内容からファイルを指定したパスに移動させるメソッドファイル名はmd5で生成し、重複がある場合
//ファイル名末尾に数字を付け足す(例：	hoge.arc hoge1.arc	)拡張子はarc(仮)
//$pathで指定した場所に置く
//ファイルをtmpから
	function gene($path,$himo="",$himo2=""){
		setlocale(LC_ALL,"ja_JP.UTF-8");
		$tmpFiles=$_FILES;
		foreach($tmpFiles as $key => $tmpFile){

			//nullチェック
			if($tmpFile["error"]==4){
				$this->error="file not found error";
				return false;
			}

			//comment取得
			//コメント紐付け変数($himo)が無かったとしてもエラーは出さない
			if(	isset($himo[$key])	){

				$comment=$_POST[$himo[$key]];
			}
			//downloadPassword取得
			//コメント紐付け変数($himo2)が無かったとしてもエラーは出さない
			if(	isset($himo2[$key])	){

				$downloadPassword=$_POST[$himo2[$key]];
			}


			//hashName作製
			$hashName=md5_file($tmpFile["tmp_name"]);

			//重複回避	

			$plus="";
//			$fileNameParts=pathinfo($path.$hashName.".arc");
//			$searchName=$fileNameParts["filename"];
			while(	file_exists($path.$hashName.$plus.".arc")	){
				$plus++;
			}
			$serverName=$hashName.$plus;

			//mime(linuxシェル版)
			$fileName = $tmpFile['tmp_name'];

//			$mime = shell_exec('file -bi '.escapeshellcmd($fileName));
//			$mime = trim($mime);
//			$mime = preg_replace("/ [^ ]*/", "", $mime);

			//mime(finfo版)
			$finfo=new finfo(FILEINFO_MIME_TYPE);
			$mime=$finfo->file($fileName);
//			$finfo->close();
//			finfo_close($finfo);

			//RegistrationTable
			if(	!($this->RegistrationTable($serverName,$tmpFile,$mime,$comment,$downloadPassword))	){
				return false;
			}

			//移動
			if(	!(is_uploaded_file($tmpFile["tmp_name"]))	){
				$this->error="tmp_file none error";
				return false;
			}
			if(	!(move_uploaded_file($tmpFile["tmp_name"],$path.$serverName.".arc"))	){
				$this->error="move_uploaded error";
				return false;
			}
			return true;
		}

	}

	//getError
	function getError(){
		return $this->error;
	}

}
?>
