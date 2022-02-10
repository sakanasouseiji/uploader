<?php
//header('Content-Type: image/gif');
//GIFに見せかける
//header('Expires:Fri, 10 May 2015 00:00:00 GMT');
//header('Cache-Control:private, no-cache, no-cache=Set-Cookie, must-revalidate');
//header('Pragma: no-cache');
//キャッシュされないようにヘッダを設定
//echo base64_decode("R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw%3D%3D");
//最後に1x1の透過画像を返す（ここではbase64エンコードを使っている）i
require_once("./logMake.php");



if (isset($_COOKIE['uploaderCount'])){
	$count = $_COOKIE['uploaderCount'] + 1;
}else{
	$count = 1;
}
setcookie('uploaderCount', $count, time() + 60*60*24*365, "/");
//とりあえずCookieを使って訪問回数を記録する

//他にもCookieで任意のkey, valueを保存させておくことができる
//PEAR::Mail読み込み
/*
require_once("Mail.php");
require_once("Mail/mime.php");
 */
//今はcomposerになったので
require_once(__DIR__."/vendor/autoload.php");
//mail送信
$REMOTE_HOST=gethostbyaddr($_SERVER["REMOTE_ADDR"]);
/*if(isset($_SERVER['REMOTE_HOST'])){
	$REMOTE_HOST=$_SERVER['REMOTE_HOST'];
}else{
	$REMOTE_HOST="";
}
*/
$REMOTE_ADDR=$_SERVER['REMOTE_ADDR'];
$SCRIPT_NAME=$_SERVER['SCRIPT_NAME'];
$SERVER_NAME=$_SERVER['SERVER_NAME'];
$USER_AGENT=$_SERVER['HTTP_USER_AGENT'];

//macアドレスの取得
//実行不可の場合は空欄
$MAC_ADDR=@exec('arp '.$REMOTE_ADDR) or $MAC_ADDR="";

//$to="sakanasouseiji_0126@yahoo.co.jp";
$to="sakanasouseiji@gmail.com";
$subject="uploaderアクセス確認しました\n";
$message=$SERVER_NAME.",".$SCRIPT_NAME.",".$REMOTE_HOST.",".$REMOTE_ADDR.",".$USER_AGENT.",count".$count;



//PEAR::Mailでの送信
$show="PEAR::Mail";

//ymobileメール利用のパターン
/*
	$from="sakanasouseiji_0126@yahoo.ne.jp";
	$para=array(
		"host"=>"ymobilepop.mail.yahoo.ne.jp",
		"port"=>995,
		"auth"=>true,
		"username"=>$from,
		"password"=>"oomori200"
	);
*/
//レンタルサーバー付属のメール利用パターン
$from="sabakan@sabakan.info";
$para=array(
	"host"=>"tls://sv52.star.ne.jp",
	"port"=>465,
	"auth"=>true,
	"username"=>$from,
	"password"=>"oomori200"
);
/*
//ドメインキング付属のメール利用パターン

$from="test@sakanasouseiji.com";
$para=array(
	"host"=>"mail.sakanasouseiji.com",
	"port"=>587,
	"auth"=>true,
	"username"=>$from,
	"password"=>"Iug6#o23"
);
 */		

/*
//gmail利用のパターン
$from="sakanasouseiji@gmail.com";
$para=array(
	"host"=>"tls://smtp.gmail.com",
	"port"=>465,
	"auth"=>true,
	"username"=>$from,
	"password"=>"scottS55"
	,"debug"=>false
);
*/
//Mailオブジェクト作製		


/*
//$countが10以下の場合のみメールを送信する
if (	isset($count)	&&	$count<10	){

	$show="PEAR::Mail";
	$mailObject=Mail::Factory("smtp",$para);
	if( is_object($mailObject) ) {
		if( !(PEAR::isError($mailObject)) ){
	//			print "Mail::Factory完了<br>";

			//$headerにto,Cc,Bcc,From,Subjectが入る
			$header=array(	"To"=>$to,
					"Cc"=>"",
					"Bcc"=>"",
					"From"=>$from,
					"Subject"=>$subject
			);

			$result=$mailObject->send($to,$header,$message);
	//			print "mail::send完了<br>";
			if(PEAR::isError($result)){
	//				print $result->getMessage();
			}

		}else{
	//			print "mail::Factory失敗<br>";
	//			print $mailObject->getMessage();
		}
	}else{
	//		print "mailObject生成失敗<br>";
	}
}
*/
//ログ作成
$fileName="webBeacon.log";
$log=new logMake($fileName,$message.$MAC_ADDR);




?>
