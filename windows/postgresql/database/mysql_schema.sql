
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actividades_universitarias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trabajador_id` bigint(20) unsigned NOT NULL,
  `tipo` varchar(150) DEFAULT NULL,
  `lugar` varchar(150) DEFAULT NULL,
  `fecha_desde` date DEFAULT NULL,
  `fecha_hasta` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `actividades_universitarias_trabajador_id_foreign` (`trabajador_id`),
  CONSTRAINT `actividades_universitarias_trabajador_id_foreign` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `tipo_entidad` varchar(255) NOT NULL,
  `entidad_id` bigint(20) unsigned DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `direccion_ip` varchar(45) DEFAULT NULL,
  `navegador` text DEFAULT NULL,
  `metodo` varchar(10) DEFAULT NULL,
  `ruta` varchar(255) DEFAULT NULL,
  `valores_anteriores` longtext DEFAULT NULL,
  `valores_nuevos` longtext DEFAULT NULL,
  `datos_peticion` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_created_at_index` (`created_at`),
  KEY `activities_user_id_index` (`user_id`),
  KEY `ix_accion` (`accion`),
  KEY `ix_tipo_entidad` (`tipo_entidad`),
  KEY `activities_tipo_entidad_index` (`tipo_entidad`),
  CONSTRAINT `activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antecedente_documentos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `antecedente_id` bigint(20) unsigned NOT NULL,
  `tipo_documento` varchar(100) NOT NULL,
  `numero_providencia` varchar(50) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `institucion_emisora` varchar(150) DEFAULT NULL,
  `folio_numero` varchar(50) DEFAULT NULL,
  `folio_hojas` smallint(5) unsigned DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `antecedente_documentos_antecedente_id_foreign` (`antecedente_id`),
  CONSTRAINT `antecedente_documentos_antecedente_id_foreign` FOREIGN KEY (`antecedente_id`) REFERENCES `antecedentes_servicio` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antecedentes_servicio` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trabajador_id` bigint(20) unsigned NOT NULL,
  `institucion` varchar(150) NOT NULL,
  `es_institucion_actual` tinyint(1) NOT NULL DEFAULT 0,
  `cargo` varchar(150) DEFAULT NULL,
  `condicion_laboral` varchar(100) DEFAULT NULL,
  `dedicacion` varchar(100) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `antecedentes_servicio_trabajador_id_foreign` (`trabajador_id`),
  CONSTRAINT `antecedentes_servicio_trabajador_id_foreign` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `areas_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cargos_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `changelogs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_autor` varchar(255) NOT NULL,
  `correo_autor` varchar(255) NOT NULL,
  `hash_commit` varchar(40) NOT NULL,
  `mensaje_commit` text NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` varchar(255) NOT NULL DEFAULT 'change',
  `seccion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `changelogs_hash_commit_unique` (`hash_commit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dedicaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dedicaciones_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documentos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expediente_id` bigint(20) unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `estado` enum('en_revision','aprobado','rechazado') NOT NULL DEFAULT 'en_revision',
  `nota_rechazo` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documentos_expediente_id_index` (`expediente_id`),
  KEY `documentos_estado_index` (`estado`),
  CONSTRAINT `documentos_expediente_id_foreign` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trabajador_id` bigint(20) unsigned NOT NULL,
  `nivel_instruccion_id` bigint(20) unsigned DEFAULT NULL,
  `especialidad` varchar(150) DEFAULT NULL,
  `casa_estudio` varchar(150) DEFAULT NULL,
  `casa_egreso` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estudios_trabajador_id_foreign` (`trabajador_id`),
  KEY `estudios_nivel_instruccion_id_foreign` (`nivel_instruccion_id`),
  CONSTRAINT `estudios_nivel_instruccion_id_foreign` FOREIGN KEY (`nivel_instruccion_id`) REFERENCES `niveles_instruccion` (`id`) ON DELETE SET NULL,
  CONSTRAINT `estudios_trabajador_id_foreign` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expedientes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trabajador_id` bigint(20) unsigned NOT NULL,
  `solicitud_id` bigint(20) unsigned NOT NULL,
  `foto_carnet` varchar(255) DEFAULT NULL,
  `estado_global` int(11) NOT NULL DEFAULT 0,
  `notas_admin` text DEFAULT NULL,
  `carta_aprobacion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expedientes_trabajador_id_index` (`trabajador_id`),
  KEY `expedientes_solicitud_id_index` (`solicitud_id`),
  KEY `expedientes_estado_global_index` (`estado_global`),
  CONSTRAINT `expedientes_solicitud_id_foreign` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expedientes_trabajador_id_foreign` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formulas_prestaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `conceptos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conceptos`)),
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `formula_matematica` text DEFAULT NULL,
  `explicacion_variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`explicacion_variables`)),
  `ejemplo_calculo` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `formulas_prestaciones_codigo_unique` (`codigo`),
  KEY `formulas_prestaciones_activo_index` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grados` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grados_nombre_unique` (`nombre`),
  UNIQUE KEY `grados_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `niveles_instruccion` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `niveles_instruccion_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nomina_trabajador` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomina_id` bigint(20) unsigned NOT NULL,
  `trabajador_id` bigint(20) unsigned NOT NULL,
  `sueldo_base` decimal(12,2) DEFAULT NULL,
  `prima_familiar` decimal(12,2) DEFAULT NULL,
  `prima_hijo` decimal(12,2) DEFAULT NULL,
  `prima_hijos_discapacidad` decimal(12,2) DEFAULT NULL,
  `prima_actividad_universitaria` decimal(12,2) DEFAULT NULL,
  `prima_profesionalizacion` decimal(12,2) DEFAULT NULL,
  `prima_responsabilidad` decimal(12,2) DEFAULT NULL,
  `complemento_prima_responsabilidad` decimal(12,2) DEFAULT NULL,
  `prima_antiguedad` decimal(12,2) DEFAULT NULL,
  `cesta_ticket` decimal(12,2) DEFAULT NULL,
  `total_asignacion` decimal(12,2) DEFAULT NULL,
  `sso` decimal(12,2) DEFAULT NULL,
  `lpf` decimal(12,2) DEFAULT NULL,
  `faov` decimal(12,2) DEFAULT NULL,
  `aporte_ipasme` decimal(12,2) DEFAULT NULL,
  `aporte_caja_ahorro` decimal(12,2) DEFAULT NULL,
  `prestamo_caja_ahorro` decimal(12,2) DEFAULT NULL,
  `isr` decimal(12,2) NOT NULL DEFAULT 0.00,
  `horas_extras` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_deduccion` decimal(12,2) DEFAULT NULL,
  `neto_a_cobrar` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomina_trabajador_nomina_id_trabajador_id_unique` (`nomina_id`,`trabajador_id`),
  KEY `nomina_trabajador_trabajador_id_foreign` (`trabajador_id`),
  CONSTRAINT `nomina_trabajador_nomina_id_foreign` FOREIGN KEY (`nomina_id`) REFERENCES `nominas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nomina_trabajador_trabajador_id_foreign` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nominas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `periodo` varchar(10) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'borrador',
  `total_general` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nominas_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `from_user_id` bigint(20) unsigned DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` varchar(255) NOT NULL DEFAULT '''info''',
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_from_user_id_foreign` (`from_user_id`),
  KEY `notifications_user_id_index` (`user_id`),
  KEY `notifications_leida_index` (`leida`),
  CONSTRAINT `notifications_from_user_id_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prestaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trabajador_id` bigint(20) unsigned NOT NULL,
  `anios_servicio` int(11) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `detalles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detalles`)),
  `sueldo_integral` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_primas` decimal(12,2) NOT NULL DEFAULT 0.00,
  `porcentaje_jubilacion` decimal(5,2) NOT NULL DEFAULT 100.00,
  `tasa_cambio_id` bigint(20) unsigned DEFAULT NULL,
  `tasa_utilizada` decimal(12,4) DEFAULT NULL,
  `moneda_tasa` varchar(10) DEFAULT NULL,
  `fecha_tasa_utilizada` timestamp NULL DEFAULT NULL,
  `fuente_tasa` varchar(100) DEFAULT NULL,
  `calculado_por_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fideicomiso_monto` decimal(12,2) DEFAULT NULL,
  `fideicomiso_observaciones` text DEFAULT NULL,
  `fideicomiso_documento` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prestaciones_trabajador_id_index` (`trabajador_id`),
  KEY `prestaciones_tasa_cambio_id_foreign` (`tasa_cambio_id`),
  KEY `prestaciones_calculado_por_user_id_foreign` (`calculado_por_user_id`),
  CONSTRAINT `prestaciones_calculado_por_user_id_foreign` FOREIGN KEY (`calculado_por_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prestaciones_tasa_cambio_id_foreign` FOREIGN KEY (`tasa_cambio_id`) REFERENCES `tasas_cambio` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prestaciones_trabajador_id_foreign` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `primas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `monto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `valor` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_vigencia` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `primas_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recibos_pago` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expediente_id` bigint(20) unsigned NOT NULL,
  `periodo` varchar(7) DEFAULT NULL,
  `concepto` varchar(150) DEFAULT NULL,
  `monto_usd` decimal(12,2) DEFAULT NULL,
  `archivo` varchar(255) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recibos_pago_expediente_id_foreign` (`expediente_id`),
  CONSTRAINT `recibos_pago_expediente_id_foreign` FOREIGN KEY (`expediente_id`) REFERENCES `expedientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solicitudes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trabajador_id` bigint(20) unsigned NOT NULL,
  `fecha_solicitud` date NOT NULL,
  `periodo` varchar(20) DEFAULT NULL,
  `tipo_jubilacion` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('pendiente','revision','aprobado','rechazado') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitudes_estado_index` (`estado`),
  KEY `solicitudes_trabajador_id_index` (`trabajador_id`),
  KEY `solicitudes_created_at_index` (`created_at`),
  CONSTRAINT `solicitudes_trabajador_id_foreign` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sueldos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `grado_id` bigint(20) unsigned NOT NULL,
  `nivel_instruccion_id` bigint(20) unsigned NOT NULL,
  `sueldo_base` decimal(12,2) NOT NULL,
  `complemento_prima_cargo` decimal(12,2) NOT NULL DEFAULT 0.00,
  `porcentaje_prima_cargo` decimal(5,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sueldos_grado_id_nivel_instruccion_id_unique` (`grado_id`,`nivel_instruccion_id`),
  KEY `sueldos_nivel_instruccion_id_foreign` (`nivel_instruccion_id`),
  CONSTRAINT `sueldos_grado_id_foreign` FOREIGN KEY (`grado_id`) REFERENCES `grados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sueldos_nivel_instruccion_id_foreign` FOREIGN KEY (`nivel_instruccion_id`) REFERENCES `niveles_instruccion` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasas_cambio` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tasa` decimal(12,4) NOT NULL,
  `moneda_origen` varchar(10) NOT NULL DEFAULT 'USD',
  `moneda_destino` varchar(10) NOT NULL DEFAULT 'VES',
  `fuente` varchar(100) DEFAULT NULL,
  `tipo` enum('automatica','manual') NOT NULL DEFAULT 'manual',
  `observacion` text DEFAULT NULL,
  `usuario_id` bigint(20) unsigned DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_consulta` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasas_cambio_usuario_id_foreign` (`usuario_id`),
  KEY `tasas_cambio_activa_index` (`activa`),
  KEY `tasas_cambio_created_at_index` (`created_at`),
  CONSTRAINT `tasas_cambio_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipos_contrato` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_contrato_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipos_jubilacion` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_jubilacion_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trabajadores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_empleado` varchar(20) DEFAULT NULL,
  `cedula` varchar(255) NOT NULL,
  `nombres` varchar(255) NOT NULL,
  `apellidos` varchar(255) NOT NULL,
  `cuenta_bancaria` varchar(255) DEFAULT NULL,
  `genero` enum('M','F') NOT NULL,
  `grado_nivel` varchar(255) NOT NULL,
  `grado_id` bigint(20) unsigned DEFAULT NULL,
  `cargo` varchar(255) NOT NULL,
  `cargo_id` bigint(20) unsigned DEFAULT NULL,
  `unidad_departamento` varchar(255) NOT NULL,
  `unidad_id` bigint(20) unsigned DEFAULT NULL,
  `area_id` bigint(20) unsigned DEFAULT NULL,
  `sueldo_base` decimal(12,2) DEFAULT NULL,
  `denominacion_salario` varchar(255) DEFAULT NULL,
  `tabulador` varchar(50) DEFAULT NULL,
  `porcentaje_prima_cargo` decimal(5,2) DEFAULT NULL,
  `complemento_prima_cargo` decimal(12,2) DEFAULT NULL,
  `es_jefe_coordinador` tinyint(1) NOT NULL DEFAULT 0,
  `cesta_ticket` decimal(12,2) DEFAULT NULL,
  `prima_profesionalizacion` decimal(12,2) DEFAULT NULL,
  `sugau` decimal(12,2) DEFAULT NULL,
  `afiliacion_sifaiuty` varchar(50) DEFAULT NULL,
  `asignacion` enum('Manual','Nomina') NOT NULL DEFAULT 'Manual',
  `fecha_nacimiento` date NOT NULL,
  `edad` int(11) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `anos_servicio_inst` int(11) NOT NULL,
  `anos_servicio_externo` int(11) NOT NULL,
  `total_anos_servicio` int(11) NOT NULL,
  `nivel_instruccion` int(11) NOT NULL,
  `nivel_instruccion_id` bigint(20) unsigned DEFAULT NULL,
  `nivel_educativo_texto` varchar(100) DEFAULT NULL,
  `dedicacion` varchar(100) DEFAULT NULL,
  `grado_cargo` varchar(100) DEFAULT NULL,
  `tipo_contrato_id` bigint(20) unsigned DEFAULT NULL,
  `tipo_nomina` varchar(20) DEFAULT NULL,
  `tipo_jubilacion_id` bigint(20) unsigned DEFAULT NULL,
  `numero_hijos` int(11) NOT NULL,
  `hijos_discapacidad` int(11) NOT NULL DEFAULT 0,
  `actividad_universitaria` tinyint(1) NOT NULL DEFAULT 0,
  `porcentaje_antiguedad` decimal(5,2) NOT NULL DEFAULT 0.00,
  `porcentaje_caja_ahorro` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trabajadores_cedula_unique` (`cedula`),
  KEY `trabajadores_cargo_id_foreign` (`cargo_id`),
  KEY `trabajadores_area_id_foreign` (`area_id`),
  KEY `trabajadores_grado_id_foreign` (`grado_id`),
  KEY `trabajadores_nivel_instruccion_id_foreign` (`nivel_instruccion_id`),
  KEY `trabajadores_tipo_contrato_id_foreign` (`tipo_contrato_id`),
  KEY `trabajadores_tipo_nomina_index` (`tipo_nomina`),
  KEY `trabajadores_unidad_id_foreign` (`unidad_id`),
  KEY `trabajadores_tipo_jubilacion_id_foreign` (`tipo_jubilacion_id`),
  CONSTRAINT `trabajadores_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trabajadores_cargo_id_foreign` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trabajadores_grado_id_foreign` FOREIGN KEY (`grado_id`) REFERENCES `grados` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trabajadores_nivel_instruccion_id_foreign` FOREIGN KEY (`nivel_instruccion_id`) REFERENCES `niveles_instruccion` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trabajadores_tipo_contrato_id_foreign` FOREIGN KEY (`tipo_contrato_id`) REFERENCES `tipos_contrato` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trabajadores_tipo_jubilacion_id_foreign` FOREIGN KEY (`tipo_jubilacion_id`) REFERENCES `tipos_jubilacion` (`id`) ON DELETE SET NULL,
  CONSTRAINT `trabajadores_unidad_id_foreign` FOREIGN KEY (`unidad_id`) REFERENCES `unidades` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unidades` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unidades_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `correo` varchar(255) NOT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `correo_verificado_en` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('usuario','admin','superadmin') NOT NULL DEFAULT 'usuario',
  `tema` varchar(255) NOT NULL DEFAULT '''light''',
  `idioma` varchar(255) NOT NULL DEFAULT '''es''',
  `color_acento` varchar(255) NOT NULL DEFAULT '''#1a365d''',
  `verificacion_dos_pasos` tinyint(1) NOT NULL DEFAULT 0,
  `secreto_2fa` varchar(255) DEFAULT NULL,
  `notificacion_correo` varchar(255) NOT NULL DEFAULT '''all''',
  `notificacion_sistema` varchar(255) NOT NULL DEFAULT '''all''',
  `perfil_publico` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `ultimo_acceso_ip` varchar(255) DEFAULT NULL,
  `token_recordar` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

