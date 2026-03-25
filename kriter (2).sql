-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: mysql-8.0
-- Время создания: Мар 18 2026 г., 18:13
-- Версия сервера: 8.0.35
-- Версия PHP: 8.1.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `kriter`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` int NOT NULL,
  `login` varchar(50) NOT NULL,
  `pass_hash` varchar(255) NOT NULL,
  `personal_code_hash` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `login`, `pass_hash`, `personal_code_hash`, `phone`, `created_at`) VALUES
(1, 'admin1', '$2y$10$rdJ/hFTH58XtZ4u482cWae20wPiESxIwcvGJFSH474vPwbfZajVya', '$2y$10$kQmYo71/rgX1fXXPE5cPZ./YLsrsykIyU9tHLilgrsJ0AEGf9lBRS', '1234567890', '2024-12-13 03:18:02');

-- --------------------------------------------------------

--
-- Структура таблицы `adress`
--

CREATE TABLE `adress` (
  `id_pecar` int NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `rating` decimal(3,1) DEFAULT NULL
) ;

--
-- Дамп данных таблицы `adress`
--

INSERT INTO `adress` (`id_pecar`, `location_name`, `location`, `rating`) VALUES
(1, 'Пекарня на Немиге', 'ул. Немига, 32, Минск, Беларусь', 5.0),
(2, 'Пекарня на Притыцкого', 'ул. Притыцкого, 156, Минск, Беларусь', 4.2),
(3, 'Пекарня на Козлова', 'ул. Козлова, 11, Минск, Беларусь', 4.7),
(4, 'Пекарня на Ленина', 'ул. Ленина, 50, Минск, Беларусь', 3.9),
(5, 'Пекарня на Купалы', 'ул. Янки Купалы, 21, Минск, Беларусь', 4.8);

-- --------------------------------------------------------

--
-- Структура таблицы `cart`
--

CREATE TABLE `cart` (
  `cart_id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`) VALUES
(72, 1, 14, 1),
(73, 1, 15, 2),
(74, 1, 13, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Торты'),
(2, 'Даниш'),
(3, 'Эклеры'),
(4, 'Слойки'),
(5, 'Макароны'),
(6, 'Пирожные');

-- --------------------------------------------------------

--
-- Структура таблицы `email`
--

CREATE TABLE `email` (
  `email_id` int NOT NULL,
  `email_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `email`
--

INSERT INTO `email` (`email_id`, `email_name`) VALUES
(1, 'info@kriter.ru'),
(2, 'support@kriter.ru');

-- --------------------------------------------------------

--
-- Структура таблицы `menu`
--

CREATE TABLE `menu` (
  `product_id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `quantity` int DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_special` tinyint(1) DEFAULT '0',
  `special_position` int DEFAULT NULL
) ;

--
-- Дамп данных таблицы `menu`
--

