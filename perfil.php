<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php';

$mensaje_perfil = "";
$id_usuario_actual = $_SESSION['id_usuario'];

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_perfil']) && $_POST['accion_perfil'] === 'actualizar_mi_perfil') {
    $nuevo_username = trim($_POST['username']);
    $nuevo_email = trim($_POST['email']);
    $nueva_password = trim($_POST['password']);
    
    // Manejo de la foto de perfil 
    $foto_sql = "";
    $params = [$nuevo_username, $nuevo_email];

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto_perfil']['tmp_name'];
        $file_name = time() . "_" . basename($_FILES['foto_perfil']['name']);
        $upload_dir = 'assets/img/avatars/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $ruta_destino = $upload_dir . $file_name;
        if (move_uploaded_file($file_tmp, $ruta_destino)) {
            $foto_sql = ", avatar = ?";
            $params[] = $ruta_destino;
            $_SESSION['avatar'] = $ruta_destino; // Actualizar sesión de inmediato
        }
    }

    if (!empty($nueva_password)) {
        $password_hash = password_hash($nueva_password, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET username = ?, email = ?, password = ?" . $foto_sql . " WHERE id_usuario = ?";
        $params[] = $password_hash;
        $params[] = $id_usuario_actual;
    } else {
        $sql = "UPDATE usuarios SET username = ?, email = ?" . $foto_sql . " WHERE id_usuario = ?";
        $params[] = $id_usuario_actual;
    }

    try {
        $stmt_update = $pdo->prepare($sql);
        $stmt_update->execute($params);
        $_SESSION['username'] = $nuevo_username; 
        
        echo "<script>window.location.href = 'dashboard.php?seccion=perfil';</script>";
        exit();
    } catch (PDOException $e) {
        $mensaje_perfil = "Error al actualizar los datos: " . $e->getMessage();
    }
}

// Consultar los datos actualizados del usuario logueado
$stmt_perfil = $pdo->prepare("SELECT u.*, r.nombre_rol FROM usuarios u LEFT JOIN roles r ON u.id_rol = r.id_rol WHERE u.id_usuario = ?");
$stmt_perfil->execute([$id_usuario_actual]);
$usuario_activo = $stmt_perfil->fetch();

// Sincronizar avatar en sesión por seguridad si existe en la BD
if (!empty($usuario_activo['avatar'])) {
    $_SESSION['avatar'] = $usuario_activo['avatar'];
}

// Calcular tiempo en la empresa
$tiempo_empresa = "No especificada";
if ($usuario_activo && !empty($usuario_activo['fecha_ingreso'])) {
    $ingreso = new DateTime($usuario_activo['fecha_ingreso']);
    $hoy = new DateTime();
    $diff = $ingreso->diff($hoy);
    $tiempo_empresa = $diff->y . " años, " . $diff->m . " meses y " . $diff->d . " días";
}
?>

