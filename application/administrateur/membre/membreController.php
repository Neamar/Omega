<?php
/**
 * membreController.php - 12 févr. 2011
 * 
 * Actions réalisables sur les membres
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
 * Contrôleur membre du module administration
 *
 * @category Default
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 */
class Administrateur_MembreController extends AbstractController
{
	public function indexAction()
	{
		$this->View->setTitle(
			'Recherche d\'un membre',
			'Cette page permet de rechercher un membre, qu\'il soit élève ou correcteur.'
		);
		$this->View->addScript();
		
		if(isset($_POST['recherche-membre']))
		{
			$this->redirect('/administrateur/membre/recherche/' . $_POST['recherche']);
		}
	}
	
	/**
	 * Renvoie tous les membres répondant à la demande.
	 * Utilisé dans le text-input de index.
	 */
	public function _searchActionWd()
	{
		$Reponses = Sql::queryAssoc(
			'SELECT 
				ID,
				Mail AS label,
				REPLACE(REPLACE(Type, "CORRECTEUR", "Correcteur"), "ELEVE", "Élève") AS category
			FROM Membres
			WHERE Mail LIKE "' . SQL::escape($this->Data['data']) . '%"',
			'ID'
		);
		
		$this->json($Reponses);
	}
	
	public function eleveAction()
	{
		$this->redirect('/administrateur/log/eleve');
	}
	
	public function correcteurAction()
	{
		$this->redirect('/administrateur/log/correcteur');
	}
	
	public function membreAction()
	{
		$this->redirect('/administrateur/log/membre');
	}
	
	/**
	 * Affichage des données d'un élève
	 */
	public function eleveActionWd()
	{
		$Eleve = $this->exists($this->Data['data'], 'Eleve');
		if(is_null($Eleve))
		{
			$this->View->setMessage('warning', "Cet élève n'existe pas.");
			$this->redirect('/administrateur/membre/');
		}
		
		$this->View->setTitle(
			'Informations élève pour ' . $Eleve->Mail,
			'Cette page affiche les différentes informations connues sur cet élève.'
		);
		
		$this->View->Eleve = $Eleve;
		
		$this->View->Exos = Sql::queryAssoc(
			'SELECT Hash, Titre, Enchere, Expiration, Statut
			FROM Exercices
			WHERE Createur = ' . $Eleve->getFilteredId() . '
			ORDER BY ID DESC',
			'Hash'
		);
		$this->View->NbExos = count($this->View->Exos);
		
		$this->View->Membre = $this->concat('/administrateur/membre/membre/' . $this->Data['data']);
		$this->View->Membre->Membre = $Eleve;
	}
	
	/**
	 * Affichage des données d'un correcteur
	 */
	public function correcteurActionWd()
	{
		$Correcteur = $this->exists($this->Data['data'], 'Correcteur');
		
		$this->View->setTitle(
			'Informations correcteur pour ' . $Correcteur->Mail,
			'Cette page affiche les différentes informations connues sur ce correcteur.'
		);
		
		$this->View->Correcteur = $Correcteur;
		
		$this->View->Exos = Sql::queryAssoc(
			'SELECT Hash, Titre, Enchere, Expiration, Statut
			FROM Exercices
			WHERE Correcteur = ' . $Correcteur->getFilteredId() . '
			ORDER BY ID DESC',
			'Hash'
		);
		$this->View->NbExos = count($this->View->Exos);
		
		$this->View->Note = Sql::singleColumn(
			'SELECT COALESCE(AVG(Note),"&empty;") AS M
			FROM Exercices
			WHERE Correcteur = ' . $Correcteur->getFilteredId() . '
			AND !ISNULL(Note)',
			'M'
		);
		
		$this->View->Membre = $this->concat('/administrateur/membre/membre/' . $this->Data['data']);
		$this->View->Membre->Membre = $Correcteur;
	}
	
	public function correcteur_cvActionWd()
	{
		$this->UseTemplate = false;
		
		$Correcteur = $this->exists($this->Data['data'], 'Correcteur');
		
		$this->View->File = DATA_PATH . '/CV/' . $Correcteur->getFilteredId() . '.pdf';
	}
	
	public function correcteur_ciActionWd()
	{
		$this->UseTemplate = false;
		
		$Correcteur = $this->exists($this->Data['data'], 'Correcteur');
		
		$Fichier = glob(DATA_PATH . '/CI/' . $Correcteur->getFilteredId() . '.*');
		
		$this->View->File = $Fichier[0];
	}
	
	/**
	 * Fonction spéciale, qui n'est théoriquement jamais appelée directement.
	 */
	public function membreActionWd()
	{
		$this->View->setTitle(
			'Informations membre',
			'Cette page affiche les différentes informations de la personne demandée en tant que membre.'
		);
	}
	
