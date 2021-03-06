INSERT INTO `[|PREFIX|]attributes` (`id`, `name`, `display_name`, `type`, `type_data`) VALUES
(1000, 'Color', 'Color', 'Store_Attribute_Type_Configurable_PickList_Swatch', 'O:49:"Store_Attribute_Type_Configurable_PickList_Swatch":3:{s:8:" * _view";N;s:27:" * _defaultAttributeValueId";N;s:26:" * _usePreviousValueOnNull";b:0;}'),
(1001, 'Size', 'Size', 'Store_Attribute_Type_Configurable_PickList_Set', 'O:46:"Store_Attribute_Type_Configurable_PickList_Set":3:{s:8:" * _view";O:30:"Store_Attribute_View_Rectangle":0:{}s:27:" * _defaultAttributeValueId";i:70;s:26:" * _usePreviousValueOnNull";b:0;}');

INSERT INTO `[|PREFIX|]attribute_values` (`id`, `attribute_id`, `sort_order`, `value_data`, `label`) VALUES
(2000, 1000, 0, 'O:42:\"Store_Attribute_ValueData_Swatch_OneColour\":2:{s:11:\"\0*\0_colours\";a:1:{i:0;s:6:\"123c91\";}s:18:\"\0*\0_imageExtension\";s:0:\"\";}', 'Blue'),
(2001, 1000, 1, 'O:42:\"Store_Attribute_ValueData_Swatch_OneColour\":2:{s:11:\"\0*\0_colours\";a:1:{i:0;s:6:\"0f961e\";}s:18:\"\0*\0_imageExtension\";s:0:\"\";}', 'Green'),
(2002, 1000, 2, 'O:42:\"Store_Attribute_ValueData_Swatch_OneColour\":2:{s:11:\"\0*\0_colours\";a:1:{i:0;s:6:\"f0f005\";}s:18:\"\0*\0_imageExtension\";s:0:\"\";}', 'Yellow'),
(2010, 1001, 0, NULL, 'S'),
(2011, 1001, 1, NULL, 'M'),
(2012, 1001, 2, NULL, 'L');

INSERT INTO `[|PREFIX|]product_types` (`id`, `name`) VALUES
(3001, 'Subset of colors'),
(3002, 'All colors and subset of sizes');

INSERT INTO `[|PREFIX|]product_type_attributes` (`id`, `product_type_id`, `attribute_id`, `display_name`, `sort_order`, `required`) VALUES
(4000, 3001, 1000, 'Color', 0, 1),
(4001, 3002, 1000, 'Color', 0, 1),
(4002, 3002, 1001, 'Size',  0, 1);

INSERT INTO `[|PREFIX|]product_type_attribute_values` (`id`, `product_type_attribute_id`, `attribute_value_id`) VALUES
(5000, 4000, 2000),
(5001, 4000, 2001),
(5002, 4002, 2010),
(5003, 4002, 2011);
