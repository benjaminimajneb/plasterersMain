
function toggleFormat(format){
	var on = format;
	var off = (on=='draught')? 'smallpack' : 'draught';
	$('tr[data-format='+on+']').show();
	$('tr[data-format='+off+']').hide();
}	

function initialCollapse() {
	$('tr [data-format=smallpack]').hide();
	console.log($('tr [data-format=smallpack]'));
}