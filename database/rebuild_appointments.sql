-- Desactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar todas las restricciones de clave foránea de appointments
ALTER TABLE `appointments` 
DROP FOREIGN KEY IF EXISTS `appointments_ibfk_1`,
DROP FOREIGN KEY IF EXISTS `appointments_ibfk_2`,
DROP FOREIGN KEY IF EXISTS `appointments_user_fk`,
DROP FOREIGN KEY IF EXISTS `appointments_pet_fk`;

-- Eliminar los índices existentes
ALTER TABLE `appointments`
DROP INDEX IF EXISTS `user_id`,
DROP INDEX IF EXISTS `pet_id`;

-- Crear una tabla temporal con la estructura correcta
CREATE TABLE `appointments_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pendiente',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiar los datos existentes si hay
INSERT INTO appointments_temp (user_id, pet_id, appointment_date, appointment_time, service_type, status, notes, created_at, updated_at)
SELECT user_id, pet_id, appointment_date, appointment_time, service_type, status, notes, created_at, updated_at
FROM appointments;

-- Eliminar la tabla original
DROP TABLE IF EXISTS `appointments`;

-- Renombrar la tabla temporal
RENAME TABLE `appointments_temp` TO `appointments`;

-- Agregar los índices y las claves foráneas correctas
ALTER TABLE `appointments`
ADD INDEX `user_id` (`user_id`),
ADD INDEX `pet_id` (`pet_id`),
ADD CONSTRAINT `appointments_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `appointments_pet_fk` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Reactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Verificar que las tablas existan y sus relaciones
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    TABLE_SCHEMA = 'guarderia_mascotas'
    AND TABLE_NAME = 'appointments'
    AND REFERENCED_TABLE_NAME IS NOT NULL;