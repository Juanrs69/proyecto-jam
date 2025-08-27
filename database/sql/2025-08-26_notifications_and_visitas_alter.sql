-- DDL para notificaciones y columnas adicionales de visitas
-- Ejecutar en MySQL (XAMPP) con el esquema en uso

-- Tabla notifications
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(64) NOT NULL,
  title VARCHAR(255) NOT NULL,
  body TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME NULL,
  INDEX idx_user_read (user_id, read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajustes a tabla visitas (opcionales si ya existen)
ALTER TABLE visitas
  ADD COLUMN IF NOT EXISTS salida DATETIME NULL AFTER fecha,
  ADD COLUMN IF NOT EXISTS departamento VARCHAR(191) NULL AFTER motivo,
  ADD COLUMN IF NOT EXISTS estado ENUM('pendiente','autorizada','rechazada') NOT NULL DEFAULT 'pendiente' AFTER departamento,
  ADD COLUMN IF NOT EXISTS autorizado_por VARCHAR(64) NULL AFTER estado,
  ADD COLUMN IF NOT EXISTS anfitrion_id VARCHAR(64) NULL AFTER visitante_id;

-- Si MySQL de tu versión no soporta IF NOT EXISTS en ALTER, usa comprobaciones previas:
-- Ejemplo alterno (ejecutar según falte la columna):
-- ALTER TABLE visitas ADD COLUMN salida DATETIME NULL AFTER fecha;
-- ALTER TABLE visitas ADD COLUMN departamento VARCHAR(191) NULL AFTER motivo;
-- ALTER TABLE visitas ADD COLUMN estado ENUM('pendiente','autorizada','rechazada') NOT NULL DEFAULT 'pendiente' AFTER departamento;
-- ALTER TABLE visitas ADD COLUMN autorizado_por VARCHAR(64) NULL AFTER estado;
-- ALTER TABLE visitas ADD COLUMN anfitrion_id VARCHAR(64) NULL AFTER visitante_id;

-- Opcional: FK si usuarios.id es compatible (ajusta el tipo si fuese INT)
-- ALTER TABLE visitas ADD CONSTRAINT fk_visitas_anfitrion FOREIGN KEY (anfitrion_id) REFERENCES usuarios(id);
