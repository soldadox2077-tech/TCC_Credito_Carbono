<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . "/../src/config/banco.php");
require_once(__DIR__ . "/../src/repositories/TransportesRepository.php");

$repository = new TransportesRepository($conexao);

$id_simulacao = 1;

$id = $repository->adicionarTransporte(
    $id_simulacao,
    "motocicleta",
    50.00,
    3.555
);

echo "Transporte criado com ID: " . $id . "<br>";

$transportes = $repository->listarPorSimulacao($id_simulacao);

echo "<pre>";
print_r($transportes);
echo "</pre>";