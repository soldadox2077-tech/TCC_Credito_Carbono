CREATE DATABASE IF NOT EXISTS simulador_database;
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
    emissao_total DECIMAL(10,2) DEFAULT 0.00,
    data_simulacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

/*
 * Catálogo de fatores de transporte.
 * Os fatores estão em kg CO2e por passageiro-km.
 * A fonte de referência inicial é o conjunto DEFRA/DESNZ 2025,
 * do Reino Unido. Portanto, são valores de referência internacionais,
 * não medições específicas da frota ou da matriz energética brasileira.
 */
CREATE TABLE tipos_transportes (
    id_tipo_transporte INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    fator_emissao DECIMAL(12,8) NOT NULL,
    unidade_referencia VARCHAR(40) NOT NULL DEFAULT 'kg CO2e/passageiro-km',
    fonte VARCHAR(180) NOT NULL,
    url_fonte VARCHAR(500) NOT NULL,
    ano_referencia SMALLINT NOT NULL,
    escopo_geografico VARCHAR(80) NOT NULL,
    limite_sistema VARCHAR(180) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1
);

/*
 * Fatores-base 2025.
 * Carro: fator médio por veículo-km dividido pela ocupação média de 1,54.
 * Motocicleta: fator por veículo-km, assumindo um ocupante.
 * Ônibus e metrô: fatores por passageiro-km.
 * Bicicleta/a pé: zero apenas para emissões operacionais diretas;
 * fabricação, manutenção e infraestrutura não estão incluídas.
 */
INSERT INTO tipos_transportes
(nome, fator_emissao, unidade_referencia, fonte, url_fonte, ano_referencia, escopo_geografico, limite_sistema, ativo)
VALUES
('Carro (fator pendente de validação)', 0.00000000, 'kg CO2e/passageiro-km',
 'UK Government GHG Conversion Factors 2025 - DEFRA/DESNZ',
 'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2025',
 2025, 'Reino Unido', 'INATIVO: fator numérico não validado; não utilizar em cálculos', 0),
('Motocicleta (fator pendente de validação)', 0.00000000, 'kg CO2e/passageiro-km',
 'UK Government GHG Conversion Factors 2025 - DEFRA/DESNZ',
 'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2025',
 2025, 'Reino Unido', 'INATIVO: fator numérico não validado; não utilizar em cálculos', 0),
('Ônibus urbano (médio)', 0.10385000, 'kg CO2e/passageiro-km',
 'UK Government GHG Conversion Factors 2025 - DEFRA/DESNZ',
 'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2025',
 2025, 'Reino Unido', 'Emissões diretas de uso; ocupação média de passageiros considerada na fonte', 1),
('Metrô (fator pendente de validação)', 0.00000000, 'kg CO2e/passageiro-km',
 'UK Government GHG Conversion Factors 2025 - DEFRA/DESNZ',
 'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2025',
 2025, 'Reino Unido', 'INATIVO: fator numérico não validado; não utilizar em cálculos'),
('Bicicleta', 0.00000000, 'kg CO2e/passageiro-km',
 'Hipótese simplificada do projeto',
 'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2025',
 2025, 'Não aplicável', 'Somente emissões operacionais diretas; ciclo de vida da bicicleta excluído', 1),
('A pé', 0.00000000, 'kg CO2e/passageiro-km',
 'Hipótese simplificada do projeto',
 'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2025',
 2025, 'Não aplicável', 'Somente emissões operacionais diretas; alimentação e infraestrutura excluídas', 1);

CREATE TABLE transportes (
    id_transporte INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    id_tipo_transporte INT NOT NULL,
    distancia_km DECIMAL(12,4) NOT NULL,
    fator_emissao_aplicado DECIMAL(12,8) NOT NULL,
    emissao_co2e DECIMAL(12,6) NOT NULL,
    FOREIGN KEY (id_simulacao) REFERENCES simulacoes(id_simulacao),
    FOREIGN KEY (id_tipo_transporte) REFERENCES tipos_transportes(id_tipo_transporte)
);

CREATE TABLE energias (
    id_energia INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    consumo_kwh DECIMAL(10,2),
    emissao_co2 DECIMAL(10,2),
    FOREIGN KEY (id_simulacao) REFERENCES simulacoes(id_simulacao)
);

CREATE TABLE consumos (
    id_consumo INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    quantidade DECIMAL(10,2),
    emissao_co2 DECIMAL(10,2),
    FOREIGN KEY (id_simulacao) REFERENCES simulacoes(id_simulacao)
);
