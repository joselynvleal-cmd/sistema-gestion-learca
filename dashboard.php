<?php
session_start();
require_once 'conexion.php';

// Control de Sesión: Redirigir al login si no hay sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

// Sincronizar el avatar en la sesión
if (isset($_SESSION['id_usuario']) && !isset($_SESSION['avatar'])) {
    $stmt_av = $pdo->prepare("SELECT avatar FROM usuarios WHERE id_usuario = ?");
    $stmt_av->execute([$_SESSION['id_usuario']]);
    $res_av = $stmt_av->fetch();
    $_SESSION['avatar'] = $res_av['avatar'] ?? '';
}

$mensaje = "";
$modo_edicion = false;
$registro_actual = [
    'id_usuario' => '',
    'cedula' => '',
    'username' => '',
    'email' => '',
    'cargo' => '',
    'id_rol' => '',
    'fecha_ingreso' => date('Y-m-d')
];

// Array de cargos oficiales de la estructura organizativa de la empresa
$cargos_empresa = [
    'Gerente General',
    'Gerente Operaciones',
    'Gerente Administrativo',
    'Asistente Administrativo',
    'Supervisor',
    'Mecánico',
    'Ayudante Mecánico',
    'Electricista',
    'Ayudante Electricista',
    'Obrero'
];

// Registrar nuevo empleado / usuario (Create)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador') {
        $cedula = trim($_POST['cedula']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
        $cargo = trim($_POST['cargo']);
        $id_rol = intval($_POST['id_rol']); 
        $fecha_ingreso = !empty($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : date('Y-m-d');

        if (!empty($cedula) && !empty($username) && !empty($email) && !empty($cargo)) {
            try {
                $stmt_check_cedula = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE cedula = ?");
                $stmt_check_cedula->execute([$cedula]);
                if ($stmt_check_cedula->fetchColumn() > 0) {
                    $mensaje = "Error: La cédula '$cedula' ya se encuentra registrada.";
                } else {
                    $stmt_check_user = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
                    $stmt_check_user->execute([$username]);
                    if ($stmt_check_user->fetchColumn() > 0) {
                        $mensaje = "Error: El nombre de usuario '$username' ya está en uso.";
                    } else {
                        $stmt_check_email = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
                        $stmt_check_email->execute([$email]);
                        if ($stmt_check_email->fetchColumn() > 0) {
                            $mensaje = "Error: El correo electrónico '$email' ya se encuentra registrado.";
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO usuarios (cedula, username, email, password, cargo, id_rol, fecha_ingreso) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$cedula, $username, $email, $password, $cargo, $id_rol, $fecha_ingreso]);
                            header("Location: dashboard.php?seccion=empleados");
                            exit();
                        }
                    }
                }
            } catch (PDOException $e) {
                $mensaje = "Error SQL: " . $e->getMessage();
            }
        } else {
            $mensaje = "Por favor completa todos los campos obligatorios.";
        }
    } else {
        $mensaje = "Acceso denegado: Solo el Administrador puede registrar nuevos empleados.";
    }
}

// Procesar Edición de Empleado (Update - POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'actualizar') {
    if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador') {
        $id_usuario_edit = $_POST['id_usuario'];
        $cedula = trim($_POST['cedula']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $cargo = trim($_POST['cargo']);
        $id_rol = intval($_POST['id_rol']);
        $fecha_ingreso = !empty($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : date('Y-m-d');

        if (!empty($cedula) && !empty($username) && !empty($email) && !empty($cargo)) {
            try {
                if (!empty($_POST['password'])) {
                    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET cedula = ?, username = ?, email = ?, password = ?, cargo = ?, id_rol = ?, fecha_ingreso = ? WHERE id_usuario = ?");
                    $stmt->execute([$cedula, $username, $email, $password, $cargo, $id_rol, $fecha_ingreso, $id_usuario_edit]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET cedula = ?, username = ?, email = ?, cargo = ?, id_rol = ?, fecha_ingreso = ? WHERE id_usuario = ?");
                    $stmt->execute([$cedula, $username, $email, $cargo, $id_rol, $fecha_ingreso, $id_usuario_edit]);
                }
                header("Location: dashboard.php?seccion=empleados");
                exit();
            } catch (PDOException $e) {
                $mensaje = "Error al actualizar los datos del empleado.";
            }
        }
    } else {
        $mensaje = "Acceso denegado: Solo el Administrador puede modificar empleados.";
    }
}

// Cargar datos del empleado para Editar (GET)
if (isset($_GET['editar'])) {
    if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador') {
        $id_editar = $_GET['editar'];
        $stmt_edit = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
        $stmt_edit->execute([$id_editar]);
        $resultado_edit = $stmt_edit->fetch();
        if ($resultado_edit) {
            $modo_edicion = true;
            $registro_actual = $resultado_edit;
        }
    }
}

