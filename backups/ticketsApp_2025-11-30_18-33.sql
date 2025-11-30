-- MySQL dump 10.13  Distrib 9.2.0, for Linux (x86_64)
--
-- Host: localhost    Database: ticketsApp
-- ------------------------------------------------------
-- Server version	9.2.0

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
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `attachment_id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`attachment_id`),
  KEY `ticket_id` (`ticket_id`),
  CONSTRAINT `fk_attachments_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`ticket_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `message_sender` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `message_receiver` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `message_sent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_sent` datetime NOT NULL,
  `ticket_id` int NOT NULL,
  PRIMARY KEY (`message_id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `message_sender` (`message_sender`),
  KEY `message_receiver` (`message_receiver`),
  CONSTRAINT `fk_chat_receiver` FOREIGN KEY (`message_receiver`) REFERENCES `user` (`username`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_chat_sender` FOREIGN KEY (`message_sender`) REFERENCES `user` (`username`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_chat_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`ticket_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evenement`
--

DROP TABLE IF EXISTS `evenement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evenement` (
  `evenement_id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `titre` varchar(255) NOT NULL,
  `date_evenement` datetime NOT NULL,
  `statut_evenement_id` int NOT NULL,
  `username` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `description` text,
  `produit_id` int NOT NULL,
  PRIMARY KEY (`evenement_id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `statut_evenement_id` (`statut_evenement_id`),
  KEY `produit_id` (`produit_id`),
  KEY `fk_username` (`username`),
  CONSTRAINT `fk_evenement_produit` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`produit_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_evenement_statut` FOREIGN KEY (`statut_evenement_id`) REFERENCES `statut_evenement` (`statut_evenement_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_evenement_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`ticket_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_evenement_user` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evenement`
--

LOCK TABLES `evenement` WRITE;
/*!40000 ALTER TABLE `evenement` DISABLE KEYS */;
INSERT INTO `evenement` VALUES (62,21,'Latence au démarrage','2025-06-03 14:26:43',1,'EAMEA','Lors de certains démarrages, le mouvement des commutateurs est vu deux fois : une fois au clic, puis retour à la position initiale, suivi du mouvement vers la position demandée trois secondes après.',1),(63,22,'Historique des points de fonctionnement','2025-06-03 14:27:06',1,'EAMEA','Il manque la trace des points sur les diagrammes P/T et PN, ce qui est important pour la pédagogie.',1);
/*!40000 ALTER TABLE `evenement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produit`
--

DROP TABLE IF EXISTS `produit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produit` (
  `produit_id` int NOT NULL AUTO_INCREMENT,
  `nom_produit` varchar(255) NOT NULL,
  PRIMARY KEY (`produit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produit`
--

LOCK TABLES `produit` WRITE;
/*!40000 ALTER TABLE `produit` DISABLE KEYS */;
INSERT INTO `produit` VALUES (1,'ZBOX'),(2,'Partiel PCP SNLE'),(3,'Partiel PCMEC PAN'),(4,'SIMSECPLO');
/*!40000 ALTER TABLE `produit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statut`
--

DROP TABLE IF EXISTS `statut`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statut` (
  `statut_id` int NOT NULL AUTO_INCREMENT,
  `valeur` varchar(50) NOT NULL,
  PRIMARY KEY (`statut_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statut`
--

LOCK TABLES `statut` WRITE;
/*!40000 ALTER TABLE `statut` DISABLE KEYS */;
INSERT INTO `statut` VALUES (1,'Nouveau'),(2,'En cours'),(3,'Résolu'),(4,'Fermé'),(5,'En attente');
/*!40000 ALTER TABLE `statut` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statut_evenement`
--

DROP TABLE IF EXISTS `statut_evenement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statut_evenement` (
  `statut_evenement_id` int NOT NULL,
  `event_type` varchar(50) NOT NULL,
  PRIMARY KEY (`statut_evenement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statut_evenement`
--

LOCK TABLES `statut_evenement` WRITE;
/*!40000 ALTER TABLE `statut_evenement` DISABLE KEYS */;
INSERT INTO `statut_evenement` VALUES (1,'Opened'),(2,'Solved'),(3,'Closed'),(4,'Attachment deleted'),(5,'Attachment added'),(7,'Text Changed'),(8,'Message');
/*!40000 ALTER TABLE `statut_evenement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket`
--

DROP TABLE IF EXISTS `ticket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket` (
  `ticket_id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_creation` datetime NOT NULL,
  `statut_id` int DEFAULT NULL,
  `urgence_id` int DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `produit_id` int DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  PRIMARY KEY (`ticket_id`),
  KEY `statut_id` (`statut_id`),
  KEY `urgence_id` (`urgence_id`),
  KEY `username` (`username`),
  KEY `produit_id` (`produit_id`),
  KEY `type_id` (`type_id`),
  CONSTRAINT `fk_ticket_produit` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`produit_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_statut` FOREIGN KEY (`statut_id`) REFERENCES `statut` (`statut_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_type` FOREIGN KEY (`type_id`) REFERENCES `type` (`type_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_urgence` FOREIGN KEY (`urgence_id`) REFERENCES `urgence` (`urgence_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_user` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket`
--

LOCK TABLES `ticket` WRITE;
/*!40000 ALTER TABLE `ticket` DISABLE KEYS */;
INSERT INTO `ticket` VALUES (21,'Latence au démarrage','Lors de certains démarrages, le mouvement des commutateurs est vu deux fois : une fois au clic, puis retour à la position initiale, suivi du mouvement vers la position demandée trois secondes après.','2025-06-03 14:26:43',1,1,'EAMEA',1,1),(22,'Historique des points de fonctionnement','Il manque la trace des points sur les diagrammes P/T et PN, ce qui est important pour la pédagogie.','2025-06-03 14:27:06',1,1,'EAMEA',1,1);
/*!40000 ALTER TABLE `ticket` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_save`
--

DROP TABLE IF EXISTS `ticket_save`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_save` (
  `ticket_save_id` int NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_creation` datetime NOT NULL,
  `statut_id` int NOT NULL,
  `urgence_id` int NOT NULL,
  `username` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `produit_id` int NOT NULL,
  `type_id` int NOT NULL,
  PRIMARY KEY (`ticket_save_id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `statut_id` (`statut_id`),
  KEY `urgence_id` (`urgence_id`),
  KEY `username` (`username`),
  KEY `produit_id` (`produit_id`),
  KEY `type_id` (`type_id`),
  CONSTRAINT `fk_ticketsave_produit` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`produit_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketsave_statut` FOREIGN KEY (`statut_id`) REFERENCES `statut` (`statut_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketsave_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`ticket_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketsave_type` FOREIGN KEY (`type_id`) REFERENCES `type` (`type_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketsave_urgence` FOREIGN KEY (`urgence_id`) REFERENCES `urgence` (`urgence_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ticketsave_user` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_save`
--

LOCK TABLES `ticket_save` WRITE;
/*!40000 ALTER TABLE `ticket_save` DISABLE KEYS */;
INSERT INTO `ticket_save` VALUES (141,21,'Latence au démarrage','Lors de certains démarrages, le mouvement des commutateurs est vu deux fois : une fois au clic, puis retour à la position initiale, suivi du mouvement vers la position demandée trois secondes après.','2025-06-03 14:26:43',1,1,'EAMEA',1,1),(142,22,'Historique des points de fonctionnement','Il manque la trace des points sur les diagrammes P/T et PN, ce qui est important pour la pédagogie.','2025-06-03 14:27:06',1,1,'EAMEA',1,1);
/*!40000 ALTER TABLE `ticket_save` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `type`
--

DROP TABLE IF EXISTS `type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `type` (
  `type_id` int NOT NULL AUTO_INCREMENT,
  `nom_type` varchar(50) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `type`
--

LOCK TABLES `type` WRITE;
/*!40000 ALTER TABLE `type` DISABLE KEYS */;
INSERT INTO `type` VALUES (1,'Bug'),(2,'Amélioration'),(3,'Question');
/*!40000 ALTER TABLE `type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `urgence`
--

DROP TABLE IF EXISTS `urgence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `urgence` (
  `urgence_id` int NOT NULL AUTO_INCREMENT,
  `niveau` varchar(50) NOT NULL,
  PRIMARY KEY (`urgence_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `urgence`
--

LOCK TABLES `urgence` WRITE;
/*!40000 ALTER TABLE `urgence` DISABLE KEYS */;
INSERT INTO `urgence` VALUES (1,'Normale'),(2,'Urgente');
/*!40000 ALTER TABLE `urgence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `username` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `entreprise` varchar(255) NOT NULL,
  `pwd` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('DCI/NAVFCO','contact@dci-navfco.fr','DCI/NAVFCO','$2y$10$ZLW8yfii/1ZBC7edXXeXP.cYknyCrNYMKaF0YkOg3fDTg4F.syTI6','user'),('EAMEA','contact@eamea.fr','EAMEA','$2y$10$ZJH.TjuAj1i08dGT5KlLCeaSGS.VlpEvXJNbcAjDDLA1TbhcDGSyi','user'),('ENSM BREST','contact@ensm-brest.fr','ENSM BREST','$2y$10$ZLW8yfii/1ZBC7edXXeXP.cYknyCrNYMKaF0YkOg3fDTg4F.syTI6','user'),('ENSM/BPN','contact@ensm-bpn.fr','ENSM/BPN','$2y$10$ZLW8yfii/1ZBC7edXXeXP.cYknyCrNYMKaF0YkOg3fDTg4F.syTI6','user'),('frederic.zitta','frederic.zitta@forsim.net','FORSIM','$2y$10$1fd19o07ZncTOd49sg5vT.H0dDi4x/tUC0S//SKp7UQcNU4B8a5T6','admin'),('test','test@forsim.net','testEntreprise','$2y$10$nKwtfqN/a4/2pvXRyFe0v.IidLQAVXmWb6UK2yWJUmYqhAJuc6HnW','user');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_produit`
--

DROP TABLE IF EXISTS `user_produit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_produit` (
  `username` varchar(255) NOT NULL,
  `produit_id` int NOT NULL,
  PRIMARY KEY (`username`,`produit_id`),
  KEY `produit_id` (`produit_id`),
  CONSTRAINT `fk_userproduit_produit` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`produit_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_userproduit_user` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_produit`
--

LOCK TABLES `user_produit` WRITE;
/*!40000 ALTER TABLE `user_produit` DISABLE KEYS */;
INSERT INTO `user_produit` VALUES ('DCI/NAVFCO',1),('EAMEA',1),('frederic.zitta',1),('test',1),('DCI/NAVFCO',2),('frederic.zitta',2),('ENSM BREST',3),('frederic.zitta',3),('ENSM/BPN',4),('frederic.zitta',4);
/*!40000 ALTER TABLE `user_produit` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-30 17:33:26
