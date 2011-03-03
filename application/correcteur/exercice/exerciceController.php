<?php
/**
 * exerciceController.php - 30 déc. 2010
 * 
 * Actions pour un correcteur sur un exercice.
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
 * Contrôleur d'exercice du module correcteur.
 * 
 * @category Controller
 * @package  Root
 * @author   Matthieu Bacconnier <matthieu@bacconnier.fr>
 * @license  Copyright http://fr.wikipedia.org/wiki/Copyright
 * @link     http://edevoir.com
 *
 */
class Correcteur_ExerciceController extends ExerciceAbstractController
{
	/**
	 * Page d'index du module
	 */
	public function indexAction()
	{
		$this->View->setTitle(
			'Mes exercices',
			"Cette page permet de consulter d'un coup d'œil les dernières actions sur les exercices que vous avez réservés."
		);
		
		$this->View->ExercicesActifs = Sql::queryAssoc(
			'SELECT Hash, Titre, Expiration
			FROM Exercices
			WHERE Correcteur = ' . $_SESSION['Correcteur']->getFilteredId() . '
			AND Statut = "EN_COURS"
			ORDER BY Expiration',
			'Hash'
		);
		
		$this->View->QuestionsActives = Sql::queryAssoc(
			'SELECT Exercices.Hash, Exercices.Titre, COUNT(*) AS NbQuestions
			FROM Exercices_FAQ FAQ
			JOIN Exercices ON (Exercices.ID = FAQ.Exercice)
			WHERE ISNULL(Parent)
			AND Membre <> ' . $this->getMembre()->getFilteredId() . '
			AND (SELECT COUNT(*) FROM Exercices_FAQ Reponses WHERE Parent = FAQ.ID) = 0
			GROUP BY Exercices.ID',
			'Hash'
		);
	}
	
	/**
	 * Page d'index d'un exercice.
	 */
	public function indexActionWd()
	{
		/**
		 * Liste des messages à afficher en fonction du statut.
		 * 
		 * @var array
		 */
		$ListeMessage = array(
			'ATTENTE_CORRECTEUR' => "Cet exercice est disponible et ouvert à la réservation.",
			'ATTENTE_ELEVE' => "Votre offre a été transmise à l'élève. Vous serez informés de son choix.",
			'EN_COURS' => "Votre offre a été acceptée, vous pouvez commencer à travailler.",
			'ENVOYE' => "Vous avez envoyé votre corrigé. L'élève n'a pas encore fait de remarques.",
			'TERMINE' => "L'exercice est terminé. Vous avez été payé.",
			'REFUSE' => "L'élève a émis une réclamation. Une équipe vérifie actuellement la justesse de cette accusation...",
			'REMBOURSE' => "L'élève a été remboursé et dédommagé à hauteur de <strong>" . $this->Exercice->Remboursement . '%</strong>.',
		);

		$this->View->setTitle(
			'Accueil de l\'exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»',
			$ListeMessage[$this->Exercice->Statut]
		);
	}
	
