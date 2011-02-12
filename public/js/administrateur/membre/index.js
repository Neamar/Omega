/**
 * Gestion de l'autocomplétion.
 * 
 */
$(function()
{
	function search(data, callback)
	{
		$.getJSON('/administrateur/membre/_search/' + data.term, callback);
	}
	
	
	$.widget("custom.catcomplete", $.ui.autocomplete, {
		_renderMenu: function( ul, items ) {
			var self = this,
				currentCategory = "";
			$.each( items, function( index, item ) {
				if ( item.category != currentCategory ) {
					ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
					currentCategory = item.category;
				}
				self._renderItem( ul, item );
			});
		}
	});
	
	$("#recherche").catcomplete({
		delay: 1,
		source: search
	});
});