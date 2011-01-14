//Affichage / masquage des fieldset
$(function() {
	$(".matiere-box input[type=checkbox]").change(function(){
		Fieldset = $(this).closest('fieldset');
		if(this.checked)
		{
			Fieldset.find('div').show('slow');
			Fieldset.css('border-color','threedface');
		}
		else
		{
			Fieldset.find('div').hide('slow');
			Fieldset.css('border-color','transparent');
		}
	});
	
	$(".matiere-box div").hide();
	$(".matiere-box input[type=checkbox]").change();
});

//Mise à jour des valeurs sélectionnables
$(function() {
	$(".matiere-box select").change(function(){
		if(this.id.substr(0,6) == 'start_')
		{
			Start = $(this);
			End = $('#' + this.id.replace('start', 'end'));
		}
		else
		{
			Start = $('#' + this.id.replace('end', 'start'));
			End = $(this);
		}
		
		StartVal = Start.val();
		EndVal = End.val();
		
		Start.find('option').each(function(){
			if(parseInt(this.value) < EndVal)
				this.disabled = "disabled";
			else
				this.removeAttribute('disabled');
		});
		
		End.find('option').each(function(){
			if(parseInt(this.value) > StartVal)
				this.disabled = "disabled";
			else
				this.removeAttribute('disabled');
		});
	});
	
	$(".matiere-box select").change();
});
