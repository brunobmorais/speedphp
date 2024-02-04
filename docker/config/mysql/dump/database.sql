-- MySQL dump 10.13  Distrib 8.0.36, for macos14 (arm64)
--
-- Host: localhost    Database: database
-- ------------------------------------------------------
-- Server version	8.2.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `MODULO`
--

DROP TABLE IF EXISTS `MODULO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `MODULO` (
  `CODMODULO` int NOT NULL AUTO_INCREMENT,
  `TITULO` varchar(100) DEFAULT NULL,
  `DESCRICAO` varchar(200) DEFAULT NULL,
  `ICONE` varchar(100) DEFAULT NULL,
  `CONTROLLER` varchar(200) DEFAULT NULL,
  `ORDEM` int DEFAULT '1',
  `SITUACAO` int DEFAULT '1',
  `EXCLUIDO` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`CODMODULO`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `MODULO`
--

LOCK TABLES `MODULO` WRITE;
/*!40000 ALTER TABLE `MODULO` DISABLE KEYS */;
INSERT INTO `MODULO` VALUES (1,'Configurações','Cadastro de informações base do sistema','mdi-cog-outline','configuracoes',1,1,0),(2,'Módulo 2','Teste','mdi-account-outline','tete',1,1,0),(3,'Módulo 3','Teste','mdi-account-outline','teste',1,1,0),(4,'Módulo 4','Teste','mdi-account-outline','tete',1,1,0),(5,'Gestão de Pessoas','Cadastro de funcionários','mdi-account-group','gestaopessoas',1,1,0);
/*!40000 ALTER TABLE `MODULO` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PERFIL`
--

