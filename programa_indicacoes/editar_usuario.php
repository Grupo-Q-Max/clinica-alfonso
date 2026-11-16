<?php
session_start();
require 'backend/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo_conta'] !== 'admin') {
    $_SESSION['login_error_message'] = "Acesso negado. Faça login como administrador.";
    header("Location: login.php");
    exit;
}


if (!isset($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$usuario_id = $_GET['id'];

$usuarioQuery = "
    SELECT id, nome, sobrenome, email, cpf, tipo_conta, ativo
    FROM usuarios
    WHERE id = ?
";

$stmt = $conexao->prepare($usuarioQuery);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: usuarios.php?erro=1");
    exit;
}

$usuario = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $tipo_conta = $_POST['tipo_conta'];

    $updateQuery = "
    UPDATE usuarios
    SET nome = ?, sobrenome = ?, email = ?, cpf = ?, tipo_conta = ?
    WHERE id = ?
";

    $stmt = $conexao->prepare($updateQuery);
    $stmt->bind_param("sssssi", $nome, $sobrenome, $email, $cpf, $tipo_conta, $usuario_id);

    if ($stmt->execute()) {
        header("Location: usuarios.php?sucesso=1");
        exit;
    } else {
        header("Location: editar_usuario.php?id=$usuario_id&erro=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="editar_usuario.css">
</head>

<body>
    <?php include 'includes/header_admin.php'; ?>

    <?php
    if (isset($_GET['sucesso'])) {
        echo '<div class="alert success">Usuário atualizado com sucesso!</div>';
    } elseif (isset($_GET['erro'])) {
        echo '<div class="alert error">Erro ao atualizar o usuário. Tente novamente.</div>';
    }
    ?>

    <div class="content">
        <div class="section-card">
            <h2>Formulário de Edição</h2>
            <form method="POST" action="editar_usuario.php?id=<?php echo $usuario['id']; ?>">
                <table class="form-table">
                    <tr>
                        <td><label for="nome">Nome:</label></td>
                        <td><input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="sobrenome">Sobrenome:</label></td>
                        <td><input type="text" id="sobrenome" name="sobrenome" value="<?php echo htmlspecialchars($usuario['sobrenome']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="email">Email:</label></td>
                        <td><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="cpf">CPF:</label></td>
                        <td><input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($usuario['cpf']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="tipo_conta">Tipo de Conta:</label></td>
                        <td>
                            <select id="tipo_conta" name="tipo_conta" required>
                                <option value="admin" <?php echo $usuario['tipo_conta'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                <option value="indicador" <?php echo $usuario['tipo_conta'] === 'indicador' ? 'selected' : ''; ?>>Indicador</option>
                            </select>
                        </td>
                    </tr>

                </table>
                <button type="submit" class="edit-btn">Atualizar Usuário</button>
            </form>
        </div>
    </div>
    </main>

    <?php include '../footer.php'; ?>
</body>

</html>