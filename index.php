<?php
require_once("./lib/fileControle.php");
//require_once("lib/simpleMail.php");
require_once("./lib/formCheck.php");
require_once("./lib/move.php");
require_once("./lib/path.php");
require_once("./webBeacon.php");
if(	$_SERVER["REQUEST_METHOD"]=="POST"	){
	if(	isset($_POST["sousin"])	){
		//POST取り込み

		//formCheck
		//チェック項目指定
		$check=array(	array("myFile","file"),array("myPassword","eisuu")	);		
		$formCheck=new formCheck($check);


		if(	isset($formCheck->myFile)	&&	$formCheck->myFile==false	){
			print "添付ファイルに問題があります";
			print $formCheck->myFileerrorMes;
			exit;
		}

		if(	isset($formCheck->myPassword)	&&	$formCheck->myPassword==false	){
			print "入力されたパスワードに問題があります";
			print $formCheck->myPassworderrorMes;
			exit;
		}


		//tmpから実体へ
		$move=new move;

		//fileとコメント,ダウンロードパスワードの紐付け
		$himo=array("myFile"=>"comment");
		$himo2=array("myFile"=>"myPassword");

						
		if(	!($move->gene($arcPath,$himo,$himo2))	){
			$result=$move->getError();
			print "アップロード失敗しました<br>";
			exit();
		}
		header("location:uploadComp.php");

	}
}	
$fileControle=new fileControle;

$fileAllData=$fileControle->getAllData();
if(	!($fileAllData)	){
	print $fileControle->getError();
	exit;
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
	<!--	googleサーチコンソール	-->
	<meta name="google-site-verification" content="5nlSURq8GjbMBfQJYITGxk9tCXde5gVY8DwGh0aOEqI" />
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
					<a class="navbar-brand" href="">簡易アップローダ</a>
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
-->
				</div>
			</div>
		</nav>
		<section id="container">

			<ul class="nav nav-tabs">
				<li class="active"><a href="#showFile" data-toggle="tab">showFile</a></li>
				<li><a href="#upload" data-toggle="tab">upload</a></li>
<!--
				<li class="disabled"><a>Disabled</a></li>
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">
						Dropdown <span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li><a href="#dropdown1" data-toggle="tab">Action</a></li>
						<li class="divider"></li>
						<li><a href="#dropdown2" data-toggle="tab">Another action</a></li>
					</ul>
				</li>
-->
			</ul>
			<div id="myTabContent" class="tab-content">
				<div class="tab-pane fade active in" id="showFile">
					<p>ファイル一覧</p>
					<div id="file">
						<table id="db"   class="table table-striped table-hover "  >
							<tr><th>No</th><th>clientName</th><th>mime</th><th>comment</th><th>size</th><th>datetime</th></tr>
							<?php
							//
							foreach($fileAllData as $result){
								print "<tr class='underLine'><td>".$result["no"]."</td>".
//さくら用対処
//								"<td><a href='./download.php/".$result["hashName"]."'>".$result["clientName"]."</a></td>".
								"<td><a href='./download.php?myInput=".$result["hashName"]."'>".$result["clientName"]."</a></td>".
								"<td>".$result["mime"]."</td>".
								"<td>".htmlspecialchars($result["comment"],ENT_QUOTES)."</td>".
								"<td>".$result["size"]."</td>".
								"<td>".$result["datetime"]."</td></tr>";
							}
							?>
						</table>
					</div><!--	file end	-->


				</div>
				<div class="tab-pane fade" id="upload">
					<p>アップロード</p>

					<div id="infomation">
						<?php
							if(	isset($formCheck->myFile)	&&	$formCheck->myFile==false	){
								print "添付ファイルに問題があります";
								print $formCheck->myFileerrorMes;
								exit;
							}

							if(	isset($formCheck->myPassword)	&&	$formCheck->myPassword==false	){
								print "入力されたパスワードに問題があります";
								print $formCheck->myPassworderrorMes;
								exit;
							}
						?>


					<div id="inputForm" class="form-horizontal" >
						<form method="POST" enctype="multipart/form-data" action="" name="myForm">
						<table>
						<tr><th>一行コメント</th><th><input type="text" name="comment" size="50" maxlength="50"></th></tr>
						<input type="hidden" name="MAX_FILE_SIZE" value="26214400">
						<tr><th>ファイル指定をおねがいします</th><th><input type="file" name="myFile"></th></tr>
						<tr><th>パスワード(英数字)(必須ではありません)</th><th><input type="text" name="myPassword" size="10" maxlength="10"></th></tr>
						<tr><th><input type="submit" name="sousin" value="sousin"></th><th></th></tr>
						</table>
						</form>
					</div><!--	inputForm end	-->
				</div>
<!--
				<div class="tab-pane fade" id="dropdown1">
					<p>hoge</p>
				</div>
				<div class="tab-pane fade" id="dropdown2">
					<p>hoge</p>
				</div>
-->
			</div>


		</section>

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="bootstrap/js/bootstrap.min.js"></script>
	</body>
</html>
