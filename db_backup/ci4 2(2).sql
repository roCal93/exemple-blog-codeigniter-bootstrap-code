-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : ven. 12 sep. 2025 à 11:19
-- Version du serveur : 9.4.0
-- Version de PHP : 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ci4`
--

-- --------------------------------------------------------

--
-- Structure de la table `news`
--

CREATE TABLE `news` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `state` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `state`, `created_at`, `user_id`) VALUES
(16, 'Pourquoi les micro-habitudes changent tout', 'Adopter une nouvelle habitude paraît souvent difficile, mais en réalité, la clé est de commencer petit. Lire une page par jour, marcher 5 minutes ou noter une idée chaque soir suffit à enclencher un effet boule de neige. Ces micro-habitudes, faciles à répéter, construisent une régularité qui mène à de vrais changements à long terme.', 0, '2025-08-31 22:00:00', 1),
(17, 'Le pouvoir caché des plantes d’intérieur', 'Au-delà de la décoration, certaines plantes améliorent l’air de la maison et réduisent le stress. Le pothos, le palmier ou encore la sansevieria purifient l’air tout en demandant peu d’entretien. En plus, leur présence apporte un effet apaisant prouvé par des études en psychologie environnementale.', 1, '2020-09-28 22:00:00', 2),
(18, '3 astuces pour gagner du temps le matin', 'Préparer ses affaires la veille (vêtements, sac, clés).\r\n\r\nUtiliser une routine fixe de 15 minutes (toilette, hydratation, petit-déj rapide).\r\n\r\nLimiter l’usage du smartphone jusqu’à être prêt à sortir.\r\nCes petits ajustements permettent de réduire le stress matinal et de commencer la journée avec plus d’énergie.', 1, '2016-05-10 22:00:00', 1),
(19, 'Pourquoi boire un verre d’eau avant le café', 'Le café déshydrate légèrement, surtout à jeun. Boire un grand verre d’eau avant sa première tasse aide à réhydrater l’organisme et évite le coup de fatigue qui survient parfois une heure après. C’est un geste simple, mais qui optimise à la fois l’énergie et la digestion.', 1, '2023-06-13 22:00:00', 2),
(20, 'Le minimalisme numérique en 10 minutes par jour', 'Désencombrer son téléphone ou son ordinateur ne demande pas des heures. Chaque jour, consacrer 10 minutes à supprimer des fichiers inutiles, trier ses photos ou désinstaller une app. Résultat : plus de clarté, plus de stockage et surtout, moins de distraction.', 1, '2021-10-14 22:00:00', 1),
(21, 'Le silence comme outil de concentration', 'On pense souvent que la productivité passe par de meilleurs outils. Pourtant, couper les notifications et travailler dans le silence peut doubler notre efficacité. Le cerveau adore l’absence de distraction : il entre plus vite dans un état de “flow”, où l’on avance sans effort.', 0, '2025-09-10 08:26:40', 1),
(23, 'L’art de dire non sans culpabiliser', 'Chaque “oui” à une demande inutile est un “non” caché à soi-même. Dire non n’est pas un rejet, mais une façon de protéger son temps et son énergie. Une réponse simple comme “je ne suis pas disponible pour ça” suffit à poser une limite claire et respectueuse.', 0, '2025-09-10 11:19:48', 2);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `created_at`, `name`, `role`) VALUES
(1, 'romaincalmelet@gmail.com', '$2y$12$oHULwsv4H5kpMZkzj6L0Z.Qg1Wn4rlmuM3eB2znGm2ZFIzr.a5MBW', '2025-09-10 11:48:20', 'Romain', 'admin'),
(2, 'josh@gmail.com', '$2y$12$eH99ZjlYMemFBS65CL2f2O2JOrFnxxJhzk8OWkcDWeS1OUBdYzN4G', '2025-09-11 10:54:57', 'Josh', 'user'),
(3, 'ben@gmail.com', '$2y$12$NyP9MEmc/thmfCzcOPdmfeDWIb5cFtbpivT5YuMSTAVbsVwTlbUve', '2025-09-11 11:04:50', 'Ben', 'user');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `news`
--
ALTER TABLE `news`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
