<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();
require_once(__DIR__ . "/../src/config/banco.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $sql = "SELECT * FROM usuarios WHERE email='$email'";

    $resultado = mysqli_query($conexao, $sql);

    if (mysqli_num_rows($resultado) > 0) {

        $usuario = mysqli_fetch_assoc($resultado);

        if (password_verify($senha, $usuario["senha"])) {

            $_SESSION["usuario_id"] = $usuario["id_usuario"];
            $_SESSION["usuario_nome"] = $usuario["nome"];

            header("Location: dashboard.php");
            exit();

        } else {

            $erro = "Senha incorreta!";

        }

    } else {

        $erro = "Usuário não encontrado!";

    }

}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">

</head>

<body>

<div class="container">
<h1>Login</h1>

<?php

if (isset($_GET["cadastro"]) && $_GET["cadastro"] == "sucesso") {
    echo "<p style='color: green;'>Cadastro realizado com sucesso! Faça seu login.</p>";
}

if (isset($_GET["logout"]) && $_GET["logout"] == "sucesso") {
    echo "<p style='color: blue;'>Logout realizado com sucesso!</p>";
}

if (isset($erro)) {
    echo "<p style='color: red;'>$erro</p>";
}

?>

<form method="POST">

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Senha:</label><br>
    <input type="password" name="senha" required><br><br>

    <button type="submit">Entrar</button>

</form>

</div>
</body>
</html>