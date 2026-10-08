<?php

require_once(__DIR__ . "/../repositories/EnergiasRepository.php");

class EnergiasController
{
    /*
     * Fator médio anual de emissão de CO2
     * do Sistema Interligado Nacional (SIN).
     *
     * Ano de referência: 2025
     * Fonte: MCTI
     *
     * 0,0461 tCO2/MWh
     * = 0,0461 kgCO2/kWh
     */
    private const FATOR_EMISSAO_ENERGIA = 0.0461;

    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function calcularEmissao($consumo_kwh)
    {
        return $consumo_kwh * self::FATOR_EMISSAO_ENERGIA;
    }

    public function adicionarEnergia(
        $id_simulacao,
        $consumo_kwh
    ) {
        $emissao = $this->calcularEmissao($consumo_kwh);

        return $this->repository->adicionarEnergia(
            $id_simulacao,
            $consumo_kwh,
            $emissao
        );
    }
}