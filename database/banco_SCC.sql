CREATE DATABASE simulador_database;

USE simulador_database;

CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE simulacoes (

    id_simulacao INT AUTO_INCREMENT PRIMARY KEY,

    id_usuario INT,

    km_transporte DECIMAL(10,2),

    consumo_energia DECIMAL(10,2),

    consumo_carne INT,

    emissao_total DECIMAL(10,2),

    data_simulacao DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
    REFERENCES usuarios(id_usuario)

);