	/**
	 * Recherche un membre et renvoie sur la page associée
	 */
	public function rechercheActionWd()
	{
		$Membre = $this->exists($this->Data['data'], 'Membre');
		$this->redirect('/administrateur/membre/' . strtolower($Membre->Type) . '/' . $Membre->Mail);
	}
	
	/**
	 * S'incarner dans le compte d'un membre.
	 * Fonctionne pour les élèves et les correcteurs.
	 * Le statut du membre doit etre OK ou BLOQUE, il n'est pas possible de s'incarner dans un membre DESINSCRIT ou EN_ATTENTE.
	 * 
	 * À noter : l'incarnation ne permet pas de faire tout et n'importe quoi.
	 * En particulier, le mot de passe n'est jamais communiqué à l'administrateur, et il lui est donc ompossible d'effectuer un retrait ou de fermer le compte.
	 * 
	 */
	public function incarnerActionWd()
	{
		$Membre = $this->exists($this->Data['data'], 'Membre');

		if($Membre->Statut != 'OK' && $Membre->Statut != 'BLOQUE')
		{
			$this->View->setMessage('warning', 'Le statut associé à ce compte (' . $Membre->Statut . ') n\'est pas incarnable.');
			$this->redirect('/administrateur/membre/');
		}
		else if($Membre->Type == 'ELEVE')
		{
			$_SESSION['Eleve'] = $this->exists($this->Data['data'], 'Eleve');
			$this->View->setMessage('info', 'Vous êtes maintenant connecté sur le compte de ' . $_SESSION['Eleve']->Mail);
			$this->redirect('/eleve/');
		}
		else if($Membre->Type == 'CORRECTEUR')
		{
			$_SESSION['Correcteur'] = $this->exists($this->Data['data'], 'Correcteur');
			$this->View->setMessage('info', 'Vous êtes maintenant connecté sur le compte de ' . $_SESSION['Correcteur']->Mail);
			$this->redirect('/correcteur/');
		}
		else
		{
			$this->View->setMessage('warning', 'Impossible d\'incarner ce compte.');
			$this->redirect('/administrateur/membre/');
		}
	}
	
	public function statutActionWd()
	{
		$Membre = $this->exists($this->Data['data'], 'Membre');
		$this->View->Status = array('OK', 'BLOQUE');
		$this->View->Default = $Membre->Statut;
		
		$this->View->setTitle(
			'Modifier le statut de ' . $Membre->Mail,
			'Cette page permet de modifier le statut d\'un membre, par exemple pour le bloquer.'
		);
		
		if(isset($_POST['changement-statut']))
		{
			if(!in_array($_POST['statut'], $this->View->Status))
			{
				$this->View->setMessage('error', 'Statut inconnu');
			}
			else 
			{
				//Avant d'appliquer la modification, on regarde si des évènements vons devoir être dispatchés.
				//Si oui, on les prépare dès maintenant pour les envoyer après.
				//On ne peut pas le faire après la mise à jour, car la détermination de l'évènement se fait à partir du statut actuel qui est écrasé.
				
				//Envoyer un mail si débloqué
				if($Membre->Statut == 'BLOQUE' && $_POST['statut'] == 'OK')
				{
					$ToDispatch = Event::MEMBRE_DEBLOQUE;
				}
				//Envoyer un mail si bloqué
				if($_POST['statut'] == 'BLOQUE')
				{
					$ToDispatch = Event::MEMBRE_BLOQUE;
				}
				//Correcteur accepté
				if($Membre->Statut == 'EN_ATTENTE' && $Membre->Type == 'CORRECTEUR' && $_POST['statut'] == 'OK')
				{
					$ToDispatch = Event::CORRECTEUR_VALIDATION;
				}
				
				//Enregistrer la modification
				$Membre->setAndSave(array('Statut' => $_POST['statut']));
				
				//Et enfin dispatcher l'évènement
				if(isset($ToDispatch))
				{
					Event::dispatch($ToDispatch, array('Membre' => $Membre));
				}
				
				$this->View->setMessage('ok', 'Modifications enregistrées');
				$this->redirect('/administrateur/membre/' . strtolower($Membre->Type) . '/' . $Membre->Mail);
			}
		}
	}
	
	/**
	 * Teste si un membre existe avec cet email.
	 * 
	 * @param string $Mail le mail
	 * @param string $Type le type du membre (Eleve, Correcteur, Membre)
	 * 
	 * @return Membre le membre associé. Si non existant, une redirection a lieu.
	 */
	protected function exists($Mail, $Type = "Membre")
	{
		$Membre = $Type::load('-1 OR Membres.Mail="' . Sql::escape($Mail) . '"', false);
		if(is_null($Membre))
		{
			$this->View->setMessage('warning', 'Ce ' . strtolower($Type) . " n'existe pas.");
			$this->redirect('/administrateur/membre/');
		}
		
		return $Membre;
	}
}