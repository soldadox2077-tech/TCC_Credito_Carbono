/*
 * MIGRAÇÃO DA TABELA transportes PARA O NOVO CATÁLOGO.
 *
 * Use somente em banco que já possui a estrutura ANTIGA:
 * tipo_transporte, distancia_km e emissao_co2.
 *
 * Faça backup antes. Execute uma única vez. Não execute este arquivo
 * sobre um banco criado integralmente por banco_SCC.sql.
 *
 * A migração preserva os valores antigos de emissão. Os fatores novos
 * não recalculam registros históricos.
 */
USE simulador_database;

/* 1. Cria o catálogo. Esta etapa pressupõe que ele ainda não existe. */
CREATE TABLE tipos_transportes (
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
 * 2. Cadastra opções. Carro, moto, ônibus e metrô ficam inativos
 * até que seus fatores sejam validados. Bicicleta e caminhada têm
 * zero somente para emissões operacionais diretas.
 */
INSERT INTO tipos_transportes
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

/* 3. Adiciona colunas novas sem apagar os dados antigos. */
ALTER TABLE transportes
    ADD COLUMN id_tipo_transporte INT NULL,
    ADD COLUMN fator_emissao_aplicado DECIMAL(12,8) NULL,
    ADD COLUMN emissao_co2e DECIMAL(12,6) NULL;

/*
 * 4. Associa cada tipo antigo ao registro correspondente.
 * Os tipos desativados continuam servindo para identificar registros
 * históricos, mas não poderão ser selecionados em novas operações.
 */
UPDATE transportes t
JOIN tipos_transportes tt
  ON tt.nome = CASE t.tipo_transporte
      WHEN 'carro' THEN 'Carro (fator pendente de validação)'
      WHEN 'motocicleta' THEN 'Motocicleta (fator pendente de validação)'
      WHEN 'onibus' THEN 'Ônibus urbano (fator pendente de validação)'
      WHEN 'metro' THEN 'Metrô (fator pendente de validação)'
      WHEN 'bicicleta' THEN 'Bicicleta'
      WHEN 'a_pe' THEN 'A pé'
      ELSE NULL
  END
SET t.id_tipo_transporte = tt.id_tipo_transporte;

/* 5. Preserva a emissão antiga e registra a taxa histórica implícita. */
UPDATE transportes
SET emissao_co2e = COALESCE(emissao_co2, 0),
    fator_emissao_aplicado = CASE
        WHEN distancia_km > 0 THEN COALESCE(emissao_co2, 0) / distancia_km
        ELSE 0
    END;

/*
 * 6. Verificação obrigatória.
 * Se o resultado não for zero, NÃO execute os comandos finais.
 */
SELECT COUNT(*) AS registros_sem_tipo_mapeado
FROM transportes
WHERE id_tipo_transporte IS NULL;

/*
 * 7. Somente depois de confirmar que registros_sem_tipo_mapeado = 0,
 * execute os comandos abaixo manualmente.
 *
 * ALTER TABLE transportes ENGINE=InnoDB;
 * ALTER TABLE simulacoes ENGINE=InnoDB;
 *
 * ALTER TABLE transportes
 *   MODIFY id_tipo_transporte INT NOT NULL,
 *   MODIFY fator_emissao_aplicado DECIMAL(12,8) NOT NULL,
 *   MODIFY emissao_co2e DECIMAL(12,6) NOT NULL,
 *   ADD CONSTRAINT fk_transportes_tipo
 *       FOREIGN KEY (id_tipo_transporte)
 *       REFERENCES tipos_transportes(id_tipo_transporte);
 *
 * Não remova tipo_transporte nem emissao_co2 antes de conferir
 * os registros migrados e manter um backup íntegro.
 */
