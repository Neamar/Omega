/**
 * Effet sur les listes
 */
$(function()
{
	$('.documentation-module-index li, .documentation-module-eleve li, .documentation-module-correcteur li').hover
	(
		function () {
		    $(this).animate({paddingLeft:'7px'}, 200);
		  },
		  function () {
			  $(this).animate({paddingLeft:'0px'}, 200);
		  }
	);
});