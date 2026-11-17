/*
 * MIGRAÇÃO DA TABELA transportes PARA O NOVO CATÁLOGO.
 *
 * Execute este arquivo UMA ÚNICA VEZ em um banco já existente.
 * Faça um backup antes. A migração preserva a emissão registrada
 * nos transportes antigos e guarda o fator histórico calculado a
 * partir dela. Os fatores novos do catálogo não reescrevem o passado.
 */
USE simulador_database;

/* 1. Cria o catálogo de tipos de transporte. */
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

/* 2. Insere os fatores de referência 2025 no catálogo. */
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
 2025, 'Reino Unido', 'INATIVO: fator numérico não validado; não utilizar em cálculos', 0),
('Bicicleta', 0.00000000, 'kg CO2e/passageiro-km',
 'Hipótese simplificada do projeto',
 'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2025',
 2025, 'Não aplicável', 'Somente emissões operacionais diretas; ciclo de vida da bicicleta excluído', 1),
('A pé', 0.00000000, 'kg CO2e/passageiro-km',
 'Hipótese simplificada do projeto',
 'https://www.gov.uk/government/publications/greenhouse-gas-reporting-conversion-factors-2025',
 2025, 'Não aplicável', 'Somente emissões operacionais diretas; alimentação e infraestrutura excluídas', 1);

/* 3. Adiciona as novas colunas sem apagar as antigas. */
ALTER TABLE transportes
    ADD COLUMN id_tipo_transporte INT NULL,
    ADD COLUMN fator_emissao_aplicado DECIMAL(12,8) NULL,
    ADD COLUMN emissao_co2e DECIMAL(12,6) NULL;

/* 4. Relaciona cada registro antigo ao novo catálogo.
 * Os nomes antigos são preservados no mapeamento.
 */
UPDATE transportes t
JOIN tipos_transportes tt
  ON tt.nome = CASE t.tipo_transporte
      WHEN 'carro' THEN 'Carro (fator pendente de validação)'
      WHEN 'motocicleta' THEN 'Motocicleta (fator pendente de validação)'
      WHEN 'onibus' THEN 'Ônibus urbano (médio)'
      WHEN 'metro' THEN 'Metrô (fator pendente de validação)'
      WHEN 'bicicleta' THEN 'Bicicleta'
      WHEN 'a_pe' THEN 'A pé'
      ELSE NULL
  END
SET t.id_tipo_transporte = tt.id_tipo_transporte;

/* 5. Preserva a emissão antiga e calcula o fator histórico efetivamente usado. */
UPDATE transportes
SET emissao_co2e = COALESCE(emissao_co2, 0),
    fator_emissao_aplicado = CASE
        WHEN distancia_km > 0 THEN COALESCE(emissao_co2, 0) / distancia_km
        ELSE 0
    END;

/* 6. Interrompe a migração se houver tipos antigos sem correspondência. */
SELECT COUNT(*) AS registros_sem_tipo_mapeado
FROM transportes
WHERE id_tipo_transporte IS NULL;

/*
 * Confira o resultado acima. Se for zero, execute os comandos finais:
 *
 * ALTER TABLE transportes
 *   MODIFY id_tipo_transporte INT NOT NULL,
 *   MODIFY fator_emissao_aplicado DECIMAL(12,8) NOT NULL,
 *   MODIFY emissao_co2e DECIMAL(12,6) NOT NULL,
 *   ADD CONSTRAINT fk_transportes_tipo
 *       FOREIGN KEY (id_tipo_transporte)
 *       REFERENCES tipos_transportes(id_tipo_transporte);
 *
 * ALTER TABLE transportes
 *   DROP COLUMN tipo_transporte,
 *   DROP COLUMN emissao_co2;
 *
 * Os comandos finais estão comentados de propósito: primeiro confira
 * se registros_sem_tipo_mapeado retornou zero.
 */
