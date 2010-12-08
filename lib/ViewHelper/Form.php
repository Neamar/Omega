<?php 
/**
 * form.php - 26 oct. 2010
 *
 * Offrir des primitives de bas niveau pour la gestion des éléments de formulaire HTML.
 *
 * PHP Version 5
 *
 * @category  ViewHelper
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://devoirminute.com
 */

/**
 * Génère un input avec les arguments spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_input($name, array $args)
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
	

	return '<input ' . ViewHelper_Form_args($args) . '/>';
}

/**
 * Génère un input avec les arguments spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param array $args
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_submit($name, $value)
{
	$args = array('type'=>'submit','value'=>$value);
	if($name!=null)
	{
		$args['name'] = $args['id'] = $name;
	}

	return '<input ' . ViewHelper_Form_args($args) . '/>';
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
	
	$Return = '<select ' . ViewHelper_Form_args($args)  . ">\n";
	foreach($values as $value=>$caption)
	{
		$Return .= '	<option value="' . $value . '"' . ($value===$selected?' selected="selected"':'') . '>' . $caption . "</option>\n";
	}
	$Return .="</select>\n";
	
	return $Return;
}

/**
 * Génère un radio avec les arguments et valeurs spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param array $values Un tableau associatif.
 * @param string selected la valeur sélectionnée par défaut
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_radio($name, array $values, $selected=null)
{
	$Return = '';
	foreach($values as $value=>$caption)
	{
		$Return .= '<input type="radio" name="' . $name . '" value="' . $value . '" id="' . $value . '"' . ($value===$selected?' checked="checked"':'') . ' /><label for="' . $value . '">' . $caption . "</label><br />\n";
	}
	
	return $Return;
}

/**
 * Génère un checkbox avec les arguments et valeurs spécifiés.
 * 
 * @param string $name Nom / id du composant
 * @param array $values Un tableau associatif.
 * @param string|array selected la valeur (les valeurs) sélectionnée(s) par défaut
 * 
 * @return string le code HTML demandé.
 */
function ViewHelper_Form_checkbox($name, array $values, $selected=null)
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
function ViewHelper_Form_inputLabel($name, $label, array $args)
{
	return ViewHelper_Form_Label($name, $label) . viewHelper_Form_Input($name, $args);
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
function ViewHelper_Form_inputLabelBr($name, $label, array $args)
{
	return ViewHelper_Form_inputLabel($name, $label, $args) . "<br />\n";
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
function ViewHelper_Form_selectLabel($name, $label, array $values, $selected, array $args=array())
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
function ViewHelper_Form_selectLabelBr($name, $label, array $values, $selected, array $args=array())
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