-- Database: `officier_de_garde`

-- --------------------------------------------------------

-- Table structure for table `activite_medicale`

CREATE TABLE `activite_medicale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporting_id` int(11) DEFAULT NULL,
  `nb_patients_admis` int(11) DEFAULT 0,
  `nb_patients_sortis` int(11) DEFAULT 0,
  `interventions_importantes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporting_id` (`reporting_id`),
  CONSTRAINT `activite_medicale_ibfk_1` FOREIGN KEY (`reporting_id`) REFERENCES `reporting_garde` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `communications`

CREATE TABLE `communications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporting_id` int(11) DEFAULT NULL,
  `details_communications` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporting_id` (`reporting_id`),
  CONSTRAINT `communications_ibfk_1` FOREIGN KEY (`reporting_id`) REFERENCES `reporting_garde` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `impacts_operations`

CREATE TABLE `impacts_operations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `incident_id` int(11) DEFAULT NULL,
  `services_affectes` text DEFAULT NULL,
  `consequences_sur_patients` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incident_id` (`incident_id`),
  CONSTRAINT `impacts_operations_ibfk_1` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `incidents`

CREATE TABLE `incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporting_id` int(11) DEFAULT NULL,
  `type_incident` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `heure_incident` time DEFAULT NULL,
  `lieu_incident` varchar(255) DEFAULT NULL,
  `personnel_impliqué` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporting_id` (`reporting_id`),
  CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`reporting_id`) REFERENCES `reporting_garde` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `services_affectes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporting_id` int(11) NOT NULL,  -- Ajout de la colonne reporting_id
  `nom_service` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reporting_id` (`reporting_id`),  -- Index pour reporting_id
  CONSTRAINT `services_affectes_ibfk_2` FOREIGN KEY (`reporting_id`) REFERENCES `reporting_garde` (`id`) ON DELETE CASCADE  -- Contrainte pour reporting_id
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `observations`

CREATE TABLE `observations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporting_id` int(11) DEFAULT NULL,
  `points_a_ameliorer` text DEFAULT NULL,
  `suggestions_futures` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporting_id` (`reporting_id`),
  CONSTRAINT `observations_ibfk_1` FOREIGN KEY (`reporting_id`) REFERENCES `reporting_garde` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `personnel`

CREATE TABLE `personnel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporting_id` int(11) DEFAULT NULL,
  `nom_personnel` varchar(255) NOT NULL,
  `fonction` varchar(255) DEFAULT NULL,
  `remplacement_ou_absence` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporting_id` (`reporting_id`),
  CONSTRAINT `personnel_ibfk_1` FOREIGN KEY (`reporting_id`) REFERENCES `reporting_garde` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `reponses`

CREATE TABLE `reponses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `incident_id` int(11) DEFAULT NULL,
  `actions_prises` text DEFAULT NULL,
  `ameliorations_proposees` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incident_id` (`incident_id`),
  CONSTRAINT `reponses_ibfk_1` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `reporting_garde`

CREATE TABLE `reporting_garde` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_heure_garde` datetime NOT NULL,
  `directeur_de_garde` varchar(255) NOT NULL,
  `hopital_concerne` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `ameliorations` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `reporting_id` int(11) DEFAULT NULL, 
  `ameliorations` text DEFAULT NULL, PRIMARY KEY (`id`), 
  KEY `reporting_id` (`reporting_id`), 
  CONSTRAINT `ameliorations_ibfk_1` FOREIGN KEY (`reporting_id`) REFERENCES `reporting_garde` (`id`) ON DELETE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporting_id` int(11) NOT NULL,
  `nb_admis` int(11) NOT NULL DEFAULT 0,
  `nb_sortis` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `reporting_id` (`reporting_id`),
  CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`reporting_id`) REFERENCES `reporting_garde` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `interventions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporting_id` int(11) NOT NULL,
  `intervention` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reporting_id` (`reporting_id`),
  CONSTRAINT `interventions_ibfk_1` FOREIGN KEY (`reporting_id`) REFERENCES `reporting_garde` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------

-- Table structure for table `ressources_utilisees`

CREATE TABLE `ressources_utilisees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reporting_id` int(11) DEFAULT NULL,
  `medicaments_equipements` text DEFAULT NULL,
  `besoin_ressources_sup` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporting_id` (`reporting_id`),
  CONSTRAINT `ressources_utilisees_ibfk_1` FOREIGN KEY (`reporting_id`) REFERENCES `reporting_garde` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `utilisateur`

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom_utilisateur` varchar(50) NOT NULL UNIQUE,
  `mot_de_passe` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserts for table `personnel`
INSERT INTO `personnel` (`id`, `reporting_id`, `nom_personnel`, `fonction`, `remplacement_ou_absence`) VALUES
(1, 1, '', '', ''),
(2, 2, 'Houssein', 'Technicien', 'suite une absence répété'),
(3, 3, 'Houssein', 'Technicien', 'QSKJDHS'),
(4, 3, 'ASMA', 'ALI', 'JSHDJS'),
(5, 3, 'ALI', 'KLSJLKD', 'LSKJDLKSDJ'),
(6, 4, 'Houssein', 'Technicien', 'Radiologie'),
(7, 4, 'ASMA', 'ORL', 'Mal PLACER');

-- Inserts for table `reporting_garde`
INSERT INTO `reporting_garde` (`id`, `date_heure_garde`, `directeur_de_garde`, `hopital_concerne`) VALUES
(1, '2024-10-31 10:27:00', 'DJAMA', 'PELTIER'),
(2, '2024-10-31 10:30:00', 'Moktar SAID', 'Peltier'),
(3, '2024-10-31 21:29:00', 'DJAMA', 'PELTIER'),
(4, '2024-11-01 17:41:00', 'SALAH', 'PELTIER');

-- Inserts for table `utilisateur`
INSERT INTO `utilisateur` (`id`, `nom_utilisateur`, `mot_de_passe`) VALUES
(1, 'admin', '$2y$10$BEmTO.0xeatLwrCFbQPDZu8pG4lrkq524Rc2CMvSJdiZbJIrCtLJu');