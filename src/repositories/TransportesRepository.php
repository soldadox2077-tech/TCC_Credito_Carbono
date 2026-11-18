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

        $stmt = mysqli_prepare($this->conexao, $sql);

        if (!$stmt) {
            return false;
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