	/**
	 * Demander à réserver un article.
	 * ATTENTE_CORRECTEUR => ATTENTE_ELEVE
	 */
	public function reservationActionWd()
	{
		if($this->getMembre()->Statut == 'BLOQUE')
		{
			$this->View->setMessage('warning', 'Votre compte est bloqué. En conséquence, vous ne pouvez pas réserver de nouvel exercice.', 'correcteur/bloque');
			$this->redirect('/correcteur/');
		}
		
		$this->canAccess(array('ATTENTE_CORRECTEUR'), 'Trop tard ! Vous ne pouvez plus réserver cet exercice !');
		
		//Peut-on accéder à l'exo ?
		$Deja = Sql::singleQuery(
			'SELECT COUNT(*) AS Vu
			FROM Exercices_Correcteurs
			WHERE Exercice = ' . DbObject::filterID($this->Exercice->ID)
		);
		if($Deja['Vu'] > 0)
		{
			$this->View->setMessage('error', "Vous avez déjà fait une offre sur cet exercice.");
			$this->redirect('/correcteur/liste');
		}
		
		$this->View->setTitle(
			'Réservation de «&nbsp;' . $this->Exercice->Titre . '&nbsp;»',
			"Cette page permet de consulter un exercice avant de le réserver en indiquant votre prix."
		);
		
		$this->View->addScript();
		
		if(isset($_POST['reservation-exercice']))
		{
			$_POST['prix'] = intval($_POST['prix']);
			if($_POST['prix'] == 0)
			{
				$this->View->setMessage('error', 'Désolé, nous ne faisons pas dans le bénévolat. Indiquez une valeur supérieure à 0.');
			}
			elseif($_POST['prix'] > MAX_SOMME)
			{
				$this->View->setMessage('error', "C'est une sacrée somme ! N'y aurait-il pas eu une erreur de saisie ? Somme max : " . MAX_SOMME);
			}
			elseif(!is_numeric($_POST['annulation_heure'])
				|| $_POST['annulation_heure'] < 0
				|| $_POST['annulation_heure'] > 23)
			{
				$this->View->setMessage('error', "Heure d'annulation invalide.");
			}
			elseif(!preg_match(Validator::DATE_REGEXP, $_POST['annulation_date'], $_POST['annulation_array'])
				|| (($_POST['annulation_ts'] = mktime($_POST['annulation_heure'], 0, 0, $_POST['annulation_array'][2], $_POST['annulation_array'][1], $_POST['annulation_array'][3])) === false))
			{
				$this->View->setMessage('error', "Date d'annulation invalide.");
			}
			elseif($_POST['annulation_ts'] <= time())
			{
				$this->View->setMessage('error', "La date d'annulation doit être dans le futur ! Les travaux prémonitoires ne sont pas supportés ici.");
			}
			elseif($_POST['annulation_ts'] >= strtotime($this->Exercice->Expiration) - 3600)
			{
				$this->View->setMessage('error', "La date d'expiration doit dépasser d'au moins une heure la date d'annulation !");
			}
			else
			{
				$ToUpdate = array(
					'Correcteur' => $_SESSION['Correcteur']->ID,
					'Enchere' => $_POST['prix'],
					'TimeoutCorrecteur' => Sql::getDate($_POST['annulation_ts']),
					'InfosCorrecteur' => $_POST['infos'],
				);
				
				Sql::start();
				
				$this->Exercice->setStatus('ATTENTE_ELEVE', $_SESSION['Correcteur'], 'Proposition correcteur', $ToUpdate);

				//Logger l'offre.
				Sql::insert(
					'Exercices_Correcteurs',
					array(
						'Exercice' => $this->Exercice->ID,
						'Correcteur' => $_SESSION['Correcteur']->ID,
						'Action' => 'ENCHERE',
						'Offre' => $_POST['prix'],
					)
				);
				
				Sql::commit();
				
				//Gestion de l'auto-accept.
				if(empty($this->Exercice->AutoAccept))
				{
					Event::dispatch(
						Event::CORRECTEUR_EXERCICE_PROPOSITION,
						array(
							'Exercice' => $this->Exercice,
							'Eleve' => $this->Exercice->getEleve()
						)
					);
					$this->View->setMessage('ok', "Vous avez fait votre proposition ! Vous serez informé par mail de son résultat.");
				}
				else
				{
					$Eleve = $this->Exercice->getEleve();
					$Prix = $this->Exercice->pricePaid();
					
					//Prendre le minimum entre le solde et la valeur indiquée.
					$AutoAccept = min($Eleve->getPoints(), $this->Exercice->AutoAccept);
					if($Prix <= $AutoAccept)
					{
						//Auto-acceptation.
						Sql::start();
						if(!$Eleve->debit($Prix, 'Paiement pour l\'exercice «&nbsp;' . $this->Exercice->Titre . '&nbsp;»', $this->Exercice))
						{
							Event::log('CRITIQUE ! Fail sur autoaccept incohérent.', $Eleve);
							Sql::rollback();
							$this->View->setMessage('error', "Une erreur critique s'est produite, nous mettons tout en œuvre pour la corriger rapidement.");
						}
						else
						{
							//Créditer la banque pour valider les contraintes
							Membre::getBanque()->credit($Prix, 'Stockage exercice', $this->Exercice);
							
							//Logger la bonne nouvelle
							$this->Exercice->setStatus('EN_COURS', $Eleve, "Acceptation automatique de l'offre.");
		
							//Terminer la transaction
							Sql::commit();
							
							//Dispatch de l'évènement ACCEPTATION
							Event::dispatch(
								Event::ELEVE_EXERCICE_ACCEPTATION_AUTOMATIQUE,
								array(
									'Exercice' => $this->Exercice
								)
							);
							
							$this->View->setMessage('info', 'Votre proposition a été automatiquement acceptée par l\'élève ; félicitations !');
						}
					}
					else
					{
						$this->Exercice->cancelOffer($Eleve, "Refus automatique de l'offre");
				
						//Dispatch de l'évènement REFUS.
						//Adjoindre le correcteur, qui n'est plus disponible sur l'exercice.
						Event::dispatch(
							Event::ELEVE_EXERCICE_REFUS_AUTOMATIQUE,
							array(
								'Exercice' => $this->Exercice,
								'Correcteur' => $this->getMembre()
							)
						);
						
						$this->View->setMessage('warning', 'Votre proposition a été automatiquement refusée par l\'élève ; désolé !');
					}
				}
				$this->redirect('/correcteur/');
			}
		}
	}
	
