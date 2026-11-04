-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: mini_erp
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `mini_erp`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `mini_erp` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `mini_erp`;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Bebidas','Produtos de Bebidas','2026-11-04 22:44:10','2026-11-04 22:44:10'),(2,'Mercearia','Produtos de Mercearia','2026-11-04 22:44:10','2026-11-04 22:44:10'),(3,'Limpeza','Produtos de Limpeza','2026-11-04 22:44:10','2026-11-04 22:44:10'),(4,'Higiene','Produtos de Higiene','2026-11-04 22:44:10','2026-11-04 22:44:10');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf_cnpj` varchar(18) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consumidor_final` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_cpf_cnpj_unique` (`cpf_cnpj`),
  UNIQUE KEY `clientes_consumidor_final_unique` (`consumidor_final`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Consumidor final',NULL,NULL,NULL,1,'2026-11-04 22:44:09','2026-11-04 22:44:09'),(2,'Inácio Neves','854.846.339-23','(33) 90996-2767','jonas02@example.net',NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(3,'Maísa Paz','486.639.716-05','(75) 97221-3376','juliano.lutero@example.net',NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(4,'Emily Lutero','510.702.170-00','(66) 92717-9393','vrangel@example.org',NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(5,'Willian Vega','799.394.597-69','(71) 92731-2197','deivid42@example.com',NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(6,'Giovana Padilha','212.428.614-59','(21) 97411-2625','razevedo@example.net',NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(7,'Dante Vasques','504.620.931-40','(28) 92705-7893','balestero.mauro@example.net',NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(8,'Heitor Dominato','045.490.909-87','(44) 91150-4228','vale.diego@example.com',NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(9,'Luis Lira','652.996.664-11','(32) 90241-6171','iazevedo@example.net',NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fornecedores`
--

DROP TABLE IF EXISTS `fornecedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fornecedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cnpj` varchar(18) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fornecedores_cnpj_unique` (`cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fornecedores`
--

LOCK TABLES `fornecedores` WRITE;
/*!40000 ALTER TABLE `fornecedores` DISABLE KEYS */;
INSERT INTO `fornecedores` VALUES (1,'Padilha e Serrano e Filhos','63.932.070/0001-06','(82) 4333-6900','cristian82@deoliveira.com','2026-11-04 22:44:10','2026-11-04 22:44:10'),(2,'Salgado-Quintana','18.520.425/0001-97','(79) 2100-0092','leticia27@barreto.net','2026-11-04 22:44:10','2026-11-04 22:44:10'),(3,'Lozano-Fidalgo','44.285.924/0001-57','(79) 2588-7550','mauricio86@burgos.com.br','2026-11-04 22:44:10','2026-11-04 22:44:10');
/*!40000 ALTER TABLE `fornecedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_01_000001_create_categorias_table',1),(5,'2026_01_01_000002_create_fornecedores_table',1),(6,'2026_01_01_000003_create_clientes_table',1),(7,'2026_01_01_000004_create_produtos_table',1),(8,'2026_01_02_000001_add_estoque_minimo_to_produtos_table',1),(9,'2026_01_02_000002_add_unique_documentos',1),(10,'2026_01_02_000003_create_vendas_table',1),(11,'2026_01_02_000004_create_venda_itens_table',1),(12,'2026_01_02_000005_create_movimentacoes_estoque_table',1),(13,'2026_09_29_000001_add_consumidor_final_to_clientes',1),(14,'2026_09_30_000001_add_custo_to_produtos',1),(15,'2026_09_30_000002_add_custo_unitario_to_venda_itens',1),(16,'2026_09_30_000003_add_pagamento_e_desconto_to_vendas',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimentacoes_estoque`
--

DROP TABLE IF EXISTS `movimentacoes_estoque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimentacoes_estoque` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `produto_id` bigint unsigned NOT NULL,
  `tipo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade` int NOT NULL,
  `estoque_apos` int NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `venda_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimentacoes_estoque_produto_id_foreign` (`produto_id`),
  KEY `movimentacoes_estoque_venda_id_foreign` (`venda_id`),
  KEY `movimentacoes_estoque_user_id_foreign` (`user_id`),
  CONSTRAINT `movimentacoes_estoque_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movimentacoes_estoque_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimentacoes_estoque_venda_id_foreign` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimentacoes_estoque`
--

LOCK TABLES `movimentacoes_estoque` WRITE;
/*!40000 ALTER TABLE `movimentacoes_estoque` DISABLE KEYS */;
INSERT INTO `movimentacoes_estoque` VALUES (1,1,'entrada',120,120,'Estoque inicial',NULL,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(2,2,'entrada',40,40,'Estoque inicial',NULL,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(3,3,'entrada',4,4,'Estoque inicial',NULL,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(4,4,'entrada',35,35,'Estoque inicial',NULL,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(5,5,'entrada',50,50,'Estoque inicial',NULL,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(6,6,'entrada',3,3,'Estoque inicial',NULL,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(7,7,'entrada',80,80,'Estoque inicial',NULL,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(8,8,'entrada',25,25,'Estoque inicial',NULL,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(9,9,'entrada',100,100,'Estoque inicial',NULL,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(10,2,'saida',2,38,'Venda #1',1,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(11,7,'saida',3,77,'Venda #2',2,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(12,1,'saida',3,117,'Venda #3',3,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(13,5,'saida',1,49,'Venda #3',3,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(14,9,'saida',3,97,'Venda #3',3,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(15,7,'saida',3,74,'Venda #4',4,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(16,2,'saida',3,35,'Venda #4',4,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(17,1,'saida',2,115,'Venda #5',5,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(18,8,'saida',1,24,'Venda #6',6,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(19,4,'saida',2,33,'Venda #6',6,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(20,1,'saida',2,113,'Venda #6',6,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(21,8,'saida',2,22,'Venda #7',7,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(22,9,'saida',3,94,'Venda #7',7,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(23,7,'saida',1,73,'Venda #8',8,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(24,8,'saida',3,19,'Venda #8',8,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(25,2,'saida',1,34,'Venda #8',8,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(26,8,'saida',2,17,'Venda #9',9,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(27,2,'saida',1,33,'Venda #10',10,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(28,4,'saida',3,30,'Venda #10',10,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(29,8,'saida',3,14,'Venda #10',10,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(30,7,'saida',3,70,'Venda #11',11,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(31,1,'saida',1,112,'Venda #11',11,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(32,2,'saida',3,30,'Venda #11',11,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(33,1,'saida',3,109,'Venda #12',12,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(34,9,'saida',3,91,'Venda #12',12,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(35,4,'saida',1,29,'Venda #13',13,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(36,1,'saida',1,108,'Venda #13',13,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(37,8,'saida',3,11,'Venda #13',13,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(38,1,'saida',2,106,'Venda #14',14,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(39,4,'saida',3,26,'Venda #15',15,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(40,1,'saida',3,103,'Venda #15',15,1,'2026-11-04 22:44:10','2026-11-04 22:44:10');
/*!40000 ALTER TABLE `movimentacoes_estoque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produtos`
--

DROP TABLE IF EXISTS `produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produtos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `preco` decimal(10,2) NOT NULL DEFAULT '0.00',
  `custo` decimal(10,2) DEFAULT NULL,
  `estoque` int NOT NULL DEFAULT '0',
  `estoque_minimo` int NOT NULL DEFAULT '5',
  `categoria_id` bigint unsigned NOT NULL,
  `fornecedor_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produtos_categoria_id_foreign` (`categoria_id`),
  KEY `produtos_fornecedor_id_foreign` (`fornecedor_id`),
  CONSTRAINT `produtos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `produtos_fornecedor_id_foreign` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `produtos_custo_nao_negativo` CHECK (((`custo` is null) or (`custo` >= 0)))
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produtos`
--

LOCK TABLES `produtos` WRITE;
/*!40000 ALTER TABLE `produtos` DISABLE KEYS */;
INSERT INTO `produtos` VALUES (1,'Água mineral 500ml',NULL,2.50,1.38,103,30,1,3,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(2,'Suco de laranja 1L',NULL,8.90,5.34,30,10,1,3,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(3,'Refrigerante 2L',NULL,10.50,6.83,4,10,1,3,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(4,'Arroz 5kg',NULL,27.90,15.35,26,10,2,2,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(5,'Feijão carioca 1kg',NULL,8.49,5.09,49,15,2,1,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(6,'Café 500g',NULL,18.90,12.29,3,8,2,2,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(7,'Detergente 500ml',NULL,2.99,1.64,70,20,3,2,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(8,'Sabão em pó 1kg',NULL,14.90,8.94,11,10,3,3,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(9,'Sabonete 90g',NULL,2.29,1.26,91,25,4,2,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(10,'Creme dental 90g',NULL,4.99,2.99,0,10,4,3,'2026-11-04 22:44:10','2026-11-04 22:44:10');
/*!40000 ALTER TABLE `produtos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrador','admin@minierp.com',NULL,'$2y$12$tEO8pespkBtCPe.dGnvVpeQ2ElaVievTbhoI.njqkAyljDZvnFmXu',NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `venda_itens`
--

DROP TABLE IF EXISTS `venda_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `venda_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venda_id` bigint unsigned NOT NULL,
  `produto_id` bigint unsigned NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `custo_unitario` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `venda_itens_venda_id_foreign` (`venda_id`),
  KEY `venda_itens_produto_id_foreign` (`produto_id`),
  CONSTRAINT `venda_itens_produto_id_foreign` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `venda_itens_venda_id_foreign` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `venda_itens_custo_nao_negativo` CHECK (((`custo_unitario` is null) or (`custo_unitario` >= 0)))
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `venda_itens`
--

LOCK TABLES `venda_itens` WRITE;
/*!40000 ALTER TABLE `venda_itens` DISABLE KEYS */;
INSERT INTO `venda_itens` VALUES (1,1,2,2,8.90,5.34,17.80,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(2,2,7,3,2.99,1.64,8.97,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(3,3,1,3,2.50,1.38,7.50,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(4,3,5,1,8.49,5.09,8.49,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(5,3,9,3,2.29,1.26,6.87,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(6,4,7,3,2.99,1.64,8.97,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(7,4,2,3,8.90,5.34,26.70,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(8,5,1,2,2.50,1.38,5.00,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(9,6,8,1,14.90,8.94,14.90,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(10,6,4,2,27.90,15.35,55.80,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(11,6,1,2,2.50,1.38,5.00,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(12,7,8,2,14.90,8.94,29.80,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(13,7,9,3,2.29,1.26,6.87,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(14,8,7,1,2.99,1.64,2.99,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(15,8,8,3,14.90,8.94,44.70,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(16,8,2,1,8.90,5.34,8.90,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(17,9,8,2,14.90,8.94,29.80,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(18,10,2,1,8.90,5.34,8.90,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(19,10,4,3,27.90,15.35,83.70,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(20,10,8,3,14.90,8.94,44.70,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(21,11,7,3,2.99,1.64,8.97,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(22,11,1,1,2.50,1.38,2.50,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(23,11,2,3,8.90,5.34,26.70,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(24,12,1,3,2.50,1.38,7.50,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(25,12,9,3,2.29,1.26,6.87,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(26,13,4,1,27.90,15.35,27.90,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(27,13,1,1,2.50,1.38,2.50,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(28,13,8,3,14.90,8.94,44.70,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(29,14,1,2,2.50,1.38,5.00,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(30,15,4,3,27.90,15.35,83.70,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(31,15,1,3,2.50,1.38,7.50,'2026-11-04 22:44:10','2026-11-04 22:44:10');
/*!40000 ALTER TABLE `venda_itens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendas`
--

DROP TABLE IF EXISTS `vendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `data` date NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'concluida',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `desconto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `forma_pagamento` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendas_cliente_id_foreign` (`cliente_id`),
  KEY `vendas_user_id_foreign` (`user_id`),
  KEY `vendas_data_index` (`data`),
  CONSTRAINT `vendas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `vendas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendas_desconto_valido` CHECK (((`desconto` >= 0) and (`desconto` <= `subtotal`))),
  CONSTRAINT `vendas_forma_pagamento_valida` CHECK (((`forma_pagamento` is null) or (`forma_pagamento` in (_utf8mb4'dinheiro',_utf8mb4'pix',_utf8mb4'debito',_utf8mb4'credito')))),
  CONSTRAINT `vendas_total_confere` CHECK ((`total` = (`subtotal` - `desconto`)))
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendas`
--

LOCK TABLES `vendas` WRITE;
/*!40000 ALTER TABLE `vendas` DISABLE KEYS */;
INSERT INTO `vendas` VALUES (1,6,1,'2026-10-28','concluida',17.80,0.00,'debito',17.80,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(2,2,1,'2026-11-03','concluida',8.97,0.00,'credito',8.97,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(3,2,1,'2026-10-23','concluida',22.86,0.00,'credito',22.86,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(4,8,1,'2026-10-31','concluida',35.67,0.00,'debito',35.67,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(5,4,1,'2026-10-26','concluida',5.00,0.00,'dinheiro',5.00,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(6,6,1,'2026-10-30','concluida',75.70,0.00,'debito',75.70,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(7,5,1,'2026-10-24','concluida',36.67,0.00,'credito',36.67,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(8,7,1,'2026-11-02','concluida',56.59,0.00,'debito',56.59,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(9,8,1,'2026-10-29','concluida',29.80,0.00,'dinheiro',29.80,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(10,2,1,'2026-10-24','concluida',137.30,0.00,'credito',137.30,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(11,3,1,'2026-11-02','concluida',38.17,0.00,'debito',38.17,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(12,3,1,'2026-10-24','concluida',14.37,0.00,'debito',14.37,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(13,8,1,'2026-10-30','concluida',75.10,0.00,'pix',75.10,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(14,7,1,'2026-11-01','concluida',5.00,0.00,'credito',5.00,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10'),(15,9,1,'2026-10-30','concluida',91.20,0.00,'credito',91.20,NULL,'2026-11-04 22:44:10','2026-11-04 22:44:10');
/*!40000 ALTER TABLE `vendas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-04 19:45:53