<div class="panel-card" style="max-width: 800px; margin: 0 auto; width: 100%; background: white; padding: 2.2rem; border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
    
    <?php if (!empty($mensaje_perfil)): ?>
        <div class="alert-success-custom" style="margin-bottom: 1.5rem; background: #ECFDF5; color: #065F46; padding: 0.8rem 1rem; border-radius: 8px; border: 1px solid #A7F3D0; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-check"></i> <?php echo $mensaje_perfil; ?>
        </div>
    <?php endif; ?>

    <!-- Cabecera del Perfil -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 1.2rem; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1.2rem;">
            <div style="width: 70px; height: 70px; background: #E0F2FE; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0284C7; font-size: 2rem; overflow: hidden; border: 2px solid #0284C7; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.2);">
                <?php if (!empty($usuario_activo['avatar']) && file_exists($usuario_activo['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($usuario_activo['avatar']); ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <i class="fa-solid fa-user"></i>
                <?php endif; ?>
            </div>
            <div>
                <h3 style="color: #0F172A; font-size: 1.3rem; margin: 0 0 4px 0; font-weight: 700;"><?php echo htmlspecialchars($usuario_activo['username']); ?></h3>
                <span style="background: #E0F2FE; color: #0369A1; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                    <?php echo htmlspecialchars($usuario_activo['nombre_rol'] ?? 'Operativo'); ?>
                </span>
            </div>
        </div>

        <button type="button" id="btnEditarPerfilToggle" onclick="toggleEditarPerfil()" style="background: #0284C7; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 3px 10px rgba(2, 132, 199, 0.2);">
            <i class="fa-solid fa-pen-to-square" id="iconoBotonEditar"></i> <span id="textoBotonEditar">Editar Mis Datos</span>
        </button>
    </div>

    <!-- VISTA NORMAL DE DATOS (Se oculta al hacer clic en editar) -->
    <div id="vistaDatosPerfil" style="display: block;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; font-size: 0.95rem;">
            
            <div style="background: #F8FAFC; padding: 1rem 1.2rem; border-radius: 10px; border: 1px solid #F1F5F9;">
                <span style="display: block; color: #64748B; font-size: 0.78rem; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Cédula de Identidad</span>
                <strong style="color: #1E293B; font-size: 1rem;"><?php echo htmlspecialchars($usuario_activo['cedula'] ?? 'N/D'); ?></strong>
            </div>

            <div style="background: #F8FAFC; padding: 1rem 1.2rem; border-radius: 10px; border: 1px solid #F1F5F9;">
                <span style="display: block; color: #64748B; font-size: 0.78rem; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Cargo en la Empresa</span>
                <strong style="color: #1E293B; font-size: 1rem;"><?php echo htmlspecialchars($usuario_activo['cargo'] ?? 'No asignado'); ?></strong>
            </div>

            <div style="background: #F8FAFC; padding: 1rem 1.2rem; border-radius: 10px; border: 1px solid #F1F5F9;">
                <span style="display: block; color: #64748B; font-size: 0.78rem; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Correo Electrónico</span>
                <strong style="color: #1E293B; font-size: 1rem;"><?php echo htmlspecialchars($usuario_activo['email'] ?? 'N/D'); ?></strong>
            </div>

            <div style="background: #F8FAFC; padding: 1rem 1.2rem; border-radius: 10px; border: 1px solid #F1F5F9;">
                <span style="display: block; color: #64748B; font-size: 0.78rem; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Fecha de Ingreso</span>
                <strong style="color: #1E293B; font-size: 1rem;"><i class="fa-regular fa-calendar" style="color: #0284C7; margin-right: 4px;"></i> <?php echo !empty($usuario_activo['fecha_ingreso']) ? date('d/m/Y', strtotime($usuario_activo['fecha_ingreso'])) : 'N/D'; ?></strong>
            </div>

        </div>

        <!-- Tarjeta Destacada de Antigüedad -->
        <div style="margin-top: 1.5rem; background: linear-gradient(135deg, #EFF6FF, #DBEAFE); border: 1px solid #BFDBFE; padding: 1.2rem 1.5rem; border-radius: 12px; display: flex; align-items: center; gap: 1rem;">
            <div style="font-size: 2rem; color: #0284C7;">
                <i class="fa-solid fa-award"></i>
            </div>
            <div>
                <h4 style="margin: 0 0 2px 0; color: #1E3A8A; font-size: 0.95rem; font-weight: 700;">Antigüedad Institucional</h4>
                <p style="margin: 0; color: #1E40AF; font-size: 0.9rem;">Tiempo laborando en Multiservicios Learca, C.A.: <strong><?php echo $tiempo_empresa; ?></strong></p>
            </div>
        </div>
    </div>

    <!-- FORMULARIO DE EDICIÓN DE PERFIL (Oculto por defecto) -->
    <div id="formularioEditarPerfil" style="display: none;">
        <form action="dashboard.php?seccion=perfil" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.2rem;">
            <input type="hidden" name="accion_perfil" value="actualizar_mi_perfil">

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                
                <div class="form-group-custom">
                    <label for="username" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Nombre de Usuario *</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($usuario_activo['username']); ?>" required style="width: 100%; padding: 0.65rem; border: 1px solid #CBD5E1; border-radius: 6px;">
                </div>

                <div class="form-group-custom">
                    <label for="email" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Correo Electrónico *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario_activo['email']); ?>" required style="width: 100%; padding: 0.65rem; border: 1px solid #CBD5E1; border-radius: 6px;">
                </div>

                <div class="form-group-custom">
                    <label for="password" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Nueva Contraseña (Opcional)</label>
                    <input type="password" id="password" name="password" placeholder="Dejar en blanco para mantener la actual" style="width: 100%; padding: 0.65rem; border: 1px solid #CBD5E1; border-radius: 6px;">
                </div>

                <div class="form-group-custom">
                    <label for="foto_perfil" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Actualizar Foto de Perfil</label>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="width: 100%; padding: 0.5rem; border: 1px solid #CBD5E1; border-radius: 6px; background: #F8FAFC;">
                </div>

            </div>

            <!-- Campos bloqueados informativos -->
            <div style="background: #F1F5F9; padding: 1rem; border-radius: 8px; font-size: 0.85rem; color: #64748B; display: flex; gap: 1.5rem; flex-wrap: wrap;">
                <div><i class="fa-solid fa-lock"></i> <strong>Cédula:</strong> <?php echo htmlspecialchars($usuario_activo['cedula']); ?> (No editable)</div>
                <div><i class="fa-solid fa-lock"></i> <strong>Cargo:</strong> <?php echo htmlspecialchars($usuario_activo['cargo']); ?> (No editable)</div>
                <div><i class="fa-solid fa-lock"></i> <strong>Rol:</strong> <?php echo htmlspecialchars($usuario_activo['nombre_rol']); ?> (No editable)</div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 1rem;">
                <button type="submit" style="flex: 1; background: #059669; color: white; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                </button>
                <button type="button" onclick="toggleEditarPerfil()" style="background: #94A3B8; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function toggleEditarPerfil() {
    const vistaDatos = document.getElementById('vistaDatosPerfil');
    const formEditar = document.getElementById('formularioEditarPerfil');
    const textoBoton = document.getElementById('textoBotonEditar');
    const iconoBoton = document.getElementById('iconoBotonEditar');
    const btnEditar = document.getElementById('btnEditarPerfilToggle');

    if (vistaDatos.style.display === 'none') {
        vistaDatos.style.display = 'block';
        formEditar.style.display = 'none';
        textoBoton.innerText = 'Editar Mis Datos';
        iconoBoton.className = 'fa-solid fa-pen-to-square';
        btnEditar.style.background = '#0284C7';
    } else {
        vistaDatos.style.display = 'none';
        formEditar.style.display = 'block';
        textoBoton.innerText = 'Ver Mis Datos';
        iconoBoton.className = 'fa-solid fa-eye';
        btnEditar.style.background = '#64748B';
    }
}
</script>