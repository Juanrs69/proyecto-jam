-- Migraciones seguras para XAMPP (MySQL/MariaDB)
-- Ejecutar en phpMyAdmin sobre la base de datos seleccionada

SET @db := DATABASE();

-- anfitrion_id
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='visitas' AND COLUMN_NAME='anfitrion_id');
SET @sql := IF(@exists=0,
  'ALTER TABLE visitas ADD COLUMN anfitrion_id VARCHAR(64) NULL AFTER visitante_id',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- índice anfitrion_id
SET @idx := (SELECT COUNT(*) FROM information_schema.statistics
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='visitas' AND INDEX_NAME='idx_visitas_anfitrion');
SET @sql := IF(@idx=0,
  'CREATE INDEX idx_visitas_anfitrion ON visitas (anfitrion_id)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- salida
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='visitas' AND COLUMN_NAME='salida');
SET @sql := IF(@exists=0,
  'ALTER TABLE visitas ADD COLUMN salida DATETIME NULL AFTER fecha',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- departamento
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='visitas' AND COLUMN_NAME='departamento');
SET @sql := IF(@exists=0,
  'ALTER TABLE visitas ADD COLUMN departamento VARCHAR(255) NULL AFTER motivo',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- estado
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='visitas' AND COLUMN_NAME='estado');
SET @sql := IF(@exists=0,
  'ALTER TABLE visitas ADD COLUMN estado ENUM(''pendiente'',''autorizada'',''rechazada'') NOT NULL DEFAULT ''pendiente'' AFTER departamento',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- autorizado_por
SET @exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='visitas' AND COLUMN_NAME='autorizado_por');
SET @sql := IF(@exists=0,
  'ALTER TABLE visitas ADD COLUMN autorizado_por VARCHAR(64) NULL AFTER estado',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- índice autorizado_por
SET @idx := (SELECT COUNT(*) FROM information_schema.statistics
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='visitas' AND INDEX_NAME='idx_visitas_autorizado_por');
SET @sql := IF(@idx=0,
  'CREATE INDEX idx_visitas_autorizado_por ON visitas (autorizado_por)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FK opcional
-- Ajustar tipos si no coinciden con usuarios.id
SET @fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='visitas'
    AND CONSTRAINT_NAME='fk_visitas_anfitrion' AND CONSTRAINT_TYPE='FOREIGN KEY');
SET @sql := IF(@fk=0,
  'ALTER TABLE visitas ADD CONSTRAINT fk_visitas_anfitrion FOREIGN KEY (anfitrion_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
