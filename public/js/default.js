/**
 * Script javascript par défaut.
 * Charge les bonnes librairies si nécessaires, effectue les différentes actions.
 * 
 * @author eDevoir <webmaster@edevoir.com>
 */

//Gestion des dates dynamiques
$(function()
{
	var Times = $('time.date');
	if(Times.length > 0)
	{
		$.getScript('/public/js/jquery-prettydate.js', function(){
			Times.prettyDate();
		});
	}
});

//Gestion des tableaux Ajax
$(function()
{
	var Tables = $('table.ajax-table');
	
	if(Tables.length > 0)
	{
		Tables.each(function(){
			Table = $(this);
		});
	}
});