	/**
	 * Permet d'envoyer le corrigé.
	 * EN_COURS => ENVOYE
	 * 
	 * @see Correcteur_ExerciceController::_compilationActionWd()
	 * La page compilant une ressource temporaire pour l'aperçu.
	 * 
	 * @see Correcteur_ExerciceController::_previewActionWd()
	 * La page récupérant une preview d'une des pages du PDF crée par _compilationAction
	 * 
	 * @see Correcteur_ExerciceController::_revertActionWd()
	 * Retourner à une ancienne version du corrigé
	 * 
	 * @see Correcteur_ExerciceController::_ressourceActionWd()
	 * Ajouter une nouvelle ressource.
	 */
	public function envoiActionWd()
	{
		$this->canAccess(array('EN_COURS'));
			
		$this->View->setTitle(
			'Rédaction du corrigé de «&nbsp;' . $this->Exercice->Titre . '&nbsp;»',
			"Cette page permet de rédiger le corrigé d'un exercice."
		);
		//Coloration syntaxique
		//http://codemirror.net/manual.html
		$this->View->addScript('/public/js/CodeMirror/codemirror.js');
		
		//Le plugin uploadify pour l'envoi de ressources
		//http://www.uploadify.com/documentation/
		$this->View->addScript('/public/js/Uploadify/swfobject.js');
		$this->View->addScript('/public/js/Uploadify/jquery-uploadify.min.js');
		
		//Gestion des onglets et du javascript de la page
		$this->View->addScript();
		
		//Gestion des numéros de ligne à côté de l'éditeur
		$this->View->addStyle('/public/css/envoi.css');
		
		//Gestion du style d'uploadify pour l'envoi des ressources
		$this->View->addStyle('/public/css/uploadify/uploadify.css');
		
		//Le token.
		//@see Correcteur_ExerciceController::computeToken() pour les détails.
		$this->View->token = $this->computeToken($this->getMembre()->ID, $this->Exercice->LongHash);
		
		if(isset($_POST['envoi-exercice']))
		{
			//Le nom de fichier utilisé pour stocker tex, pdf et autres.
			$FileName = $this->Exercice->filterTitle();

			//L'url du fichier Tex
			$CorrigeURL = PATH . '/public/exercices/' . $this->Exercice->LongHash . '/Corrige/' . $FileName . '.tex';
			
			$this->texFromTemplate($_POST['corrige'], $CorrigeURL);
			
			$Erreurs = $this->compileTex($CorrigeURL);
			
			if(!$Erreurs['ok'])
			{
				$this->View->setMessage('error', 'Des erreurs se sont produites, empêchant la compilation du document.');
				$this->View->Erreurs = $Erreurs['errors'];
			}
			else
			{
				//Insérer le PDF dans les fichiers constituant l'exercice
				$CorrigeURLPDF = substr($CorrigeURL, 0, -3) . 'pdf';
				$ToInsert = array
				(
					'Exercice' => $this->Exercice->ID,
					'Type' => 'CORRIGE',
					'URL' => '/Corrige/' . $FileName . '.pdf',
					'ThumbURL' => Thumbnail::create($CorrigeURLPDF),
					'NomUpload' => $FileName . '.pdf',
				);
				
				if(!Sql::insert('Exercices_Fichiers', $ToInsert))
				{
					$this->View->setMessage('error', "Impossible d'enregistrer le PDF sur l'exercice... merci de nous contacter.");
				}
				else
				{
					if(!isset($_POST['gratuit']))
					{
						//Envoi normal
						//Modifier le statut vers ENVOYE
						$this->Exercice->setStatus('ENVOYE', $_SESSION['Correcteur'], 'Envoi du fichier corrigé');
						
						Event::dispatch(
							Event::CORRECTEUR_EXERCICE_ENVOI,
							array(
								'Exercice' => $this->Exercice,
								'Correcteur' => $this->getMembre(),
								'Eleve' => $this->Exercice->getEleve()
							)
						);
					
						//Et rediriger
						$this->View->setMessage('ok', 'Corrigé compilé avec succès et envoyé à l\'élève.');
						$this->redirect('/correcteur/exercice/');
					}
					else
					{
						//Envoi gratuit
						//Modifier le statut vers REMBOURSE
						Sql::start();
						$this->Exercice->setStatus(
							'REMBOURSE',
							$_SESSION['Correcteur'],
							'Envoi du fichier corrigé sans frais',
							array(
								'Reclamation' => 'NON_PAYE',
								'Remboursement' => '100'
							)
						);
						$Prix = $this->Exercice->pricePaid();
						$this->Exercice->getEleve()->credit($Prix, 'Remboursement à titre gracieux de l\'exercice.', $this->Exercice);
						Membre::getBanque()->debit($Prix, 'Remboursement gracieux', $this->Exercice);
						Sql::commit();
						
						Event::dispatch(
							Event::CORRECTEUR_EXERCICE_ENVOI_GRATUIT,
							array(
								'Exercice' => $this->Exercice,
								'Correcteur' => $this->getMembre(),
								'Eleve' => $this->Exercice->getEleve()
							)
						);
					
						//Et rediriger
						$this->View->setMessage('ok', 'Corrigé compilé avec succès et envoyé gratuitement.');
						$this->redirect('/correcteur/exercice/');						
					}				
				}
			}
		}

		$this->View->Historique = Sql::queryAssoc(
			'SELECT
				ID,
				CONCAT(
					DATE_FORMAT(Date,"%d/%m/%y à %H:%m"),
					" (",
					Longueur,
					" caractère",
					IF(Longueur > 1,"s)",")")
				) AS Caption
			FROM Exercices_Corriges
			WHERE Exercice = "' . DbObject::filterID($this->Exercice->ID) . '"
			ORDER BY ID DESC',
			'ID',
			'Caption'
		);
		
		$this->View->Head = Sql::singleColumn(
			'SELECT Contenu
			FROM Exercices_Corriges
			WHERE Exercice = "' . DbObject::filterID($this->Exercice->ID) . '"
			ORDER BY ID DESC
			LIMIT 1',
			'Contenu'
		);
	}
	
