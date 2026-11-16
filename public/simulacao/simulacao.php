
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

$erro = null;

// Verifica se o usuário solicitou uma nova simulação
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nova_simulacao"])) {

    // Cria uma nova simulação no banco de dados
    $id_simulacao = $controller->criar($id_usuario);

    if ($id_simulacao) {

        // Guarda o ID da nova simulação na sessão
        $_SESSION["simulacao_id"] = $id_simulacao;

        // Redireciona para a página de transportes
        header("Location: transportes.php");
        exit();

    } else {

        $erro = "Não foi possível criar sua simulação.";

    }
}

// Busca a simulação mais recente do usuário
$simulacao = $controller->buscarMaisRecente($id_usuario);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <meta charset="UTF-8">

    <title>Simulação - Simulador de Carbono</title>

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

            <?php if ($erro): ?>

                <p>
                    <?php echo htmlspecialchars($erro); ?>
                </p>

            <?php endif; ?>

            <?php if ($simulacao): ?>

                <section>

                    <h3>
                        Simulação mais recente
                    </h3>

                    <p>
                        Emissão total:
                        <?php echo htmlspecialchars($simulacao["emissao_total"]); ?> kg CO₂
                    </p>

                    <p>
                        Data da simulação:
                        <?php echo htmlspecialchars($simulacao["data_simulacao"]); ?>
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

                </section>

            <?php endif; ?>

            <section>

                <h3>
                    Criar uma nova simulação
                </h3>

                <form method="POST">

                    <button type="submit" name="nova_simulacao">
                        Criar nova simulação
                    </button>

                </form>

            </section>

        </main>

    </div>

</body>
</html>
