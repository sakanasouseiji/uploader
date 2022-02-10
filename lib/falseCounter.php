<?php
//falseCounter
//目的
//パスワード入力等の状況に対して、tableを作成、記録を取り、複数回の失敗に対してチェックを行う
//
//falseが一定時間内に規定された回数に達した場合falseを返すclass
//point	mysqlでの時間と、phpでの時間の取り扱い
//		形式の違い
//		やり方2種類
//		① 	php上ではtime(全部秒)で扱い、mysql上では数字で記録する。
//		② 	php上でもmysql上でもdatetimeの形式で扱い、計算もそのままで行う
//
//付随ファイル
//falseCounterConfig.php
//テーブル名、データベース情報等、
//使いかた
//引数はない。
//$falseCounter=new falseCounter();
//$falseCounter->weite();		dbに失敗記録を行う
//$falseCounter->check();		① blackListを参照し、falseCounterConfigに記載されている内容に引っかかる場合trueを返しす。
//					② countTableを参照し、falseCounterConfigに記載されている内容に引っかかる場合、blackListに記録をし、trueを返す。
//						

//クラスブラックリスト

class blackList{
	public $checkQuery;
	public $weiteQuery;
	private $PDO;
	private $error;
	private $blackListTable;	//ブラックリストテーブル
	private $lockTime;		//どのくらいの時間ロックするか(単位秒)
	public function __construct(){
		//コンストラクトの役目
		//	*blackListTableの作成
		//	カラム	no(int auto_increment PRIMARY KEY)
		//		ip(string)
		//		time(dateTime)				*blackListに追加された時間
		require("./lib/falseCounterConfig.php");
		$this->lockTime=$lockTime;
		//接続
		try{
			$this->blackListTable=$blackListTable;
			$this->PDO=new PDO('mysql:host='.$host.';dbname='.$db,$user,$password);


			//テーブル捜索、なければ作る
			if(	!($stmt=$this->PDO->query("SHOW TABLES LIKE '".$blackListTable."'"))	){

				//フラグまたはメッセージ立てて戻る(query失敗)
				$this->error="show tables query error";
				$this->PDO=null;
				return false;
			}
			$die=$stmt->fetch();
			if(	$die==null	){
				$stmt=null;

				//テーブル作成
				$queryBase="CREATE TABLE ".$blackListTable." (no int AUTO_INCREMENT PRIMARY KEY,ip varchar(16),time datetime) ENGINE=innoDB";
				$this->query=$queryBase;
				if(	!($stmt=$this->PDO->query($queryBase))	){
					//フラグまたはメッセージ立てて戻る(query失敗)
					$this->error="CREATE TABLE query error";
					$this->PDO=null;
					return false;
				}
			}else{
				$stmt=null;
			}
		}catch(PDOException $e){
			$this->error="connect error";
			return;
		}
	return;
	}
	public function getError(){
		if(isset($this->error)){
			return $this->error;
		}
		return null;
	}
	//blackList->write
	public function write(){
		//write
		//	現在の時刻をテーブルに記録する
		$tz=ini_get("date.timezone");		//timezoneの取得
		$dtz=new dateTimeZone($tz);		//dateTimeクラス利用のためのタイムゾーン設定
		$dateTime=new dateTime("now",$dtz);	//現在のサーバー時間で
		$now=$dateTime->format('Y-m-d H:i:s');	//現在時間を代入

		$REMOTEADDR=$_SERVER['REMOTE_ADDR'];	//閲覧者のアドレス取得

		$string="INSERT INTO ".$this->blackListTable." (ip,time) values('".$REMOTEADDR."','".$now."')";
		if(	!($stmt=$this->PDO->query($string))	){
			//フラグまたはメッセージ立てて戻る(query失敗)
			//$this->error="INSERT INTO query error(時間記録に失敗)<br>".$string;
			$this->error="INSERT INTO query error(blackListTable時間記録に失敗)";
			$this->PDO=null;
			return false;
		}
		$this->writeQuery=$string;
		return true;	
	}
	//blackList->check()
	public function check(){
		//check
		//	①	ブラックリスを参照し、チェックを行う記録
		//		ブラックリストをipで調べ、所定の時間以内に記録があればtrueを返す
		//	②	　現在時刻から所定の時間($lockTime)さかのぼってチェックをする。
		//		そうでない場合falseを返す
		
		//	現在の時刻を取得

		$tz=ini_get("date.timezone");		//timezoneの取得
		$dtz=new dateTimeZone($tz);		//dateTimeクラス利用のためのタイムゾーン設定
		$dateTime=new dateTime("now",$dtz);	//現在のサーバー時間で
		$now=$dateTime->format('Y-m-d H:i:s');	//現在時間を代入
		$before=time()-$this->lockTime;		//LockTime分さかのぼった時間
		$dateTime->setTimestamp($before);	//$lockTime分さかのぼった時間で時間設定
		$before=$dateTime->format('Y-m-d H:i:s');	//さかのぼり時間代入

		
		$REMOTEADDR=$_SERVER['REMOTE_ADDR'];	//閲覧者のアドレス取得

		//query作製
		$string="SELECT count(*) FROM ".$this->blackListTable." WHERE time BETWEEN '".$before."' AND '".$now."'";
		if(	!($stmt=$this->PDO->query($string))	){
			//フラグまたはメッセージ立てて戻る(query失敗)
			//$this->error="SELECT before-now(範囲時間内blackListのdb記録selectに失敗)<br>".$string."<br>";
			$this->error="SELECT before-now(blackListTable selectに失敗)";
			$this->PDO=null;
			return false;
		}
		$result=$stmt->fetchColumn();
		if($result<1){
			//falseでリターン
			return false;
		}
		$this->checkQuery=$string;
		return true;
	}
}

