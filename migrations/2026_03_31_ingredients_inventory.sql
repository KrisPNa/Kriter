-- Учет ингредиентов и списание по рецепту

CREATE TABLE IF NOT EXISTS `ingredients` (
  `ingredient_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `unit` VARCHAR(32) NOT NULL DEFAULT 'кг',
  `stock_qty` DECIMAL(12,3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`ingredient_id`),
  UNIQUE KEY `uniq_ingredient_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE IF NOT EXISTS `product_ingredients` (
  `product_id` INT NOT NULL,
  `ingredient_id` INT NOT NULL,
  `qty_per_product` DECIMAL(12,3) NOT NULL,
  PRIMARY KEY (`product_id`, `ingredient_id`),
  KEY `idx_pi_ingredient` (`ingredient_id`),
  CONSTRAINT `pi_fk_product` FOREIGN KEY (`product_id`) REFERENCES `menu` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `pi_fk_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

