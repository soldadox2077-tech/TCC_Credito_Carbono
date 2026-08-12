<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once(__DIR__ . "/../src/config/banco.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    $verifica = "SELECT * FROM usuarios WHERE email = '$email'";

    $resultado = mysqli_query($conexao, $verifica);

    if (mysqli_num_rows($resultado) > 0) {

        echo "Este e-mail já está cadastrado.";

    } else {

        $sql = "INSERT INTO usuarios (nome, email, senha)
                VALUES ('$nome', '$email', '$senha')";

        if (mysqli_query($conexao, $sql)) {

            header("Location: login.php?cadastro=sucesso");
            exit();

        } else {

            echo "Erro: " . mysqli_error($conexao);

        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/login.css">

<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
</head>

<body>

<div class="container">
<h1>Cadastro</h1>

<form method="POST">

    Nome:<br>
    <input type="text" name="nome" required>
    <br><br>

    Email:<br>
    <input type="email" name="email" required>
    <br><br>

    Senha:<br>
    <input type="password" name="senha" required>
    <br><br>

    <button type="submit">
        Cadastrar
    </button>

</form>
</div>
<script src="assets/js/script.js"></script>

</body>
</html>