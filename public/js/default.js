/**
 * Script javascript par défaut.
 * Charge les bonnes librairies si nécessaires, effectue les différentes actions.
 * 
 * @author eDevoir <webmaster@edevoir.com>
 */

function dynamicDate()
{
	var Times = $('time.date');
	if(Times.length > 0)
	{
		$.getScript('/public/js/jquery-prettydate.js', function(){
			Times.prettyDate();
		});
	}
}
//Gestion des dates dynamiques
$(dynamicDate);

//Gestion des tableaux Ajax
$(function()
{
	var Tables = $('table.ajax-table');
	
	if(Tables.length > 0)
	{
		Tables.each(function(){
			var Table = $(this);
			function update()
			{
				$.ajax({
					url: Table.data('source'),
					success: function(data){
						data = jQuery.parseJSON(data);
						Lignes = '';
						for(var i = 0 ; i < data.length ; i++)
						{
							Lignes += '<tr>';
							
							for(var j = 0 ; j < data[i].length ; j++)
							{
								Lignes += '<td>' + data[i][j] + '</td>';
							}
							Lignes += "</tr>\n";
						}
						
						Table.find('tbody').html(Lignes);
					}
				});
			}
			
			update();
			setInterval(update, 60000);
		});
	}
});