INSERT INTO `menu` (`product_id`, `category_id`, `name`, `description`, `price`, `quantity`, `image_url`, `is_special`, `special_position`) VALUES
(13, 1, 'Торт Лур', 'Нежный торт с легким бисквитом, пропитанным ароматным сиропом, и слоями воздушного крема. Идеален для торжественных случаев.', 19.46, 10, 'img\\71terri-1096-edit-tort-loure_700x420_ff6.webp', 0, NULL),
(14, 1, 'Медовый торт', 'Сладкий и насыщенный медовый торт, состоящий из нескольких слоев медового бисквита и крема на основе сгущенного молока. Прекрасно подходит к чаю.', 10.84, 23, 'img\\111-terri-1079-edit-tort-medovyi_700x420_ff6.webp', 0, NULL),
(15, 1, 'Творожно-Бисквитный торт', 'легкий и воздушный торт с нежным творожным кремом и мягким бисквитом. Отличный выбор для любителей десертов с низкой калорийностью.', 13.79, 7, 'img\\101-terri-553-edit-tvorozhno-biskvitnyi-tort_700x420_ff6.webp', 0, NULL),
(16, 1, 'Торт Наполеон', 'Классический десерт с хрустящими слоеными коржами и богатым заварным кремом. Этот торт покорит сердца всех сладкоежек.', 21.46, 28, 'img\\img-6296_700x420_ff6.webp', 0, NULL),
(17, 1, 'Торт Миндально-клубничный', 'Уникальное сочетание миндального бисквита и свежих клубник, пропитанных легким кремом. Идеальный выбор для летнего десерта.', 28.92, 24, 'img\\img-6295_700x420_ff6.webp', 0, NULL),
(18, 2, 'Даниш с клубникой', 'Сдобное тесто, наполненное сладкой клубничной начинкой и посыпанное сахарной пудрой. Прекрасное лакомство к утреннему кофе.', 11.24, 11, 'img\\4danish-s-klubnikoi_700x420_ff6.webp', 0, NULL),
(19, 2, 'Даниш с черникой', 'Нежное тесто с насыщенной черничной начинкой, которая дарит яркий вкус и аромат. Идеален для завтрака или полдника.', 14.43, 23, 'img\\5danish-s-chernikoi_700x420_ff6.webp', 0, NULL),
(20, 2, 'Даниш с малиной', 'Сладкое тесто, наполненное сочной малиной и покрытое легкой глазурью. Это лакомство обязательно порадует любителей ягодных десертов.', 31.42, 27, 'img\\6danish-s-malinoi_700x420_ff6.webp', 0, NULL),
(21, 3, 'Эклер вишневый', 'Нежные эклеры с вишневым кремом внутри, покрытые шоколадной глазурью. Идеальный десерт для любителей кисло-сладких вкусов.', 13.80, 17, 'img\\27-terri-1108-edit-ekler-vishnevyi_700x420_ff6.webp', 0, NULL),
(22, 3, 'Эклер кокосовый', 'Легкие эклеры с кокосовым кремом, обладающие нежным вкусом тропиков. Отлично подходят для летнего угощения.', 24.75, 14, 'img\\37-terri-1112-edit-ekler-kokosovyi_700x420_ff6.webp', 0, NULL),
(23, 3, 'Эклер с черной смородиной', 'Эклеры с насыщенным кремом из черной смородины, покрытые сладкой глазурью. Прекрасное сочетание кислоты и сладости.', 12.34, 23, 'img\\67-terri-1115-edit-ekler-s-chernoi-smorodinoi_700x420_ff6.webp', 0, NULL),
(24, 3, 'Эклер фисташковый', 'Нежные эклеры с фисташковым кремом внутри, украшенные фисташковой крошкой сверху. Идеальный выбор для гурманов.', 37.44, 33, 'img\\87terri-485-edit-ekler-fistashkovyi_700x420_ff6.webp', 0, NULL),
(25, 3, 'Эклер ванильный', 'Классические эклеры с нежным ванильным кремом, покрытые шоколадной глазурью. Настоящее наслаждение для сладкоежек.', 20.18, 36, 'img\\ekler-vanilnyi_700x420_ff6.webp', 0, NULL),
(26, 4, 'Слойка с изюмом', 'Хрустящие слоеные пирожки с сочным изюмом внутри, посыпанные сахаром. Прекрасно подходят к чаю или кофе.', 38.58, 9, 'img\\13-sloika-s-izyumom_700x420_ff6.webp', 0, NULL),
(27, 4, 'Слойка с вишней', 'Слоеное тесто с ароматной вишневой начинкой и легким сахарным посыпанием. Идеальный десерт для любителей кислых ягод.', 32.37, 9, 'img\\16sloika-s-vishnei_700x420_ff6.webp', 0, NULL),
(28, 4, 'Слойка с абрикосом', 'Сочные абрикосы в слоеном тесте создают идеальное сочетание сладости и кислинки в каждом кусочке.', 36.12, 26, 'img\\17sloika-s-abrikosom_700x420_ff6.webp', 0, NULL),
(29, 4, 'Слойка с ветчиной и сыром', 'Сытная слойка с нежной ветчиной и расплавленным сыром внутри — отличный вариант для перекуса или легкого обеда.', 13.47, 33, 'img\\terri-395-sloika-s-vetchinoi-i-syrom_700x420_ff6.webp', 0, NULL),
(30, 4, 'Слойка с сыром', 'Хрустящая слойка, наполненная нежным сыром, идеально подходит для закуски на празднике или в качестве перекуса.', 39.00, 4, 'img\\terri-402-sloika-s-syrom_700x420_ff6.webp', 0, NULL),
(31, 4, 'Слойка с курицей и карри', 'Ароматная куриная начинка со специями карри в слоеном тесте — экзотическое угощение для любителей острых вкусов.', 24.59, 28, 'img\\terri-424-sloika-s-kuritsei-i-karri_700x420_ff6.webp', 0, NULL),
(32, 5, 'Макарон с клубникой', 'Нежные макароны с ароматной клубничной начинкой — идеальное лакомство для сладкоежек и романтических вечеров.', 25.93, 24, 'img\\25-terri-1161-makaron-klubnichnyi_700x420_ff6.webp', 0, NULL),
(33, 5, 'Макарон с черной смородиной', 'Яркие макароны с насыщенным вкусом черной смородины — отличное сочетание сладости и кислинки в каждом кусочке.', 15.90, 24, 'img\\35-makaron-s-chernoi-smorodinoi_700x420_ff6.webp', 0, NULL),
(34, 5, 'Макарон фисташковый', 'Легкие макароны с фисташковым кремом внутри — это изысканное угощение для настоящих гурманов.', 21.72, 14, 'img\\65-terri-1164-makaron-fistashkovyi_700x420_ff6.webp', 0, NULL),
(35, 5, 'Макарон малиновый', 'Нежные макароны с малиновым кремом, которые порадуют вас своим ярким вкусом и ароматом свежих ягод.', 20.91, 20, 'img\\15-terri-2284-makaron-malinovyi_700x420_ff6.webp', 0, NULL),
(36, 5, 'Макарон с соленой карамелью', 'Уникальное сочетание сладости и легкой солености в каждом макароне — это лакомство поражает своим вкусом!', 29.35, 5, 'img\\55-terri-506-makaron-s-solenoi-karamelyu_700x420_ff6.webp', 0, NULL),
(37, 6, 'Пирожное Ализи', 'Нежное пирожное со сливочным кремом и фруктовой начинкой, которое подарит вам истинное наслаждение при каждом укусе.', 14.06, 38, 'img\\26-terri-1084-edit-pirozhnoe-alizi_700x420_ff6.webp', 0, NULL),
(38, 6, 'Пирожное Версаль', 'Элегантное пирожное с шоколадным муссом и тонким слоем бисквита — идеальный выбор для особых случаев.', 32.22, 7, 'img\\56-terri-430-pirozhnoe-versal_700x420_ff6.webp', 0, NULL),
(39, 6, 'Пирожное Лурэ', 'Нежное пирожное со сливочным кремом и ягодами, которое станет настоящим украшением любого стола.', 18.95, 21, 'img\\96-terri-431-edit-pirozhnoe-loure_700x420_ff6.webp', 0, NULL),
(40, 6, 'Пирожное Акапулько', 'Экзотическое пирожное с фруктовой начинкой и легким кремом, которое перенесет вас на солнечные пляжи Мексики.', 18.08, 33, 'img\\16-terri-229-pirozhnoe-akapulko_700x420_ff6.webp', 0, NULL),
(41, 6, 'Пирожное Маскарпоне', 'Нежное пирожное на основе сыра маскарпоне, которое порадует вас своим сливочным вкусом и легкостью.', 23.56, 32, 'img\\46-terri-454-edit-pirozhnoe-maskarpone_700x420_ff6.webp', 0, NULL),
(42, 6, 'Пирожное Три шоколада', 'Тонкий миндальный бисквит в основе, три слоя лёгкого мусса – горький, молочный и белый шоколад в дополнении хрустящей прослойки из вафельной крошки и миндальной пасты Пралине.', 13.15, 49, 'img\\86-terri-434-edit-pirozhnoe-tri-shokolada.jpg', 0, NULL),
(43, 5, 'йцукен', 'йывакеролщшгнпаку', 16.15, 40, 'img\\86-terri-434-edit-pirozhnoe-tri-shokolada.jpg', 0, NULL),
(44, 5, 'ddddddddddddddd', 'dddddddddddddddddddddddddddddddddddd', 2222.00, 222, 'img\\86-terri-434-edit-pirozhnoe-tri-shokolada.jpg', 0, NULL),
(45, 4, 'cccccccccccccccccccccccccccccc', 'ccccccccccccccccccc cccccccccccccccc cccccccccccccccccc cccccccccccccccccc cccccccccccccccccc ccccccccccc', 22222.00, 2222, 'img\\86-terri-434-edit-pirozhnoe-tri-shokolada.jpg', 0, NULL),
(46, 2, 'Торт Лурэ', 'мрикнг4от5ге8п9мщ счдыжцук567гш', 12345.00, 234, 'img\\86-terri-434-edit-pirozhnoe-tri-shokolada.jpg', 0, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `number_order` int NOT NULL,
  `user_id` int NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `addres_street` varchar(255) NOT NULL,
  `addres_house` varchar(50) NOT NULL,
  `addres_apt` varchar(50) DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `delivery_time` time NOT NULL,
  `status` enum('Принят','Доставлен') NOT NULL,
  `total_price` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`number_order`, `user_id`, `user_name`, `addres_street`, `addres_house`, `addres_apt`, `delivery_date`, `delivery_time`, `status`, `total_price`) VALUES
