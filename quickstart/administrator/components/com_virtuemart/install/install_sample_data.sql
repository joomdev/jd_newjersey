INSERT IGNORE INTO `#__virtuemart_calcs` (`virtuemart_calc_id`, `calc_name`, `calc_descr`, `calc_kind`, `calc_value_mathop`, `calc_value`, `calc_currency`, `ordering`, `published`) VALUES
	(1, 'Tax 20%', 'common vat, if your shop needs only one VAT, just use it without any category set', 'VatTax', '+%', 20.0000, 47, 0, 1),
	(2, 'Discount 5% for ladies', 'The discount is based on net price with VAT, the tax amount is recalculated based on the new net price', 'DATax', '-%', 5.0000, 47, 0, 1),
	(3, 'Tax 10%', 'as example for the category product variants', 'VatTax', '+%', 10.0000, 47, 0, 1),
	(4, 'Tax 7%', 'as example for the category product attributes', 'VatTax', '+%', 7.0000, 47, 0, 1);

INSERT IGNORE INTO `#__virtuemart_calc_categories` (`virtuemart_calc_id`, `virtuemart_category_id`) VALUES
  (1, 2),
  (1, 3),
  (1, 4),
  (1, 7),
  (1, 8),
  (1, 9),
	(2, 9),
	(3, 11),
	(4, 12);

INSERT IGNORE INTO `#__virtuemart_categories` (`virtuemart_category_id`, `virtuemart_vendor_id`, `ordering`, `shared`, `published`) VALUES
	(2, 1, 2, 1, 1),
	(3, 1, 3, 1, 1),
	(4, 1, 4, 1, 1),
	(7, 1, 5, 1, 1),
	(8, 1, 7, 1, 1),
	(9, 1, 6, 1, 1),
	(11, 1, 1, 1, 1),
	(12, 1, 0, 1, 1);

INSERT IGNORE INTO `#__virtuemart_categories_XLANG` (`virtuemart_category_id`, `category_name`, `category_description`, `metadesc`, `metakey`, `customtitle`, `slug`) VALUES
  (2, 'Product pattern', '<p><span style="background-color: #fcdb73; text-align: center; padding: 5px 40px;">Example for usage of product pattern. For showcase reason the PATTERN is NOT unpublished.</span></p>', '', '', '', 'product-pattern'),
	(3, 'Pagination', '<p>Use this category to test the ordering of products. Sort order by Name, SKU, Manufacturer (more available in vmconfig &gt; product order settings)<br />Additionally filter by Manufacturer,</p>&#13;&#10;<p style="background-color: #fcdb73; text-align: center; padding: 5px 40px;"><strong>Advise:</strong> There are three pattern HGD#0, CEG#0, FAC#0. The last two digits represent the sort order.</p>', '', '', '', 'pagination'),
	(4, 'Headpiece', '', '', '', '', 'headpiece'),
	(7, 'Wear', '', '', '', '', 'wear'),
	(8, 'Mister', '<p>Sample for Subcategory. <br />Select superordinated category in VM BE &gt; <em>Product Categories</em> &gt; Your Category in section <em>Details &gt; Category Ordering </em></p>', '', '', '', 'mister'),
	(9, 'Lady', '<p>Sample for Subcategory. <br />Select superordinated category in VM BE &gt; <em>Product Categories</em> &gt; Your Category in section <em>Details &gt; Category Ordering </em></p>', '', '', '', 'lady'),
	(11, 'Product variants', '<p><span style="background-color: #fcdb73; text-align: center; padding: 5px 40px;">Product variants by customfields w/ user input.</span></p>', '', '', '', 'product-variants'),
	(12, 'Product attributes', '<p><span style="background-color: #fcdb73; text-align: center; padding: 5px 40px;">Products using customfields as attribute.</span></p>', '', '', '', 'product-attributes');

INSERT IGNORE INTO `#__virtuemart_category_categories` (`id`, `category_parent_id`, `category_child_id`, `ordering`) VALUES
	(2, 0, 2, 4),
	(3, 0, 3, 6),
	(4, 0, 4, 6),
	(11, 0, 11, 1),
	(7, 0, 7, 5),
	(8, 7, 8, 8),
	(9, 7, 9, 7),
	(12, 0, 12, 2);

INSERT IGNORE INTO `#__virtuemart_category_medias` (`virtuemart_category_id`, `virtuemart_media_id`, `ordering`) VALUES
	(12, 10, 1),
	(3, 10, 1),
	(2, 10, 1),
	(7, 11, 1),
	(4, 12, 1),
	(8, 11, 1),
	(11, 10, 1),
	(9, 13, 1);

