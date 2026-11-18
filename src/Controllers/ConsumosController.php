<?php
require_once(__DIR__ . "/../repositories/ConsumosRepository.php");

class ConsumosController
{
    /*
     * Médias globais Poore & Nemecek (2018), ano de referência 2010,
     * processadas por Our World in Data. Unidade: kg CO2e/kg de produto.
     * Não são fatores específicos do Brasil; bovinos usam a categoria
     * "beef herd". Transporte da cadeia até o varejo já está no escopo.
     * Fonte: https://ourworldindata.org/grapher/ghg-per-kg-poore
     */
    private const FATORES = [
        "carne_bovina" => 99.48,
        "carne_suina" => 12.31,
        "frango" => 9.87,
        "ovos" => 4.67,
        "leite" => 3.15,
        "arroz" => 4.45,
        "tomate" => 2.09,
        "milho" => 1.70,
        "trigo" => 1.57,
        "ervilha" => 0.98,
        "banana" => 0.86,
        "batata" => 0.46,
        "vegetais" => 0.53,
    ];
    private $repository;
    public function __construct($repository) { $this->repository = $repository; }
    public function categorias(): array { return self::FATORES; }
    public function adicionar(int $idSimulacao, string $categoria, float $quantidade)
    {
        if (!isset(self::FATORES[$categoria]) || $quantidade <= 0 || $quantidade > 100000) return false;
        return $this->repository->adicionar($idSimulacao, $categoria, $quantidade, $quantidade * self::FATORES[$categoria]);
    }
}
