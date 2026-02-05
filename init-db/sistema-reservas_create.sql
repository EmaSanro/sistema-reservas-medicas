-- Created by Redgate Data Modeler (https://datamodeler.redgate-platform.com)
-- Last modification date: 2026-01-29 15:37:00.089
-- schema: reservasmedicas;
CREATE DATABASE IF NOT EXISTS sistemareservas;
-- tables
-- Table: Consultorio
CREATE TABLE consultorio (
    id int  NOT NULL,
    direccion varchar(100)  NOT NULL,
    ciudad varchar(60)  NOT NULL,
    horario_apertura datetime  NOT NULL,
    horario_cierre datetime  NOT NULL,
    id_profesional int  NULL,
    UNIQUE INDEX ak_id_profesional (id_profesional),
    CONSTRAINT pk_id PRIMARY KEY (id)
);

-- Table: Profesional
CREATE TABLE profesional (
    idprofesional int  NOT NULL,
    profesion varchar(60)  NOT NULL,
    CONSTRAINT pk_profesional PRIMARY KEY (idprofesional)
);

-- Table: Reservas
CREATE TABLE reservas (
    id int  NOT NULL,
    idprofesional int  NOT NULL,
    idpaciente int  NOT NULL,
    fecha_reserva datetime  NOT NULL,
    UNIQUE INDEX reservas_ak_idprofesional (idprofesional),
    UNIQUE INDEX reservas_ak_idpaciente (idpaciente),
    CONSTRAINT pk_reservas PRIMARY KEY (id)
);

-- Table: Usuario
CREATE TABLE usuario (
    id int  NOT NULL AUTO_INCREMENT,
    nombre varchar(50)  NOT NULL,
    apellido varchar(70)  NOT NULL,
    rol varchar(15)  NOT NULL,
    email varchar(150)  NULL,
    telefono varchar(45)  NULL,
    password varchar(150)  NOT NULL,
    UNIQUE INDEX email_ak (email),
    UNIQUE INDEX telefono_ak (telefono),
    CONSTRAINT pk_usuario PRIMARY KEY (id)
);

-- foreign keys
-- Reference: Reservas_Usuario (table: Reservas)
ALTER TABLE reservas ADD CONSTRAINT fk_reservas_usuario FOREIGN KEY fk_reservas_usuario (idpaciente)
    REFERENCES usuario (id);

-- Reference: fk_consultorio_profesional (table: Consultorio)
ALTER TABLE consultorio ADD CONSTRAINT fk_consultorio_profesional FOREIGN KEY fk_consultorio_profesional (id_profesional)
    REFERENCES profesional (idprofesional)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- Reference: fk_profesional_usuario (table: Profesional)
ALTER TABLE profesional ADD CONSTRAINT fk_profesional_usuario FOREIGN KEY fk_profesional_usuario (idprofesional)
    REFERENCES usuario (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- Reference: fk_reservas_profesional (table: Reservas)
ALTER TABLE reservas ADD CONSTRAINT fk_reservas_profesional FOREIGN KEY fk_reservas_profesional (idprofesional)
    REFERENCES profesional (idprofesional);

-- End of file.

