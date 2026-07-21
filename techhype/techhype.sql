-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 06:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `techhype`
--

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `attempted_at`) VALUES
(7, 'admin@techhype.my', '::1', '2026-04-13 21:14:50'),
(8, 'admin@techype.my', '::1', '2026-04-13 21:15:07'),
(9, 'admin@techype.com', '::1', '2026-04-13 21:15:22'),
(10, 'admin@techype.com', '::1', '2026-04-13 21:15:48'),
(11, 'admin@techype.com', '::1', '2026-04-13 21:15:58'),
(12, 'admin2@techype.com', '::1', '2026-04-13 21:17:11'),
(13, 'admin2@techype.my', '::1', '2026-04-13 21:17:32'),
(17, 'admin2@techhype.com', '::1', '2026-04-13 21:37:26'),
(18, 'admin@techhype.my', '::1', '2026-04-14 10:19:30'),
(19, 'admin2@techhype.my', '::1', '2026-04-14 14:50:02'),
(20, 'admin2@techhype.com', '::1', '2026-04-14 14:50:07'),
(21, 'admin2@techhype.com', '::1', '2026-04-14 14:50:11'),
(22, 'admin2@techhype.com', '::1', '2026-04-14 14:51:26'),
(24, 'admin@techhype.my', '::1', '2026-04-14 15:16:31');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_name` varchar(100) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `payment_method` enum('card','bank','cod') DEFAULT 'cod',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `shipping_name`, `shipping_phone`, `shipping_address`, `payment_method`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 3999.00, 'delivered', 'Admin', '0177350799', 'no 01, Jalan Merak 8\r\nTaman Scientex Aster, 81700 Pasir Gudang', 'card', '', '2026-03-10 07:35:44', '2026-03-10 07:36:34'),
(2, 1, 5499.00, 'delivered', 'Admin1', 'huynmiopk', 'buyinhjok', 'card', '', '2026-03-19 12:20:31', '2026-03-23 14:17:35'),
(3, 2, 2699.00, 'delivered', 'Cheng Wei Yang', '0177350799', 'no 10, Jalan Merak 5\r\nTaman Scientex Aster', '', '', '2026-03-23 13:47:14', '2026-03-24 08:19:09'),
(4, 2, 6530.00, 'delivered', 'Pirates', '0177350799', 'hgdrtyfughjk', 'cod', '', '2026-03-24 08:15:43', '2026-03-24 08:18:49'),
(5, 2, 5433.00, 'pending', 'Sabrinaha', '0177350799', 'no 10, Jalan Merak 2\r\nTaman Scientex Aster', 'bank', '', '2026-04-14 07:14:15', '2026-04-14 07:14:15'),
(6, 10, 5499.00, 'pending', 'KAI ZHE', '+60 17 735 0798', 'no 19, Jalan Merak 4\r\nTaman Scientex Aster', 'card', '', '2026-04-16 06:44:23', '2026-04-16 06:44:23');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(2, 2, 41, 1, 5499.00),
(3, 3, 36, 1, 2699.00),
(4, 4, 48, 1, 959.00),
(5, 4, 21, 1, 5699.00),
(6, 5, 77, 1, 5499.00),
(7, 6, 77, 1, 5499.00);

-- --------------------------------------------------------

--
-- Table structure for table `points_log`
--

CREATE TABLE `points_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `type` enum('earn','redeem','refund') NOT NULL,
  `description` varchar(255) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `points_log`
--

