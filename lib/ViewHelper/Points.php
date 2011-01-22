<?php 
/**
 * Points.php - 22 janv. 2011
 *
 * Offrir des primitives de haut niveau pour le contrôleur de points.
 *
 * PHP Version 5
 *
 * @category  ViewHelper
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

/**
 * Renvoie le formulaire permettant de retirer des points.
 * 
 * @param int $Max la somme max à retirer (à titre indicatif)
 */
function ViewHelper_Points_formRetrait($Max)
{
	if(!function_exists("ViewHelper_Form_pointsLabel"))
	{
		include OO2FS::viewHelperPath('Form');
	}
	
	$Points = ViewHelper_Form_pointsLabelBr(
		'retrait',
		'Nombre de points à retirer',
		$Max,
		array(
			'value' => 0,
		)
	);
	
	$Type = ViewHelper_Form_radio(
		'type',
		array(
			'rib' => 'Paiement par virement bancaire. ' . ViewHelper_Form_ribLabel('rib', 'RIB') ,
			'paypal' => 'Paiement par paypal. ' . ViewHelper_Form_inputLabel(
				'paypal',
				'Compte',
				array(
					'placeholder' => 'nom@fai.fr',
					'type' => 'email',
					'maxlength' => '29'
				)
			)
		),
		'rib'
	);
	
	$Submit = ViewHelper_Form_submit('retrait-points', "Retirer des points");

	$H = '<form id="form_retrait-points" method="post" action="">
	<p>Vous pouvez retirer jusqu\'à ' . $Max . ' points (' . ViewHelper_Points_euros($Max) . ')<br />
	Attention ! Le délai minimum entre deux retraits est d\'une semaine.</p>
' . $Points . '
' . $Type . '
' . $Submit . '
</form>';

	return $H;
}

/**
 * Convertit une valeur en points en euros
 * 
 * @param int $Points les points à transformer
 */
function ViewHelper_Points_euros($Points)
{
	return number_format($Points / EQUIVALENCE_POINT, 2, ',', ' ') . '&nbsp;€';
}