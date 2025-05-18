-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.33 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             11.2.0.6213
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for deteksi
CREATE DATABASE IF NOT EXISTS `deteksi` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `deteksi`;

-- Dumping structure for table deteksi.assessment_criteria
CREATE TABLE IF NOT EXISTS `assessment_criteria` (
  `criteria_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` enum('motorik','bahasa','kognitif') NOT NULL,
  `criteria_name` varchar(100) NOT NULL,
  `min_value` float DEFAULT NULL,
  `max_value` float DEFAULT NULL,
  PRIMARY KEY (`criteria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table deteksi.assessment_criteria: ~0 rows (approximately)
/*!40000 ALTER TABLE `assessment_criteria` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessment_criteria` ENABLE KEYS */;

-- Dumping structure for table deteksi.assessment_results
CREATE TABLE IF NOT EXISTS `assessment_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `motorik_kasar` int(11) DEFAULT NULL,
  `motorik_halus` int(11) DEFAULT NULL,
  `komunikasi` int(11) DEFAULT NULL,
  `membaca` int(11) DEFAULT NULL,
  `sosial_skill` int(11) DEFAULT NULL,
  `menyimak` int(11) DEFAULT NULL,
  `ekspresif` int(11) DEFAULT NULL,
  `pra_akademik` int(11) DEFAULT NULL,
  `prediction` varchar(50) DEFAULT NULL,
  `rekomendasi` text,
  `tanggal` date DEFAULT NULL,
  `motorik_score` float DEFAULT NULL,
  `bahasa_score` float DEFAULT NULL,
  `kognitif_score` float DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK__students` (`student_id`) USING BTREE,
  CONSTRAINT `FK_assessment_results_students` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=latin1;

-- Dumping data for table deteksi.assessment_results: ~20 rows (approximately)
/*!40000 ALTER TABLE `assessment_results` DISABLE KEYS */;
INSERT INTO `assessment_results` (`id`, `student_id`, `motorik_kasar`, `motorik_halus`, `komunikasi`, `membaca`, `sosial_skill`, `menyimak`, `ekspresif`, `pra_akademik`, `prediction`, `rekomendasi`, `tanggal`, `motorik_score`, `bahasa_score`, `kognitif_score`) VALUES
	(30, 2, 4, 4, 1, 2, 1, 2, 2, 1, 'Terlambat', '{"bahasa":["Bacakan buku cerita setiap hari dan diskusikan isinya","Ajak anak berbicara dan dengarkan dengan penuh perhatian","Perkenalkan kosakata baru melalui permainan kata","Gunakan lagu dan musik untuk mengembangkan kemampuan bahasa"],"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"],"umum":["Konsultasikan dengan dokter anak atau psikolog anak untuk evaluasi lebih lanjut","Pertimbangkan untuk mengikuti program stimulasi terpadu","Pastikan asupan gizi anak terpenuhi dengan baik","Berikan waktu istirahat dan tidur yang cukup"]}', '2025-03-13', NULL, NULL, NULL),
	(37, 4, 4, 4, 4, 3, 3, 4, 4, 3, 'Normal', '[]', '2025-03-13', NULL, NULL, NULL),
	(46, 7, 4, 2, 3, 2, 4, 1, 3, 3, 'Terlambat', '{"bahasa":["Bacakan buku cerita setiap hari dan diskusikan isinya","Ajak anak berbicara dan dengarkan dengan penuh perhatian","Perkenalkan kosakata baru melalui permainan kata","Gunakan lagu dan musik untuk mengembangkan kemampuan bahasa"],"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"],"umum":["Konsultasikan dengan dokter anak atau psikolog anak untuk evaluasi lebih lanjut","Pertimbangkan untuk mengikuti program stimulasi terpadu","Pastikan asupan gizi anak terpenuhi dengan baik","Berikan waktu istirahat dan tidur yang cukup"]}', '2025-03-16', NULL, NULL, NULL),
	(47, 8, 4, 3, 3, 3, 2, 1, 2, 4, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-03-17', NULL, NULL, NULL),
	(50, 9, 2, 2, 3, 1, 2, 2, 1, 2, 'Terlambat', '{"motorik":["Lakukan aktivitas fisik yang menyenangkan seperti bermain bola atau berenang","Latih keseimbangan dengan permainan sederhana seperti berjalan di garis lurus","Berikan mainan yang membutuhkan keterampilan motorik halus seperti puzzle atau balok susun","Ajak anak melakukan kegiatan menggambar dan mewarnai secara rutin"],"bahasa":["Bacakan buku cerita setiap hari dan diskusikan isinya","Ajak anak berbicara dan dengarkan dengan penuh perhatian","Perkenalkan kosakata baru melalui permainan kata","Gunakan lagu dan musik untuk mengembangkan kemampuan bahasa"],"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"],"umum":["Konsultasikan dengan dokter anak atau psikolog anak untuk evaluasi lebih lanjut","Pertimbangkan untuk mengikuti program stimulasi terpadu","Pastikan asupan gizi anak terpenuhi dengan baik","Berikan waktu istirahat dan tidur yang cukup"]}', '2025-03-23', NULL, NULL, NULL),
	(57, 3, 4, 4, 3, 4, 4, 3, 3, 4, 'Normal', '[]', '2025-03-23', NULL, NULL, NULL),
	(60, 10, 3, 3, 2, 4, 2, 1, 2, 4, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-04-20', NULL, NULL, NULL),
	(61, 11, 4, 3, 3, 3, 1, 2, 2, 4, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-04-23', NULL, NULL, NULL),
	(62, 12, 4, 4, 2, 4, 2, 1, 2, 4, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-04-23', NULL, NULL, NULL),
	(63, 13, 4, 4, 3, 3, 1, 1, 1, 3, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-04-23', NULL, NULL, NULL),
	(64, 14, 4, 4, 2, 4, 2, 2, 2, 4, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-04-23', NULL, NULL, NULL),
	(65, 15, 3, 3, 3, 3, 1, 3, 1, 4, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-04-23', NULL, NULL, NULL),
	(67, 16, 4, 3, 2, 4, 1, 2, 1, 4, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-04-23', NULL, NULL, NULL),
	(68, 17, 4, 4, 3, 3, 1, 1, 1, 3, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-04-23', NULL, NULL, NULL),
	(71, 18, 4, 3, 3, 4, 1, 1, 2, 4, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-04-23', NULL, NULL, NULL),
	(79, 1, 3, 4, 3, 3, 3, 4, 2, 4, 'Normal', '[]', '2025-04-24', NULL, NULL, NULL),
	(83, 19, 4, 4, 3, 3, 1, 1, 2, 4, 'Terlambat', '{"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"]}', '2025-05-01', NULL, NULL, NULL),
	(88, 25, 4, 4, 4, 3, 2, 4, 3, 3, 'Normal', '[]', '2025-05-05', NULL, NULL, NULL),
	(89, 24, 4, 4, 3, 3, 4, 2, 3, 4, 'Normal', '[]', '2025-05-05', NULL, NULL, NULL),
	(97, 21, 4, 4, 3, 3, 0, 0, 0, 0, 'Terlambat', '{"bahasa":["Bacakan buku cerita setiap hari dan diskusikan isinya","Ajak anak berbicara dan dengarkan dengan penuh perhatian","Perkenalkan kosakata baru melalui permainan kata","Gunakan lagu dan musik untuk mengembangkan kemampuan bahasa"],"kognitif":["Berikan permainan yang melibatkan pemecahan masalah sederhana","Latih anak mengelompokkan benda berdasarkan warna, bentuk, atau ukuran","Ajak anak bermain peran untuk mengembangkan imajinasi","Lakukan kegiatan menghitung dan mengenal angka dalam aktivitas sehari-hari"],"umum":["Konsultasikan dengan dokter anak atau psikolog anak untuk evaluasi lebih lanjut","Pertimbangkan untuk mengikuti program stimulasi terpadu","Pastikan asupan gizi anak terpenuhi dengan baik","Berikan waktu istirahat dan tidur yang cukup"]}', '2025-05-07', NULL, NULL, NULL);
/*!40000 ALTER TABLE `assessment_results` ENABLE KEYS */;

-- Dumping structure for table deteksi.assessment_results12
CREATE TABLE IF NOT EXISTS `assessment_results12` (
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `assessment_date` date NOT NULL,
  `motorik_score` float DEFAULT NULL,
  `bahasa_score` float DEFAULT NULL,
  `kognitif_score` float DEFAULT NULL,
  `final_result` enum('normal','terlambat') NOT NULL,
  `recommendations` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`result_id`),
  KEY `student_id` (`student_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `assessment_results12_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  CONSTRAINT `assessment_results12_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table deteksi.assessment_results12: ~0 rows (approximately)
/*!40000 ALTER TABLE `assessment_results12` DISABLE KEYS */;
/*!40000 ALTER TABLE `assessment_results12` ENABLE KEYS */;

-- Dumping structure for table deteksi.data_aktual
CREATE TABLE IF NOT EXISTS `data_aktual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `nama` varchar(250) DEFAULT NULL,
  `status_aktual` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=latin1;

-- Dumping data for table deteksi.data_aktual: ~22 rows (approximately)
/*!40000 ALTER TABLE `data_aktual` DISABLE KEYS */;
INSERT INTO `data_aktual` (`id`, `student_id`, `nama`, `status_aktual`) VALUES
	(64, 10, 'CH', 'Perlu Pembelajaran Khusus'),
	(65, 11, 'KHA', 'Perlu Pembelajaran Khusus'),
	(66, 12, 'M I', 'Perlu Pembelajaran Khusus'),
	(67, 13, 'MD S', 'Perlu Pembelajaran Khusus'),
	(68, 14, 'M Z', 'Perlu Pembelajaran Khusus'),
	(69, 15, 'N D', 'Perlu Pembelajaran Khusus'),
	(70, 16, 'N S', 'Perlu Pembelajaran Khusus'),
	(71, 17, 'N S', 'Perlu Pembelajaran Khusus'),
	(72, 18, 'SH', 'Perlu Pembelajaran Khusus'),
	(75, 19, 'T M F', 'Perlu Pembelajaran Khusus'),
	(76, 21, 'abiyu', 'Normal'),
	(80, 22, 'kamila', 'Normal'),
	(90, 23, 'omer', 'Normal'),
	(92, 25, 'pocut', 'Normal'),
	(93, 24, 'nia', 'Normal'),
	(101, 2, 'mita zahara', 'Normal'),
	(102, 4, 'dea', 'Perlu Pembelajaran Khusus'),
	(103, 7, 'sora sinclair', 'Normal'),
	(104, 8, 'yunus yoseph', 'Normal'),
	(105, 9, 'abay', 'Normal'),
	(106, 3, 'mill', 'Perlu Pembelajaran Khusus'),
	(107, 1, 'atra', 'Perlu Pembelajaran Khusus');
/*!40000 ALTER TABLE `data_aktual` ENABLE KEYS */;

-- Dumping structure for table deteksi.model_metrics
CREATE TABLE IF NOT EXISTS `model_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total_predictions` int(11) NOT NULL,
  `true_positive` int(11) NOT NULL,
  `true_negative` int(11) NOT NULL,
  `false_positive` int(11) NOT NULL,
  `false_negative` int(11) NOT NULL,
  `accuracy` decimal(10,4) NOT NULL,
  `precision_val` decimal(10,4) NOT NULL,
  `recall` decimal(10,4) NOT NULL,
  `f1_score` decimal(10,4) NOT NULL,
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table deteksi.model_metrics: ~0 rows (approximately)
/*!40000 ALTER TABLE `model_metrics` DISABLE KEYS */;
INSERT INTO `model_metrics` (`id`, `total_predictions`, `true_positive`, `true_negative`, `false_positive`, `false_negative`, `accuracy`, `precision_val`, `recall`, `f1_score`, `last_updated`) VALUES
	(1, 22, 10, 4, 5, 3, 0.6364, 0.6667, 0.7692, 0.7143, '2025-05-06 01:13:06');
/*!40000 ALTER TABLE `model_metrics` ENABLE KEYS */;

-- Dumping structure for table deteksi.reports
CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `period` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

-- Dumping data for table deteksi.reports: ~3 rows (approximately)
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` (`id`, `type`, `period`, `content`, `created_at`) VALUES
	(1, 'monthly', 'Maret 2025', '\n                        <div class="text-center mb-4">\n                            <h2>Laporan Bulanan Assessment Siswa</h2>\n                            <h4 id="reportPeriodTitle">Maret 2025</h4>\n                        </div>\n                        \n                        <table class="table table-bordered">\n                            <thead>\n                                <tr>\n                                    <th>No</th>\n                                    <th>Nama Siswa</th>\n                                    <th>Tanggal</th>\n                                    <th>Status</th>\n                                    <th>Skor Motorik</th>\n                                    <th>Skor Kognitif</th>\n                                    <th>Skor Bahasa</th>\n                                </tr>\n                            </thead>\n                            <tbody id="monthlyReportData">\n                        <tr>\n                            <td>1</td>\n                            <td>atenk</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>2.3</td>\n                            <td>2.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>2</td>\n                            <td>mita zahara</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>4</td>\n                            <td>1.7</td>\n                            <td>1.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>3</td>\n                            <td>mill</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>2.7</td>\n                            <td>2.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>4</td>\n                            <td>dea</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>3</td>\n                            <td>2.7</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>5</td>\n                            <td>atra</td>\n                            <td>14/3/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>4</td>\n                            <td>3.3</td>\n                            <td>3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>6</td>\n                            <td>mill</td>\n                            <td>14/3/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>3.5</td>\n                            <td>3.3</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>7</td>\n                            <td>atra</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>1</td>\n                            <td>1.7</td>\n                            <td>1.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>8</td>\n                            <td>dea</td>\n                            <td>14/3/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>4</td>\n                            <td>3.7</td>\n                            <td>3.3</td>\n                        </tr>\n                    </tbody>\n                        </table>\n                    ', '2025-03-15 01:05:07'),
	(17, 'monthly', 'Maret 2025', '\n                        <div class="text-center mb-4">\n                            <h2>Laporan Bulanan Assessment Siswa</h2>\n                            <h4 id="reportPeriodTitle">Maret 2025</h4>\n                        </div>\n                        \n                        <table class="table table-bordered">\n                            <thead>\n                                <tr>\n                                    <th>No</th>\n                                    <th>Nama Siswa</th>\n                                    <th>Tanggal</th>\n                                    <th>Status</th>\n                                    <th>Skor Motorik</th>\n                                    <th>Skor Kognitif</th>\n                                    <th>Skor Bahasa</th>\n                                </tr>\n                            </thead>\n                            <tbody id="monthlyReportData">\n                        <tr>\n                            <td>1</td>\n                            <td>atenk</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>2.3</td>\n                            <td>2.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>2</td>\n                            <td>mita zahara</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>4</td>\n                            <td>1.7</td>\n                            <td>1.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>3</td>\n                            <td>mill</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>2.7</td>\n                            <td>2.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>4</td>\n                            <td>dea</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>3</td>\n                            <td>2.7</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>5</td>\n                            <td>atra</td>\n                            <td>14/3/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>4</td>\n                            <td>3.3</td>\n                            <td>3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>6</td>\n                            <td>mill</td>\n                            <td>14/3/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>3.5</td>\n                            <td>3.3</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>7</td>\n                            <td>atra</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>1</td>\n                            <td>1.7</td>\n                            <td>1.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>8</td>\n                            <td>dea</td>\n                            <td>14/3/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>4</td>\n                            <td>3.7</td>\n                            <td>3.3</td>\n                        </tr>\n                    </tbody>\n                        </table>\n                    ', '2025-03-15 02:17:49'),
	(18, 'monthly', 'Maret 2025', '\n                        <div class="text-center mb-4">\n                            <h2>Laporan Bulanan Assessment Siswa</h2>\n                            <h4 id="reportPeriodTitle">Maret 2025</h4>\n                        </div>\n                        \n                        <table class="table table-bordered">\n                            <thead>\n                                <tr>\n                                    <th>No</th>\n                                    <th>Nama Siswa</th>\n                                    <th>Tanggal</th>\n                                    <th>Status</th>\n                                    <th>Skor Motorik</th>\n                                    <th>Skor Kognitif</th>\n                                    <th>Skor Bahasa</th>\n                                </tr>\n                            </thead>\n                            <tbody id="monthlyReportData">\n                        <tr>\n                            <td>1</td>\n                            <td>atenk</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>2.3</td>\n                            <td>2.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>2</td>\n                            <td>mita zahara</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>4</td>\n                            <td>1.7</td>\n                            <td>1.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>3</td>\n                            <td>mill</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>2.7</td>\n                            <td>2.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>4</td>\n                            <td>dea</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>3</td>\n                            <td>2.7</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>5</td>\n                            <td>atra</td>\n                            <td>14/3/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>4</td>\n                            <td>3.3</td>\n                            <td>3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>6</td>\n                            <td>mill</td>\n                            <td>14/3/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>3.5</td>\n                            <td>3.3</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>7</td>\n                            <td>atra</td>\n                            <td>14/3/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>1</td>\n                            <td>1.7</td>\n                            <td>1.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>8</td>\n                            <td>dea</td>\n                            <td>14/3/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>4</td>\n                            <td>3.7</td>\n                            <td>3.3</td>\n                        </tr>\n                    </tbody>\n                        </table>\n                    ', '2025-03-15 02:37:51'),
	(19, 'monthly', 'April 2025', '\n                        <div class="text-center mb-4">\n                            <h2>Laporan Bulanan Assessment Siswa</h2>\n                            <h4 id="reportPeriodTitle">April 2025</h4>\n                        </div>\n                        \n                        <table class="table table-bordered">\n                            <thead>\n                                <tr>\n                                    <th>No</th>\n                                    <th>Nama Siswa</th>\n                                    <th>Tanggal</th>\n                                    <th>Status</th>\n                                    <th>Skor Motorik</th>\n                                    <th>Skor Kognitif</th>\n                                    <th>Skor Bahasa</th>\n                                </tr>\n                            </thead>\n                            <tbody id="monthlyReportData">\n                        <tr>\n                            <td>1</td>\n                            <td>T M F</td>\n                            <td>25/4/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>3.5</td>\n                            <td>3</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>2</td>\n                            <td>atra</td>\n                            <td>24/4/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>3.5</td>\n                            <td>3</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>3</td>\n                            <td>atenk</td>\n                            <td>24/4/2025</td>\n                            <td class="text-success">Normal</td>\n                            <td>3</td>\n                            <td>3</td>\n                            <td>3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>4</td>\n                            <td>wanda</td>\n                            <td>24/4/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>2.5</td>\n                            <td>1</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>5</td>\n                            <td>KHA</td>\n                            <td>23/4/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>1.7</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>6</td>\n                            <td>M I</td>\n                            <td>23/4/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>4</td>\n                            <td>1.7</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>7</td>\n                            <td>MD S</td>\n                            <td>23/4/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>4</td>\n                            <td>1</td>\n                            <td>3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>8</td>\n                            <td>M Z</td>\n                            <td>23/4/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>4</td>\n                            <td>2</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>9</td>\n                            <td>N D</td>\n                            <td>23/4/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3</td>\n                            <td>1.7</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>10</td>\n                            <td>N S</td>\n                            <td>23/4/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>1.3</td>\n                            <td>3.3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>11</td>\n                            <td>N S</td>\n                            <td>23/4/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>4</td>\n                            <td>1</td>\n                            <td>3</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>12</td>\n                            <td>SH</td>\n                            <td>23/4/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3.5</td>\n                            <td>1.3</td>\n                            <td>3.7</td>\n                        </tr>\n                    \n                        <tr>\n                            <td>13</td>\n                            <td>CH</td>\n                            <td>20/4/2025</td>\n                            <td class="text-danger">Perlu Pembelajaran Khusus</td>\n                            <td>3</td>\n                            <td>1.7</td>\n                            <td>3.3</td>\n                        </tr>\n                    </tbody>\n                        </table>\n                    ', '2025-04-29 22:23:32');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;

-- Dumping structure for table deteksi.students
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `usia` int(11) NOT NULL DEFAULT '0',
  `gender` enum('L','P') NOT NULL,
  `class` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;

-- Dumping data for table deteksi.students: ~22 rows (approximately)
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` (`student_id`, `parent_id`, `name`, `usia`, `gender`, `class`) VALUES
	(1, 5, 'atra', 0, 'L', 'III-A'),
	(2, 4, 'mita zahara', 22, 'P', '1'),
	(3, 23, 'mill', 25, 'L', '1'),
	(4, 2, 'dea', 0, 'P', 'XII-A'),
	(7, 6, 'sora sinclair', 0, 'P', 'VI-A'),
	(8, 24, 'yunus yoseph', 23, 'L', '1'),
	(9, 7, 'abay', 0, 'L', 'XI-C'),
	(10, 15, 'CH', 23, 'L', '1'),
	(11, 8, 'KHA', 17, 'L', 'I C'),
	(12, 10, 'M I', 19, 'L', 'IX C'),
	(13, 9, 'MD S', 17, 'P', 'IX C'),
	(14, 11, 'M Z', 11, 'L', 'IV C'),
	(15, 12, 'N D', 16, 'P', 'VIII C'),
	(16, 14, 'N S', 17, 'L', 'VII C'),
	(17, 13, 'N S', 16, 'P', 'VIII C'),
	(18, 16, 'SH', 13, 'P', 'IV C'),
	(19, 17, 'T M F', 19, 'L', 'VII C'),
	(21, 18, 'abiyu', 5, 'L', 'TK'),
	(22, 19, 'kamila', 6, 'P', 'TK'),
	(23, 21, 'omer', 6, 'L', 'TK'),
	(24, 20, 'nia', 5, 'P', 'TK'),
	(25, 22, 'pocut', 4, 'P', 'TK');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;

-- Dumping structure for table deteksi.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `level` int(11) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `alamat` varchar(300) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

-- Dumping data for table deteksi.users: ~24 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`user_id`, `fullname`, `username`, `password`, `level`, `phone`, `alamat`, `created_at`) VALUES
	(1, 'riski sarma', 'riski@gmail.com', '0192023a7bbd73250516f069df18b500', 1, '0823123123', 'tambon', '2025-01-30 00:00:00'),
	(2, 'baskoro', 'bas@gmail.com', '0192023a7bbd73250516f069df18b500', 2, '082313124', 'ciracas', '2025-01-30 00:00:00'),
	(3, 'Marcel Bas', 'marcel@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 1, '08231231221', 'ciracas', '2025-02-02 15:43:34'),
	(4, 'diego', 'diego@gmail.com', '0192023a7bbd73250516f069df18b500', 2, '082142131', 'los santos\r\n', '2025-02-02 21:20:31'),
	(5, 'Leonardo', 'leo@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '082142131312', 'san andreas', '2025-02-02 21:48:17'),
	(6, 'opung', 'opung@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '082914123113', 'jatinagor\r\n', '2025-03-16 15:36:09'),
	(7, 'furqon', 'furqon@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '08123242112', 'bsd', '2025-03-17 05:31:05'),
	(8, 'Budi Santoso', 'budi@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '081234567890', 'Jl. Melati No. 12, Jakarta Selatan', '2025-04-23 08:54:22'),
	(9, 'Siti Aminah ', 'siti@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '081399887766', 'Jl. Melur No. 3, Jakarta Timur', '2025-04-23 08:55:16'),
	(10, 'Agus Pranoto', 'agus@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '082198765432', 'Jl. Kenanga No. 5, Bandung', '2025-04-23 08:57:46'),
	(11, 'Dewi Lestari', 'dewi@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '082355667788', 'Jl. Kemuning No. 7, Bandung', '2025-04-23 08:58:48'),
	(12, 'Sumarno Hidayat', 'sumarno@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '085611223344', 'Jl. Cemara No. 88, Yogyakarta', '2025-04-23 09:00:17'),
	(13, 'Nurhayati ', 'nurhayati@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '085788886666', 'Jl. Angsana No. 1, Yogyakarta', '2025-04-23 09:01:01'),
	(14, 'Dedi Supriyadi', 'dedi@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '081322114455', 'Jl. Mawar No. 20, Bekasi', '2025-04-23 09:01:41'),
	(15, 'Yuniarti Wahyuni', 'yuniarti@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '81222334454', 'Jl. Seruni No. 12, Bekasi', '2025-04-23 09:02:24'),
	(16, 'Wahyu Nugroho', 'wahyu@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '087766557788', 'Jl. Merpati No. 9, Surabaya', '2025-04-23 09:24:27'),
	(17, 'Sri Rahayu', 'sri@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '087833445566', 'Jl. Pandan No. 18, Surabaya', '2025-04-23 09:24:59'),
	(18, 'Hendra Saputra', 'hendra@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '089633445566', 'Jl. Teratai No. 33, Semarang', '2025-05-05 20:36:00'),
	(19, 'Indah Permatasari', 'indah@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '089977885566', 'Jl. Wijaya Kusuma No. 5, Semarang', '2025-05-05 20:40:20'),
	(20, 'Bambang Riyanto', 'bambang@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '081945678901', 'Jl. Anggrek No. 10, Malang', '2025-05-05 20:42:37'),
	(21, 'Rina Marlina', 'rina@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '081199881122', 'Jl. Ketapang No. 21, Malang', '2025-05-05 20:45:26'),
	(22, 'Sutrisno Adi', 'sutrisno@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '085290908080', 'Jl. Dahlia No. 2, Palembang', '2025-05-05 20:48:28'),
	(23, 'Fitriani ', 'fitri@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '082144337788', 'Jl. Kamboja No. 19, Palembang', '2025-05-05 22:05:03'),
	(24, 'Joko Purnomo', 'joko@gmail.com', '5f4dcc3b5aa765d61d8327deb882cf99', 2, '082233447788', 'Jl. Flamboyan No. 6, Depok', '2025-05-05 22:09:47');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
