<?php
/**
 * Tex.php - 9 mars 2011
 * 
 * Gère un "objet compilation" LaTeX permettant de récupérer un fichier PDF résultat.
 * La compilation en elle-même peut être faite à distance via CLSI, ou en local avec pdflatex.
 * Attention, la similarité des résultats n'est pas garantie !
 * 
 * PHP Version 5
 * 
 * @category  Default
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 * @param string
 * @param string
 */
 
/**
 * Gestion de la compilation en LaTeX
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Tex
{
	/**	
	 * Le fichier principal sur lequel la compilation doit être lancé.
	 * (contient le chemin vers le fichier, et non son contenu)
	 * 
	 * @var string
	 */
	protected $MainFile;
	
	/**
	 * Le dossier dans lequel sera effectué la compilation
	 */
	protected $Workspace;
	
	/**
	 * Un tableau des différentes ressources nécessaires à la compilation
	 * 
	 * @var array
	 */
	protected $Resources = array();
	
	/**
	 * Construit un nouvel objet de compilation TeX
	 * 
	 * @param string $MainFile l'adresse du fichier .tex principal
	 */
	public function __construct($MainFile)
	{
		$this->MainFile = $MainFile;
		$this->Workspace = substr($MainFile, 0, strrpos($MainFile, '/'));
	}
	
	/**
	 * Ajoute une ressource au projet.
	 * 
	 * @param string $Resource l'adresse de la ressource
	 */
	public function addResource($Resource)
	{
		$this->Resources[] = $Resource;
	}
	
	/**
	 * Compile le document spécifié
	 * 
	 * @param bool $Local si la compilation doit se faire en local ou si elle peut être déportée vers un serveur distant par CLSI
	 * 
	 * @return array
	 * 	- la clé output contient la liste des lignes renvoyées,
	 *  - la clé errors la liste des lignes d'erreurs,
	 *  - la clé ok est un booléen indiquant le résultat de la compilation
	 *  - la clé log contient le chemin vers le fichier de log
	 *  - la clé pdf contient le chemin vers le fichier PDF. Attention, si 'ok' != true, ce fichier peut ne pas exister !
	 */
	public function compile($Local = false)
	{
		if($Local)
		{
			$CompileResults = $this->compileLocal();
		}
		else
		{
			$CompileResults = $this->compileRemote();
		}
		
		$Logs = file($CompileResults['log'], FILE_IGNORE_NEW_LINES);;
		$Erreurs = array();
		foreach($Logs as $Log)
		{
			if(isset($Log[0]) && $Log[0] == '!')
			{
				$Erreurs[] = substr($Log, 2);
			}
		}
		
		return array(
			'errors' => $Erreurs,
			'output' => $Logs,
			'ok' => empty($Erreurs),
			'log' => $CompileResults['log'],
			'pdf' => $CompileResults['pdf']
		);
	}
	
	/**
	 * Compile en local le fichier et ses ressources associées
	 * 
	 * @return array
	 *  - la clé log contient l'emplacement du fichier de logs résultant de la compilation
	 *  - la clé pdf contient l'emplacement du PDF. Attention, si la compilation a échouée, le fichier peut ne pas exister ou être obsolète 
	 */
	protected function compileLocal()
	{
		
		exec('/usr/bin/pdflatex -halt-on-error -interaction=nonstopmode -output-directory ' . escapeshellarg($this->Workspace) . ' ' . escapeshellarg($this->MainFile));
		$LogFile = str_replace('.tex', '.log', $this->MainFile);
		$PdfFile = str_replace('.tex', '.pdf', $this->MainFile);
		
		return array(
			'log' => $LogFile,
			'pdf' => $PdfFile
		);
	}
	
	/**
	 * Compile à distance le fichier TeX
	 * 
	 * @return array
	 *  - la clé log contient l'emplacement du fichier de logs résultant de la compilation
	 *  - la clé pdf contient l'emplacement du PDF. Attention, si la compilation a échouée, le fichier peut ne pas exister ou être obsolète 
	 */
	protected function compileRemote()
	{
		
		$MainFileAbsolute = $this->absoluteToRelative($this->MainFile);
		$Request = '
<?xml version="1.0" encoding="UTF-8"?>
<compile>
  <token>' . CLSI_TOKEN . '</token>
  <resources root-resource-path="' . $MainFileAbsolute . '">
    <resource path="' . $MainFileAbsolute . '">
      <![CDATA[
' . file_get_contents($this->MainFile) . '
]]>
    </resource>';
		
		foreach($this->Resources as $Resource)
		{
	    	$Request .= '<resource url="' . str_replace(PATH, URL, $Resource) . '" path="' . $this->absoluteToRelative($Resource) . '" modified="' . date('D M d H:i:s +0100 Y', filemtime($Resource)) . '"></resource>';
	    }
	    
	    $Request .= '
	  </resources>
	</compile>';
	    
	    $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://clsi.scribtex.com/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $Request);
		$data = curl_exec($ch);
		curl_close($ch);
		
		exit($data);
	}
	
	/**
	 * Retourne le chemin relatif du fichier par rapport à l'espace de travail défini.
	 * 
	 * @param unknown_type $Path
	 */
	protected function absoluteToRelative($Path)
	{
		return str_replace($this->Workspace . '/', '', $Path);
	}
}