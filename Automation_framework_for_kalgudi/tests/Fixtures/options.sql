SET foreign_key_checks = 0;
TRUNCATE TABLE [|PREFIX|]product_attribute_combinations;
TRUNCATE TABLE [|PREFIX|]product_attribute_combination_values;
TRUNCATE TABLE [|PREFIX|]product_attribute_rules;
TRUNCATE TABLE [|PREFIX|]product_attribute_rule_conditions;
TRUNCATE TABLE [|PREFIX|]product_attributes;
TRUNCATE TABLE [|PREFIX|]product_types;
TRUNCATE TABLE [|PREFIX|]product_type_attributes;
TRUNCATE TABLE [|PREFIX|]product_type_attribute_values;
TRUNCATE TABLE [|PREFIX|]attributes;
TRUNCATE TABLE [|PREFIX|]attribute_values;
TRUNCATE TABLE [|PREFIX|]category_filter_attributes;
TRUNCATE TABLE [|PREFIX|]order_product_attributes;
UPDATE [|PREFIX|]products SET product_type_id = 0;
SET foreign_key_checks = 1;

INSERT INTO `[|PREFIX|]attributes` (`id`, `name`, `display_name`, `type`, `type_data`) VALUES
(1, 'Venture Exploiting', 'Venture Exploiting', 'Store_Attribute_Type_Configurable_PickList_Set', 'O:46:"Store_Attribute_Type_Configurable_PickList_Set":1:{s:8:"\0*\0_view";O:27:"Store_Attribute_View_Select":0:{}}'),
(2, 'Colleague Those Unsupported Locking', 'Colleague Those', 'Store_Attribute_Type_Configurable_PickList_Swatch', 'O:49:"Store_Attribute_Type_Configurable_PickList_Swatch":1:{s:18:"\0*\0_imageDirectory";s:22:"attribute_value_images";}'),
(3, 'Rail Smoked Bird''s', 'Rail Smoked', 'Store_Attribute_Type_Configurable_PickList_Product', 'O:50:"Store_Attribute_Type_Configurable_PickList_Product":1:{s:8:"\0*\0_view";N;}'),
(4, 'Consensus''s Adjacent', 'Consensus''s Adjacent', 'Store_Attribute_Type_Configurable_Entry_Checkbox', 'O:48:"Store_Attribute_Type_Configurable_Entry_Checkbox":2:{s:18:"\0*\0_defaultChecked";b:1;s:9:"\0*\0_label";s:34:"Yes, vision''s soundtrack eccentric";}'),
(5, 'Blind Caveat Enhancing Plans', 'Blind Caveat', 'Store_Attribute_Type_Configurable_Entry_File', 'O:44:"Store_Attribute_Type_Configurable_Entry_File":6:{s:18:"\0*\0_fileTypeOption";s:8:"specific";s:21:"\0*\0_imageFilesAllowed";b:0;s:24:"\0*\0_documentFilesAllowed";b:0;s:21:"\0*\0_otherFilesAllowed";b:0;s:18:"\0*\0_otherFileTypes";a:0:{}s:15:"\0*\0_maxFileSize";i:0;}'),
(6, 'Response Shine Launches', 'Response Shine', 'Store_Attribute_Type_Configurable_Entry_Text', 'O:44:"Store_Attribute_Type_Configurable_Entry_Text":4:{s:16:"\0*\0_defaultValue";s:0:"";s:27:"\0*\0_validateCharacterLength";b:0;s:13:"\0*\0_minLength";i:0;s:13:"\0*\0_maxLength";i:0;}'),
(7, 'Ten''s Product Quoted', 'Ten''s Product', 'Store_Attribute_Type_Configurable_Entry_Text_MultiLine', 'O:54:"Store_Attribute_Type_Configurable_Entry_Text_MultiLine":6:{s:22:"\0*\0_validateLineLength";b:0;s:12:"\0*\0_maxLines";i:0;s:16:"\0*\0_defaultValue";s:0:"";s:27:"\0*\0_validateCharacterLength";b:0;s:13:"\0*\0_minLength";i:0;s:13:"\0*\0_maxLength";i:0;}'),
(8, 'Bug''s Principle''s Hardback''s Had', 'Bug''s Principle''s', 'Store_Attribute_Type_Configurable_Entry_NumbersOnlyText', 'O:55:"Store_Attribute_Type_Configurable_Entry_NumbersOnlyText":6:{s:16:"\0*\0_defaultValue";s:0:"";s:14:"\0*\0_limitInput";b:0;s:15:"\0*\0_lowestValue";i:0;s:16:"\0*\0_highestValue";i:0;s:15:"\0*\0_integerOnly";b:0;s:20:"\0*\0_limitInputOption";s:6:"lowest";}'),
(9, 'Donation''s Profile''s', 'Donation''s Profile''s', 'Store_Attribute_Type_Configurable_Entry_Date', 'O:44:"Store_Attribute_Type_Configurable_Entry_Date":6:{s:16:"\0*\0_defaultValue";i:1292217895;s:16:"\0*\0_earliestDate";i:0;s:14:"\0*\0_latestDate";i:0;s:13:"\0*\0_limitDate";b:0;s:19:"\0*\0_limitDateOption";s:8:"earliest";s:20:"\0*\0_limitDateOptions";a:3:{i:0;s:8:"earliest";i:1;s:6:"latest";i:2;s:5:"range";}}');

