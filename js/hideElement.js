//hideElement.js
//hideElement.
//4つのメソッド
//登録
//	var hoge=new hideElement;
//	hoge.entry(Ypx,element);
//
//Ypxは反応するスクロール量、elementは隠したいエレメント
//スクロール量が規定に達すると画面上の隠したいエレメントをtopを操作することで上に持ち上げて画面外に隠してしまう。
//サンプルはhideElementSample.html
//問題点
//	1.操作量が15pxごとなので中途半端な大きさのエレメントだと全部隠れてくれない
//	2.再読み込みに対応していない
//	3.IEだと動かない(はず)、setTimeoutの引数渡し方法を修正すると治るはず
//	4.現状登録はできても解除はできない。removeEventListenerがオブジェクト登録の場合の解除が特殊になるため対応していない

//エレメントを隠す
//該当エレメントのtopとheightを調べ、一定間隔ごとにtopを減らしていきheightと同値になると終了

function hideElement(){
	this.hide=function (element){
		var myself=this;
		var top=element.offsetTop;
		var height=element.offsetHeight;
		if(0>=(top+height)){
			return;
		}
		setTimeout(
			function(element,top){
				element.style.top=top-15+"px";
				myself.hide(element);
			}
		,10,element,top);
	}

//エレメントを表示する
	this.show=function(element){
		var myself=this;
		var top=element.offsetTop;
		var height=element.offsetHeight;
		if(0<=top){
			return;
		}
		setTimeout(
			function(element,top){
				element.style.top=top+15+"px";
				myself.show(element);
			}
		,10,element,top);
	}
	//スクロール検知()
	this.senseEvent=function(Ypx,element){
		var a=document.body.scrollTop;
		var b=document.documentElement.scrollTop;
		c= a || b;
		//条件成立なら
		if(c>Ypx){
			this.hide(element);
		}else if(0>element.offsetTop){
			this.show(element);
		};
	}

//イベントをエレメントを登録する
	this.entry=function (Ypx,element){
		var myself=this;
		window.addEventListener("scroll",function(){	myself.senseEvent(Ypx,element);	},false);
		window.addEventListener("unload",function(){	myself.senseEvent(Ypx,element);	},false);
		this.o=element;
		this.Ypx=Ypx;
	}
/*
//イベントとプロパティを削除し、終了する
	this.close=function (){
		var myself=this;
		window.removeEventListener("scroll",function(){	myself.senseEvent(myself.Ypx,myself.o); },false);
		delete this.o;
		delete this.Ypx;
	}
*/		


}