(15, 1, 'qwer', 'чи', '99', '199', '2025-01-04', '19:23:00', 'Принят', 83.68),
(18, 1, 'Торт Лурэ', 'впаркпа', '32', '32', '2024-12-25', '18:44:00', 'Принят', 186.12),
(19, 1, 'qwer', 'чичурин', '7s', '123', '2024-12-27', '23:55:00', 'Принят', 20.48),
(20, 1, 'qwer', 'чичурин', '96a', '17', '2024-12-19', '11:59:00', 'Принят', 74.25),
(22, 1, 'qwer', 'чичка', '95', '32', '2024-12-26', '10:00:00', 'Принят', 234.00),
(23, 1, 'qwer', 'чичурина', '99', '18', '2026-02-22', '18:33:00', 'Принят', 32.52),
(24, 1, 'ke_na', 'чичурин', '32', '17', '2024-12-20', '20:07:00', 'Принят', 49.84),
(26, 4, 'qwerty', 'Карвата', '32', '30', '2024-12-27', '15:30:00', 'Принят', 382.53),
(27, 1, 'Медовый торт', 'чичурин', '98', '17', '2025-01-28', '17:13:00', 'Принят', 234.00),
(28, 3, 'Торт Лурэ', 'чичурин', '18', '99', '3025-12-09', '11:59:00', 'Доставлен', 207.41),
(31, 3, 'Торт Лурэ', 'довато', '97', '32', '2025-05-31', '17:00:00', 'Доставлен', 343.90),
(32, 3, 'рппито', 'чичурина', '96', '32', '2025-05-06', '20:37:00', 'Доставлен', 57.15),
(33, 3, 'Торт Лурэ', 'доватора', '97', '32', '2025-05-15', '17:40:00', 'Доставлен', 0.00);

