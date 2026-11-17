<?php

require_once(__DIR__ . "/../repositories/TransportesRepository.php");

class TransportesController
{
    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /*
     * Calcula a emissão a partir do fator cadastrado no banco.
     * A unidade de referência do catálogo é kg CO2e/passageiro-km.
     */
    public function calcularEmissao($distancia_km, $fator_emissao)
    {
        return $distancia_km * $fator_emissao;
    }

    /*
     * Valida o ID, recupera o fator confiável no repositório
     * e só então calcula e registra a emissão.
     */
    public function adicionarTransporte(
        $id_simulacao,
        $id_tipo_transporte,
        $distancia_km
    ) {
        if (
            !is_numeric($distancia_km) ||
            !is_finite((float) $distancia_km) ||
            (float) $distancia_km <= 0
        ) {
            return false;
        }

        $tipo = $this->repository->buscarTipoPorId(
            $id_tipo_transporte
        );

        if (!$tipo) {
            return false;
        }

        $distancia_km = (float) $distancia_km;
        $fator = (float) $tipo["fator_emissao"];
        $emissao = $this->calcularEmissao($distancia_km, $fator);

        return $this->repository->adicionarTransporte(
            $id_simulacao,
            (int) $tipo["id_tipo_transporte"],
            $distancia_km,
            $fator,
            $emissao
        );
    }

    public function listarTiposTransportes()
    {
        return $this->repository->listarTiposTransportes();
    }

    public function listarPorSimulacao($id_simulacao)
    {
        return $this->repository->listarPorSimulacao($id_simulacao);
    }
}
