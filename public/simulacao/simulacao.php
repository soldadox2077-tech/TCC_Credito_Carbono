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
require_once(__DIR__ . "/../../src/Controllers/SimulacoesController.php");

$repository = new SimulacoesRepository($conexao);

$controller = new SimulacoesController($repository);

$id_usuario = $_SESSION["usuario_id"];

$simulacao = $controller->buscarMaisRecente($id_usuario);

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
                Minha Simulação
            </h2>

            <?php if ($simulacao): ?>

                <section>

                    <h3>
                        Simulação mais recente
                    </h3>

                    <p>
                        Emissão total:
                        <?php echo $simulacao["emissao_total"]; ?> kg CO₂
                    </p>

                    <p>
                        Data da simulação:
                        <?php echo $simulacao["data_simulacao"]; ?>
                    </p>

                </section>

            <?php else: ?>

                <section>
                    <h3>
                        🌱 Você ainda não fez nenhuma simulação.
                    </h3>

                    <p>
                        Comece a criar uma!
                    </p>

                    <a href="transportes.php">
                    Começar simulação
                    </a>

                </section>

            <?php endif; ?>

        </main>

    </div>

</body>
</html>