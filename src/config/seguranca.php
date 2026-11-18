<?php
function tokenCsrf(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function validarCsrf(): bool
{
    return isset($_POST["csrf_token"], $_SESSION["csrf_token"])
        && hash_equals($_SESSION["csrf_token"], (string) $_POST["csrf_token"]);
}

function simulacaoPertenceAoUsuario(mysqli $conexao, int $idSimulacao, int $idUsuario): bool
{
    $stmt = mysqli_prepare($conexao, "SELECT 1 FROM simulacoes WHERE id_simulacao = ? AND id_usuario = ?");
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, "ii", $idSimulacao, $idUsuario);
    if (!mysqli_stmt_execute($stmt)) return false;
    $resultado = mysqli_stmt_get_result($stmt);
    return $resultado && mysqli_num_rows($resultado) === 1;
}