-- --------------------------------------------------------

--
-- Структура таблицы `order_details`
--

CREATE TABLE `order_details` (
  `product_id` int NOT NULL,
  `number_order` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `order_details`
--

INSERT INTO `order_details` (`product_id`, `number_order`, `product_name`, `quantity`, `price`, `total_price`) VALUES
(13, 15, 'Торт Лурэ', 8, 10.46, 83.68),
(26, 18, 'Слойка с изюмом', 4, 38.58, 186.12),
(33, 18, 'Макарон с черной смородиной', 2, 15.90, 186.12),
(18, 19, 'Даниш с клубникой', 2, 10.24, 20.48),
(22, 20, 'Эклер кокосовый', 3, 24.75, 74.25),
(30, 22, 'Слойка с сыром', 6, 39.00, 234.00),
(14, 23, 'Медовый торт', 3, 10.84, 32.52),
(14, 24, 'Медовый торт', 1, 10.84, 49.84),
(30, 24, 'Слойка с сыром', 1, 39.00, 49.84),
(24, 26, 'Эклер фисташковый', 2, 37.44, 382.53),
(26, 26, 'Слойка с изюмом', 2, 38.58, 382.53),
(30, 26, 'Слойка с сыром', 2, 39.00, 382.53),
(36, 26, 'Макарон с соленой карамелью', 3, 29.35, 382.53),
(38, 26, 'Пирожное Версаль', 2, 32.22, 382.53),
(30, 27, 'Слойка с сыром', 6, 39.00, 234.00),
(30, 28, 'Слойка с сыром', 3, 39.00, 207.41),
(19, 28, 'Даниш с черникой', 3, 14.43, 207.41),
(41, 28, 'Пирожное Маскарпоне', 2, 23.56, 207.41),
(17, 31, 'Торт Миндально-клубничный', 5, 28.92, 343.90),
(25, 31, 'Эклер ванильный', 4, 20.18, 343.90),
(27, 31, 'Слойка с вишней', 2, 32.37, 343.90),
(13, 31, 'Торт Лурэ', 4, 13.46, 343.90),
(14, 32, 'Медовый торт', 4, 10.84, 57.15),
(15, 32, 'Творожно-Бисквитный торт', 1, 13.79, 57.15),
(14, 33, 'Медовый торт', 22, 10.84, 0.00),
(15, 33, 'Творожно-Бисквитный торт', 1, 13.79, 0.00);

--
-- Триггеры `order_details`
--
DELIMITER $$
CREATE TRIGGER `sync_order_details_total_price_insert` BEFORE INSERT ON `order_details` FOR EACH ROW BEGIN
    DECLARE order_total DECIMAL(10,2);
    
    SELECT total_price INTO order_total
    FROM orders
    WHERE number_order = NEW.number_order
    LIMIT 1;
    
    IF order_total IS NOT NULL THEN
        SET NEW.total_price = order_total;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `sync_order_details_total_price_update` BEFORE UPDATE ON `order_details` FOR EACH ROW BEGIN
    DECLARE order_total DECIMAL(10,2);
    
    SELECT total_price INTO order_total
    FROM orders
    WHERE number_order = NEW.number_order
    LIMIT 1;
    
    IF order_total IS NOT NULL THEN
        SET NEW.total_price = order_total;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `phones`
--

CREATE TABLE `phones` (
  `phone_id` int NOT NULL,
  `phone` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `phones`
--

INSERT INTO `phones` (`phone_id`, `phone`) VALUES
(1, '123-456-7890'),
(2, '987-654-3210'),
(3, '555-555-5555');

-- --------------------------------------------------------

--
-- Структура таблицы `social_network`
--

CREATE TABLE `social_network` (
  `social_network_id` int NOT NULL,
  `social_network_name` varchar(100) NOT NULL,
  `social_network_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `social_network`
--

INSERT INTO `social_network` (`social_network_id`, `social_network_name`, `social_network_url`) VALUES
(1, 'TikTok', 'https://www.tiktok.com/@nikit.nest'),
(2, 'Instagram', 'https://www.instagram.com/nikit.nest/');

-- --------------------------------------------------------

--
-- Структура таблицы `special_offers`
--

CREATE TABLE `special_offers` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `position` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `special_offers`
--

INSERT INTO `special_offers` (`id`, `product_id`, `position`) VALUES
(40, 39, 1),
(41, 37, 2),
(42, 38, 3);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `name_user` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `login` varchar(50) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(17) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id_user`, `name_user`, `login`, `pass`, `email`, `phone`) VALUES
(1, 'qazwsx', 'qazwsx', '$2y$10$Ma01BBePE2DRTjI4uYqw2eouYD127m3sbGWKMSOW/VyAdhtwt0I3S', 'qazwsx.2005.qazwsx@gmail.com', '+375(33)664-02-53'),
(2, 'йцукен', 'Ибрагим', '$2y$10$DEugcLI59dP9ydYbp7wLROAiPpsxPrKQzpHFld1xVKnfBKVXTbN7e', 'ibragimMystafa@gmail.com', '+375(44)456-78-02'),
(3, 'mnmnmnmnmnmnmn', 'mnuentity', '$2y$10$bcX4NsgJzbD84eHc6WoyButtrvOEZ1EpJk7q6HuvGE8vgTNaS18E6', 'mnuentity.mn@gmail.com', '+375(44)664-02-78'),
(4, 'qwerty', 'qwerty', '$2y$10$TPbrxgs5Bj4dJaII2868Quqreo4i3tIycGUz/tVwr561pSk76i0yW', 'qwert.123456789.qwert@gmail.com', '+375(33)664-02-53');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Индексы таблицы `adress`
--
ALTER TABLE `adress`
  ADD PRIMARY KEY (`id_pecar`);

--
-- Индексы таблицы `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Индексы таблицы `email`
--
ALTER TABLE `email`
  ADD PRIMARY KEY (`email_id`);

--
-- Индексы таблицы `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`number_order`),
  ADD KEY `fk_user` (`user_id`);

--
-- Индексы таблицы `order_details`
--
ALTER TABLE `order_details`
  ADD KEY `number_order` (`number_order`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `phones`
--
ALTER TABLE `phones`
  ADD PRIMARY KEY (`phone_id`);

--
-- Индексы таблицы `social_network`
--
ALTER TABLE `social_network`
  ADD PRIMARY KEY (`social_network_id`);

--
-- Индексы таблицы `special_offers`
--
ALTER TABLE `special_offers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_position` (`position`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `adress`
--
ALTER TABLE `adress`
  MODIFY `id_pecar` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `email`
--
ALTER TABLE `email`
  MODIFY `email_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `menu`
--
ALTER TABLE `menu`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `number_order` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT для таблицы `phones`
--
ALTER TABLE `phones`
  MODIFY `phone_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `social_network`
--
ALTER TABLE `social_network`
  MODIFY `social_network_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `special_offers`
--
ALTER TABLE `special_offers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `menu` (`product_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`);

--
-- Ограничения внешнего ключа таблицы `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`number_order`) REFERENCES `orders` (`number_order`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `special_offers`
--
ALTER TABLE `special_offers`
  ADD CONSTRAINT `special_offers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `menu` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
