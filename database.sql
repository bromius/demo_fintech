
CREATE TABLE `transactions` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ident` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date` DATETIME NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_ident` (`ident`),
  KEY `idx_account_created_at` (`account`, `created_at`),
  KEY `idx_created_at_deleted_at` (`created_at`, `deleted_at`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `rates` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `rate` decimal(12,6) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `currency_created` (`currency`, `created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;