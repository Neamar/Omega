$(function()
{
	var InfosInitiales = $('#infos').val();
	
	$('#form_recapitulatif-ok').submit(function(e)
	{
		if($('#infos').val() != InfosInitiales)
		{
			if(!confirm('Attention ! Vous n\'avez pas encore enregistré vos modifications sur les infos. Êtes-vous sûr de vouloir continuer et de perdre les informations entrées ?'))
			{
				e.preventDefault();
				$('#infos').focus();
			}
		}
	});
});