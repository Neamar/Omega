$(function() {
	$( ".slider" ).each(function(){
		var slider = $(this);
		
		//Récupérer les select associés
		slider.data('start', $(slider.data('start')));
		slider.data('end', $(slider.data('end')));
		
		var disableFunction = function()
		{
			values = slider.slider("option", "values");
			values[0] = translate(values[0]);
			values[1] = translate(values[1]);
			
			slider.data('start').find('option').each(function(){
				if(parseInt(this.value) < values[1])
					this.disabled = "disabled";
				else
					this.removeAttribute('disabled');
			});
			
			slider.data('end').find('option').each(function(){
				if(parseInt(this.value) >= values[0])
					this.disabled = "disabled";
				else
					this.removeAttribute('disabled');
			})
		};
		
		var changeFunction = function(){
			NValues = [ translate(slider.data('start').val()),  translate(slider.data('end').val())];
			if(slider.slider("option","values") != NValues)
			{
				slider.slider("option", "values", [ translate(slider.data('start').val()),  translate(slider.data('end').val())]);
				disableFunction();
			}
		};
		
		slider.data('start').change(changeFunction);
		slider.data('end').change(changeFunction);
		
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
				disableFunction();
			}
		});
	});
});

/**
 * Effectue la transition entre les coordonnées "slider" et les coordonnées "combobox".
 * @param val la valeur à transformer
 * @returns int la valeur transformée
 */
function translate(val)
{
	return ClasseMax - val + ClasseMin;
}