	/**
	 * Compile un PDF d'aperçu, et renvoie la sortie console.
	 * 
	 * @see Correcteur_ExerciceController::envoiActionWd()
	 */
	public function _compilationActionWd()
	{
		$this->canAccess(array('EN_COURS'));
		
		if(isset($_POST['texte']))
		{
			$ToInsert = array(
				'Exercice' => $this->Exercice->ID,
				'_Date' => 'NOW()',
				'Contenu' => $_POST['texte'],
				'Longueur' => mb_strlen($_POST['texte'], 'utf-8')
			);
			Sql::insert('Exercices_Corriges', $ToInsert);
			$this->View->ID = Sql::lastId();
			//exit(mysql_error());
			//Le nom de fichier utilisé pour stocker tex, pdf et autres.
			$FileName = 'preview';

			//L'url du fichier Tex
			$PreviewURL = PATH . '/public/exercices/' . $this->Exercice->LongHash . '/Corrige/' . $FileName . '.tex';
			
			$this->texFromTemplate($_POST['texte'], $PreviewURL);
			
			$Back = $this->compileTex($PreviewURL);
			
			$this->View->Out = str_replace(PATH, '~', implode("\n", $Back['output']));
		}
		else
		{
			$this->View->Out = 'Appel incorrect.';
		}
	}
	
