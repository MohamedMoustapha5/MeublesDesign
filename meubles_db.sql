-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Mer 11 Février 2026 à 02:49
-- Version du serveur :  5.6.17
-- Version de PHP :  5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `meubles_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `sujet` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `date_envoi` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lu` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `messages`
--

INSERT INTO `messages` (`id`, `nom`, `email`, `sujet`, `contenu`, `date_envoi`, `lu`, `created_at`) VALUES
(1, 'din', 'user@gmail.com', 'bizzazrement', 'bla bla bla*\r\n', '2026-02-11 00:08:12', 1, '2026-02-11 02:22:07'),
(2, 'din', 'user@gmail.com', 'bizzazrement', 'yo.', '2026-02-11 01:19:20', 0, '2026-02-11 02:22:07');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'En attente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `created_at`) VALUES
(1, 1, '705.00', 'En attente', '2026-02-11 00:28:03');

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT '0',
  `categorie` varchar(50) DEFAULT 'Autre',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Contenu de la table `products`
--

INSERT INTO `products` (`id`, `nom`, `description`, `prix`, `image`, `stock`, `categorie`) VALUES
(1, 'Canapé Velours Bleu', 'Canapé 3 places ultra confortable en velours bleu nuit.', '850.00', 'uploads/canape-bleu.jpg', 5, 'Salon'),
(2, 'Table Basse Marbre', 'Table basse élégante avec plateau en marbre blanc et pieds dorés.', '220.00', 'uploads/table-marbre.jpg', 12, 'Salon'),
(3, 'Fauteuil Lounge Osier', 'Fauteuil au style bohème, parfait pour un coin lecture.', '180.00', 'uploads/fauteuil-osier.jpg', 8, 'Salon'),
(4, 'Meuble TV Chêne', 'Meuble TV minimaliste en chêne massif avec rangements.', '340.00', 'uploads/meuble-tv.jpg', 4, 'Salon'),
(5, 'Lit King Size Scandinave', 'Cadre de lit en bois clair avec sommier à lattes inclus.', '590.00', 'uploads/lit-scandi.jpg', 3, 'Chambre'),
(6, 'Table de Chevet Noire', 'Table de nuit compacte avec un tiroir silencieux.', '45.00', 'uploads/chevet-noir.jpg', 20, 'Chambre'),
(7, 'Armoire Miroir 3 Portes', 'Grande armoire avec penderie et étagères intégrées.', '450.00', 'uploads/armoire.jpg', 2, 'Chambre'),
(8, 'Commode 6 Tiroirs', 'Rangement spacieux pour vêtements au design moderne.', '280.00', 'uploads/commode.jpg', 7, 'Chambre'),
(9, 'Bureau d''Angle Industriel', 'Grand plan de travail avec structure en métal noir.', '210.00', 'uploads/bureau-angle.jpg', 6, 'Bureau'),
(10, 'Chaise Ergonomique Pro', 'Chaise de bureau réglable avec support lombaire.', '150.00', 'uploads/chaise-bureau.jpg', 14, 'Bureau'),
(11, 'Étagère Bibliothèque', 'Étagère haute pour livres et objets de décoration.', '110.00', 'uploads/etagere.jpg', 10, 'Bureau'),
(12, 'Lampe de Bureau LED', 'Lampe articulée avec variateur de luminosité.', '35.00', 'uploads/lampe.jpg', 24, 'Bureau'),
(13, 'Table à Manger Ronde', 'Table pour 4 personnes en bois de noyer.', '420.00', 'uploads/table-ronde.jpg', 4, 'Cuisine'),
(14, 'Lot de 4 Chaises Design', 'Chaises en coque blanche et pieds en bois.', '160.00', 'uploads/chaises-lot.jpg', 10, 'Cuisine'),
(15, 'Desserte de Cuisine', 'Meuble d''appoint sur roulettes avec plan de travail.', '95.00', 'uploads/desserte.jpg', 6, 'Cuisine'),
(16, 'Buffet Vaisselier', 'Buffet haut pour ranger toute votre vaisselle.', '520.00', 'uploads/buffet.jpg', 2, 'Cuisine'),
(17, 'test', 'tres bien', '29.00', 'uploads/1770774471_OIP (1).webp', 5, 'Salon');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'din', 'dingaston88@gmail.com', '$2y$10$pDOFqLhOZasVxz6SVhByxeH/.M5rLQIfSN3rDHKl6KuDAO7bv9FQm', 'client', '2026-02-10 23:31:58'),
(2, 'Moustapha', 'bla@gmail.com', '$2y$10$lQFa/vNmtPRj8aXsxOzSsennVKGrPRD7T6i6/8IIwzvANOtcmgHqa', 'admin', '2026-02-11 00:20:07');

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
