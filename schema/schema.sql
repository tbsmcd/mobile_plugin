-- --------------------------------------------------------

--
-- テーブルの構造 `emojis`
--

CREATE TABLE `emojis` (
  `added_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `docomo_jis` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `docomo_sjis` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `docomo_utf` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kddi_jis` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kddi_sjis` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kddi_utf` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `softbank_sjis` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `softbank_utf` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gif` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`added_id`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
