<?php

class SimulacoesRepository
{
    private $conexao;

    public function __construct($conexao)
    {
        $this->conexao = $conexao;
    }

    public function criarSimulacao($id_usuario)
    {
        $sql = "INSERT INTO simulacoes (id_usuario, emissao_total)
                VALUES (?, 0.00)";

        $stmt = mysqli_prepare($this->conexao, $sql);
        if (!$stmt) return false;

        mysqli_stmt_bind_param($stmt, "i", $id_usuario);

        if (!mysqli_stmt_execute($stmt)) return false;

        return mysqli_insert_id($this->conexao);
    }

    public function buscarMaisRecente($id_usuario)
    {
        $sql = "SELECT *
                FROM simulacoes
                WHERE id_usuario = ?
                ORDER BY data_simulacao DESC
                LIMIT 1";

        $stmt = mysqli_prepare($this->conexao, $sql);
        if (!$stmt) return null;

        mysqli_stmt_bind_param($stmt, "i", $id_usuario);

        if (!mysqli_stmt_execute($stmt)) return null;

        $resultado = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($resultado);
    }
}
