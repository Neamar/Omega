$(function() {
	$(".matiere-box input[type=checkbox]").change(function(){
		if(this.checked)
		{
			$(this).closest('div').find('div').show('slow');
		}
		else
		{
			$(this).closest('div').find('div').hide('slow');
		}
	});
	
	$(".matiere-box div").hide();
	$(".matiere-box input[type=checkbox]").change();
});

$(function() {
	/**
	 * Effectue la transition entre les coordonnées "slider" et les coordonnées "combobox".
	 * @param val la valeur à transformer
	 * @returns int la valeur transformée
	 */
	function translate(val)
	{
		return ClasseMax - val + ClasseMin;
	}
	
	$(".slider").each(function(){
		var slider = $(this);
		
		//Récupérer les select associés
		slider.data('start', $(slider.data('start')));
		slider.data('end', $(slider.data('end')));
		
		
		slider.data('start').click(function(){slider.hide('slow')});
		slider.data('end').click(function(){slider.hide('slow')});
		
		slider.slider({
			range: true,
			min: ClasseMin,
			max: ClasseMax,
			values: [
			         translate(slider.data('start').val()),
			         translate(slider.data('end').val())
	        ],
			slide: function( event, ui ) {
				$(this).data('start').val(translate(ui.values[0]));
				$(this).data('end').val(translate(ui.values[1]));
			}
		});
	});
});
