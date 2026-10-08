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

        mysqli_stmt_bind_param(
            $stmt,
            "idd",
            $id_simulacao,
            $consumo_kwh,
            $emissao_co2
        );

        mysqli_stmt_execute($stmt);

        return mysqli_insert_id($this->conexao);
    }
}