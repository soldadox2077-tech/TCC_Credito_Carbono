<?php

class TransportesRepository
{
    private $conexao;

    public function __construct($conexao)
    {
        $this->conexao = $conexao;
    }

    public function adicionarTransporte(
        $id_simulacao,
        $tipo_transporte,
        $distancia_km,
        $emissao_co2
    ) {
        $sql = "INSERT INTO transportes
                (id_simulacao, tipo_transporte, distancia_km, emissao_co2)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conexao, $sql);

        if (!$stmt) return false;
        mysqli_stmt_bind_param(
            $stmt,
            "isdd",
            $id_simulacao,
            $tipo_transporte,
            $distancia_km,
            $emissao_co2
        );

        if (!mysqli_begin_transaction($this->conexao)) return false;
        if (!mysqli_stmt_execute($stmt)) { mysqli_rollback($this->conexao); return false; }
        $id = mysqli_insert_id($this->conexao);
        $total = mysqli_prepare($this->conexao, "UPDATE simulacoes SET emissao_total = emissao_total + ? WHERE id_simulacao = ?");
        if (!$total) { mysqli_rollback($this->conexao); return false; }
        mysqli_stmt_bind_param($total, "di", $emissao_co2, $id_simulacao);
        if (!mysqli_stmt_execute($total)) { mysqli_rollback($this->conexao); return false; }
        if (!mysqli_commit($this->conexao)) return false;

        return $id;
    }

    public function listarPorSimulacao($id_simulacao)
    {
        $sql = "SELECT *
                FROM transportes
                WHERE id_simulacao = ?
                ORDER BY id_transporte ASC";

        $stmt = mysqli_prepare($this->conexao, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_simulacao
        );

        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    }
}
