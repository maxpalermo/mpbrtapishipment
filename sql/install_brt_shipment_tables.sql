-- Table: brt_shipment_request
CREATE TABLE IF NOT EXISTS `__PREFIX__brt_shipment_request` (
  `id_brt_shipment_request` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `account_json` TEXT NOT NULL,
  `create_data_json` LONGTEXT NOT NULL,
  `is_label_required` TINYINT(1) NOT NULL DEFAULT 1,
  `label_parameters_json` TEXT NOT NULL,
  `date_add` DATETIME NOT NULL,
  `date_upd` DATETIME NOT NULL,
  PRIMARY KEY (`id_brt_shipment_request`),
  KEY (`order_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Table: brt_shipment_response
CREATE TABLE IF NOT EXISTS `__PREFIX__brt_shipment_response` (
  `id_brt_shipment_response` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `current_time_utc` VARCHAR(64) DEFAULT NULL,
  `arrival_terminal` VARCHAR(64) DEFAULT NULL,
  `arrival_depot` VARCHAR(64) DEFAULT NULL,
  `delivery_zone` VARCHAR(64) DEFAULT NULL,
  `parcel_number_from` VARCHAR(64) DEFAULT NULL,
  `parcel_number_to` VARCHAR(64) DEFAULT NULL,
  `departure_depot` VARCHAR(64) DEFAULT NULL,
  `series_number` VARCHAR(64) DEFAULT NULL,
  `service_type` VARCHAR(64) DEFAULT NULL,
  `consignee_company_name` VARCHAR(255) DEFAULT NULL,
  `consignee_address` VARCHAR(255) DEFAULT NULL,
  `consignee_zip_code` VARCHAR(32) DEFAULT NULL,
  `consignee_city` VARCHAR(128) DEFAULT NULL,
  `consignee_province_abbreviation` VARCHAR(8) DEFAULT NULL,
  `consignee_country_abbreviation_brt` VARCHAR(8) DEFAULT NULL,
  `number_of_parcels` INT DEFAULT 0,
  `weight_kg` FLOAT DEFAULT 0,
  `volume_m3` FLOAT DEFAULT 0,
  `alphanumeric_sender_reference` VARCHAR(64) DEFAULT NULL,
  `sender_company_name` VARCHAR(255) DEFAULT NULL,
  `sender_province_abbreviation` VARCHAR(8) DEFAULT NULL,
  `disclaimer` VARCHAR(255) DEFAULT NULL,
  `execution_message` LONGTEXT,
  PRIMARY KEY (`id_brt_shipment_response`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Table: brt_shipment_response_label
CREATE TABLE IF NOT EXISTS `__PREFIX__brt_shipment_response_label` (
  `id_brt_shipment_response_label` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_brt_shipment_response` INT UNSIGNED NOT NULL,
  `label_id` VARCHAR(64) DEFAULT NULL,
  `parcel_number` VARCHAR(64) DEFAULT NULL,
  `label_type` VARCHAR(32) DEFAULT NULL,
  `label_stream` LONGTEXT,
  `label_format` VARCHAR(16) DEFAULT NULL,
  PRIMARY KEY (`id_brt_shipment_response_label`),
  KEY (`id_brt_shipment_response`),
  CONSTRAINT `fk_brt_shipment_response_label_response` FOREIGN KEY (`id_brt_shipment_response`) REFERENCES `__PREFIX__brt_shipment_response` (`id_brt_shipment_response`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Table: brt_label_weight
CREATE TABLE IF NOT EXISTS `__PREFIX__brt_shipment_label_weight` (
  `id_weight` int(11) NOT NULL AUTO_INCREMENT,
  `barcode` varchar(255) NOT NULL,
  `weight` float NOT NULL,
  `volume` float NOT NULL DEFAULT 1,
  `x` float NOT NULL DEFAULT 1,
  `y` float NOT NULL DEFAULT 1,
  `z` float NOT NULL DEFAULT 1,
  `id_read` int(11) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `is_envelope` tinyint(1) DEFAULT 0,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_weight`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;