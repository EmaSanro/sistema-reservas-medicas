-- Created by Redgate Data Modeler (https://datamodeler.redgate-platform.com)
-- Last modification date: 2026-02-09 21:20:22.429

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

-- Table: Nota
CREATE TABLE nota (
    id int  NOT NULL AUTO_INCREMENT,
    motivo_visita varchar(150)  NOT NULL,
    texto_nota text  NOT NULL,
    reserva_id int  NOT NULL,
    CONSTRAINT pk_nota PRIMARY KEY (id)
);

-- Table: Profesional
CREATE TABLE profesional (
    idprofesional int  NOT NULL,
    profesion varchar(60)  NOT NULL,
    CONSTRAINT pk_profesional PRIMARY KEY (idprofesional)
);

-- Table: Reservas
CREATE TABLE reservas (
    id int  NOT NULL AUTO_INCREMENT,
    idprofesional int  NOT NULL,
    idpaciente int  NOT NULL,
    fecha_reserva datetime  NOT NULL,
    estado varchar(15)  NOT NULL,
    fecha_cancelacion datetime  NULL,
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
    activo bool  NOT NULL,
    motivo_baja varchar(255)  NULL,
    fecha_baja datetime  NULL,
    UNIQUE INDEX email_ak (email),
    UNIQUE INDEX telefono_ak (telefono),
    CONSTRAINT pk_usuario PRIMARY KEY (id)
);

-- Table: archivo_nota
CREATE TABLE archivo_nota (
    id int  NOT NULL AUTO_INCREMENT,
    nombre_original varchar(255)  NOT NULL,
    nombre_sistema varchar(255)  NOT NULL,
    ruta varchar(500)  NOT NULL,
    tipo_archivo varchar(50)  NOT NULL,
    peso int  NOT NULL,
    fecha_subida datetime  NOT NULL,
    nota_id int  NOT NULL,
    CONSTRAINT pk_archivo_nota PRIMARY KEY (id)
);

-- foreign keys
-- Reference: Reservas_Usuario (table: Reservas)
ALTER TABLE reservas ADD CONSTRAINT reservas_usuario FOREIGN KEY reservas_usuario (idpaciente)
    REFERENCES usuario (id);

-- Reference: fk_consultorio_profesional (table: Consultorio)
ALTER TABLE consultorio ADD CONSTRAINT fk_consultorio_profesional FOREIGN KEY fk_consultorio_profesional (id_profesional)
    REFERENCES profesional (idprofesional)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- Reference: fk_nota_archivo_nota (table: archivo_nota)
ALTER TABLE archivo_nota ADD CONSTRAINT fk_nota_archivo_nota FOREIGN KEY fk_nota_archivo_nota (nota_id)
    REFERENCES nota (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- Reference: fk_nota_reservas (table: Nota)
ALTER TABLE nota ADD CONSTRAINT fk_nota_reservas FOREIGN KEY fk_nota_reservas (reserva_id)
    REFERENCES reservas (id);

-- Reference: fk_profesional_usuario (table: Profesional)
ALTER TABLE profesional ADD CONSTRAINT fk_profesional_usuario FOREIGN KEY fk_profesional_usuario (idprofesional)
    REFERENCES usuario (id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- Reference: fk_reservas_profesional (table: Reservas)
ALTER TABLE reservas ADD CONSTRAINT fk_reservas_profesional FOREIGN KEY fk_reservas_profesional (idprofesional)
    REFERENCES profesional (idprofesional);

-- End of file.

