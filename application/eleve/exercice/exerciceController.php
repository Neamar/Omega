<?php
/**
 * exerciceController.php - 26 oct. 2010
 * 
 * Actions pour un élève sur un exercice
 * 
 * PHP Version 5
 * 
 * @category  Controller
 * @package   Root
 * @author    Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @copyright 2010 Matthieu Bacconnier
 * @license   Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link      http://edevoir.com
 */

/**
 * Contrôleur d'exercice du module élève.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Eleve_ExerciceController extends ExerciceAbstractController
{
	const FORM_SIZE_LIMIT = 20971520;
	
	/**
	 * Page d'accueil du module ; afficher les infos du compte et les liens utiles. 
	 * 
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Accueil des exercices',
			"Cette page sert de porte d'entrée vers vos exercices."
		);
		
		$this->View->ExercicesActifs = Sql::queryAssoc(
			'SELECT Hash, Titre, Statut
			FROM Exercices
			WHERE Createur = ' . $_SESSION['Eleve']->getFilteredId() . '
			AND Statut NOT IN("ANNULE", "TERMINE", "REMBOURSE")',
			'Hash'
		);
		
		$this->View->Messages = $this->statusString();
	}
	
	/**
	 * Page d'accueil d'un exercice.
	 * (all) =>
	 */
	public function indexActionWd()
	{
		/**
		 * Liste des messages à afficher en fonction du statut.
		 * 
		 * @var array
		 */
		$ListeMessage = $this->statusString();

		$this->View->setTitle(
			'Accueil de l\'exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»',
			$ListeMessage[$this->Exercice->Statut]
		);
	}
	
	/**
	 * Création d'un nouvel exercice
	 * ∅ => VIERGE
	 */
	public function creationAction()
	{
		$this->View->setTitle(
			'Création d\'un nouvel exercice',
			"Vous allez ajouter un nouvel exercice. Soyez le plus complet possible afin que nous puissions vous offrir un service de qualité !"
		);
		
		$this->View->addScript('/public/js/eleve/exercice/creation.js');
		
		//Charger la liste des matières pour le combobox :
		$this->View->Matieres = SQL::queryAssoc('SELECT Matiere FROM Matieres', 'Matiere', 'Matiere');
		
		//Charger la liste des classes pour le combobox :
		$this->View->Classes = SQL::queryAssoc('SELECT Classe, DetailsClasse FROM Classes ORDER BY Classe DESC', 'Classe', 'DetailsClasse');
		
		//Charger la liste des types d'exercices pour le combobox :
		$this->View->Types = SQL::queryAssoc('SELECT Type, DetailsType FROM Types', 'Type', 'DetailsType');
		
		//Créer la liste des demandes supportées :
		$this->View->Demandes = SQL::queryAssoc('SELECT Demande, DetailsDemande FROM Demandes', 'Demande', 'DetailsDemande');
		
		if(isset($_POST['creation-exercice']))
		{
			if($_POST['titre'] == '')
			{
				$this->View->setMessage('error', "Vous devez donner un nom à votre article.");
			}
			elseif(!isset($this->View->Classes[$_POST['classe']]))
			{
				$this->View->setMessage('error', "Cette classe n'existe pas.");
			}
			elseif(!isset($this->View->Matieres[$_POST['matiere']]))
			{
				$this->View->setMessage('error', "Cette matière n'existe pas.");
			}
			elseif(!isset($this->View->Types[$_POST['type']]))
			{
				$this->View->setMessage('error', "Ce type d'exercice n'existe pas.");
			}
			elseif(!isset($this->View->Demandes[$_POST['demande']]))
			{
				$this->View->setMessage('error', "Cette demande n'existe pas.");
			}
			elseif(!is_numeric($_POST['rendu_heure'])
				|| $_POST['rendu_heure'] < 0
				|| $_POST['rendu_heure'] > 23)
			{
				$this->View->setMessage('error', "Heure de rendu invalide.");
			}
			elseif(!is_numeric($_POST['annulation_heure'])
				|| $_POST['annulation_heure'] < 0
				|| $_POST['annulation_heure'] > 23)
			{
				$this->View->setMessage('error', "Heure d'annulation invalide.");
			}
			elseif(!preg_match(Validator::DATE_REGEXP, $_POST['rendu_date'], $_POST['rendu_array'])
				|| (($_POST['rendu_ts'] = mktime($_POST['rendu_heure'], 0, 0, $_POST['rendu_array'][2], $_POST['rendu_array'][1], $_POST['rendu_array'][3])) === false))
			{
				$this->View->setMessage('error', "Date de rendu invalide.");
			}
			elseif(!preg_match(Validator::DATE_REGEXP, $_POST['annulation_date'], $_POST['annulation_array'])
				|| (($_POST['annulation_ts'] = mktime($_POST['annulation_heure'], 0, 0, $_POST['annulation_array'][2], $_POST['annulation_array'][1], $_POST['annulation_array'][3])) === false))
			{
				$this->View->setMessage('error', "Date d'annulation invalide.");
			}
			elseif($_POST['rendu_ts'] < time() + 2*3600)
			{
				$this->View->setMessage('error', "Le délai spécifié pour le rendu est trop court ou dans le passé. Nous ne faisons pas machine à remonter le temps, désolé.");
			}
			elseif($_POST['rendu_ts'] < $_POST['annulation_ts'])
			{
				$this->View->setMessage('error', "La date de rendu doit être postérieure à la date d'annulation automatique.");
			}
			elseif(!is_numeric($_POST['auto_accept']) || $_POST['auto_accept'] < 0)
			{
				$this->View->setMessage('error', "La valeur spécifiée pour l'acceptation automatique doit être numérique positive.");
			}
			elseif($_POST['auto_accept'] > $_SESSION['Eleve']->getPoints())
			{
				$this->View->setMessage('error', "La valeur spécifiée pour l'acceptation automatique est supérieure à votre solde. Valeur maximum : " . $_SESSION['Eleve']->getPoints());
			}
			else
			{
				$LongHash = Exercice::generateHash();
				
				$ToInsert = array
				(
					'Hash' => substr($LongHash, 0, HASH_LENGTH),
					'LongHash' => $LongHash,
					'Titre' => $_POST['titre'],
					'Createur' => $_SESSION['Eleve']->ID,
					'_IP' => 'INET_ATON("' . Sql::escape($_SERVER['REMOTE_ADDR']) . '")',
					'_Creation' => 'NOW()',
					'TimeoutEleve' => Sql::getDate($_POST['annulation_ts']),
					'Expiration' => Sql::getDate($_POST['rendu_ts']),
					'Matiere' => $_POST['matiere'],
					'Classe' => $_POST['classe'],
					'Section' => substr($_POST['section'], 0, 20),
					'Type' => $_POST['type'],
					'Demande' => $_POST['demande'],
					'InfosEleve' => $_POST['infos'],
					'Modificateur' => $_SESSION['Eleve']->getRaise(),
				);
				
				if($_POST['auto_accept'] > 0)
				{
					$ToInsert['AutoAccept'] = $_POST['auto_accept'];
				}
				
				if(Sql::insert('Exercices', $ToInsert))
				{
					//Créer les dossiers contenant les images et les miniatures :
					mkdir(PATH . '/public/exercices/' . $LongHash);
					mkdir(PATH . '/public/exercices/' . $LongHash . '/Sujet');
					mkdir(PATH . '/public/exercices/' . $LongHash . '/Sujet/Thumbs');
					mkdir(PATH . '/public/exercices/' . $LongHash . '/Corrige');
					mkdir(PATH . '/public/exercices/' . $LongHash . '/Corrige/Thumbs');
					mkdir(PATH . '/public/exercices/' . $LongHash . '/Corrige/Ressources');
					mkdir(PATH . '/public/exercices/' . $LongHash . '/Reclamation');
					mkdir(PATH . '/public/exercices/' . $LongHash . '/Reclamation/Thumbs/');
					
					//Logger la création de l'exercice.
					$Exercice = Exercice::load($ToInsert['Hash']);
					$Exercice->log('Exercices_Logs', 'Création de l\'exercice.', $_SESSION['Eleve'], $Exercice, array('NouveauStatut' => 'VIERGE'));
					
					//Mission accomplie ! Dispatcher l'évènement
					Event::dispatch(
						Event::ELEVE_EXERCICE_CREATION,
						array(
							'Exercice' => $Exercice
						)
					);
					
					$this->View->setMessage('info', 'Votre exercice a bien été ajouté');
					
					$this->redirect('/eleve/exercice/ajout/' . $ToInsert['Hash']);
				}
				else
				{
					$this->View->setMessage('error', "Impossible de créer cet exercice. Veuillez réessayer dans quelques minutes.");
				}
			}
		}
	}
	
	/**
	 * Ajoute des fichiers à un exercice si possible.
	 */
	public function ajoutActionWd()
	{
		$this->canAccess(array('VIERGE'), "Vous ne pouvez plus ajouter de fichiers sur cet exercice.");
		
		$this->View->setTitle(
			"Ajout de fichiers à l'exercice",
			"Cette page permet d'ajouter des fichiers à l'exercice avant de l'envoyer aux correcteurs."
		);
		
		$this->View->addScript('/public/js/jquery-multiupload.min.js');
		$this->View->addScript('/public/js/eleve/exercice/ajout.js');
		
		//Le nombre maximum de fichiers que l'on pourra envoyer depuis la page :
		//10, ou moins si la limite des 25 est proche.
		$NbFichiersPresents = $this->Exercice->getFilesCount(array('SUJET'));
		$NbFichiersAjoutes = 0;
		
		//La taille maximale du formulaire (20Mo) :
		$this->View->SizeLimit = self::FORM_SIZE_LIMIT;

		if(isset($_POST['upload-noscript']) || isset($_POST['upload']))
		{
			$NbFiles = count($_FILES['fichiers']['name']);
			$Messages = array();
			$Extensions = explode('|', EXTENSIONS);
			
			//Commencer une transaction pour rollbacker en cas d'erreur.
			Sql::start();

			for($i=0;$i<$NbFiles;$i++)
			{
				if($_FILES['fichiers']['name'][$i]=='')
				{
					continue;
				}

				$ExtensionFichier = Util::extension($_FILES['fichiers']['name'][$i]);
				$URL = '/Sujet/' . $NbFichiersPresents . '.' . $ExtensionFichier;
				$URLAbsolue = PATH . '/public/exercices/' . $this->Exercice->LongHash . $URL;
				
				//Effectuer les vérifications usuelles
				$Retour = $this->checkUploadedFile($i, $ExtensionFichier, $URLAbsolue, $NbFichiersPresents);
				
				if($Retour !== true)
				{
					$Messages[] = $Retour;
				}
				else
				{
					$ToInsert = array
					(
						'Exercice' => $this->Exercice->ID,
						'Type' => 'SUJET',
						'URL' => $URL,
						'ThumbURL' => Thumbnail::create($URLAbsolue),
						'NomUpload' => $_FILES['fichiers']['name'][$i],
					);
					
					if(!Sql::insert('Exercices_Fichiers', $ToInsert))
					{
						//Erreur à l'enregistrement en base de données
						$Messages[] = 'Impossible d\'enregistrer ' . $_FILES['fichiers']['name'][$i] . ' en base de données.';
					}
					else
					{
						$NbFichiersPresents++;
						$NbFichiersAjoutes++;
					}
				}		
			}
			
			if($NbFichiersAjoutes>0)
			{
				$Corrects = (($NbFichiersAjoutes>1)?$NbFichiersAjoutes . ' fichiers ont bien été ajoutés' : 'Votre fichier a bien été ajouté') . '. Vous pouvez encore ajouter jusqu\'à ' . (MAX_FICHIERS_EXERCICE - $NbFichiersPresents) . " fichiers.";
				$CorrectsClass = 'ok';
			}
			else
			{
				$Corrects = 'Aucun fichier ajouté.';
				$CorrectsClass = 'warning';
			}
			
			if(!empty($Messages))
			{
				//Il y a des erreurs
				Sql::rollback();
				$this->View->setMessage('error', 'Envoi de fichier annulé.<br />' . implode("<br />\n", $Messages));
			}
			else
			{
				//Ok !
				Sql::commit();
				if($_POST['next_page'] != 'resume')
				{
					//Il n'y a pas d'erreurs
					$this->View->setMessage($CorrectsClass, $Corrects);
				}
				else
				{
					//Vérifier que l'on peut passer à l'étape suivante.
					$CanForward = true;
					
					//Si on veut passer à la suite sans aucun fichier :
					if($NbFichiersPresents==0)
					{
						if($this->Exercice->InfosEleve == '')
						{
							$this->View->setMessage('error', "Cet exercice ne contient aucun fichier et aucune information. Impossible de passer à l'étape suivante.");
							$CanForward = false;
						}
						else
						{
							$this->View->setMessage(
								'warning',
								"Attention, vous avez validé cet exercice sans aucun fichier.<br />
								Seul le texte soumis en tant qu'infomation servira aux correcteurs.<br />
								S'il s'agit d'une erreur, vous pouvez <a href='/eleve/ajout/" . $this->Exercice->Hash . "'>retourner à l'ajout de fichier</a>."
							);
						}
					}
					
					if($CanForward)
					{					
						$this->redirectExercice('/eleve/exercice/recapitulatif/');
					}
				}
			}
		}
		
		$this->View->NbFilesUpload = min(10, MAX_FICHIERS_EXERCICE - $NbFichiersPresents);
		$this->View->NbFiles = $this->Exercice->getFilesCount(array('SUJET'));
	}
	
	/**
	 * Affiche le récapitulatif de l'exercice avant son envoi aux correcteurs
	 * VIERGE => ATTENTE_CORRECTEUR
	 */
	public function recapitulatifActionWd()
	{
		//TODO : ajouter la possibilité de modifier l'auto accept
		//TODO : ajouter la possibilité de modifier la date d'annulation automatique
		$this->canAccess(array('VIERGE'));
		
		$this->View->setTitle(
			'Récapitulatif des données envoyées aux correcteurs',
			"Cette page liste les informations qui seront envoyées au correcteur."
		);
		
		if($this->Exercice->getFilesCount(array('SUJET')) == 0 && empty($this->Exercice->InfosEleve))
		{
			$this->View->setMessage('warning', 'Vous ne pouvez pas envoyer un exercice ne contenant aucun fichier et aucun message. Entrez un texte dans le champ information ou ajoutez des fichiers en cliquant sur le lien approprié.');
		}
		
		if(isset($_POST['change-info']))
		{
			$this->View->setMessage('ok', "Les informations de l'exercice ont bien été modifiées.");
			$this->Exercice->setAndSave(array('InfosEleve'=>$_POST['infos']));
		}
		if(isset($_POST['resume']))
		{
			if($this->Exercice->getFilesCount(array('SUJET')) == 0 && empty($this->Exercice->InfosEleve))
			{
				$this->View->setMessage('error', 'Vous ne pouvez pas envoyer un exercice ne contenant aucun fichier et aucun message.');
			}
			else
			{
				$this->Exercice->setStatus('ATTENTE_CORRECTEUR', $_SESSION['Eleve'], "Envoi de l'exercice aux correcteurs.");
				
				Event::dispatch(
					Event::ELEVE_EXERCICE_ENVOI,
					array(
						'Exercice' => $this->Exercice
					)
				);
				
				$this->View->setMessage('info', "Votre exercice a bien été envoyé ! Vous serez averti par mail lorsqu'une offre vous sera faite.");
				$this->redirectExercice();
			}
		}
	}
	
	/**
	 * Annule un exercice.
	 * (VIERGE|ATTENTE_CORRECTEUR|ATTENTE_ÉLÈVE) => ANNULÉ
	 */
	public function annulationActionWd()
	{
		$this->canAccess(array('VIERGE', 'ATTENTE_CORRECTEUR','ATTENTE_ELEVE'), 'Vous ne pouvez plus annuler cet exercice pour l\'instant. Si nécessaire, vous pouvez <a href="/contact.htm">nous contacter</a>.');
		
		$this->View->setTitle(
			'Annulation de «&nbsp;' . $this->Exercice->Titre . '&nbsp;»',
			"Cette page permet l'annulation de l'exercice. Cette action n'a aucun coût."
		);
		
		if(isset($_POST['annulation']))
		{		
			$this->Exercice->cancelExercice($_SESSION['Eleve'], 'Annulation de l\'exercice.');
			
			$this->View->setMessage('info', "Votre exercice a été annulé.");
			$this->redirect("/eleve/exercice/");
		}
	}
	
	/**
	 * Consulte une offre et agit en conséquence.
	 * ATTENTE_ELEVE => (ANNULE|ATTENTE_CORRECTEUR|EN_COURS)
	 */
	public function consultation_offreActionWd()
	{
		$this->canAccess(array('ATTENTE_ELEVE'), 'Ce n\'est pas le moment de consulter les offres !');
		
		$this->View->setTitle(
			'Consultation offre pour «&nbsp;' . $this->Exercice->Titre . '&nbsp;»',
			"Cette page permet la consultation de l'offre qui vous a été faite. Vous pouvez l'accepter, la refuser ou annuler l'exercice."
		);
		
		if(isset($_POST['consultation-offre']))
		{
			if(!isset($_POST['choix']))
			{
				$this->View->setMessage('error', 'Sélectionnez une option !');
			}
			elseif($_POST['choix'] == 'oui' && $this->Exercice->pricePaid() > $_SESSION['Eleve']->getPoints())
			{
				$this->View->setMessage('error', "Vous n'avez pas assez de points pour accepter l'offre.", 'eleve/depot');
			}
			elseif($_POST['choix'] == 'annuler')
			{
				//Annuler l'exercice.
				$_POST['annulation'] = true;
				$this->concat('/eleve/exercice/annulation/' . $this->Exercice->Hash);
				//Jamais de retour (annulation redirige).
			}
			elseif($_POST['choix'] == 'non')
			{
				//Récupérer le correcteur actuel. À faire maintenant, puisqu'on le supprime après.
				$Correcteur = $this->Exercice->getCorrecteur();
				
				$this->Exercice->cancelOffer($_SESSION['Eleve'], "Refus de l'offre par l'élève");
				
				//Dispatch de l'évènement REFUS
				//Adjoindre le correcteur, qui n'est plus disponible sur l'exercice.
				Event::dispatch(
					Event::ELEVE_EXERCICE_REFUS,
					array(
						'Exercice' => $this->Exercice,
						'Correcteur' => $Correcteur
					)
				);

				if($this->Exercice->Statut == 'ANNULE')
				{
					$this->View->setMessage('info', "L'offre a bien été refusée. L'exercice est maintenant annulé.");
				}
				else
				{
					$this->View->setMessage('info', "L'offre a bien été refusée. Vous serez avertis par mail si un autre correcteur se déclare interessé.");
				}
				
				$this->redirectExercice();
				
			}
			elseif($_POST['choix'] == 'oui')
			{
				$Prix = $this->Exercice->pricePaid();
				Sql::start();
				if(!$_SESSION['Eleve']->debit($Prix, 'Paiement pour l\'exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»', $this->Exercice))
				{
					Sql::rollback();
					$this->View->setMessage('error', "Impossible d'effectuer le débit ; merci de réessayer ultérieurement.");
				}
				else
				{
					//Créditer la banque pour valider les contraintes
					Membre::getBanque()->credit($Prix, 'Stockage exercice', $this->Exercice);
					
					//Logger la bonne nouvelle
					$this->Exercice->setStatus('EN_COURS', $_SESSION['Eleve'], "Acceptation de l'offre par l'élève");

					//Terminer la transaction
					Sql::commit();
					
					//Dispatch de l'évènement ACCEPTATION
					Event::dispatch(
						Event::ELEVE_EXERCICE_ACCEPTATION,
						array(
							'Exercice' => $this->Exercice
						)
					);

					//Et rediriger.
					$this->View->setMessage('info', "Paiement effectué avec succès. Le correcteur va maintenant commencer à travailler...", 'eleve/acceptation');
					$this->redirect('/eleve/');					
				}
			}
		}
	}
	
	/**
	 * Noter l'exercice
	 * ENVOYE => TERMINE
	 */
	public function noteActionWd() 
	{
		$this->canAccess(array('ENVOYE'));
		
		$this->View->setTitle(
			'Noter la correction de «&nbsp;' . $this->Exercice->Titre . '&nbsp;»',
			"Cette page permet de noter le travail du correcteur."
		);
		$this->View->setSeelink('/eleve/exercice/reclamation/' . $this->Exercice->Hash, "Émettre une réclamation");
		
		if(isset($_POST['note-exercice']))
		{
			if(!is_numeric($_POST['note']) || $_POST['note'] < 0 || $_POST['note'] > 5)
			{
				$this->View->setMessage('error', 'La note doit être comprise entre 0 et 5.');
			}
			else
			{
				$ToUpdate = array(
					'Notation' => intval($_POST['note'])
				);
				
				$this->Exercice->closeExercice("Notation de l'exercice", $_SESSION['Eleve'], $ToUpdate);
				Event::dispatch(Event::ELEVE_EXERCICE_TERMINE, array('Exercice' => $this->Exercice));
				
				$this->View->setMessage('info', 'La note a été enregistrée, l\'exercice est terminé.');
				$this->redirectExercice();
			}
		}
	}
	
	/**
	 * Émet une réclamation pour remboursement (ou simple contestation si trop tard).
	 */
	public function contestationActionWd()
	{
		$this->canAccess(array('ENVOYE', 'TERMINE'), "Vous ne pouvez pas émettre de réclamation actuellement.");
		
		if(!empty($this->Exercice->Reclamation))
		{
			$this->View->setMessage('warning', 'Vous avez déjà envoyé une réclamation.');
			$this->redirectExercice();
		}
		
		$this->View->setTitle(
			"Émettre une réclamation",
			"Cette page permet de demander un jugement externe si le travail du correcteur n'est pas correct."
		);
				
		$this->View->addScript('/public/js/jquery-multiupload.min.js');
		$this->View->addScript('/public/js/eleve/exercice/ajout.js');
		
		
		$this->View->EstRemboursable = ($this->Exercice->Statut == 'ENVOYE');
		
		$this->View->SizeLimit = self::FORM_SIZE_LIMIT;
		$this->View->NbFilesUpload = MAX_FICHIERS_EXERCICE;
		
		if(isset($_POST['contestation-exercice']))
		{
			$NbFiles = count($_FILES['fichiers']['name']);
			$Messages = array();
			$NbFichiersPresents = 0;
			
			if(empty($_POST['message']))
			{
				$this->View->setMessage('error', 'Vous devez entrer un message détaillant votre réclamation');
			}
			else
			{
				//Démarrer une transaction : soit tout passe, soit tout casse.
				Sql::start();
				for($i=0;$i<$NbFiles;$i++)
				{
					if($_FILES['fichiers']['name'][$i]=='')
					{
						continue;
					}
					
					//Calcul du chemin du fichier
					$ExtensionFichier = Util::extension($_FILES['fichiers']['name'][$i]);
					$URL = '/Reclamation/' . $NbFichiersPresents . '.' . $ExtensionFichier;
					$URLAbsolue = PATH . '/public/exercices/' . $this->Exercice->LongHash . $URL;
					
					//Effectuer la plupart des tests.
					$Retour = $this->checkUploadedFile($i, $ExtensionFichier, $URLAbsolue, $NbFichiersPresents);
					
					if($Retour !== true)
					{
						//Enregistrer le message d'erreur et passer à la suite
						$Messages[] = $Retour;
					}
					else
					{
						$ToInsert = array
						(
							'Exercice' => $this->Exercice->ID,
							'Type' => 'RECLAMATION',
							'URL' => $URL,
							'ThumbURL' => Thumbnail::create($URLAbsolue),
							'NomUpload' => $_FILES['fichiers']['name'][$i],
						);
						
						if(!Sql::insert('Exercices_Fichiers', $ToInsert))
						{
							//Erreur à l'enregistrement en base de données
							$Messages[] = 'Impossible d\'enregistrer ' . $_FILES['fichiers']['name'][$i] . ' en base de données.';
						}
						else
						{
							$NbFichiersPresents++;
						}
					}
				}//fin for
				
				
				if(count($Messages) != 0)
				{
					//Il y a des erreurs
					$this->View->setMessage('error', implode("<br />\n", $Messages));
					Sql::rollback();
				}
				else
				{
					//Modifier le statut
					$ToUpdate = array(
						'Reclamation' => ($this->View->EstRemboursable?'REMBOURSEMENT':'CONTESTATION'),
						'InfosReclamation' => $_POST['message']
					);
					
					$this->Exercice->setStatus('REFUSE', $_SESSION['Eleve'], 'Contestation sur l\'exercice', $ToUpdate);
					
					//Et enregistrer
					Sql::commit();
					
					Event::dispatch(
						Event::ELEVE_EXERCICE_RECLAMATION,
						array(
							'Exercice' => $this->Exercice
						)
					);
					
					$this->View->setMessage('ok', 'Contestation envoyée. Vous serez informé par mail du dénouement de cette affaire...');
					$this->redirectExercice();
				}
			}//fin empty(message)
		}//fin POST
	}
	
	/**
	 * Afficher le corrigé
	 * @see ExerciceAbstractController::corrigeActionWd()
	 */
	public function corrigeActionWd()
	{
		$this->View->Infos = 'Vous allez consulter le corrigé. <br />
		N\'oubliez pas de <a href="/eleve/exercice/note/' . $this->Exercice->Hash . '">noter le correcteur</a> pour son travail !
		Vous pouvez aussi <a href="/eleve/exercice/contestation/' . $this->Exercice->Hash . '">émettre une réclamation</a> si nécessaire.';
		
		parent::corrigeActionWd();
	}
	
	/**
	 * Chat de l'exercice
	 * @see ExerciceAbstractController::faqActionWd()
	 */
	public function faqActionWd()
	{
		//Lest tests d'accès sont effectués dans la méthode parente	
		$this->View->setTitle(
			"Chat de l'exercice",
			"Cette page permet de dialoguer avec le correcteur pour éclaircir les points restés obscurs."
		);
		
		//Enregistrer et récupérer les données
		parent::faqActionWd();
	}
		
	/**
	 * Liste les actions effectuées sur un exercice
	 */
	public function _actionsActionWd()
	{
		$this->ajax(
			'SELECT DATE_FORMAT(Date,"%d/%m/%y à %Hh"), Action
			FROM Exercices_Logs
			WHERE Exercice = ' . DbObject::filterID($this->Exercice->ID)
		);
	}
	
	/**
	 * Liste les actions des exercices
	 */
	public function _actionsAction()
	{
		$this->ajax(
			'SELECT DATE_FORMAT(Date,"%d/%m/%y à %Hh"), CONCAT(Matiere, \' : <a href="/eleve/exercice/index/\', Hash, \'">\', Titre, \'</a>\'), Action
			FROM Exercices_Logs
			LEFT JOIN Exercices ON (Exercices_Logs.Exercice = Exercices.ID)
			WHERE Createur = ' . $_SESSION['Eleve']->getFilteredId()
		);
	}
	
	/**
	 * Vérifie si un fichier uploadé est correct et l'enregistre dans le dossier $URL spécifié.
	 * Teste les erreurs HTTP, la taille, le nombre de fichiers et l'extension.
	 * 
	 * @param int $i le numéro dans le tableau $_FILES
	 * @param string $ExtensionFichier l'extension du fichier.
	 * @param string $URL le path dans lequel enregistrer le fichier.
	 * @param int $NbFichiersPresents le nombre de fichiers déjà enregistrés.
	 * 
	 * @return bool|string true si tout va bien, ou un message d'erreur.
	 */
	protected function checkUploadedFile($i, $ExtensionFichier, $URL, $NbFichiersPresents)
	{
		static $Extensions = array();
		
		if(empty($Extensions))
		{
			$Extensions = explode('|', EXTENSIONS);
		}
		
		if($_FILES['fichiers']['error'][$i] > 0)
		{
			//Erreur côté http
			return 'Une erreur est survenue lors de l\'envoi du fichier ' .  $_FILES['fichiers']['name'][$i] . ' (<a href="/documentation/eleve/erreurs_upload">erreur ' . $_FILES['fichiers']['error'][$i] . '</a>).';
		}
		elseif($_FILES['fichiers']['size'][$i] > self::FORM_SIZE_LIMIT)
		{
			//Dépassement de la taille maximale
			return 'Une erreur est survenue lors de l\'envoi du fichier ' .  $_FILES['fichiers']['name'][$i] . ' (<a href="/documentation/eleve/erreurs_upload">erreur 1</a>).';
		}
		elseif($NbFichiersPresents >= MAX_FICHIERS_EXERCICE)
		{
			return 'Une erreur est survenue lors de l\'envoi du fichier ' .  $_FILES['fichiers']['name'][$i] . ' (<a href="/documentation/eleve/erreurs_upload">erreur 6</a>).';
		}
		else
		{
			//Vérification de l'extension
			if (!in_array($ExtensionFichier, $Extensions))
			{
				return 'Une erreur est survenue lors de l\'envoi du fichier ' .  $_FILES['fichiers']['name'][$i] . ' (<a href="/documentation/eleve/erreurs_upload">erreur 5</a>).';
			}
			
			if(!move_uploaded_file($_FILES['fichiers']['tmp_name'][$i], $URL))
			{
				return 'Impossible de récupérer ' . $_FILES['fichiers']['name'][$i];
			}
			
			return true;
		}
	}
	
	/**
	 * Vérifie que l'exercice associé à la page est disponible.
	 * Overridé par les classes filles.
	 * @see ExerciceAbstractController::hasAccess
	 * 
	 * @return bool true si l'exercice peut être accédé.
	 */
	protected function hasAccess(Exercice $Exercice)
	{
		return ($Exercice->Createur == $_SESSION['Eleve']->ID);
	}
	
	/**
	 * Liste des messages à afficher en fonction du statut.
	 * 
	 * @return array
	 */
	protected function statusString()
	{
		return array(
			'VIERGE' => "Cet exercice attend encore vos fichiers. Si vous en avec fini avec l'envoi, vous pouvez l'envoyer aux correcteurs en sélectionnant l'option appropriée.",
			'ATTENTE_CORRECTEUR' => "Cet exercice est actuellement disponible chez les correcteurs, qui se battent tels des bêtes sauvages pour avoir l'honneur de le corriger. Vous serez informé par mail dès que l'un d'eux vous fera une offre !",
			'ATTENTE_ELEVE' => "Une offre vous a été faite ; acceptez-la ou déclinez-la.",
			'EN_COURS' => "Le correcteur s'occupe de tout... relax !",
			'ENVOYE' => "Le corrigé est disponible !",
			'ANNULE' => 'Cet exercice a été annulé. Vous ne pouvez plus rien faire dessus, <a href="/eleve/exercice/creation">pourquoi ne pas en créer un nouveau</a> ?', 
			'TERMINE' => 'Cet exercice est terminé. Vous pouvez encore consulter sujet, corrigé et le chat.', 
			'REFUSE' => 'Vous avez émis une contestation. Vous serez averti par mail des résultats.', 
			'REMBOURSE' => is_null($this->Exercice)?'WTF?':'Vous avez été remboursé à hauteur de <strong>' . $this->Exercice->Remboursement . '%</strong>. Avec toute nos excuses pour le préjudice subi !', 
		);
	}
}