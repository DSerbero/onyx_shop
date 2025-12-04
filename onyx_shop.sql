/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.3-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: onyx_shop
-- ------------------------------------------------------
-- Server version	11.8.3-MariaDB-1+b1 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `documento` bigint(20) NOT NULL,
  `nombre` tinytext NOT NULL,
  `direccion` tinytext NOT NULL,
  `telefono` bigint(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `ref1` text DEFAULT NULL,
  `ref2` text DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `documento` (`documento`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `clientes` VALUES
(1,1046527725,'Israel Quiñones','Calle 99C #76 - 35',3195739773,'israeldqz301@gmail.com',NULL,NULL),
(14,112123,'asdasddas','asdasd',1212312332,'asdasdaq@adsdaasd.cas','asdasd','sddas');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(100) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `categoria` enum('importada','nacional') NOT NULL,
  `tipo_de_producto` enum('jean','camisa','blusa','medias','tenis','maquillaje','ropa interior','accesorio') NOT NULL,
  `costo` bigint(20) NOT NULL,
  `venta` bigint(20) NOT NULL,
  `cantidad` bigint(20) NOT NULL,
  `cantidad_minima` int(4) NOT NULL,
  PRIMARY KEY (`id_producto`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `productos` VALUES
(1,'CA_70','Camisa tipo polo','importada','camisa',50000,70000,10,1),
(3,'005','camiseta hombre 65','importada','camisa',65000,90000,12,12);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cargo` varchar(255) NOT NULL DEFAULT 'admin',
  `correo` varchar(255) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `usuarios` VALUES
(1,'admin','israeldqz301@gmail.com','$2y$10$7RIRrNeR6DnLki3FipJrNOE1pGKDQZ6Sn155JgNdsY8g4UUi4Tev6');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente` int(11) NOT NULL,
  `productos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`productos`)),
  `tipo_pago` text NOT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_venta`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `ventas` VALUES
(1,1,'{\"005\":1}','efectivo,90000','2025-11-26 23:54:55'),
(2,1,'{\"CA_70\":1}','efectivo,70000','2025-12-02 01:23:28'),
(3,14,'[]','efectivo,0','2025-12-02 01:23:38');
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
commit;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-12-03 19:54:51
