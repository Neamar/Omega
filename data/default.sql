-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Lun 20 Décembre 2010 à 12:46
-- Version du serveur: 5.1.41
-- Version de PHP: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `work`
--

-- --------------------------------------------------------

--
-- Structure de la table `Admins`
--

CREATE TABLE IF NOT EXISTS `Admins` (
  `ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `Admins`
--


-- --------------------------------------------------------

--
-- Structure de la table `Alertes`
--

CREATE TABLE IF NOT EXISTS `Alertes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Membre` int(11) DEFAULT NULL,
  `Exercice` int(2) DEFAULT NULL,
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
-- Structure de la table `Banque`
--

CREATE TABLE IF NOT EXISTS `Banque` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Date` datetime NOT NULL,
  `Solde` int(11) NOT NULL,
  `Action` varchar(50) NOT NULL,
  `Delta` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='La banque centrale et ses points' AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Banque`
--


-- --------------------------------------------------------

--
-- Structure de la table `Classes`
--

CREATE TABLE IF NOT EXISTS `Classes` (
  `ID` int(2) NOT NULL,
  `Nom` varchar(15) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Nom` (`Nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des années scolaires (avec ordre)';

--
-- Contenu de la table `Classes`
--

INSERT INTO `Classes` (`ID`, `Nom`) VALUES
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
  `Siret` int(14) DEFAULT NULL,
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
  `Commence` int(2) NOT NULL,
  `Finit` int(2) NOT NULL,
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
-- Structure de la table `Eleves`
--

CREATE TABLE IF NOT EXISTS `Eleves` (
  `ID` int(11) NOT NULL,
  `Classe` int(2) NOT NULL,
  `Section` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Classe` (`Classe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des élèves inscrits';

--
-- Contenu de la table `Eleves`
--

INSERT INTO `Eleves` (`ID`, `Classe`, `Section`) VALUES
(3, 1, 'ES'),
(4, 2, 'Littéraire'),
(15, 0, 'S');

-- --------------------------------------------------------

--
-- Structure de la table `Exercices`
--

CREATE TABLE IF NOT EXISTS `Exercices` (
  `ID` int(2) NOT NULL AUTO_INCREMENT,
  `Hash` varchar(6) NOT NULL,
  `Createur` int(11) NOT NULL,
  `IP` int(10) unsigned NOT NULL COMMENT 'Adresse IP du posteur',
  `Creation` datetime NOT NULL,
  `TimeoutEleve` datetime DEFAULT NULL,
  `Expiration` datetime NOT NULL,
  `Matiere` varchar(15) NOT NULL,
  `Classe` int(2) NOT NULL,
  `Section` varchar(20) DEFAULT NULL,
  `Type` varchar(15) NOT NULL,
  `Demande` enum('COMPLET','AIDE') NOT NULL,
  `InfosEleve` mediumtext,
  `Autoaccept` int(11) DEFAULT NULL,
  `Modificateur` int(11) NOT NULL DEFAULT '100',
  `Statut` varchar(20) DEFAULT NULL,
  `Correcteur` int(11) DEFAULT NULL,
  `TimeoutCorrecteur` int(11) DEFAULT NULL,
  `InfosCorrecteur` mediumtext,
  `Enchere` int(11) NOT NULL DEFAULT '0',
  `NbRefus` int(11) NOT NULL DEFAULT '0',
  `Remboursement` int(11) DEFAULT NULL,
  `Notation` int(11) DEFAULT NULL,
  `Retard` binary(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Hash` (`Hash`),
  KEY `Createur` (`Createur`),
  KEY `Correcteur` (`Correcteur`),
  KEY `Matiere` (`Matiere`),
  KEY `Classe` (`Classe`),
  KEY `Type` (`Type`),
  KEY `Statut` (`Statut`)
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
  `Exercice` int(2) NOT NULL,
  `Correcteur` int(11) NOT NULL,
  `Action` enum('VUE','ENCHERE','SIGNALEMENT') NOT NULL,
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
  `Exercice` int(2) NOT NULL,
  `Creation` date NOT NULL,
  `Texte` mediumtext NOT NULL,
  `Parent` int(11) NOT NULL,
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
  `Exercice` int(2) NOT NULL,
  `Type` enum('SUJET','CORRIGE','RECLAMATION') NOT NULL,
  `URL` varchar(50) NOT NULL,
  `NomUpload` varchar(255) NOT NULL COMMENT 'Nom original du fichier sur le disque dur de l''expéditeur',
  `Description` mediumtext NOT NULL,
  `OCR` mediumtext,
  PRIMARY KEY (`ID`),
  KEY `Exercice` (`Exercice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
  `Exercice` int(2) NOT NULL,
  `Membre` int(11) DEFAULT NULL,
  `Action` varchar(50) NOT NULL,
  `Statut` varchar(20) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Exercice` (`Exercice`),
  KEY `Membre` (`Membre`),
  KEY `Statut` (`Statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Toutes les actions ayant un impact sur un exercice' AUTO_INCREMENT=1 ;

--
-- Contenu de la table `Exercices_Logs`
--


-- --------------------------------------------------------

--
-- Structure de la table `Logs`
--

CREATE TABLE IF NOT EXISTS `Logs` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Date` datetime NOT NULL,
  `Membre` int(11) NOT NULL,
  `Exercice` int(2) DEFAULT NULL,
  `Action` varchar(50) NOT NULL,
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
  `Type` enum('ELEVE','CORRECTEUR','ADMIN') DEFAULT 'ELEVE',
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
  `Type` enum('ELEVE','CORRECTEUR','ADMIN') NOT NULL,
  `RIB` int(23) DEFAULT NULL,
  `Paypal` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Mail` (`Mail`,`Type`),
  KEY `Mail_2` (`Mail`,`Pass`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Contenu de la table `Membres`
--

INSERT INTO `Membres` (`ID`, `Mail`, `Pass`, `Points`, `Creation`, `Connexion`, `Statut`, `Type`, `RIB`, `Paypal`) VALUES
(3, 'neamar@neamar.fr', 'b3bbd55564e350cedca6f153c3e817ca5f2e25e1', 0, '2010-12-08 17:49:38', '2010-12-11 12:58:08', 'OK', 'ELEVE', NULL, NULL),
(4, 'essai@neamar.fr', '9fee891593f8c384cdb7e964a18ed1f20a48f787', 0, '2010-12-11 10:55:17', '2010-12-11 10:55:17', 'EN_ATTENTE', 'ELEVE', NULL, NULL),
(15, 'ok@neamar.fr', 'b3bbd55564e350cedca6f153c3e817ca5f2e25e1', 150, '2010-12-17 23:07:33', '2010-12-19 22:59:46', 'OK', 'ELEVE', NULL, NULL);

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
  PRIMARY KEY (`Statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Liste des statuts que peut prendre un exercice';

--
-- Contenu de la table `Statuts`
--

INSERT INTO `Statuts` (`Statut`) VALUES
('ANNULE'),
('ATTENTE_CORRECTEUR'),
('ATTENTE_ELEVE'),
('ENVOYE'),
('EN_COURS'),
('REFUSE'),
('REMBOURSE'),
('TERMINE'),
('VIERGE');

-- --------------------------------------------------------

--
-- Structure de la table `Types`
--

CREATE TABLE IF NOT EXISTS `Types` (
  `Type` varchar(15) NOT NULL,
  `Details` varchar(255) NOT NULL DEFAULT 'NULL',
  PRIMARY KEY (`Type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Types d''exercices : QCM, Exercice court...';

--
-- Contenu de la table `Types`
--

INSERT INTO `Types` (`Type`, `Details`) VALUES
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


--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `Admins`
--
ALTER TABLE `Admins`
  ADD CONSTRAINT `Admins_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `Membres` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Alertes`
--
ALTER TABLE `Alertes`
  ADD CONSTRAINT `Alertes_ibfk_1` FOREIGN KEY (`Membre`) REFERENCES `Membres` (`ID`),
  ADD CONSTRAINT `Alertes_ibfk_2` FOREIGN KEY (`Exercice`) REFERENCES `Exercices` (`ID`),
  ADD CONSTRAINT `Alertes_ibfk_3` FOREIGN KEY (`FAQ`) REFERENCES `Exercices_FAQ` (`ID`);

--
-- Contraintes pour la table `Correcteurs`
--
ALTER TABLE `Correcteurs`
  ADD CONSTRAINT `Correcteurs_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `Membres` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Correcteurs_Capacites`
--
ALTER TABLE `Correcteurs_Capacites`
  ADD CONSTRAINT `Correcteurs_Capacites_ibfk_1` FOREIGN KEY (`Correcteur`) REFERENCES `Correcteurs` (`ID`),
  ADD CONSTRAINT `Correcteurs_Capacites_ibfk_2` FOREIGN KEY (`Matiere`) REFERENCES `Matieres` (`Matiere`),
  ADD CONSTRAINT `Correcteurs_Capacites_ibfk_3` FOREIGN KEY (`Commence`) REFERENCES `Classes` (`ID`),
  ADD CONSTRAINT `Correcteurs_Capacites_ibfk_4` FOREIGN KEY (`Finit`) REFERENCES `Classes` (`ID`);

--
-- Contraintes pour la table `Eleves`
--
ALTER TABLE `Eleves`
  ADD CONSTRAINT `Eleves_ibfk_2` FOREIGN KEY (`Classe`) REFERENCES `Classes` (`ID`),
  ADD CONSTRAINT `Eleves_ibfk_3` FOREIGN KEY (`ID`) REFERENCES `Membres` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `Exercices`
--
ALTER TABLE `Exercices`
  ADD CONSTRAINT `Exercices_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `Classes` (`ID`),
  ADD CONSTRAINT `Exercices_ibfk_2` FOREIGN KEY (`Createur`) REFERENCES `Eleves` (`ID`),
  ADD CONSTRAINT `Exercices_ibfk_3` FOREIGN KEY (`Matiere`) REFERENCES `Matieres` (`Matiere`),
  ADD CONSTRAINT `Exercices_ibfk_4` FOREIGN KEY (`Classe`) REFERENCES `Classes` (`ID`),
  ADD CONSTRAINT `Exercices_ibfk_5` FOREIGN KEY (`Type`) REFERENCES `Types` (`Type`),
  ADD CONSTRAINT `Exercices_ibfk_6` FOREIGN KEY (`Statut`) REFERENCES `Statuts` (`Statut`),
  ADD CONSTRAINT `Exercices_ibfk_7` FOREIGN KEY (`Correcteur`) REFERENCES `Correcteurs` (`ID`);

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
-- Contraintes pour la table `Exercices_Logs`
--
ALTER TABLE `Exercices_Logs`
  ADD CONSTRAINT `Exercices_Logs_ibfk_1` FOREIGN KEY (`Exercice`) REFERENCES `Exercices` (`ID`),
  ADD CONSTRAINT `Exercices_Logs_ibfk_2` FOREIGN KEY (`Membre`) REFERENCES `Membres` (`ID`),
  ADD CONSTRAINT `Exercices_Logs_ibfk_3` FOREIGN KEY (`Statut`) REFERENCES `Statuts` (`Statut`);

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
