<?php 
/**
 * form.php - 26 oct. 2010
 *
 * Offrir des primitives de haut niveau pour la gestion des éléments de formulaire HTML.
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
 * Génère un formulaire basique avec un simple bouton submit (action = la page actuelle)
 * 
 * @param string $form_id l'identifiant du formulaire. Form_ sera automatiquement ajouté devant.
 * @param string $name le nom du bouton
 * @param unknown_type $value la valeur du bouton
 */
function ViewHelper_form($form_id, $name, $value)
{
	return '
<form method="post" action="" id="form_' . $form_id . '">
' . ViewHelper_Form_submit($name, $value) . '
</form>';
}

/**
 * Génère un input avec les arguments spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_input($name, array $args = array())
{
	if(!isset($args['name']))
	{
		$args['name'] = $name;
	}
	if(!isset($args['id']))
	{
		$args['id'] = $name;
	}
	if(!isset($args['type']))
	{
		$args['type'] = 'text';
	}
	if(isset($_POST[$name]) && $args['type']!='password')
	{
		$args['value'] = $_POST[$name];
	}
	

	return '<input ' . ViewHelper_Form_args($args) . '/>';
}

/**
 * Génère un textarea avec les arguments spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_textarea($name, array $args = array())
{
	if(!isset($args['name']))
	{
		$args['name'] = $name;
	}
	if(!isset($args['id']))
	{
		$args['id'] = $name;
	}
	if(!isset($args['cols']))
	{
		$args['cols'] = 30;
	}
	if(!isset($args['rows']))
	{
		$args['rows'] = 5;
	}
	if(isset($_POST[$name]))
	{
		$args['value'] = $_POST[$name];
	}

	$Value = (isset($args['value']))?$args['value']:'';
	unset($args['value']);

	return '<textarea ' . ViewHelper_Form_args($args) . '>' . $Value . '</textarea>';
}

/**
 * Génère un bouton submit avec les arguments spécifiés.
 * 
 * @param string $name Nom / id du bouton
 * @param string $value le contenu du bouton
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_submit($name, $value)
{
	$args = array('type' => 'submit','value'=>$value);
	if($name!=null)
	{
		$args['name'] = $args['id'] = $name;
	}

	return '<input ' . ViewHelper_Form_args($args) . '/>';
}

/**
 * Génère un input pouvant prendre un RIB en paramètre.
 * 
 * @param string $name Nom / id du composant, qui servira de préfixe.
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_rib($name)
{
	$H = ViewHelper_Form_input(
		$name . '-banque',
		array(
			'placeholder' => 'BBBBB',
			'maxlength' => 5,
			'size' => 5,
			//'type' => 'numeric',
			'min' => '11111'
		)
	);
	
	$H .= '-' . ViewHelper_Form_input(
		$name . '-guichet',
		array(
			'placeholder' => 'GGGGG',
			'maxlength' => 5,
			'size' => 5,
			//'type' => 'numeric'
		)
	);
	
	$H .= '-' . ViewHelper_Form_input(
		$name . '-compte',
		array(
			'placeholder' => 'CCCCCCCCCCC',
			'maxlength' => 11,
			'size' => 11
		)
	);
	
	$H .= '-' . ViewHelper_Form_input(
		$name . '-cle',
		array(
			'placeholder' => 'RR',
			'maxlength' => 2,
			'size' => 2,
			//'type' => 'numeric',
		)
	);
	
	return $H;
}

/**
 * Génère un input de type numérique gérant les points.
 * 
 * @param string $name Nom / id du composant
 * @param int $max la valeur maximum
 * @param array $args paramètres additionnels.
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_points($name, $max = MAX_SOMME, $args = array())
{
	$args['type'] = 'number';
	$args['min'] = 0;
	$args['value'] = 0;
	$args['max'] = $max;
	$args['step'] = 25;
	
	return ViewHelper_Form_input($name, $args);
}

/**
 * Génère un select avec les arguments et valeurs spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param array $values Un tableau associatif.
 * @param string selected la valeur sélectionnée par défaut
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_select($name, array $values, $selected=null, array $args=array())
{
	if(!isset($args['name']))
	{
		$args['name'] = $name;
	}
	if(!isset($args['id']))
	{
		$args['id'] = $name;
	}
	if(isset($_POST[$name]))
	{
		$selected = $_POST[$name];
	}
	
	$Return = '<select ' . ViewHelper_Form_args($args)  . ">\n";
	foreach($values as $value=>$caption)
	{
		$Return .= '	<option value="' . $value . '"' . ($value==$selected?' selected="selected"':'') . '>' . $caption . "</option>\n";
	}
	$Return .="</select>\n";
	
	return $Return;
}

/**
 * Génère un radio avec les arguments et valeurs spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param array $values Un tableau associatif : la valeur, l'étiquette.
 * @param string selected la valeur sélectionnée par défaut
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_radio($name, array $values, $selected=null, $id_prefix='')
{
	$Return = '';
	foreach($values as $value=>$caption)
	{
		//Ne pas utiliser VH_Form_label, qui ajoute ":" à la fin ce qui n'est pas nécessaire ici.
		$Return .= '<input type="radio" name="' . $name . '" value="' . $value . '" id="' . $id_prefix . $value . '"' . ($value===$selected?' checked="checked"':'') . ' /><label for="' . $id_prefix . $value . '">' . $caption . "</label><br />\n";
	}
	
	return $Return;
}

/**
 * Génère un checkbox avec les arguments et valeurs spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param string $label le label du checkbox
 * @param bool $selected true pour le cocher
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_checkbox($name, $label, $selected=false, $args=array())
{
	$args['type'] = 'checkbox';
	
	if(!isset($args['name']))
	{
		$args['name'] = $name;
	}
	if(!isset($args['id']))
	{
		$args['id'] = $name;
	}
	if($selected || (isset($_POST[$name]) && $_POST[$name]=='on'))
	{
		$args['checked'] = 'checked';
	}
	
	return  '<input ' . ViewHelper_Form_args($args) . ' /><label for="' . $args['name'] . '">' . $label . "</label><br />\n";
}


/**
 * Génère une liste checkbox avec les arguments et valeurs spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param array $values Un tableau associatif.
 * @param string|array selected la valeur (les valeurs) sélectionnée(s) par défaut
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_checkboxs($name, array $values, $selected=null)
{
	if(!is_array($selected))
	{
		$selected = array($selected);
	}
	
	$Return = '';
	foreach($values as $value=>$caption)
	{
		$Return .= '<input type="checkbox" name="' . $name . '[]" value="' . $value . '" id="' . $value . '"' . (in_array($value, $selected)?' checked="checked"':'') . ' /><label for="' . $value . '">' . $caption . "</label><br />\n";
	}
	
	return $Return;
}

/**
 * Génère un label pour l'élément spécifié
 * 
 * @param string $name Nom / id du composant
 * @param string $label auquel sera automatiquement ajouté " : "
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_label($name, $label)
{
	return '<label for="' . $name . '">' . $label . '&nbsp;: </label>';
}

/**
 * Génère un input et son label avec les arguments spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param string $label
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_inputLabel($name, $label, array $args=array())
{
	return ViewHelper_Form_label($name, $label) . ViewHelper_Form_input($name, $args);
}

/**
 * Génère un input-rib et son label avec les arguments spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param string $label
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_ribLabel($name, $label)
{
	return ViewHelper_Form_label($name . '-banque', $label) . ViewHelper_Form_rib($name);
}

/**
 * Génère un input-rib et son label avec les arguments spécifiés. Ajoute un retour à la ligne
 * 
 * @param string $name Nom / id du composant
 * @param string $label
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_ribLabelBr($name, $label)
{
	return ViewHelper_Form_ribLabel($name, $label) . "<br />\n";
}



/**
 * Génère un composant points et son label avec les arguments spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param string $label
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_pointsLabel($name, $label, $max = MAX_SOMME, array $args=array())
{
	return ViewHelper_Form_label($name, $label) . ViewHelper_Form_points($name, $max, $args);
}

/**
 * Génère un textarea et son label avec les arguments spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param string $label
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_textareaLabel($name, $label, array $args=array())
{
	return ViewHelper_Form_label($name, $label) . "<br />\n" . ViewHelper_Form_textarea($name, $args);
}

/**
 * Génère un input et son label avec les arguments spécifiés. Ajoute un retour à la ligne.
 * 
 * @param string $name Nom / id du composant
 * @param string $label
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_inputLabelBr($name, $label, array $args=array())
{
	return ViewHelper_Form_inputLabel($name, $label, $args) . "<br />\n";
}

/**
 * Génère un composant points et son label avec les arguments spécifiés. Ajoute un retour à la ligne.
 * 
 * @param string $name Nom / id du composant
 * @param string $label
 * @param int $max la valeur maximale
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_pointsLabelBr($name, $label, $max = MAX_SOMME, array $args=array())
{
	return ViewHelper_Form_pointsLabel($name, $label, $max, $args) . "<br />\n";
}

/**
 * Génère un textarea et son label avec les arguments spécifiés. Ajoute un retour à la ligne.
 * 
 * @param string $name Nom / id du composant
 * @param string $label
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_textareaLabelBr($name, $label, array $args=array())
{
	return ViewHelper_Form_textareaLabel($name, $label, $args) . "<br />\n";
}

/**
 * Génère un select et son label avec les arguments et valeurs spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param string $label le contenu du label
 * @param array $values Un tableau associatif.
 * @param string selected la valeur sélectionnée par défaut
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_selectLabel($name, $label, array $values, $selected = null, array $args=array())
{
	return ViewHelper_Form_Label($name, $label) . ViewHelper_Form_select($name, $values, $selected, $args);
}

/**
 * Génère un select et son label avec les arguments et valeurs spécifiés. Ajoute un retour à la ligne.
 * 
 * @param string $name Nom / id du composant
 * @param string $label le contenu du label
 * @param array $values Un tableau associatif.
 * @param string selected la valeur sélectionnée par défaut
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_selectLabelBr($name, $label, array $values, $selected = null, array $args=array())
{
	return ViewHelper_Form_selectLabel($name, $label, $values, $selected, $args) . "<br />\n";
}




/**
 * Sérialise une pile d'arguments en attributs HTML.
 * 
 * @param array $args
 * 
 * @return string la liste d'attributs.
 */
function ViewHelper_Form_args(array $args)
{
	$argString = '';
	foreach($args as $att=>$val)
	{
		$argString .= $att . '="' . $val . '" ';
	}
	return $argString;
}