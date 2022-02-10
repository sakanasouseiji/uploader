//hideElement2
//特定の要素を隠し、スイッチ要素をクリックすることによって出したり引っこめたりする

//全部忘れても使える使い方
//hoge=new hideElement2;
//hoge.entry(element,switcElement);
//element=隠したいエレメント
//cssで事前にdisplay:block状態のclass="show"と
//display:none状態のclass="hidden"を用意しておく
//html上ではshow状態を基本で記述しておく

//hideElement2に登録することによってswitchElementにイベントを追加し
//elementは直後にhiddenに書き換え、見せないようにする
//elementのheightはhoge.heightに記録される
//イベントが起こると



function hideElement2(){

	//メソッドその1
	//.entry
	this.entry(element,switchElement){
		this.element=element;
		this.element=switchEment;
		this.height=this.element.style.height;
		this.display=this.element.style.display;
		if( this.display=="block" && this.class)
	}


}
