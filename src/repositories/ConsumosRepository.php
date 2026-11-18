<?php
class ConsumosRepository
{
    private $conexao;
    public function __construct($conexao) { $this->conexao = $conexao; }

    public function adicionar(int $idSimulacao, string $categoria, float $quantidade, float $emissao): int|false
    {
        if (!mysqli_begin_transaction($this->conexao)) return false;
        $stmt = mysqli_prepare($this->conexao, "INSERT INTO consumos (id_simulacao, categoria, quantidade, emissao_co2) VALUES (?, ?, ?, ?)");
        if (!$stmt) { mysqli_rollback($this->conexao); return false; }
        mysqli_stmt_bind_param($stmt, "isdd", $idSimulacao, $categoria, $quantidade, $emissao);
        if (!mysqli_stmt_execute($stmt)) { mysqli_rollback($this->conexao); return false; }
        $id = mysqli_insert_id($this->conexao);
        $total = mysqli_prepare($this->conexao, "UPDATE simulacoes SET emissao_total = emissao_total + ? WHERE id_simulacao = ?");
        if (!$total) { mysqli_rollback($this->conexao); return false; }
        mysqli_stmt_bind_param($total, "di", $emissao, $idSimulacao);
        if (!mysqli_stmt_execute($total)) { mysqli_rollback($this->conexao); return false; }
        if (!mysqli_commit($this->conexao)) return false;
        return $id;
    }
}
