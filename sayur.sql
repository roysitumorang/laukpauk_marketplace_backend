/*
SQLyog Ultimate
MySQL - 10.1.18-MariaDB : Database - sayur
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`sayur` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `sayur`;

/*Table structure for table `bank_accounts` */

DROP TABLE IF EXISTS `bank_accounts`;

CREATE TABLE `bank_accounts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `number` varchar(20) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `bank` varchar(20) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `banner_categories` */

DROP TABLE IF EXISTS `banner_categories`;

CREATE TABLE `banner_categories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `banners` */

DROP TABLE IF EXISTS `banners`;

CREATE TABLE `banners` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `banner_category_id` bigint(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `link` varchar(150) DEFAULT NULL,
  `type` char(5) NOT NULL,
  `show` tinyint(1) NOT NULL,
  `file_url` varchar(100) DEFAULT NULL,
  `file_name` char(36) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banner_category_id` (`banner_category_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `show` (`show`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `captcha_codes` */

DROP TABLE IF EXISTS `captcha_codes`;

CREATE TABLE `captcha_codes` (
  `id` varchar(40) NOT NULL,
  `namespace` varchar(32) NOT NULL,
  `code` varchar(32) NOT NULL,
  `code_display` varchar(32) NOT NULL,
  `created` int(11) NOT NULL,
  `audio_data` longblob,
  PRIMARY KEY (`id`,`namespace`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `cities` */

DROP TABLE IF EXISTS `cities`;

CREATE TABLE `cities` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `province_id` bigint(20) NOT NULL,
  `type` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `origin_id` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `province_id` (`province_id`,`name`,`type`),
  KEY `origin_id` (`origin_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `type` (`type`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `coupon_cities` */

DROP TABLE IF EXISTS `coupon_cities`;

CREATE TABLE `coupon_cities` (
  `coupon_id` bigint(20) NOT NULL,
  `city_id` bigint(20) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`city_id`,`coupon_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `coupon_id` (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `coupon_products` */

DROP TABLE IF EXISTS `coupon_products`;

CREATE TABLE `coupon_products` (
  `coupon_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`coupon_id`,`product_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `coupons` */

DROP TABLE IF EXISTS `coupons`;

CREATE TABLE `coupons` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(15) NOT NULL,
  `effective_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `discount_amount` decimal(10,0) NOT NULL,
  `discount_type` char(1) NOT NULL,
  `status` char(1) NOT NULL,
  `usage` char(1) NOT NULL,
  `minimum_purchase` decimal(10,0) NOT NULL,
  `description` text,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_desc` varchar(250) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `show` tinyint(1) NOT NULL,
  `permalink` varchar(200) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `permalink` (`permalink`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `show` (`show`),
  KEY `status` (`status`),
  KEY `effective_date` (`effective_date`),
  KEY `expiry_date` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `discount_cities` */

DROP TABLE IF EXISTS `discount_cities`;

CREATE TABLE `discount_cities` (
  `discount_id` bigint(20) NOT NULL,
  `city_id` bigint(20) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`city_id`,`discount_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `discount_id` (`discount_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `discount_products` */

DROP TABLE IF EXISTS `discount_products`;

CREATE TABLE `discount_products` (
  `discount_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`discount_id`,`product_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `discounts` */

DROP TABLE IF EXISTS `discounts`;

CREATE TABLE `discounts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `effective_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `percentage` decimal(10,0) NOT NULL,
  `status` char(1) NOT NULL,
  `description` text,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_desc` varchar(250) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `show` tinyint(1) NOT NULL,
  `permalink` varchar(200) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permalink` (`permalink`),
  KEY `effective_date` (`effective_date`),
  KEY `expiry_date` (`expiry_date`),
  KEY `show` (`show`),
  KEY `status` (`status`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `email_recipients` */

DROP TABLE IF EXISTS `email_recipients`;

CREATE TABLE `email_recipients` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email_id` bigint(20) NOT NULL,
  `email` varchar(80) NOT NULL,
  `transaction_id` char(36) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`,`email_id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `email_id` (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `email_templates` */

DROP TABLE IF EXISTS `email_templates`;

CREATE TABLE `email_templates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `body` text NOT NULL,
  `sender_name` varchar(50) NOT NULL,
  `sender_email` varchar(50) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `emails` */

DROP TABLE IF EXISTS `emails`;

CREATE TABLE `emails` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sender_email` varchar(80) NOT NULL,
  `sender_name` varchar(80) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `body` text,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `sender_name` (`sender_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `images` */

DROP TABLE IF EXISTS `images`;

CREATE TABLE `images` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) DEFAULT NULL,
  `name` char(36) NOT NULL,
  `width` smallint(6) NOT NULL,
  `height` smallint(6) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `name` (`name`),
  KEY `width` (`width`),
  KEY `height` (`height`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `invoices` */

DROP TABLE IF EXISTS `invoices`;

CREATE TABLE `invoices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `date_of_issue` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `description` text,
  `total` int(11) NOT NULL,
  `date_of_payment` date DEFAULT NULL,
  `status` char(1) NOT NULL,
  `type_of_payment` varchar(50) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `date_of_issue` (`date_of_issue`),
  KEY `due_date` (`due_date`),
  KEY `status` (`status`),
  KEY `date_of_payment` (`date_of_payment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `login_history` */

DROP TABLE IF EXISTS `login_history`;

CREATE TABLE `login_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `sign_in_at` datetime NOT NULL,
  `ip_address` varchar(41) DEFAULT NULL,
  `user_agent` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `sign_in_at` (`sign_in_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `memberships` */

DROP TABLE IF EXISTS `memberships`;

CREATE TABLE `memberships` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `validity_length` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `memos` */

DROP TABLE IF EXISTS `memos`;

CREATE TABLE `memos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `body` text NOT NULL,
  `show` tinyint(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `show` (`show`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `message_recipients` */

DROP TABLE IF EXISTS `message_recipients`;

CREATE TABLE `message_recipients` (
  `message_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`message_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `read_at` (`read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `messages` */

DROP TABLE IF EXISTS `messages`;

CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subject` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `news` */

DROP TABLE IF EXISTS `news`;

CREATE TABLE `news` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `news_category_id` bigint(20) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `permalink` varchar(100) NOT NULL,
  `custom_link` varchar(200) DEFAULT NULL,
  `body` text,
  `picture` char(36) DEFAULT NULL,
  `meta_title` varchar(100) DEFAULT NULL,
  `meta_desc` varchar(200) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permalink` (`permalink`),
  KEY `created_by` (`created_by`),
  KEY `created_at` (`created_at`),
  KEY `news_category_id` (`news_category_id`),
  KEY `subject` (`subject`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `news_categories` */

DROP TABLE IF EXISTS `news_categories`;

CREATE TABLE `news_categories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `permalink` varchar(100) NOT NULL,
  `comment_status` tinyint(1) NOT NULL,
  `moderation_status` tinyint(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `permalink` (`permalink`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `news_comments` */

DROP TABLE IF EXISTS `news_comments`;

CREATE TABLE `news_comments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `news_id` bigint(20) NOT NULL,
  `name` varchar(80) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `website` varchar(70) DEFAULT NULL,
  `body` text NOT NULL,
  `ip_address` varchar(41) NOT NULL,
  `approved` tinyint(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `news_id` (`news_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `approved` (`approved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `notification_templates` */

DROP TABLE IF EXISTS `notification_templates`;

CREATE TABLE `notification_templates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `url` varchar(100) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `notifications` */

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `link` varchar(200) NOT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `read_at` (`read_at`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `page_categories` */

DROP TABLE IF EXISTS `page_categories`;

CREATE TABLE `page_categories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `show_new_page_menu` tinyint(1) NOT NULL,
  `show_picture_icon` tinyint(1) NOT NULL,
  `show_content` tinyint(1) NOT NULL,
  `show_url` tinyint(1) NOT NULL,
  `show_link_target` tinyint(1) NOT NULL,
  `show_rich_editor` tinyint(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `pages` */

DROP TABLE IF EXISTS `pages`;

CREATE TABLE `pages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `permalink` varchar(100) NOT NULL,
  `url` varchar(100) DEFAULT NULL,
  `body` text,
  `url_target` varchar(6) NOT NULL,
  `picture` char(36) DEFAULT NULL,
  `meta_title` varchar(100) DEFAULT NULL,
  `meta_desc` varchar(200) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `show` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `permalink` (`permalink`),
  KEY `created_by` (`created_by`),
  KEY `parent_id` (`parent_id`),
  KEY `position` (`position`),
  KEY `show` (`show`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `passwords` */

DROP TABLE IF EXISTS `passwords`;

CREATE TABLE `passwords` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `hash` varchar(100) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `point_categories` */

DROP TABLE IF EXISTS `point_categories`;

CREATE TABLE `point_categories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `permalink` varchar(50) NOT NULL,
  `picture` char(36) DEFAULT NULL,
  `show` tinyint(1) NOT NULL,
  `description` text,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_desc` varchar(200) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `permalink` (`permalink`),
  KEY `show` (`show`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `point_images` */

DROP TABLE IF EXISTS `point_images`;

CREATE TABLE `point_images` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `point_id` bigint(20) NOT NULL,
  `name` char(36) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `point_id` (`point_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `point_orders` */

DROP TABLE IF EXISTS `point_orders`;

CREATE TABLE `point_orders` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `point_id` bigint(20) NOT NULL,
  `amount` int(11) NOT NULL,
  `status` varchar(10) NOT NULL,
  `ip_address` varchar(41) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `point_id` (`point_id`),
  KEY `status` (`status`),
  KEY `updated_by` (`updated_by`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `points` */

DROP TABLE IF EXISTS `points`;

CREATE TABLE `points` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `point_category_id` bigint(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `permalink` varchar(150) NOT NULL,
  `description` text,
  `amount` int(11) NOT NULL,
  `available` tinyint(1) NOT NULL,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_desc` varchar(200) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `point_type` char(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `permalink` (`permalink`),
  KEY `available` (`available`),
  KEY `created_by` (`created_by`),
  KEY `point_category_id` (`point_category_id`),
  KEY `point_type` (`point_type`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_brands` */

DROP TABLE IF EXISTS `product_brands`;

CREATE TABLE `product_brands` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `permalink` varchar(50) NOT NULL,
  `picture` char(36) NOT NULL,
  `description` text,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_desc` varchar(250) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `permalink` (`permalink`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_cities` */

DROP TABLE IF EXISTS `product_cities`;

CREATE TABLE `product_cities` (
  `product_id` bigint(20) NOT NULL,
  `city_id` bigint(20) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`city_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_dimensions` */

DROP TABLE IF EXISTS `product_dimensions`;

CREATE TABLE `product_dimensions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `item` varchar(20) NOT NULL,
  `size` decimal(10,0) NOT NULL,
  `unit` varchar(10) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item` (`item`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_hits` */

DROP TABLE IF EXISTS `product_hits`;

CREATE TABLE `product_hits` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `visits` smallint(6) NOT NULL,
  `like` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `visits` (`visits`),
  KEY `like` (`like`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_images` */

DROP TABLE IF EXISTS `product_images`;

CREATE TABLE `product_images` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `name` char(36) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `product_id` (`product_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_notifications` */

DROP TABLE IF EXISTS `product_notifications`;

CREATE TABLE `product_notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `email` varchar(80) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_order_items` */

DROP TABLE IF EXISTS `product_order_items`;

CREATE TABLE `product_order_items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `quantity` smallint(6) NOT NULL,
  `buy_point` decimal(10,0) NOT NULL,
  `affiliate_point` decimal(10,0) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_orders` */

DROP TABLE IF EXISTS `product_orders`;

CREATE TABLE `product_orders` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(80) NOT NULL,
  `email` varchar(80) NOT NULL,
  `address` varchar(200) NOT NULL,
  `subdistrict_id` bigint(20) NOT NULL,
  `zip_code` char(5) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `tracking_number` varchar(20) DEFAULT NULL,
  `payment` text,
  `final_bill` decimal(10,0) NOT NULL,
  `status` varchar(20) NOT NULL,
  `buyer_id` bigint(20) DEFAULT NULL,
  `admin_fee` decimal(10,0) NOT NULL,
  `original_bill` decimal(10,0) NOT NULL,
  `ip_address` varchar(41) DEFAULT NULL,
  `affiliate_user_id` bigint(20) DEFAULT NULL,
  `shipping_fee` decimal(10,0) DEFAULT NULL,
  `detail` text,
  `coupon_id` bigint(20) DEFAULT NULL,
  `estimated_delivery` datetime NOT NULL,
  `actual_delivery` datetime DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `affiliate_user_id` (`affiliate_user_id`),
  KEY `buyer_id` (`buyer_id`),
  KEY `coupon_id` (`coupon_id`),
  KEY `created_by` (`created_by`),
  KEY `status` (`status`),
  KEY `subdistrict_id` (`subdistrict_id`),
  KEY `updated_by` (`updated_by`),
  KEY `actual_delivery` (`actual_delivery`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_reviews` */

DROP TABLE IF EXISTS `product_reviews`;

CREATE TABLE `product_reviews` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `body` text NOT NULL,
  `rank` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `approved` (`approved`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_slot_items` */

DROP TABLE IF EXISTS `product_slot_items`;

CREATE TABLE `product_slot_items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_slot_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `show` tinyint(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`product_slot_id`),
  KEY `product_slot_id` (`product_slot_id`),
  KEY `created_by` (`created_by`),
  KEY `show` (`show`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_slots` */

DROP TABLE IF EXISTS `product_slots`;

CREATE TABLE `product_slots` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subject` varchar(100) NOT NULL,
  `permalink` varchar(100) NOT NULL,
  `picture` char(36) DEFAULT NULL,
  `description` text NOT NULL,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_desc` varchar(200) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `show` tinyint(1) DEFAULT NULL,
  `displayed_at_home` tinyint(1) DEFAULT NULL,
  `position` int(11) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permalink` (`permalink`),
  UNIQUE KEY `subject` (`subject`),
  UNIQUE KEY `picture` (`picture`),
  KEY `created_by` (`created_by`),
  KEY `displayed_at_home` (`displayed_at_home`),
  KEY `position` (`position`),
  KEY `show` (`show`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_types` */

DROP TABLE IF EXISTS `product_types`;

CREATE TABLE `product_types` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `show` tinyint(1) NOT NULL,
  `stock` int(11) NOT NULL,
  `extra_price` int(11) DEFAULT NULL,
  `classification` varchar(10) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `classification` (`classification`),
  KEY `created_by` (`created_by`),
  KEY `name` (`name`),
  KEY `show` (`show`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `product_wishlists` */

DROP TABLE IF EXISTS `product_wishlists`;

CREATE TABLE `product_wishlists` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `product_id` bigint(20) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `product_category_id` bigint(20) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `permalink` varchar(150) NOT NULL,
  `description` text,
  `price` decimal(10,0) NOT NULL,
  `weight` int(11) NOT NULL,
  `show` tinyint(1) NOT NULL,
  `status` smallint(6) NOT NULL,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_desc` varchar(200) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `stock` smallint(6) NOT NULL,
  `product_brand_id` bigint(20) DEFAULT NULL,
  `buy_point` decimal(10,0) NOT NULL,
  `affiliate_point` decimal(10,0) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permalink` (`permalink`),
  UNIQUE KEY `code` (`code`),
  KEY `created_by` (`created_by`),
  KEY `name` (`name`),
  KEY `product_brand_id` (`product_brand_id`),
  KEY `product_category_id` (`product_category_id`),
  KEY `show` (`show`),
  KEY `status` (`status`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `provinces` */

DROP TABLE IF EXISTS `provinces`;

CREATE TABLE `provinces` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `searches` */

DROP TABLE IF EXISTS `searches`;

CREATE TABLE `searches` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(250) NOT NULL,
  `hits` int(11) NOT NULL,
  `custom_url` varchar(250) DEFAULT NULL,
  `source` varchar(100) NOT NULL,
  `description` text,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_desc` varchar(200) DEFAULT NULL,
  `meta_keyword` varchar(200) DEFAULT NULL,
  `show` tinyint(1) NOT NULL,
  `permalink` varchar(200) DEFAULT NULL,
  `show_at` text,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `hits` (`hits`),
  KEY `keyword` (`keyword`),
  KEY `permalink` (`permalink`),
  KEY `show` (`show`),
  KEY `source` (`source`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `seo_templates` */

DROP TABLE IF EXISTS `seo_templates`;

CREATE TABLE `seo_templates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `subject` varchar(200) NOT NULL,
  `detail` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` varchar(64) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `ip_address` varchar(41) NOT NULL,
  `user_agent` text NOT NULL,
  `data` text NOT NULL,
  `last_activity` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `last_activity` (`last_activity`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `shipping_costs` */

DROP TABLE IF EXISTS `shipping_costs`;

CREATE TABLE `shipping_costs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `shipping_service_id` bigint(20) NOT NULL,
  `origin_id` bigint(20) NOT NULL,
  `destination_id` bigint(20) NOT NULL,
  `rate` int(11) NOT NULL,
  `estimated_time_of_departure` varchar(5) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `destination_id` (`destination_id`,`shipping_service_id`,`origin_id`),
  KEY `origin_id` (`origin_id`),
  KEY `shipping_service_id` (`shipping_service_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `shipping_couriers` */

DROP TABLE IF EXISTS `shipping_couriers`;

CREATE TABLE `shipping_couriers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `shipping_services` */

DROP TABLE IF EXISTS `shipping_services`;

CREATE TABLE `shipping_services` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `shipping_courier_id` bigint(20) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` varchar(30) DEFAULT NULL,
  `show` tinyint(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`,`shipping_courier_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `shipping_courier_id` (`shipping_courier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `short_url_logs` */

DROP TABLE IF EXISTS `short_url_logs`;

CREATE TABLE `short_url_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `short_url_id` bigint(20) NOT NULL,
  `ip_address` varchar(41) NOT NULL,
  `hits` int(11) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`,`short_url_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `short_url_id` (`short_url_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `short_urls` */

DROP TABLE IF EXISTS `short_urls`;

CREATE TABLE `short_urls` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `long_url` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `long_url` (`long_url`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `subdistricts` */

DROP TABLE IF EXISTS `subdistricts`;

CREATE TABLE `subdistricts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `city_id` bigint(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `city_id` (`city_id`,`name`),
  KEY `name` (`name`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `testimonies` */

DROP TABLE IF EXISTS `testimonies`;

CREATE TABLE `testimonies` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `body` text NOT NULL,
  `rank` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approved` (`approved`),
  KEY `rank` (`rank`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `user_memberships` */

DROP TABLE IF EXISTS `user_memberships`;

CREATE TABLE `user_memberships` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `description` text,
  `effective_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `price` int(11) NOT NULL,
  `status` char(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `effective_date` (`effective_date`),
  KEY `status` (`status`),
  KEY `updated_by` (`updated_by`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(80) NOT NULL,
  `password` char(60) NOT NULL,
  `address` varchar(150) DEFAULT NULL,
  `zip_code` char(5) DEFAULT NULL,
  `subdistrict_id` bigint(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `premium` tinyint(1) NOT NULL,
  `affiliate_link` varchar(200) DEFAULT NULL,
  `status` smallint(6) NOT NULL,
  `activated_at` datetime DEFAULT NULL,
  `activation_token` char(32) DEFAULT NULL,
  `password_reset_token` char(32) DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `deposit` decimal(10,0) NOT NULL,
  `ktp` varchar(80) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `npwp` varchar(80) DEFAULT NULL,
  `avatar` char(36) DEFAULT NULL,
  `registration_ip` varchar(41) NOT NULL,
  `twitter_id` bigint(20) DEFAULT NULL,
  `google_id` bigint(20) DEFAULT NULL,
  `facebook_id` bigint(20) DEFAULT NULL,
  `reward` decimal(10,0) NOT NULL,
  `gender` varchar(6) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `buy_point` decimal(10,0) NOT NULL,
  `affiliate_point` decimal(10,0) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `activation_token` (`activation_token`),
  UNIQUE KEY `facebook_id` (`facebook_id`),
  UNIQUE KEY `google_id` (`google_id`),
  UNIQUE KEY `password_reset_token` (`password_reset_token`),
  UNIQUE KEY `twitter_id` (`twitter_id`),
  KEY `activated_at` (`activated_at`),
  KEY `created_by` (`created_by`),
  KEY `date_of_birth` (`date_of_birth`),
  KEY `gender` (`gender`),
  KEY `name` (`name`),
  KEY `premium` (`premium`),
  KEY `status` (`status`),
  KEY `subdistrict_id` (`subdistrict_id`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `withdrawals` */

DROP TABLE IF EXISTS `withdrawals`;

CREATE TABLE `withdrawals` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `amount` decimal(10,0) NOT NULL,
  `approved` tinyint(1) NOT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approved` (`approved`),
  KEY `created_at` (`created_at`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
