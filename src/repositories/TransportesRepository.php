<?php

class TransportesRepository
{
    private $conexao;

    public function __construct($conexao)
    {
        $this->conexao = $conexao;
    }

    /*
     * Retorna os tipos ativos do catálogo.
     * Retorna false em falha SQL, diferenciando erro de catálogo vazio.
     */
    public function listarTiposTransportes()
    {
        $sql = "SELECT id_tipo_transporte, nome, fator_emissao,
                       unidade_referencia, fonte, ano_referencia,
                       escopo_geografico, limite_sistema
                FROM tipos_transportes
                WHERE ativo = 1
                ORDER BY id_tipo_transporte ASC";

        $resultado = mysqli_query($this->conexao, $sql);

        if (!$resultado) {
            return false;
        }

        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    }

    /*
     * Busca somente tipos ativos. O fator nunca vem do formulário.
     */
    public function buscarTipoPorId($id_tipo_transporte)
    {
        $sql = "SELECT id_tipo_transporte, nome, fator_emissao,
                       unidade_referencia, fonte, ano_referencia,
                       escopo_geografico, limite_sistema
                FROM tipos_transportes
                WHERE id_tipo_transporte = ? AND ativo = 1";

        $stmt = mysqli_prepare($this->conexao, $sql);

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $id_tipo_transporte);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $resultado = mysqli_stmt_get_result($stmt);

        if (!$resultado) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $tipo = mysqli_fetch_assoc($resultado) ?: null;
        mysqli_stmt_close($stmt);

