<?php
$includedFiles=get_included_files();
if(	array_shift($includedFiles)===__FILE__	){
	exit("直接アクセス禁止");
}
//if(	$_SERVER["HTTP_REFERER"]==	)
//フォームチェック
//フォームチェッククラスとフォームデータクラス
//formDataクラス
//	指定されたフォームのデータを保持する
//		各フォームデータは$this->"フォーム要素名"
//		フォーム要素名一覧は$this->nameList
//
//formCheckクラス
//	mailとpasswordとfileについてチェックを行う(暫定)
//	素のhtmlにはなるべく影響を与えない「控えめなphp」を意識する
//	constructでチェックを行い、結果をクラスのプロパティとして保持する
//	引数は多次元配列で渡す
//	引数の1番目がチェックしたいform要素のname,2番目がチェック形式
//		array(	array("mailAddressInput","mail") , array("passInput","password") )
//
//チェック形式	と	各エラー返り値
//mail:
//	必須入力チェック(優先)						$this->elementName."NotFound"
//	正規表現におけるメール形式のチェック				$this->$elementName."MailSeiki"="hogehoge"
//	htmlspecialcharsを利用しての使用前後の内容変化チェック		$this->$elementName."Htmlspecialchars"="hogehoge"
//file:
//	ファイルチェック
//		$_FILESをチェックし、内容に応じてエラーを返す
//eisuu:			
//	英数チェック
//		正規表現を利用し、英数以外の場合エラーを出す

//password:
//	必須入力チェック(優先)								$this->elementName."NotFound"
//	正規表現によるパスワード文字種類のチェック(アルファベット,数字,各種記号)	$this->$elementName."PasswordSeiki"="hoge"
//	文字数チェック(8文字以上)							$this->elementName."Mojisu"="hoge"
//返り値
//$formCheck->start=true;				formCheck起動確認用
//$formCheck->error=false;				(trueは無い)
//$formCheck->"チェックしたいform要素"=false;		(同上)
//$formCheck->"チェックしたいform要素"."errorMes"=メッセージ
//	
//reuire_once("formCheck.php");
//hoge=new formCheck($array());
//$a=hoge->mail();

class formData
{
	//プロパティ一覧
	private $nameList;
	function __construct(){
	//$_POSTをプロパティにする
		if( !empty( $_POST ) ){	
			$fdata=$_POST;
			foreach($fdata as $name => $value){
				$this->$name=$value;
			}
//			$this->nameList=$fdata;
		}
		//files
		$keyArray=array_keys($_FILES);
		foreach($keyArray as $name){
			$this->$name=$_FILES[$name];
		}

	}
}

class formCheck
{
	public $formData;
	function __construct($parameter){
		$this->formData = new formData;       
		if( empty( $_POST ) ){
			return false;
		}
			$this->start=true;
			foreach($parameter as $element){
				$elementName=$element[0];
				$elementCheckFormat=$element[1];
				if( $elementCheckFormat	){
//					if( $this->formData->$elementName ){
						
//					}
					$this->$elementCheckFormat($elementName);
				}
			}	
		if( empty($_FILES) ){
			return false;
		}
		return true;
	}

	//mailチェック
	//$this->mail()
	private function mail($elementName){
		$string=$this->formData->$elementName;
		//foundチェック
		if( $string==NULL ){
			$this->error=false;
			$this->$elementName=false;
			$el=$elementName."errorMes";
			$this->$el="value not found error!";
			return;
		}

		//正規表現チェック
		if( preg_match("/^[^@]+@([-a-z0-9]+\.)+[a-z]{2,}$/",$string)===0 ){
			$this->error=false;
			$this->$elementName=false;
			$el=$elementName."errorMes";
			$this->$el="mail match error!";
		}
		//htmlspecialcharsチェック
		if( htmlspecialchars($string,ENT_QUOTES,"UTF-8")!=$string ){
			$this->error=false;
			$this->$elementName=false;
			$el=$elementName."errorMes";
			$this->$el="htmlspecialchars match error!";
		}
	}

	//passwordチェック
	//$this->password
	private function password($elementName){
		$string=$this->formData->$elementName;
		//foundチェック
		if( $string==NULL ){
			$this->error=false;
			$this->$elementName=false;
			$el=$elementName."errorMes";
			$this->$el="value not found error!";
			return;
		}


		//正規表現チェック(半角英数記号)
		if(	!(	preg_match("/[\@-\~]/",$string)	)	){
			$this->error=false;
			$this->$elementName=false;
			$el=$elementName."errorMer";
			$this->$el="password match error!";
	
		}
		//文字数チェック(8文字以上)
		if(	mb_strlen($string)<8	){
			$this->error=false;
			$this->$elementName=false;
			$el=$elementName."errorMes";
			$this->$el="mojisuu match error!";
	
		}
	}
	//eisuuチェック
	//$this->password
	private function eisuu($elementName){
		$string=$this->formData->$elementName;
		//foundチェック(未入力可なのでnullの場合そのまま返る)
		if( $string==NULL ){
			return;
		}


		//正規表現チェック(半角英数記号)
		if(	!(	preg_match("/^[a-zA-Z0-9]+$/",$string)	)	){
			$this->error=false;
			$this->$elementName=false;
			$el=$elementName."errorMes";
			$this->$el="eisuu match error!";
	
		}
	}


	//fileチェック
	//$this->file
	private function file($elementName){
		//foundチェック
		$string=$this->formData->$elementName;

		if(	$string==NULL	){
			$this->error=false;
			$this->$elementName=false;
			$el=$elementName."errorMes";
			$this->$el="value not found error!";
			return;
		}
		//ここから$_FILESのチェック
		if(	!isset($string["error"])	||	!is_int($string["error"])	){
			$this->error=false;
			$this->$elementName=false;	
			$el=$elementName."errorMes";
			$this->$el="input array error!";
			return;
		}
		switch(	$string["error"]	){
			case 0:
				break;
			case 1:
				$this->error=false;
				$this->$elementName=false;
				$el=$elementName."errorMes";
				$this->$el="upload_max_filesize Over!";
				break;
			case 2:
				$this->error=false;
				$this->$elementName=false;
				$el=$elementName."errorMes";
				$this->$el="MAX_FILESIZE over!";
				break;
			case 3:
				$this->error=false;
				$this->$elementName=false;
				$el=$elementName."errorMes";
				$this->$el="seaquense falt!";
				break;
			case 4:
				$this->error=false;
				$this->$elementName=false;
				$el=$elementName."errorMes";
				$this->$el="file not choice";
				break;
			case 6:
				$this->error=false;
				$this->$elementName=false;
				$el=$elementName."errorMes";
				$this->$el="not tmp folder";
				break;
			case 7:
				$this->error=false;
				$this->$elementName=false;
				$el=$elementName."errorMes";
				$this->$el="disk wright error";
				break;
			default:
				$this->error=false;
				$this->$elementName=false;
				$el=$elementName."errorMes";
				$this->$el="other error";
				break;
		}
		return;
	}
}

?>
