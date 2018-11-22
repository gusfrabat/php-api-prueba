DROP DATABASE IF EXISTS Curso_Angular;
CREATE DATABASE IF NOT EXISTS Curso_Angular;

USE Curso_Angular;

CREATE TABLE productos (
    id              int(25) auto_increment not null,
    nombre          VARCHAR(255),
    descripcion     text,
    precio          VARCHAR(255),
    imagen          VARCHAR(255),
    CONSTRAINT pk_productos_id PRIMARY KEY (id)
);

CREATE TABLE entradaproductos (
    id              int(25) auto_increment not null,
    create_at       DATETIME,
    id_producto     int(25) not null,
    CONSTRAINT pk_productosentrada_id PRIMARY KEY (id)
);

CREATE TABLE usuario (
    usuario VARCHAR(100) not null,
    nombres VARCHAR(50) not null,
    apellidos VARCHAR(50) not null,
    contrasena VARCHAR(50) not null,
    rol_id int(10) not null,
    direccion varchar(50),
    documento int(15) NOT NULL,
    telefono int(50) ,
    foto varchar(100),
    CONSTRAINT pk_usuario PRIMARY KEY (documento)
);


CREATE TABLE entradausuario (
    id                      int(25) auto_increment not null,
    entrada               DATETIME,
    salida               DATETIME,
    documento_usuario      int(15) not null,
    CONSTRAINT pk_productosentrada_id PRIMARY KEY (id)
) ;

CREATE TABLE rol (
    id int(25) not null,
    nombre varchar(25) not null,
    CONSTRAINT pk_rol_id PRIMARY KEY (id)
);

ALTER TABLE entradausuario ADD CONSTRAINT FOREIGN KEY (documento_usuario) REFERENCES usuario(documento);
ALTER TABLE entradaproductos ADD CONSTRAINT FOREIGN KEY (id_producto) REFERENCES productos(id);
ALTER TABLE usuario ADD CONSTRAINT FOREIGN KEY (rol_id) REFERENCES rol(id);