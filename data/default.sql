INSERT INTO `Classes` (`ID`, `Nom`) VALUES
(-1, 'Post-bac'),
(0, 'Terminale'),
(1, 'Première'),
(2, 'Seconde'),
(3, 'Troisième'),
(4, 'Quatrième'),
(5, 'Cinquième'),
(6, 'Sixième');

INSERT INTO `Statuts` (`Statut`) VALUES
('ANNULÉ'),
('ATTENTE_CORRECTEUR'),
('ATTENTE_ÉLÈVE'),
('ENVOYÉ'),
('EN_COURS'),
('REFUSÉ'),
('REMBOURSÉ'),
('TERMINÉ'),
('VIERGE');

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