// Eliminar empleado (Delete)
if (isset($_GET['eliminar'])) {
    if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador') {
        $id_eliminar = $_GET['eliminar'];
        if ($id_eliminar != $_SESSION['id_usuario']) {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$id_eliminar]);
            header("Location: dashboard.php?seccion=empleados");
            exit();
        } else {
            $mensaje = "No puedes eliminar tu propio usuario activo.";
        }
    } else {
        $mensaje = "Acceso denegado: Solo el Administrador puede eliminar registros.";
    }
}

// Obtener listado de empleados registrados (Read)
$stmt = $pdo->query("SELECT u.*, r.nombre_rol 
                     FROM usuarios u 
                     LEFT JOIN roles r ON u.id_rol = r.id_rol 
                     ORDER BY u.id_usuario ASC");
$empleados = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiservicios Learca, C.A.</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-container input {
            width: 100%;
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: #64748B;
            font-size: 1rem;
        }
        .toggle-password:hover {
            color: #0E5AAC;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-box {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            position: relative;
        }
        .table-scroll-container {
            max-height: 440px;
            overflow-y: auto;
            overflow-x: hidden;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
        }
        .table-scroll-container thead th {
            position: sticky;
            top: 0;
            background: #F8FAFC;
            z-index: 10;
        }
    </style>
</head>
<body class="dashboard-body">

    <div class="app-layout">
        
        <!-- Sidebar Izquierdo Corporativo -->
        <aside class="sidebar">
            <div>
                <div class="sidebar-header" style="text-align: center; padding: 0.5rem 0.5rem 0.8rem 0.5rem;">
                    <img src="assets/img/logo_learca.png" alt="Logo Learca" style="width: 230px; height: 80px; object-fit: contain; margin-bottom: -0.5rem; filter: drop-shadow(0 2px 5px rgba(0,0,0,0.3));">
                </div>

                <nav class="sidebar-menu">
                    <!-- Inicio -->
                    <a href="dashboard.php?seccion=inicio" class="menu-link-custom <?php echo (!isset($_GET['seccion']) || $_GET['seccion'] === 'inicio') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-house" style="width: 20px; text-align: center;"></i>
                        <span>Inicio</span>
                    </a>

                    <!-- Mi Perfil -->
                    <a href="dashboard.php?seccion=perfil" class="menu-link-custom <?php echo (isset($_GET['seccion']) && $_GET['seccion'] === 'perfil') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-id-card" style="width: 20px; text-align: center;"></i>
                        <span>Mi Perfil</span>
                    </a>

                    <!-- Personal -->
                    <a href="dashboard.php?seccion=empleados" class="menu-link-custom <?php echo (isset($_GET['seccion']) && $_GET['seccion'] === 'empleados') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-users" style="width: 20px; text-align: center;"></i>
                        <span>Personal</span>
                    </a>
                    
                    <!-- Proveedores y Compras -->
                    <a href="dashboard.php?seccion=proveedores" class="menu-link-custom <?php echo (isset($_GET['seccion']) && $_GET['seccion'] === 'proveedores') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-cart-shopping" style="width: 20px; text-align: center;"></i>
                        <span>Proveedores y Compras</span>
                    </a>

                    <!-- Reportes -->
                    <a href="dashboard.php?seccion=reportes" class="menu-link-custom <?php echo (isset($_GET['seccion']) && $_GET['seccion'] === 'reportes') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-chart-line" style="width: 20px; text-align: center;"></i>
                        <span>Reportes</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Área de Contenido Principal -->
        <main class="main-wrapper">
            
            <header class="top-navbar">
                <div class="nav-title">
                    <h2><?php 
                        if (isset($_GET['seccion']) && $_GET['seccion'] === 'empleados') {
                            echo 'Nómina y Empleados';
                        } elseif (isset($_GET['seccion']) && $_GET['seccion'] === 'proveedores') {
                            echo 'Directorio de Proveedores';
                        } elseif (isset($_GET['seccion']) && $_GET['seccion'] === 'reportes') {
                            echo 'Panel de Estadísticas y Reportes';
                        } elseif (isset($_GET['seccion']) && $_GET['seccion'] === 'gestion') {
                            echo 'Entidad de Gestión y Control';
                        } elseif (isset($_GET['seccion']) && $_GET['seccion'] === 'perfil') {
                            echo 'Mi Perfil Corporativo';
                        } else {
                            echo 'Portal Institucional';
                        }
                    ?></h2>
                    <p><?php 
                        if (isset($_GET['seccion']) && $_GET['seccion'] === 'empleados') {
                            echo 'Gestiona los registros, cargos y accesos de tus empleados.';
                        } elseif (isset($_GET['seccion']) && $_GET['seccion'] === 'proveedores') {
                            echo 'Controla y hazle seguimiento a tus proveedores activos para la contratación y servicios.';
                        } elseif (isset($_GET['seccion']) && $_GET['seccion'] === 'reportes') {
                            echo 'Visualiza los indicadores generales y descarga los reportes oficiales en PDF.';
                        } elseif (isset($_GET['seccion']) && $_GET['seccion'] === 'gestion') {
                            echo 'Registro de bitácora y trazabilidad de operaciones del sistema.';
                        } elseif (isset($_GET['seccion']) && $_GET['seccion'] === 'perfil') {
                            echo 'Consulta los datos de tu cuenta, cargo asignado y tiempo en la empresa.';
                        } else {
                            echo 'Información general e identidad corporativa.';
                        }
                    ?></p>
                </div>
                
                <div class="user-profile">
                    <?php if (!empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="Avatar" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 1.5px solid #0284C7;">
                    <?php else: ?>
                        <i class="fa-solid fa-circle-user avatar-icon" style="font-size: 2.2rem; color: #0284C7;"></i>
                    <?php endif; ?>

                    <div class="user-info">
                        <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?></strong>
                        <span><?php echo htmlspecialchars($_SESSION['nombre_rol'] ?? 'Operativo'); ?></span>
                    </div>
                    <a href="logout.php" class="btn-logout" title="Cerrar Sesión">
                        <i class="fa-solid fa-power-off"></i>
                    </a>
                </div>
            </header>

            <div class="content-body" style="display: flex; flex-direction: column;">
                
                <?php if (!empty($mensaje)): ?>
                    <div class="alert-success-custom">
                        <i class="fa-solid fa-circle-check"></i> <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <?php 
                $seccion = $_GET['seccion'] ?? 'inicio';

                if ($seccion === 'inicio') {
                    if (file_exists('inicio.php')) {
                        include 'inicio.php';
                    } else {
                        echo '<div class="alert-success-custom">Error: No se encontró el archivo inicio.php</div>';
                    }
                } elseif ($seccion === 'perfil') {
                    if (file_exists('perfil.php')) {
                        include 'perfil.php';
                    } else {
                        echo '<div class="alert-success-custom">Error: No se encontró el archivo perfil.php</div>';
                    }
                } elseif ($seccion === 'empleados') {
                    $vista_registro = (isset($_GET['accion']) && $_GET['accion'] === 'nuevo') || isset($_GET['editar']);
                ?>
                    <?php if ($vista_registro && isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                        
                        <section class="panel-card" style="max-width: 800px; margin: 0 auto;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.8rem;">
                                <div class="card-header-title" style="margin-bottom: 0;">
                                    <i class="fa-solid <?php echo $modo_edicion ? 'fa-pen-to-square' : 'fa-user-plus'; ?>"></i>
                                    <h3><?php echo $modo_edicion ? 'Editar Empleado #' . $registro_actual['id_usuario'] : 'Registrar Nuevo Empleado'; ?></h3>
                                </div>
                                <a href="dashboard.php?seccion=empleados" style="padding: 6px 12px; background: #64748B; color: white; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-arrow-left"></i> Volver a la Lista
                                </a>
                            </div>

                            <form action="dashboard.php?seccion=empleados" method="POST" class="form-styled">
                                <input type="hidden" name="accion" value="<?php echo $modo_edicion ? 'actualizar' : 'crear'; ?>">
                                <?php if ($modo_edicion): ?>
                                    <input type="hidden" name="id_usuario" value="<?php echo $registro_actual['id_usuario']; ?>">
                                <?php endif; ?>
                                
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                    <div class="form-group-custom">
                                        <label for="cedula">Cédula *</label>
                                        <input type="text" id="cedula" name="cedula" value="<?php echo htmlspecialchars($registro_actual['cedula'] ?? ''); ?>" placeholder="Ej. V-12345678" required>
                                    </div>

                                    <div class="form-group-custom">
                                        <label for="username">Nombre de Usuario *</label>
                                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($registro_actual['username']); ?>" placeholder="Ej. jleal" required>
                                    </div>

                                    <div class="form-group-custom">
                                        <label for="email">Correo Electrónico *</label>
                                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($registro_actual['email']); ?>" placeholder="empleado@learca.com" required>
                                    </div>

                                    <div class="form-group-custom">
                                        <label for="password">Contraseña <?php echo $modo_edicion ? '(Dejar en blanco para mantener)' : '*'; ?></label>
                                        <div class="password-container">
                                            <input type="password" id="password" name="password" placeholder="********" <?php echo $modo_edicion ? '' : 'required'; ?>>
                                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                                <i class="fa-solid fa-eye" id="eye-icon"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group-custom">
                                        <label for="cargo">Cargo en la Empresa *</label>
                                        <select id="cargo" name="cargo" class="form-control" required style="width: 100%; padding: 0.65rem 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem;">
                                            <option value="">Seleccione el cargo...</option>
                                            <?php foreach ($cargos_empresa as $c): ?>
                                                <option value="<?php echo $c; ?>" <?php echo ($registro_actual['cargo'] === $c) ? 'selected' : ''; ?>>
                                                    <?php echo $c; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group-custom">
                                        <label for="id_rol">Nivel de Acceso (Rol) *</label>
                                        <select id="id_rol" name="id_rol" class="form-control" required style="width: 100%; padding: 0.65rem 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem;">
                                            <option value="1" <?php echo ($registro_actual['id_rol'] == 1) ? 'selected' : ''; ?>>Administrador</option>
                                            <option value="2" <?php echo ($registro_actual['id_rol'] == 2) ? 'selected' : ''; ?>>Empleado / Operativo</option>
                                        </select>
                                    </div>

                                    <div class="form-group-custom" style="grid-column: span 2;">
                                        <label for="fecha_ingreso">Fecha de Ingreso a la Empresa *</label>
                                        <input type="date" id="fecha_ingreso" name="fecha_ingreso" value="<?php echo htmlspecialchars(!empty($registro_actual['fecha_ingreso']) ? date('Y-m-d', strtotime($registro_actual['fecha_ingreso'])) : date('Y-m-d')); ?>" class="form-control" required style="width: 100%; padding: 0.65rem 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem;">
                                    </div>
                                </div>

                                <div class="form-actions-flex" style="display: flex; gap: 10px; margin-top: 1.5rem;">
                                    <button type="submit" class="btn-submit-action" style="flex: 1;">
                                        <i class="fa-solid <?php echo $modo_edicion ? 'fa-floppy-disk' : 'fa-user-plus'; ?>"></i> 
                                        <?php echo $modo_edicion ? 'Actualizar Empleado' : 'Guardar Empleado'; ?>
                                    </button>
                                    <a href="dashboard.php?seccion=empleados" style="padding: 10px 20px; background: #94A3B8; color: white; border-radius: 6px; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                                        Cancelar
                                    </a>
                                </div>
                            </form>
                        </section>

                    <?php else: ?>

                        <section class="panel-card" style="width: 100%; <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'max-width: 950px; margin: 0 auto;' : 'flex: 1;'; ?> display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 1.2rem;">
                                    <div style="position: relative; flex: 1; min-width: 260px;">
                                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #0284C7;"></i>
                                        <input type="text" id="inputBuscar" onkeyup="filtrarTabla()" placeholder="Buscar empleado por nombre, cédula o cargo..." style="width: 100%; padding: 0.65rem 1rem 0.65rem 2.5rem; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.88rem; background: #F8FAFC; outline: none;">
                                    </div>

                                    <div style="display: flex; align-items: center; gap: 6px; background: #F8FAFC; border: 1px solid #CBD5E1; padding: 0.4rem 0.8rem; border-radius: 8px;">
                                        <i class="fa-solid fa-briefcase" style="color: #0284C7; font-size: 0.85rem;"></i>
                                        <select id="filtroCargo" onchange="filtrarTabla()" style="border: none; background: transparent; font-size: 0.85rem; color: #1E293B; outline: none; cursor: pointer;">
                                            <option value="">Cargo: Todos</option>
                                            <?php foreach ($cargos_empresa as $c): ?>
                                                <option value="<?php echo $c; ?>"><?php echo $c; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                        <div style="display: flex; align-items: center; gap: 6px; background: #F8FAFC; border: 1px solid #CBD5E1; padding: 0.4rem 0.8rem; border-radius: 8px;">
                                            <i class="fa-solid fa-user-shield" style="color: #0284C7; font-size: 0.85rem;"></i>
                                            <select id="filtroRol" onchange="filtrarTabla()" style="border: none; background: transparent; font-size: 0.85rem; color: #1E293B; outline: none; cursor: pointer;">
                                                <option value="">Rol: Todos</option>
                                                <option value="Administrador">Administrador</option>
                                                <option value="Empleado">Empleado</option>
                                            </select>
                                        </div>

                                        <a href="dashboard.php?seccion=empleados&accion=nuevo" style="width: auto; padding: 0.65rem 1.4rem; border-radius: 8px; background: #0284C7; color: white; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);">
                                            <i class="fa-solid fa-plus"></i> Nuevo Empleado
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="table-scroll-container">
                                    <table class="learca-table" id="tablaEmpleados">
                                        <thead>
                                            <tr>
                                                <th style="<?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'width: 10%; text-align: center;' : ''; ?>">N°</th>
                                                <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                                    <th>Cédula</th>
                                                <?php endif; ?>
                                                <th style="<?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'width: 40%;' : ''; ?>">Usuario</th>
                                                <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                                    <th>Correo</th>
                                                <?php endif; ?>
                                                <th style="<?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'width: 35%;' : ''; ?>">Cargo</th>
                                                <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                                    <th>Fecha de Ingreso</th>
                                                    <th>Rol del Sistema</th>
                                                <?php endif; ?>
                                                <th style="text-align: center; <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'width: 15%;' : ''; ?>">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($empleados) > 0): ?>
                                                <?php $contador = 1; foreach ($empleados as $emp): ?>
                                                    <tr>
                                                        <td style="<?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'text-align: center;' : ''; ?>"><strong><?php echo $contador++; ?></strong></td>
                                                        
                                                        <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                                            <td><?php echo htmlspecialchars($emp['cedula'] ?? 'N/D'); ?></td>
                                                        <?php endif; ?>
                                                        
                                                        <td><?php echo htmlspecialchars($emp['username']); ?></td>
                                                        
                                                        <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                                            <td><?php echo htmlspecialchars($emp['email']); ?></td>
                                                        <?php endif; ?>
                                                        
                                                        <td><span class="badge-rif" style="background: #E0F2FE; color: #0369A1;"><?php echo htmlspecialchars($emp['cargo'] ?? 'No asignado'); ?></span></td>
                                                        
                                                        <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                                            <td><i class="fa-regular fa-calendar" style="color: var(--text-muted);"></i> <?php echo date('d/m/Y', strtotime($emp['fecha_ingreso'] ?? 'now')); ?></td>
                                                            <td><span class="badge-user"><?php echo htmlspecialchars($emp['nombre_rol'] ?? 'Operativo'); ?></span></td>
                                                        <?php endif; ?>
                                                        
                                                        <td style="text-align: center;">
                                                            <button type="button" onclick="verEmpleado('<?php echo htmlspecialchars($emp['cedula'] ?? 'N/D'); ?>', '<?php echo htmlspecialchars($emp['username']); ?>', '<?php echo htmlspecialchars($emp['email']); ?>', '<?php echo htmlspecialchars($emp['cargo'] ?? 'No asignado'); ?>', '<?php echo htmlspecialchars($emp['nombre_rol'] ?? 'Operativo'); ?>', '<?php echo date('d/m/Y', strtotime($emp['fecha_ingreso'] ?? 'now')); ?>')" title="Visualizar" style="background: none; border: none; color: #0284c7; font-size: 1rem; cursor: pointer; margin-right: 6px;">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>

                                                            <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                                                <a href="dashboard.php?seccion=empleados&editar=<?php echo $emp['id_usuario']; ?>" title="Editar" style="color: #ffc107; margin-right: 6px;">
                                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                                </a>
                                                                <?php if ($emp['id_usuario'] != $_SESSION['id_usuario']): ?>
                                                                    <a href="dashboard.php?seccion=empleados&eliminar=<?php echo $emp['id_usuario']; ?>" onclick="return confirm('¿Estás segura de eliminar este empleado del sistema?');" title="Eliminar" style="color: #dc3545;">
                                                                        <i class="fa-solid fa-trash-can"></i>
                                                                    </a>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="8" class="empty-table">No hay empleados registrados actualmente.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Botón de Exportar Nómina PDF (Bloqueado para Empleados / Activo para Administradores) -->
                            <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                                <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                    <a href="reporte_pdf.php" target="_blank" style="background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 3px 10px rgba(220, 53, 69, 0.2);">
                                        <i class="fa-solid fa-file-pdf"></i> Exportar Nómina PDF
                                    </a>
                                <?php else: ?>
                                    <span title="Acceso restringido solo para administradores" style="background: #94A3B8; color: #F1F5F9; padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: not-allowed; opacity: 0.7;">
                                        <i class="fa-solid fa-lock"></i> Exportar Nómina PDF
                                    </span>
                                <?php endif; ?>
                            </div>
                        </section>

                    <?php endif; ?>

                <?php } elseif ($seccion === 'proveedores') {
                    if (file_exists('proveedores.php')) {
                        include 'proveedores.php';
                    } else {
                        echo '<div class="alert-success-custom">Error: No se encontró el archivo proveedores.php</div>';
                    }
                } elseif ($seccion === 'reportes') {
                    if (file_exists('reportes.php')) {
                        include 'reportes.php';
                    } else {
                        echo '<div class="alert-success-custom">Error: No se encontró el archivo reportes.php</div>';
                    }
                } elseif ($seccion === 'gestion') {
                    if (file_exists('gestion.php')) {
                        include 'gestion.php';
                    } else {
                        echo '<div class="alert-success-custom">Error: No se encontró el archivo gestion.php</div>';
                    }
                } ?>

            </div>
        </main>
    </div>

    <!-- Modal para Visualizar Detalles -->
    <div id="modalVisualizar" class="modal-overlay">
        <div class="modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.5rem;">
                <h3 style="color: var(--primary-blue); font-size: 1.1rem;"><i class="fa-solid fa-user-shield"></i> Detalles del Empleado</h3>
                <button onclick="cerrarModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748B;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div style="font-size: 0.9rem; color: var(--text-main); display: flex; flex-direction: column; gap: 8px;">
                <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                    <p><strong>Cédula:</strong> <span id="modal-cedula"></span></p>
                    <p><strong>Usuario:</strong> <span id="modal-username"></span></p>
                    <p><strong>Correo:</strong> <span id="modal-email"></span></p>
                    <p><strong>Cargo:</strong> <span id="modal-cargo"></span></p>
                    <p><strong>Fecha de Ingreso:</strong> <span id="modal-fecha"></span></p>
                    <p><strong>Rol del Sistema:</strong> <span id="modal-rol"></span></p>
                <?php else: ?>
                    <p><strong>Usuario:</strong> <span id="modal-username"></span></p>
                    <p><strong>Cargo:</strong> <span id="modal-cargo"></span></p>
                <?php endif; ?>
            </div>
            <div style="margin-top: 1.5rem; text-align: right;">
                <button onclick="cerrarModal()" class="btn-submit-action" style="padding: 6px 15px; font-size: 0.85rem;">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        function verEmpleado(cedula, username, email, cargo, rol, fecha) {
            const esAdmin = <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador') ? 'true' : 'false'; ?>;
            
            if (esAdmin) {
                document.getElementById('modal-cedula').innerText = cedula;
                document.getElementById('modal-email').innerText = email;
                document.getElementById('modal-fecha').innerText = fecha;
                document.getElementById('modal-rol').innerText = rol;
            }
            
            document.getElementById('modal-username').innerText = username;
            document.getElementById('modal-cargo').innerText = cargo;
            document.getElementById('modalVisualizar').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalVisualizar').style.display = 'none';
        }

        function filtrarTabla() {
            const texto = document.getElementById('inputBuscar').value.toLowerCase();
            const cargo = document.getElementById('filtroCargo').value.toLowerCase();
            const filtroRolEl = document.getElementById('filtroRol');
            const rol = filtroRolEl ? filtroRolEl.value.toLowerCase() : '';
            const filas = document.querySelectorAll('#tablaEmpleados tbody tr');

            filas.forEach(fila => {
                const contenidoFila = fila.innerText.toLowerCase();
                const coincideTexto = contenidoFila.includes(texto);
                const coincideCargo = cargo === '' || contenidoFila.includes(cargo);
                const coincideRol = rol === '' || contenidoFila.includes(rol);

                if (coincideTexto && coincideCargo && coincideRol) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>