DROP TABLE IF EXISTS `PERFIL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PERFIL` (
  `CODPERFIL` int NOT NULL AUTO_INCREMENT,
  `NOME` varchar(100) NOT NULL,
  `NIVEL` int NOT NULL DEFAULT '10',
  `EXCLUIDO` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`CODPERFIL`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PERFIL`
--

LOCK TABLES `PERFIL` WRITE;
/*!40000 ALTER TABLE `PERFIL` DISABLE KEYS */;
INSERT INTO `PERFIL` VALUES (1,'EXTERNO',1,0),(2,'TÉCNICO',10,0),(3,'COORDENADOR',20,0),(4,'GERENTE',30,0),(5,'DIRETOR',40,0),(6,'PRESIDENTE',50,0);
/*!40000 ALTER TABLE `PERFIL` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PERFIL_USUARIO`
--

DROP TABLE IF EXISTS `PERFIL_USUARIO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PERFIL_USUARIO` (
  `CODPERFIL_USUARIO` int NOT NULL AUTO_INCREMENT,
  `CODUSUARIO` int NOT NULL,
  `CODPERFIL` int NOT NULL,
  `EXCLUIDO` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`CODPERFIL_USUARIO`),
  KEY `fk_PERFIL_USUARIO_USUARIO1_idx` (`CODUSUARIO`),
  KEY `fk_PERFIL_USUARIO_PERFIL1_idx` (`CODPERFIL`),
  CONSTRAINT `fk_PERFIL_USUARIO_USUARIO1` FOREIGN KEY (`CODUSUARIO`) REFERENCES `USUARIO` (`CODUSUARIO`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PERFIL_USUARIO`
--

LOCK TABLES `PERFIL_USUARIO` WRITE;
/*!40000 ALTER TABLE `PERFIL_USUARIO` DISABLE KEYS */;
INSERT INTO `PERFIL_USUARIO` VALUES (1,1,6,0);
/*!40000 ALTER TABLE `PERFIL_USUARIO` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PESSOA`
--

DROP TABLE IF EXISTS `PESSOA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PESSOA` (
  `CODPESSOA` int NOT NULL AUTO_INCREMENT,
  `NOME` varchar(200) DEFAULT NULL,
  `CPF` varchar(45) DEFAULT NULL,
  `EMAIL` varchar(200) DEFAULT NULL,
  `EXCLUIDO` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`CODPESSOA`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PESSOA`
--

LOCK TABLES `PESSOA` WRITE;
/*!40000 ALTER TABLE `PESSOA` DISABLE KEYS */;
INSERT INTO `PESSOA` VALUES (1,'Usuario Teste','00000000000','brunomoraisti@gmail.com',0);
/*!40000 ALTER TABLE `PESSOA` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `PRIVILEGIO`
--

DROP TABLE IF EXISTS `PRIVILEGIO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `PRIVILEGIO` (
  `CODPRIVILEGIO` int NOT NULL AUTO_INCREMENT,
  `CODSERVICO` int NOT NULL,
  `CODPERFIL` int NOT NULL,
  `EXCLUIR` int NOT NULL DEFAULT '0',
  `LER` int NOT NULL DEFAULT '0',
  `SALVAR` int NOT NULL DEFAULT '0',
  `ALTERAR` int NOT NULL DEFAULT '0',
  `OUTROS` int NOT NULL DEFAULT '0',
  `EXCLUIDO` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`CODPRIVILEGIO`),
  KEY `FK_PRIVILEGIO_SERVICO_idx` (`CODSERVICO`),
  KEY `fk_PRIVILEGIO_PERFIL1_idx` (`CODPERFIL`),
  CONSTRAINT `FK_PRIVILEGIO_SERVICO` FOREIGN KEY (`CODSERVICO`) REFERENCES `SERVICO` (`CODSERVICO`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PRIVILEGIO`
--

LOCK TABLES `PRIVILEGIO` WRITE;
/*!40000 ALTER TABLE `PRIVILEGIO` DISABLE KEYS */;
INSERT INTO `PRIVILEGIO` VALUES (62,1,6,1,1,1,1,1,0),(63,2,6,1,1,1,1,1,0),(64,3,6,1,1,1,1,1,0),(65,4,6,1,1,1,1,1,0),(66,5,6,1,1,1,1,1,0);
/*!40000 ALTER TABLE `PRIVILEGIO` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `SERVICO`
--

DROP TABLE IF EXISTS `SERVICO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `SERVICO` (
  `CODSERVICO` int NOT NULL AUTO_INCREMENT,
  `CODMODULO` int DEFAULT NULL,
  `TITULO` varchar(100) DEFAULT NULL,
  `DESCRICAO` varchar(200) DEFAULT NULL,
  `ICONE` varchar(100) DEFAULT NULL,
  `CONTROLLER` varchar(100) DEFAULT NULL,
  `ORDEM` int NOT NULL DEFAULT '1',
  `SITUACAO` int NOT NULL DEFAULT '1',
  `EXCLUIDO` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`CODSERVICO`),
  KEY `FK_SERVICO_MODULO_idx` (`CODMODULO`),
  CONSTRAINT `FK_SERVICO_MODULO` FOREIGN KEY (`CODMODULO`) REFERENCES `MODULO` (`CODMODULO`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `SERVICO`
--

LOCK TABLES `SERVICO` WRITE;
/*!40000 ALTER TABLE `SERVICO` DISABLE KEYS */;
INSERT INTO `SERVICO` VALUES (1,1,'Módulos','modulos','mdi mdi-archive-plus-outline','modulos',1,1,0),(2,1,'Serviços','Serviços','mdi mdi-archive-plus-outline','servicos',1,1,0),(3,1,'Privilégio Perfil','Privilégios','mdi mdi-archive-plus-outline','privilegios',1,1,0),(4,5,'Funcionários','Cadastro de funcionários','mdi-account','funcionarios',1,1,0),(5,1,'Perfils','Pefil de usuários','mdi mdi-archive-plus-outline','perfil',1,1,0);
/*!40000 ALTER TABLE `SERVICO` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `USUARIO`
--

DROP TABLE IF EXISTS `USUARIO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `USUARIO` (
  `CODUSUARIO` int NOT NULL AUTO_INCREMENT,
  `CODPESSOA` int NOT NULL,
  `SENHA` varchar(200) DEFAULT NULL,
  `SITUACAO` int NOT NULL DEFAULT '0',
  `DATACADASTRO` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `EXCLUIDO` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`CODUSUARIO`),
  KEY `FK_USUARIO_PESSOA_idx` (`CODPESSOA`),
  CONSTRAINT `FK_USUARIO_PESSOA` FOREIGN KEY (`CODPESSOA`) REFERENCES `PESSOA` (`CODPESSOA`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `USUARIO`
--

LOCK TABLES `USUARIO` WRITE;
/*!40000 ALTER TABLE `USUARIO` DISABLE KEYS */;
INSERT INTO `USUARIO` VALUES (1,1,'$2y$10$H92r2Spv1CAI4Gqu.d0xRO9eJsxbeskj8QyuRCHypq9aRtjCWn8M.',1,'2023-01-01 00:00:00',0);
/*!40000 ALTER TABLE `USUARIO` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-02-03 19:26:39
