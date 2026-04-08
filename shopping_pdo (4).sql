-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 08, 2026 at 12:06 PM
-- Server version: 5.7.24
-- PHP Version: 8.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shopping_pdo`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_contact`
--

CREATE TABLE `tbl_contact` (
  `contact_id` int(11) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_address` text,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(150) DEFAULT NULL,
  `contact_facebook` varchar(255) DEFAULT NULL,
  `contact_line` varchar(255) DEFAULT NULL,
  `contact_map` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_contact`
--

INSERT INTO `tbl_contact` (`contact_id`, `contact_name`, `contact_address`, `contact_phone`, `contact_email`, `contact_facebook`, `contact_line`, `contact_map`) VALUES
(1, 'SUKSUD - สุขสุด', 'xxx', '12345678', 'xxx@gmail.com', 'https://www.google.com/search?q=google&oq=goo&gs_lcrp=EgRlZGdlKg0IARAAGIMBGLEDGIAEMgYIABBFGDkyDQgBEAAYgwEYsQMYgAQyDQgCEAAYgwEYsQMYgAQyCggDEAAYsQMYgAQyDQgEEAAYgwEYsQMYgAQyBggFEEUYPDIGCAYQRRg8MgYIBxBFGDwyBggIEEUYPDIICAkQ6QcY_FXSAQgzMjMyajBqMagCALACAQ&source', 'https://www.google.com/search?q=google&oq=goo&gs_lcrp=EgRlZGdlKg0IARAAGIMBGLEDGIAEMgYIABBFGDkyDQgBEA', 'SUKSUD - สุขสุด อ่านต่อได้ที่ https://www.wongnai.com/restaurants/1420930pC-suksud-%E0%B8%AA%E0%B8%B8%E0%B8%82%E0%B8%AA%E0%B8%B8%E0%B8%94?ref=ct');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_img_detail`
--

CREATE TABLE `tbl_img_detail` (
  `id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_img_detail`
--

INSERT INTO `tbl_img_detail` (`id`, `p_id`, `img`) VALUES
(27, 18, '69cf768d70f05_0.jpg'),
(28, 19, '69cf773c2e697_0.jpg'),
(29, 20, '69cf77ba98922_0.jpg'),
(30, 21, '69cf77f19752c_0.jpg'),
(31, 22, '69cf786266625_0.jpg'),
(32, 23, '69cf78bacd5c2_0.jpg'),
(33, 24, '69d0c1907b227_0.jpg'),
(34, 25, '69d0c315cd18c_0.jpg'),
(35, 26, '69d0c3d0baf03_0.jpg'),
(36, 27, '69d0c47da5f2e_0.jpg'),
(37, 28, '69d0c5549a822_0.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_member`
--

CREATE TABLE `tbl_member` (
  `m_id` int(11) NOT NULL,
  `m_username` varchar(100) NOT NULL,
  `m_password` varchar(255) NOT NULL,
  `m_name` varchar(150) DEFAULT NULL,
  `m_email` varchar(150) DEFAULT NULL,
  `m_tel` varchar(50) DEFAULT NULL,
  `m_address` text,
  `m_level` varchar(20) DEFAULT 'member',
  `m_img` varchar(255) DEFAULT NULL,
  `m_status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_member`
--

INSERT INTO `tbl_member` (`m_id`, `m_username`, `m_password`, `m_name`, `m_email`, `m_tel`, `m_address`, `m_level`, `m_img`, `m_status`) VALUES
(1, 'admin', '1234', 'numnim', 'admin@example.com', '0999999999', '-', 'admin', '138361385120260327_210338.png', 1),
(2, 'admin1', '1234', 'numnim', 'numnim22052003@gmail.com', '', '11/1 Room 431, 8th Floor, ABC Condominium (11/1 ห้อง 431 ชั้น 12 คอนโดมิเนียม ABC)', 'member', '1753063416_20260327_203600.png', 1),
(3, 'member2', '1234', 'cccc', 'numnim22052003@gmail.com', '1234567890', 'ตำบล แม่เหียะ อำเภอเมืองเชียงใหม่ เชียงใหม่ 50100', 'member', '180012635920260327_210315.png', 1),
(5, 'member', '1234', 'root', 'na16972@sansai.ac.th', '', '11/1 Room 431, 8th Floor, ABC Condominium (11/1 ห้อง 431 ชั้น 12 คอนโดมิเนียม ABC)', 'member', '1959906550_20260327_232033.png', 0),
(6, 'memberr', '1234', 'root', 'na16972@sansai.ac.th', '', '11/1 Room 431, 8th Floor, ABC Condominium (11/1 ห้อง 431 ชั้น 12 คอนโดมิเนียม ABC)', 'member', '686991449_20260327_232541.png', 0),
(7, 'nimnim', '1234', 'nimnim', 'numnim22052003@gmail.com', '', 'xxx', 'member', '923598231_20260403_135806.png', 0),
(8, 'leemnim', '12345', 'Big admin', 'numnim22052003@gmail.com', '1234567890', 'xxx', 'admin', '85203869020260403_151103.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment_slip`
--

CREATE TABLE `tbl_payment_slip` (
  `slip_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `payer_name` varchar(100) NOT NULL,
  `payer_phone` varchar(20) DEFAULT NULL,
  `pay_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pay_datetime` datetime NOT NULL,
  `slip_image` varchar(255) NOT NULL,
  `note` text,
  `status` varchar(50) NOT NULL DEFAULT 'รอตรวจสอบ',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_payment_slip`
--

INSERT INTO `tbl_payment_slip` (`slip_id`, `member_id`, `payer_name`, `payer_phone`, `pay_amount`, `pay_datetime`, `slip_image`, `note`, `status`, `created_at`) VALUES
(1, 5, 'สุขสุด', '0947210689', '1742.00', '2026-04-04 15:52:00', 'slip_1775292763_8410.png', 'sale | pickup:2026-04-04 17:50', 'รอตรวจสอบ', '2026-04-04 08:52:43'),
(2, 2, 'สมชาย', '22222222', '1164.00', '2026-04-04 15:58:00', 'slip_1775293100_1669.png', 'sale | pickup:2026-04-04 17:00', 'รอตรวจสอบ', '2026-04-04 08:58:20'),
(3, 2, 'นาย สุชาติ', '0987654321', '1068.00', '2026-04-04 16:00:00', 'slip_1775293222_3778.png', 'sale | pickup:2026-04-04 16:00', 'รอตรวจสอบ', '2026-04-04 09:00:22'),
(4, NULL, 'ใจดี', '0947210689', '1240.00', '2026-04-04 16:05:00', 'slip_1775293536_5771.png', 'reserve_id:1', 'รอตรวจสอบ', '2026-04-04 09:05:36'),
(5, 1, 'น.ส. เบเกอรี่ หวานหอม', '083438843', '1806.00', '2026-04-04 16:09:00', 'slip_1775293764_6682.png', 'reserve_id:2', 'รอตรวจสอบ', '2026-04-04 09:09:24'),
(6, 1, 'น.ส. น้ำตาล ละมุน', '0947210689', '845.00', '2026-04-04 16:40:00', 'slip_1775295641_7871.png', 'reserve_id:3', 'รอตรวจสอบ', '2026-04-04 09:40:41'),
(7, 1, 'ปรียาภรณ์ อัครคชสาร', '0947210689', '690.00', '2026-04-04 16:53:00', 'slip_1775296446_9478.png', 'sale | pickup:2026-04-04 17:55', 'รอตรวจสอบ', '2026-04-04 09:54:06'),
(8, 1, 'น.ส. น้ำตาล ละมุน', '0947210689', '312.50', '2026-04-04 17:00:00', 'slip_1775296838_4128.png', 'reserve_id:4', 'รอตรวจสอบ', '2026-04-04 10:00:38'),
(9, 1, 'ปรียาภรณ์ อัครคชสาร', '0947210689', '1485.00', '2026-04-04 17:01:00', 'slip_1775296886_8531.png', 'sale | pickup:2026-04-04 17:00', 'รอตรวจสอบ', '2026-04-04 10:01:27'),
(10, 1, 'mmm', '0987654321', '169.00', '2026-04-04 17:03:00', 'slip_1775296996_7048.png', 'sale | pickup:2026-04-04 17:02', 'รอตรวจสอบ', '2026-04-04 10:03:16'),
(11, 1, 'ปรียาภรณ์ อัครคชสาร จจ', '0947210689', '69.00', '2026-04-04 17:12:00', 'slip_1775297544_9595.png', 'sale | pickup:2026-04-04 17:12', 'รอตรวจสอบ', '2026-04-04 10:12:24'),
(12, 1, 'สมบุญ', '0947210689', '395.00', '2026-04-04 17:31:00', 'slip_1775298719_2843.png', 'reserve_id:5', 'รอตรวจสอบ', '2026-04-04 10:31:59'),
(13, 5, 'ปรียาภรณ์ อัครคชสาร', '22222222', '690.00', '2026-04-05 12:35:00', 'slip_1775367334_9071.jpg', 'sale | pickup:2026-04-05 14:42', 'รอตรวจสอบ', '2026-04-05 05:35:34'),
(14, NULL, 'khaojee', '4674', '740.00', '2026-04-05 12:37:00', 'slip_1775367483_5594.jpg', 'reserve_id:7', 'รอตรวจสอบ', '2026-04-05 05:38:03'),
(15, 1, 'ตัง', '0947210689', '554.00', '2026-04-07 14:12:00', 'slip_1775545985_1256.png', 'sale | pickup:2026-04-07 14:12', 'รอตรวจสอบ', '2026-04-07 07:13:05'),
(16, 1, 'ตัง1', '0947210689', '552.00', '2026-04-07 14:16:00', 'slip_1775546199_6021.png', 'sale | pickup:2026-04-07 14:16', 'รอตรวจสอบ', '2026-04-07 07:16:39'),
(17, 1, 'ตัง2', '0947210689', '69.00', '2026-04-07 14:46:00', 'slip_1775547988_9552.png', 'sale | pickup:2026-04-07 14:46', 'รอตรวจสอบ', '2026-04-07 07:46:28'),
(18, 1, 'khaojee', '4674', '1240.00', '2026-04-07 14:53:00', 'slip_1775548408_6303.png', 'reserve_id:8', 'รอตรวจสอบ', '2026-04-07 07:53:28'),
(19, 1, 'ปรียาภรณ์ อัครคชสาร', '0947210689', '1945.00', '2026-04-07 15:03:00', 'slip_1775549023_3345.png', 'sale | pickup:2026-04-07 15:03', 'รอตรวจสอบ', '2026-04-07 08:03:43'),
(20, NULL, 'oooooo', '4674', '332.00', '2026-04-08 11:35:00', 'slip_1775622962_8663.png', 'reserve_id:9', 'รอตรวจสอบ', '2026-04-08 04:36:02'),
(21, 5, 'to', '00000000000', '79.00', '2026-04-08 11:37:00', 'slip_1775623055_5726.png', 'sale | pickup:2026-04-08 13:37', 'รอตรวจสอบ', '2026-04-08 04:37:35');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product`
--

CREATE TABLE `tbl_product` (
  `p_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_detail` text,
  `p_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `p_qty` int(11) NOT NULL DEFAULT '0',
  `img` varchar(255) DEFAULT NULL,
  `p_date_save` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `p_unit` varchar(50) DEFAULT 'ชิ้น',
  `p_status` tinyint(1) DEFAULT '1',
  `p_stock` int(11) NOT NULL DEFAULT '0',
  `sale_type` enum('sale','preorder') NOT NULL DEFAULT 'sale'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_product`
--

INSERT INTO `tbl_product` (`p_id`, `type_id`, `p_name`, `p_detail`, `p_price`, `p_qty`, `img`, `p_date_save`, `p_unit`, `p_status`, `p_stock`, `sale_type`) VALUES
(18, 7, 'มะยงชิด', 'มะยงชิด คัดพิเศษ ไซส์จัมโบ้\r\nเปรี้ยวหวานลงตัว สดชื่นสุดๆ\r\nนำมาทำเป็นเมนูต่างๆมากมาย', '99.00', 30, NULL, '2026-04-03 08:13:01', 'กล่อง', 1, 13, 'sale'),
(19, 3, 'เค้กกล้วยหอม', 'เค้กกล้วยหอมทอง &quot;สุขสุด✨\r\n• กล้วยหอมทองคัดพิเศษ\r\n• เนื้อเค้กนุ่ม ชุ่มฉ่ำ\r\n• แป้งไม่ขัดสี\r\n• หอมกล้วยจากธรรมชาติ ไม่แต่งกลิ่นใดๆ\r\n• ทำสดใหม่ ไร้สารกันบูด', '69.00', 30, NULL, '2026-04-03 08:15:56', 'ชิ้น', 1, 0, 'sale'),
(20, 3, 'เค้กนมชมพู', 'เค้กนมชมพู เข้มข้นนมชมพู หอมละมุนมาก', '69.00', 10, NULL, '2026-04-03 08:18:02', 'ชิ้น', 1, 10, 'sale'),
(21, 3, 'ทอฟฟี่เค้กนมชมพู', 'ทอฟฟี่เค้กนมชมพู หอมมากก ทานคู่กับหน้าทอฟฟี่ลงตัวที่สุด', '59.00', 10, NULL, '2026-04-03 08:18:57', 'ชิ้น', 1, 10, 'sale'),
(22, 7, 'เอแคลร์ ชูครีม ไส้พิเศษ นมชมพู', 'แป้งชูเนื้อบางนุ่ม กับไส้นมชมพูหอมหวานละมุน\r\nที่มีเอกลักษณ์เฉพาะตัว อร่อยลงตัวสุดๆ', '89.00', 10, NULL, '2026-04-03 08:20:50', 'กล่อง', 1, 10, 'sale'),
(23, 5, 'คุกกี้เนยสด หลากหลายรูปแบบ', 'คุกกี้เนยสด หลากหลายรูปแบบ\r\nขนมทุกชิ้นทุกกล่อง ทำแบบโฮมเมด\r\nพวกเราตั้งใจบีบและวาดมือ ในทุกชิ้นเลยค่ะ\r\nทุกๆชิ้น พอถูกมาจัดวางในกล่องเดียวกันแล้ว\r\nดูแล้วอบอุ่น น่ารักสุดๆเลย', '169.00', 10, NULL, '2026-04-03 08:22:18', 'กล่อง', 1, 9, 'sale'),
(24, 4, 'คาราเมลครีมพัฟ สไตล์โอซาก้า', 'ด้านบนเคลือบด้วยคาราเมลสูตรพิเศษของทางร้าน\r\nที่มีความหอมของคาราเมล แต่หวานน้อย\r\nใช้เทคนิคการผสมผสานแป้งและอบแบบพิเศษ\r\nที่ทำให้มีความ “กรอบบบบบบ” สุดๆเลยค่ะ', '79.00', 10, NULL, '2026-04-04 07:45:20', 'กล่อง', 1, 7, 'sale'),
(25, 3, 'ทอฟฟี่เค้กหน้าแน่น', 'เนื้อเค้กสปันจ์ เนื้อนุ่ม ชุ่มฉ่ำ ไม่ฝืดคอ\r\nคู่กับความพิเศษด้านบนด้วยหน้าทอฟฟี่แน่นๆ\r\nเม็ดมะม่วงหิมพานต์จัดเต็ม \r\nหวานกำลังดี หอมเนย หอมนัวมากๆ', '89.00', 40, NULL, '2026-04-04 07:51:49', 'ชิ้น', 1, 26, 'sale'),
(26, 2, 'ชิโอะปัง', 'ตอนอบ เนยจะละลายไหลลงด้านล่าง\r\n ทำให้ “ก้นขนมปัง” กรอบ ฉ่ำเนย (เป็นเอกลักษณ์เลย)\r\nโรยเกลือด้านบนเล็กน้อย → เพิ่มรสเค็มตัดความมัน', '89.00', 15, NULL, '2026-04-04 07:54:56', 'ชิ้น', 1, 0, 'sale'),
(27, 3, 'ทุเรียนหมอนทอง', 'เค้กทุเรียนหน้าล้น และทุเรียนชีสพาย \r\nทุกองค์ประกอบในแต่ละเมนู ลงตัวและเข้ากันมากๆค่ะ', '299.00', 10, NULL, '2026-04-04 07:57:49', 'ชิ้น', 1, 1, 'sale'),
(28, 5, 'คุกกี้นิ่ม ชูววี่ เนื้อหนึบบ หนับบบบบ', 'เนื้อหนึบหนับ เคี้ยวหนึบ ชุ่มฉ่ำ หวานพอดี\r\nรสชาติคลาสสิค หอมเนยมากกก', '169.00', 12, NULL, '2026-04-04 08:01:24', 'กล่อง', 1, 90, 'sale');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_reservation`
--

CREATE TABLE `tbl_reservation` (
  `reserve_id` int(11) NOT NULL,
  `m_id` int(11) DEFAULT NULL,
  `order_type` enum('reserve','sale') NOT NULL DEFAULT 'reserve',
  `reserve_name` varchar(150) NOT NULL,
  `reserve_phone` varchar(20) NOT NULL,
  `pickup_date` date NOT NULL,
  `pickup_time` time NOT NULL,
  `reserve_note` text,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deposit_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_status` varchar(50) NOT NULL DEFAULT 'pending',
  `reserve_status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_reservation`
--

INSERT INTO `tbl_reservation` (`reserve_id`, `m_id`, `order_type`, `reserve_name`, `reserve_phone`, `pickup_date`, `pickup_time`, `reserve_note`, `total_amount`, `deposit_amount`, `payment_status`, `reserve_status`, `created_at`) VALUES
(1, NULL, 'reserve', 'ใจดี', '0947210689', '2026-04-03', '16:05:00', '', '2480.00', '1240.00', 'paid', 'pending', '2026-04-04 09:05:19'),
(2, 1, 'sale', 'น.ส. เบเกอรี่ หวานหอม', '083438843', '2026-04-06', '17:09:00', '', '3612.00', '1806.00', 'paid', 'pending', '2026-04-04 09:09:05'),
(3, 1, 'sale', 'น.ส. น้ำตาล ละมุน', '0947210689', '2026-04-07', '16:40:00', '', '1690.00', '845.00', 'paid', 'pending', '2026-04-04 09:40:27'),
(4, 1, 'reserve', 'น.ส. น้ำตาล ละมุน', '0947210689', '2026-04-07', '17:00:00', '', '625.00', '312.50', 'paid', 'pending', '2026-04-04 10:00:24'),
(5, 1, 'reserve', 'สมบุญ', '0947210689', '2026-04-07', '17:30:00', '', '790.00', '395.00', 'paid', 'pending', '2026-04-04 10:31:01'),
(6, NULL, 'reserve', 'khaojee', '4674', '2569-07-13', '17:00:00', 'ขอไปรับของช้าประมาณ 17:00', '2020.00', '1010.00', 'pending', 'pending', '2026-04-05 03:06:44'),
(7, NULL, 'reserve', 'khaojee', '4674', '2569-07-13', '16:40:00', '', '1480.00', '740.00', 'paid', 'pending', '2026-04-05 05:37:45'),
(8, 1, 'reserve', 'khaojee', '4674', '2569-07-13', '14:52:00', '', '2480.00', '1240.00', 'paid', 'pending', '2026-04-07 07:53:10'),
(9, NULL, 'reserve', 'oooooo', '4674', '2569-07-14', '14:35:00', '', '664.00', '332.00', 'paid', 'pending', '2026-04-08 04:35:35');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_reservation_detail`
--

CREATE TABLE `tbl_reservation_detail` (
  `rd_id` int(11) NOT NULL,
  `reserve_id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_reservation_detail`
--

INSERT INTO `tbl_reservation_detail` (`rd_id`, `reserve_id`, `p_id`, `qty`, `price`, `subtotal`) VALUES
(1, 1, 24, 10, '79.00', '790.00'),
(2, 1, 28, 10, '169.00', '1690.00'),
(3, 2, 27, 6, '299.00', '1794.00'),
(4, 2, 18, 10, '99.00', '990.00'),
(5, 2, 19, 12, '69.00', '828.00'),
(6, 3, 23, 10, '169.00', '1690.00'),
(7, 4, 27, 1, '299.00', '299.00'),
(8, 4, 18, 1, '99.00', '99.00'),
(9, 4, 19, 1, '69.00', '69.00'),
(10, 4, 20, 1, '69.00', '69.00'),
(11, 4, 22, 1, '89.00', '89.00'),
(12, 5, 20, 5, '69.00', '345.00'),
(13, 5, 22, 5, '89.00', '445.00'),
(14, 6, 24, 7, '79.00', '553.00'),
(15, 6, 28, 3, '169.00', '507.00'),
(16, 6, 23, 2, '169.00', '338.00'),
(17, 6, 26, 5, '89.00', '445.00'),
(18, 6, 21, 3, '59.00', '177.00'),
(19, 7, 26, 10, '89.00', '890.00'),
(20, 7, 21, 10, '59.00', '590.00'),
(21, 8, 24, 10, '79.00', '790.00'),
(22, 8, 28, 10, '169.00', '1690.00'),
(23, 9, 24, 3, '79.00', '237.00'),
(24, 9, 28, 1, '169.00', '169.00'),
(25, 9, 23, 1, '169.00', '169.00'),
(26, 9, 26, 1, '89.00', '89.00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sale_detail`
--

CREATE TABLE `tbl_sale_detail` (
  `sd_id` int(11) NOT NULL,
  `slip_id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_sale_detail`
--

INSERT INTO `tbl_sale_detail` (`sd_id`, `slip_id`, `p_id`, `qty`, `price`, `subtotal`) VALUES
(2, 17, 19, 1, '69.00', '69.00'),
(3, 19, 24, 2, '79.00', '158.00'),
(4, 19, 27, 3, '299.00', '897.00'),
(5, 19, 25, 10, '89.00', '890.00'),
(6, 21, 24, 1, '79.00', '79.00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_type`
--

CREATE TABLE `tbl_type` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_type`
--

INSERT INTO `tbl_type` (`type_id`, `type_name`) VALUES
(2, 'ขนมปัง (Bread)'),
(3, 'เค้ก (Cake)'),
(4, 'เพสตรี้ (Pastry)'),
(5, 'คุกกี้ (Cookie)'),
(6, 'ขนมอบอื่น ๆ (Quick Bread & Others)'),
(7, 'ขนมอบแบบพิเศษ (Specialty Bakery)');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_contact`
--
ALTER TABLE `tbl_contact`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `tbl_img_detail`
--
ALTER TABLE `tbl_img_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_img_p_id` (`p_id`);

--
-- Indexes for table `tbl_member`
--
ALTER TABLE `tbl_member`
  ADD PRIMARY KEY (`m_id`);

--
-- Indexes for table `tbl_payment_slip`
--
ALTER TABLE `tbl_payment_slip`
  ADD PRIMARY KEY (`slip_id`);

--
-- Indexes for table `tbl_product`
--
ALTER TABLE `tbl_product`
  ADD PRIMARY KEY (`p_id`),
  ADD KEY `idx_type_id` (`type_id`);

--
-- Indexes for table `tbl_reservation`
--
ALTER TABLE `tbl_reservation`
  ADD PRIMARY KEY (`reserve_id`);

--
-- Indexes for table `tbl_reservation_detail`
--
ALTER TABLE `tbl_reservation_detail`
  ADD PRIMARY KEY (`rd_id`),
  ADD KEY `reserve_id` (`reserve_id`);

--
-- Indexes for table `tbl_sale_detail`
--
ALTER TABLE `tbl_sale_detail`
  ADD PRIMARY KEY (`sd_id`),
  ADD KEY `idx_sale_detail_slip` (`slip_id`),
  ADD KEY `idx_sale_detail_product` (`p_id`);

--
-- Indexes for table `tbl_type`
--
ALTER TABLE `tbl_type`
  ADD PRIMARY KEY (`type_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_contact`
--
ALTER TABLE `tbl_contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_img_detail`
--
ALTER TABLE `tbl_img_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `tbl_member`
--
ALTER TABLE `tbl_member`
  MODIFY `m_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_payment_slip`
--
ALTER TABLE `tbl_payment_slip`
  MODIFY `slip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbl_product`
--
ALTER TABLE `tbl_product`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `tbl_reservation`
--
ALTER TABLE `tbl_reservation`
  MODIFY `reserve_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_reservation_detail`
--
ALTER TABLE `tbl_reservation_detail`
  MODIFY `rd_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_sale_detail`
--
ALTER TABLE `tbl_sale_detail`
  MODIFY `sd_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_type`
--
ALTER TABLE `tbl_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_img_detail`
--
ALTER TABLE `tbl_img_detail`
  ADD CONSTRAINT `fk_img_product` FOREIGN KEY (`p_id`) REFERENCES `tbl_product` (`p_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_product`
--
ALTER TABLE `tbl_product`
  ADD CONSTRAINT `fk_product_type` FOREIGN KEY (`type_id`) REFERENCES `tbl_type` (`type_id`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_reservation_detail`
--
ALTER TABLE `tbl_reservation_detail`
  ADD CONSTRAINT `tbl_reservation_detail_ibfk_1` FOREIGN KEY (`reserve_id`) REFERENCES `tbl_reservation` (`reserve_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_sale_detail`
--
ALTER TABLE `tbl_sale_detail`
  ADD CONSTRAINT `fk_sale_detail_product` FOREIGN KEY (`p_id`) REFERENCES `tbl_product` (`p_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sale_detail_slip` FOREIGN KEY (`slip_id`) REFERENCES `tbl_payment_slip` (`slip_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
