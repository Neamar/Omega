$(function(){
	var InputPts = $('#ajout');
	var InputEur = $('#amount');
	var SpanEur = $('#points-euro');
	var Equivalence = $('#equivalence').val();
	
	function updateForm()
	{
		if(isNaN(parseInt(this.value)))
		{
			$('#ajout-points-paypal').hide();
			SpanEur.text('--');
			InputEur.val(0);
		}
		else
		{
			Euro = this.value / Equivalence;
			SpanEur.text(Euro);
			InputEur.val(Euro);
			$('#ajout-points-paypal').show();
		}
	}
	$('#ajout')
		.change(updateForm)
		.keyup(updateForm)
		.keydown(updateForm);
	
});