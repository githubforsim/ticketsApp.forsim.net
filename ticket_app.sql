-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 23 mai 2024 à 09:30
-- Version du serveur : 5.7.40
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ticket_app`
--

-- --------------------------------------------------------

--
-- Structure de la table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
CREATE TABLE IF NOT EXISTS `attachments` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  PRIMARY KEY (`attachment_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `attachments`
--

INSERT INTO `attachments` (`attachment_id`, `ticket_id`, `filename`) VALUES
(3, 9, 'ticketsApp/app/src/Controllers/../upload/Sans titre9.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

DROP TABLE IF EXISTS `produit`;
CREATE TABLE IF NOT EXISTS `produit` (
  `produit_id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_produit` varchar(255) NOT NULL,
  PRIMARY KEY (`produit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`produit_id`, `nom_produit`) VALUES
(2, 'Produit A'),
(3, 'Produit B');

-- --------------------------------------------------------

--
-- Structure de la table `statut`
--

DROP TABLE IF EXISTS `statut`;
CREATE TABLE IF NOT EXISTS `statut` (
  `statut_id` int(11) NOT NULL AUTO_INCREMENT,
  `valeur` varchar(50) NOT NULL,
  PRIMARY KEY (`statut_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `statut`
--

INSERT INTO `statut` (`statut_id`, `valeur`) VALUES
(1, 'En cours'),
(2, 'En attente validation client'),
(3, 'Réalisée');

-- --------------------------------------------------------

--
-- Structure de la table `ticket`
--

DROP TABLE IF EXISTS `ticket`;
CREATE TABLE IF NOT EXISTS `ticket` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_creation` datetime NOT NULL,
  `statut_id` int(11) DEFAULT NULL,
  `urgence_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`ticket_id`),
  KEY `statut_id` (`statut_id`),
  KEY `urgence_id` (`urgence_id`),
  KEY `username` (`username`),
  KEY `produit_id` (`produit_id`),
  KEY `type_id` (`type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `ticket`
--

INSERT INTO `ticket` (`ticket_id`, `titre`, `description`, `date_creation`, `statut_id`, `urgence_id`, `username`, `produit_id`, `type_id`) VALUES
(9, 'azeaze', 'azeaze', '2024-05-23 00:00:00', 1, 3, 'UserTest', 3, 3);

-- --------------------------------------------------------

--
-- Structure de la table `type`
--

DROP TABLE IF EXISTS `type`;
CREATE TABLE IF NOT EXISTS `type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_type` varchar(50) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `type`
--

INSERT INTO `type` (`type_id`, `nom_type`) VALUES
(2, 'Correction'),
(3, 'Evolution');

-- --------------------------------------------------------

--
-- Structure de la table `urgence`
--

DROP TABLE IF EXISTS `urgence`;
CREATE TABLE IF NOT EXISTS `urgence` (
  `urgence_id` int(11) NOT NULL AUTO_INCREMENT,
  `niveau` varchar(50) NOT NULL,
  PRIMARY KEY (`urgence_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `urgence`
--

INSERT INTO `urgence` (`urgence_id`, `niveau`) VALUES
(2, 'Normale'),
(3, 'Urgente');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `username` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `entreprise` varchar(255) NOT NULL,
  `pwd` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`username`, `mail`, `entreprise`, `pwd`, `role`) VALUES
('Frederic', 'frederic.zitta@forsim.net', 'FORSIM', '$2y$10$MjK05Z1TW0PkgNZma/CGh.AOpcmaxCQ2726tMgyZ1/N9mVDDkz77a', 'admin'),
('UserTest', 'usertest@gmail.com', 'FORSIM', '$2y$10$zesLGskNCKVPhbtj7JPnzeCJ2Bsd0d1RF5f1vgMwQnNHK/QpALRNe', 'user');

-- --------------------------------------------------------

--
-- Structure de la table `user_produit`
--

DROP TABLE IF EXISTS `user_produit`;
CREATE TABLE IF NOT EXISTS `user_produit` (
  `username` varchar(255) NOT NULL,
  `produit_id` int(11) NOT NULL,
  PRIMARY KEY (`username`,`produit_id`),
  KEY `produit_id` (`produit_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `user_produit`
--

INSERT INTO `user_produit` (`username`, `produit_id`) VALUES
('UserTest', 3);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