INSERT IGNORE INTO `#__virtuemart_coupons` (`virtuemart_coupon_id`, `virtuemart_vendor_id`, `coupon_code`, `percent_or_total`, `coupon_type`, `coupon_value`, `coupon_start_date`, `coupon_expiry_date`, `coupon_value_valid`, `coupon_used`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES
	(1, 1,'Sample Coupon', 'total', 'permanent', 0.01000, '', '', 0.00000, '0', 1, '', 635, '', 635, '', 0);

INSERT IGNORE INTO `#__virtuemart_customs` (`virtuemart_custom_id`, `custom_parent_id`, `ordering`, `field_type`, `is_cart_attribute`, `is_input`, `is_list`, `searchable`, `layout_pos`, `custom_title`, `custom_tip`, `custom_desc`, `custom_value`, `custom_params`, `show_title`, `published`) VALUES
 (01, 0, 0, 'R', 0, 0, 0, 0,'related_products','COM_VIRTUEMART_RELATED_PRODUCTS','COM_VIRTUEMART_RELATED_PRODUCTS_TIP','COM_VIRTUEMART_RELATED_PRODUCTS_DESC', 'related_products','wPrice="1"|wImage="1"|wDescr="1"|', 1, 1),
 (02, 0, 0, 'Z', 0, 0, 0, 0, 'related_categories','COM_VIRTUEMART_RELATED_CATEGORIES','COM_VIRTUEMART_RELATED_CATEGORIES_TIP','COM_VIRTUEMART_RELATED_CATEGORIES_DESC', 'related_categories','wImage="1"|wDescr="1"|', 1, 1),
 (09, 0, 0, 'G', 0, 0, 0, 0, '','Group','Group','set of cart attribute', '','', 1, 1),

 (100, 0, 0, 'S', 0, 0, 0, 1, '','String','Also with tooltip','For additional searchable product attributes displayed in a consistent format in the product description', '','', 1, 1),
 (98, 0, 0, 'S', 1, 0, 0, 1, '','String, searchable attribute','Show charactistic product attributs in the cart','For additional searchable product attributes displayed in a consistent format in the product description and cart.', '','', 1, 1),

 (96, 0, 0, 'S', 0, 1, 0, 0, 'addtocart','String, is input','Select a variant','Combine customfields of the same type to create flexible selections (dropdowns, radio lists)', '','addEmpty="0"|selectType="0"|', 1, 1),
 (95, 0, 0, 'S', 0, 1, 0, 0, 'addtocart','String, is input optional','Select a variant','Combine customfields of the same type to create flexible selections (dropdowns, radio lists)', '','addEmpty="1"|selectType="0"|', 1, 1),
 (94, 0, 0, 'S', 0, 1, 1, 0, 'addtocart','String, is input list','Select a colour','Directly create a reusable dropdown by using values seperated by ;', 'White;Red;Green;Blue;Yellow;Black','addEmpty="0"|selectType="0"|', 1, 1),
 (93, 0, 0, 'S', 0, 1, 1, 0, 'addtocart','String, is input list optional','Select a colour','Directly create a reusable dropdown by using values seperated by ;', 'Floral;Paisly;Fret;Camouflage;Checker','addEmpty="1"|selectType="0"|', 1, 1),
 (92, 0, 0, 'S', 0, 1, 2, 0, 'addtocart','String, is input admin list','Select a colour','Admin list for faster product editing, combine customfields of the same type, but the choice is predefined', 'White;Red;Green;Blue;Yellow;Black','addEmpty="0"|selectType="0"|', 1, 1),
  (91, 0, 0, 'S', 0, 1, 2, 0, 'addtocart','String, is input admin list optional','Select optional pattern','Admin list with empty option', 'Floral;Paisly;Fret;Camouflage;Checker','addEmpty="1"|selectType="0"|', 1, 1),

	(90, 0, 0, 'S', 0, 1, 0, 0, 'addtocart','String, is input radio','Select a variant','Combine Customfields of the same type to create flexible selections (dropdowns, radio lists)', '','addEmpty="0"|selectType="1"|', 1, 1),
	(89, 0, 0, 'S', 0, 1, 0, 0, 'addtocart','String, is input radio optional','Select a variant','Combine Customfields of the same type to create flexible selections (dropdowns, radio lists)', '','addEmpty="1"|selectType="1"|', 1, 1),
 (88, 0, 0, 'S', 0, 1, 1, 0, 'addtocart','String, is input list radio','Select a variant','Directly create a reusable dropdown by using values seperated by ;', 'White;Black','addEmpty="0"|selectType="1"|', 1, 1),
 (87, 0, 0, 'S', 0, 1, 1, 0, 'addtocart','String, is input list radio optional','Select optional variant','Directly create a reusable dropdown by using values seperated by ;', 'Floral;Paisly;Camouflage','addEmpty="1"|selectType="1"|', 1, 1),
  (86, 0, 0, 'S', 0, 1, 2, 0, 'addtocart','String, is input admin list radio','Select variant','Combine Customfields of the same type to create flexible selections (dropdowns, radio lists), but the choice is predefined', 'Red;Green;Blue;','addEmpty="0"|selectType="1"|', 1, 1),
  (84, 0, 0, 'S', 0, 1, 2, 0, 'addtocart','String, is input admin list radio optional','Select optional variant','Combine Customfields of the same type to create flexible selections (dropdowns, radio lists), but the choice is predefined', 'Fret;Checker','addEmpty="1"|selectType="1"|', 1, 1),

	(80, 0, 0, 'M', 0, 1, 0, 0, 'addtocart','Media, is input','Select a variant','Combine customfields of the same type to create flexible selections (dropdowns, radio lists)', '','addEmpty="0"|selectType="0"|', 1, 1),
	(79, 0, 0, 'M', 0, 1, 0, 0, 'addtocart','Media, is input optional','Select a variant','Combine customfields of the same type to create flexible selections (dropdowns, radio lists)', '','addEmpty="1"|selectType="1"|', 1, 1),
	(78, 0, 0, 'M', 0, 1, 1, 0, 'addtocart','Media, is input list','Select a colour','Directly create a reusable dropdown by using values seperated by ;', '11;29','addEmpty="0"|selectType="1"|', 1, 1),
	(77, 0, 0, 'M', 0, 1, 1, 0, 'addtocart','Media, is input list optional','Select a colour','Directly create a reusable dropdown by using values seperated by ;', '21;22;23','addEmpty="1"|selectType="1"|', 1, 1),
	(76, 0, 0, 'M', 0, 1, 2, 0, 'addtocart','Media, is input admin list','Select a colour','Admin list for faster product editing, combine customfields of the same type, but the choice is predefined', '11;29','addEmpty="0"|selectType="1"|', 1, 1),
	(74, 0, 0, 'M', 0, 1, 2, 0, 'addtocart','Media, is input admin list optional','Select optional pattern','Admin list with empty option', '21;22;23','addEmpty="1"|selectType="1"|', 1, 1),

 (11, 9, 1, 'S', 1, 0, 0, 0, '','String, attribute','Use values separated by ; to directly select the value in the backend','', '','', 1, 1),
 (12, 0, 0, 'S', 1, 0, 1, 0, '','String, list','Use values separated by ; to directly select the value in the backend','', 'Cotton;Wool;Flax;Nylon;Polyester','', 1, 1),
 (13, 0, 0, 'S', 1, 1, 0, 0, 'addtocart','String, is input','Select a variant','', 'Combine Customfields of the same type to create flexible selections (dropdowns, radio lists)','', 1, 1),
 (14, 0, 0, 'S', 1, 1, 2, 0, 'addtocart','String, admin list','Select a variant','', 'Cotton;Wool;Flax;Nylon;Polyester','', 1, 1),
 (15, 9, 0, 'M', 1, 0, 1, 0, '','Media, list','Also with tooltip','extra Image', '20;21;22;23;24','', 1, 1),
 (16, 9, 3, 'X', 0, 0, 0, 0, '','Editor','Show extra conditions','Testimonial', 'Use the texteditor to display extra text at predefined positions','', 1, 1),
 (17, 0, 0, 'D', 1, 0, 0, 0, 'addtocart','Date','Show date','Next delivery', '','', 1, 1),
 (18, 0, 0, 'T', 0, 0, 0, 0, 'addtocart','Time','Show time','Workshop at ', '','', 1, 1),
 (20, 0, 0, 'A', 1, 0, 0, 0, 'ontop','Generic Child Variant','Also with tooltip','', 'Use extra children tab for faster product editing','withParent="0"|parentOrderable="0"|wPrice="1"|', 1, 1),
 (21, 0, 0, 'C', 1, 0, 0, 0, 'addtocart','Multi Variant','Also with tooltip','', 'Use admin lists for faster product editing','usecanonical="1"|showlabels="0"|sCustomId="11"|selectoptions="0"|clabels="0"|options="0"|', 1, 1),
 (22, 0, 0, 'M', 1, 1, 0, 0, 'addtocart','Media, paid','Also with tooltip','paid extra Image', '','width="0"|height="0"|addEmpty="0"|selectType="1"|', 1, 1),
 (23, 0, 0, 'S', 1, 1, 0, 0, 'addtocart','String, is input alternative','Select a variant','', 'Combine Customfields of the same type to create flexible selections (dropdowns, radio lists)','', 1, 1),
 (24, 0, 0, 'P', 0, 0, 0, 0, '','Property','','', '','', 0, 1);

INSERT IGNORE INTO `#__virtuemart_product_customfields` (`virtuemart_customfield_id`, `virtuemart_product_id`, `virtuemart_custom_id`, `customfield_price`, `customfield_value`) VALUES
	(10000, 2000, 100, 0, 'Simple String'),
	(10001, 2000, 98, 0, 'String, in cart'),
	(10002, 2000, 96, 0, 'String variant A'),
	(10003, 2000, 96, 1, 'String variant B'),
	(10004, 2000, 96, 2, 'String variant C'),
	(10005, 2000, 95, 4, 'String asset D'),
	(10006, 2000, 95, 5, 'String asset E'),
	(10007, 2000, 94, 0, 'Red'),
	(10008, 2000, 93, 3, 'Red'),
	(10009, 2000, 92, 0, 'Green'),
	(10010, 2000, 92, 5, 'Blue'),
	(10011, 2000, 92, 6, 'Black'),
	(10012, 2000, 91, 8, 'Camouflage'),
	(10013, 2000, 91, 11, 'Paisly'),
	(10014, 2000, 91, 11, 'Floral'),
	(10015, 2000, 91, 9, 'Fret'),

	(10020, 2001, 90, 0, 'Stringvariant A'),
	(10021, 2001, 90, 1, 'Stringvariant B'),
	(10022, 2001, 90, 2, 'Stringvariant C'),
	(10023, 2001, 89, 4, 'String asset D'),
	(10024, 2001, 89, 5, 'String asset E'),
	(10025, 2001, 88, 0, 'Red'),
	(10026, 2001, 87, 3, 'Red'),
	(10027, 2001, 86, 5, 'Green'),
	(10028, 2001, 86, 6, 'Blue'),
	(10029, 2001, 86, 7, 'Black'),
	(10030, 2001, 84, 0, 'Camouflage'),
	(10031, 2001, 84, 4, 'Paisly'),
	(10032, 2001, 84, 8, 'Floral'),
	(10033, 2001, 84, 9, 'Fret'),

	(10040, 2003, 80, 0, '24'),
	(10041, 2003, 80, 1, '25'),
	(10042, 2003, 80, 2, '26'),
	(10043, 2003, 79, 4, '21'),
	(10044, 2003, 79, 5, '23'),
	(10045, 2003, 78, 0, '11'),
	(10046, 2003, 77, 3, '29'),
	(10047, 2003, 76, 5, '11'),
	(10048, 2003, 76, 6, '29'),
	(10049, 2003, 74, 0, '21'),
	(10050, 2003, 74, 4, '22'),
	(10051, 2003, 74, 8, '23');

INSERT IGNORE INTO `#__virtuemart_manufacturercategories` (`virtuemart_manufacturercategories_id`, `published`) VALUES
  (1, 1);

INSERT IGNORE INTO `#__virtuemart_manufacturercategories_XLANG` (`virtuemart_manufacturercategories_id`, `mf_category_name`, `mf_category_desc`, `slug`) VALUES
	(1, 'default', 'This is the default manufacturer category ', 'default');

INSERT IGNORE INTO `#__virtuemart_manufacturers` (`virtuemart_manufacturer_id`, `virtuemart_manufacturercategories_id`, `published`) VALUES
 	(1, 1, 1),
	(2, 1, 1),
	(3, 1, 1);

INSERT IGNORE INTO `#__virtuemart_manufacturers_XLANG` (`virtuemart_manufacturer_id`, `mf_name`, `mf_email`, `mf_desc`, `mf_url`, `slug`) VALUES
	(1, 'Manufacturer', 'manufacturer@example.org', '<p>An example for a manufacturer</p>', 'http://virtuemart.net', 'manufacturer'),
	(2, 'Default', 'example@manufacturer.net', '<p>Default manufacturer</p>', 'http://virtuemart.net', 'default'),
	(3, 'Producer', 'info@producer.com', '<p>An example for another manufacturer.</p>', 'http://virtuemart.net', 'producer');

INSERT IGNORE INTO `#__virtuemart_manufacturer_medias` (`virtuemart_manufacturer_id`, `virtuemart_media_id`, `ordering`) VALUES
	(3, 5, 1),
	(1, 6, 1),
	(2, 5, 1);

INSERT IGNORE INTO `#__virtuemart_medias` (`virtuemart_media_id`, `file_is_product_image`, `file_type`, `file_mimetype`, `file_title`, `file_description`, `file_meta`, `file_url`, `file_url_thumb`, `file_params`, `published`) VALUES
 (01, 0, 'vendor', 'image/gif','ShopLogo','Used in the invoice','virtuemart shop','images/virtuemart/vendor/vendor.gif', 'images/virtuemart/vendor/resized/vendor_0x90.gif', '', 1),

 (05, 0, 'manufacturer', 'image/jpeg', 'Manufacturer','','','images/virtuemart/manufacturer/manufacturer.jpg', '', '', 1),
 (06, 0, 'manufacturer', 'image/jpeg', 'Producer','','','images/virtuemart/manufacturer/producer.jpg', '', '', 1),

 (10, 0, 'category', 'image/jpeg', 'student hat', 'Products in this category showing tips and tricks','student_hat_16','images/virtuemart/category/student_hat_16.jpg', '', '', 1),
 (11, 0, 'product', 'image/png', 'T-Shirts', 'Warp5 T-Shirts','virtuemart warp5','images/virtuemart/product/tshirt5.png', '', '', 1),
 (12, 0, 'product', 'image/png', 'Hats', 'Hat #1','virtuemart #1','images/virtuemart/product/hat1.png', '', '', 1),
 (13, 0, 'product', 'image/png', 'Skirts', 'Skirt #1','virtuemart #1','images/virtuemart/product/skirt1.png', '', '', 1),

 (20, 1, 'product', 'image/jpeg', 'VM Cart Logo','The Famous VirtueMart Cart Logo','virtuemart cart logo','images/virtuemart/product/cart_logo.jpg', '', '', 1),
 (21, 1, 'product', 'image/png', 'Hat 1','VirtueMart Sample','virtuemart sample','images/virtuemart/product/hat1.png', '', '', 1),
 (22, 1, 'product', 'image/png', 'Hat 2','VirtueMart Sample','virtuemart sample','images/virtuemart/product/hat2.png', '', '', 1),
 (23, 1, 'product', 'image/png', 'Hat 3','VirtueMart Sample','virtuemart sample','images/virtuemart/product/hat3.png', '', '', 1),
 (24, 1, 'product', 'image/png', 'shirt 1','VirtueMart Sample','virtuemart sample','images/virtuemart/product/shirt1.png', '', '', 1),
 (25, 1, 'product', 'image/png', 'shirt 2','VirtueMart Sample','virtuemart sample','images/virtuemart/product/shirt2.png', '', '', 1),
 (26, 1, 'product', 'image/png', 'Coat','','','images/virtuemart/product/coat1.png', '', '', 1),
 (27, 1, 'product', 'image/png', 'Skirt 1','VirtueMart Sample','virtuemart sample','images/virtuemart/product/skirt1.png', '', '', 1),
 (28, 1, 'product', 'image/png', 'Skirt 2','VirtueMart Sample','virtuemart sample','images/virtuemart/product/skirt2.png', '', '', 1),
 (29, 1, 'product', 'image/png', 'T-Shirt EightBall','VirtueMart Sample','virtuemart sample','images/virtuemart/product/tshirt8.png', '', '', 1);

#Common associations for patterns
INSERT IGNORE INTO `#__virtuemart_products` (`virtuemart_product_id`, `product_parent_id`, `product_sku`, `product_weight`, `product_weight_uom`, `product_length`, `product_width`, `product_height`, `product_lwh_uom`, `product_in_stock`, `product_params`, `published`) VALUES
  (9, 0, 'root', 0.1, 'KG', 0.1000, 0.1000, 0.1000, 'M', 10, 'min_order_level=""|max_order_level=""|step_order_level=""|product_box="1"|', 0);
INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`, `product_s_desc`, `product_desc`) VALUES
  (9, 'Root Pattern', 'root', '','<p>This product is derived from a pattern for other products. It is a parent product and has multiple child products. <br />You can set several settings (content, customfields) for parent product. Childs of this parent will basically have the same settings as the parent automatically inherit except you overwrite the settings.<br /><br /></p>\r\n<p>In this case product price is set in pattern.</p><p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</p>\r\n<p>At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>\r\n<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.   <br /><br /></p>');

INSERT IGNORE INTO `#__virtuemart_product_manufacturers` (`virtuemart_product_id`, `virtuemart_manufacturer_id`) VALUES
  (9, 1),
  (163, 2),
  (170, 3),
  (200, 2),
  (204, 3),
  (300, 3);

INSERT IGNORE INTO `#__virtuemart_product_prices` (`virtuemart_product_id`, `product_price`, `product_currency`) VALUES
	(9, 10.000000, 47);

INSERT IGNORE INTO `#__virtuemart_product_medias` (`virtuemart_product_id`, `virtuemart_media_id`, `ordering`) VALUES
	(9, 20, 1);

#Product attributes
INSERT IGNORE INTO `#__virtuemart_products` (`virtuemart_product_id`, `product_parent_id`, `product_sku`, `product_special`, `product_in_stock`, `product_weight`, `published`, `pordering`) VALUES
	(163, 9, '', 0, 20, NULL, 1, 1),
	(164, 163, '', 0, 20, 5.0000, 1, 2),
	(165, 163, 'DP2', 0, 20, 1.0000, 1, 3),
	(166, 163, 'DP3', 0, 20, 10.0000, 1, 4),
	(167, 163, 'CFD', 0, 20, 10.0000, 1, 5),
	(168, 163, 'MPRS', 0, 20, 10.0000, 1, 6),
	(169, 163, 'MP002', 0, 20, 10.0000, 1, 7);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`, `product_s_desc`, `product_desc`) VALUES
	(163, 'PATTERN Product attributes', 'pattern-product-attributes', '', ''),
	(164, 'Default product', 'default-product', '', ''),
	(165, 'Free product', 'free-product', 'It\'s a free Product!', '<p>This product shows how a free product is set up. The shopper can purchase without beeing charged. In all cases the shopper needs to checkout.</p>\r\n<p>It can be used e.g. if you want to offer catalogues or sample products.</p>'),
	(166, 'String &#38; list, editor', 'string,-list,-editor', 'Default product with customfield string & editor. ', '<p>Please note: this example for string is no cart attribute, if you want to display the string detail in cart please enable Cart attribut in custom prototype.</p>'),
	(167, 'Images &#38; list', 'images-list', 'Showcase image customfield.', '<p>Use customfield to display an image or image list on desired layout position. <br /><br />Customfield image allows to display any of your media images, while image list provides a preset based on list in customfied prototype.<br />See for details be &gt; Custom Fields &gt; Image list</p>'),
	(168, 'Multiple price ranges', 'multiple-price-ranges', 'Price ranges for product quantity.', '<p>Price ranges for product quantity. Test out the price changes following the values below<br /><br />100€ 1-5 pcs</p>\r\n<p>80€ 6-10 pcs</p>\r\n<p>70€ 11- pcs</p>'),
	(169, 'Multiple prices', 'multiple-price-group', 'Mutliple prices per shoppergroups.', '<p>Your shoppergroup changes your price. Login to preview.<br /><br />100€ Guest</p>\r\n<p>80€ Registered</p>\r\n<p>50€ Gold Member</p>');

INSERT IGNORE INTO `#__virtuemart_product_customfields` (`virtuemart_product_id`, `virtuemart_custom_id`, `disabler`, `override`, `customfield_value`, `customfield_price`, `ordering`, `customfield_params`) VALUES
	(166, 16, 0, 0, '<p><strong>Editor field content.</strong> We have additional room here for more information. Use of <em>WYSIWYG</em> editor customfield here.</p>', NULL, 2, ''),
	(166, 12, 0, 0, 'Polyester', NULL, 1, ''),
	(166, 100, 0, 0, 'My string content here', NULL, 0, ''),
	(167, 15, 0, 0, '11', NULL, 2, 'width="0"|height="0"|'),
	(167, 15, 0, 0, '10', NULL, 1, 'width="0"|height="0"|'),
	(167, 16, 0, 0, '1', NULL, 0, 'width="0"|height="0"|');

INSERT IGNORE INTO `#__virtuemart_product_prices` (`virtuemart_product_id`, `virtuemart_shoppergroup_id`, `product_price`, `product_currency`, `price_quantity_start`, `price_quantity_end`) VALUES
  (163, 0, 10.000000, 47, NULL, NULL),
  (165, 0, 0.00001, 47, NULL, NULL),
  (168, 0, 100.000000, 47, 0, 5),
  (168, 0, 80.000000, 47, 6, 10),
  (168, 0, 70.000000, 47, 11, 0),
  (169, 0, 100.000000, 47, 0, 0),
  (169, 2, 80.000000, 47, 0, 0),
  (169, 4, 50.000000, 47, 0, 0);

INSERT IGNORE INTO `#__virtuemart_product_categories` (`virtuemart_product_id`, `virtuemart_category_id`, `ordering`) VALUES
	(164, 12, 0),
	(165, 12, 1),
	(166, 12, 2),
	(167, 12, 3),
	(168, 12, 4),
	(169, 12, 5);

INSERT IGNORE INTO `#__virtuemart_ratings` (`virtuemart_rating_id`, `virtuemart_product_id`, `rates`, `ratingcount`, `rating`, `published`) VALUES
  (160, 164, 5, 1, 4.9, 1),
  (161, 165, 4, 1, 4.3, 1),
  (162, 166, 5, 1, 4.8, 1),
  (163, 167, 5, 1, 4.2, 1),
  (164, 168, 4, 1, 3.8, 1),
  (165, 169, 5, 1, 5.0, 1);

#Showcase patterns
INSERT IGNORE INTO `#__virtuemart_products` (`virtuemart_product_id`, `product_parent_id`, `product_sku`, `product_special`, `product_in_stock`, `product_weight`, `published`, `pordering`) VALUES
	(200, 9, '005', 0, 10, 0.1000, 1, 1),
	(201, 200, '006', 0, 10, 0.1000, 1, 2),
	(202, 200, '007', 0, 10, NULL, 1, 3),
	(203, 200, '008', 0, 10, 4.0000, 1, 4),
	(204, 9, '009', 0, 10, 0.4000, 1, 5),
	(205, 204, '010', 0, 10, NULL, 1, 6),
	(206, 204, '011', 0, 10, NULL, 1, 7),
	(207, 204, '012', 0, 10, NULL, 1, 8);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`, `product_s_desc`, `product_desc`) VALUES
	(200, 'Basic PATTERN', 'basic-pattern', 'Showcase for pattern usage.', '<p>This product is used as a pattern for other products. It is a parent product and has multiple child products. <br />You can set several settings (content, customfields) for parent product. Childs of this parent will basically have the same settings as the parent automatically inherit except you overwrite the settings.<br /><br /></p>\r\n<p>In this case product price is set in pattern.</p>'),
  (201, 'Basic child', 'basic-pattern-1', 'This is a basic child of Product PATTERN.', '<p>This is a basic child of Product PATTERN. You see inherited settings, only Product description is overwritten.<br /></p>'),
  (202, 'Basic price overwrite', 'basic-price-overwrite', 'This is a basic child of Product PATTERN. You see inherited settings.', '<p>This is a basic child of Product PATTERN. You see inherited settings. <br />Overwritten are following setting/content:<br />- Product desc<br />- Product price<br /></p>'),
  (203, 'Basic multiple overwrites', 'basic-multiple-overwrites', 'Multiple overwrites short desc.', '<p>This is a child of Product PATTERN. Most inherited settings are overwritten: <br />- Short desc<br />- Product desc<br />- Product price<br />- Product Images<br />- Product Dimension and Weight (Units in Box)<br /></p>'),
  (204, 'Advanced PATTERN', 'advanced-pattern', 'Showcase advanced pattern usage.', '<p>This product is used as a pattern for other products. It is a parent product and has multiple child products. <br />You can set several settings (content, customfields) for parent product. Childs of this parent will basically have the same settings as the parent inherited until you overwrite.</p>\r\n<p>One of the hugest advantages is stock control ability.</p>'),
  (205, 'Advanced child', 'advanced-child', '', '<p>This is a basic child of Product PATTERN. You see inherited settings, only Product description is overwritten.<br /></p>'),
	(206, 'Advanced price overwrite', 'advanced-price-overwrite', '', '<p>This is a advanced child of Advanced PATTERN. You see inherited settings. <br />Overwritten are following setting/content:<br />- Product desc<br />- Product price<br /></p>'),
	(207, 'Advanced multiple overrides', 'advanced-multiple-overrides', 'Advanced multiple overrides', '<p>This is a child of Product PATTERN. Most inherited settings are overwritten: <br />- Short desc<br />- Product desc<br />- Product price<br />- Product Images<br />- Product Dimension and Weight (Units in Box)<br />- Customfields<br /></p>');

INSERT IGNORE INTO `#__virtuemart_product_categories` (`virtuemart_product_id`, `virtuemart_category_id`, `ordering`) VALUES
	(200, 2, 0),
	(201, 2, 1),
	(202, 2, 2),
	(203, 2, 3),
	(204, 2, 4),
	(205, 2, 5),
	(206, 2, 6),
	(207, 2, 7);

INSERT IGNORE INTO `#__virtuemart_product_customfields` (`virtuemart_customfield_id`, `virtuemart_product_id`, `virtuemart_custom_id`, `disabler`, `override`, `customfield_value`, `customfield_price`, `ordering`, `customfield_params`) VALUES
	(200, 204, 100, 0, 0, 'Customfield string 1: Child content', NULL, 0, ''),
	(201, 204, 100, 0, 0, 'Customfield string 2: Child content', NULL, 1, ''),
	(202, 204, 16, 0, 0, '<p>Advanced PATTERN content <br />&gt;&gt; This customfields are assigned in parent product.</p>', NULL, 2, ''),
	(203, 207, 16, 0, 202, '<p>Advanced PATTERN content <br />&gt;&gt; This customfields are assigned in parent product.<br /><br />Enable overrides in plugin customfields and set your new content.<br />You can add customfields additional to inherited.</p>', NULL, 2, ''),
	(204, 207, 100, 0, 201, 'Override for string 2', NULL, 1, ''),
	(205, 207, 100, 200, 0, 'Disables string 1 of parent', NULL, 0, ''),
	(206, 207, 15, 0, 0, '21', NULL, 3, ''),
	(276, 207, 13, 0, 0, 'A Variant', 0.00001, 10, ''),
	(277, 207, 13, 0, 0, 'B Variant', 5.00, 11, ''),
	(278, 207, 13, 0, 0, 'C Variant', 10.00, 12, ''),
	(279, 207, 13, 0, 0, 'D Variant', -5.00, 13, '');

INSERT IGNORE INTO `#__virtuemart_product_medias` (`virtuemart_product_id`, `virtuemart_media_id`, `ordering`) VALUES
	(203, 21, 1),
	(207, 22, 1);

INSERT IGNORE INTO `#__virtuemart_product_prices` (`virtuemart_product_id`, `virtuemart_shoppergroup_id`, `product_price`, `product_currency`, `price_quantity_start`, `price_quantity_end`) VALUES
  (200, 0, 33.000000, 47, NULL, NULL),
	(204, 0, 25.000000, 47, NULL, NULL),
	(207, 0, 80.000000, 47, 6, 10),
	(207, 0, 50.000000, 47, 11, 0);

INSERT IGNORE INTO `#__virtuemart_ratings` (`virtuemart_rating_id`, `virtuemart_product_id`, `rates`, `ratingcount`, `rating`, `published`) VALUES
  (200, 200, 5, 1, 5.0, 1),
  (201, 201, 5, 1, 4.9, 1),
  (202, 202, 4, 1, 3.0, 1),
  (203, 203, 5, 1, 4.9, 1),
  (204, 204, 5, 1, 4.4, 1),
  (205, 205, 4, 1, 4.0, 1),
  (206, 206, 5, 1, 4.9, 1),
  (207, 207, 5, 1, 4.9, 1);

#Product variants
INSERT IGNORE INTO `#__virtuemart_products` (`virtuemart_product_id`, `product_parent_id`, `product_sku`, `product_special`, `product_in_stock`, `product_weight`, `published`, `pordering`) VALUES
	(170, 9, '', 0, 10, NULL, 1, 0),
	(171, 170, 'CSIV', 0, 11, NULL, 1, 0),
	(172, 170, 'GCCV', 0, 12, NULL, 1, 0),
	(173, 172, 'GCV-A', 0, 13, NULL, 1, 1),
	(174, 172, 'GCV-B', 0, 14, NULL, 1, 2),
	(175, 172, 'GCV-C', 0, 15, NULL, 1, 3),
	(176, 170, 'CSIV', 0, 11, NULL, 1, 0);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`, `product_s_desc`, `product_desc`) VALUES
  (170, 'PATTERN Product variants', 'pattern-product-variants', '', ''),
	(171, 'Customfield string', 'customfield-string', '', ''),
	(172, 'Generic child variant', 'generic-child-cart-variant', 'Default product generic child variant and cart variant.', '<p>Showcase to present the combination of product price, child variant price, and cart variant price:<br /><br />Child variant -&gt; full overrideable genuine product, stock control per variant</p>\r\n<p>Cart variant -&gt; easy to use variant, add price, no stock control</p>'),
	(173, 'generic child variant A', 'generic-child-variant-a', '', ''),
	(174, 'generic child variant B', 'generic-child-variant-b', 'Default product generic child variant and cart variant.', '<p>This product is a showcase to present the combination of product price, child variant price, and cart variant price.<br /><br />Cart variant -&gt; easy to use, add price (+0.50€) no variant stock control</p>\r\n<p><br />Child variant -&gt; full overrideable genuine product as variant</p>'),
	(175, 'generic child variant C', 'generic-child-variant-c', 'Default product generic child variant and cart variant.', '<p>This product is a showcase to present the combination of product price, child variant price, and cart variant price.<br /><br />Cart variant -&gt; easy to use, add price (+0.50€) no variant stock control</p>\r\n<p><br />Child variant -&gt; full overrideable genuine product as variant</p>'),
	(176, 'Customfield image variant', 'customfield-image-variant', 'Product image variant', '<p>This product is a showcase for customfield image variant as shopper selection</p>');

INSERT IGNORE INTO `#__virtuemart_product_categories` (`virtuemart_product_id`, `virtuemart_category_id`, `ordering`) VALUES
	(170, 11, 0),
	(171, 11, 1),
	(172, 11, 3),
	(176, 11, 10);

INSERT IGNORE INTO `#__virtuemart_product_customfields` (`virtuemart_product_id`, `virtuemart_custom_id`, `disabler`, `override`, `customfield_value`, `customfield_price`, `ordering`, `customfield_params`) VALUES
  (171, 100, 0, 0, 'My customfield string content', NULL, 0, ''),
  (171, 12, 0, 0, 'My customfield cart string content', NULL, 0, ''),
  (171, 69, 0, 0, '6', NULL, 3, 'width="0"|height="0"|'),
	(171, 69, 0, 0, '7', 3.000000, 5, 'width="0"|height="0"|'),
	(171, 69, 0, 0, '8', 2.000000, 4, 'width="0"|height="0"|'),
	(171, 14, 0, 0, 'Cotton', NULL, 1, ''),
	(171, 14, 0, 0, 'Wool', 4.000000, 2, ''),
	(171, 14, 0, 0, 'Flax', 7.000000, 3, ''),
	(171, 13, 0, 0, 'Advanced', NULL, 0, ''),
	(171, 13, 0, 0, 'Expert', 4.900000, 3, ''),
	(172, 20, 0, 0, 'product_sku', NULL, 0, 'withParent="0"|parentOrderable="0"|wPrice=0|'),
	(176, 22, 0, 0, '21', NULL, 1, ''),
	(176, 22, 0, 0, '22', 5.00, 2, '');

INSERT IGNORE INTO `#__virtuemart_ratings` (`virtuemart_rating_id`, `virtuemart_product_id`, `rates`, `ratingcount`, `rating`, `published`) VALUES
  (170, 170, 5, 1, 4.6, 1),
  (171, 171, 4, 1, 4.0, 1),
  (172, 172, 5, 1, 4.9, 1);

#Examples Clothing
INSERT IGNORE INTO `#__virtuemart_products` (`virtuemart_product_id`, `product_parent_id`, `product_sku`, `product_special`, `product_weight_uom`, `product_weight`, `product_lwh_uom`, `product_length`, `product_width`, `product_height`, `product_in_stock`, `product_sales`, `published`, `pordering`) VALUES
  (153, 9, '', 0, 'KG', NULL, 'M', NULL, NULL, NULL, 0, 0, 1, 0),
	(154, 9, 'PR-DST', 1, 'G', 200.0000, 'CM', 40.0000, 40.0000, 40.0000, 50, 0, 1, 1),
	(155, 9, 'MA-SS', 1, 'G', 200.0000, 'CM', 40.0000, 40.0000, 40.0000, 50, 0, 1, 2),
  (156, 153, 'MA-ZP', 0, 'G', 200.0000, 'CM', 40.0000, 40.0000, 3.0000, 20, 0, 1, 3),
	(157, 153, 'MA-H2J', 0, 'G', 300.0000, 'CM', 45.0000, 45.0000, 10.0000, 5, 0, 1, 4),
	(158, 153, 'DESKR', 0, 'G', 200.0000, 'CM', 35.0000, 35.0000, 5.0000, 100, 0, 1, 5),
  (159, 9, '', 0, 'KG', NULL, 'M', NULL, NULL, NULL, 0, 0, 1, 0),
	(160, 159, 'XSF', 1, 'G', 150.0000, 'CM', 30.0000, 30.0000, 30.0000, 30, 0, 1, 1),
	(161, 159, 'PRCB', 1, 'G', 100.0000, 'CM', 20.0000, 20.0000, 20.0000, 20, 0, 1, 2),
	(162, 159, 'TPCM', 1, 'G', 150.0000, 'CM', 35.0000, 30.0000, 15.0000, 30, 0, 1, 3),
	(195, 154, 'DS-Small', 0, 'G', NULL, 'CM', NULL, NULL, NULL, 20, 0, 1, 1),
	(196, 154, 'DS-Large', 0, 'G', NULL, 'CM', NULL, NULL, NULL, 20, 0, 1, 2),
	(197, 155, 'TS-Small', 0, 'G', NULL, 'CM', NULL, NULL, NULL, 30, 0, 1, 1),
	(198, 155, 'TS-Medium', 0, 'G', NULL, 'CM', NULL, NULL, NULL, 30, 0, 1, 2),
	(199, 155, 'TS-Large', 0, 'G', NULL, 'CM', NULL, NULL, NULL, 20, 0, 1, 3);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`, `product_s_desc`, `product_desc`) VALUES
  (153, 'PATTERN Wear', 'pattern-wear', '', ''),
	(154, 'Dress Shirt with tie', 'dress-shirt-with-tie', 'Fine feathers make fine birds - time to get serious.', '<p>Refresh yourself with genuine VM dress.</p>'),
	(155, 'T-Shirt classic blue', 't-shirt-classic-blue', 'Freetime & leisure 360° comrade.', '<p>The first print VM shirt available - the one - the virtue. Profit by introductory offer </p>'),
	(156, 'Zipper pullover', 'zipper-pullover', 'Your winter season friend. ', ''),
	(157, 'H20 Jacket', 'h20-jacket', 'Hard rain? Cold? - keep yourself dry & warm.', ''),
	(158, 'Skirt &#34;Knock-Rock&#34;', 'skirt-knock-rock', 'Redesigned traditional pattern. Decently highlighted VM emblem.', ''),
	(159, 'PATTERN Headpiece', 'pattern-headpiece', '', ''),
	(160, 'Safety Helmet', 'safety-helmet', 'Masterclass protection, your everyday safety.', ''),
	(161, 'Cap &#34;Baseball&#34;', 'cap-baseball', 'Need something genuine for your freetime?', ''),
	(162, 'Cowboy Hat', 'cowboy-hat', 'Classic pattern, durable stiff brim resists sun & moisture.', ''),
  (195, 'Dress Shirt with tie - small', 'dress-shirt-with-tie-s', '', ''),
	(196, 'Dress Shirt with tie - large', 'dress-shirt-with-tie-l', '', ''),
	(197, 'T-Shirt classic blue - small', 't-shirt-classic-s', '', ''),
	(198, 'T-Shirt classic blue - medium', 't-shirt-classic-m', '', ''),
	(199, 'T-Shirt classic blue - large', 't-shirt-classic-l', '', '');

INSERT IGNORE INTO `#__virtuemart_product_manufacturers` (`virtuemart_product_id`, `virtuemart_manufacturer_id`) VALUES
  (153, 2),
  (154, 3),
  (155, 3),
  (159, 2);

INSERT IGNORE INTO `#__virtuemart_product_categories` (`virtuemart_product_id`, `virtuemart_category_id`, `ordering`) VALUES
	(156, 9, 0),
	(161, 4, 0),
	(156, 7, 0),
	(160, 4, 0),
	(158, 9, 0),
	(155, 8, 0),
	(158, 7, 0),
	(155, 9, 0),
	(155, 7, 0),
	(154, 7, 0),
	(157, 7, 0),
	(157, 9, 0),
	(157, 8, 0),
	(154, 8, 0),
	(156, 8, 0),
	(162, 4, 0);

INSERT IGNORE INTO `#__virtuemart_product_customfields` (`virtuemart_customfield_id`, `virtuemart_product_id`, `virtuemart_custom_id`, `disabler`, `override`, `customfield_value`, `customfield_price`, `ordering`, `customfield_params`) VALUES
	(400, 153, 11, 0, 0, 'Stiched genuine VM logo, color: black', NULL, 0, ''),
	(401, 153, 13, 0, 0, 'Basic', NULL, 1, ''),
	(402, 153, 13, 0, 0, 'Handmade', 100.000000, 2, ''),
	(403, 153, 14, 0, 0, 'Cotton', NULL, 3, ''),
	(404, 153, 14, 0, 0, 'Wool', NULL, 4, ''),
	(405, 153, 14, 0, 0, 'Polyester', NULL, 5, ''),
	(406, 153, 16, 0, 0, '<p>Wear pattern data field using customfield editor.</p>', NULL, 6, ''),
	(407, 154, 11, 0, 0, 'Stiched genuine VM logo, color: black', NULL, 0, ''),
	(408, 154, 100, 0, 0, 'Machine-washable', NULL, 3, ''),
  (412, 154, 20, 0, 0, 'product_sku', NULL, 1, 'withParent="0"|parentOrderable="0"|wPrice=0|'),
	(413, 154, 13, 0, 0, 'Silver Buttons', 0.00001, 2, ''),
	(414, 154, 13, 0, 0, 'Gold Buttons', 10, 2, ''),
	(415, 155, 20, 0, 0, 'product_sku', NULL, 0, 'withParent="0"|parentOrderable="0"|wPrice=0|'),
	(416, 155, 11, 0, 0, 'Stiched genuine VM logo, color: black', NULL, 1, ''),
	(417, 155, 12, 0, 0, 'Flax', NULL, 2, ''),
	(418, 155, 100, 0, 0, 'Machine-washable', NULL, 3, ''),
	(425, 156, 14, 0, 404, 'Wool', NULL, 5, ''),
	(426, 156, 14, 0, 0, 'Flax', 12.99, 6, ''),
	(427, 156, 14, 0, 0, 'Nylon', 7.99, 3, ''),
	(428, 156, 14, 0, 405, 'Polyester', 3.99, 4, ''),
	(429, 156, 16, 0, 406, '<p>Wear pattern data field using customfield editor override for Zipper.</p>', NULL, 7, ''),
	(430, 157, 13, 0, 402, 'Handmade', NULL, 2, ''),
	(431, 157, 14, 0, 0, 'Nylon', NULL, 3, ''),
	(432, 157, 14, 0, 0, 'Polyester', NULL, 3, ''),
	(433, 157, 16, 0, 404, '<p>Wear pattern data field using customfield editor, override for H2O jacket</p>', NULL, 4, ''),
	(434, 158, 13, 0, 402, 'Handmade', NULL, 2, ''),
	(435, 158, 14, 0, 0, 'Cotton', NULL, 3, ''),
	(436, 158, 14, 0, 0, 'Wool', 35.83333, 3, ''),
	(437, 158, 14, 0, 0, 'Flax', 15.83333, 3, ''),
  (438, 158, 16, 0, 404, '<p>Wear pattern data field using customfield editor, Skirt "Knock-Rock."</p>', NULL, 4, ''),
	(439, 159, 100, 0, 0, '', NULL, 1, ''),
	(440, 159, 16, 0, 0, '<p>Example text for customfield editor position default.</p>', NULL, 2, ''),
	(441, 159, 11, 0, 0, '', NULL, 0, ''),
	(442, 160, 11, 0, 441, 'Color: yellow, Logo: monochrome', NULL, 0, ''),
	(443, 160, 100, 0, 439, 'Stiched VM logo', NULL, 1, ''),
  (444, 160, 13, 0, 0, 'Retail', NULL, 1, ''),
	(445, 160, 13, 0, 0, 'Bulk', -2.00, 2, ''),
	(446, 161, 11, 0, 441, 'Color: red, Logo: monochrome', NULL, 0, ''),
	(447, 161, 13, 0, 0, 'Size: S', NULL, 1, ''),
	(448, 161, 13, 0, 0, 'Size: M', NULL, 2, ''),
	(449, 161, 13, 0, 0, 'Size: L', NULL, 3, ''),
  (450, 161, 13, 0, 0, 'Size: Uni', 2.00, 4, ''),
	(451, 161, 100, 0, 439, 'Stiched VM logo', NULL, 1, ''),
	(452, 162, 11, 0, 441, 'Color: brown, Logo: monochrome', NULL, 0, ''),
	(453, 162, 100, 0, 439, 'Stiched VM logo', NULL, 1, ''),
  (454, 162, 14, 0, 0, 'Standard', NULL, 2, ''),
  (455, 162, 14, 0, 0, 'Waxed', 10.00, 3, ''),
  (456, 162, 13, 0, 0, 'Size: M', NULL, 4, ''),
  (457, 162, 13, 0, 0, 'Size: L', NULL, 5, ''),
  (458, 162, 13, 0, 0, 'Size: XL', NULL, 6, ''),
  (473, 195, 14, 0, 0, 'Cotton', NULL, 2, ''),
	(474, 195, 14, 0, 0, 'Wool', NULL, 2, ''),
	(475, 195, 14, 0, 0, 'Flax', NULL, 2, ''),
	(477, 195, 13, 0, 413, 'Silver Buttons', 0.00001, 2, ''),
	(478, 195, 13, 0, 414, 'Gold Buttons', 10.00, 2, ''),
	(479, 196, 14, 0, 0, 'Cotton', NULL, 2, ''),
	(480, 196, 14, 0, 0, 'Wool', NULL, 2, ''),
	(481, 196, 14, 0, 0, 'Flax', NULL, 2, ''),
	(483, 196, 13, 0, 413, 'Silver Buttons', 0.00001, 2, ''),
	(484, 196, 13, 0, 414, 'Gold Buttons', 14.00, 2, ''),
 	(459, 197, 23, 0, 0, '5D', 0.00001, 2, ''),
 	(460, 197, 23, 0, 0, '10D', 5.00, 2, ''),
 	(461, 197, 13, 0, 0, 'Industry made', 0.00001, 5, ''),
 	(462, 197, 13, 0, 0, 'Hand made', 20.00, 6, ''),
 	(463, 198, 23, 0, 0, '20D', NULL, 2, ''),
 	(464, 198, 23, 0, 0, '30D', 10.00, 3, ''),
 	(465, 198, 23, 0, 0, '40D', 15.00, 4, ''),
 	(466, 198, 13, 0, 0, 'Industry made', 0.00001, 5, ''),
 	(467, 198, 13, 0, 0, 'Hand made', 20.00, 6, ''),
  (468, 199, 23, 0, 0, '30D', NULL, 2, ''),
 	(469, 199, 23, 0, 0, '40D', 20.00, 3, ''),
 	(470, 199, 23, 0, 0, '50D', 30.00, 4, ''),
 	(471, 199, 13, 0, 0, 'Industry made', 0.00001, 5, ''),
 	(472, 199, 13, 0, 0, 'Hand made', 25.00, 6, '');

INSERT IGNORE INTO `#__virtuemart_product_prices` (`virtuemart_product_id`, `product_price`, `product_currency`) VALUES
	(161, 15.833330, 47),
	(160, 40.833330, 47),
	(158, 140.833330, 47),
	(157, 207.500000, 47),
	(156, 49.166670, 47),
	(154, 40.833330, 47);

INSERT IGNORE INTO `#__virtuemart_product_prices` (`virtuemart_product_id`, `virtuemart_shoppergroup_id`, `product_price`, `override`, `product_override_price`, `product_currency`, `price_quantity_start`, `price_quantity_end`) VALUES
	(155, 0, 24.166670, 0, 19.00000, 47, 0, 0);

INSERT IGNORE INTO `#__virtuemart_product_medias` (`virtuemart_product_id`, `virtuemart_media_id`, `ordering`) VALUES
	(154, 24, 1),
	(155, 29, 1),
	(156, 25, 1),
	(157, 26, 1),
	(158, 13, 1),
	(160, 22, 1),
	(161, 21, 1),
	(162, 23, 1),
	(164, 20, 2),
	(164, 21, 3),
	(164, 22, 4),
	(164, 23, 5),
	(164, 24, 6),
	(164, 25, 7),
	(164, 26, 8),
	(164, 27, 9),
	(164, 28, 10),
	(164, 29, 11);

INSERT IGNORE INTO `#__virtuemart_ratings` (`virtuemart_rating_id`, `virtuemart_product_id`, `rates`, `ratingcount`, `rating`, `published`) VALUES
  (150, 154, 5, 1, 4.6, 1),
  (151, 155, 5, 1, 4.8, 1),
  (152, 156, 4, 1, 3.8, 1),
  (153, 157, 5, 1, 5.0, 1),
  (154, 158, 5, 1, 4.4, 1),
  (155, 160, 4, 1, 4.2, 1),
  (156, 161, 5, 1, 5.0, 1),
  (157, 162, 5, 1, 4.7, 1);

#Multivariant
INSERT IGNORE INTO `#__virtuemart_products` (`virtuemart_product_id`, `product_parent_id`, `product_sku`, `product_special`, `product_in_stock`, `published`, `product_lwh_uom`,`product_width`) VALUES
  (300, 170, 'MV Parent', 1, 10, 1, 'CM', 56.0);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`, `product_s_desc`, `product_desc`) VALUES
  (300, 'Multi Variant', 'multi-variant', 'Depended Multivariants', 'The new Multi variant feature lets manage you 100s of product variants in the parent. The product content is replaced by the selected product');

INSERT IGNORE INTO `#__virtuemart_product_categories` (`virtuemart_product_id`, `virtuemart_category_id`, `ordering`) VALUES
  (300,11,16);

INSERT IGNORE INTO `#__virtuemart_product_prices` (`virtuemart_product_id`, `product_price`, `product_currency`) VALUES
  (300, 10, 47),
  (301, 8, 47),
  (302, 8.2, 47),
  (303, 8.5, 47),
  (304, 8.7, 47),
  (305, 9.9, 47),
  (306, 10, 47),
  (307, 10.3, 47),
  (308, 10.5, 47),
  (309, 10.8, 47),
  (310, 12, 47),
  (311, 12.4, 47),
  (312, 12.7, 47),
  (313, 12.9, 47),
  (314, 13.5, 47),
  (315, 14.5, 47);

INSERT IGNORE INTO `#__virtuemart_products` (`virtuemart_product_id`, `product_parent_id`, `product_sku`, `product_in_stock`, `published`, `product_width`, `product_length`, `pordering`) VALUES
	(301, 300, 'MV 2', 8, 1, 46.0, 68.5, 0),
	(302, 300, 'MV 3', 5, 1, 46.0, 68.5, 1),
	(303, 300, 'MV 4', 10, 1, 46.0, 71.0, 2),
	(304, 300, 'MV 5', 20, 1, 46.0, 71.0, 3),
	(305, 300, 'MV 6', 30, 1, 51.0, 68.5, 10),
	(306, 300, 'MV 7', 35, 1, 51.0, 71.0, 11),
	(307, 300, 'MV 8', 25, 1, 51.0, 71.0, 12),
	(308, 300, 'MV 9', 40, 1, 51.0, 73.5, 13),
	(309, 300, 'MV 10', 30, 1, 51.0, 73.5, 14),
	(310, 300, 'MV 11', 20, 1, 56.0, 73.5, 20),
	(311, 300, 'MV 12', 15, 1, 56.0, 73.5, 21),
	(312, 300, 'MV 13', 27, 1, 56.0, 76, 22),
	(313, 300, 'MV 14', 24, 1, 56.0, 76, 23),
  (314, 300, 'MV 15', 33, 1, 61.0, 76.0, 24),
	(315, 300, 'MV 16', 31, 1, 61.0, 76.0, 25);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`) VALUES
  (301, 'Multi Variant Child', 'multi-variant-2'),
  (302, 'Multi Variant Child', 'multi-variant-3'),
  (303, 'Multi Variant Child', 'multi-variant-4'),
  (304, 'Multi Variant Child', 'multi-variant-5'),
  (305, 'Multi Variant Child', 'multi-variant-6'),
  (306, 'Multi Variant Child', 'multi-variant-7'),
  (307, 'Multi Variant Child', 'multi-variant-8'),
  (308, 'Multi Variant Child', 'multi-variant-9'),
  (309, 'Multi Variant Child', 'multi-variant-10'),
  (310, 'Multi Variant Child', 'multi-variant-11'),
  (311, 'Multi Variant Child', 'multi-variant-12'),
  (312, 'Multi Variant Child', 'multi-variant-13'),
  (313, 'Multi Variant Child', 'multi-variant-14'),
  (314, 'Multi Variant Child', 'multi-variant-15'),
  (315, 'Multi Variant Child', 'multi-variant-16');

INSERT IGNORE INTO `#__virtuemart_product_customfields` (`virtuemart_customfield_id`, `virtuemart_product_id`, `virtuemart_custom_id`, `disabler`, `override`, `customfield_value`, `customfield_price`, `ordering`, `customfield_params`) VALUES
	(300, 300, 21, 0, 0, NULL, NULL, 0, 'usecanonical=0|showlabels=0|sCustomId=100|selectoptions=[{"voption":"product_width","clabel":"1","values":"46.0000\\r\\n51.0000\\r\\n56.0000\\r\\n61.0000"},{"voption":"product_length","clabel":"1","values":"68.5000\\r\\n71.0000\\r\\n73.5000\\r\\n76.0000"},{"voption":"clabels","clabel":"Weave","values":"Advanced\\r\\nPremium"}]|clabels=0|options={"300":["","","0"],"301":["46.0000","68.5000","Advanced"],"302":["46.0000","68.5000","Premium"],"303":["46.0000","71.0000","Advanced"],"304":["46.0000","71.0000","Premium"],"305":["51.0000","68.5000","Advanced"],"306":["51.0000","71.0000","Advanced"],"307":["51.0000","71.0000","Premium"],"308":["51.0000","73.5000","Advanced"],"309":["51.0000","73.5000","Premium"],"310":["56.0000","73.5000","Advanced"],"311":["56.0000","73.5000","Premium"],"312":["56.0000","76.0000","Advanced"],"313":["56.0000","76.0000","Premium"],"314":["61.0000","76.0000","Advanced"],"315":["61.0000","76.0000","Premium"]}|'),
	(301, 301, 100, 0, 0, 'Advanced', NULL, 0, ''),
	(302, 302, 100, 0, 0, 'Premium', NULL, 0, ''),
	(303, 303, 100, 0, 0, 'Advanced', NULL, 0, ''),
	(304, 304, 100, 0, 0, 'Premium', NULL, 0, ''),
	(305, 305, 100, 0, 0, 'Advanced', NULL, 0, ''),
	(306, 306, 100, 0, 0, 'Advanced', NULL, 0, ''),
	(307, 307, 100, 0, 0, 'Premium', NULL, 0, ''),
	(308, 308, 100, 0, 0, 'Advanced', NULL, 0, ''),
	(309, 309, 100, 0, 0, 'Premium', NULL, 0, ''),
	(310, 310, 100, 0, 0, 'Advanced', NULL, 0, ''),
	(311, 311, 100, 0, 0, 'Premium', NULL, 0, ''),
	(312, 312, 100, 0, 0, 'Advanced', NULL, 0, ''),
	(313, 313, 100, 0, 0, 'Premium', NULL, 0, ''),
	(314, 314, 100, 0, 0, 'Advanced', NULL, 0, ''),
	(315, 315, 100, 0, 0, 'Premium', NULL, 0, '');

INSERT IGNORE INTO `#__virtuemart_product_medias` (`virtuemart_product_id`, `virtuemart_media_id`, `ordering`) VALUES
	(300, 11, 1),
	(302, 29, 1),
	(304, 29, 1),
	(307, 29, 1),
	(309, 29, 1),
	(311, 29, 1),
	(313, 29, 1),
	(315, 29, 1);

INSERT IGNORE INTO `#__virtuemart_ratings` (`virtuemart_rating_id`, `virtuemart_product_id`, `rates`, `ratingcount`, `rating`, `published`) VALUES
	(300, 300, 5, 1, 5.0, 1),
  (301, 301, 4, 1, 4.0, 1),
  (302, 302, 5, 1, 5.0, 1),
  (303, 303, 5, 1, 5.0, 1),
  (304, 304, 5, 1, 5.0, 1),
  (305, 305, 4, 1, 4.0, 1),
  (306, 306, 5, 1, 5.0, 1),
  (307, 307, 5, 1, 5.0, 1),
  (308, 308, 5, 1, 5.0, 1),
  (309, 309, 5, 1, 5.0, 1),
  (310, 310, 4, 1, 4.0, 1),
  (311, 311, 5, 1, 5.0, 1),
  (312, 312, 5, 1, 5.0, 1),
  (313, 313, 4, 1, 4.0, 1),
  (314, 314, 5, 1, 5.0, 1),
  (315, 315, 5, 1, 5.0, 1);

#Pagination
INSERT IGNORE INTO `#__virtuemart_products` ( `virtuemart_product_id`, `product_parent_id`, `product_sku`, `product_weight`, `product_length`, `product_width`, `product_height`, `product_in_stock`, `product_sales`, `published`) VALUES
  (1000, 9, 'CEG', 1.0, 10, 10, 10, 10, 170, 1),
  (1100, 9, 'GHD', 2.0, 20, 20, 10, 20, 180, 1),
  (1200, 9, 'FAC', 3.0, 30, 20, 30, 30, 190, 1);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`) VALUES
  (1000, 'CEG #0 03', 'ceg-0-03'),
  (1100, 'GHD #0 08', 'ghd-0-08'),
  (1200, 'FAC #0 20', 'fac-0-20');

INSERT IGNORE INTO `#__virtuemart_products` (`virtuemart_product_id`,  `product_parent_id`, `product_weight`, `product_in_stock`, `product_sales`, `product_special`, `published`) VALUES
	(1001, 1000, 1.1, 20, 50, 0, 1),
	(1002, 1000, 1.2, 30, 40, 0, 1),
	(1003, 1000, 1.2, 25, 70, 0, 1),
	(1004, 1000, 1.1, 20, 50, 0, 1),
	(1005, 1000, 1.3, 23, 51, 0, 1),
	(1006, 1000, 1.3, 20, 52, 0, 1),
	(1007, 1000, 1.1, 20, 53, 0, 1),
	(1008, 1000, 1.4, 25, 54, 0, 1),
	(1009, 1000, 1.5, 20, 55, 0, 1),
	(1010, 1000, 1.8, 30, 56, 0, 1);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`) VALUES
  (1001, 'CEG #1 06', 'ceg-1-06'),
  (1002, 'CEG #2 15', 'ceg-2-15'),
  (1003, 'CEG #3 30', 'ceg-3-30'),
  (1004, 'CEG #4 17', 'ceg-4-17'),
  (1005, 'CEG #5 16', 'ceg-5-16'),
  (1006, 'CEG #6 22', 'ceg-6-22'),
  (1007, 'CEG #7 23', 'ceg-7-23'),
  (1008, 'CEG #8 12', 'ceg-8-12'),
  (1009, 'CEG #9 18', 'ceg-9-18'),
  (1010, 'CEG #a 33', 'ceg-a-33');

INSERT IGNORE INTO `#__virtuemart_products` (`virtuemart_product_id`,  `product_parent_id`, `product_weight`, `product_in_stock`, `product_sales`, `product_special`, `published`) VALUES
  (1101, 1100, 2.1, 20, 20, 0, 1),
  (1102, 1100, 2.2, 30, 30, 0, 1),
  (1103, 1100, 2.2, 25, 40, 0, 1),
  (1104, 1100, 2.1, 20, 50, 0, 1),
  (1105, 1100, 2.3, 23, 51, 0, 1),
  (1106, 1100, 2.5, 20, 52, 0, 1),
  (1107, 1100, 2.6, 20, 53, 0, 1),
  (1108, 1100, 2.7, 25, 54, 0, 1),
  (1109, 1100, 2.8, 20, 55, 0, 1),
  (1110, 1100, 2.9, 30, 56, 0, 1);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`) VALUES
  (1101, 'GHD #1 02', 'ghd-1-02'),
  (1102, 'GHD #2 07', 'ghd-2-07'),
  (1103, 'GHD #3 05', 'ghd-3-05'),
  (1104, 'GHD #4 04', 'ghd-4-04'),
  (1105, 'GHD #5 01', 'ghd-5-01'),
  (1106, 'GHD #6 32', 'ghd-6-32'),
  (1107, 'GHD #7 25', 'ghd-7-25'),
  (1108, 'GHD #8 24', 'ghd-8-24'),
  (1109, 'GHD #9 27', 'ghd-9-27'),
  (1110, 'GHD #a 28', 'ghd-a-28');

INSERT IGNORE INTO `#__virtuemart_products` (`virtuemart_product_id`,  `product_parent_id`, `product_weight`, `product_length`, `product_sales`, `product_special`, `published`) VALUES
	(1201, 1200, NULL, 20, 20, 0, 1),
	(1202, 1200, NULL, 30, 30, 0, 1),
	(1203, 1200, NULL, 25, 40, 0, 1),
	(1204, 1200, NULL, 20, 50, 0, 1),
	(1205, 1200, NULL, 23, 51, 0, 1),
	(1206, 1200, NULL, 20, 52, 0, 1),
	(1207, 1200, NULL, 20, 53, 0, 1),
	(1208, 1200, 3.9, 25, 54, 0, 1),
	(1209, 1200, 3.8, 20, 55, 0, 1),
	(1210, 1200, 3.7, 30, 56, 0, 1);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`) VALUES
  (1201, 'FAC #1 19', 'fac-1-19'),
  (1202, 'FAC #2 14', 'fac-2-14'),
  (1203, 'FAC #3 13', 'fac-3-13'),
  (1204, 'FAC #4 11', 'fac-4-11'),
  (1205, 'FAC #5 26', 'fac-5-26'),
  (1206, 'FAC #6 09', 'fac-6-09'),
  (1207, 'FAC #7 31', 'fac-7-31'),
  (1208, 'FAC #8 10', 'fac-8-10'),
  (1209, 'FAC #9 29', 'fac-9-29'),
  (1210, 'FAC #a 12', 'fac-a-12');

INSERT IGNORE INTO `#__virtuemart_product_manufacturers` (`virtuemart_product_id`, `virtuemart_manufacturer_id`) VALUES
  (1000, 1),
  (1100, 2),
  (1200, 3);

INSERT IGNORE INTO `#__virtuemart_product_medias` (`virtuemart_product_id`, `virtuemart_media_id`) VALUES
  (1000, 20),
  (1100, 20),
  (1200, 20);

INSERT IGNORE INTO `#__virtuemart_product_prices` (`virtuemart_product_id`, `product_price`, `product_currency`) VALUES
  (1000, 10, 47),
  (1100, 50, 47),
  (1200, 80, 47);

INSERT IGNORE INTO `#__virtuemart_product_categories` (`virtuemart_product_id`, `virtuemart_category_id`, `ordering`) VALUES
  (1000, 3, 3),
  (1001, 3, 6),
  (1002, 3, 15),
  (1003, 3, 30),
  (1004, 3, 17),
  (1005, 3, 16),
  (1006, 3, 22),
  (1007, 3, 23),
  (1008, 3, 12),
  (1009, 3, 18),
  (1010, 3, 33),
  (1100, 3, 8),
  (1101, 3, 2),
  (1102, 3, 7),
  (1103, 3, 5),
  (1104, 3, 4),
  (1105, 3, 1),
  (1106, 3, 32),
  (1107, 3, 25),
  (1108, 3, 24),
  (1109, 3, 27),
  (1110, 3, 28),
  (1200, 3, 20),
  (1201, 3, 19),
  (1202, 3, 14),
  (1203, 3, 13),
  (1204, 3, 11),
  (1205, 3, 26),
  (1206, 3, 9),
  (1207, 3, 31),
  (1208, 3, 10),
  (1209, 3, 29),
  (1210, 3, 12);

INSERT IGNORE INTO `#__virtuemart_ratings` (`virtuemart_rating_id`, `virtuemart_product_id`, `rates`, `ratingcount`, `rating`, `published`) VALUES
  (1000, 1000, 5, 1, 4.8, 1),
	(1001, 1001, 5, 1, 4.8, 1),
	(1002, 1002, 4, 1, 4.0, 1),
	(1003, 1003, 5, 1, 3.8, 1),
	(1004, 1004, 5, 1, 4.8, 1),
	(1005, 1005, 4, 1, 4.0, 1),
	(1006, 1006, 5, 1, 4.8, 1),
	(1007, 1007, 5, 1, 4.9, 1),
	(1008, 1008, 5, 1, 4.8, 1),
	(1009, 1009, 5, 1, 3.8, 1),
	(1100, 1100, 5, 1, 4.5, 1),
	(1101, 1101, 5, 1, 4.8, 1),
	(1102, 1102, 5, 1, 4.8, 1),
	(1103, 1103, 5, 1, 4.5, 1),
	(1104, 1104, 5, 1, 4.8, 1),
	(1105, 1105, 5, 1, 3.8, 1),
	(1106, 1106, 5, 1, 4.8, 1),
	(1107, 1107, 5, 1, 4.5, 1),
	(1108, 1108, 5, 1, 4.8, 1),
	(1109, 1109, 5, 1, 4.8, 1),
	(1110, 1110, 5, 1, 4.5, 1),
	(1200, 1200, 5, 1, 3.8, 1),
	(1201, 1201, 5, 1, 4.8, 1),
	(1202, 1202, 5, 1, 4.5, 1),
	(1203, 1203, 5, 1, 4.5, 1),
	(1204, 1204, 5, 1, 3.8, 1),
	(1205, 1205, 5, 1, 4.5, 1),
	(1206, 1206, 5, 1, 4.5, 1),
	(1207, 1207, 5, 1, 4.8, 1),
	(1208, 1208, 5, 1, 4.5, 1),
	(1209, 1209, 5, 1, 4.5, 1),
	(1210, 1210, 5, 1, 4.8, 1);

#Test products
INSERT IGNORE INTO `#__virtuemart_products` ( `virtuemart_product_id`, `product_parent_id`, `product_sku`, `product_weight`, `product_length`, `product_width`, `product_height`, `product_in_stock`, `product_sales`, `published`) VALUES
	(1999, 164, 'TP-PARENT', 1.0, 10, 10, 10, 10, 170, 1),
  (2000, 1999, 'TP-CF-S', 1.0, 10, 10, 10, 10, 170, 1),
  (2001, 1999, 'TP-CF-S-R', 2.0, 20, 20, 10, 20, 180, 1),
	(2003, 1999, 'TP-CF-M-R', 2.0, 20, 20, 10, 20, 180, 1);

INSERT IGNORE INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `slug`) VALUES
	(1999, 'Parent Test Product for Customfields', 'test-product-for-customfields'),
  (2000, 'Test Product for Customfield S', 'test-product-for-customfield-s'),
  (2001, 'Test Product for Customfield S with radio', 'test-product-for-customfield-s-radio'),
	(2003, 'Test Product for Customfield M with radio', 'test-product-for-customfield-m-radio');

INSERT IGNORE INTO `#__virtuemart_product_customfields` (`virtuemart_product_id`, `virtuemart_custom_id`, `disabler`, `override`, `customfield_value`, `customfield_price`, `ordering`, `customfield_params`) VALUES
 (1999, 20, 0, 0, 'product_sku', NULL, 0, 'withParent="0"|parentOrderable="0"|wPrice=0|');

INSERT IGNORE INTO `#__virtuemart_product_categories` (`virtuemart_product_id`, `virtuemart_category_id`, `ordering`) VALUES
	(1999, 12, 0);

INSERT IGNORE INTO `#__virtuemart_shoppergroups` (`virtuemart_shoppergroup_id`, `shopper_group_name`, `shopper_group_desc`, `default`, `shared`, `published`) VALUES
( 3,'Wholesale', 'Shoppers that can buy at wholesale.', 0,1,1),
( 4,'Gold Level', 'Gold Level Shoppers.', 0,1,1);