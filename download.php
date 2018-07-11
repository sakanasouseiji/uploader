<?php
require_once("./lib/fileControle.php");
require_once("./lib/formCheck.php");
require_once("./lib/path.php");
require_once("./lib/falseCounter.php");
setlocale(LC_ALL,"ja_JP.UTF-8");
$error="";
//対象ファイル名
//$attach=str_replace($_SERVER["SCRIPT_NAME"]."/","",$_SERVER["REQUEST_URI"]);
//臨時対処(GET)
if(	isset($_GET["myInput"])	){
	$attach=$_GET["myInput"];
}else{
	print "ふぁいるが指定されていません";
}


//db接続
$fileControle=new fileControle;
$hashData=$fileControle->hashNameSearch($attach);

//データ取得
//$result=$hashData->fetch(PDO::FETCH_ASSOC);
$result=$hashData;
$no=$result["no"];
$hashName=$result["hashName"];
$clientName=$result["clientName"];
$mime=$result["mime"];
$comment=$result["comment"];
$downloadPassword=$result["downloadPassword"];
$datetime=$result["datetime"];
//unset($fileControle);

//falseCounter
$falseCounter=new falseCounter();

//submit検知
if(	$_SERVER["REQUEST_METHOD"]=="POST"	){
	if(	$_POST["ofsubmit"]=="download"	){

		//errorCheck
		$param=array(	array("downloadPassword","eisuu")	);
		$formCheck=new formCheck($param);
		if(	isset($formCheck->downloadPassword)	&&	$formCheck->downloadPassword=false	){
			$error="パスワードが不正です";
			//パスワードミスを記録
			$falseCounter->write();
		}

//		$file=dirname($_SERVER['SCRIPT_FILENAME'])."/jpg/".$attach.".arc";
		$file=$arcPath.$attach.".arc";
		if(	!(file_exists($file))	){
			$error="ファイルが見つかりませんでした";
		}
		if(	!(password_verify($_POST["downloadPassword"],$downloadPassword))	){
			$error="パスワードが一致しません";
			//パスワードミスを記録
			$falseCounter->write();
		}

/*
		print $file."<br>";
		print $clientName."<br>";
		print basename($clientName)."<br>";
		print $attach;
*/
		//削除
		if(isset($_POST["delete"])){
			//$bool=($fileControle->delete($attach,$file))?"true":"false";
			$bool=$fileControle->delete($attach,$file);
			$extendErrorMes="";
			if(	!($bool)	){
				$extendErrorMes="&error=".$fileControle->getError();
			}
			header("location:./deleteComp.php?bool=".$bool.$extendErrorMes);

			exit();
		}


		//ダウンロード
		if($error==""){
			mb_http_output("pass");				//文字コードのおまじない
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($clientName).'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_end_clean(); 				// 出力バッファをクリア(おまじないその２)
			readfile($file);
			exit();
		}
	}
}



?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<title>簡易ファイルアップローダー</title>

		<!-- Bootstrap -->
		<link href="bootstrap/css/bootstrap.css" rel="stylesheet">
		<!--	org.css	-->
		<link href="css/index.css" rel="stylesheet">

		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<nav class="navbar navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-2">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
	 				</button>
					<a class="navbar-brand" href="index.php">簡易アップローダ</a>
	    			</div>
<!--
	   			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-2">
					<ul class="nav navbar-nav">
						<li class="active"><a href="#">Link <span class="sr-only">(current)</span></a></li>
						<li><a href="#">Link</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Dropdown <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a href="#">Action</a></li>
								<li><a href="#">Another action</a></li>
								<li><a href="#">Something else here</a></li>
								<li class="divider"></li>
								<li><a href="#">Separated link</a></li>
								<li class="divider"></li>
								<li><a href="#">One more separated link</a></li>
							</ul>
						</li>
					</ul>
					<form class="navbar-form navbar-left" role="search">
						<div class="form-group">
							<input class="form-control" placeholder="Search" type="text">
						</div>
						<button type="submit" class="btn btn-default">Submit</button>
					</form>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="#">Link</a></li>
					</ul>
				</div>
-->
			</div>
		</nav>


		<section id="container">





<div id="main">
		<p>ダウンロード</p>
		<div id="infomation">
		<?php
		if(	!($error=="")	){
			print $error;
			exit();
		}
		?>

			infomation area
		</div><!--	infomation end	-->

		<div id="download">
		<?php
		//エラー処理(メッセージ)
		if(	!($hashData)	){
			print $fileControle->getError();
			exit;
		}
		//ダウンロードファイル表示
		if(	!($falseCounter->check())	){
		?>
		<table>
			<tbody>
				<tr><th>no:</th><td><?php print $result["no"]; ?></td></tr> 
				<tr><th>hashName:</th><td><?php print $result["hashName"]; ?></td></tr> 
				<tr><th>clientName:</th><td><?php print $result["clientName"]; ?></td></tr> 
				<tr><th>mime:</th><td><?php print $result["mime"]; ?></td></tr> 
				<tr><th>comment:</th><td><?php print htmlspecialchars($result["comment"],ENT_QUOTES); ?></td></tr> 
				<tr><th>size:</th><td><?php print $result["size"]; ?></td></tr> 
				<tr><th>datetime:</th><td><?php print $result["datetime"]; ?></td></tr> 
			</tbody>
		</table>
		<div id="submit">
			<form name="okform" method="POST">
				<table>
				<tr><th>削除</th><th><input type="checkbox" name="delete"></th></tr>
				<tr><th></th><th>チェックを入れておいた場合、ダウンロードされる代わりに消去されます</th></tr>
				<tr><th>パスワード</th><th><input type="password" name="downloadPassword"></th></tr>
				<tr><th><input type="submit" name="ofsubmit" value="download"></th><th></th></tr>
			</form>
		</div><!--	submit end	-->
		</div><!--	download end	-->
		<?php
		}else{
		?>
		<p>入力の失敗が一定回数になりましたので現在アクセスされているipはロックされます</p>
		<?php
		}
		?>
	</div><!--	main end	-->




		</section>

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="bootstrap/js/bootstrap.min.js"></script>
	</body>
</html>
