<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {

    header("Location: login.php");
    exit();

}

$nome = $_SESSION["usuario_nome"];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <title>Dashboard - Simulador de Carbono</title>
    
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <div class="dashboard">
    <header>

        <h1>
            🌱 Simulador de Crédito de Carbono
        </h1>

        <a href="logout.php" onclick="return confirmarLogout()">
            Sair
        </a>

    </header>


    <main>

        <h2>
            Olá, <?php echo $nome; ?>!
        </h2>

        <p id="saudacao"></p>

        <p id="data"></p>

        <p id="mensagem"></p>

        <p>
            Bem-vindo ao seu painel.
        </p>


        <section>

            <h3>
                Nova simulação
            </h3>

            <p>
                Calcule sua estimativa de emissão de carbono.
            </p>

            <a href="simulacao/simulacao.php">
                Nova Simulação
            </a>

        </section>


        <section>

            <h3>
                Histórico
            </h3>

            <p>
                Consulte suas simulações anteriores.
            </p>

            <a href="#">
                Meu Histórico
            </a>

        </section>

    </main>
</div>

<script src="assets/js/script.js"></script>

<script src="assets/js/dashboard.js"></script>


</body>

</html>