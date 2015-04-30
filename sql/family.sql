CREATE TABLE `family` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `family` varchar(255) NOT NULL,
     `type` varchar(255) NOT NULL,
     `counts` int(11) NOT NULL,
     `interpro_release` int(11) NOT NULL,
     PRIMARY KEY (`id`),
     UNIQUE KEY `family` (`family`,`type`,`interpro_release`)
) ENGINE=InnoDB AUTO_INCREMENT=16402 DEFAULT CHARSET=latin1
