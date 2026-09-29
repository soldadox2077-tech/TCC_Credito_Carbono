<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

if (!isset($_SESSION["usuario_id"])) {

    header("Location: ../login.php");
    exit();

}

require_once(__DIR__ . "/../../src/config/banco.php");
require_once(__DIR__ . "/../../src/repositories/SimulacoesRepository.php");
require_once(__DIR__ . "/../../src/controllers/SimulacoesController.php");

$repository = new SimulacoesRepository($conexao);

$controller = new SimulacoesController($repository);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">

    <title>Simulacao - Simulador de Carbono</title>
    
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <header>
            
            <h1>
                Simulador de Crédito de Carbono
            </h1>

            <a href="../dashboard.php">
                Voltar à tela inicial
            </a>
        
        </header>

        <main>

            <h2>
                
            </h2>

        </main>

    </div>

</body>
</html>