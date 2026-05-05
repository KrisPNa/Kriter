-- Добавление поля "composition" (состав) к товарам
-- Таблица: menu

ALTER TABLE `menu`
  ADD COLUMN `composition` TEXT NULL AFTER `description`;

