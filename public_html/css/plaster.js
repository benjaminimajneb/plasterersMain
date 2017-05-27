$().ready(function() { // once page is loaded, load a random backdrop
	var i = Math.random();
	var bgNum=parseInt(i*7)+1;
	$('#website').css({'background-image':'url(img/back'+bgNum+'.jpg)'});
});