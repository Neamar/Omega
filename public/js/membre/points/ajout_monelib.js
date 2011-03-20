$(function()
{
	//$('#accordion').accordion();
	var TOC = '<ul>';
	$('h2[id]').each(function(){TOC += '<li><a href="#' + this.id + '">' + $(this).html() + '</a></li>';});
	TOC += '</ul>';
	$('#toc').html(TOC);
});