	/**
	 * Prévisualiser un document Tex.
	 * 
	 * @see Correcteur_ExerciceController::envoiActionWd()
	 */
	public function _previewActionWd()
	{
		if(!isset($this->Data['page']) || !is_numeric($this->Data['page']))
		{
			exit('Page mal spécifiée.');
		}
		elseif(!isset($this->Data['width']) || !is_numeric($this->Data['width']))
		{
			exit('Largeur mal spécifiée.');
		}
		else
		{
			//Effectuer une translation de 1.
			$Page = intval($this->Data['page']) - 1;
			$Largeur = min(1300, intval($this->Data['width']));
			$Filename = PATH . '/public/exercices/' . $this->Exercice->LongHash . '/Corrige/preview.pdf';
			$FilenameOut = PATH . '/public/exercices/' . $this->Exercice->LongHash . '/Corrige/preview.png';
			if(!is_file($Filename))
			{
				exit('Aperçu inexistant.');
			}
			else
			{
				exec('convert -density 250 ' . $Filename . '[' . $Page . '] -resize ' . $Largeur . ' ' . $FilenameOut, $L);

				$this->View->Img = $FilenameOut;
			}
		}
	}
	
	/**
	 * Renvoie une révision antérieure du document
	 * 
	 * @see Correcteur_ExerciceController::envoiActionWd()
	 */
	public function _revertActionWd()
	{
		if(!isset($this->Data['id']) || !is_numeric($this->Data['id']))
		{
			exit('Impossible d\'effectuer un revert vers cette révision.');
		}

		$this->View->Texte = Sql::singleColumn(
			'SELECT Contenu
			FROM Exercices_Corriges
			WHERE Exercice = "' . DbObject::filterID($this->Exercice->ID) . '"
			AND ID = "' . intval($this->Data['id']) . '"',
			'Contenu'
		);
	}
	
	/**
	 * Ajouter une ressource pour la correction.
	 * Cette page n'a pas de vue associée.
	 * 
	 * @see Correcteur_ExerciceController::envoiActionWd()
	 */
	public function _ressourceAction()
	{
		if (!isset($_FILES['Filedata']['name']) || !isset($_POST['hash']) || !isset($_POST['token']))
		{
			echo 'Connecteur mal utilisé.';
		}
		else
		{
			$this->Exercice = Exercice::load(substr($_POST['hash'], 0, HASH_LENGTH));
			$ExtensionFichier = Util::extension($_FILES['Filedata']['name']);
			$Extensions = array('png', 'jpg', 'gif', 'pdf', 'svg', 'ps');
			
			if(is_null($this->Exercice))
			{
				exit('Exercice inconnu.');
			}
			elseif($_POST['token'] != $this->computeToken($this->Exercice->Correcteur, $this->Exercice->LongHash))
			{
				exit('Token invalide.');
			}
			elseif($_FILES['Filedata']['error'] > 0)
			{
				exit('Erreur à l\'upload.');
			}
			elseif(!in_array($ExtensionFichier, $Extensions))
			{
				exit('Extensions non autorisées.');
			}
			else 
			{
				//Ça a marché !
				$FinalURL = PATH . '/public/exercices/' . $_POST['hash'] . '/Corrige/Ressources/' . $_FILES['Filedata']['name'];
				move_uploaded_file(
					$_FILES['Filedata']['tmp_name'],
					PATH . '/public/exercices/' . $_POST['hash'] . '/Corrige/Ressources/' . $_FILES['Filedata']['name']
				);
				echo $FinalURL;
			}
		}
		
		//Dans tous les cas, on stoppe ici. Pas de fichier de vue.
		exit();
	}
	
