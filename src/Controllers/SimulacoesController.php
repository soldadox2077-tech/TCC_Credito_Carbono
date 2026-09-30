<?php

require_once(__DIR__ . "/../repositories/SimulacoesRepository.php");

class SimulacoesController
{
    private $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function criar($id_usuario)
    {
        return $this->repository->criarSimulacao($id_usuario);
    }

    public function buscarMaisRecente($id_usuario)
    {
        return $this->repository->buscarMaisRecente($id_usuario);
    }
}