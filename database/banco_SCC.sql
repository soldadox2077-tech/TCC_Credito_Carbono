CREATE DATABASE IF NOT EXISTS simulador_database
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simulador_database;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS simulacoes (
    id_simulacao INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    emissao_total DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    data_simulacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_simulacoes_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transportes (
    id_transporte INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    tipo_transporte VARCHAR(50) NOT NULL,
    distancia_km DECIMAL(10,2) NOT NULL,
    emissao_co2 DECIMAL(12,3) NOT NULL,
    CONSTRAINT fk_transportes_simulacao FOREIGN KEY (id_simulacao)
        REFERENCES simulacoes(id_simulacao) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS energias (
    id_energia INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    consumo_kwh DECIMAL(10,2) NOT NULL,
    emissao_co2 DECIMAL(12,3) NOT NULL,
    CONSTRAINT fk_energias_simulacao FOREIGN KEY (id_simulacao)
        REFERENCES simulacoes(id_simulacao) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consumos (
    id_consumo INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    quantidade DECIMAL(10,2) NOT NULL,
    emissao_co2 DECIMAL(12,3) NOT NULL,
    CONSTRAINT fk_consumos_simulacao FOREIGN KEY (id_simulacao)
        REFERENCES simulacoes(id_simulacao) ON DELETE CASCADE
) ENGINE=InnoDB;