//クラスfalseCounter
class falseCounter{
	public $checkQuery;
	public $weiteQuery;
	private $PDO;
	private $error;
	private $countTable;		//失敗回数カウントテーブル
	private $blackListTable;	//ブラックリストテーブル
	private $blackList;		//クラスブラックリストはここに入れる
	private $lockTime;		//どのくらいの時間ロックするか(単位秒)
	private $limit;			//何回まで許容するか
	private $checkTime;		//さかのぼってチェックする時間(単位秒)
	public function __construct(){
		//コンストラクトの役目
		//	*falseCounterTableの作成			*falseを出した記録
		//	カラム	no(int auto_increment PRIMARY KEY)	
		//		ip(string)				*
		//		time(dateTime)				*
		require("./lib/falseCounterConfig.php");
		$this->limit=$limit;
		$this->checkTime=$checkTime;
		//接続
		try{
			$this->countTable=$countTable;
			$this->PDO=new PDO('mysql:host='.$host.';dbname='.$db,$user,$password);


			//テーブル捜索、なければ作る
			if(	!($stmt=$this->PDO->query("SHOW TABLES LIKE '".$countTable."'"))	){

				//フラグまたはメッセージ立てて戻る(query失敗)
				$this->error="show tables query error";
				$this->PDO=null;
				return false;
			}
			$die=$stmt->fetch();
			if(	$die==null	){
				$stmt=null;

				//テーブル作成
				$queryBase="CREATE TABLE ".$countTable." (no int AUTO_INCREMENT PRIMARY KEY,ip varchar(16),time datetime) ENGINE=innoDB";
				$this->query=$queryBase;
				if(	!($stmt=$this->PDO->query($queryBase))	){
					//フラグまたはメッセージ立てて戻る(query失敗)
					$this->error="CREATE TABLE query error";
					$this->PDO=null;
					return false;
				}
			}else{
				$stmt=null;
			}
		}catch(PDOException $e){
			$this->error="connect error";
			return;
		}
	return;
	}
	public function getError(){
		if(isset($this->error)){
			return $this->error;
		}
		return null;
	}
	public function write(){
		//write
		//	現在の時刻をテーブルに記録する
		$tz=ini_get("date.timezone");		//timezoneの取得
		$dtz=new dateTimeZone($tz);		//dateTimeクラス利用のためのタイムゾーン設定
		$dateTime=new dateTime("now",$dtz);	//現在のサーバー時間で
		$now=$dateTime->format('Y-m-d H:i:s');	//現在時間を代入

		$REMOTEADDR=$_SERVER['REMOTE_ADDR'];	//閲覧者のアドレス取得

		$string="INSERT INTO ".$this->countTable." (ip,time) values('".$REMOTEADDR."','".$now."')";
		if(	!($stmt=$this->PDO->query($string))	){
			//フラグまたはメッセージ立てて戻る(query失敗)
			//$this->error="INSERT INTO query error(時間記録に失敗)<br>".$string;
			$this->error="INSERT INTO query error(時間記録に失敗)";
			$this->PDO=null;
			return false;
		}
		$this->writeQuery=$string;
		return true;	
	}
	//falseCounter->check
	public function check(){
		//check


		//	①	現在時刻から所定の時間さかのぼってチェックをする。
		//	②	所定の回数記録がなければfalseを返す
		//		記録があればばブラックリストに記録をしtrueを返す
		//	③	ブラックリストを参照し、チェックを行う記録があればfalseを返す

		//
		//	現在時刻から所定の時間さかのぼってチェック
		//	現在の時刻を取得
		$tz=ini_get("date.timezone");		//timezoneの取得
		$dtz=new dateTimeZone($tz);		//dateTimeクラス利用のためのタイムゾーン設定
		$dateTime=new dateTime("now",$dtz);	//現在のサーバー時間で
		$now=$dateTime->format('Y-m-d H:i:s');	//現在時間を代入
		$before=time()-$this->checkTime;
		$dateTime->setTimestamp($before);	//$checkTime分さかのぼった時間で時間設定
		$before=$dateTime->format('Y-m-d H:i:s');	//さかのぼり時間代入

		
		$REMOTEADDR=$_SERVER['REMOTE_ADDR'];	//閲覧者のアドレス取得

		//query作製
		$string="SELECT count(*) FROM ".$this->countTable." WHERE time BETWEEN '".$before."' AND '".$now."'";
		if(	!($stmt=$this->PDO->query($string))	){
			//フラグまたはメッセージ立てて戻る(query失敗)
			//$this->error="SELECT before-now(範囲時間内db記録selectに失敗)<br>".$string."<br>";
			$this->error="SELECT before-now(範囲時間内db記録selectに失敗)";
			$this->PDO=null;
			return false;
		}
		$result=$stmt->fetchColumn();

		$this->blackList=new blackList();

		//falseCounterTableの取得結果と回数との比較
		if($this->limit<=$result){
			//回数が限界超え

			//ブラックリストへの記録
			if(	!($this->blackList->write())	){
				//getErrorに何かある場合はそれを$this->errorにそのまま突っ込む
				$this->error=$this->blackList->getError();
				return false;
			}
			return true;
		}

		//②	ブラックリストをチェック→
		if(	!($this->blackList->check())	){
			//getErrorに何かある場合はそれを$this->errorにそのまま突っ込む
			$this->error=$this->blackList->getError();
			return false;
		}	
		$this->checkQuery=$string;
		return true;
	}
}


?>
