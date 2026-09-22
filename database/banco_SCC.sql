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
    id_usuario INT NOT NULL,
    emissao_total DECIMAL(10,2),
    data_simulacao DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
);


CREATE TABLE transportes (
    id_transporte INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    tipo_transporte VARCHAR(50) NOT NULL,
    distancia_km DECIMAL(10,2),
    emissao_co2 DECIMAL(10,2),

    FOREIGN KEY (id_simulacao)
        REFERENCES simulacoes(id_simulacao)
);


CREATE TABLE energias (
    id_energia INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    consumo_kwh DECIMAL(10,2),
    emissao_co2 DECIMAL(10,2),

    FOREIGN KEY (id_simulacao)
        REFERENCES simulacoes(id_simulacao)
);


CREATE TABLE consumos (
    id_consumo INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    quantidade DECIMAL(10,2),
    emissao_co2 DECIMAL(10,2),

    FOREIGN KEY (id_simulacao)
        REFERENCES simulacoes(id_simulacao)
);