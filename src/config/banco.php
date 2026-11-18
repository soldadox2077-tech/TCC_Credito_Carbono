<?php

$host = getenv("DB_HOST") ?: "localhost";
$usuario = getenv("DB_USER") ?: "root";
$senha = getenv("DB_PASSWORD") ?: "";
$banco = getenv("DB_NAME") ?: "simulador_database";

$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    error_log("Falha ao conectar ao banco: " . mysqli_connect_error());
    http_response_code(500);
    die("Não foi possível conectar ao sistema. Tente novamente mais tarde.");
}

mysqli_set_charset($conexao, "utf8mb4");

?>
