<?php
/**
 * Thumbnail.php - 22 déc. 2010
 * 
 * Génère des miniatures de fichier.
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
 * Documentation de la classe
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Thumbnail
{
	const EXTENSION = 'png';
	const WIDTH = 150;
	const HEIGHT = 212;
	
	/**
	 * Crée une miniature du fichier $Filename passé en paramètre.
	 * 
	 * @param string $Filename le chemin absolu vers le fichier à miniaturiser
	 * 
	 * @return string le chemin (http) vers la miniature créé
	 */
	public static function create($Filename)
	{
		$Images = array('jpg', 'png', 'jpeg', 'gif');
		$Extension = Util::extension($Filename);
		
		if(in_array($Extension, $Images))
		{
			return self::createImage($Filename);
		}
		elseif($Extension == 'pdf')
		{
			return self::createPdf($Filename);
		}
		else
		{
			return self::createDefault($Filename);
		}
	}
	
	/**
	 * Crée une miniature pour une image
	 * 
	 * @param string $Filename le chemin absolu vers l'image à miniaturiser
	 * @param string $Extension l'extension du fichier. Si non fournie, calculée via le nom.
	 * 
	 * @return string l'URL (HTTP) de la nouvelle image
	 */
	public static function createImage($Filename, $Extension = '')
	{
		if($Extension == '')
		{
			$Extension =Util::Extension($Filename); 
		}
		
		if($Extension == 'jpg' || $Extension == 'jpeg')
		{
			$Source = imagecreatefromjpeg($Filename);
		}
		elseif($Extension == 'gif')
		{
			$Source = imagecreatefromgif($Filename);
		}
		elseif($Extension == 'png')
		{
			$Source = imagecreatefrompng($Filename);
		}
		else
		{
			return self::createDefault($Filename);
		}
		
		$Thumb = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
		imagesavealpha($Thumb, true);
		imagefill($Thumb, 0, 0, imagecolorallocatealpha($Thumb, 255, 255, 255, 127));
		
		$Width = imagesx($Source);
		$Height = imagesy($Source);
		
		if($Width > $Height)
		{
			$ThumbWidth = self::WIDTH;
			$ThumbHeight = $Height * ($ThumbWidth / $Width); 
		}
		elseif($Width < $Height)
		{
			$ThumbHeight = self::HEIGHT;
			$ThumbWidth = $Width * ($ThumbHeight / $Height);
		}
		else
		{
			$ThumbHeight = self::WIDTH;
			$ThumbWidth = self::HEIGHT;
		}
		//Création de l'image.
		imagecopyresampled(
			$Thumb,
			$Source,
			(self::WIDTH - $ThumbWidth) / 2,
			(self::HEIGHT - $ThumbHeight) / 2,
			0,
			0,
			$ThumbWidth,
			$ThumbHeight,
			$Width,
			$Height
		);
		
		//Et sauvegarde.
		$ThumbFilename = self::getThumbFileName($Filename);
		imagepng($Thumb, $ThumbFilename, 8);
		
		return str_replace(PATH, '', $ThumbFilename);
	}
	
	/**
	 * Crée une miniature de la première page du PDF fourni
	 * 
	 * @param string $Filename le fichier
	 * 
	 * @return string l'URL (HTTP) de la nouvelle image
	 */
	public static function createPdf($Filename)
	{
		$ThumbFilename = self::getThumbFileName($Filename);
		
		//Générer l'aperçu de la première page
		if(1)
		{
			$ThumbFilename = str_replace('.png','.gif',$ThumbFilename);
			exec('convert ' . escapeshellarg($Filename) . '[0-5] -delay 100 -thumbnail 150x212! ' . $ThumbFilename . '>> /dev/null');
		}
		else
		{
			exec('convert ' . escapeshellarg($Filename) . '[0] -thumbnail 150x212! ' . $ThumbFilename .  '>> /dev/null');
		}
	
		return str_replace(PATH, '', $ThumbFilename);
	}
	
	/**
	 * Appelé quand le système ne sait pas comment générer l'aperçu.
	 * Renvoie une image générique.
	 * 
	 * @param string $Filename
	 * 
	 * @return string l'URL (HTTP) de la nouvelle image
	 */
	public static function createDefault($Filename)
	{
		return '/public/images/unavailable.png';
	}
	
	protected static function getThumbFileName($Filename)
	{
		$ThumbFilename = preg_replace(
			'`/([^/]+)\.(' . EXTENSIONS . ')$`',
			'/Thumbs/$1.png',
			$Filename
		);
		
		return $ThumbFilename;
	}
}