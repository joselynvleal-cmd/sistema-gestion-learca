<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Multiservicios Learca, C.A.</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">

    <div class="login-split-container">
        
        <!-- Panel Izquierdo -->
        <div class="login-brand-panel">
            
            <!-- Banner de Learca arriba -->
            <div class="brand-banner-container">
                <img src="assets/img/logo_learca.png" alt="Multiservicios Learca, C.A. RIF: J-29754653-7">
            </div>

            <!-- Eslogan institucional con su barra vertical celeste abajo -->
            <div class="brand-slogan-bottom">
                <div class="blue-line"></div>
                <p>Soluciones en  <br> mantenimiento y servicios <br> industriales</p>
            </div>

        </div>

        <!-- Panel Derecho: Formulario -->
        <div class="login-card-side">
            <div class="login-form-box">
                <div class="login-header">
                    <h2>Iniciar Sesión</h2>
                    <p>Accede a tu cuenta para continuar</p>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div style="background-color: #FEE2E2; border: 1px solid #EF4444; color: #B91C1C; padding: 0.8rem; border-radius: 8px; font-size: 0.9rem; margin-bottom: 1.5rem;">
                        <i class="fa-solid fa-circle-exclamation"></i> Usuario o contraseña incorrectos.
                    </div>
                <?php endif; ?>

                <form action="login_process.php" method="POST">
                    <div class="input-group">
                        <div class="input-with-icon">
                            <i class="fa-regular fa-user input-icon-left"></i>
                            <input type="text" id="username" name="username" placeholder="Usuario" required autocomplete="off">
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-with-icon">
                            <i class="fa-solid fa-lock input-icon-left"></i>
                            <input type="password" id="password" name="password" placeholder="Contraseña" required>
                            <i class="fa-regular fa-eye-slash input-icon-right" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="forgot-link">
                        <a href="recuperar.php">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-login-blue">
                        Ingresar <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="divider-container">
                    <span class="divider-text">Multiservicios Learca, C.A.</span>
                </div>

                <div class="sub-footer-text">
                    Sistema de Gestión Empresarial
                </div>
            </div>
        </div>

    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        if(togglePassword) {
            togglePassword.addEventListener('click', function () {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>
</html>