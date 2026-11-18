<?php

class EnergiasRepository
{
    private $conexao;

    public function __construct($conexao)
    {
        $this->conexao = $conexao;
    }

    public function adicionarEnergia(
        $id_simulacao,
        $consumo_kwh,
        $emissao_co2
    ) {
        $sql = "INSERT INTO energias
                (id_simulacao, consumo_kwh, emissao_co2)
                VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($this->conexao, $sql);

        if (!$stmt) return false;
        mysqli_stmt_bind_param(
            $stmt,
            "idd",
            $id_simulacao,
            $consumo_kwh,
            $emissao_co2
        );

        if (!$stmt || !mysqli_begin_transaction($this->conexao)) return false;
        if (!mysqli_stmt_execute($stmt)) { mysqli_rollback($this->conexao); return false; }
        $id = mysqli_insert_id($this->conexao);
        $total = mysqli_prepare($this->conexao, "UPDATE simulacoes SET emissao_total = emissao_total + ? WHERE id_simulacao = ?");
        if (!$total) { mysqli_rollback($this->conexao); return false; }
        mysqli_stmt_bind_param($total, "di", $emissao_co2, $id_simulacao);
        if (!mysqli_stmt_execute($total)) { mysqli_rollback($this->conexao); return false; }
        if (!mysqli_commit($this->conexao)) return false;

        return $id;
    }
}
