<?php
//単純なログファイルの作成
//機能1	ログファイルがなければ作る
//
//機能2 ログファイルがあればそれに与えられた文字列を追加してログファイルを大きくする
//
//コンストラクト
	
class logMake{
	function __construct ($fileName,$text=""){
		//なければ初期メッセージと共に作る
		if(	!($logFile=@file_get_contents($fileName))	){
			$firstText="##webBeacon.log start##\r\n";
		}else{
			$firstText="";
		}
		$text =$firstText.date('y/m/d H:i:s').":".$text."\r\n";
		return file_put_contents($fileName,$text,FILE_APPEND | LOCK_EX );
	}
	

}
?>