	/**
	 * Renvoie la liste des ressources présentes sur l'exercice.
	 */
	public function _ressourcesActionWd()
	{
		$Ressources = array();
		$handle = opendir(PATH . '/public/exercices/' . $this->Exercice->LongHash . '/Corrige/Ressources');
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != "..")
			{
				$Ressources[] = $file;
			}
		}
		
		$this->View->Ressources = $Ressources;
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
			"Cette page permet de dialoguer avec l'élève pour éclaircir les points restés obscurs. Soyez clair et concis !"
		);
		
		//Enregistrer et récupérer les données
		parent::faqActionWd();
	}
	
	/**
	 * Liste les actions des exercices
	 */
	public function _actionsAction()
	{
		$this->ajax(
			'SELECT DATE_FORMAT(Date,"%d/%m/%y à %Hh"), CONCAT(Matiere, \' : <a href="/correcteur/exercice/index/\', Hash, \'">\', Titre, \'</a>\'), Action
			FROM Exercices_Logs
			LEFT JOIN Exercices ON (Exercices_Logs.Exercice = Exercices.ID)
			LEFT JOIN Membres ON (Membres.ID = Exercices_Logs.Membre)
			WHERE 
			(
				Exercices.Correcteur = ' . $_SESSION['Correcteur']->getFilteredId() . '
				AND NouveauStatut NOT IN("VIERGE", "ATTENTE_CORRECTEUR")
				AND
				(
					Membres.Type = "ELEVE"
					OR
					Membres.ID = ' . $_SESSION['Correcteur']->getFilteredId() . '
				)
			)'
		);
	}
	
	/**
	 * Liste les actions d'un exercice en particulier
	 */
	public function _actionsActionWd()
	{
		$this->ajax(
			'SELECT DATE_FORMAT(Date,"%d/%m/%y à %Hh"), Action
			FROM Exercices_Logs
			LEFT JOIN Exercices ON (Exercices_Logs.Exercice = Exercices.ID)
			WHERE 
			(
				Exercices_Logs.Membre = ' . $_SESSION['Correcteur']->getFilteredId() . '
				OR
				Exercices_Logs.Membre = Exercices.Createur
			)'
		);
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
		return (($Exercice->Correcteur == $_SESSION['Correcteur']->ID) || $Exercice->Statut == 'ATTENTE_CORRECTEUR');
	}
	
	/**
	 * Compile un document LaTeX en PDF. Le PDF se trouve dans le même dossier que le fichier TeX.
	 * 
	 * @param string $URL le fichier TeX à compiler.
	 * 
	 * @return array un tableau ; la clé output contient la liste des lignes renvoyées, la clé errors la liste des lignes d'erreurs, et la clé ok est un booléen indiquant le résultat de la compilation
	 */
	protected function compileTex($URL)
	{
		$OutputDir = substr($URL, 0, strrpos($URL, '/'));
		exec('/usr/bin/pdflatex -halt-on-error -interaction=nonstopmode -output-directory ' . escapeshellarg($OutputDir) . ' ' . escapeshellarg($URL));
		
		$Return = file(str_replace('.tex', '.log', $URL), FILE_IGNORE_NEW_LINES);
		$Erreurs = array();
		foreach($Return as $Line)
		{
			if(isset($Line[0]) && $Line[0] == '!')
			{
				$Erreurs[] = substr($Line, 2);
			}
		}
		
		return array(
			'errors' => $Erreurs,
			'output' => $Return,
			'ok' => empty($Erreurs),
		);
	}
	
	/**
	 * Construit un document TeX à partir d'un fichier générique.
	 * 
	 * @param string $Texte le texte de l'environnement {document}
	 * @param string $Fichier le contenu du fichier
	 */
	protected function texFromTemplate($Texte, $Fichier)
	{
		//Le template LaTeX générique
		$Template = file_get_contents(DATA_PATH . '/layouts/template.tex');
		
		//En déduire le contenu par remplacement :
		$Remplacements = array(
			'__TITRE__' => $this->Exercice->Titre,
			'__CONTENU__' => $Texte,
			'__GRAPHICS__' => PATH . '/public/exercices/' . $this->Exercice->LongHash . '/Corrige/Ressources/',
		);
		
		$Contenu = str_replace(array_keys($Remplacements), array_values($Remplacements), $Template);
		
		file_put_contents($Fichier, $Contenu);
	}
}