INSERT INTO `points_log` (`id`, `user_id`, `points`, `type`, `description`, `order_id`, `created_at`) VALUES
(1, 2, 2699, 'earn', 'Earned from Order #00003', 3, '2026-03-23 21:47:14'),
(2, 2, 200, 'earn', 'Student verification bonus', NULL, '2026-03-23 22:15:20'),
(3, 2, -2800, 'redeem', 'Redeemed for Order #00004', 4, '2026-03-24 16:15:43'),
(4, 2, 6530, 'earn', 'Earned from Order #00004', 4, '2026-03-24 16:15:43'),
(5, 2, -6600, 'redeem', 'Redeemed for Order #00005', 5, '2026-04-14 15:14:15'),
(6, 2, 5433, 'earn', 'Earned from Order #00005', 5, '2026-04-14 15:14:15'),
(7, 1, 200, 'earn', 'Student verification bonus', NULL, '2026-04-14 15:21:48'),
(8, 10, 5499, 'earn', 'Earned from Order #00006', 6, '2026-04-16 14:44:23');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `category` enum('mobile','tablet','laptop','console','audio','watch','accessory') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `specs` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `photo` varchar(255) DEFAULT 'default-product.png',
  `stock` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `colors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`colors`)),
  `storage_variants` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`storage_variants`)),
  `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`)),
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `brand`, `category`, `price`, `sale_price`, `specs`, `description`, `specifications`, `photo`, `stock`, `status`, `created_at`, `updated_at`, `colors`, `storage_variants`, `gallery`, `sort_order`) VALUES
(2, 'Galaxy Z Fold 6', 'Samsung', 'mobile', 7499.00, NULL, '512GB | 12GB RAM | Foldable', 'Samsung foldable phone with multitasking capabilities.', NULL, 'galaxy-z-fold6.png', 30, 'active', '2026-03-10 07:17:45', '2026-04-02 17:01:42', '[{\"name\":\"Navy\",\"hex\":\"#1b2a4a\",\"image\":\"\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 7,499\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 7,999\",\"sale\":\"\"},{\"label\":\"12GB + 1TB\",\"price\":\"RM 8,999\",\"sale\":\"\"}]},{\"name\":\"Silver Shadow\",\"hex\":\"#c0c0c0\",\"image\":\"\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 7,499\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 7,999\",\"sale\":\"\"}]},{\"name\":\"Pink\",\"hex\":\"#f5c6d0\",\"image\":\"\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 7,599\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 8,099\",\"sale\":\"\"}]}]', '[{\"label\":\"12GB + 256GB\",\"price\":7499,\"sale\":0},{\"label\":\"12GB + 512GB\",\"price\":7999,\"sale\":0},{\"label\":\"12GB + 1TB\",\"price\":8999,\"sale\":0}]', NULL, 7),
(3, 'Galaxy Tab S11 Ultra (5G)', 'Samsung', 'tablet', 5499.00, NULL, '256GB | 12GB RAM | 14.6&amp;amp;quot;', 'Premium Android tablet with LED display.', '{\"display_size\":\"14.6\",\"display_resolution\":\"2960 x 1848 (WQXGA+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"13.0 MP + 8.0 MP\",\"front_camera\":\"12.0MP\",\"rear_camera_fnumber\":\"-\",\"video_resolution\":\"UHD 4K (3840 x 2160)@30fps\",\"chipset\":\"MediaTek Dimensity 9400+\",\"battery_capacity\":\"11600\",\"cpu_speed\":\"3.73GHz, 3.3GHz, 2.4GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256 \\/ 512\",\"dimensions\":\"165.8 x 254.3 x 6.0\",\"weight\":\"500\",\"sim_count\":\"Dual-SIM\",\"os\":\"Android\",\"connectivity\":\"Wi-Fi + 5G\"}', '69b98434d5830.jpg', 25, 'active', '2026-03-10 07:17:45', '2026-04-02 17:01:42', '[{\"name\":\"Gray\",\"hex\":\"#383838\",\"image\":\"69b98434d812a.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,899\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,499\",\"sale\":\"RM 6,299\"}]},{\"name\":\"Silver\",\"hex\":\"#e5e4e2\",\"image\":\"69b98434d8445.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 2,199\",\"sale\":\"RM 2,099\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,499\",\"sale\":\"RM 6,299\"}]}]', '[{\"label\":\"12GB + 256GB\",\"price\":4799,\"sale\":0},{\"label\":\"16GB + 512GB\",\"price\":5799,\"sale\":0}]', '[\"69b98434d6f9d.jpg\",\"69b98434d72ee.jpg\",\"69b98434d7631.webp\",\"69b98434d7ae7.webp\"]', 8),
(4, 'iPhone 17 Pro', 'Apple', 'mobile', 5499.00, NULL, '256GB | 8GB RAM | A18 Pro', 'Apple flagship with titanium design and Action button.', '{\"display_size\":\"6.3\",\"display_resolution\":\"1206 x 2622\",\"display_technology\":\"LTPO Super Retina XDR OLED\",\"rear_camera\":\"48.0 MP + 48.0 MP + 48.0 MP\",\"front_camera\":\"18.0 MP\",\"rear_camera_fnumber\":\"F1.8 , F2.8 , F2.2\",\"video_resolution\":\"4K@24\\/25\\/30\\/60\\/100\\/120fps, 1080p@25\\/30\\/60\\/120\\/240fps\",\"chipset\":\"Apple A19 Pro (3 nm)\",\"battery_capacity\":\"3998\",\"cpu_speed\":\"2x4.26 GHz + 4x2.60 GHz\",\"cpu_type\":\"Hexa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256 \\/ 512 \\/ 1024 \\/ 2048\",\"sim_count\":\"Dual-SIM\",\"os\":\"iOS 26\"}', '69c296afac992.webp', 60, 'active', '2026-03-10 07:17:45', '2026-04-14 01:18:30', '[{\"name\":\"Silver\",\"hex\":\"#e5e4e2\",\"image\":\"69c296afaf1b0.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,499\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,499\",\"sale\":\"\"},{\"label\":\"12GB + 1024GB\",\"price\":\"RM 7,499\",\"sale\":\"\"}]},{\"name\":\"Cosmic Orange\",\"hex\":\"#ff6700\",\"image\":\"69c296afaf5e6.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,499\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,499\",\"sale\":\"\"},{\"label\":\"12GB + 1024GB\",\"price\":\"RM 7,499\",\"sale\":\"\"}]},{\"name\":\"Deep Blue\",\"hex\":\"#4169e1\",\"image\":\"69c296afaf9dc.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,499\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,499\",\"sale\":\"\"},{\"label\":\"12GB + 1024GB\",\"price\":\"RM 7,499\",\"sale\":\"\"}]}]', '[{\"label\":\"8GB + 256GB\",\"price\":5799,\"sale\":0},{\"label\":\"8GB + 512GB\",\"price\":6599,\"sale\":0},{\"label\":\"8GB + 1TB\",\"price\":7599,\"sale\":0}]', '[\"69c296afadba1.webp\",\"69c296afadeb7.webp\",\"69c296afae1c4.webp\",\"69c296afae4ad.webp\",\"69c296afae7e7.webp\",\"69c296afaeaa4.webp\",\"69c296afaee8f.webp\"]', 1),
(6, 'iPad Pro M4', 'Apple', 'tablet', 5499.00, NULL, '256GB | 16GB RAM | 13&quot;', 'Thinnest Apple product ever with M4 chip.', '{\"display_size\":\"13.0\",\"display_resolution\":\"2064 x 2752 pixels\",\"display_technology\":\"Ultra Retina Tandem OLED\",\"rear_camera\":\"12.0MP\",\"front_camera\":\"12.0MP\",\"rear_camera_fnumber\":\"F1.8\",\"video_resolution\":\"4K@24\\/25\\/30\\/60fps, 1080p@25\\/30\\/60\\/120\\/240fps\",\"chipset\":\"Apple M5\",\"battery_capacity\":\"10,290\",\"cpu_speed\":\"-\",\"cpu_type\":\"Hexa-Core\",\"memory_gb\":\"12, 16\",\"storage_gb\":\"256 \\/ 512 \\/ 1024 \\/ 2048\",\"sim_count\":\"-\",\"os\":\"iOS 26\",\"connectivity\":\"Wi-Fi Only\"}', '69cc8a4c4051f.webp', 40, 'active', '2026-03-10 07:17:45', '2026-04-14 01:18:30', '[{\"name\":\"Space Black\",\"hex\":\"#1d1d1f\",\"image\":\"69cc8a4c42a81.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,799\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,699\",\"sale\":\"\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 8,499\",\"sale\":\"\"},{\"label\":\"16GB + 2048GB\",\"price\":\"RM 10,299\",\"sale\":\"\"}]},{\"name\":\"Silver\",\"hex\":\"#e3e4e5\",\"image\":\"69cc8a4c42de6.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,799\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,699\",\"sale\":\"\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 8,499\",\"sale\":\"\"},{\"label\":\"16GB + 2048GB\",\"price\":\"RM 10,299\",\"sale\":\"\"}]}]', '[{\"label\":\"8GB + 256GB\",\"price\":4999,\"sale\":0},{\"label\":\"8GB + 512GB\",\"price\":5999,\"sale\":0},{\"label\":\"16GB + 1TB\",\"price\":7999,\"sale\":0}]', '[\"69cc8a4c416db.webp\",\"69cc8a4c4191e.webp\",\"69cc8a4c41bbd.webp\",\"69cc8a4c41dba.webp\",\"69cc8a4c41fdf.webp\",\"69cc8a4c421a5.webp\",\"69cc8a4c423e0.webp\",\"69cc8a4c42739.webp\"]', 4),
(9, 'PlayStation 5 Digital Edition', 'Sony', 'console', 3199.00, NULL, '2TB | 4K 120fps | Ray Tracing', 'Next-gen gaming console with enhanced graphics.', '[]', 'ps5-pro.png', 30, 'active', '2026-03-10 07:17:45', '2026-04-02 17:01:53', '[{\"name\":\"White\",\"hex\":\"#f5f5f5\",\"image\":\"ps5-pro.png\",\"storage\":[]}]', '[{\"label\":\"2TB\",\"price\":2999,\"sale\":0}]', '[]', 3),
(10, 'PlayStation 5 Bundle Disc', 'Sony', 'console', 2290.00, NULL, '1TB | Digital Edition', 'Slimmer PS5 design with digital game library.', '{\"console_cpu\":\"8-Core AMD Ryzen Zen 2 (16 threads); Base 3.5 GHz \\/\",\"console_gpu\":\"16.7 TFLOPS (AMD Radeon RDNA-based); 60 Compute Units (up from 36 on base PS5)\",\"console_memory\":\"16GB GDDR6 + 2GB DDR5\",\"console_storage_type\":\"2TB Custom NVMe SSD (5.5 GB\\/s raw read speed); includes M.2 expansion slot\",\"console_optical_drive\":\"4K @ 120Hz\",\"console_max_resolution\":\"8K @ 60Hz\",\"console_frame_rate\":\"120 FPS\",\"console_ray_tracing\":\"Yes\",\"console_hdr\":\"Supports HDR10 and Dynamic HDR\",\"console_audio_output\":\"Tempest 3D AudioTech engine\",\"console_usb_ports\":\"2x USB-C (SuperSpeed 10Gbps), 2x USB-A (SuperSpeed 10Gbps)\",\"console_hdmi\":\"HDMI 2.1 (48 Gbps)\",\"console_wifi\":\"Wi-Fi 7\",\"console_bluetooth\":\"Bluetooth 5.1\",\"console_power_consumption\":\"390W\"}', '69ce9ffd6ee3c.webp', 50, 'active', '2026-03-10 07:17:45', '2026-04-13 14:33:47', '[]', '[{\"label\":\"1TB Digital\",\"price\":1699,\"sale\":0},{\"label\":\"1TB Disc\",\"price\":1999,\"sale\":0}]', '[]', 1),
(11, 'WH-1000XM5', 'Sony', 'audio', 1499.00, 1199.00, 'ANC | 30hr | LDAC | Hi-Res', 'Industry-leading noise cancelling headphones.', NULL, 'sony-wh1000xm5.png', 100, 'active', '2026-03-10 07:17:45', '2026-04-02 17:01:53', '[{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"sony-wh1000xm5.png\"},{\"name\":\"Silver\",\"hex\":\"#c0c0c0\",\"image\":\"sony-wh1000xm5.png\"},{\"name\":\"Midnight Blue\",\"hex\":\"#1b2a4a\",\"image\":\"sony-wh1000xm5.png\"}]', '[]', NULL, 4),
(12, 'Pixel 10 Pro', 'Google', 'mobile', 3749.00, NULL, '128GB | 16GB RAM | Tensor G4', 'Google AI-first smartphone with best camera.', '{\"display_size\":\"6.3-inch\",\"display_resolution\":\"1280 x 2856\",\"display_technology\":\"LTPO OLED\",\"rear_camera\":\"50.0 MP + 48.0 MP + 48.0 MP\",\"front_camera\":\"42\",\"rear_camera_fnumber\":\"F1.68 , F1.7 , F2.8\",\"video_resolution\":\"8K video recording at 24\\/30 FPS\",\"chipset\":\"Google Tensor G5\",\"battery_capacity\":\"4870\",\"cpu_speed\":\"3.78GHz, 3.05GHz, 2.25GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"16\",\"storage_gb\":\"128 \\/ 256 \\/ 512 \\/ 1024\",\"sim_count\":\"Dual-SIM\",\"os\":\"Android 16\"}', '69ce0016677f0.jpg', 40, 'active', '2026-03-10 07:17:45', '2026-04-02 16:58:28', '[{\"name\":\"Moonstone\",\"hex\":\"#e5e4e2\",\"image\":\"69cdf8105a5fe.jpg\",\"storage\":[{\"label\":\"16GB + 128GB\",\"price\":\"RM 3,749\",\"sale\":\"\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 4,249\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,849\",\"sale\":\"\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,299\",\"sale\":\"\"}]},{\"name\":\"Jade\",\"hex\":\"#98ff98\",\"image\":\"69cdf8105a95f.jpg\",\"storage\":[{\"label\":\"16GB + 128GB\",\"price\":\"RM 3,749\",\"sale\":\"\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 4,249\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,849\",\"sale\":\"\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,299\",\"sale\":\"\"}]},{\"name\":\"Porcelain\",\"hex\":\"#f5c6d0\",\"image\":\"69cdf8105abd3.jpg\",\"storage\":[{\"label\":\"16GB + 128GB\",\"price\":\"RM 3,749\",\"sale\":\"\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 4,249\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,849\",\"sale\":\"\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,299\",\"sale\":\"\"}]},{\"name\":\"Obsidian\",\"hex\":\"#3C3D3A\",\"image\":\"69cdf8105af12.jpg\",\"storage\":[{\"label\":\"16GB + 128GB\",\"price\":\"RM 3,749\",\"sale\":\"\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 4,249\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,849\",\"sale\":\"\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,299\",\"sale\":\"\"}]}]', '[{\"label\":\"16GB + 128GB\",\"price\":4299,\"sale\":0},{\"label\":\"16GB + 256GB\",\"price\":4699,\"sale\":0},{\"label\":\"16GB + 512GB\",\"price\":5199,\"sale\":0}]', '[\"69cdf81059d5d.jpg\",\"69cdf81059fe8.jpg\",\"69cdf8105a21b.jpg\"]', 0),
(16, 'Xiaomi 14 Ultra', 'Xiaomi', 'mobile', 4999.00, NULL, '512GB | 16GB RAM | Leica', 'Xiaomi flagship with Leica professional optics.', NULL, 'xiaomi-14-ultra.png', 35, 'active', '2026-03-10 07:17:45', '2026-04-14 01:20:11', '[{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"xiaomi-14-ultra.png\"},{\"name\":\"White\",\"hex\":\"#f5f5f5\",\"image\":\"xiaomi-14-ultra.png\"}]', '[{\"label\":\"16GB + 512GB\",\"price\":4999,\"sale\":0},{\"label\":\"16GB + 1TB\",\"price\":5499,\"sale\":0}]', NULL, 6),
(17, 'Nothing Phone (3a) Community Edition', 'Nothing', 'mobile', 1699.00, NULL, '256GB | 12GB RAM | Glyph', 'Unique transparent design with Glyph interface.', '{\"display_size\":\"6.77\",\"display_resolution\":\"1084 x 2392 (387 PPI)\",\"display_technology\":\"FLEXIBLE AMOLED\",\"rear_camera\":\"50.0 MP + 50.0 MP + 8.0 MP\",\"front_camera\":\"50MP\",\"rear_camera_fnumber\":\"F1.88 , F2.55 , F2.2\",\"video_resolution\":\"4K@30fps, 1080p@30fps\",\"chipset\":\"Snapdragon 7s Gen 3 5G\",\"battery_capacity\":\"5000\",\"cpu_speed\":\"2.5 GHZ\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"163.5 x 77.5 x 8.35\",\"weight\":\"211\",\"sim_count\":\"Dual-SIM\",\"os\":\"Nothing OS 3.1 Powered by Android 15\"}', '69c1614edc09f.webp', 45, 'active', '2026-03-10 07:17:45', '2026-04-14 01:19:13', '[{\"name\":\"Green\",\"hex\":\"#2e8b57\",\"image\":\"nothing-phone-2a-plus.png\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,699\",\"sale\":\"RM 1,459\"}]}]', '[{\"label\":\"8GB + 256GB\",\"price\":1499,\"sale\":0},{\"label\":\"12GB + 256GB\",\"price\":1699,\"sale\":0}]', '[\"69c1610ca1db5.webp\",\"69c1610ca20b5.webp\",\"69c1610ca241d.webp\",\"69c1610ca26c5.webp\",\"69c1610ca2940.webp\",\"69c1610ca2be8.webp\",\"69c1610ca2e62.webp\"]', 1),
(19, 'Vivo X200 Pro', 'Vivo', 'mobile', 3999.00, NULL, '256GB | 16GB RAM | Zeiss', 'Camera-centric flagship with Zeiss optics.', NULL, 'vivo-x200-pro.png', 30, 'active', '2026-03-10 07:17:45', '2026-03-20 07:25:50', '[{\"name\":\"Titanium Grey\",\"hex\":\"#6e6e6e\",\"image\":\"vivo-x200-pro-grey.jpg\",\"storage\":[{\"label\":\"16GB + 256GB\",\"price\":\"RM 3,999\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,499\",\"sale\":\"\"}]},{\"name\":\"Cosmos Black\",\"hex\":\"#1a1a1a\",\"image\":\"vivo-x200-pro-black.jpg\",\"storage\":[{\"label\":\"16GB + 256GB\",\"price\":\"RM 4,099\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,599\",\"sale\":\"\"}]}]', '[{\"label\":\"16GB + 256GB\",\"price\":3999,\"sale\":0},{\"label\":\"16GB + 512GB\",\"price\":4499,\"sale\":0}]', '[\"vivo-x200-pro-1.jpg\",\"vivo-x200-pro-2.jpg\",\"vivo-x200-pro-3.jpg\",\"vivo-x200-pro-4.jpg\",\"vivo-x200-pro-5.jpg\",\"vivo-x200-pro-6.jpg\",\"vivo-x200-pro-7.jpg\",\"vivo-x200-pro-8.jpg\"]', 1),
(20, 'Oppo Find X9', 'Oppo', 'mobile', 4499.00, NULL, 'Released 2025, October 22 203g, 8mm thickness Android 16, up to 5 major upgrades 256GB/512GB/1TB storage, no card slot', 'Premium phone with Hasselblad camera system.', '{\"display_size\":\"6.59\",\"display_resolution\":\"1256 x 2760 pixels\",\"display_technology\":\"AMOLED\",\"rear_camera\":\"50.0 MP + 50.0 MP + 50.0 MP\",\"front_camera\":\"32.0 MP\",\"rear_camera_fnumber\":\"F1.6 , F2.6 , F2.0\",\"video_resolution\":\"4K@30\\/60\\/120fps, 1080p@30\\/60\\/240fps\",\"chipset\":\"MediaTek Dimensity 9500\",\"battery_capacity\":\"7025\",\"cpu_speed\":\"4.21GHz, 3.35GHz, 2.7GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"16\",\"storage_gb\":\"512\",\"sim_count\":\"Dual-SIM\",\"os\":\"ColorOS 16\"}', '69cc1970b2422.png', 25, 'active', '2026-03-10 07:17:45', '2026-04-02 17:00:06', '[{\"name\":\"Velvet Red\",\"hex\":\"#ff3b30\",\"image\":\"69cc1970b5f25.jpg\",\"storage\":[{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,699\",\"sale\":\"RM 3,699\"}]},{\"name\":\"Gray\",\"hex\":\"#7D7C78\",\"image\":\"69cc1970b6092.jpg\",\"storage\":[{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,699\",\"sale\":\"RM 3,699\"}]}]', '[{\"label\":\"16GB + 256GB\",\"price\":4299,\"sale\":0},{\"label\":\"16GB + 512GB\",\"price\":4799,\"sale\":0}]', '[\"69cc1970b2b3b.jpg\",\"69cc1970b2cc9.jpg\",\"69cc1970b2e9d.jpg\",\"69cc1970b2ff9.png\",\"69cc1970b5d84.png\"]', 1),
(21, 'Galaxy S25 Ultra', 'Samsung', 'mobile', 5299.00, 4799.00, '200MP | 12GB RAM | S Pen', 'The Galaxy S25 Ultra features a 200MP camera, S Pen, 12GB RAM, and premium titanium design.', '{\"display_size\":\"174.2mm (6.9\\\" full rectangle) \\/ 172.2mm (6.8\\\" rounded corners)\",\"display_resolution\":\"3120 x 1440 (Quad HD+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"200.0 MP + 50.0 MP + 50.0 MP + 10.0 MP\",\"rear_camera_fnumber\":\"F1.7 , F3.4 , F1.9 , F2.4\",\"video_resolution\":\"UHD 8K (7680 x 4320)@30fps\",\"battery_capacity\":\"5000\",\"cpu_speed\":\"4.47GHz, 3.5GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256 \\/ 512 \\/ 1024\",\"dimensions\":\"162.8 x 77.6 x 8.2\",\"weight\":\"218\",\"sim_count\":\"Dual-SIM\"}', 'galaxy-s25-ultra-1.jpg', 49, 'active', '2026-03-16 07:23:16', '2026-04-02 17:01:42', '[{\"name\":\"Titanium Black\",\"hex\":\"#3c3d3a\",\"image\":\"galaxy-s25-highlights-color-Titanium-Black-back-mo.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,499\",\"sale\":\"RM 4,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,099\",\"sale\":\"RM 5,699\"},{\"label\":\"12GB + 1TB\",\"price\":\"RM 7,299\",\"sale\":\"RM 6,999\"}]},{\"name\":\"Titanium Gray\",\"hex\":\"#7d7c78\",\"image\":\"galaxy-s25-highlights-color-Titanium-Gray-back-mo.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,499\",\"sale\":\"RM 4,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,099\",\"sale\":\"RM 5,699\"},{\"label\":\"12GB + 1TB\",\"price\":\"RM 7,299\",\"sale\":\"RM 6,999\"}]},{\"name\":\"Titanium Silverblue\",\"hex\":\"#8999ad\",\"image\":\"galaxy-s25-highlights-color-Titanium-Silverblue-back-mo.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,499\",\"sale\":\"RM 4,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,099\",\"sale\":\"RM 5,699\"},{\"label\":\"12GB + 1TB\",\"price\":\"RM 7,299\",\"sale\":\"RM 6,999\"}]},{\"name\":\"Titanium Whitesilver\",\"hex\":\"#e5e4e2\",\"image\":\"galaxy-s25-highlights-color-Titanium-WhiteSilver-back-mo.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,499\",\"sale\":\"RM 4,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,099\",\"sale\":\"RM  5,699\"},{\"label\":\"12GB + 1TB\",\"price\":\"RM 7,299\",\"sale\":\"RM  6,999\"}]}]', NULL, '[\"galaxy-s25-ultra-1.jpg\",\"galaxy-s25-ultra-2.jpg\",\"galaxy-s25-ultra-3.jpg\",\"galaxy-s25-ultra-4.jpg\",\"galaxy-s25-ultra-5.jpg\",\"galaxy-s25-ultra-6.jpg\",\"galaxy-s25-highlights-color-Titanium-Black-back-mo.jpg\",\"galaxy-s25-highlights-color-Titanium-Gray-back-mo.jpg\",\"galaxy-s25-highlights-color-Titanium-Silverblue-back-mo.jpg\",\"galaxy-s25-highlights-color-Titanium-WhiteSilver-back-mo.jpg\"]', 3),
(22, 'Galaxy S25+', 'Samsung', 'mobile', 4999.00, 4799.00, '50MP | 12GB RAM | Dynamic AMOLED', 'Samsung Galaxy S25+ with 50MP camera and Dynamic AMOLED display.', '{\"display_size\":\"171.1mm (6.7\",\"display_resolution\":\"1080 x 2340 (FHD+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"50.0 MP + 12.0 MP + 8.0 MP\",\"rear_camera_fnumber\":\"F1.8 , F2.2 , F2.4\",\"video_resolution\":\"UHD 8K (7680 x 4320)@30fps\",\"battery_capacity\":\"4900\",\"cpu_speed\":\"3.2GHz , 2.9GHz, 2.6GHz, 1.95GHz\",\"cpu_type\":\"Deca-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256 \\/ 512\",\"dimensions\":\"161.3 x 76.6 x 7.4\",\"weight\":\"190\",\"sim_count\":\"Dual-SIM\"}', '69b821b1354c1.webp', 30, 'active', '2026-03-16 12:35:20', '2026-04-02 17:01:42', '[{\"name\":\"Navy \\/ Dark Blue\",\"hex\":\"#1b2a4a\",\"image\":\"69b814d18e70b.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,799\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,599\",\"sale\":\"RM 5,299\"}]},{\"name\":\"Icyblue\",\"hex\":\"#87ceeb\",\"image\":\"69b814d18f1b8.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,799\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,599\",\"sale\":\"RM 5,299\"}]},{\"name\":\"Silver Shadow\",\"hex\":\"#e5e4e2\",\"image\":\"69b814d18f557.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,799\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,599\",\"sale\":\"RM 5,299\"}]},{\"name\":\"Mint\",\"hex\":\"#98ff98\",\"image\":\"69b814d18f81f.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,599\",\"sale\":\"RM 4,799\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,599\",\"sale\":\"RM 5,299\"}]}]', NULL, '[\"69b81d5c30ffb.jpg\",\"69b81d5c314c7.webp\",\"69b81d5c31996.webp\",\"69b81d5c32095.webp\",\"69b81d5c32486.webp\"]', 4),
(27, 'Vivo X200', 'Vivo', 'mobile', 2799.00, NULL, '256GB | 12GB RAM | 50MP', 'Vivo X200 with 50MP camera.', '{\"display_size\":\"6.67 inches, 107.4 cm2 (~89.6% screen-to-body ratio)\",\"display_resolution\":\"1260 x 2800 pixels, 20:9 ratio (~460 ppi density)\",\"display_technology\":\"AMOLED, 1B colors, 120Hz, 2160Hz PWM, HDR10+, 4500 nits (peak)\",\"rear_camera\":\"50.0 MP + 50.0 MP + 50.0 MP\",\"front_camera\":\"32.0 MP\",\"rear_camera_fnumber\":\"F1.6 , F2.6 , F2.0\",\"video_resolution\":\"4K@30\\/60fps, 1080p@30\\/60\\/120\\/240fps, gyro-EIS\",\"chipset\":\"MediaTek Dimensity 9400+\",\"battery_capacity\":\"5800\",\"cpu_speed\":\"3.63GHz , 3.3GHz, 2.4GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12 \\/ 16\",\"storage_gb\":\"256 \\/ 512 \\/ 1024\",\"dimensions\":\"160.3 x 74.8 x 8 mm (6.31 x 2.94 x 0.31 in)\",\"weight\":\"202\",\"sim_count\":\"Dual-SIM\",\"os\":\"Funtouch OS 15\"}', '69bbe3ea0beac.webp', 25, 'active', '2026-03-16 12:35:20', '2026-04-02 17:02:11', '[{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69bbe3ea0ce40.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 2,339\",\"sale\":\"RM 2,339\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 2,549\",\"sale\":\"RM 2,549\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 2,949\",\"sale\":\"RM 2,749\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 3,329\",\"sale\":\"RM 3,129\"}]},{\"name\":\"Aurora Green\",\"hex\":\"#5a7a5a\",\"image\":\"69bbe3ea0dae2.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 2,339\",\"sale\":\"RM 2,339\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 2,549\",\"sale\":\"RM 2,549\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 2,949\",\"sale\":\"RM 2,749\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 3,329\",\"sale\":\"RM 3,129\"}]}]', NULL, '[]', 2),
(28, 'Vivo V60 Pro', 'Vivo', 'mobile', 2299.00, 1999.00, '256GB | 12GB RAM | Aura', 'Vivo V40 Pro with Aura camera.', '{\"display_size\":\"17.20 cm (6.77\\u2033)\",\"display_resolution\":\"2392 \\u00d7 1080\",\"display_technology\":\"AMOLED\",\"rear_camera\":\"50.0 MP + 50.0 MP + 8.0 MP\",\"front_camera\":\"50\",\"rear_camera_fnumber\":\"F1.9 , F2.7 , F2.0\",\"video_resolution\":\"4K@30fps, 1080p@30\\/60fps\",\"chipset\":\"Snapdragon 7 Gen 4\",\"battery_capacity\":\"6500\",\"cpu_speed\":\"(1x2.8 GHz Cortex-720 & 4x2.4 GHz Cortex-720 & 3x1.8 GHz Cortex-520)\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"8 \\/ 12 \\/ 16\",\"storage_gb\":\"128 \\/ 256 \\/ 512\",\"dimensions\":\"163.5 x 77 x 7.5 mm or 7.8 mm\",\"weight\":\"201\",\"sim_count\":\"Dual-SIM\",\"os\":\"Funtouch OS 15\"}', '69ce37e5199b3.webp', 20, 'active', '2026-03-16 12:35:20', '2026-04-02 17:02:11', '[{\"name\":\"Berry Purple\",\"hex\":\"#f5c6d0\",\"image\":\"69ce37e51ac8a.webp\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 1,849\",\"sale\":\"RM 1,549\"}]},{\"name\":\"Mist Grey\",\"hex\":\"#6e6e6e\",\"image\":\"69ce37e51b0d3.webp\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 1,849\",\"sale\":\"RM 1,549\"}]}]', NULL, '[\"69ce37e519d6c.webp\",\"69ce37e51a058.webp\",\"69ce37e51a405.webp\",\"69ce37e51a7fd.webp\"]', 3),
(30, 'Vivo Y21', 'Vivo', 'mobile', 599.00, NULL, '128GB | 8GB RAM | 64MP', 'Vivo Y11d budget smartphone.', '{\"display_size\":\"109.7\",\"display_resolution\":\"1600 x 720\",\"display_technology\":\"LCD\",\"rear_camera\":\"50 MP\",\"front_camera\":\"5 MP\",\"rear_camera_fnumber\":\"F1.8\",\"video_resolution\":\"1080p@30fps\",\"chipset\":\"Mediatek Dimensity 6300\",\"battery_capacity\":\"6500\",\"cpu_speed\":\"2.4GHz , 2.0GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"8\",\"storage_gb\":\"128\",\"sim_count\":\"Dual-SIM\",\"os\":\"OriginOs 6\"}', '69ce3e41db373.jpg', 50, 'active', '2026-03-16 12:35:20', '2026-04-02 10:00:33', '[{\"name\":\"Crystal Black\",\"hex\":\"#1a1a1a\",\"image\":\"69ce3e41dcacb.webp\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"\",\"sale\":\"\"}]},{\"name\":\"Light Gold\",\"hex\":\"#e8c87a\",\"image\":\"69ce3e41dcfd3.png\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"\",\"sale\":\"\"}]}]', NULL, '[\"69ce3e41db774.jpg\",\"69ce3e41dbb2e.jpg\",\"69ce3e41dbe4e.jpg\",\"69ce3e41dc130.webp\",\"69ce3e41dc5e1.png\"]', 4),
(33, 'Vivo Buds Air 3', 'Vivo', 'audio', 399.00, NULL, 'ANC | Hi-Res | 30hr', 'Vivo TWS 4 wireless earbuds.', '{\"audio_driver_size\":\"12mm\",\"audio_type\":\"In-Ear\",\"audio_frequency_response\":\"-\",\"audio_impedance\":\"-\",\"audio_sensitivity\":\"-\",\"audio_anc\":\"Yes (Adaptive ANC)\",\"audio_battery_life\":\"10Hours(ANC OFF)\",\"audio_charging_time\":\"10 minutes = 3 hours of listening time\",\"audio_bluetooth\":\"Bluetooth 6.0\",\"audio_codec\":\"-\",\"audio_water_resistance\":\"IP54\",\"audio_microphone\":\"-\",\"audio_noise_cancelling\":\"-\",\"audio_controls\":\"Touch Sensor\",\"audio_cable_length\":\"-\"}', '69ce436bdf815.jpg', 60, 'active', '2026-03-16 12:35:20', '2026-04-02 17:02:11', '[{\"name\":\"Moonlight White\",\"hex\":\"#f0f0f0\",\"image\":\"69ce436be780a.jpg\",\"storage\":[{\"label\":\"Standard\",\"price\":\"RM 399\",\"sale\":\"\"}]},{\"name\":\"Starry Black\",\"hex\":\"#1a1a1a\",\"image\":\"69ce436be7b81.jpg\",\"storage\":[{\"label\":\"Standard\",\"price\":\"RM 399\",\"sale\":\"\"}]}]', NULL, '[\"69ce436bdfbee.jpg\",\"69ce436bdff2e.jpg\",\"69ce436be02d9.jpg\",\"69ce436be06e8.jpg\",\"69ce436be569a.jpg\",\"69ce436be5969.jpg\",\"69ce436be5d29.jpg\",\"69ce436be60f3.jpg\",\"69ce436be63fc.jpg\",\"69ce436be6bb5.jpg\",\"69ce436be6e75.jpg\",\"69ce436be70be.jpg\",\"69ce436be73b5.jpg\"]', 5),
(34, 'Galaxy S25', 'Samsung', 'mobile', 3999.00, 3799.00, '50MP | 12GB RAM | Dynamic LTPO AMOLED 2X', 'Samsung Galaxy S25+ with 50MP camera and Dynamic AMOLED display.', '{\"display_size\":\"171.1mm (6.7\",\"display_resolution\":\"1080 x 2340 (FHD+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"50.0 MP + 12.0 MP + 8.0 MP\",\"front_camera\":\"12.0MP\",\"rear_camera_fnumber\":\"F1.8 , F2.2 , F2.4\",\"video_resolution\":\"UHD 8K (7680 x 4320)@30fps\",\"battery_capacity\":\"4900\",\"cpu_speed\":\"3.2GHz , 2.9GHz, 2.6GHz, 1.95GHz\",\"cpu_type\":\"Deca-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256 \\/ 512\",\"dimensions\":\"161.3 x 76.6 x 7.4\",\"weight\":\"190\",\"sim_count\":\"Dual-SIM\"}', '69b83545eeb00.webp', 30, 'active', '2026-03-16 15:11:19', '2026-04-02 17:01:42', '[{\"name\":\"Navy \\/ Dark Blue\",\"hex\":\"#1b2a4a\",\"image\":\"69b820b8c150a.png\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,999\",\"sale\":\"RM 3,799\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 4,599\",\"sale\":\"RM 4,299\"}]},{\"name\":\"Icyblue\",\"hex\":\"#87ceeb\",\"image\":\"69b820b8c1e18.png\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,999\",\"sale\":\"RM 3,799\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 4,599\",\"sale\":\"RM 4,299\"}]},{\"name\":\"Silver Shadow\",\"hex\":\"#e5e4e2\",\"image\":\"69b820b8c2229.png\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,999\",\"sale\":\"RM 3,799\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 4,599\",\"sale\":\"RM 4,299\"}]},{\"name\":\"Mint\",\"hex\":\"#98ff98\",\"image\":\"69b820b8c2840.png\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,999\",\"sale\":\"RM 3,799\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 4,599\",\"sale\":\"RM 4,299\"}]}]', NULL, '[\"69b821354925c.webp\",\"69b82135495fd.webp\",\"69b821354995e.webp\",\"69b8213549c84.webp\",\"69b821354a593.webp\"]', 5),
(35, 'Galaxy S25 FE', 'Samsung', 'mobile', 2799.00, 2699.00, '50MP | 12GB RAM | Dynamic LTPO AMOLED 2X', 'Samsung Galaxy S25+ with 50MP camera and Dynamic AMOLED display.', '{\"display_size\":\"171.1mm (6.7\",\"display_resolution\":\"1080 x 2340 (FHD+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"50.0 MP + 12.0 MP + 8.0 MP\",\"front_camera\":\"12.0MP\",\"rear_camera_fnumber\":\"F1.8 , F2.2 , F2.4\",\"video_resolution\":\"UHD 8K (7680 x 4320)@30fps\",\"chipset\":\"Samsung Exynos 2400\",\"battery_capacity\":\"4900\",\"cpu_speed\":\"3.2GHz , 2.9GHz, 2.6GHz, 1.95GHz\",\"cpu_type\":\"Deca-Core\",\"memory_gb\":\"8\",\"storage_gb\":\"128 \\/ 256 \\/ 512\",\"dimensions\":\"161.3 x 76.6 x 7.4\",\"weight\":\"190\",\"sim_count\":\"Dual-SIM\"}', '69b83d5ff2832.jpg', 30, 'active', '2026-03-16 16:26:37', '2026-04-02 17:01:42', '[{\"name\":\"Navy \\/ Dark Blue\",\"hex\":\"#1b2a4a\",\"image\":\"69b8346082361.webp\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"RM 2,399\",\"sale\":\"RM 2,399\"},{\"label\":\"8GB + 256GB\",\"price\":\"RM 2,799\",\"sale\":\"RM 2,799\"},{\"label\":\"8GB + 512GB\",\"price\":\"RM 3,099\",\"sale\":\"RM 2,899\"}]},{\"name\":\"Icyblue\",\"hex\":\"#87ceeb\",\"image\":\"69b8346082655.webp\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"RM 2,399\",\"sale\":\"RM 2,399\"},{\"label\":\"8GB + 256GB\",\"price\":\"RM 2,799\",\"sale\":\"RM 2,799\"},{\"label\":\"8GB + 512GB\",\"price\":\"RM 3,099\",\"sale\":\"RM 2,899\"}]},{\"name\":\"Jetblack\",\"hex\":\"#1a1a1a\",\"image\":\"69b834608296e.webp\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"RM 2,399\",\"sale\":\"RM 2,399\"},{\"label\":\"8GB + 256GB\",\"price\":\"RM 2,799\",\"sale\":\"RM 2,799\"},{\"label\":\"8GB + 512GB\",\"price\":\"RM 3,099\",\"sale\":\"RM 2,899\"}]},{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"69b8346082e25.webp\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"RM 2,399\",\"sale\":\"RM 2,399\"},{\"label\":\"8GB + 256GB\",\"price\":\"RM 2,799\",\"sale\":\"RM 2,799\"},{\"label\":\"8GB + 512GB\",\"price\":\"RM 3,099\",\"sale\":\"RM 2,899\"}]}]', NULL, '[\"69b8346080c8f.webp\",\"69b8346081229.webp\",\"69b8346081a59.webp\",\"69b834608204e.webp\"]', 6),
(36, 'Galaxy S26 Ultra', 'Samsung', 'mobile', 2799.00, 2699.00, '50MP | 12GB RAM | Dynamic AMOLED 2X', 'Samsung Galaxy S25+ with 50MP camera and Dynamic AMOLED display.', '{\"display_size\":\"174.9mm (6.9\",\"display_resolution\":\"3120 x 1440 (Quad HD+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"200.0 MP + 50.0 MP + 50.0 MP + 10.0 MP\",\"front_camera\":\"12.0MP\",\"rear_camera_fnumber\":\"F1.4 , F2.9 , F1.9 , F2.4\",\"video_resolution\":\"UHD 8K (7680 x 4320)@30fps\",\"chipset\":\"Snapdragon 8 Elite Gen 5\",\"battery_capacity\":\"5000\",\"cpu_speed\":\"4.74GHz, 3.6GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12 \\/ 16\",\"storage_gb\":\"256 \\/ 512 \\/ 1024\",\"dimensions\":\"163.6 x 78.1 x 7.9\",\"weight\":\"214\",\"sim_count\":\"Dual-SIM\"}', '69b8d961be454.jpg', 29, 'active', '2026-03-16 16:55:42', '2026-03-23 14:18:52', '[{\"name\":\"Pink gold\",\"hex\":\"#f5c6d0\",\"image\":\"69b83cbb9257a.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"RM 7,299\"}]},{\"name\":\"Silver shadow\",\"hex\":\"#e5e4e2\",\"image\":\"69b83cbb92871.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"RM 7,299\"}]},{\"name\":\"Cobalt Violet\",\"hex\":\"#6a5acd\",\"image\":\"69b83cbb92b40.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,99\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"RM 7,299\"}]},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69b83cbb92e5d.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16 + 1024GB\",\"price\":\"RM7,999\",\"sale\":\"RM 7,299\"}]},{\"name\":\"Sky Blue\",\"hex\":\"#87ceeb\",\"image\":\"69b83cbb932a6.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"RM 7,299\"}]},{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"RM 7,299\"}]}]', NULL, '[\"69b83cbb8f776.jpg\",\"69b83cbb8fb24.jpg\",\"69b83cbb8fe9b.webp\",\"69b83cbb90287.webp\",\"69b83cbb904e4.webp\",\"69b83cbb907e1.webp\",\"69b83cbb90b5a.webp\",\"69b83cbb90ef5.webp\",\"69b83cbb9130e.jpg\",\"69b83cbb916bb.jpg\",\"69b83cbb91a6b.jpg\",\"69b83cbb91cd2.jpg\",\"69b83cbb91fa0.jpg\",\"69b83cbb92322.jpg\"]', 0),
(37, 'Galaxy S26+', 'Samsung', 'mobile', 2799.00, 2699.00, '50MP | 12GB RAM | Dynamic AMOLED 2X', 'Samsung Galaxy S26+ with 50MP camera and Dynamic AMOLED display. The chipset is Snapdragon 8 Elite Gen 5.', '{\"display_size\":\"159.3mm (6.3\",\"display_resolution\":\"2340 x 1080 (FHD+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"50.0 MP + 10.0 MP + 12.0 MP\",\"front_camera\":\"12.0MP\",\"rear_camera_fnumber\":\"F1.8 , F2.4 , F2.2\",\"video_resolution\":\"UHD 8K (7680 x 4320)@30fps\",\"chipset\":\"Snapdragon 8 Elite Gen 5\",\"battery_capacity\":\"4300\",\"cpu_speed\":\"3.8GHz, 3.26GHz, 2.76GHz\",\"cpu_type\":\"Deca-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256 \\/ 512\",\"dimensions\":\"149.6 x 71.7 x 7.2\",\"weight\":\"167\",\"sim_count\":\"Dual-SIM\"}', '69b8dbcc5ad6c.webp', 30, 'active', '2026-03-16 17:30:51', '2026-03-23 14:18:52', '[{\"name\":\"Pink gold\",\"hex\":\"#f5c6d0\",\"image\":\"69b8db5d686bf.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"RM 7,299\"}]},{\"name\":\"Silver shadow\",\"hex\":\"#e5e4e2\",\"image\":\"69b8db5d68a83.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"RM 7,299\"}]},{\"name\":\"Cobalt Violet\",\"hex\":\"#6a5acd\",\"image\":\"69b8db5d68cc8.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,99\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"RM 7,299\"}]},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69b8db5d68f7e.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16 + 1024GB\",\"price\":\"RM7,999\",\"sale\":\"RM 7,299\"}]},{\"name\":\"Sky Blue\",\"hex\":\"#87ceeb\",\"image\":\"69b8db5d69193.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"RM 7,299\"}]},{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"69b8423abdd05.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,799\",\"sale\":\"RM 6,799\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"RM 7,299\"}]}]', NULL, '[\"69b8dbcc5b708.webp\",\"69b8dbcc5b96e.webp\",\"69b8dbcc5bca8.webp\",\"69b8dbcc5bfd7.webp\",\"69b8dbcc5c645.webp\",\"69b8dbcc5c908.webp\",\"69b8dbcc5cba0.jpg\",\"69b8dbcc5cdd6.jpg\",\"69b8dbcc5cfe4.webp\",\"69b8dbcc5d262.webp\",\"69b8dbcc5d44a.webp\"]', 1),
(38, 'Galaxy S26', 'Samsung', 'mobile', 2799.00, 2699.00, '50MP | 12GB RAM | Dynamic AMOLED 2X', 'Samsung Galaxy S26+ with 50MP camera and Dynamic AMOLED display. The chipset is Snapdragon 8 Elite Gen 5.', '{\"display_size\":\"159.3mm (6.3\\\" full rectangle) \\/ 155.9mm (6.1\\\" rounded corners)\",\"display_resolution\":\"2340 x 1080 (FHD+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"50.0 MP + 10.0 MP + 12.0 MP\",\"front_camera\":\"12.0MP\",\"rear_camera_fnumber\":\"F1.8 , F2.4 , F2.2\",\"video_resolution\":\"UHD 8K (7680 x 4320)@30fps\",\"chipset\":\"Snapdragon 8 Elite Gen 5\",\"battery_capacity\":\"4300\",\"cpu_speed\":\"3.8GHz, 3.26GHz, 2.76GHz\",\"cpu_type\":\"Deca-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256 \\/ 512\",\"dimensions\":\"149.6 x 71.7 x 7.2\",\"weight\":\"167\",\"sim_count\":\"Dual-SIM\"}', '69b8e00b22e87.webp', 30, 'active', '2026-03-17 04:43:14', '2026-04-02 17:01:42', '[{\"name\":\"Pink gold\",\"hex\":\"#f5c6d0\",\"image\":\"69b8e00b23ab9.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,399\",\"sale\":\"RM 4,199\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,199\",\"sale\":\"RM 5,099\"}]},{\"name\":\"Silver shadow\",\"hex\":\"#e5e4e2\",\"image\":\"69b8e00b23d36.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,399\",\"sale\":\"RM 4,199\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,199\",\"sale\":\"RM 5,099\"}]},{\"name\":\"Cobalt Violet\",\"hex\":\"#6a5acd\",\"image\":\"69b8e00b240e1.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,199\",\"sale\":\"RM 4,099\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,199\",\"sale\":\"RM 5,099\"}]},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69b8e00b24490.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,399\",\"sale\":\"RM 4,299\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,199\",\"sale\":\"RM 5,099\"}]},{\"name\":\"Sky Blue\",\"hex\":\"#87ceeb\",\"image\":\"69b8e00b2477e.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,399\",\"sale\":\"RM 4,299\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,199\",\"sale\":\"RM 5,099\"}]},{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"69b8e00b24a43.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,399\",\"sale\":\"RM 4,299\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,199\",\"sale\":\"RM 5,099\"}]}]', NULL, '[]', 2),
(39, 'Galaxy Tab S11 (5G)', 'Samsung', 'tablet', 5499.00, NULL, '256GB | 12GB RAM | 14.6&amp;amp;amp;quot;', 'Premium Android tablet with AMOLED display.', '{\"display_size\":\"11.0 (278.1mm) (11.0\\\" full rectangle) \\/ 276.2mm (10.9\\\" rounded corners)\",\"display_resolution\":\"2560 x 1600 (WQXGA+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"13.0 MP\",\"front_camera\":\"12.0MP\",\"rear_camera_fnumber\":\"-\",\"video_resolution\":\"UHD 4K (3840 x 2160)@30fps\",\"chipset\":\"MediaTek Dimensity 9400+\",\"battery_capacity\":\"8400\",\"cpu_speed\":\"3.73GHz, 3.3GHz, 2.4GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"165.8 x 253.8 x 5.5\",\"weight\":\"471\",\"sim_count\":\"Dual-SIM\",\"os\":\"Android\",\"connectivity\":\"Wi-Fi + 5G\"}', '69b9845d71a6e.jpg', 25, 'active', '2026-03-17 16:42:05', '2026-04-02 17:01:42', '[{\"name\":\"Gray\",\"hex\":\"#383838\",\"image\":\"69b98434d812a.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,099\",\"sale\":\"RM 3,899\"}]},{\"name\":\"Silver\",\"hex\":\"#e5e4e2\",\"image\":\"69b98434d8445.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,099\",\"sale\":\"RM 3,899\"}]}]', NULL, '[\"69b9845d720bf.jpg\",\"69b9845d72740.jpg\",\"69b9845d730cb.webp\",\"69b9845d7368d.webp\"]', 10),
(40, 'Galaxy Tab S11 Ultra (WI-FI Only)', 'Samsung', 'tablet', 5499.00, NULL, '256GB | 12GB RAM | 14.6&amp;amp;amp;quot;', 'Premium Android tablet with LED display.', '{\"display_size\":\"14.6\",\"display_resolution\":\"2960 x 1848 (WQXGA+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"13.0 MP + 8.0 MP\",\"front_camera\":\"12.0MP\",\"rear_camera_fnumber\":\"-\",\"video_resolution\":\"UHD 4K (3840 x 2160)@30fps\",\"chipset\":\"MediaTek Dimensity 9400+\",\"battery_capacity\":\"11600\",\"cpu_speed\":\"3.73GHz, 3.3GHz, 2.4GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256 \\/ 512\",\"dimensions\":\"165.8 x 254.3 x 6.0\",\"weight\":\"500\",\"sim_count\":\"Dual-SIM\",\"os\":\"Android\",\"connectivity\":\"Wi-Fi Only\"}', '69b985a2577c3.jpg', 25, 'active', '2026-03-17 16:47:30', '2026-04-02 17:01:42', '[{\"name\":\"Gray\",\"hex\":\"#383838\",\"image\":\"69b98434d812a.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,899\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,499\",\"sale\":\"RM 6,299\"}]},{\"name\":\"Silver\",\"hex\":\"#e5e4e2\",\"image\":\"69b98434d8445.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 2,199\",\"sale\":\"RM 2,099\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,499\",\"sale\":\"RM 6,299\"}]}]', NULL, '[\"69b985a2583fe.jpg\",\"69b985a258b76.jpg\",\"69b985a259382.webp\",\"69b985a259cb9.webp\"]', 9),
(41, 'Galaxy Tab S11 (WI-FI Only)', 'Samsung', 'tablet', 5499.00, NULL, '256GB | 12GB RAM | 14.6&amp;amp;amp;amp;quot;', 'Premium Android tablet with AMOLED display.', '{\"display_size\":\"11.0 (278.1mm) (11.0\",\"display_resolution\":\"2560 x 1600 (WQXGA+)\",\"display_technology\":\"Dynamic AMOLED 2X\",\"rear_camera\":\"13.0 MP\",\"front_camera\":\"12.0MP\",\"rear_camera_fnumber\":\"-\",\"video_resolution\":\"UHD 4K (3840 x 2160)@30fps\",\"chipset\":\"MediaTek Dimensity 9400+\",\"battery_capacity\":\"8400\",\"cpu_speed\":\"3.73GHz, 3.3GHz, 2.4GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"165.8 x 253.8 x 5.5\",\"weight\":\"471\",\"sim_count\":\"Dual-SIM\",\"os\":\"Android\",\"connectivity\":\"Wi-Fi + 5G\"}', '69b98914d6926.jpg', 24, 'active', '2026-03-17 17:02:12', '2026-04-02 17:01:42', '[{\"name\":\"Gray\",\"hex\":\"#383838\",\"image\":\"69b98434d812a.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,099\",\"sale\":\"RM 3,899\"}]},{\"name\":\"Silver\",\"hex\":\"#e5e4e2\",\"image\":\"69b98434d8445.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,099\",\"sale\":\"RM 3,899\"}]}]', NULL, '[\"69b98914d7217.jpg\",\"69b98914d7acd.jpg\",\"69b98914d8454.webp\",\"69b98914d89d9.webp\"]', 11),
(44, 'Nothing Phone (3)', 'Nothing', 'mobile', 1699.00, NULL, '256GB | 12GB RAM | Glyph', 'Unique transparent design with Glyph interface.', '{\"display_size\":\"6.67\",\"display_resolution\":\"1260 x 2800 (460 PPI)\",\"display_technology\":\"FLEXIBLE AMOLED\",\"rear_camera\":\"50.0 MP + 50.0 MP + 50.0 MP\",\"front_camera\":\"50MP\",\"rear_camera_fnumber\":\"F1.68 , F2.68 , F2.2\",\"video_resolution\":\"4K@30fps, 1080p@30fps\",\"chipset\":\"Snapdragon 8s Gen 4\",\"battery_capacity\":\"5500\",\"cpu_speed\":\"3.21GHz, 3.2GHz, 3.0GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"160.60 x 75.59 x 8.99\",\"weight\":\"218\",\"sim_count\":\"Dual-SIM\",\"os\":\"Nothing OS 3.1 Powered by Android 15\"}', '69c15faa0f720.webp', 45, 'active', '2026-03-23 14:32:32', '2026-04-14 01:19:13', '[{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"69c15faa114b4.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,200\",\"sale\":\"RM 2,959\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,700\",\"sale\":\"RM 3,459\"}]},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69c15faa117e4.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,200\",\"sale\":\"RM 2,959\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,700\",\"sale\":\"RM 3,459\"}]}]', NULL, '[\"69c15faa10321.webp\",\"69c15faa104fa.webp\",\"69c15faa1070e.webp\",\"69c15faa109de.webp\",\"69c15faa10c4d.webp\",\"69c15faa10ed4.webp\",\"69c15faa1117e.webp\"]', 4),
(45, 'Nothing Phone (3a) Lite', 'Nothing', 'mobile', 1099.00, NULL, '256GB | 8GB RAM | Glyph', 'Unique transparent design with Glyph interface.', '{\"display_size\":\"6.77\",\"display_resolution\":\"1084 x 2392 (387 PPI)\",\"display_technology\":\"FLEXIBLE AMOLED\",\"rear_camera\":\"50.0 MP + 8.0 MP + 2.0 MP\",\"front_camera\":\"16MP\",\"rear_camera_fnumber\":\"F1.88 , F2.2 , F2.4\",\"video_resolution\":\"4K@30fps, 1080p@30fps\",\"chipset\":\"MediaTek Dimensity 7300 Pro 5G\",\"battery_capacity\":\"5000\",\"cpu_speed\":\"2.5 GHZ\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"8\",\"storage_gb\":\"256\",\"dimensions\":\"164.0 x 78 x 8.3\",\"weight\":\"119\",\"sim_count\":\"Dual-SIM\",\"os\":\"Nothing OS 3.1 Powered by Android 15\"}', '69c158cfb9c47.webp', 45, 'active', '2026-03-23 14:32:38', '2026-04-14 01:19:13', '[{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"69c158cfbabb2.webp\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 1,199\",\"sale\":\"RM 1,099\"}]},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69c158cfbaea9.webp\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 1,199\",\"sale\":\"RM 1,099\"}]},{\"name\":\"Limited Edition Blue\",\"hex\":\"#4169e1\",\"image\":\"69c158cfbb0dd.webp\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 1,199\",\"sale\":\"RM 1,099\"}]}]', NULL, '[\"69c157786ae37.webp\",\"69c157786b07e.webp\",\"69c157786b2d4.webp\",\"69c157786b5cb.webp\",\"69c157786b8a0.webp\",\"69c157786bc08.webp\",\"69c157786be97.webp\",\"69c157786c144.webp\"]', 3),
(46, 'Nothing Phone (3a) Pro', 'Nothing', 'mobile', 1699.00, NULL, '256GB | 12GB RAM | Glyph', 'Unique transparent design with Glyph interface.', '{\"display_size\":\"6.77\",\"display_resolution\":\"1084 x 2392 (387 PPI)\",\"display_technology\":\"FLEXIBLE AMOLED\",\"rear_camera\":\"50.0 MP + 50.0 MP + 8.0 MP\",\"front_camera\":\"50MP\",\"rear_camera_fnumber\":\"F1.88 , F2.55 , F2.2\",\"video_resolution\":\"4K@30fps, 1080p@30fps\",\"chipset\":\"Snapdragon 7s Gen 3 5G\",\"battery_capacity\":\"5000\",\"cpu_speed\":\"2.5 GHZ\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"163.5 x 77.5 x 8.35\",\"weight\":\"211\",\"sim_count\":\"Dual-SIM\",\"os\":\"Nothing OS 3.1 Powered by Android 15\"}', '69c1539dbcf49.webp', 45, 'active', '2026-03-23 14:32:52', '2026-04-02 16:59:14', '[{\"name\":\"Gray\",\"hex\":\"#7D7C78\",\"image\":\"69c1539dbf9f3.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,659\",\"sale\":\"RM 1,599\"}]},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69c1539dc3b54.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,659\",\"sale\":\"RM 1,599\"}]}]', NULL, '[\"69c1539dbe22e.webp\",\"69c1539dbe481.webp\",\"69c1539dbe6c6.webp\",\"69c1539dbe99d.webp\",\"69c1539dbec5d.webp\",\"69c1539dbef83.webp\",\"69c1539dbf249.webp\",\"69c1539dbf52a.webp\",\"69c1539dbf774.webp\"]', 0),
(47, 'Nothing Phone (3a)', 'Nothing', 'mobile', 1699.00, NULL, '256GB | 12GB RAM | Glyph', 'Unique transparent design with Glyph interface.', '{\"display_size\":\"6.77\",\"display_resolution\":\"1084 x 2392 (387 PPI)\",\"display_technology\":\"FLEXIBLE AMOLED\",\"rear_camera\":\"50.0 MP + 50.0 MP + 8.0 MP\",\"front_camera\":\"50MP\",\"rear_camera_fnumber\":\"F1.88 , F2.55 , F2.2\",\"video_resolution\":\"4K@30fps, 1080p@30fps\",\"chipset\":\"Snapdragon 7s Gen 3 5G\",\"battery_capacity\":\"5000\",\"cpu_speed\":\"2.5 GHZ\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"163.5 x 77.5 x 8.35\",\"weight\":\"211\",\"sim_count\":\"Dual-SIM\",\"os\":\"Nothing OS 3.1 Powered by Android 15\"}', '69c15da3789f0.webp', 45, 'active', '2026-03-23 15:30:19', '2026-04-14 01:19:13', '[{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"69c15da3793ba.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,699\",\"sale\":\"RM 1,359\"}]},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69c15da37959e.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,699\",\"sale\":\"RM 1,359\"}]},{\"name\":\"Blue\",\"hex\":\"#4169e1\",\"image\":\"69c15da3797f5.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,699\",\"sale\":\"RM 1,359\"}]}]', NULL, '[\"69c15c8b7db12.webp\",\"69c15c8b7e22c.webp\",\"69c15c8b7e8ed.webp\",\"69c15c8b7ee94.webp\",\"69c15c8b7f5a7.webp\",\"69c15c8b7fe0e.webp\",\"69c15c8b80509.webp\",\"69c15c8b80bb3.webp\"]', 2),
(48, 'Headphone(1)', 'Nothing', 'audio', 1099.00, 959.00, '-', 'Up to 80 hours of playback, Sound by KEF, Real-time adaptive ANC.', '{\"dimensions\":\"173.85 x 78.0 x 189.25\",\"weight\":\"329\",\"sim_count\":\"-\",\"audio_driver_size\":\"40MM\",\"audio_type\":\"On-Ear\",\"audio_frequency_response\":\"20HZ - 40KHZ\",\"audio_anc\":\"Yes (Adaptive ANC)\",\"audio_battery_life\":\"30 Hours (ANC On)\",\"audio_charging_time\":\"120 Mins\",\"audio_bluetooth\":\"5.3\",\"audio_codec\":\"AAC, SBC, LDAC\",\"audio_water_resistance\":\"-\",\"audio_microphone\":\"6 Mics total\",\"audio_noise_cancelling\":\"REAL-TIME ADAPTIVE ANC\",\"audio_controls\":\"Pairing , Roller , Paddle , Button , Led Light Status\",\"audio_cable_length\":\"-\"}', '69c2474f1af8e.webp', 49, 'active', '2026-03-23 15:59:14', '2026-04-02 16:59:14', '[{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69c2474f1cfc8.webp\",\"storage\":[]},{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"69c2474f1d2ea.webp\",\"storage\":[]}]', NULL, '[\"69c2474f1b231.webp\",\"69c2474f1b4c2.webp\",\"69c2474f1b7ca.webp\",\"69c2474f1bb70.webp\",\"69c2474f1be59.webp\",\"69c2474f1c0d5.webp\",\"69c2474f1c3d9.webp\",\"69c2474f1c5fb.webp\",\"69c2474f1c7e8.webp\",\"69c2474f1ca6b.jpg\",\"69c2474f1ccae.jpg\"]', 5);
INSERT INTO `products` (`id`, `name`, `brand`, `category`, `price`, `sale_price`, `specs`, `description`, `specifications`, `photo`, `stock`, `status`, `created_at`, `updated_at`, `colors`, `storage_variants`, `gallery`, `sort_order`) VALUES
(49, 'PlayStation 5 Pro', 'Sony', 'console', 2199.00, 1869.00, '2TB | Pro', 'Slimmer PS5 design with digital game library.  (Model CFI-7000)', '{\"dimensions\":\"388 x 89 x 216 mm\",\"weight\":\"3100\",\"console_cpu\":\"8-Core AMD Ryzen Zen 2 (16 threads); Base 3.5 GHz \\/\",\"console_gpu\":\"16.7 TFLOPS (AMD Radeon RDNA-based); 60 Compute Units (up from 36 on base PS5)\",\"console_memory\":\"16GB GDDR6 + 2GB DDR5\",\"console_storage_type\":\"2TB Custom NVMe SSD (5.5 GB\\/s raw read speed); includes M.2 expansion slot\",\"console_optical_drive\":\"4K @ 120Hz\",\"console_max_resolution\":\"8K @ 60Hz\",\"console_frame_rate\":\"120 FPS\",\"console_ray_tracing\":\"Yes\",\"console_hdr\":\"Supports HDR10 and Dynamic HDR\",\"console_audio_output\":\"Tempest 3D AudioTech engine\",\"console_usb_ports\":\"2x USB-C (SuperSpeed 10Gbps), 2x USB-A (SuperSpeed 10Gbps)\",\"console_hdmi\":\"HDMI 2.1 (48 Gbps)\",\"console_wifi\":\"Wi-Fi 7\",\"console_bluetooth\":\"Bluetooth 5.1\",\"console_power_consumption\":\"390W\"}', '69c2172a9420f.webp', 50, 'active', '2026-03-23 16:02:55', '2026-04-02 17:01:53', '[{\"name\":\"White\",\"hex\":\"#f5f5f5\",\"image\":\"69c2172a9c95a.webp\",\"storage\":[]}]', NULL, '[\"69c2172a9c4c6.webp\"]', 2),
(52, 'iPhone Air', 'Apple', 'mobile', 3799.00, 3499.00, '128GB | 8GB RAM | 48MP', 'Powerful iPhone with Camera Control button.', '{\"display_size\":\"6.5\",\"display_resolution\":\"1260 x 2736 pixels\",\"display_technology\":\"LTPO Super Retina XDR OLED\",\"rear_camera\":\"48.0 MP\",\"front_camera\":\"18 MP\",\"rear_camera_fnumber\":\"F1.6\",\"video_resolution\":\"4K@24\\/25\\/30\\/60fps, 1080p@25\\/30\\/60\\/120\\/240fps\",\"chipset\":\"Apple A19 Pro\",\"battery_capacity\":\"3149\",\"cpu_speed\":\"3.8GHz\",\"cpu_type\":\"Hexa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256 \\/ 512 \\/ 1024\",\"sim_count\":\"Dual-SIM\",\"os\":\"iOS 26\"}', '69cc17e26ca7a.webp', 80, 'active', '2026-03-24 13:15:17', '2026-04-14 01:18:30', '[{\"name\":\"Sky Blue\",\"hex\":\"#87ceeb\",\"image\":\"69cc17e26f261.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 1024GB\",\"price\":\"RM 6,999\",\"sale\":\"RM 6,999\"}]},{\"name\":\"Light Gold\",\"hex\":\"custom\",\"image\":\"69cc17e26f4b1.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 1024GB\",\"price\":\"RM 6,999\",\"sale\":\"RM 6,999\"}]},{\"name\":\"Cloud White\",\"hex\":\"#ffffff\",\"image\":\"69cc17e26f7a0.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 1024GB\",\"price\":\"RM 6,999\",\"sale\":\"RM 6,999\"}]},{\"name\":\"Space Black\",\"hex\":\"#1a1a1a\",\"image\":\"69cc17e26f993.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,999\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,999\",\"sale\":\"RM 5,999\"},{\"label\":\"12GB + 1024GB\",\"price\":\"RM 6,999\",\"sale\":\"RM 6,999\"}]}]', NULL, '[\"69cc17e26dd03.webp\",\"69cc17e26df1b.webp\",\"69cc17e26e1b7.webp\",\"69cc17e26e3b1.webp\",\"69cc17e26e5fa.webp\",\"69cc17e26e9dc.webp\",\"69cc17e26ecd7.webp\",\"69cc17e26efbe.webp\"]', 2),
(53, 'iPhone 17 Pro Max', 'Apple', 'mobile', 5499.00, NULL, '256GB | 12GB RAM | A18 Pro', 'Apple flagship with titanium design and Action button.', '{\"display_size\":\"6.9\",\"display_resolution\":\"1320 x 2868\",\"display_technology\":\"LTPO Super Retina XDR OLED\",\"rear_camera\":\"48.0 MP + 48.0 MP + 48.0 MP\",\"front_camera\":\"18.0 MP\",\"rear_camera_fnumber\":\"F1.8 , F2.8 , F2.2\",\"video_resolution\":\"4K@24\\/25\\/30\\/60\\/100\\/120fps, 1080p@25\\/30\\/60\\/120\\/240fps\",\"chipset\":\"Apple A19 Pro (3 nm)\",\"battery_capacity\":\"3998\",\"cpu_speed\":\"2x4.26 GHz + 4x2.60 GHz\",\"cpu_type\":\"Hexa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"256 \\/ 512 \\/ 1024 \\/ 2048\",\"sim_count\":\"Dual-SIM\",\"os\":\"iOS 26\"}', '69c29796d9dbc.webp', 60, 'active', '2026-03-24 13:54:30', '2026-03-24 14:01:41', '[{\"name\":\"Silver\",\"hex\":\"#e5e4e2\",\"image\":\"69c29945d3749.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,999\",\"sale\":\"\"},{\"label\":\"12GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"\"},{\"label\":\"12GB + 2048GB\",\"price\":\"RM 9,999\",\"sale\":\"\"}]},{\"name\":\"Cosmic Orange\",\"hex\":\"#ff6700\",\"image\":\"69c29945d3dac.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,999\",\"sale\":\"\"},{\"label\":\"12GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"\"},{\"label\":\"12GB + 2048GB\",\"price\":\"RM 9,999\",\"sale\":\"\"}]},{\"name\":\"Deep Blue\",\"hex\":\"#4169e1\",\"image\":\"69c29945d4108.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,999\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 6,999\",\"sale\":\"\"},{\"label\":\"12GB + 1024GB\",\"price\":\"RM 7,999\",\"sale\":\"\"},{\"label\":\"12GB + 2048GB\",\"price\":\"RM 9,999\",\"sale\":\"\"}]}]', NULL, '[\"69c29796da6b8.webp\",\"69c29796dae66.webp\",\"69c29796db5d7.webp\",\"69c29796dbc54.webp\",\"69c29796dc37c.webp\",\"69c29796dcaac.webp\",\"69c29796dd370.webp\"]', 0),
(54, 'iPhone 17', 'Apple', 'mobile', 3999.00, NULL, '', '', '{\"display_size\":\"6.3\",\"display_resolution\":\"1206 x 2622\",\"display_technology\":\"LTPO Super Retina XDR OLED\",\"rear_camera\":\"48.0 MP + 48.0 MP\",\"front_camera\":\"18.0MP\",\"rear_camera_fnumber\":\"F1.6 , F2.2\",\"video_resolution\":\"4K@24\\/25\\/30\\/60fps, 1080p@25\\/30\\/60\\/120\\/240fps\",\"chipset\":\"Apple A19\",\"battery_capacity\":\"3692\",\"cpu_speed\":\"2x4.26 GHz + 4x2.60 GHz\",\"cpu_type\":\"Hexa-Core\",\"memory_gb\":\"8\",\"storage_gb\":\"256 \\/ 512\",\"sim_count\":\"Dual-SIM\",\"os\":\"iOS 26\"}', '69c29c5bed444.webp', 50, 'active', '2026-03-24 14:14:51', '2026-04-14 01:18:30', '[{\"name\":\"Thin Purple\",\"hex\":\"#9a7fb8\",\"image\":\"69c29c5bf2309.webp\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 3,999\",\"sale\":\"RM 3,999\"},{\"label\":\"8GB + 512GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,999\"}]},{\"name\":\"Mint\",\"hex\":\"#98ff98\",\"image\":\"69c29c5bf25e2.webp\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 3,999\",\"sale\":\"RM 3,999\"},{\"label\":\"8GB + 512GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,999\"}]},{\"name\":\"Sky Blue\",\"hex\":\"#5b8cbf\",\"image\":\"69c29c5bf290d.webp\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 3,999\",\"sale\":\"RM 3,999\"},{\"label\":\"8GB + 512GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,999\"}]},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69c29c5bf2c76.webp\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 3,999\",\"sale\":\"RM 3,999\"},{\"label\":\"8GB + 512GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,999\"}]},{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"69c29c5bf304b.webp\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 3,999\",\"sale\":\"RM 3,999\"},{\"label\":\"8GB + 512GB\",\"price\":\"RM 4,999\",\"sale\":\"RM 4,999\"}]}]', NULL, '[\"69c29c5bed6b0.webp\",\"69c29c5bed948.webp\",\"69c29c5bedbde.webp\",\"69c29c5bf0b99.webp\",\"69c29c5bf0edb.webp\",\"69c29c5bf11ec.webp\",\"69c29c5bf164d.webp\",\"69c29c5bf1a27.webp\",\"69c29c5bf1d07.webp\",\"69c29c5bf1ea9.webp\",\"69c29c5bf2091.webp\"]', 3),
(55, 'Oppo Find X9 Pro', 'Oppo', 'mobile', 4999.00, NULL, '', '', '{\"display_size\":\"6.78\",\"display_resolution\":\"1272 x 2772 pixels\",\"display_technology\":\"LTPO AMOLED\",\"rear_camera\":\"50.0 MP + 200.0 MP + 50.0 MP\",\"front_camera\":\"50.0 MP\",\"rear_camera_fnumber\":\"F1.5 , F2.1 , F2.0\",\"video_resolution\":\"4K@30\\/60\\/120fps, 1080p@30\\/60\\/240fps\",\"chipset\":\"MediaTek Dimensity 9500\",\"battery_capacity\":\"7500\",\"cpu_speed\":\"4.21GHz, 3.35GHz, 2.7GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"16\",\"storage_gb\":\"512\",\"sim_count\":\"Dual-SIM\",\"os\":\"ColorOS 16\"}', '69cc1bf7b4927.png', 0, 'active', '2026-03-31 19:08:50', '2026-03-31 19:09:43', '[{\"name\":\"Titanium Charcoal\",\"hex\":\"#7D7C78\",\"image\":\"69cc1bc29e4a2.png\",\"storage\":[{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,999\",\"sale\":\"\"}]},{\"name\":\"Silk White\",\"hex\":\"#ffffff\",\"image\":\"69cc1bc29e644.png\",\"storage\":[{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,999\",\"sale\":\"\"}]}]', NULL, '[\"69cc1bc29dbf5.png\",\"69cc1bc29dea5.png\",\"69cc1bc29e0b1.png\",\"69cc1bc29e28c.jpg\"]', 0),
(56, 'Oppo A6 5G', 'Oppo', 'mobile', 1499.00, NULL, '', '', '{\"display_size\":\"6.75\",\"display_resolution\":\"720 x 1570 pixels\",\"display_technology\":\"IPS LCD\",\"rear_camera\":\"50.0 MP\",\"front_camera\":\"8.0 MP\",\"rear_camera_fnumber\":\"F1.8\",\"video_resolution\":\"1080p@30\\/60fps\",\"chipset\":\"Mediatek Dimensity 6300\",\"battery_capacity\":\"7000\",\"cpu_speed\":\"2.4 GHz , 2.0 GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"8\",\"storage_gb\":\"512\",\"sim_count\":\"Dual-SIM\",\"os\":\"ColorOS 15\"}', '69cc1e092e7c5.png', 0, 'active', '2026-03-31 19:18:33', '2026-04-02 17:00:06', '[{\"name\":\"Sakura Pink\",\"hex\":\"#f5c6d0\",\"image\":\"69cc1e092f16d.png\",\"storage\":[{\"label\":\"8GB + 512GB\",\"price\":\"RM 1,499\",\"sale\":\"\"}]},{\"name\":\"Sapphire Blue\",\"hex\":\"#4169e1\",\"image\":\"69cc1e092f2fd.png\",\"storage\":[{\"label\":\"8GB + 512GB\",\"price\":\"RM 1,499\",\"sale\":\"\"}]}]', NULL, '[\"69cc1e092ea3c.png\",\"69cc1e092ed1c.png\",\"69cc1e092efb9.png\"]', 4),
(57, 'Oppo Find N6', 'Oppo', 'mobile', 8699.00, NULL, '', '', '{\"display_size\":\"8.12\",\"display_resolution\":\"2248 x 2480 pixels\",\"display_technology\":\"Foldable LTPO OLED\",\"sub_display_size\":\"6.62\",\"sub_display_resolution\":\"1140 x 2616 pixels\",\"sub_display_technology\":\"LTPO OLED\",\"rear_camera\":\"200.0 MP + 50.0 MP + 50.0 MP\",\"front_camera\":\"20.0 MP\",\"rear_camera_fnumber\":\"F1.8 , F2.7 , F2.0\",\"video_resolution\":\"4K@30\\/60\\/120fps\",\"chipset\":\"Qualcomm SM8850-AC Snapdragon 8 Elite Gen 5\",\"battery_capacity\":\"6000\",\"cpu_speed\":\"4.6GHz, 3.62GHz\",\"cpu_type\":\"Hepta-Core\",\"memory_gb\":\"16\",\"storage_gb\":\"512\",\"sim_count\":\"Dual-SIM\",\"os\":\"ColorOS 16\"}', '69cc20a8e3fc0.jpg', 0, 'active', '2026-03-31 19:29:44', '2026-04-02 17:00:06', '[{\"name\":\"Blossom Orange\",\"hex\":\"#ff6700\",\"image\":\"69cc20a8e5c31.png\",\"storage\":[{\"label\":\"16GB + 512GB\",\"price\":\"RM 8,699\",\"sale\":\"\"}]},{\"name\":\"Stellar Titanium\",\"hex\":\"#7D7C78\",\"image\":\"69cc20a8e5ea8.png\",\"storage\":[{\"label\":\"16GB + 512GB\",\"price\":\"RM 8,699\",\"sale\":\"\"}]}]', NULL, '[\"69cc20a8e41d9.jpg\",\"69cc20a8e440f.png\",\"69cc20a8e4846.png\",\"69cc20a8e4c7c.png\",\"69cc20a8e4ec1.png\",\"69cc20a8e5095.png\",\"69cc20a8e5262.png\",\"69cc20a8e5467.png\",\"69cc20a8e5606.png\",\"69cc20a8e5788.png\",\"69cc20a8e59b1.jpg\"]', 2),
(58, 'Oppo Reno 15 Pro 5G', 'Oppo', 'mobile', 2999.00, NULL, '', '', '{\"display_size\":\"6.32\",\"display_resolution\":\"1216 x 2640 pixels\",\"display_technology\":\"AMOLED\",\"rear_camera\":\"200.0 MP + 50.0 MP + 50.0 MP\",\"front_camera\":\"50.0 MP\",\"rear_camera_fnumber\":\"F1.8 , F2.8 , F2.0\",\"video_resolution\":\"4K@30\\/60fps, 1080p@30\\/60\\/120\\/240\\/480fps\",\"chipset\":\"Mediatek Dimensity 8450\",\"battery_capacity\":\"6200\",\"cpu_speed\":\"3.25GHz ,3.0GHz, 2.1GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12\",\"storage_gb\":\"512\",\"sim_count\":\"Dual-SIM\",\"os\":\"ColorOS 16\"}', '69cc225609e34.png', 0, 'active', '2026-03-31 19:36:54', '2026-04-02 17:00:06', '[{\"name\":\"Aurora Blue\",\"hex\":\"#4169e1\",\"image\":\"69cc2256116af.png\",\"storage\":[{\"label\":\"12GB + 512GB\",\"price\":\"RM 2,999\",\"sale\":\"\"}]}]', NULL, '[\"69cc225609fc2.png\",\"69cc22560a1f4.png\",\"69cc225610ae5.png\",\"69cc225610e6f.png\",\"69cc2256110e1.png\",\"69cc22561136b.png\",\"69cc22561152c.png\"]', 3),
(59, 'Watch X', 'Oppo', 'audio', 349.00, NULL, '', '', '{\"audio_driver_size\":\"12.4mm Dynamic Driver\",\"audio_frequency_response\":\"20~20KHz\",\"audio_impedance\":\"-\",\"audio_sensitivity\":\"112 \\u00b1 3dB@1KHz\",\"audio_anc\":\"Yes (Adaptive ANC)\",\"audio_battery_life\":\"20 Hours (ANC On)\",\"audio_charging_time\":\"4 hours playback from 10min charge\",\"audio_bluetooth\":\"Bluetooth 5.4\",\"audio_codec\":\"AAC, SBC, LDAC\",\"audio_water_resistance\":\"IP55\",\"audio_microphone\":\"Triple-microphone system\",\"audio_noise_cancelling\":\"Max, Moderate, Mild modes\",\"audio_controls\":\"Touch Sensor\",\"audio_cable_length\":\"-\"}', '69cc8255356b1.jpg', 50, 'active', '2026-04-01 02:26:29', '2026-04-02 17:00:06', '[{\"name\":\"Moonlight White\",\"hex\":\"#ffffff\",\"image\":\"69cc82553be18.jpg\",\"storage\":[]}]', NULL, '[\"69cc82553590a.jpg\",\"69cc825535b7a.jpg\",\"69cc82553ba27.jpg\",\"69cc82553bbfb.png\"]', 6),
(60, 'OPPO Pad 5 Matte Display Edition', 'Oppo', 'tablet', 1699.00, NULL, '256GB | 8GB RAM | LPS LCD', '', '{\"display_size\":\"12.1\",\"display_resolution\":\"2800 \\u00d7 1980 pixels\",\"display_technology\":\"IPS LCD\",\"rear_camera\":\"8.0MP\",\"front_camera\":\"8.0MP\",\"rear_camera_fnumber\":\"F2.0\",\"video_resolution\":\"1080P \\/ 720P @ 30fps\",\"chipset\":\"MediaTek Dimensity 7300-Ultra\",\"battery_capacity\":\"10,050\",\"cpu_speed\":\"2.5 GHZ\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"8\",\"storage_gb\":\"256\",\"sim_count\":\"Nano SIM\",\"os\":\"ColorOS 16\",\"connectivity\":\"Wi-Fi + 5G\"}', '69cc8529c1361.png', 0, 'active', '2026-04-01 02:38:33', '2026-04-02 17:00:06', '[{\"name\":\"Aurora Pink\",\"hex\":\"#f5c6d0\",\"image\":\"69cc8529c23b0.png\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 1,699\",\"sale\":\"\"}]},{\"name\":\"Starlight Black\",\"hex\":\"#1a1a1a\",\"image\":\"69cc8529c25b2.png\",\"storage\":[{\"label\":\"8GB + 256GB\",\"price\":\"RM 1,699\",\"sale\":\"\"}]}]', NULL, '[\"69cc8529c1562.png\",\"69cc8529c17de.png\",\"69cc8529c1a8d.png\",\"69cc8529c1d88.png\",\"69cc8529c1f3c.png\",\"69cc8529c217a.png\"]', 5),
(61, 'Apple Watch Ultra 3', 'Apple', 'watch', 3699.00, NULL, '', '', '{\"display_size\":\"1.98\",\"display_resolution\":\"514 x 422 pixels\",\"display_technology\":\"Retina LTPO3 OLED\",\"rear_camera\":\"-\",\"front_camera\":\"-\",\"rear_camera_fnumber\":\"-\",\"video_resolution\":\"-\",\"chipset\":\"Apple S10\",\"battery_capacity\":\"599\",\"cpu_speed\":\"-\",\"cpu_type\":\"Dual-Core\",\"memory_gb\":\"-\",\"storage_gb\":\"64\",\"sim_count\":\"-\",\"os\":\"watchOS 26\",\"connectivity\":\"Wi-Fi Only\",\"band_colors\":[{\"name\":\"Light Blue\",\"hex\":\"#87ceeb\",\"image\":\"69cc911c1435d.jpg\"},{\"name\":\"Terra Cotta\",\"hex\":\"#8b4513\",\"image\":\"69cc911c14602.jpg\"},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69cc911c14838.jpg\"},{\"name\":\"Blue\",\"hex\":\"#4169e1\",\"image\":\"69cc911c14a16.jpg\"},{\"name\":\"Green\",\"hex\":\"#2e8b57\",\"image\":\"69cc911c14dbc.jpg\"},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69cc911c150af.jpg\"},{\"name\":\"Neon Green\",\"hex\":\"#2e8b57\",\"image\":\"69cc911c15343.jpg\"},{\"name\":\"Anchor Blue\",\"hex\":\"#4169e1\",\"image\":\"69cc911c1b5d5.jpg\"},{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69cc911c1b8b4.jpg\"}]}', '69cc8f28a95b6.jpg', 50, 'active', '2026-04-01 03:21:12', '2026-04-14 01:18:30', '[{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"69cc8f28a9aa5.jpg\",\"storage\":[]},{\"name\":\"Natural\",\"hex\":\"#fffdd0\",\"image\":\"69cc8f28a9d93.jpg\",\"storage\":[]}]', NULL, '[\"69cc8f28a979d.jpg\",\"69cc8f28a9944.jpg\"]', 5),
(62, 'Airpods 4', 'Apple', 'audio', 829.00, NULL, '', 'AirPods 4 with Active Noise Cancellation', '{\"display_size\":\"-\",\"display_resolution\":\"-\",\"display_technology\":\"-\",\"audio_driver_size\":\"-\",\"audio_type\":\"In-Ear\",\"audio_frequency_response\":\"-\",\"audio_impedance\":\"-\",\"audio_sensitivity\":\"-\",\"audio_anc\":\"Yes (Adaptive ANC)\",\"audio_battery_life\":\"20 Hours (ANC On)\",\"audio_charging_time\":\"5 minutes provides ~1 hour of listening time\",\"audio_bluetooth\":\"Bluetooth 5.3\",\"audio_codec\":\"AAC\",\"audio_water_resistance\":\"IP54\",\"audio_microphone\":\"Triple-microphone system\",\"audio_noise_cancelling\":\"REAL-TIME ADAPTIVE ANC\",\"audio_controls\":\"Touch Sensor\",\"audio_cable_length\":\"-\"}', '69cca077357df.jpg', 50, 'active', '2026-04-01 03:38:59', '2026-04-07 07:31:31', '[{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"69cca077359ef.jpg\",\"storage\":[]}]', NULL, '[]', 6),
(64, 'Pixel 10a', 'Google', 'mobile', 2299.00, NULL, '128GB | 16GB RAM | Tensor G4', 'Google AI-first smartphone with best camera.', '{\"display_size\":\"6.3-inch\",\"display_resolution\":\"1080 x 2424\",\"display_technology\":\"P-OLED\",\"rear_camera\":\"48.0 MP + 13.0 MP\",\"front_camera\":\"13MP\",\"rear_camera_fnumber\":\"F1.7 , F2.2\",\"video_resolution\":\"4K video recording at 30\\/60 FPS\",\"chipset\":\"Google Tensor G4\",\"battery_capacity\":\"5100\",\"cpu_speed\":\"3.1GHz, 2.6GHz, 1.19GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"8\",\"storage_gb\":\"128 \\/ 256\",\"sim_count\":\"Dual-SIM\",\"os\":\"Android 16\"}', '69cdfaf76f993.webp', 40, 'active', '2026-04-02 05:01:11', '2026-04-02 16:58:28', '[{\"name\":\"Lavender\",\"hex\":\"#1b2a4a\",\"image\":\"69cdfac2a60b4.webp\",\"storage\":[{\"label\":\"16GB + 128GB\",\"price\":\"RM 2,299\",\"sale\":\"\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 2,799\",\"sale\":\"\"}]},{\"name\":\"Berry\",\"hex\":\"#ff3b30\",\"image\":\"69cdfac2a6330.webp\",\"storage\":[{\"label\":\"16GB + 128GB\",\"price\":\"RM 2,299\",\"sale\":\"\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 2,799\",\"sale\":\"\"}]},{\"name\":\"Fog\",\"hex\":\"#98ff98\",\"image\":\"69cdfac2a6680.webp\",\"storage\":[{\"label\":\"16GB + 128GB\",\"price\":\"RM 2,299\",\"sale\":\"\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 2,799\",\"sale\":\"\"}]},{\"name\":\"Obsidian\",\"hex\":\"#3C3D3A\",\"image\":\"69cdfac2a68db.webp\",\"storage\":[{\"label\":\"16GB + 128GB\",\"price\":\"RM 2,299\",\"sale\":\"\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 2,799\",\"sale\":\"\"}]}]', NULL, '[\"69cdfac29fd52.webp\",\"69cdfac29ff75.webp\",\"69cdfac2a0160.webp\"]', 1),
(65, 'Pixel Buds Pro 2', 'Google', 'audio', 1099.00, NULL, '', '', '{\"audio_driver_size\":\"11mm\",\"audio_type\":\"In-Ear\",\"audio_frequency_response\":\"-\",\"audio_impedance\":\"-\",\"audio_sensitivity\":\"-\",\"audio_anc\":\"Yes (Adaptive ANC)\",\"audio_battery_life\":\"8 Hours (ANC On)\",\"audio_charging_time\":\"-\",\"audio_bluetooth\":\"Bluetooth 5.4\",\"audio_codec\":\"-\",\"audio_water_resistance\":\"-\",\"audio_microphone\":\"Beamforming mics, wind-blocking mesh covers\",\"audio_noise_cancelling\":\"REAL-TIME ADAPTIVE ANC\",\"audio_controls\":\"Touch Sensor\",\"audio_cable_length\":\"-\"}', '69cdfdb93f9e4.jpg', 0, 'active', '2026-04-02 05:25:13', '2026-04-02 16:58:28', '[{\"name\":\"Moonstone\",\"hex\":\"#8999AD\",\"image\":\"69cdfdb945307.jpg\",\"storage\":[]},{\"name\":\"Hazel\",\"hex\":\"#1a1a1a\",\"image\":\"69cdfdb9455c4.jpg\",\"storage\":[]},{\"name\":\"Porcelain\",\"hex\":\"#7D7C78\",\"image\":\"69cdfdb94587e.jpg\",\"storage\":[]},{\"name\":\"Peony\",\"hex\":\"#f5c6d0\",\"image\":\"69cdfdb945add.jpg\",\"storage\":[]}]', NULL, '[\"69cdfdb93fcea.jpg\",\"69cdfdb943b4a.jpg\",\"69cdfdb943d8c.jpg\",\"69cdfdb9440aa.jpg\",\"69cdfdb94436d.jpg\",\"69cdfdb94462e.jpg\",\"69cdfdb944a39.jpg\",\"69cdfdb944ce4.jpg\",\"69cdfdb945009.jpg\"]', 4),
(66, 'Pixel 10 Pro Fold', 'Google', 'mobile', 3749.00, NULL, '256GB | 16GB RAM | Tensor G4', 'Google AI-first smartphone with best camera.', '{\"display_size\":\"162mm\",\"display_resolution\":\"1080 x 2364\",\"display_technology\":\"OLED\",\"sub_display_size\":\"204mm\",\"sub_display_resolution\":\"2076 x 2152\",\"sub_display_technology\":\"OLED\",\"rear_camera\":\"48.0 MP + 10.5 MP + 10.8 MP\",\"front_camera\":\"10 MP\",\"rear_camera_fnumber\":\"F1.70 , F2.2 , F3.1\",\"video_resolution\":\"4K video recording at 24\\/30\\/60 FPS\",\"chipset\":\"Google Tensor G5\",\"battery_capacity\":\"5015\",\"cpu_speed\":\"3.78GHz, 3.05GHz, 2.25GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"16\",\"storage_gb\":\"256 \\/ 512\",\"sim_count\":\"Dual-SIM\",\"os\":\"Android 16\"}', '69ce0003a6831.png', 40, 'active', '2026-04-02 05:26:12', '2026-04-02 16:58:28', '[{\"name\":\"Moonstone\",\"hex\":\"#e5e4e2\",\"image\":\"69ce0003a7d43.png\",\"storage\":[{\"label\":\"16GB + 256GB\",\"price\":\"RM 5,899\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 6,499\",\"sale\":\"\"}]}]', NULL, '[\"69ce0003a7183.png\",\"69ce0003a7411.png\",\"69ce0003a76cb.png\",\"69ce0003a7a74.jpg\"]', 2),
(67, 'Pixel Watch 4', 'Google', 'watch', 2189.00, NULL, '', '', '{\"display_size\":\"45 mm\",\"display_resolution\":\"320 ppi\",\"display_technology\":\"AMOLED LTPO\",\"sub_display_size\":\"-\",\"sub_display_resolution\":\"-\",\"sub_display_technology\":\"-\",\"rear_camera\":\"-\",\"front_camera\":\"-\",\"rear_camera_fnumber\":\"-\",\"video_resolution\":\"-\",\"chipset\":\"Qualcomm Snapdragon\\u00ae W5 Gen 2\",\"battery_capacity\":\"455\",\"memory_gb\":\"2\",\"storage_gb\":\"32\",\"sim_count\":\"-\",\"os\":\"Wear OS 6\"}', '69ce0310c7434.jpg', 0, 'active', '2026-04-02 05:48:00', '2026-04-02 16:58:28', '[{\"name\":\"Polished Silver Aluminium case \\/ Porcelain Active Band\",\"hex\":\"#7D7C78\",\"image\":\"69ce0310c8ea5.jpg\",\"storage\":[]},{\"name\":\"Matte Black Aluminium case \\/ Obsidian Active Band\",\"hex\":\"#1a1a1a\",\"image\":\"69ce0310c9101.jpg\",\"storage\":[]}]', NULL, '[\"69ce0310c7666.jpg\",\"69ce0310c7877.jpg\",\"69ce0310c7acb.jpg\",\"69ce0310c7cff.jpg\",\"69ce0310c801b.jpg\",\"69ce0310c82e1.jpg\",\"69ce0310c8554.jpg\",\"69ce0310c87e7.jpg\",\"69ce0310c8a52.jpg\",\"69ce0310c8c51.jpg\"]', 3),
(68, 'Vivo X300', 'Vivo', 'mobile', 3299.00, NULL, '256GB | 12GB RAM | 50MP Zeiss', 'Vivo X300 powered by Dimensity 9400 with triple 50MP Zeiss cameras and 5800mAh battery.', '{\"display_size\":\"6.67 inches, 106.4 cm2 (~89.4% screen-to-body ratio)\",\"display_resolution\":\"1260 x 2800 pixels, 20:9 ratio (~460 ppi density)\",\"display_technology\":\"AMOLED, 1B colors, 120Hz, 2160Hz PWM, HDR10+, 4500 nits (peak)\",\"rear_camera\":\"50.0 MP + 50.0 MP + 50.0 MP\",\"front_camera\":\"50.0 MP\",\"rear_camera_fnumber\":\"F1.57 , F2.6 , F2.0\",\"video_resolution\":\"4K@30\\/60fps, 1080p@30\\/60\\/120\\/240fps, gyro-EIS\",\"chipset\":\"MediaTek Dimensity 9400\",\"battery_capacity\":\"5800\",\"cpu_speed\":\"3.63GHz, 3.3GHz, 2.4GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12 \\/ 16\",\"storage_gb\":\"256 \\/ 512\",\"dimensions\":\"160.5 x 75.5 x 7.9 mm (6.32 x 2.97 x 0.31 in)\",\"weight\":\"202\",\"sim_count\":\"Dual-SIM\",\"os\":\"Funtouch OS 15\"}', '69ce3f321af16.webp', 35, 'active', '2026-04-02 05:53:28', '2026-04-02 17:02:11', '[{\"name\":\"Phantom Black\",\"hex\":\"#1a1a1a\",\"image\":\"69ce3f321d300.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,299\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 3,599\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,799\",\"sale\":\"\"}]},{\"name\":\"Halo Pink\",\"hex\":\"#f5c6d0\",\"image\":\"69ce3f321d5bf.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,299\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 3,599\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,799\",\"sale\":\"\"}]},{\"name\":\"Mist Blue\",\"hex\":\"#87ceeb\",\"image\":\"69ce3f321d880.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,299\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 3,599\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,799\",\"sale\":\"\"}]}]', '[{\"label\":\"12GB + 256GB\",\"price\":3299,\"sale\":0},{\"label\":\"12GB + 512GB\",\"price\":3599,\"sale\":0},{\"label\":\"16GB + 512GB\",\"price\":3799,\"sale\":0}]', '[\"69ce3f321b282.webp\",\"69ce3f321b5e2.webp\",\"69ce3f321b95e.webp\",\"69ce3f321bd50.webp\",\"69ce3f321c0c1.webp\",\"69ce3f321c5ae.webp\",\"69ce3f321c9d2.webp\",\"69ce3f321cd5e.webp\",\"69ce3f321d0bc.webp\"]', 0),
(70, 'iQOO 13', 'iQOO', 'mobile', 3373.00, NULL, '256GB | 16GB RAM | SD 8 Elite', 'iQOO 13 flagship powered by Snapdragon 8 Elite with 50MP triple cameras and 6000mAh battery.', '{\"display_size\":\"6.82 inches\",\"display_resolution\":\"1440 x 3168 pixels\",\"display_technology\":\"LTPO AMOLED 4500 nits (peak)\",\"rear_camera\":\"50.0 MP + 50.0 MP + 50.0 MP\",\"front_camera\":\"32.0 MP\",\"rear_camera_fnumber\":\"F1.85 , F2.57 , F2.0\",\"video_resolution\":\"8K@24fps, 4K@30\\/60\\/120fps, 1080p@30\\/60\\/120\\/240fps\",\"chipset\":\"Qualcomm Snapdragon 8 Elite\",\"battery_capacity\":\"6150\",\"cpu_speed\":\"4.32GHz, 3.53GHz, 3.53GHz\",\"cpu_type\":\"Octa-Core\",\"memory_gb\":\"12 \\/ 16\",\"storage_gb\":\"256 \\/ 512 \\/ 1024\",\"dimensions\":\"163.98 x 77.13 x 8.18 mm\",\"weight\":\"225\",\"sim_count\":\"Dual-SIM\",\"os\":\"Funtouch OS 15\"}', '69ce4b2259770.webp', 30, 'active', '2026-04-02 10:28:56', '2026-04-02 10:55:30', '[{\"name\":\"Nardo Grey\",\"hex\":\"#6e6e6e\",\"image\":\"69ce4b225e928.jpg\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,699\",\"sale\":\"RM 3,373\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 3,799\",\"sale\":\"RM 3,499\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 3,899\",\"sale\":\"RM 3,557\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,929\",\"sale\":\"RM 3,389\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 5,299\",\"sale\":\"RM 4,799\"}]},{\"name\":\"BMW Motorsport (M Branding)\",\"hex\":\"#ffffff\",\"image\":\"69ce4b225eca8.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,699\",\"sale\":\"RM 3,373\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 3,799\",\"sale\":\"RM 3,499\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 3,899\",\"sale\":\"RM 3,557\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,929\",\"sale\":\"RM 3,389\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 5,299\",\"sale\":\"RM 4,799\"}]},{\"name\":\"Alpha\",\"hex\":\"#1a1a1a\",\"image\":\"69ce4b225eff4.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,699\",\"sale\":\"RM 3,373\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 3,799\",\"sale\":\"RM 3,499\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 3,899\",\"sale\":\"RM 3,557\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,929\",\"sale\":\"RM 3,389\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 5,299\",\"sale\":\"RM 4,799\"}]},{\"name\":\"Ace Green\",\"hex\":\"#5a7a5a\",\"image\":\"69ce4b2265820.png\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,699\",\"sale\":\"RM 3,373\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 3,799\",\"sale\":\"RM 3,499\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 3,899\",\"sale\":\"RM 3,557\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,929\",\"sale\":\"RM 3,389\"},{\"label\":\"16GB + 1024GB\",\"price\":\"RM 5,299\",\"sale\":\"RM 4,799\"}]}]', '[{\"label\":\"16GB + 256GB\",\"price\":3299,\"sale\":0},{\"label\":\"16GB + 512GB\",\"price\":3699,\"sale\":0}]', '[\"69ce4b2259ab0.webp\",\"69ce4b2259ded.webp\",\"69ce4b225a1fd.jpg\",\"69ce4b225dc8f.png\",\"69ce4b225dffc.webp\",\"69ce4b225e3b6.webp\"]', 1),
(74, 'iQOO 15', 'iQOO', 'mobile', 3499.00, NULL, '256GB | 12GB RAM | SD 8 Elite', 'iQOO flagship with Snapdragon 8 Elite, 6000mAh battery, and 120W ultra-fast charging.', '{\"display_size\":\"6.82 in\",\"display_resolution\":\"1260 x 2800\",\"display_technology\":\"LTPO AMOLED, 144Hz\",\"rear_camera\":\"50MP + 50MP + 50MP\",\"front_camera\":\"16MP\",\"rear_camera_fnumber\":\"f\\/1.69 + f\\/2.0 + f\\/2.0\",\"video_resolution\":\"8K@30fps\",\"chipset\":\"Snapdragon 8 Elite\",\"battery_capacity\":\"6000\",\"cpu_speed\":\"4.32GHz\",\"cpu_type\":\"Octa-core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"163.8 x 76.3 x 8.5\",\"weight\":\"221\",\"sim_count\":\"2\",\"os\":\"Android 15, OriginOS 5\"}', '69ce98c69d019.webp', 40, 'active', '2026-04-02 16:15:44', '2026-04-02 16:36:45', '[{\"name\":\"Legend White\",\"hex\":\"#ffffff\",\"image\":\"69ce98c69e449.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,499.00\",\"sale\":\"RM 3,279\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,999.00\",\"sale\":\"RM 3,799\"}]},{\"name\":\"Zen Gray\",\"hex\":\"#7D7C78\",\"image\":\"69ce98c69e708.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,499.00\",\"sale\":\"RM 3,279\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 3,999.00\",\"sale\":\"RM 3,799\"}]}]', NULL, '[\"69ce98c69d3c0.webp\",\"69ce98c69d66d.webp\",\"69ce98c69d9f3.webp\",\"69ce98c69dd58.webp\",\"69ce98c69e050.webp\"]', 0),
(75, 'iQOO Neo 10 5G', 'iQOO', 'mobile', 2099.00, NULL, '256GB | 12GB RAM | SD 8s Gen 4', 'Performance-focused mid-ranger with 6400mAh battery and 120W fast charging.', '{\"display_size\":\"6.78 in\",\"display_resolution\":\"1260 x 2800\",\"display_technology\":\"AMOLED, 144Hz\",\"rear_camera\":\"50MP + 8MP\",\"front_camera\":\"16MP\",\"rear_camera_fnumber\":\"f\\/1.88 + f\\/2.2\",\"video_resolution\":\"4K@60fps\",\"chipset\":\"Snapdragon 8s Gen 4\",\"battery_capacity\":\"6400\",\"cpu_speed\":\"3.21GHz\",\"cpu_type\":\"Octa-core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"163.2 x 75.8 x 8.2\",\"weight\":\"205\",\"sim_count\":\"2\",\"os\":\"Android 15, OriginOS 5\"}', '69ce99917af5b.webp', 50, 'active', '2026-04-02 16:15:44', '2026-04-02 16:58:36', '[{\"name\":\"Astral Black\",\"hex\":\"#1c1c1c\",\"image\":\"69ce9991815bd.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 2,099.00\",\"sale\":\"RM 1,999\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 2,299.00\",\"sale\":\"RM 2,199\"}]},{\"name\":\"Titan Gold\",\"hex\":\"#d2b48c\",\"image\":\"69ce9991818af.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 2,099.00\",\"sale\":\"RM 1,999\"},{\"label\":\"16GB + 256GB\",\"price\":\"RM 2,299.00\",\"sale\":\"RM 2,199\"}]}]', NULL, '[\"69ce99917b2d3.webp\",\"69ce99917b513.webp\",\"69ce999180e80.webp\",\"69ce99918128b.webp\"]', 2),
(76, 'iQOO Z10 5G', 'iQOO', 'mobile', 1299.00, NULL, '128GB | 8GB RAM | SD 7s Gen 3', 'Affordable 5G phone with 6000mAh battery and smooth 120Hz AMOLED display.', '{\"display_size\":\"6.77 in\",\"display_resolution\":\"1080 x 2392\",\"display_technology\":\"AMOLED, 120Hz\",\"rear_camera\":\"50MP + 2MP\",\"front_camera\":\"8MP\",\"rear_camera_fnumber\":\"f\\/1.88 + f\\/2.4\",\"video_resolution\":\"4K@30fps\",\"chipset\":\"Snapdragon 7s Gen 3\",\"battery_capacity\":\"6000\",\"cpu_speed\":\"2.50GHz\",\"cpu_type\":\"Octa-core\",\"memory_gb\":\"8\",\"storage_gb\":\"128\",\"dimensions\":\"164.5 x 75.8 x 8.0\",\"weight\":\"196\",\"sim_count\":\"2\",\"os\":\"Android 15, OriginOS 5\"}', '69ce99ba6ea0b.webp', 60, 'active', '2026-04-02 16:15:44', '2026-04-02 16:58:36', '[{\"name\":\"Stellar Black\",\"hex\":\"#1a1a1a\",\"image\":\"69ce99ba6f5fe.webp\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"RM 1,299.00\",\"sale\":\"RM 1,199\"},{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,499.00\",\"sale\":\"\"}]},{\"name\":\"Glacier Silver\",\"hex\":\"#e5e4e2\",\"image\":\"69ce99ba6f973.webp\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"RM 1,299.00\",\"sale\":\"RM 1,999\"},{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,499.00\",\"sale\":\"\"}]}]', NULL, '[\"69ce99ba6ecc1.webp\",\"69ce99ba6eee7.webp\",\"69ce99ba6f219.webp\"]', 3),
(77, 'Xperia 1 VII', 'Sony', 'mobile', 5499.00, NULL, '256GB | 12GB RAM | SD 8 Elite', 'Sony Xperia 1 VII with professional-grade Zeiss optics, 4K OLED display, and Snapdragon 8 Elite performance.', '{\"display_size\":\"6.5 in\",\"display_resolution\":\"1644 x 3840 (4K)\",\"display_technology\":\"OLED, 1-120Hz\",\"rear_camera\":\"52MP + 48MP + 48MP\",\"front_camera\":\"12MP\",\"rear_camera_fnumber\":\"f\\/1.9 + f\\/2.2 + f\\/2.3\",\"video_resolution\":\"4K@120fps\",\"chipset\":\"Snapdragon 8 Elite\",\"battery_capacity\":\"5000\",\"cpu_speed\":\"4.32GHz\",\"cpu_type\":\"Octa-core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"162.9 x 71.5 x 8.2\",\"weight\":\"192\",\"sim_count\":\"2\",\"os\":\"Android 15\"}', '69ce9eebd4eb5.webp', 28, 'active', '2026-04-02 16:46:15', '2026-04-16 06:44:23', '[{\"name\":\"Slate Black\",\"hex\":\"#1a1a1a\",\"image\":\"69ce9eebd5e11.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,499.00\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,999.00\",\"sale\":\"\"}]},{\"name\":\"Orchid Purple\",\"hex\":\"#6b4c8a\",\"image\":\"69ce9eebd6046.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,499.00\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,999.00\",\"sale\":\"\"}]},{\"name\":\"Moss Green\",\"hex\":\"#7d8a6a\",\"image\":\"69ce9eebd635b.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 5,499.00\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 5,999.00\",\"sale\":\"\"}]}]', NULL, '[\"69ce9eebd50fa.webp\",\"69ce9eebd5309.webp\",\"69ce9eebd55dd.webp\",\"69ce9eebd5907.webp\",\"69ce9eebd5bbb.webp\"]', 0),
(78, 'Xiaomi 15T Pro', 'Xiaomi', 'mobile', 3399.00, NULL, '256GB | 12GB RAM | SD 8 Elite', 'Xiaomi 15T Pro with Leica triple camera, 120W HyperCharge, and Snapdragon 8 Elite.', '{\"display_size\":\"6.67 in\",\"display_resolution\":\"1220 x 2712\",\"display_technology\":\"AMOLED, 144Hz\",\"rear_camera\":\"50MP + 50MP + 12MP\",\"front_camera\":\"32MP\",\"rear_camera_fnumber\":\"f\\/1.6 + f\\/2.0 + f\\/2.2\",\"video_resolution\":\"8K@24fps\",\"chipset\":\"Snapdragon 8 Elite\",\"battery_capacity\":\"5000\",\"cpu_speed\":\"4.32GHz\",\"cpu_type\":\"Octa-core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"160.5 x 75.1 x 8.0\",\"weight\":\"209\",\"sim_count\":\"2\",\"os\":\"Android 15, HyperOS 2\"}', '69cea2862dcf0.webp', 40, 'active', '2026-04-02 17:05:43', '2026-04-14 01:20:10', '[{\"name\":\"Black Titanium\",\"hex\":\"#1a1a1a\",\"image\":\"69cea28634b67.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,399.00\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 3,799.00\",\"sale\":\"\"}]},{\"name\":\"Titanium Grey\",\"hex\":\"#7a7a7a\",\"image\":\"69cea28634ecb.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,399.00\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 3,799.00\",\"sale\":\"\"}]},{\"name\":\"Mocha Gold\",\"hex\":\"#d2b48c\",\"image\":\"69cea2863543d.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,399.00\",\"sale\":\"\"},{\"label\":\"12GB + 512GB\",\"price\":\"RM 3,799.00\",\"sale\":\"\"}]}]', NULL, '[\"69cea2862e076.webp\",\"69cea2862e39e.webp\",\"69cea2862e6f4.webp\",\"69cea2862eaac.webp\",\"69cea2862ed28.webp\",\"69cea2862efa5.webp\",\"69cea286345b7.webp\",\"69cea2863481d.webp\"]', 2),
(79, 'Xiaomi 17', 'Xiaomi', 'mobile', 3999.00, NULL, '256GB | 12GB RAM | SD 8 Elite Gen 2', 'Xiaomi 17 flagship with Leica optics, LTPO AMOLED display and next-gen performance.', '{\"display_size\":\"6.73 in\",\"display_resolution\":\"1440 x 3200\",\"display_technology\":\"LTPO AMOLED, 1-144Hz\",\"rear_camera\":\"50MP + 50MP + 50MP\",\"front_camera\":\"32MP\",\"rear_camera_fnumber\":\"f\\/1.6 + f\\/2.0 + f\\/2.2\",\"video_resolution\":\"8K@30fps\",\"chipset\":\"Snapdragon 8 Elite Gen 2\",\"battery_capacity\":\"5500\",\"cpu_speed\":\"4.47GHz\",\"cpu_type\":\"Octa-core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"161.2 x 75.5 x 8.1\",\"weight\":\"210\",\"sim_count\":\"2\",\"os\":\"Android 16, HyperOS 3\"}', '69cea2fbcecb0.webp', 35, 'active', '2026-04-02 17:05:43', '2026-04-14 01:20:10', '[{\"name\":\"Midnight Black\",\"hex\":\"#1a1a1a\",\"image\":\"\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,999.00\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,499.00\",\"sale\":\"\"}]},{\"name\":\"Pearl White\",\"hex\":\"#f5f5f5\",\"image\":\"\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,999.00\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,499.00\",\"sale\":\"\"}]},{\"name\":\"Sage Green\",\"hex\":\"#7a9a7a\",\"image\":\"\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 3,999.00\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 4,499.00\",\"sale\":\"\"}]}]', NULL, '[\"69cea2fbcef90.webp\",\"69cea2fbcf2bc.webp\",\"69cea2fbcf605.webp\",\"69cea2fbcfa4d.webp\",\"69cea2fbcfd4a.webp\",\"69cea2fbd000c.webp\",\"69cea2fbd02b7.webp\",\"69cea2fbd0504.webp\",\"69cea2fbd0779.webp\",\"69cea2fbd33a9.webp\",\"69cea2fbd3621.webp\",\"69cea2fbd392b.webp\",\"69cea2fbd3cd6.webp\",\"69cea2fbd3fea.webp\",\"69cea2fbd426e.webp\",\"69cea2fbd4512.webp\",\"69cea2fbd483c.webp\",\"69cea2fbda76c.webp\",\"69cea2fbdab32.webp\"]', 1),
(80, 'Xiaomi 17 Ultra', 'Xiaomi', 'mobile', 5499.00, NULL, '512GB | 16GB RAM | SD 8 Elite Gen 2', 'Xiaomi 17 Ultra with variable aperture Leica camera, 6200mAh and 120W wireless charging.', '{\"display_size\":\"6.73 in\",\"display_resolution\":\"1440 x 3200\",\"display_technology\":\"LTPO AMOLED, 1-120Hz\",\"rear_camera\":\"50MP + 200MP + 50MP\",\"front_camera\":\"32MP\",\"rear_camera_fnumber\":\"f\\/1.4-f\\/4.0 + f\\/2.6 + f\\/2.2\",\"video_resolution\":\"8K@30fps\",\"chipset\":\"Snapdragon 8 Elite Gen 2\",\"battery_capacity\":\"6200\",\"cpu_speed\":\"4.47GHz\",\"cpu_type\":\"Octa-core\",\"memory_gb\":\"16\",\"storage_gb\":\"512\",\"dimensions\":\"162.0 x 75.8 x 9.5\",\"weight\":\"228\",\"sim_count\":\"2\",\"os\":\"Android 16, HyperOS 3\"}', '69cea362a1fc2.webp', 25, 'active', '2026-04-02 17:05:43', '2026-04-02 17:12:02', '[{\"name\":\"Black\",\"hex\":\"#1a1a1a\",\"image\":\"\",\"storage\":[{\"label\":\"16GB + 512GB\",\"price\":\"RM 5,499.00\",\"sale\":\"\"},{\"label\":\"16GB + 1TB\",\"price\":\"RM 5,999.00\",\"sale\":\"\"}]},{\"name\":\"Green\",\"hex\":\"#2e8b57\",\"image\":\"\",\"storage\":[{\"label\":\"16GB + 512GB\",\"price\":\"RM 5,499.00\",\"sale\":\"\"},{\"label\":\"16GB + 1TB\",\"price\":\"RM 5,999.00\",\"sale\":\"\"}]},{\"name\":\"White\",\"hex\":\"#ffffff\",\"image\":\"\",\"storage\":[{\"label\":\"16GB + 512GB\",\"price\":\"RM 5,499.00\",\"sale\":\"\"},{\"label\":\"16GB + 1TB\",\"price\":\"RM 5,999.00\",\"sale\":\"\"}]}]', NULL, '[\"69cea362a21ec.webp\",\"69cea362a249d.webp\",\"69cea362a278b.webp\",\"69cea362a2bf2.webp\",\"69cea362a304a.webp\",\"69cea362a3370.webp\",\"69cea362a3693.webp\",\"69cea362a38f3.webp\",\"69cea362a3bb9.webp\",\"69cea362a3ead.webp\",\"69cea362a4092.webp\",\"69cea362a43b4.webp\",\"69cea362a468d.webp\",\"69cea362a4991.webp\",\"69cea362a4d82.webp\",\"69cea362a50d1.webp\",\"69cea362a5452.webp\",\"69cea362a5839.webp\",\"69cea362a5a6d.webp\"]', 0),
(81, 'Xiaomi Pad 8', 'Xiaomi', 'tablet', 1599.00, NULL, '128GB | 8GB RAM | 11.2&quot; 144Hz', 'Xiaomi Pad 8 with large 11.2-inch display, 8850mAh battery and 45W fast charging.', '{\"display_size\":\"11.2 in\",\"display_resolution\":\"1800 x 2800\",\"display_technology\":\"LCD, 144Hz\",\"rear_camera\":\"13MP\",\"front_camera\":\"8MP\",\"rear_camera_fnumber\":\"f\\/2.0\",\"video_resolution\":\"4K@30fps\",\"chipset\":\"Snapdragon 7s Gen 3\",\"battery_capacity\":\"8850\",\"cpu_speed\":\"2.50GHz\",\"cpu_type\":\"Octa-core\",\"memory_gb\":\"8\",\"storage_gb\":\"128\",\"dimensions\":\"251.2 x 168.3 x 6.5\",\"weight\":\"510\",\"sim_count\":\"1\",\"os\":\"Android 15, HyperOS 2\"}', '69cea3ab4a8e9.webp', 50, 'active', '2026-04-02 17:05:43', '2026-04-14 01:20:11', '[{\"name\":\"Dark Grey\",\"hex\":\"#4a4a4a\",\"image\":\"69cea3ab514ab.webp\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"RM 1,599.00\",\"sale\":\"\"},{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,899.00\",\"sale\":\"\"}]},{\"name\":\"Mint Green\",\"hex\":\"#7dbf9e\",\"image\":\"69cea3ab51820.webp\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"RM 1,599.00\",\"sale\":\"\"},{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,899.00\",\"sale\":\"\"}]},{\"name\":\"Blue\",\"hex\":\"#4169e1\",\"image\":\"69cea3ab51aaa.webp\",\"storage\":[{\"label\":\"8GB + 128GB\",\"price\":\"RM 1,599.00\",\"sale\":\"\"},{\"label\":\"12GB + 256GB\",\"price\":\"RM 1,899.00\",\"sale\":\"\"}]}]', NULL, '[\"69cea3ab4abe8.webp\",\"69cea3ab4ae6d.webp\",\"69cea3ab4b1db.webp\",\"69cea3ab4b4b5.webp\",\"69cea3ab4b83a.webp\",\"69cea3ab4bbcd.webp\",\"69cea3ab4bf5a.webp\",\"69cea3ab4c221.webp\",\"69cea3ab4c503.webp\",\"69cea3ab4c7ca.webp\",\"69cea3ab50d5c.webp\",\"69cea3ab5104c.webp\"]', 3),
(82, 'Xiaomi Pad 8 Pro', 'Xiaomi', 'tablet', 2499.00, NULL, '256GB | 12GB RAM | 12.1&quot; AMOLED', 'Xiaomi Pad 8 Pro with 12.1-inch AMOLED display, Snapdragon 8s Gen 3 and 10090mAh battery.', '{\"display_size\":\"12.1 in\",\"display_resolution\":\"2560 x 1600\",\"display_technology\":\"AMOLED, 144Hz\",\"rear_camera\":\"50MP\",\"front_camera\":\"32MP\",\"rear_camera_fnumber\":\"f\\/2.0\",\"video_resolution\":\"4K@30fps\",\"chipset\":\"Snapdragon 8s Gen 3\",\"battery_capacity\":\"10090\",\"cpu_speed\":\"3.00GHz\",\"cpu_type\":\"Octa-core\",\"memory_gb\":\"12\",\"storage_gb\":\"256\",\"dimensions\":\"278.7 x 181.9 x 6.3\",\"weight\":\"590\",\"sim_count\":\"1\",\"os\":\"Android 15, HyperOS 2\"}', '69cea3ff7736a.webp', 35, 'active', '2026-04-02 17:05:43', '2026-04-14 01:20:11', '[{\"name\":\"Space Grey\",\"hex\":\"#4a4a4a\",\"image\":\"69cea3ff78de1.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 2,499.00\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 2,899.00\",\"sale\":\"\"}]},{\"name\":\"Green\",\"hex\":\"#2e8b57\",\"image\":\"69cea3ff790cf.webp\",\"storage\":[{\"label\":\"12GB + 256GB\",\"price\":\"RM 2,499.00\",\"sale\":\"\"},{\"label\":\"16GB + 512GB\",\"price\":\"RM 2,899.00\",\"sale\":\"\"}]}]', NULL, '[\"69cea3ff77623.webp\",\"69cea3ff777ee.webp\",\"69cea3ff779e4.webp\",\"69cea3ff77c49.webp\",\"69cea3ff78013.webp\",\"69cea3ff78304.webp\",\"69cea3ff785d2.webp\",\"69cea3ff7896c.webp\"]', 4),
(83, 'Xiaomi Watch 15', 'Xiaomi', 'watch', 699.00, NULL, 'AMOLED | GPS | SpO2 | 5ATM', 'Xiaomi Watch 15 with health tracking, built-in GPS and premium AMOLED display.', '{\"display_size\":\"1.43 in\",\"display_resolution\":\"466 x 466\",\"display_technology\":\"AMOLED\",\"rear_camera\":\"\\u2014\",\"front_camera\":\"\\u2014\",\"rear_camera_fnumber\":\"\\u2014\",\"video_resolution\":\"\\u2014\",\"chipset\":\"Xiaomi W3\",\"battery_capacity\":\"500\",\"cpu_speed\":\"\\u2014\",\"cpu_type\":\"\\u2014\",\"memory_gb\":\"\\u2014\",\"storage_gb\":\"\\u2014\",\"dimensions\":\"46.0 x 46.0 x 11.5\",\"weight\":\"36\",\"sim_count\":\"0\",\"os\":\"HyperOS 2 (Wear)\"}', '69cea46e5d60e.webp', 60, 'active', '2026-04-02 17:05:43', '2026-04-14 01:20:11', '[{\"name\":\"Midnight Black\",\"hex\":\"#1a1a1a\",\"image\":\"69cea46e5f251.webp\",\"storage\":[{\"label\":\"Standard\",\"price\":\"RM 699.00\",\"sale\":\"\"}]},{\"name\":\"Green\",\"hex\":\"#2e8b57\",\"image\":\"69cea46e5f43c.webp\",\"storage\":[{\"label\":\"Standard\",\"price\":\"RM 699.00\",\"sale\":\"\"}]}]', NULL, '[\"69cea46e5d79f.webp\",\"69cea46e5d904.webp\",\"69cea46e5db7c.webp\",\"69cea46e5df3a.webp\",\"69cea46e5e21f.webp\",\"69cea46e5e4b8.webp\",\"69cea46e5e817.webp\",\"69cea46e5eb6f.webp\",\"69cea46e5eecd.webp\"]', 5);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reviewer_name` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_verifications`
--

CREATE TABLE `student_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `university` varchar(150) NOT NULL,
  `student_email` varchar(150) NOT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'verified',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_verifications`
--

INSERT INTO `student_verifications` (`id`, `user_id`, `student_id`, `university`, `student_email`, `status`, `created_at`) VALUES
(1, 2, 'ytfygjh46b', 'Universiti Malaysia Pahang Al-Sultan Abdullah (UMPSA)', 'tonystark@student.umpsa.edu.my', 'verified', '2026-03-23 22:15:20'),
(2, 1, '25JMD0916', 'Universiti Malaya (UM)', 'tonystark@student.umpsa.edu.my', 'verified', '2026-04-14 15:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `photo` varchar(255) DEFAULT 'default.png',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive','blocked') DEFAULT 'active',
  `reset_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_verified` tinyint(1) DEFAULT 0,
  `verify_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `photo`, `phone`, `address`, `status`, `reset_token`, `created_at`, `updated_at`, `email_verified`, `verify_token`) VALUES
