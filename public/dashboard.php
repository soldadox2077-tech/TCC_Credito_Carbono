<?php

session_start();


if (!isset($_SESSION["usuario_id"])) {

    header("Location: login.php");
    exit();

}

?>


<!DOCTYPE html>
<html>

<head>

<title>Dashboard</title>

</head>


<body>


<h1>
Simulador de Crédito de Carbono
</h1>


<h2>
Olá, <?php echo $_SESSION["usuario_nome"]; ?>!
</h2>


<p>
Bem-vindo ao sistema.
</p>


<hr>


<h3>
O que deseja fazer?
</h3>


<button>
Nova Simulação
</button>


<button>
Histórico
</button>


<br><br>


<a href="logout.php">
Sair
</a>


</body>

</html>