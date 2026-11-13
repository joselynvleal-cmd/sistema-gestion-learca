<?php
session_start();
require_once 'conexion.php';

$error = "";

if (!isset($_SESSION['email_recuperacion'])) {
    header("Location: recuperar.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_ingresado = trim($_POST['codigo']);
    $email = $_SESSION['email_recuperacion'];

    // Validar que el código coincida y no haya expirado
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND codigo_recuperacion = ? AND token_expiracion > NOW()");
    $stmt->execute([$email, $codigo_ingresado]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $_SESSION['codigo_verificado'] = true;
        header("Location: nuevo_password.php");
        exit();
    } else {
        $error = "El código es incorrecto o ya ha expirado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar Código - Learca</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-split-container" style="justify-content: center; width: 100vw;">
        <div class="login-card-side" style="width: 100%;">
            <div class="login-form-box">
                <div class="login-header">
                    <h2>Verificar Código</h2>
                    <p>Ingresa el código de 6 dígitos enviado a tu Gmail</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div style="background: #FEE2E2; color: #991B1B; padding: 0.8rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem;">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="verificar_codigo.php" method="POST">
                    <div class="input-group">
                        <div class="input-with-icon">
                            <i class="fa-solid fa-shield-halved input-icon-left"></i>
                            <input type="text" name="codigo" placeholder="Ej. 482910" maxlength="6" required style="letter-spacing: 4px; font-weight: bold; text-align: center; font-size: 1.1rem;">
                        </div>
                    </div>
                    <button type="submit" class="btn-login-blue">
                        Validar Código <i class="fa-solid fa-check"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>