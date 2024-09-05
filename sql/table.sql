CREATE DATABASE IF NOT EXISTS 'lorry_app';

CREATE TABLE IF NOT EXISTS`news_letters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `heading` varchar(45) NOT NULL,
  `content` text NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` int DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ;

CREATE TABLE IF NOT EXISTS`reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `report` text NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` int DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
);

CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) DEFAULT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(155) DEFAULT NULL,
  `phone_number` varchar(45) DEFAULT NULL,
  `approval_status` int DEFAULT '2',
  `otp` varchar(45) DEFAULT NULL,
  `is_verified` tinyint DEFAULT '0',
  `role_id` int DEFAULT '3',
  `category` varchar(155) DEFAULT NULL,
  `taluk` varchar(155) DEFAULT NULL,
  `district` varchar(155) DEFAULT NULL,
  `state` varchar(155) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` int DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `phone_number_UNIQUE` (`phone_number`)
) ;


CREATE TABLE IF NOT EXISTS `districts_ref` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state_code` varchar(45) NOT NULL,
  `district_code` varchar(45) NOT NULL,
  `name` varchar(155) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ;

CREATE TABLE IF NOT EXISTS `states_ref` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(155) NOT NULL,
  `code` varchar(45) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ;

CREATE TABLE IF NOT EXISTS `villages_ref` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state_code` varchar(45) DEFAULT NULL,
  `district_code` varchar(45) NOT NULL,
  `village_code` varchar(45) NOT NULL,
  `name` varchar(155) NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(155) NOT NULL,
  `code` varchar(45) NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)  
) ;

