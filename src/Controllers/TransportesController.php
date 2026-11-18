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
     * O fator está em kg CO2e por passageiro-quilômetro.
     */
    public function calcularEmissao($distancia_km, $fator_emissao)
    {
        return $distancia_km * $fator_emissao;
    }

    /*
     * Valida os identificadores e a distância, consulta o fator
     * diretamente no catálogo e registra o resultado calculado.
     */
    public function adicionarTransporte(
        $id_simulacao,
        $id_tipo_transporte,
        $distancia_km
    ) {
        if (
            filter_var($id_simulacao, FILTER_VALIDATE_INT) === false ||
            (int) $id_simulacao <= 0 ||
            filter_var($id_tipo_transporte, FILTER_VALIDATE_INT) === false ||
            (int) $id_tipo_transporte <= 0 ||
            !is_numeric($distancia_km) ||
            !is_finite((float) $distancia_km) ||
            (float) $distancia_km <= 0 ||
            (float) $distancia_km > 99999999.9999
        ) {
            return false;
        }

        $tipo = $this->repository->buscarTipoPorId(
            (int) $id_tipo_transporte
        );

        if (!$tipo || !is_numeric($tipo["fator_emissao"])) {
            return false;
        }

        $distancia_km = (float) $distancia_km;
        $fator = (float) $tipo["fator_emissao"];

        if (!is_finite($fator) || $fator < 0) {
            return false;
        }

        $emissao = $this->calcularEmissao($distancia_km, $fator);

        /* O campo emissao_co2e é DECIMAL(12,6). */
        if (
            !is_finite($emissao) ||
            $emissao < 0 ||
            $emissao > 999999.999999
        ) {
            return false;
        }

        return $this->repository->adicionarTransporte(
            (int) $id_simulacao,
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
