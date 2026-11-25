-- phpMyAdmin SQL Dump
-- Database: `graha_clouth`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

CREATE DATABASE IF NOT EXISTS `graha_clouth` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `graha_clouth`;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_whatsapp` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `users`
-- Admin password is 'admin123'
INSERT INTO `users` (`id`, `nama`, `email`, `no_whatsapp`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@grahaclouth.com', '081234567890', '$2y$10$wO4q6Ld28H/Vl0R9fK1u0.sU8fX2.pX1R1V1Z1C1Z1C1Z1C1Z1C1', 'admin', '2026-08-11 12:00:00');

-- --------------------------------------------------------

-- Table structure for table `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `nama_kategori`, `created_at`) VALUES
(1, 'Kaos', '2026-08-11 12:00:00'),
(2, 'Kemeja', '2026-08-11 12:00:00'),
(3, 'Hoodie', '2026-08-11 12:00:00'),
(4, 'Jaket', '2026-08-11 12:00:00'),
(5, 'Celana', '2026-08-11 12:00:00'),
(6, 'Aksesoris', '2026-08-11 12:00:00');

-- --------------------------------------------------------

-- Table structure for table `products`
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `harga_promo` decimal(10,2) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL,
  `ukuran` varchar(255) DEFAULT NULL, -- Comma separated like 'S,M,L,XL,XXL'
  `warna` varchar(255) DEFAULT NULL, -- Comma separated like 'Hitam,Putih'
  `status` enum('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `products`
INSERT INTO `products` (`id`, `category_id`, `nama_produk`, `deskripsi`, `harga`, `harga_promo`, `stok`, `gambar`, `ukuran`, `warna`, `status`, `created_at`) VALUES
(1, 1, 'Graha Basic T-Shirt', 'Kaos basic nyaman digunakan sehari-hari. Material katun combed 30s yang menyerap keringat dan lembut di kulit.', 85000.00, 75000.00, 100, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'S,M,L,XL,XXL', 'Hitam,Putih,Navy', 'aktif', '2026-08-11 12:00:00'),
(2, 1, 'Graha Oversize T-Shirt', 'Kaos oversize dengan potongan modern. Cocok untuk tampilan streetwear casual Anda.', 110000.00, NULL, 50, 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'M,L,XL', 'Abu-abu,Hitam', 'aktif', '2026-08-11 12:00:00'),
(3, 1, 'Graha Polo Shirt', 'Polo shirt elegan untuk acara semi-formal. Dibuat dari bahan pique katun berkualitas.', 125000.00, 110000.00, 75, 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'S,M,L,XL', 'Navy,Maroon', 'aktif', '2026-08-11 12:00:00'),
(4, 2, 'Graha Casual Shirt', 'Kemeja casual lengan pendek. Sempurna untuk hangout santai bersama teman.', 150000.00, NULL, 40, 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'M,L,XL', 'Putih,Biru Muda', 'aktif', '2026-08-11 12:00:00'),
(5, 3, 'Graha Hoodie', 'Hoodie tebal dan hangat. Dilengkapi saku depan dan tali penyesuai pada tudung.', 250000.00, 225000.00, 30, 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'M,L,XL,XXL', 'Hitam,Abu-abu Misty', 'aktif', '2026-08-11 12:00:00'),
(6, 4, 'Graha Jacket', 'Jaket trucker stylish. Cocok dikombinasikan dengan t-shirt basic Anda.', 300000.00, NULL, 20, 'https://images.unsplash.com/photo-1551028719-00167b16eac5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'M,L,XL', 'Navy,Army', 'aktif', '2026-08-11 12:00:00'),
(7, 5, 'Graha Cargo Pants', 'Celana cargo dengan banyak kantong fungsional. Material kuat dan tahan lama.', 220000.00, 199000.00, 45, 'https://images.unsplash.com/photo-1624378439575-d1ead6bb2414?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'S,M,L,XL', 'Hitam,Khaki', 'aktif', '2026-08-11 12:00:00'),
(8, 5, 'Graha Chino Pants', 'Celana chino slim fit untuk penampilan smart casual. Melar (stretch) nyaman dipakai.', 180000.00, NULL, 60, 'https://images.unsplash.com/photo-1473966968600-fa801b869a1a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'S,M,L,XL', 'Krem,Navy,Hitam', 'aktif', '2026-08-11 12:00:00'),
(9, 5, 'Graha Denim Pants', 'Jeans denim berkualitas tinggi. Potongan lurus yang klasik dan timeless.', 250000.00, NULL, 35, 'https://images.unsplash.com/photo-1542272604-787c3835535d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'S,M,L,XL', 'Biru Tua,Biru Muda', 'aktif', '2026-08-11 12:00:00'),
(10, 6, 'Graha Cap', 'Topi baseball cap dengan logo bordir minimalis. Melindungi dari sinar matahari dengan gaya.', 65000.00, NULL, 100, 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'All Size', 'Hitam,Navy', 'aktif', '2026-08-11 12:00:00'),
(11, 6, 'Graha Tote Bag', 'Tas jinjing kanvas tebal untuk membawa keperluan sehari-hari Anda.', 85000.00, 70000.00, 80, 'https://images.unsplash.com/photo-1597484661643-2f5fef640df1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'All Size', 'Putih Tulang,Hitam', 'aktif', '2026-08-11 12:00:00'),
(12, 6, 'Graha Fashion Set', 'Set pakaian komplit termasuk atasan, bawahan, dan aksesoris terkoordinasi.', 450000.00, 399000.00, 15, 'https://images.unsplash.com/photo-1492707892479-7bc8d5a4ee93?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'M,L', 'Mix', 'aktif', '2026-08-11 12:00:00');

-- --------------------------------------------------------

-- Table structure for table `orders`
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL, -- NULL if guest checkout allowed, but we require login in this spec? Let's say user_id is required.
  `nomor_order` varchar(50) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `no_whatsapp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `metode_pengiriman` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `diskon` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ongkir` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `status` enum('Pesanan Baru','Diproses','Dikemas','Dikirim','Selesai','Dibatalkan') NOT NULL DEFAULT 'Pesanan Baru',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_order` (`nomor_order`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `order_details`
CREATE TABLE `order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `ukuran` varchar(50) DEFAULT NULL,
  `warna` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
