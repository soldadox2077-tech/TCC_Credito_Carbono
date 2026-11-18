<?php

session_start();
$erro = null;
require_once(__DIR__ . "/../src/config/banco.php");
require_once(__DIR__ . "/../src/config/seguranca.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!validarCsrf()) { http_response_code(403); $erro = "Formulário expirado. Recarregue a página."; }
    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senhaBruta = $_POST["senha"] ?? "";

    if (!$erro && ($nome === "" || mb_strlen($nome) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150 || strlen($senhaBruta) < 8)) {
        $erro = "Informe nome e e-mail válidos e uma senha com ao menos 8 caracteres.";
    }

    if (!$erro) {
      $senha = password_hash($senhaBruta, PASSWORD_DEFAULT);
      $stmt = mysqli_prepare($conexao, "SELECT id_usuario FROM usuarios WHERE email = ?");
      mysqli_stmt_bind_param($stmt, "s", $email);
      mysqli_stmt_execute($stmt);
      $resultado = mysqli_stmt_get_result($stmt);
      if (mysqli_num_rows($resultado) > 0) {
        $erro = "Este e-mail já está cadastrado.";
      } else {

        $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
        $insert = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($insert, "sss", $nome, $email, $senha);
        if (mysqli_stmt_execute($insert)) {

            header("Location: login.php?cadastro=sucesso");
            exit();

        } else {

            error_log("Falha no cadastro: " . mysqli_error($conexao));
            $erro = "Não foi possível concluir o cadastro.";

        }
      }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <title>Cadastro</title>
</head>

<body>

<div class="container">
<h1>Cadastro</h1>
<?php if (!empty($erro)): ?><p><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">

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