        return $tipo;
    }

    /*
     * Guarda também o fator aplicado, preservando o histórico
     * caso o catálogo seja atualizado no futuro.
     */
    public function adicionarTransporte(
        $id_simulacao,
        $id_tipo_transporte,
        $distancia_km,
        $fator_emissao_aplicado,
        $emissao_co2e
    ) {
        $sql = "INSERT INTO transportes
                (id_simulacao, id_tipo_transporte, distancia_km,
                 fator_emissao_aplicado, emissao_co2e)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conexao, $sql);

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param(
            $stmt,
            "iiddd",
            $id_simulacao,
            $id_tipo_transporte,
            $distancia_km,
            $fator_emissao_aplicado,
            $emissao_co2e
        );

        if (!mysqli_begin_transaction($this->conexao)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_rollback($this->conexao);
            mysqli_stmt_close($stmt);
            return false;
        }

        $id = mysqli_insert_id($this->conexao);
        mysqli_stmt_close($stmt);

        $total = mysqli_prepare(
            $this->conexao,
            "UPDATE simulacoes SET emissao_total = emissao_total + ? WHERE id_simulacao = ?"
        );
        if (!$total) {
            mysqli_rollback($this->conexao);
            return false;
        }
        mysqli_stmt_bind_param($total, "di", $emissao_co2e, $id_simulacao);
        if (!mysqli_stmt_execute($total)) {
            mysqli_rollback($this->conexao);
            mysqli_stmt_close($total);
            return false;
        }
        mysqli_stmt_close($total);
        if (!mysqli_commit($this->conexao)) return false;

        return $id;
    }

    /*
     * Persiste os novos fatores PHP usando a tabela existente de consumos,
     * sem exigir tabela ou coluna nova. O fator fica junto da categoria
     * para que o histórico preserve o valor usado no cálculo.
     */
    public function adicionarEstimativaTransporte($id_simulacao, $tipo, $atividade, $fator, $emissao)
    {
        $categoria = sprintf("transporte:%s:%.8f", $tipo, $fator);
        if (strlen($categoria) > 50 || !mysqli_begin_transaction($this->conexao)) return false;

        /* emissao_co2 é DECIMAL(10,2); guardamos centi-kg para preservar casas decimais. */
        $emissaoPersistida = $emissao * 100;
        $stmt = mysqli_prepare($this->conexao, "INSERT INTO consumos (id_simulacao, categoria, quantidade, emissao_co2) VALUES (?, ?, ?, ?)");
        if (!$stmt) { mysqli_rollback($this->conexao); return false; }
        mysqli_stmt_bind_param($stmt, "isdd", $id_simulacao, $categoria, $atividade, $emissaoPersistida);
        if (!mysqli_stmt_execute($stmt)) { mysqli_rollback($this->conexao); mysqli_stmt_close($stmt); return false; }
        $id = mysqli_insert_id($this->conexao);
        mysqli_stmt_close($stmt);

        $total = mysqli_prepare($this->conexao, "UPDATE simulacoes SET emissao_total = emissao_total + ? WHERE id_simulacao = ?");
        if (!$total) { mysqli_rollback($this->conexao); return false; }
        mysqli_stmt_bind_param($total, "di", $emissao, $id_simulacao);
        if (!mysqli_stmt_execute($total)) { mysqli_rollback($this->conexao); mysqli_stmt_close($total); return false; }
        mysqli_stmt_close($total);
        if (!mysqli_commit($this->conexao)) return false;
        return $id;
    }

    public function listarEstimativasPorSimulacao($id_simulacao, array $tipos)
    {
        $stmt = mysqli_prepare($this->conexao, "SELECT id_consumo, categoria, quantidade, emissao_co2 / 100.0 AS emissao_kg FROM consumos WHERE id_simulacao = ? AND categoria LIKE 'transporte:%' ORDER BY id_consumo ASC");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "i", $id_simulacao);
        if (!mysqli_stmt_execute($stmt)) { mysqli_stmt_close($stmt); return false; }
        $resultado = mysqli_stmt_get_result($stmt);
        if (!$resultado) { mysqli_stmt_close($stmt); return false; }

        $linhas = [];
        while ($linha = mysqli_fetch_assoc($resultado)) {
            $partes = explode(":", $linha["categoria"]);
            $chave = $partes[1] ?? "";
            $fator = isset($partes[2]) ? (float) $partes[2] : null;
            if (!isset($tipos[$chave]) || $fator === null) continue;
            $tipo = $tipos[$chave];
            $linhas[] = [
                "id_transporte" => (int) $linha["id_consumo"],
                "tipo_transporte" => $tipo["nome"],
                "atividade" => (float) $linha["quantidade"],
                "unidade_atividade" => $tipo["atividade"],
                "fator_emissao_aplicado" => $fator,
                "emissao_co2e" => (float) $linha["emissao_kg"],
                "unidade_referencia" => $tipo["unidade_referencia"],
                "fonte" => $tipo["fonte"],
                "url_fonte" => $tipo["url_fonte"] ?? "",
                "ano_referencia" => $tipo["ano"],
                "escopo_geografico" => $tipo["escopo"],
                "registro_php" => true,
            ];
        }
        mysqli_stmt_close($stmt);
        return $linhas;
    }

    /*
     * Lista os deslocamentos da simulação e recupera o nome/fonte
     * pelo relacionamento com tipos_transportes.
     */
    public function listarPorSimulacao($id_simulacao)
    {
        $sql = "SELECT t.id_transporte, t.distancia_km,
                       t.fator_emissao_aplicado, t.emissao_co2e,
                       tt.nome AS tipo_transporte,
                       tt.unidade_referencia, tt.fonte,
                       tt.ano_referencia, tt.escopo_geografico
                FROM transportes t
                INNER JOIN tipos_transportes tt
                    ON tt.id_tipo_transporte = t.id_tipo_transporte
                WHERE t.id_simulacao = ?
                ORDER BY t.id_transporte ASC";

        try {
            $stmt = mysqli_prepare($this->conexao, $sql);
        } catch (mysqli_sql_exception $erro) {
            if ((int) $erro->getCode() === 1146) return [];
            throw $erro;
        }

        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, "i", $id_simulacao);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $resultado = mysqli_stmt_get_result($stmt);

        if (!$resultado) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $transportes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);

        return $transportes;
    }
}
