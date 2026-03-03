CREATE TABLE `sf_guard_user_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(80) DEFAULT NULL,
  `fullname` varchar(80) DEFAULT NULL,
  `validate` varchar(17) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `registeras` int(11) NOT NULL,
  `profile_pic` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_idx` (`user_id`),
  KEY `sf_guard_user_profile_user_id_idx` (`user_id`)
);