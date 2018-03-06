-- VirtueMart table SQL script
-- This will install all the tables need to run VirtueMart


--
-- Table structure for table `#__virtuemart_adminmenuentries`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_adminmenuentries` (
  `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'The ID of the VM Module, this Item is assigned to',
  `parent_id` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` char(64) NOT NULL DEFAULT '0',
  `link` char(64) NOT NULL DEFAULT '0',
  `depends` char(64) NOT NULL DEFAULT '' COMMENT 'Names of the Parameters, this Item depends on',
  `icon_class` char(96),
  `ordering` int(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `tooltip` char(128),
  `view` char(32),
  `task` char(32),
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  KEY `published` (`published`),
  KEY `ordering` (`ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Administration Menu Items' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_calcs`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_calcs` (
  `virtuemart_calc_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Belongs to vendor',
  `calc_jplugin_id` int(1) NOT NULL DEFAULT '0',
  `calc_name` varchar(64) NOT NULL DEFAULT '' COMMENT 'Name of the rule',
  `calc_descr` varchar(128) NOT NULL DEFAULT '' COMMENT 'Description',
  `calc_kind` varchar(16) NOT NULL DEFAULT '' COMMENT 'Discount/Tax/Margin/Commission',
  `calc_value_mathop` varchar(8) NOT NULL DEFAULT '' COMMENT 'the mathematical operation like (+,-,+%,-%)',
  `calc_value` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'The Amount',
  `calc_currency` smallint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Currency of the Rule',
  `calc_shopper_published` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Visible for Shoppers',
  `calc_vendor_published` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Visible for Vendors',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Startdate if nothing is set = permanent',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Enddate if nothing is set = permanent',
  `for_override` tinyint(1) NOT NULL DEFAULT '0',
  `calc_params` varchar(15359) NOT NULL DEFAULT '',
  `ordering` int(1) NOT NULL DEFAULT '0',
  `shared` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_calc_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  KEY `published` (`published`),
  KEY `calc_kind` (`calc_kind`),
  KEY `shared` (`shared`),
  KEY `publish_up` (`publish_up`),
  KEY `publish_down` (`publish_down`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_calc_categories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_calc_categories` (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_calc_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_category_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_calc_id` (`virtuemart_calc_id`,`virtuemart_category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `#__virtuemart_calc_manufacturers` (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_calc_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_manufacturer_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_calc_id` (`virtuemart_calc_id`,`virtuemart_manufacturer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_calc_shoppergroups`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_calc_shoppergroups` (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_calc_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_shoppergroup_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_calc_id` (`virtuemart_calc_id`,`virtuemart_shoppergroup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_calc_countries`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_calc_countries` (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_calc_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_country_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_calc_id` (`virtuemart_calc_id`,`virtuemart_country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_calc_states`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_calc_states` (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_calc_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_state_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_calc_id` (`virtuemart_calc_id`,`virtuemart_state_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_categories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_categories` (
  `virtuemart_category_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Belongs to vendor',
  `category_template` varchar(128),
  `category_layout` varchar(64),
  `category_product_layout` varchar(64),
  `products_per_row` varchar(1) NOT NULL DEFAULT '',
  `limit_list_step` varchar(32),
  `limit_list_initial` smallint(1) UNSIGNED,
  `hits` int(1) unsigned NOT NULL DEFAULT '0',
  `cat_params` varchar(15359) NOT NULL DEFAULT '',
  `metarobot` varchar(40) NOT NULL DEFAULT '',
  `metaauthor` varchar(64) NOT NULL DEFAULT '',
  `ordering` int(1) NOT NULL DEFAULT '0',
  `shared` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_category_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  KEY `published` (`published`),
  KEY `shared` (`shared`),
  KEY `ordering` (`ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Product Categories are stored here' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_category_categories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_category_categories` (
  `id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_parent_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `category_child_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `category_child_id` (`category_child_id`),
  KEY `ordering` (`ordering`),
  UNIQUE KEY `category_parent_id` (`category_parent_id`,`category_child_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Category child-parent relation list';

--
-- Table structure for table `#__virtuemart_category_medias`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_category_medias` (
  `id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_category_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_media_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ordering` (`virtuemart_category_id`, `ordering`),
  UNIQUE KEY `virtuemart_category_id` (`virtuemart_category_id`,`virtuemart_media_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_countries`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_countries` (
  `virtuemart_country_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_worldzone_id` tinyint(1) NOT NULL DEFAULT '1',
  `country_name` varchar(64),
  `country_3_code` char(3),
  `country_2_code` char(2),
  `ordering` int(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_country_id`),
  KEY `country_3_code` (`country_3_code`),
  KEY `country_2_code` (`country_2_code`),
  KEY `country_name` (`country_name`),
  KEY `ordering` (`ordering`),
  KEY `published` (`published`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Country records' ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_coupons`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_coupons` (
  `virtuemart_coupon_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` INT(1) UNSIGNED NOT NULL,
  `coupon_code` varchar(32) NOT NULL DEFAULT '',
  `percent_or_total` enum('percent','total') NOT NULL DEFAULT 'percent',
  `coupon_type` enum('gift','permanent') NOT NULL DEFAULT 'gift',
  `coupon_value` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `coupon_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `coupon_expiry_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `coupon_value_valid` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `coupon_used` varchar(200) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`virtuemart_coupon_id`),
   KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
   KEY `coupon_code` (`coupon_code`),
   KEY `coupon_type` (`coupon_type`),
   KEY `published` (`published`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Used to store coupon codes' ;

CREATE TABLE IF NOT EXISTS `#__virtuemart_carts` (
  `virtuemart_cart_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_user_id` INT(1) UNSIGNED NOT NULL,
  `virtuemart_vendor_id` INT(1) UNSIGNED NOT NULL,
  `cartData` VARBINARY(50000),
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_cart_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  KEY `virtuemart_user_id` (`virtuemart_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Used to store the cart';

-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_currencies`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_currencies` (
  `virtuemart_currency_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `currency_name` varchar(64),
  `currency_code_2` char(2),
  `currency_code_3` char(3),
  `currency_numeric_code` int(4),
  `currency_exchange_rate` decimal(12,5),
  `currency_symbol` varchar(8),
  `currency_decimal_place` varchar(8),
  `currency_decimal_symbol` varchar(8),
  `currency_thousands` varchar(8),
  `currency_positive_style` varchar(64),
  `currency_negative_style` varchar(64),
  `ordering` int(1) NOT NULL DEFAULT '0',
  `shared` tinyint(1) NOT NULL DEFAULT '1',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_currency_id`),
  KEY `ordering` (`ordering`),
  KEY `currency_name` (`currency_name`),
  KEY `published` (`published`),
  KEY `shared` (`shared`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  UNIQUE KEY `currency_code_3` (`currency_code_3`),
  KEY `currency_numeric_code` (`currency_numeric_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Used to store currencies';


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_customs`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_customs` (
  `virtuemart_custom_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `custom_parent_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `custom_jplugin_id` int(1) NOT NULL DEFAULT '0',
  `custom_element` varchar(50) NOT NULL DEFAULT '',
  `admin_only` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:Display in admin only',
  `custom_title` varchar(255) NOT NULL DEFAULT '' COMMENT 'field title',
  `show_title` tinyint(1) NOT NULL DEFAULT '1',
  `custom_tip` varchar(255) NOT NULL DEFAULT '' COMMENT 'tip',
  `custom_value` varchar(4095) COMMENT 'default value',
  `custom_desc` varchar(4095) COMMENT 'description or unit',
  `field_type` varchar(2) NOT NULL DEFAULT '0' COMMENT 'S:string,I:int,P:parent, B:bool,D:date,T:time,H:hidden',
  `is_list` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'list of values',
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:hidden',
  `is_cart_attribute` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Add attributes to cart',
  `is_input` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Add input to cart',
  `searchable` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Available as search filter',
  `layout_pos` varchar(24) COMMENT 'Layout Position',
  `custom_params` text  NOT NULL,
  `shared` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'valid for all vendors?',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_custom_id`),
  KEY `custom_parent_id` (`custom_parent_id`),
	KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  KEY `custom_element` (`custom_element`),
  KEY `field_type` (`field_type`),
  KEY `is_cart_attribute` (`is_cart_attribute`),
  KEY `is_input` (`is_input`),
  KEY `searchable` (`searchable`),
  KEY `shared` (`shared`),
  KEY `published` (`published`),
  KEY `ordering` (`ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='custom fields definition' AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `#__virtuemart_invoices` (
  `virtuemart_invoice_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `virtuemart_order_id` int(1) UNSIGNED,
  `invoice_number` varchar(64),
  `order_status` char(2),
  `xhtml` text,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_invoice_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`,`virtuemart_vendor_id`),
  KEY `virtuemart_order_id` (`virtuemart_order_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='custom fields definition' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_manufacturers`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_manufacturers` (
  `virtuemart_manufacturer_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_manufacturercategories_id` int(1),
  `metarobot` varchar(400),
  `metaauthor` varchar(400),
  `hits` int(1) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_manufacturer_id`),
  UNIQUE KEY `virtuemart_manufacturercategories_id` (`virtuemart_manufacturer_id`,`virtuemart_manufacturercategories_id`),
  KEY `published` (`published`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Manufacturers are those who deliver products' AUTO_INCREMENT=1 ;

--
-- Table structure for table `#__virtuemart_manufacturer_medias`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_manufacturer_medias` (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_manufacturer_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_media_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ordering` (`ordering`),
  UNIQUE KEY `virtuemart_manufacturer_id` (`virtuemart_manufacturer_id`,`virtuemart_media_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_manufacturercategories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_manufacturercategories` (
  `virtuemart_manufacturercategories_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_manufacturercategories_id`),
  KEY `published` (`published`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Manufacturers are assigned to these categories' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_medias` (was  `#__virtuemart_product_files`)
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_medias` (
  `virtuemart_media_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `file_title` varchar(126) NOT NULL DEFAULT '',
  `file_description` varchar(254) NOT NULL DEFAULT '',
  `file_meta` varchar(254) NOT NULL DEFAULT '',
  `file_class` varchar(64) NOT NULL DEFAULT '',
  `file_mimetype` varchar(64) NOT NULL DEFAULT '',
  `file_type` varchar(32) NOT NULL DEFAULT '',
  `file_url` varchar(900) NOT NULL DEFAULT '',
  `file_url_thumb` varchar(900) NOT NULL DEFAULT '',
  `file_is_product_image` tinyint(1) NOT NULL DEFAULT '0',
  `file_is_downloadable` tinyint(1) NOT NULL DEFAULT '0',
  `file_is_forSale` tinyint(1) NOT NULL DEFAULT '0',
  `file_params` varchar(12287) NOT NULL DEFAULT '',
  `file_lang` varchar(500) NOT NULL DEFAULT '',
  `shared` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_media_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  KEY `published` (`published`),
  KEY `file_type` (`file_type`),
  KEY `shared` (`shared`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Additional Images and Files which are assigned to products' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_migration_oldtonew_ids` (only used for migration)
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_migration_oldtonew_ids` (
	    `id` smallint(1) UNSIGNED NOT NULL AUTO_INCREMENT,
	    `cats` longblob,
	    `catsxref` blob,
	    `manus` longblob,
	    `mfcats` blob,
	    `shoppergroups` longblob,
	    `products` longblob,
	    `products_start` int(1),
	    `orderstates` blob,
	    `orders` longblob,
	    `attributes` longblob,
	    `relatedproducts` longblob,
	    `orders_start` int(1),
	    PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='xref table for vm1 ids to vm2 ids' ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_modules`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_modules` (
  `module_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_name` char(255),
  `module_description` varchar(15359),
  `module_perms` char(255),
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `is_admin` enum('0','1') NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`module_id`),
  KEY `module_name` (`module_name`),
  KEY `ordering` (`ordering`),
  KEY `published` (`published`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='VirtueMart Core Modules, not: Joomla modules' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_orders`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_orders` (
  `virtuemart_order_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_user_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `order_number` varchar(64),
  `customer_number` varchar(32),
  `order_pass` varchar(34),
  `order_create_invoice_pass` varchar(32),
  `order_total` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_salesPrice` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_billTaxAmount` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_billTax` varchar(400),
  `order_billDiscountAmount` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_discountAmount` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_subtotal` decimal(15,5),
  `order_tax` decimal(12,5),
  `order_shipment` decimal(12,5),
  `order_shipment_tax` decimal(10,5),
  `order_payment` decimal(12,2),
  `order_payment_tax` decimal(10,5),
  `coupon_discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `coupon_code` varchar(32),
  `order_discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `order_currency` smallint(1),
  `order_status` char(1),
  `user_currency_id` smallint(1),
  `user_currency_rate` DECIMAL(12,6) NOT NULL DEFAULT '1.000000',
  `user_shoppergroups` varchar(30),
  `payment_currency_id` smallint(1),
  `payment_currency_rate` DECIMAL(12,6) NOT NULL DEFAULT '1.000000',
  `virtuemart_paymentmethod_id` int(1) UNSIGNED,
  `virtuemart_shipmentmethod_id` int(1) UNSIGNED,
  `delivery_date` varchar(200),
  `order_language` varchar(7),
  `ip_address` char(15) NOT NULL DEFAULT '',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_order_id`),
  KEY `virtuemart_user_id` (`virtuemart_user_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  KEY `order_number` (`order_number`),
  KEY `virtuemart_paymentmethod_id` (`virtuemart_paymentmethod_id`),
  KEY `virtuemart_shipmentmethod_id` (`virtuemart_shipmentmethod_id`),
  KEY `created_on` (`created_on`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Used to store all orders' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_order_histories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_order_histories` (
  `virtuemart_order_history_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_order_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `order_status_code` char(1) NOT NULL DEFAULT '0',
  `customer_notified` tinyint(1) NOT NULL DEFAULT '0',
  `comments` varchar(15359),
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_order_history_id`),
  KEY `virtuemart_order_id` (`virtuemart_order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores all actions and changes that occur to an order' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_order_items`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_order_items` (
  `virtuemart_order_item_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_order_id` int(1) UNSIGNED,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `virtuemart_product_id` int(1),
  `order_item_sku` varchar(255) NOT NULL DEFAULT '',
  `order_item_name` varchar(4096) NOT NULL DEFAULT '',
  `product_quantity` int(1),
  `product_item_price` decimal(15,5),
  `product_priceWithoutTax` decimal(15,5),
  `product_tax` decimal(15,5),
  `product_basePriceWithTax` decimal(15,5),
  `product_discountedPriceWithoutTax` decimal(15,5),
  `product_final_price` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `product_subtotal_discount` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `product_subtotal_with_tax` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_item_currency` INT(1),
  `order_status` char(1),
  `product_attribute` mediumtext,
  `delivery_date` varchar(200),
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_order_item_id`),
  KEY `virtuemart_product_id` (`virtuemart_product_id`),
  KEY `virtuemart_order_id` (`virtuemart_order_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Stores all items (products) which are part of an order' AUTO_INCREMENT=1 ;
-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_order_calc_rules`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_order_calc_rules` (
  `virtuemart_order_calc_rule_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_calc_id` int(1) UNSIGNED,
  `virtuemart_order_id` int(1) UNSIGNED,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `virtuemart_order_item_id` int(1),
  `calc_rule_name`  varchar(64) NOT NULL DEFAULT '' COMMENT 'Name of the rule',
  `calc_kind` varchar(16) NOT NULL DEFAULT '' COMMENT 'Discount/Tax/Margin/Commission',
  `calc_mathop` varchar(16) NOT NULL DEFAULT '' COMMENT 'Discount/Tax/Margin/Commission',
  `calc_amount` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `calc_result` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `calc_value` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `calc_currency` int(1),
  `calc_params` varchar(15359) NOT NULL DEFAULT '',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_order_calc_rule_id`),
  KEY `virtuemart_calc_id` (`virtuemart_calc_id`),
  KEY `virtuemart_order_id` (`virtuemart_order_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores all calculation rules which are part of an order' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_orderstates`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_orderstates` (
  `virtuemart_orderstate_id` tinyint(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `order_status_code` char(1) NOT NULL DEFAULT '',
  `order_status_name` varchar(64),
  `order_status_color` varchar(64),
  `order_status_description` varchar(15359),
  `order_stock_handle` char(1) NOT NULL DEFAULT 'A',
  `ordering` int(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_orderstate_id`),
  KEY `ordering` (`ordering`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  KEY `published` (`published`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='All available order statuses' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_paymentmethods`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_paymentmethods` (
  `virtuemart_paymentmethod_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `payment_jplugin_id` int(1) NOT NULL DEFAULT '0',
  `payment_element` varchar(50) NOT NULL DEFAULT '',
  `payment_params` text,
  `currency_id` int(1) UNSIGNED,
  `shared` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'valide for all vendors?',
  `ordering` int(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_paymentmethod_id`),
	KEY `payment_jplugin_id` (`payment_jplugin_id`),
	KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
	KEY `payment_element` (payment_element,`virtuemart_vendor_id`),
	KEY `ordering` (`ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='The payment methods of your store' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_paymentmethod_shoppergroups`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_paymentmethod_shoppergroups` (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_paymentmethod_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_shoppergroup_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_paymentmethod_id` (`virtuemart_paymentmethod_id`,`virtuemart_shoppergroup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='xref table for paymentmethods to shoppergroup' AUTO_INCREMENT=1 ;



-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_products`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_products` (
  `virtuemart_product_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `product_parent_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `product_sku` varchar(255),
  `product_gtin` varchar(64),
  `product_mpn` varchar(64),
  `product_weight` decimal(10,4),
  `product_weight_uom` varchar(7),
  `product_length` decimal(10,4),
  `product_width` decimal(10,4),
  `product_height` decimal(10,4),
  `product_lwh_uom` varchar(7),
  `product_url` varchar(255),
  `product_in_stock` int(1) NOT NULL DEFAULT '0',
  `product_ordered` int(1) NOT NULL DEFAULT '0',
  `product_stockhandle` varchar(24) NOT NULL DEFAULT '0',
  `low_stock_notification` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `product_available_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `product_availability` varchar(32),
  `product_special` tinyint(1) NOT NULL DEFAULT '0',
  `product_discontinued` tinyint(1) NOT NULL DEFAULT '0',
  `product_sales` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `product_unit` varchar(8),
  `product_packaging` decimal(8,4) UNSIGNED,
  `product_params` varchar(255) NOT NULL,
  `hits` int(1) unsigned,
  `intnotes` text,
  `metarobot` varchar(400),
  `metaauthor` varchar(400),
  `layout` varchar(16),
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `pordering` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_product_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  KEY `product_parent_id` (`product_parent_id`),
  KEY `product_special` (`product_special`),
  KEY `product_discontinued` (`product_discontinued`),
  KEY `product_in_stock` (`product_in_stock`),
  KEY `product_ordered` (`product_ordered`),
  KEY `published` (`published`),
  KEY `pordering` (`pordering`),
  KEY `created_on` (`created_on`),
  KEY `modified_on` (`modified_on`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='All products are stored here.' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_product_categories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_categories` (
  `id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_product_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_category_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_product_id` (`virtuemart_product_id`,`virtuemart_category_id`),
  KEY `ordering` (`ordering`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Maps Products to Categories';

-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_product_shoppergroups`
--
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_shoppergroups` (
  `id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_product_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_shoppergroup_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_product_id` (`virtuemart_product_id`,`virtuemart_shoppergroup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Maps Products to Categories';

-- --------------------------------------------------------
--
-- Table structure `#__virtuemart_product_customfields`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_customfields` (
  `virtuemart_customfield_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'field id',
  `virtuemart_product_id` int(1) NOT NULL DEFAULT '0',
  `virtuemart_custom_id` int(1) NOT NULL DEFAULT '1' COMMENT 'custom group id',
  `customfield_value` varchar(2500) COMMENT 'field value',
  `customfield_price` decimal(15,6) COMMENT 'price',
  `disabler` INT(1) UNSIGNED NOT NULL DEFAULT '0',
  `override` INT(1) UNSIGNED NOT NULL DEFAULT '0',
  `customfield_params` text COMMENT 'Param for Plugins',
  `product_sku` varchar(64),
  `product_gtin` varchar(64),
  `product_mpn` varchar(64),
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_customfield_id`),
  KEY `virtuemart_product_id` (`virtuemart_product_id`,`ordering`),
  KEY `virtuemart_custom_id` (`virtuemart_custom_id`),
  KEY `published` (`published`),
  KEY `ordering` (`ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='custom fields' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_product_medias`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_medias` (
  `id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_product_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_media_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `virtuemart_media_id` (`virtuemart_media_id`),
  UNIQUE KEY `virtuemart_product_id` (`virtuemart_product_id`,`virtuemart_media_id`),
  KEY `ordering` (`virtuemart_product_id`, `ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_product_manufacturers`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_manufacturers` (
  `id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_product_id` int(1),
  `virtuemart_manufacturer_id` int(1) UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_product_id` (`virtuemart_product_id`,`virtuemart_manufacturer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Maps a product to a manufacturer';


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_product_prices`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_prices` (
  `virtuemart_product_price_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_product_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_shoppergroup_id` int(1) UNSIGNED  NOT NULL DEFAULT '0',
  `product_price` decimal(15,6),
  `override` tinyint(1),
  `product_override_price` decimal(15,5),
  `product_tax_id` int(1),
  `product_discount_id` int(1),
  `product_currency` smallint(1),
  `product_price_publish_up` datetime NOT NULL default '0000-00-00 00:00:00',
  `product_price_publish_down` datetime NOT NULL default '0000-00-00 00:00:00',
  `price_quantity_start` int(1) unsigned NOT NULL default '0',
  `price_quantity_end` int(1) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_product_price_id`),
  KEY `virtuemart_product_id` (`virtuemart_product_id`),
  KEY `product_price` (`product_price`),
  KEY `virtuemart_shoppergroup_id` (`virtuemart_shoppergroup_id`),
  KEY `product_price_publish_up` (`product_price_publish_up`),
  KEY `product_price_publish_down` (`product_price_publish_down`),
  KEY `price_quantity_start` (`price_quantity_start`),
  KEY `price_quantity_end` (`price_quantity_end`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds price records for a product' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_rating_reviews`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_rating_reviews` (
  `virtuemart_rating_review_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_rating_vote_id` INT(1) UNSIGNED,
  `virtuemart_product_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `comment` varchar(15359),
  `review_ok` tinyint(1) NOT NULL DEFAULT '0',
  `review_rates` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `review_ratingcount` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `review_rating` decimal(10,2) NOT NULL DEFAULT '0.00',
  `review_editable` tinyint(1) NOT NULL DEFAULT '1',
  `lastip` char(50) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `customer` varchar(128) NOT NULL DEFAULT '',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_rating_review_id`),
  KEY `virtuemart_rating_vote_id` (`virtuemart_rating_vote_id`),
  KEY `virtuemart_product_id` (`virtuemart_product_id`,`created_by`),
  KEY `created_on` (`created_on`),
  KEY `created_by` (`created_by`),
  KEY `published` (`published`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_ratings`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_ratings` (
  `virtuemart_rating_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_product_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `rates` int(1) NOT NULL DEFAULT '0',
  `ratingcount` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `rating` decimal(10,1) NOT NULL DEFAULT '0.0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_rating_id`),
  UNIQUE KEY `virtuemart_product_id` (`virtuemart_product_id`,`virtuemart_rating_id`),
  KEY `published` (`published`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Stores all ratings for a product';


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_rating_votes`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_rating_votes` (
  `virtuemart_rating_vote_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_product_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `vote` int(1) NOT NULL DEFAULT '0',
  `lastip` char(50) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_rating_vote_id`),
  KEY `virtuemart_product_id` (`virtuemart_product_id`,`created_by`),
  KEY `created_by` (`created_by`),
  KEY `created_on` (`created_on`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Stores all ratings for a product';


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_shipmentmethods`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_shipmentmethods` (
  `virtuemart_shipmentmethod_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `shipment_jplugin_id` int(1) NOT NULL DEFAULT '0',
  `shipment_element` varchar(50) NOT NULL DEFAULT '',
  `shipment_params` text,
  `currency_id` int(1) UNSIGNED,
  `ordering` int(1) NOT NULL DEFAULT '0',
  `shared` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_shipmentmethod_id`),
	KEY `shipment_jplugin_id` (`shipment_jplugin_id`),
	KEY `shipment_element` (shipment_element,`virtuemart_vendor_id`),
	KEY `ordering` (`ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Shipment created from the shipment plugins' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_shipmentmethods_shoppergroups`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_shipmentmethod_shoppergroups` (
  `id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_shipmentmethod_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_shoppergroup_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_shipmentmethod_id` (`virtuemart_shipmentmethod_id`,`virtuemart_shoppergroup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='xref table for shipment to shoppergroup' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_shoppergroups`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_shoppergroups` (
  `virtuemart_shoppergroup_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `shopper_group_name` varchar(128),
  `shopper_group_desc` varchar(255),
  `custom_price_display` tinyint(1) NOT NULL DEFAULT '0',
  `price_display` blob NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `sgrp_additional` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  `shared` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_shoppergroup_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  KEY `shopper_group_name` (`shopper_group_name`),
  KEY `ordering` (`ordering`),
  KEY `shared` (`shared`),
  KEY `published` (`published`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Shopper Groups that users can be assigned to' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


--
-- Table structure for table `#__virtuemart_states`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_states` (
  `virtuemart_state_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `virtuemart_country_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_worldzone_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `state_name` varchar(64),
  `state_3_code` char(3),
  `state_2_code` char(2),
  `ordering` int(1) NOT NULL DEFAULT '0',
  `shared` tinyint(1) NOT NULL DEFAULT '1',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_state_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  UNIQUE KEY `state_3_code` (`virtuemart_vendor_id`,`virtuemart_country_id`,`state_3_code`),
  UNIQUE KEY `state_2_code` (`virtuemart_vendor_id`,`virtuemart_country_id`,`state_2_code`),
  KEY `virtuemart_country_id` (`virtuemart_country_id`),
  KEY `ordering` (`ordering`),
  KEY `shared` (`shared`),
  KEY `published` (`published`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='States that are assigned to a country' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_userfields`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_userfields` (
  `virtuemart_userfield_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `userfield_jplugin_id` int(1) NOT NULL DEFAULT '0',
  `name` varchar(250) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(2048),
  `type` varchar(70) NOT NULL DEFAULT '',
  `maxlength` int(1),
  `size` int(1),
  `required` tinyint(4) NOT NULL DEFAULT '0',
  `cols` int(1),
  `rows` int(1),
  `value` varchar(255),
  `default` varchar(255),
  `registration` tinyint(1) NOT NULL DEFAULT '0',
  `shipment` tinyint(1) NOT NULL DEFAULT '0',
  `account` tinyint(1) NOT NULL DEFAULT '1',
  `cart` tinyint(1) NOT NULL DEFAULT '0',
  `readonly` tinyint(1) NOT NULL DEFAULT '0',
  `calculated` tinyint(1) NOT NULL DEFAULT '0',
  `sys` tinyint(4) NOT NULL DEFAULT '0',
  `userfield_params` text,
  `ordering` int(1) NOT NULL DEFAULT '0',
  `shared` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_userfield_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  UNIQUE KEY `name` (`name`),
  KEY `ordering` (`ordering`),
  KEY `shared` (`shared`),
  KEY `published` (`published`),
  KEY `account` (`account`),
  KEY `shipment` (`shipment`),
  KEY `cart` (`cart`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds the fields for the user information' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_userfield_values`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_userfield_values` (
  `virtuemart_userfield_value_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_userfield_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `fieldtitle` varchar(255) NOT NULL DEFAULT '',
  `fieldvalue` varchar(255) NOT NULL DEFAULT '',
  `sys` tinyint(4) NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_userfield_value_id`),
  KEY `virtuemart_userfield_id` (`virtuemart_userfield_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds the different values for dropdown and radio lists' AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_vendors`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_vendors` (
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendor_name` varchar(64),
  `vendor_currency` int(1),
  `vendor_accepted_currencies` varchar(1536) NOT NULL DEFAULT '',
  `vendor_params` varchar(14335) NOT NULL DEFAULT '',
  `metarobot` varchar(20),
  `metaauthor` varchar(64),
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_vendor_id`),
  KEY `vendor_name` (`vendor_name`),
  KEY `vendor_currency` (`vendor_currency`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Vendors manage their products in your store' AUTO_INCREMENT=1 ;


--
-- Table structure for table `#__virtuemart_vendor_medias`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_vendor_medias` (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_media_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`,`virtuemart_media_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `#__virtuemart_vendor_users` (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_user_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`,`virtuemart_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_vmusers`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_vmusers` (
  `virtuemart_user_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `user_is_vendor` tinyint(1) NOT NULL DEFAULT '0',
  `customer_number` varchar(32),
  `virtuemart_paymentmethod_id` int(1) UNSIGNED,
  `virtuemart_shipmentmethod_id` int(1) UNSIGNED,
  `agreed` tinyint(1) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_user_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`),
  UNIQUE KEY `u_virtuemart_user_id` (`virtuemart_user_id`,`virtuemart_vendor_id`),
  KEY `user_is_vendor` (`user_is_vendor`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='Holds the unique user data' ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_vmuser_shoppergroups`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_vmuser_shoppergroups` (
  `id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_user_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_shoppergroup_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtuemart_user_id` (`virtuemart_user_id`,`virtuemart_shoppergroup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='xref table for users to shopper group' ;


-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_waitingusers`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_waitingusers` (
  `virtuemart_waitinguser_id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_product_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `virtuemart_user_id` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `notify_email` varchar(150) NOT NULL DEFAULT '',
  `notified` tinyint(1) NOT NULL DEFAULT '0',
  `notify_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ordering` int(1) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_waitinguser_id`),
  KEY `virtuemart_product_id` (`virtuemart_product_id`),
  KEY `notify_email` (`notify_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores notifications, users waiting f. products out of stock' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_worldzones`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_worldzones` (
  `virtuemart_worldzone_id` smallint(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `virtuemart_vendor_id` int(1) UNSIGNED,
  `zone_name` varchar(255),
  `zone_cost` decimal(10,2),
  `zone_limit` decimal(10,2),
  `zone_description` varchar(14335),
  `zone_tax_rate` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `ordering` int(1) NOT NULL DEFAULT '0',
  `shared` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(1) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`virtuemart_worldzone_id`),
  KEY `virtuemart_vendor_id` (`virtuemart_vendor_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='The Zones managed by the Zone Shipment Module' AUTO_INCREMENT=1 ;

