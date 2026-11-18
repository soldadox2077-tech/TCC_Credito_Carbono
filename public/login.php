<?php

session_start();
$erro = null;
require_once(__DIR__ . "/../src/config/banco.php");
require_once(__DIR__ . "/../src/config/seguranca.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if (!validarCsrf()) { http_response_code(403); $erro = "Formulário expirado. Recarregue a página."; }
    $stmt = mysqli_prepare($conexao, "SELECT * FROM usuarios WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (!$erro && $resultado && mysqli_num_rows($resultado) > 0) {

        $usuario = mysqli_fetch_assoc($resultado);

        if (password_verify($senha, $usuario["senha"])) {

            session_regenerate_id(true);
            $_SESSION["usuario_id"] = $usuario["id_usuario"];
            $_SESSION["usuario_nome"] = $usuario["nome"];

            header("Location: dashboard.php");
            exit();

        } else {

            $erro = "E-mail ou senha inválidos.";

        }

    } elseif (!$erro) {

        $erro = "E-mail ou senha inválidos.";

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
    echo "<p style='color: red;'>" . htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') . "</p>";
}

?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Senha:</label><br>
    <input type="password" name="senha" required><br><br>

    <button type="submit">Entrar</button>

</form>

</div>
</body>
</html>