(1, 'Admin', 'admin@techhype.com', '$2b$12$c53D64/ArWLrDmi3TCX47ePP5O6s28FCbNONJas6O3ojs8lEK780O', 'admin', 'default.png', NULL, NULL, 'active', NULL, '2026-03-10 07:17:45', '2026-04-14 02:19:12', 1, NULL),
(2, 'Pirates', 'deveswarm-jm25@student.tarc.edu.my', '$2b$12$c53D64/ArWLrDmi3TCX47ePP5O6s28FCbNONJas6O3ojs8lEK780O', 'member', '69bcf4f28f8ee.png', '0177350799', NULL, 'active', NULL, '2026-03-20 07:19:14', '2026-04-14 03:24:58', 1, NULL),
(5, 'Deveswar', 'deveswar07@gmail.com', '$2b$12$c53D64/ArWLrDmi3TCX47ePP5O6s28FCbNONJas6O3ojs8lEK780O', 'member', '69dce9b4a9bf6.jpeg', '0177350792', NULL, 'active', NULL, '2026-04-13 13:03:48', '2026-04-14 03:26:05', 0, '04aa57dbc10b2314ac737697bad1317417db2d0943a086670311b0656dd02225'),
(6, 'Admin2', 'admin2@techhype.my', '$2b$12$c53D64/ArWLrDmi3TCX47ePP5O6s28FCbNONJas6O3ojs8lEK780O', 'admin', 'default.png', NULL, NULL, 'active', NULL, '2026-04-13 13:16:57', '2026-04-14 03:26:15', 1, NULL),
(7, 'cheng', 'deveswarmohan@gmail.com', '$2y$10$AHg0zmtTBwmPcuRVSUoCWuIwgqKdS8BKsNKe7Roiwp.qWS1xpgKkG', 'member', 'default.png', '0177350799', NULL, 'active', NULL, '2026-04-14 02:26:53', '2026-04-14 02:29:57', 1, NULL),
(8, 'KAI ZHE', 'deveswarmohan07@gmail.com', '$2y$10$IjSzcv.OVDLA6KOKqaAd4evRJAd.ZblwiOeIKsHATlQ6nuVVotpPy', 'member', 'default.png', '0123456789', NULL, 'blocked', NULL, '2026-04-14 07:06:55', '2026-04-14 07:38:20', 0, 'b0ca434223eff64a93c4906bc41bc054575772d59eab68b8b00f5884bf40b49d'),
(9, 'KAI ZHE', 'mobalegendhack@gmail.com', '$2y$10$T6Zvqr3MAadyFL9XEF2TMutSW1culs3d5KgzyFckx0vkNFPb5XXAi', 'member', 'default.png', '0123456789', NULL, 'active', NULL, '2026-04-14 07:08:05', '2026-04-14 07:09:54', 0, 'd31b25d1d60a71c3a3c40b4126bd70368f269dfe5470b1341f982c723cc8d694'),
(10, 'KAI ZHE', 'kzhe71304@gmail.com', '$2y$10$rgqXyMtJQKJf1QWbfmkOB.0IXTa.P37Ao4r2B0.RGdTvUaw78.cL6', 'member', '69e07a63eb582.jpg', '+60 17 735 0798', 'no 19, Jalan Merak 4\r\nTaman Scientex Aster', 'active', NULL, '2026-04-16 05:57:56', '2026-04-16 06:23:07', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `type` enum('fixed','percent','shipping') NOT NULL,
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `min_spend` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','used','expired') DEFAULT 'active',
  `used_order_id` int(11) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `user_id`, `code`, `type`, `value`, `max_discount`, `min_spend`, `status`, `used_order_id`, `expires_at`, `created_at`) VALUES
(1, 2, 'STU-4AA95CAF', 'percent', 35.00, 100.00, 50.00, 'used', 4, '2026-06-21 15:15:20', '2026-03-23 22:15:20'),
(2, 1, 'STU-062D9ECF', 'percent', 35.00, 100.00, 50.00, 'active', NULL, '2026-07-13 09:21:48', '2026-04-14 15:21:48');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 2, 79, '2026-04-03 12:50:14'),
(2, 2, 80, '2026-04-03 12:50:22'),
(3, 2, 82, '2026-04-07 07:37:00'),
(5, 2, 77, '2026-04-14 07:13:02'),
(6, 10, 77, '2026-04-16 15:29:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_ip` (`ip_address`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `points_log`
--
ALTER TABLE `points_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `student_verifications`
--
ALTER TABLE `student_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wish` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `points_log`
--
ALTER TABLE `points_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_verifications`
--
ALTER TABLE `student_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `points_log`
--
ALTER TABLE `points_log`
  ADD CONSTRAINT `points_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `points_log_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_verifications`
--
ALTER TABLE `student_verifications`
  ADD CONSTRAINT `student_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
