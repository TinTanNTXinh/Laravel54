CREATE TABLE `districts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã',
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên',
  `type` enum('Quận','Thị xã','Huyện','Thành phố') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Loại',
  `city_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Thành phố',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Kích hoạt',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `districts_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

