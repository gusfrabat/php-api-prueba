DROP DATABASE IF EXISTS Proyecto;
CREATE DATABASE IF NOT EXISTS Proyecto;

CREATE TABLE empleado (
    documento   int(11) not null,
    nombres     varchar(50),
    apellidos   varchar(50),
    telefono    bigint(10),
    correo      varchar(50) ,
    id_sede     int(5) not null,
    CONSTRAINT pk_usuario PRIMARY KEY (documento)
);

CREATE TABLE causa (
    id_causa        int(5) auto_increment not null,
    nom_causa       varchar(25) not NULL,
    soporte         varchar(50),
    CONSTRAINT pk_causa PRIMARY KEY (id_causa)
);

CREATE TABLE permiso (
    id_permiso          int(5) auto_increment NOT NULL,
    documento_empleado  int(11) not null,
    fecha               datetime,
    id_causa            int(5) not null,
    desde               datetime,
    hasta               datetime,
    CONSTRAINT pk_permiso PRIMARY KEY (id_permiso)
);

CREATE TABLE usuario (
    id_usu              int(5) auto_increment not null,
    usuario             varchar(25) not null,
    contrasena          varchar(25) not null,
    id_rol              int(5) not null,
    documento_empleado  int(11) not null,
    CONSTRAINT pk_usuario PRIMARY KEY (id_usu)
);

CREATE TABLE movimiento (
    id_mov              int(5) auto_increment not null,
    entrada             datetime,
    salida              datetime,
    dif                 time,
    id_sede             int(5) not null,
    documento_empleado  int(11) not null,
    CONSTRAINT pk_movimiento PRIMARY KEY (id_mov)
);

CREATE TABLE rol (
    id_rol          int(5) auto_increment not null,
    nom_rol      varchar(20) not null,
    CONSTRAINT  pk_rol PRIMARY KEY (id_rol)
); 

CREATE TABLE sede (
    id_sede    int(5) auto_increment not null,
    nom_sede   varchar(50) not null,
    ciudad     varchar(25) not null,
    direccion  varchar(25),
    telefono   bigint(11),
    latitud    varchar(20),
    longitud   varchar(20),  
    CONSTRAINT pk_sede PRIMARY KEY (id_sede)
);

ALTER TABLE permiso ADD CONSTRAINT fk_permiso_empleado FOREIGN KEY (documento_empleado) REFERENCES empleado (documento);
ALTER TABLE permiso ADD CONSTRAINT fk_permiso_causa FOREIGN KEY (id_causa) REFERENCES causa (id_causa);
ALTER TABLE movimiento ADD CONSTRAINT fk_movimiento_sede FOREIGN KEY (id_sede) REFERENCES sede (id_sede);
ALTER TABLE movimiento ADD CONSTRAINT fk_movimiento_empleado FOREIGN KEY (documento_empleado) REFERENCES empleado (documento);
ALTER TABLE usuario ADD CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES rol (id_rol);
ALTER TABLE empleado ADD CONSTRAINT fk_empleado_sede FOREIGN KEY (id_sede) REFERENCES sede (id_sede);
alter TABLE usuario ADD CONSTRAINT fk_usuario_empleado FOREIGN KEY (documento_empleado) REFERENCES empleado (documento);