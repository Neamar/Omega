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
	 * @param string $Resource l'adresse absolue de la ressource
	 */
	public function addResource($Resource)
	{
		$this->Resources[] = $Resource;
	}
	
	/**
	 * Ajoute toutes les ressources contenues dans le dossier spécifié.
	 * Le chemin spécifié doit-être relatif au Workspace.
	 * 
	 * @param string $Path
	 */
	public function globResource($Path)
	{
		$Files = glob($this->Workspace . '/' . $Path . '/*');
		foreach($Files as $File)
		{
			$this->Resources[] = $File;
		}
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
		//Sé déplacer dans le bon dossier en enregistrant le dossier actuel
		$Cwd = getcwd();
		chdir($this->Workspace);
		//Compiler
		exec('/usr/bin/pdflatex -halt-on-error -interaction=nonstopmode -output-directory ' . escapeshellarg($this->Workspace) . ' ' . escapeshellarg($this->MainFile));
		chdir($Cwd);
		$Log = str_replace('.tex', '.log', $this->MainFile);
		$Pdf = str_replace('.tex', '.pdf', $this->MainFile);
		
		return array(
			'log' => $Log,
			'pdf' => $Pdf
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
	    	$Request .= '<resource url="' . str_replace(PATH, URL, $Resource) . '" path="' . $this->absoluteToRelative($Resource) . '" modified="' . date('c', filemtime($Resource)) . '"></resource>';
	    }
	    
	    $Request .= '
	  </resources>
	</compile>';
	    
	    $XMLPath = tempnam('/tmp', 'texcompile');
	    file_put_contents($XMLPath, $Request);
	    
	    $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://clsi.scribtex.com/clsi/compile');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => '@' . $XMLPath));
		$data = curl_exec($ch);
		curl_close($ch);
		unset($ch);
		
		if(!substr($data, 0, 5) == '<?xml')
		{
			throw new Exception('Retour non XML sur la compilation.');
		}

		$doc = new DOMDocument();
		$doc->loadXML($data);
		$Status = $doc->getElementsByTagName('status')->item(0)->textContent;
		$RemotePdf = $doc->getElementsByTagName('output')->item(0)->getElementsByTagName('file')->item(0)->getAttribute('url');
		$RemoteLog = $doc->getElementsByTagName('logs')->item(0)->getElementsByTagName('file')->item(0)->getAttribute('url');
		
		//Récupérer les deux fichiers sur le disque
		$Log = str_replace('.tex', '.log', $this->MainFile);
		$LogFile = fopen($Log, 'w');
		$Pdf = str_replace('.tex', '.pdf', $this->MainFile);
		$PdfFile = fopen($Pdf, 'w');
		
		$ch = curl_multi_init();

		$chLog = curl_init();
		curl_setopt($chLog, CURLOPT_URL, $RemoteLog);
		curl_setopt($chLog, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($chLog, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($chLog, CURLOPT_FILE, $LogFile);
		curl_multi_add_handle($ch, $chLog);

		
		$chPdf = curl_init();
		curl_setopt($chPdf, CURLOPT_URL, $RemotePdf);
		curl_setopt($chPdf, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($chPdf, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($chPdf, CURLOPT_FILE, $PdfFile);
		curl_multi_add_handle($ch, $chPdf);
		
		$running = null;
		do
		{
		    usleep(10000);
		    curl_multi_exec($ch, $running);
		}
		while($running > 0);
		curl_multi_remove_handle($ch, $chLog);
		curl_multi_remove_handle($ch, $chPdf);
		curl_multi_close($ch);

		
		curl_exec($chLog);
		curl_close($chLog);
		fclose($LogFile);
		curl_exec($chPdf);
		curl_close($chPdf);
		fclose($PdfFile);
		
		return array(
			'log' => $Log,
			'pdf' => $Pdf
		);
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