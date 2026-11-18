CREATE DATABASE IF NOT EXISTS simulador_database
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simulador_database;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS simulacoes (
    id_simulacao INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    emissao_total DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    data_simulacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_simulacoes_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

/*
 * Catálogo de fatores de transporte.
 * Unidade: kg CO2e por passageiro-km.
 * Os fatores que ainda não foram validados ficam inativos.
 * Não usar fatores de referência estrangeiros como se fossem
 * medições específicas da realidade brasileira.
 */
CREATE TABLE IF NOT EXISTS tipos_transportes (
    id_tipo_transporte INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    fator_emissao DECIMAL(12,8) NOT NULL,
    unidade_referencia VARCHAR(40) NOT NULL DEFAULT 'kg CO2e/passageiro-km',
    fonte VARCHAR(180) NOT NULL,
    url_fonte VARCHAR(500) NULL,
    ano_referencia SMALLINT NOT NULL,
    escopo_geografico VARCHAR(80) NOT NULL,
    limite_sistema VARCHAR(180) NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

/*
 * Carro, motocicleta, ônibus e metrô permanecem cadastrados,
 * mas INATIVOS até a validação de seus fatores, unidade,
 * ocupação e limites metodológicos.
 *
 * Bicicleta e caminhada usam fator operacional direto igual a zero.
 * Isso não representa emissões de fabricação, manutenção,
 * infraestrutura ou alimentação.
 */
INSERT IGNORE INTO tipos_transportes
(nome, fator_emissao, unidade_referencia, fonte, url_fonte, ano_referencia, escopo_geografico, limite_sistema, ativo)
VALUES
('Carro (fator pendente de validação)', 0.00000000, 'kg CO2e/passageiro-km',
 'Fator pendente de validação', NULL,
 2025, 'Não definido', 'INATIVO: não utilizar em cálculos até validação metodológica', 0),
('Motocicleta (fator pendente de validação)', 0.00000000, 'kg CO2e/passageiro-km',
 'Fator pendente de validação', NULL,
 2025, 'Não definido', 'INATIVO: não utilizar em cálculos até validação metodológica', 0),
('Ônibus urbano (fator pendente de validação)', 0.00000000, 'kg CO2e/passageiro-km',
 'Fator pendente de validação', NULL,
 2025, 'Não definido', 'INATIVO: valor anterior não foi confirmado na fonte primária', 0),
('Metrô (fator pendente de validação)', 0.00000000, 'kg CO2e/passageiro-km',
 'Fator pendente de validação', NULL,
 2025, 'Não definido', 'INATIVO: não utilizar em cálculos até validação metodológica', 0),
('Bicicleta', 0.00000000, 'kg CO2e/passageiro-km',
 'Hipótese operacional simplificada do projeto', NULL,
 2025, 'Não aplicável', 'Somente emissões operacionais diretas; ciclo de vida excluído', 1),
('A pé', 0.00000000, 'kg CO2e/passageiro-km',
 'Hipótese operacional simplificada do projeto', NULL,
 2025, 'Não aplicável', 'Somente emissões operacionais diretas; alimentação e infraestrutura excluídas', 1);

CREATE TABLE IF NOT EXISTS transportes (
    id_transporte INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    id_tipo_transporte INT NOT NULL,
    distancia_km DECIMAL(12,4) NOT NULL,
    fator_emissao_aplicado DECIMAL(12,8) NOT NULL,
    emissao_co2e DECIMAL(12,6) NOT NULL,
    CONSTRAINT fk_transportes_simulacao
        FOREIGN KEY (id_simulacao) REFERENCES simulacoes(id_simulacao),
    CONSTRAINT fk_transportes_tipo
        FOREIGN KEY (id_tipo_transporte) REFERENCES tipos_transportes(id_tipo_transporte)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS energias (
    id_energia INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    consumo_kwh DECIMAL(10,2),
    emissao_co2 DECIMAL(10,2),
    FOREIGN KEY (id_simulacao) REFERENCES simulacoes(id_simulacao)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS consumos (
    id_consumo INT AUTO_INCREMENT PRIMARY KEY,
    id_simulacao INT NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    quantidade DECIMAL(10,2),
    emissao_co2 DECIMAL(10,2),
    FOREIGN KEY (id_simulacao) REFERENCES simulacoes(id_simulacao)
) ENGINE=InnoDB;
