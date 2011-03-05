-- phpMyAdministrateur SQL Dump
-- version 3.2.4
-- http://www.phpmyAdministrateur.net
--
-- Serveur: localhost
-- Généré le : Jeu 23 Décembre 2010 à 12:39
-- Version du serveur: 5.1.41
-- Version de PHP: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `work`
--

-- --------------------------------------------------------

--
-- Structure de la table `Administrateurs`
--

CREATE TABLE IF NOT EXISTS `Administrateurs` (
  `ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `Administrateurs`
--


-- --------------------------------------------------------

--
-- Structure de la table `Alertes`
--

CREATE TABLE IF NOT EXISTS `Alertes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Membre` int(11) DEFAULT NULL,
  `Exercice` int(11) DEFAULT NULL,
  `FAQ` int(11) DEFAULT NULL,
  `Texte` mediumtext NOT NULL,
  `Remarque` varchar(200) DEFAULT NULL,
  `Eleve` int(11) DEFAULT NULL,
  `Statut` enum('ATTENTE','FAUX_POSITIF','MOUAIS','BLOQUAGE') NOT NULL DEFAULT 'ATTENTE',
  PRIMARY KEY (`ID`),
  KEY `Membre` (`Membre`),
  KEY `Exercice` (`Exercice`),
  KEY `FAQ` (`FAQ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des alertes "divulgation d''information" détectées auto' AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Alertes`
--

-- --------------------------------------------------------

--
-- Structure de la table `Blog_Articles`
--

CREATE TABLE `work`.`Blog_Articles` (
`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`Auteur` INT NOT NULL ,
`Creation` DATETIME NOT NULL ,
`Titre` TINYTEXT NOT NULL ,
`Abstract` MEDIUMTEXT NOT NULL ,
`Article` TEXT NOT NULL ,
INDEX ( `Auteur` )
) ENGINE = InnoDB COMMENT = 'Articles du blog';

--
-- Contenu de la table `Blog_Articles`
--

INSERT INTO `Blog_Articles` (`ID`, `Auteur`, `Creation`, `Titre`, `Abstract`, `Article`) VALUES
(1, 4, '2011-03-07 00:00:00', 'eDevoir ?', 'Présentation de la société \\eDevoir et du site associé, \\l[http://edevoir.com]{edevoir.com}.', '\\eDevoir est une société française créée en 2011 et ayant pour but l''aide aux devoirs et la réalisation d''écrits à destination d''élèves et d''étudiants, quel que soit leur niveau. Notre site internet met en relation des élèves, étudiants et parents désireux d''être épaulés dans la réalisation de devoirs scolaires ou projets, avec des personnes compétentes susceptibles de répondre à ce besoin.\r\n\r\nIl incombe à chaque élève de doser sa charge de travail pour équilibrer "temps passé" et "compréhension du sujet". Et dans certains cas, il faut reconnaître que les exigences professorales sont entièrement déconnectées de la réalité ; que ce soit une surcharge de travail permanente ou uniquement à l''approche des vacances. En conséquence, nous nous proposons de décharger l''élève des tâches qu''il juge inutiles afin de lui permettre de se focaliser sur les matières ou exercices qu''il juge plus importants.\r\nMais \\eDevoir, ce n''est pas uniquement cela. À l''approche d''un devoir sur table ou du BAC, les rendus proposées par notre équipe compétentes peuvent également supplanter des corrections parfois douteuses fournies par certains professeurs et permettre aux élèves d''avoir un support solide sur lequel travailler.\r\nCes corrections s''adressent également aux parents qui se trouve en demeure devant l''exercice d''un enfant. En effet, le parent d''un lycéen de terminale S n''aura pas toujours eu une formation scientifique permettant d''aider son enfant pour la réalisation d''un devoir maison ou autre, et pourra demander sur notre site à ce que ce travail soit réalisé rapidement et proprement.\r\n\r\nNous nous plaçons ainsi en "complément" du système scolaire (niveaux collège et lycée), pour permettre à chacun d''adapter sa charge de travail à son potentiel. Cette société se veut à la fois être une alternative rapide et efficace à des obligations scolaires et une aide précieuse pour des parents désireux d''aider leur(s) enfant(s),et qui ne sont pas toujours aptes à le faire.\r\n\r\nLe principe se veut le plus novateur et égalitaire possible. En effet, ce n''est pas le site internet qui fixe les prix, ni les élèves. C''est le correcteur, qui, en toute connaissance du sujet qui lui est soumis, évalue la quantité de travail à fournir et donc le prix pour ce travail. Lorsque celui-ci a statué sur la somme qu''il souhaite pour ce travail, il soumet -- via notre site internet -- sa proposition à l''élève (étudiant). Si celui-ci accepte, la procédure est lancée.\r\n\r\n\\textbf{Cette société n''est pas une alternative à l''éducation professorale ou parentale}, mais véritablement une aide pour les élèves, étudiants et parents en demeure devant un problème de mathématiques incompréhensible. Elle ne se substitue ni au travail personnel des élèves, ni au devoir parental mais est, dans des proportions raisonnables d''utilisation, une alternative plus pédagogique que les divers processus de "triche" (plagiat sur internet, chez un camarade etc.) auxquels s''adonnent les élèves lorsqu''ils ne veulent pas se soumettre à un devoir. \\textbf{Ce que nous proposons à nos interlocuteurs, c''est un rendu professionnel et un contenu de qualité, réalisé par des gens compétents dans leurs domaines}.');

-- --------------------------------------------------------

--
-- Structure de la table `Classes`
--

CREATE TABLE IF NOT EXISTS `Classes` (
  `Classe` int(11) NOT NULL,
  `DetailsClasse` varchar(15) NOT NULL,
  PRIMARY KEY (`Classe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des années scolaires (avec ordre)';

--
-- Contenu de la table `Classes`
--

INSERT INTO `Classes` (`Classe`, `DetailsClasse`) VALUES
(5, 'Cinquième'),
(-1, 'Post-bac'),
(1, 'Première'),
(4, 'Quatrième'),
(2, 'Seconde'),
(6, 'Sixième'),
(0, 'Terminale'),
(3, 'Troisième');

-- --------------------------------------------------------

--
-- Structure de la table `Correcteurs`
--

CREATE TABLE IF NOT EXISTS `Correcteurs` (
  `ID` int(11) NOT NULL,
  `Prenom` varchar(50) NOT NULL,
  `Nom` varchar(50) NOT NULL,
  `Telephone` varchar(10) NOT NULL,
  `Siret` varchar(14) DEFAULT NULL,
  `SiretOK` binary(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Siret` (`Siret`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des correcteurs';

--
-- Contenu de la table `Correcteurs`
--


-- --------------------------------------------------------

--
-- Structure de la table `Correcteurs_Capacites`
--

CREATE TABLE IF NOT EXISTS `Correcteurs_Capacites` (
  `Correcteur` int(11) NOT NULL,
  `Matiere` varchar(15) NOT NULL,
  `Commence` int(11) NOT NULL,
  `Finit` int(11) NOT NULL,
  PRIMARY KEY (`Correcteur`,`Matiere`),
  KEY `Matiere` (`Matiere`),
  KEY `Commence` (`Commence`),
  KEY `Finit` (`Finit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Les capacités de chaque correcteur';

--
-- Contenu de la table `Correcteurs_Capacites`
--


-- --------------------------------------------------------

--
-- Structure de la table `Demandes`
--

CREATE TABLE IF NOT EXISTS `Demandes` (
  `Demande` varchar(10) CHARACTER SET utf8 NOT NULL,
  `DetailsDemande` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`Demande`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Demandes possibles pour un exercice';

--
-- Contenu de la table `Demandes`
--

INSERT INTO `Demandes` (`Demande`, `DetailsDemande`) VALUES
('AIDE', 'Pistes de résolution'),
('COMPLET', 'Corrigé complet');

-- --------------------------------------------------------

--
-- Structure de la table `Eleves`
--

CREATE TABLE IF NOT EXISTS `Eleves` (
  `ID` int(11) NOT NULL,
  `Classe` int(11) NOT NULL,
  `Section` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Classe` (`Classe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des élèves inscrits';

--
-- Contenu de la table `Eleves`
--


-- --------------------------------------------------------

--
-- Structure de la table `Exercices`
--

CREATE TABLE IF NOT EXISTS `Exercices` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Hash` varchar(6) NOT NULL,
  `LongHash` varchar(40) NOT NULL,
  `Titre` varchar(50) NOT NULL,
  `Createur` int(11) NOT NULL,
  `IP` int(10) unsigned NOT NULL COMMENT 'Adresse IP du posteur',
  `Creation` datetime NOT NULL,
  `TimeoutEleve` datetime DEFAULT NULL,
  `Expiration` datetime NOT NULL,
  `Matiere` varchar(15) NOT NULL,
  `Classe` int(11) NOT NULL,
  `Section` varchar(20) DEFAULT NULL,
  `Type` varchar(15) NOT NULL,
  `Demande` varchar(10) NOT NULL,
  `InfosEleve` mediumtext,
  `AutoAccept` int(11) DEFAULT NULL,
  `Modificateur` int(11) NOT NULL DEFAULT '100',
  `Statut` varchar(20) NOT NULL DEFAULT 'VIERGE',
  `Correcteur` int(11) DEFAULT NULL,
  `TimeoutCorrecteur` datetime DEFAULT NULL,
  `InfosCorrecteur` mediumtext,
  `Enchere` int(11) NOT NULL DEFAULT '0',
  `NbRefus` int(11) NOT NULL DEFAULT '0',
  `Reclamation` enum('NON_PAYE','REMBOURSEMENT','CONTESTATION','RETARD') DEFAULT NULL COMMENT 'La raison du remboursement. NON_PAYE : envoi gratuit',
  `InfosReclamation` mediumtext,
  `Remboursement` int(11) DEFAULT NULL,
  `Note` int(11) DEFAULT NULL,
  `InfosNote` mediumtext,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Hash` (`Hash`),
  KEY `Createur` (`Createur`),
  KEY `Correcteur` (`Correcteur`),
  KEY `Matiere` (`Matiere`),
  KEY `Classe` (`Classe`),
  KEY `Type` (`Type`),
  KEY `Statut` (`Statut`),
  KEY `TimeoutEleve` (`TimeoutEleve`),
  KEY `Expiration` (`Expiration`),
  KEY `TimeoutCorrecteur` (`TimeoutCorrecteur`),
  KEY `Demande` (`Demande`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des exercices' AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Exercices`
--


-- --------------------------------------------------------

--
-- Structure de la table `Exercices_Correcteurs`
--

CREATE TABLE IF NOT EXISTS `Exercices_Correcteurs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Exercice` int(11) NOT NULL,
  `Correcteur` int(11) NOT NULL,
  `Action` enum('ENCHERE','SIGNALEMENT') NOT NULL,
  `Offre` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Correcteur` (`Correcteur`),
  KEY `Exercice` (`Exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Quel correcteur a vu quel exercice ?' AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Exercices_Correcteurs`
--


-- --------------------------------------------------------

--
-- Structure de la table `Exercices_FAQ`
--

CREATE TABLE IF NOT EXISTS `Exercices_FAQ` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Exercice` int(11) NOT NULL,
  `Creation` datetime NOT NULL,
  `Texte` mediumtext NOT NULL,
  `Parent` int(11) DEFAULT NULL,
  `Statut` enum('OK','HORS_SUJET','REPETITION','AUTRE_EXERCICE','PAS_COMPRIS') NOT NULL DEFAULT 'OK',
  `Membre` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Exercice` (`Exercice`),
  KEY `Membre` (`Membre`),
  KEY `Parent` (`Parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Exercices_FAQ`
--


-- --------------------------------------------------------

--
-- Structure de la table `Exercices_Fichiers`
--

CREATE TABLE IF NOT EXISTS `Exercices_Fichiers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Exercice` int(11) NOT NULL,
  `Type` enum('SUJET','CORRIGE','RECLAMATION') NOT NULL,
  `URL` varchar(80) NOT NULL,
  `ThumbURL` varchar(80) NOT NULL,
  `NomUpload` varchar(255) NOT NULL COMMENT 'Nom original du fichier sur le disque dur de l''expéditeur',
  `Description` mediumtext NOT NULL,
  `OCR` mediumtext NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Exercice` (`Exercice`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=55 ;

--
-- Contenu de la table `Exercices_Fichiers`
--


-- --------------------------------------------------------

--
-- Structure de la table `Exercices_Logs`
--

CREATE TABLE IF NOT EXISTS `Exercices_Logs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Date` datetime NOT NULL,
  `Exercice` int(11) NOT NULL,
  `Membre` int(11) DEFAULT NULL,
  `Action` varchar(70) NOT NULL,
  `AncienStatut` varchar(20) DEFAULT NULL,
  `NouveauStatut` varchar(20) NOT NULL,
  `Correcteur` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Exercice` (`Exercice`),
  KEY `Membre` (`Membre`),
  KEY `AncienStatut` (`AncienStatut`),
  KEY `NouveauStatut` (`NouveauStatut`),
  KEY `Correcteur` (`Correcteur`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Toutes les actions ayant un impact sur un exercice' AUTO_INCREMENT=1 ;
--
-- Contenu de la table `Exercices_Logs`
--


-- --------------------------------------------------------

--
-- Structure de la table `Exercices_Corriges`
--

CREATE TABLE IF NOT EXISTS `Exercices_Corriges` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Exercice` int(2) NOT NULL,
  `Date` datetime NOT NULL,
  `Contenu` text NOT NULL,
  `Longueur` int(5) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Date` (`Date`),
  KEY `Exercice` (`Exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Sauvegardes des corrigés TeX.' AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Exercices_Corriges`
--


-- --------------------------------------------------------

--
-- Structure de la table `Logs`
--

CREATE TABLE IF NOT EXISTS `Logs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Date` datetime NOT NULL,
  `Membre` int(11) NOT NULL,
  `Exercice` int(11) DEFAULT NULL,
  `Action` varchar(200) NOT NULL,
  `Delta` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Membre` (`Membre`),
  KEY `Exercice` (`Exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Toutes les actions ayant un impact sur le solde d''un membre' AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Logs`
--


-- --------------------------------------------------------

--
-- Structure de la table `Mails`
--

CREATE TABLE IF NOT EXISTS `Mails` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Type` enum('ELEVE','CORRECTEUR','Administrateur') DEFAULT 'ELEVE',
  `Description` varchar(255) NOT NULL,
  `Defaut` binary(1) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Les différents types de mails' AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Mails`
--


-- --------------------------------------------------------

--
-- Structure de la table `Matieres`
--

CREATE TABLE IF NOT EXISTS `Matieres` (
  `Matiere` varchar(35) NOT NULL,
  PRIMARY KEY (`Matiere`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des matières supportées';

--
-- Contenu de la table `Matieres`
--

INSERT INTO `Matieres` (`Matiere`) VALUES
('Allemand'),
('Anglais'),
('Autre langue vivante'),
('Chimie'),
('Droit'),
('Éducation civique'),
('Espagnol'),
('Français'),
('Géographie'),
('Grec'),
('Histoire'),
('Informatique'),
('Italien'),
('Latin'),
('Mathématiques'),
('Médecine'),
('Philosophie'),
('Physique'),
('Sciences de l''ingénieur'),
('Sciences de la Vie et de la Terre'),
('Sciences économiques et sociales');

-- --------------------------------------------------------

--
-- Structure de la table `Membres`
--

CREATE TABLE IF NOT EXISTS `Membres` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Mail` varchar(50) NOT NULL,
  `Pass` varchar(40) DEFAULT NULL,
  `Points` int(11) NOT NULL DEFAULT '0',
  `Creation` datetime NOT NULL,
  `Connexion` datetime NOT NULL,
  `Statut` enum('EN_ATTENTE','OK','BLOQUE','DESINSCRIT') NOT NULL DEFAULT 'EN_ATTENTE',
  `Type` enum('ELEVE','CORRECTEUR','ADMINISTRATEUR') NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Mail` (`Mail`),
  KEY `Login` (`Mail`,`Pass`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Membres`
--


-- --------------------------------------------------------

--
-- Structure de la table `Membres_Mails`
--

CREATE TABLE IF NOT EXISTS `Membres_Mails` (
  `Membre` int(11) NOT NULL,
  `Mail` int(11) NOT NULL,
  `Valeur` binary(1) NOT NULL,
  PRIMARY KEY (`Membre`,`Mail`),
  KEY `Mail` (`Mail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Quels mails recevoir ?';

--
-- Contenu de la table `Membres_Mails`
--


-- --------------------------------------------------------

--
-- Structure de la table `Statuts`
--

CREATE TABLE IF NOT EXISTS `Statuts` (
  `Statut` varchar(20) NOT NULL,
  `DetailsStatut` varchar(255) NOT NULL,
  PRIMARY KEY (`Statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des statuts que peut prendre un exercice';

--
-- Contenu de la table `Statuts`
--

INSERT INTO `Statuts` (`Statut`, `DetailsStatut`) VALUES
('ANNULE', 'Annulé'),
('ATTENTE_CORRECTEUR', 'En attente d''un correcteur'),
('ATTENTE_ELEVE', 'En attente d''une réponse de l''élève'),
('ENVOYE', 'Rendu envoyé à l''élève, en attente de sa réaction'),
('EN_COURS', 'Un correcteur travaille actuellement sur le sujet.'),
('REFUSE', 'Un administrateur examine actuellement le litige.'),
('REMBOURSE', 'L''exercice a été remboursé à l''élève.'),
('TERMINE', 'L''exercice est clos.'),
('VIERGE', 'En attente des fichiers élèves.');

-- --------------------------------------------------------

--
-- Structure de la table `Types`
--

CREATE TABLE IF NOT EXISTS `Types` (
  `Type` varchar(15) NOT NULL,
  `DetailsType` varchar(255) NOT NULL DEFAULT 'NULL',
  PRIMARY KEY (`Type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Types d''exercices : QCM, Exercice court...';

--
-- Contenu de la table `Types`
--

INSERT INTO `Types` (`Type`, `DetailsType`) VALUES
('CORRECTION', 'Correction de devoir'),
('COURS', 'Question de cours'),
('DM', 'Devoir Maison'),
('EXO_COURT', 'Exercice court (un quart d''heure ou moins)'),
('EXO_LONG', 'Exercice long'),
('EXO_TROU', 'Exercice à trous'),
('QCM', 'Questionnaire à Choix multiples justifié'),
('QCM_NJ', 'Questionnaire à Choix multiples non justifié'),
('THEORIE', 'Question théorique');

-- --------------------------------------------------------

--
-- Structure de la table `Virements`
--

CREATE TABLE IF NOT EXISTS `Virements` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Membre` int(11) DEFAULT NULL,
  `Date` datetime NOT NULL,
  `Montant` int(11) NOT NULL,
  `Type` enum('PAYPAL','RIB') NOT NULL,
  `Beneficiaire` varchar(30) DEFAULT NULL,
  `Statut` enum('INDETERMINE','TRAITE') NOT NULL DEFAULT 'INDETERMINE',
  `Traitement` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Membre` (`Membre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des virements à effectuer' AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Virements`
--

-- --------------------------------------------------------

--
-- Structure de la table `Entrees`
--

CREATE TABLE IF NOT EXISTS `Entrees` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Membre` int(11) NOT NULL,
  `Montant` int(11) NOT NULL,
  `Date` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Membre` (`Membre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Liste des entrées d''argent' AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Entrees`
--

-- --------------------------------------------------------

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `Administrateurs`
--
ALTER TABLE `Administrateurs`
  ADD CONSTRAINT `Administrateurs_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `Membres` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Alertes`
--
ALTER TABLE `Alertes`
  ADD CONSTRAINT `Alertes_ibfk_1` FOREIGN KEY (`Membre`) REFERENCES `Membres` (`ID`),
  ADD CONSTRAINT `Alertes_ibfk_2` FOREIGN KEY (`Exercice`) REFERENCES `Exercices` (`ID`),
  ADD CONSTRAINT `Alertes_ibfk_3` FOREIGN KEY (`FAQ`) REFERENCES `Exercices_FAQ` (`ID`);

--
-- Contraintes pour la table `Blog_Articles`
--
ALTER TABLE `Blog_Articles` ADD FOREIGN KEY ( `Auteur` ) REFERENCES `work`.`Administrateurs` (
`ID`
);

--
-- Contraintes pour la table `Correcteurs`
--
ALTER TABLE `Correcteurs`
  ADD CONSTRAINT `Correcteurs_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `Membres` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Correcteurs_Capacites`
--
ALTER TABLE `Correcteurs_Capacites`
  ADD CONSTRAINT `Correcteurs_Capacites_ibfk_4` FOREIGN KEY (`Finit`) REFERENCES `Classes` (`Classe`),
  ADD CONSTRAINT `Correcteurs_Capacites_ibfk_1` FOREIGN KEY (`Correcteur`) REFERENCES `Correcteurs` (`ID`),
  ADD CONSTRAINT `Correcteurs_Capacites_ibfk_2` FOREIGN KEY (`Matiere`) REFERENCES `Matieres` (`Matiere`),
  ADD CONSTRAINT `Correcteurs_Capacites_ibfk_3` FOREIGN KEY (`Commence`) REFERENCES `Classes` (`Classe`);

--
-- Contraintes pour la table `Eleves`
--
ALTER TABLE `Eleves`
  ADD CONSTRAINT `Eleves_ibfk_4` FOREIGN KEY (`Classe`) REFERENCES `Classes` (`Classe`),
  ADD CONSTRAINT `Eleves_ibfk_3` FOREIGN KEY (`ID`) REFERENCES `Membres` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Exercices`
--
ALTER TABLE `Exercices`
  ADD CONSTRAINT `Exercices_ibfk_10` FOREIGN KEY (`Demande`) REFERENCES `Demandes` (`Demande`),
  ADD CONSTRAINT `Exercices_ibfk_2` FOREIGN KEY (`Createur`) REFERENCES `Eleves` (`ID`),
  ADD CONSTRAINT `Exercices_ibfk_3` FOREIGN KEY (`Matiere`) REFERENCES `Matieres` (`Matiere`),
  ADD CONSTRAINT `Exercices_ibfk_6` FOREIGN KEY (`Statut`) REFERENCES `Statuts` (`Statut`),
  ADD CONSTRAINT `Exercices_ibfk_7` FOREIGN KEY (`Correcteur`) REFERENCES `Correcteurs` (`ID`),
  ADD CONSTRAINT `Exercices_ibfk_8` FOREIGN KEY (`Type`) REFERENCES `Types` (`Type`),
  ADD CONSTRAINT `Exercices_ibfk_9` FOREIGN KEY (`Classe`) REFERENCES `Classes` (`Classe`);

--
-- Contraintes pour la table `Exercices_Correcteurs`
--
ALTER TABLE `Exercices_Correcteurs`
  ADD CONSTRAINT `Exercices_Correcteurs_ibfk_2` FOREIGN KEY (`Correcteur`) REFERENCES `Correcteurs` (`ID`),
  ADD CONSTRAINT `Exercices_Correcteurs_ibfk_3` FOREIGN KEY (`Exercice`) REFERENCES `Exercices` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Exercices_FAQ`
--
ALTER TABLE `Exercices_FAQ`
  ADD CONSTRAINT `Exercices_FAQ_ibfk_3` FOREIGN KEY (`Membre`) REFERENCES `Membres` (`ID`),
  ADD CONSTRAINT `Exercices_FAQ_ibfk_4` FOREIGN KEY (`Exercice`) REFERENCES `Exercices` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Exercices_FAQ_ibfk_5` FOREIGN KEY (`Parent`) REFERENCES `Exercices_FAQ` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Exercices_Fichiers`
--
ALTER TABLE `Exercices_Fichiers`
  ADD CONSTRAINT `Exercices_Fichiers_ibfk_1` FOREIGN KEY (`Exercice`) REFERENCES `Exercices` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Exercices_Corriges`
--
ALTER TABLE `Exercices_Corriges`
  ADD CONSTRAINT `Exercices_Corriges_ibfk_1` FOREIGN KEY (`Exercice`) REFERENCES `Exercices` (`ID`);

--
-- Contraintes pour la table `Exercices_Logs`
--
ALTER TABLE `Exercices_Logs`
  ADD CONSTRAINT `Exercices_Logs_ibfk_5` FOREIGN KEY (`Correcteur`) REFERENCES `Correcteurs` (`ID`),
  ADD CONSTRAINT `Exercices_Logs_ibfk_4` FOREIGN KEY (`AncienStatut`) REFERENCES `Statuts` (`Statut`),
  ADD CONSTRAINT `Exercices_Logs_ibfk_1` FOREIGN KEY (`Exercice`) REFERENCES `Exercices` (`ID`),
  ADD CONSTRAINT `Exercices_Logs_ibfk_2` FOREIGN KEY (`Membre`) REFERENCES `Membres` (`ID`),
  ADD CONSTRAINT `Exercices_Logs_ibfk_3` FOREIGN KEY (`NouveauStatut`) REFERENCES `Statuts` (`Statut`);

--
-- Contraintes pour la table `Logs`
--
ALTER TABLE `Logs`
  ADD CONSTRAINT `Logs_ibfk_1` FOREIGN KEY (`Membre`) REFERENCES `Membres` (`ID`),
  ADD CONSTRAINT `Logs_ibfk_2` FOREIGN KEY (`Exercice`) REFERENCES `Exercices` (`ID`);

--
-- Contraintes pour la table `Membres_Mails`
--
ALTER TABLE `Membres_Mails`
  ADD CONSTRAINT `Membres_Mails_ibfk_1` FOREIGN KEY (`Membre`) REFERENCES `Membres` (`ID`),
  ADD CONSTRAINT `Membres_Mails_ibfk_2` FOREIGN KEY (`Mail`) REFERENCES `Mails` (`ID`);

--
-- Contraintes pour la table `Virements`
--
ALTER TABLE `Virements`
  ADD CONSTRAINT `Virements_ibfk_1` FOREIGN KEY (`Membre`) REFERENCES `Membres` (`ID`);
  
--
-- Contraintes pour la table `Entrees`
--
ALTER TABLE `Entrees`
  ADD CONSTRAINT `Entrees_ibfk_1` FOREIGN KEY (`Membre`) REFERENCES `Membres` (`ID`);
 
--
-- Valeurs par défaut
--

INSERT INTO `Membres` (`ID`, `Mail`, `Pass`, `Points`, `Creation`, `Connexion`, `Statut`, `Type`) VALUES
(1, 'Banque', NULL, 0, '2011-01-22 12:00:00', '2011-01-22 12:00:00', 'OK', 'Administrateur'),
(2, 'eleve@neamar.fr', 'b3bbd55564e350cedca6f153c3e817ca5f2e25e1', 0, '2011-01-18 13:44:26', '2011-01-18 13:44:53', 'OK', 'ELEVE'),
(3, 'correcteur@neamar.fr', 'b3bbd55564e350cedca6f153c3e817ca5f2e25e1', 0, '2011-01-18 13:45:51', '2011-01-18 13:49:24', 'OK', 'CORRECTEUR'),
(4, 'admin@neamar.fr', 'b3bbd55564e350cedca6f153c3e817ca5f2e25e1', 0, '2011-01-18 13:45:51', '2011-01-18 13:49:24', 'OK', 'ADMINISTRATEUR');
INSERT INTO `Eleves` (`ID`, `Classe`, `Section`) VALUES
(2, 2, 'ES');
INSERT INTO `Correcteurs` (`ID`, `Prenom`, `Nom`, `Telephone`, `Siret`, `SiretOK`) VALUES
(3, 'Matthieu', 'Bacconnier', '0669347015', NULL, '0');
INSERT INTO `Administrateurs` (`ID`) VALUES (4);

