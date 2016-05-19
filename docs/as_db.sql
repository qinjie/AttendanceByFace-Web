DROP DATABASE IF EXISTS `attendance_system`;
CREATE DATABASE `attendance_system`;

USE `attendance_system`;

CREATE TABLE `as_user` (
	`id` bigint NOT NULL AUTO_INCREMENT,
	`username` varchar(255) NOT NULL,
	`auth_key` varchar(32) DEFAULT '',
	`password_hash` varchar(255) DEFAULT '',
	`password_reset_token` varchar(255) DEFAULT NULL,
	`email` varchar(255) NOT NULL,
	`email_confirm_token` varchar(255) DEFAULT NULL,
	`status` varchar(10) DEFAULT '10',
	`created_at` DATETIME DEFAULT NULL,
	`updated_at` DATETIME DEFAULT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `as_user_token` (
	`id` bigint NOT NULL AUTO_INCREMENT,
	`user_id` bigint NOT NULL,
	`token` varchar(32) NOT NULL DEFAULT '',
	`ip_address` varchar(32) NOT NULL,
	`expire` DATETIME NOT NULL,
	`created_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY (`token`),
	FOREIGN KEY (user_id) REFERENCES as_user(id) 
);


