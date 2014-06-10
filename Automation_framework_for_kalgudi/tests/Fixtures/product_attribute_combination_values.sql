INSERT INTO product_attribute_combination_values (product_attribute_combination_id, product_attribute_id, attribute_value_id)
SELECT id, 2000, 3000 FROM product_attribute_combinations WHERE product_id IN (1000, 1001) UNION
SELECT id, 2001, 3001 FROM product_attribute_combinations WHERE product_id IN (1000, 1001);
