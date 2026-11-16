<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_id'])) {
    require_once 'backend/config.php';

    $id_usuario = $_SESSION['usuario_id'];

    $pagina_atual = basename($_SERVER['PHP_SELF']);

    $sql = "INSERT INTO navegacao_usuarios (id_usuario, pagina_visitada) VALUES (?, ?)";

    $stmt = $conexao->prepare($sql);

    if ($stmt === false) {
    } else {
        $stmt->bind_param("is", $id_usuario, $pagina_atual);
        $stmt->execute();
        $stmt->close();
    }
}
