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

        mysqli_stmt_bind_param($stmt, "i", $id_usuario);

        mysqli_stmt_execute($stmt);

        return mysqli_insert_id($this->conexao);
    }
}