INSERT INTO `[|PREFIX|]attribute_values` (`id`, `attribute_id`, `sort_order`, `value_data`, `label`) VALUES
(1, 1, 0, '', 'Lorem'),
(2, 1, 1, '', 'Ipsum'),
(3, 1, 2, '', 'Dolor'),
(4, 1, 3, '', 'Sit'),
(5, 1, 4, '', 'Amet'),
(6, 2, 0, 'O:42:"Store_Attribute_ValueData_Swatch_OneColour":2:{s:11:"\0*\0_colours";a:1:{i:0;s:6:"ff0000";}s:9:"\0*\0_image";s:0:"";}', 'Red'),
(7, 2, 1, 'O:42:"Store_Attribute_ValueData_Swatch_TwoColour":2:{s:11:"\0*\0_colours";a:2:{i:0;s:6:"ff0000";i:1;s:6:"00ff00";}s:9:"\0*\0_image";s:0:"";}', 'Red, Green'),
(8, 2, 2, 'O:44:"Store_Attribute_ValueData_Swatch_ThreeColour":2:{s:11:"\0*\0_colours";a:3:{i:0;s:6:"ff0000";i:1;s:6:"00ff00";i:2;s:6:"0000ff";}s:9:"\0*\0_image";s:0:"";}', 'Red, Green, Blue'),
(9, 2, 3, 'O:40:"Store_Attribute_ValueData_Swatch_Texture":2:{s:11:"\0*\0_colours";a:0:{}s:9:"\0*\0_image";s:59:"attribute_value_images/f610fa1f0f867a8a81084377008fbbda.gif";}', 'Gibbon monkey dog tail!'),
(10, 3, 0, '', 'Apple iPhone Bluetooth Headset'),
(11, 3, 1, '', 'Speck PixelSkin Case for iPhone 3G'),
(12, 3, 2, '', 'Incase Sports Armband for iPod Nano'),
(13, 3, 3, '', 'Apple iPod Socks'),
(14, 4, 0, '', 'Yes'),
(15, 4, 1, '', 'No');

INSERT INTO `[|PREFIX|]product_types` (`id`, `name`) VALUES
(1, 'Balance Hypocritical Hasten Expertise Removal''s Pretends');

INSERT INTO `[|PREFIX|]product_type_attributes` (`id`, `product_type_id`, `attribute_id`, `display_name`, `sort_order`, `required`) VALUES
(1, 1, 1, 'Venture Exploiting', 0, 0),
(2, 1, 2, 'Colleague Those', 1, 0),
(3, 1, 3, 'Rail Smoked', 2, 0),
(4, 1, 4, 'Consensus''s Adjacent', 3, 0),
(5, 1, 5, 'Blind Caveat', 4, 0),
(6, 1, 6, 'Response Shine', 5, 0),
(7, 1, 7, 'Ten''s Product', 6, 0),
(8, 1, 8, 'Bug''s Principle''s', 7, 0),
(9, 1, 9, 'Donation''s Profile''s', 8, 0);

