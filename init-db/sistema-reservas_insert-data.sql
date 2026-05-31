-- =============================================================
-- Script de INSERT para sistema de reservas
-- Tablas: usuario, profesional, consultorio, reservas
-- =============================================================

USE sistemareservas;
SET NAMES utf8mb4;

-- -------------------------------------------------------------
-- 1. USUARIO
--    Incluye tanto pacientes como profesionales (rol diferencia)
-- -------------------------------------------------------------
INSERT INTO usuario (id, nombre, apellido, rol, email, telefono, password, activo, motivo_baja, fecha_baja) VALUES
(1,  'Lucía',     'Fernández',   'paciente',     'lucia.fernandez@email.com',   '2914501234', 'hashed_pass_001', TRUE,  NULL, NULL),
(2,  'Martín',    'González',    'paciente',     'martin.gonzalez@email.com',   '2914502345', 'hashed_pass_002', TRUE,  NULL, NULL),
(3,  'Sofía',     'Ramírez',     'paciente',     'sofia.ramirez@email.com',     '2914503456', 'hashed_pass_003', TRUE,  NULL, NULL),
(4,  'Ignacio',   'López',       'paciente',     'ignacio.lopez@email.com',     '2914504567', 'hashed_pass_004', FALSE, 'Solicitud del paciente', '2025-11-15 10:00:00'),
(5,  'Valentina', 'Torres',      'paciente',     'valentina.torres@email.com',  '2914505678', 'hashed_pass_005', TRUE,  NULL, NULL),
(6,  'Carlos',    'Méndez',      'profesional',  'carlos.mendez@clinica.com',   '2914506789', 'hashed_pass_006', TRUE,  NULL, NULL),
(7,  'Ana',       'Suárez',      'profesional',  'ana.suarez@clinica.com',      '2914507890', 'hashed_pass_007', TRUE,  NULL, NULL),
(8,  'Roberto',   'Herrera',     'profesional',  'roberto.herrera@clinica.com', '2914508901', 'hashed_pass_008', TRUE,  NULL, NULL),
(9,  'Elena',     'Castillo',    'admin',        'elena.castillo@clinica.com',  '2914509012', 'hashed_pass_009', TRUE,  NULL, NULL),
(10, 'Diego',     'Morales',     'paciente',     'diego.morales@email.com',     '2914510123', 'hashed_pass_010', TRUE,  NULL, NULL);

-- -------------------------------------------------------------
-- 2. PROFESIONAL
--    Referencia a usuario (solo los usuarios con rol profesional)
-- -------------------------------------------------------------
INSERT INTO profesional (idprofesional, profesion) VALUES
(6, 'Médico Clínico'),
(7, 'Psicóloga'),
(8, 'Traumatólogo');

-- -------------------------------------------------------------
-- 3. CONSULTORIO
--    Cada consultorio tiene a lo sumo un profesional (UNIQUE)
-- -------------------------------------------------------------
INSERT INTO consultorio (id, direccion, ciudad, horario_apertura, horario_cierre, id_profesional) VALUES
(1, 'Av. San Martín 1250, Piso 2, Of. 5', 'Tres Arroyos', '2026-01-01 08:00:00', '2026-01-01 16:00:00', 6),
(2, 'Calle Moreno 780',                   'Tres Arroyos', '2026-01-01 09:00:00', '2026-01-01 17:00:00', 7),
(3, 'Av. Independencia 340, Of. 12',      'Tres Arroyos', '2026-01-01 07:00:00', '2026-01-01 15:00:00', 8),
(4, 'Calle Belgrano 95',                  'Tres Arroyos', '2026-01-01 10:00:00', '2026-01-01 18:00:00', NULL);

-- -------------------------------------------------------------
-- 4. RESERVAS
--    idprofesional e idpaciente tienen índice UNIQUE:
--    cada profesional y cada paciente aparece una sola vez.
-- -------------------------------------------------------------
INSERT INTO reservas (id, idprofesional, idpaciente, fecha_reserva, estado, fecha_cancelacion) VALUES
(1, 6, 1,  '2026-03-10 09:00:00', 'confirmada',  NULL),
(2, 7, 2,  '2026-03-11 10:30:00', 'confirmada',  NULL),
(3, 8, 3,  '2026-03-12 08:00:00', 'pendiente',   NULL),
(4, 6, 4,  '2026-02-20 11:00:00', 'cancelada',   '2026-02-18 09:00:00'),
(5, 7, 5,  '2026-03-15 14:00:00', 'confirmada',  NULL),
(6, 8, 10, '2026-03-18 07:30:00', 'pendiente',   NULL);

-- =============================================================
-- Fin del script de inserción
-- =============================================================
