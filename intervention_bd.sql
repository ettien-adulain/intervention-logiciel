-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Phase 1 : schéma avec intégrité référentielle (FK) et table `recus` alignée Laravel.
-- Base de données : `intervention_bd`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `medias`;
DROP TABLE IF EXISTS `validations`;
DROP TABLE IF EXISTS `planifications`;
DROP TABLE IF EXISTS `interventions`;
DROP TABLE IF EXISTS `recus`;
DROP TABLE IF EXISTS `requetes`;
DROP TABLE IF EXISTS `logs`;
DROP TABLE IF EXISTS `utilisateurs`;
DROP TABLE IF EXISTS `clients`;
DROP TABLE IF EXISTS `migrations`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;

-- --------------------------------------------------------
-- Laravel : cache, files d'attente
-- --------------------------------------------------------

CREATE TABLE `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Métier
-- --------------------------------------------------------

CREATE TABLE `clients` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom_entreprise` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text COLLATE utf8mb4_unicode_ci,
  `statut` enum('actif','inactif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'actif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `utilisateurs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('super_admin','client_admin','client_user','technicien') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client_user',
  `statut` enum('actif','inactif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'actif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilisateurs_email_unique` (`email`),
  KEY `utilisateurs_client_id_foreign` (`client_id`),
  CONSTRAINT `utilisateurs_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `logs_user_id_foreign` (`user_id`),
  CONSTRAINT `logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `requetes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `technicien_id` bigint UNSIGNED DEFAULT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `urgence` enum('faible','moyenne','elevee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'moyenne',
  `statut` enum('ouverte','en_attente','planifiee','en_cours','terminee','cloturee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ouverte',
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_planification` datetime DEFAULT NULL,
  `date_intervention` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `requetes_client_id_foreign` (`client_id`),
  KEY `requetes_user_id_foreign` (`user_id`),
  KEY `requetes_technicien_id_foreign` (`technicien_id`),
  KEY `requetes_client_id_date_creation_index` (`client_id`,`date_creation`),
  CONSTRAINT `requetes_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `requetes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `requetes_technicien_id_foreign` FOREIGN KEY (`technicien_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recus` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `requete_id` bigint UNSIGNED NOT NULL,
  `chemin_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recus_requete_id_foreign` (`requete_id`),
  CONSTRAINT `recus_requete_id_foreign` FOREIGN KEY (`requete_id`) REFERENCES `requetes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `interventions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `requete_id` bigint UNSIGNED NOT NULL,
  `technicien_id` bigint UNSIGNED NOT NULL,
  `rapport` text COLLATE utf8mb4_unicode_ci,
  `pieces_utilisees` text COLLATE utf8mb4_unicode_ci,
  `heure_debut` datetime DEFAULT NULL,
  `heure_fin` datetime DEFAULT NULL,
  `statut` enum('en_cours','terminee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_cours',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `interventions_requete_id_foreign` (`requete_id`),
  KEY `interventions_technicien_id_foreign` (`technicien_id`),
  CONSTRAINT `interventions_requete_id_foreign` FOREIGN KEY (`requete_id`) REFERENCES `requetes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interventions_technicien_id_foreign` FOREIGN KEY (`technicien_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `planifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `requete_id` bigint UNSIGNED NOT NULL,
  `technicien_id` bigint UNSIGNED NOT NULL,
  `date_intervention` datetime NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `statut` enum('planifiee','confirmee','annulee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planifiee',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `planifications_requete_id_foreign` (`requete_id`),
  KEY `planifications_technicien_id_foreign` (`technicien_id`),
  CONSTRAINT `planifications_requete_id_foreign` FOREIGN KEY (`requete_id`) REFERENCES `requetes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `planifications_technicien_id_foreign` FOREIGN KEY (`technicien_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `validations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `requete_id` bigint UNSIGNED NOT NULL,
  `client_arrivee` tinyint(1) NOT NULL DEFAULT '0',
  `client_fin` tinyint(1) NOT NULL DEFAULT '0',
  `technicien_fin` tinyint(1) NOT NULL DEFAULT '0',
  `date_validation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `validations_requete_id_foreign` (`requete_id`),
  CONSTRAINT `validations_requete_id_foreign` FOREIGN KEY (`requete_id`) REFERENCES `requetes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `medias` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `requete_id` bigint UNSIGNED NOT NULL,
  `type` enum('image','video') COLLATE utf8mb4_unicode_ci NOT NULL,
  `chemin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taille` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `medias_requete_id_foreign` (`requete_id`),
  CONSTRAINT `medias_requete_id_foreign` FOREIGN KEY (`requete_id`) REFERENCES `requetes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2026_04_06_175049_create_clients_table', 1),
(4, '2026_04_06_175140_create_utilisateurs_table', 1),
(5, '2026_04_06_175222_create_logs_table', 1),
(6, '2026_04_06_175425_create_requetes_table', 1),
(7, '2026_04_06_175426_create_recus_table', 1),
(8, '2026_04_06_175427_create_interventions_table', 1),
(9, '2026_04_06_175428_create_planifications_table', 1),
(10, '2026_04_06_175450_create_validations_table', 1),
(11, '2026_04_06_175647_create_medias_table', 1);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
