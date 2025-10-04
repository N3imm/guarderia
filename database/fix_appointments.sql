-- Primero eliminamos la clave foránea existente
ALTER TABLE `appointments` 
DROP FOREIGN KEY `appointments_ibfk_2`;

-- Luego creamos la clave foránea correcta
ALTER TABLE `appointments` 
ADD CONSTRAINT `appointments_pet_fk` 
FOREIGN KEY (`pet_id`) 
REFERENCES `pets` (`id`) 
ON DELETE CASCADE 
ON UPDATE CASCADE;