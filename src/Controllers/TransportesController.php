<?php

require_once(__DIR__ . "/../repositories/TransportesRepository.php");

class TransportesController
{
    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function calcularEmissao($tipo_transporte, $distancia_km)
    {
        $fatores = [
            "carro" => 0.1268,
            "motocicleta" => 0.0711,
            "onibus" => 0.0160,
            "metro" => 0.0035,
            "bicicleta" => 0.0000,
            "a_pe" => 0.0000
        ];

        if (!isset($fatores[$tipo_transporte])) {
            return false;
        }

        $fator = $fatores[$tipo_transporte];

        return $distancia_km * $fator;
    }

    public function adicionarTransporte(
        $id_simulacao,
        $tipo_transporte,
        $distancia_km
    ) {
        $emissao = $this->calcularEmissao(
            $tipo_transporte,
            $distancia_km
        );

        if ($emissao === false) {
            return false;
        }

        return $this->repository->adicionarTransporte(
            $id_simulacao,
            $tipo_transporte,
            $distancia_km,
            $emissao
        );
    }
}