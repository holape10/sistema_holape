# Host: localhost  (Version 5.7.38-log)
# Date: 2022-09-03 10:40:32
# Generator: MySQL-Front 6.0  (Build 2.20)


#
# Structure for table "users"
#

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `IdUsuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `apeusu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estusu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IdEmpresa` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_empresa_negocio` int(11) DEFAULT NULL,
  `password_admin` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `turno` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'Cerrado',
  `id_turno` int(11) DEFAULT NULL,
  `IdIngreso` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_sistema` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '1=restarurante 2=bodega',
  `factura` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terminal` int(11) DEFAULT NULL,
  `proc_id` int(11) DEFAULT NULL,
  `apeusumat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emp_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`IdUsuario`),
  KEY `fk_acceso_empleado` (`emp_id`),
  CONSTRAINT `fk_acceso_empleado` FOREIGN KEY (`emp_id`) REFERENCES `empleado` (`emp_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

#
# Data for table "users"
#

INSERT INTO `users` VALUES (37,'20609392267','20609392267','$2y$10$41HdFEQQ2UNZ072HIOIwfeVgtAfEZiGq8r85j8RgKgPCFlb3sqm3K','OvHRMdrciEOewYCU0SbZLNTFbw130fDAmWXehWWBKSFdepKyCKKAPJX4ZYlk',NULL,NULL,'SAVAMILE S.A.C.','Activo','20609392267',3,NULL,'Aperturado',109,NULL,NULL,NULL,1,NULL,NULL,40),(38,'ADMIN','admin','$2y$10$Al3W9eqLekuqxRfHcxIXUeXHNmPI4YTe/VjEu4s8EAupnwfBqPYPC','Fxu97x52fk8wravl4Rgv2k3PqArFRVfTL5zQOwB0vUmDAx1VVxxoBmomBs7z',NULL,NULL,' -','1','20609392267',3,NULL,'Aperturado',114,NULL,NULL,NULL,1,NULL,NULL,41),(39,'CAJA','caja','$2y$10$/u5rqZV0A78q6c8ZGXfqwOayCPcVz5GLxCKanpss7dsHD6FFkE/mu','lFCXtLxxUki04dVPvPakrI4hR68TZ46ohwfbcClMSVKlJAPeH4gMEmpPPfuF',NULL,NULL,' .','1','20609392267',3,NULL,'Aperturado',115,NULL,NULL,NULL,1,NULL,NULL,42),(41,'MOZO','MOZO','$2y$10$1.r5QuYX5vgEwoZ8xZzN3uVqgUOamnKno5QLVWdWVMiTiRupZBySq','6Gi3Ngn0PUWmASZaKSHdjchPPIvVUPg6PpZf6WFdecf2pijXiRlpU3yN7lNv',NULL,NULL,' MOZO','1','20609392267',3,NULL,'Cerrado',NULL,NULL,NULL,NULL,1,NULL,NULL,44);
