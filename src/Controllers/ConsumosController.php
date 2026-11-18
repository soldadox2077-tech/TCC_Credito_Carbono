<?php
require_once(__DIR__ . "/../repositories/ConsumosRepository.php");

class ConsumosController
{
    // Estimativas simplificadas em kg CO2e por kg informado; revisar com fonte local antes de uso oficial.
    private const FATORES = ["carne_bovina" => 27.0, "frango" => 6.9, "arroz" => 2.7, "vegetais" => 2.0];
    private $repository;
    public function __construct($repository) { $this->repository = $repository; }
    public function categorias(): array { return self::FATORES; }
    public function adicionar(int $idSimulacao, string $categoria, float $quantidade)
    {
        if (!isset(self::FATORES[$categoria]) || $quantidade <= 0 || $quantidade > 100000) return false;
        return $this->repository->adicionar($idSimulacao, $categoria, $quantidade, $quantidade * self::FATORES[$categoria]);
    }
}
