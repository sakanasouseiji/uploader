//elementFixed
//画面上の要素に使用し、要素が画面最上部に移動した際にfixedに変えそのまま上部に固定する
//使用法
//利用したい要素には事前にcssで"fixed"と"static"でクラス指定をしておく
//条件に応じてこのjsでclassNameを切り替えることにより挙動を操作する
/*
div#content{			//動かない側の要素
	float:left;
	width:645px;
	margin:0px;
	padding:0px;
}
div#rap-right{			//目的の要素は別divでくるんでおく
	position:relative;
	right:0px;
	width:150px;
	float:right;
	margin-right:0px;
	margin:0px;
	padding:0px;
}
div#side-navi{			//通常時
	width:150px;
	top:0px;
	padding:0px;
	background-color:white;
}
div#side-navi.fixed{		//上部固定時
	position:fixed;
	top:0px;
}
div#side-navi.relative{		//フッターに押し上げられている時
	position:relative;
}

*/

//
//var hoge=new elementFixed;
//hoge.entry(element,underElement);
//element=自動で固定させたいエレメント
//underElementにはフッターなどその下にある重なって欲しくないエレメントを指定。現在のところ省略はできない、いづれ省略可能にする

function elementFixed(){

	//登録メソッド
	this.entry=function(element,underElement){
		
		if(typeof element=="string") element=document.getElementById(element);
		if(typeof underElement=="string") underElement=document.getElementById(underElement);
		
		var myself=this;
		//要素のoffsetTopをプロパティに格納
		this.offsetTop=element.offsetTop;
		console.log("start");
		console.log(this.offsetTop);
		//登録された要素はscrollイベントに紐付けられ		
		window.addEventListener("scroll",function(){
						myself.elementScrollSearch(element,underElement);
							},false);
	}

	//スクロール検知メソッド
	this.elementScrollSearch=function(element,underElement){
		var a=document.body.scrollTop;			//chrome
		var b=document.documentElement.scrollTop;	//firefox.ie
		var sc= a||b;					//
		console.log(sc);
		console.log(this.offsetTop);

		//staticとfixedの分かれ目
		if(sc>=this.offsetTop){
			element.className="fixed";
			element.style.top="";

			//fixedとrelativeの分かれ目

			//境目となるスクロール量
			var relativeChangeLine=sc+element.offsetHeight;
			//フッター上端
			var footer=underElement.offsetTop;

			//要素の下端がフッター上端を上回るときのみrelativeに
			if(relativeChangeLine>=footer){
				element.className="relative";

				//要素の下端
				var elementUnderLine=element.offsetHeight+element.offsetTop;
				
				element.style.top=(footer-elementUnderLine)+"px";
			}



		}else{
			element.className="static";	
		}
/*
		console.log("フッター"+footer);
		console.log("elementUnderLine"+elementUnderLine);
		if(elementUnderLine>footer){
			var bottom=elementUnderLine-footer;
			console.log("bottom:"+bottom);
			element.style.bottom=bottom+"px";
		}
*/
	}


}


