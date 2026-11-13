<?php
require_once 'conexion.php';

$accion_prov = $_GET['accion_prov'] ?? 'lista';
$id_prov = $_GET['id'] ?? null;
$mensaje_error = "";

$prov_edit = [
    'id_proveedor' => '',
    'rif_cedula' => '',
    'razon_social' => '',
    'telefono' => '',
    'email' => '',
    'direccion' => '',
    'estado' => 'Activo'
];

// PROCESAR ACCIONES POST (Registrar y Editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador') {
        $rif_cedula   = trim($_POST['rif_cedula']);
        $razon_social = trim($_POST['razon_social']);
        $telefono     = trim($_POST['telefono']);
        $email        = trim($_POST['email']);
        $direccion    = trim($_POST['direccion']);
        $estado       = $_POST['estado'] ?? 'Activo';
        $id_prov_post = $_POST['id_proveedor'] ?? '';

        // Mantener los valores ingresados en caso de error
        $prov_edit = [
            'id_proveedor' => $id_prov_post,
            'rif_cedula' => $rif_cedula,
            'razon_social' => $razon_social,
            'telefono' => $telefono,
            'email' => $email,
            'direccion' => $direccion,
            'estado' => $estado
        ];

        try {
            if (!empty($id_prov_post)) {
                // EDITAR PROVEEDOR
                $stmt = $pdo->prepare("UPDATE proveedores SET rif_cedula = ?, razon_social = ?, telefono = ?, email = ?, direccion = ?, estado = ? WHERE id_proveedor = ?");
                $stmt->execute([$rif_cedula, $razon_social, $telefono, $email, $direccion, $estado, $id_prov_post]);
            } else {
                // REGISTRAR NUEVO PROVEEDOR
                $stmt = $pdo->prepare("INSERT INTO proveedores (rif_cedula, razon_social, telefono, email, direccion, estado) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$rif_cedula, $razon_social, $telefono, $email, $direccion, $estado]);
            }
            echo '<script>window.location.href="dashboard.php?seccion=proveedores";</script>';
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $mensaje_error = "Error: El RIF o Cédula '$rif_cedula' ya se encuentra registrado.";
            } else {
                $mensaje_error = "Error SQL: " . $e->getMessage();
            }
            $accion_prov = !empty($id_prov_post) ? 'editar' : 'nuevo';
        }
    } else {
        $mensaje_error = "Acceso denegado: Solo el Administrador puede realizar esta acción.";
    }
}

// PROCESAR ELIMINAR
if ($accion_prov === 'eliminar' && $id_prov) {
    if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador') {
        try {
            $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id_proveedor = ?");
            $stmt->execute([$id_prov]);
        } catch (PDOException $e) {
        }
    }
    echo '<script>window.location.href="dashboard.php?seccion=proveedores";</script>';
    exit();
}

// Cargar datos para editar mediante GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $accion_prov === 'editar' && $id_prov) {
    $stmt_ed = $pdo->prepare("SELECT * FROM proveedores WHERE id_proveedor = ?");
    $stmt_ed->execute([$id_prov]);
    $fetched = $stmt_ed->fetch();
    if ($fetched) {
        $prov_edit = $fetched;
    }
}

// OBTENER LISTADO DE PROVEEDORES DESDE LA BD
$stmt = $pdo->query("SELECT * FROM proveedores ORDER BY id_proveedor ASC");
$proveedores = $stmt->fetchAll();
?>

<?php if (($accion_prov === 'nuevo' || $accion_prov === 'editar') && isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
    
    <!-- Alerta en caso de error -->
    <?php if (!empty($mensaje_error)): ?>
        <div style="max-width: 800px; margin: 0 auto 1.2rem auto; background: #FEE2E2; color: #991B1B; padding: 0.8rem 1rem; border-radius: 8px; border: 1px solid #FCA5A5; display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $mensaje_error; ?>
        </div>
    <?php endif; ?>

    <!-- FORMULARIO DE REGISTRO / EDICIÓN -->
    <section class="panel-card" style="max-width: 800px; margin: 0 auto; width: 100%; background: white; padding: 1.8rem; border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.8rem;">
            <div class="card-header-title" style="margin-bottom: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-truck-field" style="color: #0284C7; font-size: 1.2rem;"></i>
                <h3 style="color: #0F172A; font-size: 1.1rem; margin: 0;"><?php echo ($accion_prov === 'editar') ? 'Editar Proveedor' : 'Registrar Nuevo Proveedor'; ?></h3>
            </div>
            <a href="dashboard.php?seccion=proveedores" style="padding: 6px 12px; background: #64748B; color: white; border-radius: 6px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Volver a la Lista
            </a>
        </div>

        <form action="dashboard.php?seccion=proveedores" method="POST" class="form-styled">
            <?php if (!empty($prov_edit['id_proveedor'])): ?>
                <input type="hidden" name="id_proveedor" value="<?php echo $prov_edit['id_proveedor']; ?>">
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                <div class="form-group-custom">
                    <label for="rif_cedula" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">RIF / Cédula *</label>
                    <input type="text" id="rif_cedula" name="rif_cedula" value="<?php echo htmlspecialchars($prov_edit['rif_cedula']); ?>" placeholder="Ej. J-12345678-9" required style="width: 100%; padding: 0.6rem; border: 1px solid #CBD5E1; border-radius: 6px;">
                </div>
                <div class="form-group-custom">
                    <label for="razon_social" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Nombre / Razón Social *</label>
                    <input type="text" id="razon_social" name="razon_social" value="<?php echo htmlspecialchars($prov_edit['razon_social']); ?>" placeholder="Ej. Inversiones Industriales, C.A." required style="width: 100%; padding: 0.6rem; border: 1px solid #CBD5E1; border-radius: 6px;">
                </div>
                <div class="form-group-custom">
                    <label for="telefono" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Teléfono *</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($prov_edit['telefono']); ?>" placeholder="Ej. 0412-5551234" required style="width: 100%; padding: 0.6rem; border: 1px solid #CBD5E1; border-radius: 6px;">
                </div>
                <div class="form-group-custom">
                    <label for="email" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Correo Electrónico *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($prov_edit['email']); ?>" placeholder="proveedor@correo.com" required style="width: 100%; padding: 0.6rem; border: 1px solid #CBD5E1; border-radius: 6px;">
                </div>
                <div class="form-group-custom">
                    <label for="estado" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Estado *</label>
                    <select name="estado" id="estado" style="width: 100%; padding: 0.6rem; border: 1px solid #CBD5E1; border-radius: 6px;">
                        <option value="Activo" <?php echo ($prov_edit['estado'] === 'Activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="Inactivo" <?php echo ($prov_edit['estado'] === 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                <div class="form-group-custom" style="grid-column: span 2;">
                    <label for="direccion" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Dirección *</label>
                    <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($prov_edit['direccion']); ?>" placeholder="Av. Principal, Local 12" required style="width: 100%; padding: 0.6rem; border: 1px solid #CBD5E1; border-radius: 6px;">
                </div>
            </div>
            <div style="margin-top: 1.5rem; display: flex; gap: 10px;">
                <button type="submit" class="btn-submit-action" style="flex: 1; background: #0284C7; color: white; padding: 0.75rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                    <i class="fa-solid fa-floppy-disk"></i> <?php echo (!empty($prov_edit['id_proveedor'])) ? 'Actualizar Proveedor' : 'Guardar Proveedor'; ?>
                </button>
                <a href="dashboard.php?seccion=proveedores" style="padding: 10px 20px; background: #94A3B8; color: white; border-radius: 6px; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                    Cancelar
                </a>
            </div>
        </form>
    </section>

<?php else: ?>

    <!-- VISTA PRINCIPAL DE PROVEEDORES -->
    <section class="panel-card" style="width: 100%; <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'max-width: 950px; margin: 0 auto;' : 'height: 100%;'; ?> background: white; padding: 1.8rem; border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between; box-sizing: border-box;">
        <div>
            <!-- Barra Superior con -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; gap: 1rem; flex-wrap: wrap;">
                
                <!-- Buscador por RIF, Cédula o Nombre -->
                <div style="position: relative; flex: 1; min-width: 260px; max-width: 350px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #0284C7;"></i>
                    <input type="text" id="inputBuscarProveedor" onkeyup="filtrarProveedores()" placeholder="Buscar proveedor por RIF, cédula, nombre..." style="width: 100%; padding: 0.65rem 1rem 0.65rem 2.5rem; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.88rem; background: #F8FAFC; outline: none;">
                </div>

                <!-- Filtros Desplegables y Botón Nuevo -->
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <!-- Selector de Estado -->
                    <div style="display: flex; align-items: center; gap: 8px; border: 1px solid #CBD5E1; border-radius: 8px; padding: 0.5rem 0.9rem; background: #FFFFFF; font-size: 0.85rem; color: #334155;">
                        <i class="fa-solid fa-filter" style="color: #0284C7;"></i>
                        <select id="filtroEstado" onchange="filtrarProveedores()" style="border: none; background: transparent; outline: none; font-size: 0.85rem; color: #334155; cursor: pointer;">
                            <option value="">Estado: Todos</option>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>

                    <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                        <!-- Botón Nuevo Proveedor -->
                        <a href="dashboard.php?seccion=proveedores&accion_prov=nuevo" style="width: auto; padding: 0.65rem 1.4rem; border-radius: 8px; background: #0284C7; color: white; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);">
                            <i class="fa-solid fa-plus"></i> Nuevo Proveedor
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabla de Proveedores con Scroll Interno -->
            <div class="table-scroll-container" style="max-height: 450px; overflow-y: auto;">
                <table class="learca-table" id="tablaProveedores" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="background-color: #F8FAFC; border-bottom: 1px solid #CBD5E1;">
                            <th style="padding: 0.8rem 1rem; text-align: <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'center' : 'left'; ?>; color: #718096; <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'width: 10%;' : ''; ?>">N°</th>
                            
                            <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                <th style="padding: 0.8rem 1rem; text-align: left; color: #718096;">RIF / Cédula</th>
                            <?php endif; ?>
                            
                            <th style="padding: 0.8rem 1rem; text-align: left; color: #718096; <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'width: 40%;' : ''; ?>">Nombre / Razón Social</th>
                            
                            <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                <th style="padding: 0.8rem 1rem; text-align: left; color: #718096;">Teléfono</th>
                                <th style="padding: 0.8rem 1rem; text-align: left; color: #718096;">Correo Electrónico</th>
                                <th style="padding: 0.8rem 1rem; text-align: left; color: #718096;">Dirección</th>
                            <?php else: ?>
                                <th style="padding: 0.8rem 1rem; text-align: left; color: #718096; width: 25%;">Teléfono</th>
                            <?php endif; ?>
                            
                            <th style="padding: 0.8rem 1rem; text-align: left; color: #718096; <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'width: 15%;' : ''; ?>">Estado</th>
                            
                            <th style="padding: 0.8rem 1rem; text-align: center; color: #718096; <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'width: 10%;' : ''; ?>">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($proveedores) > 0): ?>
                            <?php $contador = 1; foreach ($proveedores as $prov): ?>
                                <tr style="border-bottom: 1px solid #F1F5F9;" data-estado="<?php echo htmlspecialchars($prov['estado'] ?? 'Activo'); ?>">
                                    <td style="padding: 0.8rem 1rem; text-align: <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Empleado') ? 'center' : 'left'; ?>;"><strong><?php echo $contador++; ?></strong></td>
                                    
                                    <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                        <td style="padding: 0.8rem 1rem;"><?php echo htmlspecialchars($prov['rif_cedula']); ?></td>
                                    <?php endif; ?>
                                    
                                    <td style="padding: 0.8rem 1rem;"><strong><?php echo htmlspecialchars($prov['razon_social']); ?></strong></td>
                                    
                                    <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                        <td style="padding: 0.8rem 1rem;"><?php echo htmlspecialchars($prov['telefono']); ?></td>
                                        <td style="padding: 0.8rem 1rem;"><a href="mailto:<?php echo htmlspecialchars($prov['email']); ?>" style="color: #0284C7; text-decoration: none;"><?php echo htmlspecialchars($prov['email']); ?></a></td>
                                        <td style="padding: 0.8rem 1rem;"><?php echo htmlspecialchars($prov['direccion']); ?></td>
                                    <?php else: ?>
                                        <td style="padding: 0.8rem 1rem;"><?php echo htmlspecialchars($prov['telefono']); ?></td>
                                    <?php endif; ?>
                                    
                                    <td style="padding: 0.8rem 1rem;">
                                        <span style="background: #D1FAE5; color: #065F46; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                            <?php echo htmlspecialchars($prov['estado'] ?? 'Activo'); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 0.8rem 1rem; text-align: center;">
                                        <button type="button" onclick="verProveedor('<?php echo htmlspecialchars($prov['rif_cedula'] ?? ''); ?>', '<?php echo htmlspecialchars($prov['razon_social'] ?? ''); ?>', '<?php echo htmlspecialchars($prov['telefono'] ?? ''); ?>', '<?php echo htmlspecialchars($prov['email'] ?? ''); ?>', '<?php echo htmlspecialchars($prov['direccion'] ?? ''); ?>', '<?php echo htmlspecialchars($prov['estado'] ?? 'Activo'); ?>')" title="Visualizar" style="background: none; border: none; color: #0284c7; font-size: 1rem; cursor: pointer; <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador') ? 'margin-right: 6px;' : ''; ?>">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        
                                        <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                                            <a href="dashboard.php?seccion=proveedores&accion_prov=editar&id=<?php echo $prov['id_proveedor']; ?>" title="Editar" style="color: #D97706; font-size: 0.95rem; text-decoration: none; margin-right: 6px;"><i class="fa-solid fa-pen-to-square"></i></a>
                                            <a href="dashboard.php?seccion=proveedores&accion_prov=eliminar&id=<?php echo $prov['id_proveedor']; ?>" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este proveedor?');" style="color: #DC2626; font-size: 0.95rem; text-decoration: none;"><i class="fa-solid fa-trash"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="empty-table" style="text-align: center; padding: 2rem; color: #718096;">No hay proveedores registrados actualmente.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Botón de Exportar a PDF (Bloqueado para Empleados / Activo para Administradores) -->
        <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
            <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                <a href="reporte_proveedores.php" target="_blank" style="background: #dc3545; color: white; padding: 0.6rem 1rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-file-pdf"></i> Exportar Directorio de Proveedores PDF
                </a>
            <?php else: ?>
                <span title="Acceso restringido solo para administradores" style="background: #94A3B8; color: #F1F5F9; padding: 0.6rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; cursor: not-allowed; opacity: 0.7;">
                    <i class="fa-solid fa-lock"></i> Exportar Directorio de Proveedores PDF
                </span>
            <?php endif; ?>
        </div>
    </section>

<?php endif; ?>

<!-- Modal para Visualizar Detalles del Proveedor -->
<div id="modalVisualizarProveedor" class="modal-overlay">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.5rem;">
            <h3 style="color: #0284c7; font-size: 1.1rem;"><i class="fa-solid fa-truck-field"></i> Detalles del Proveedor</h3>
            <button onclick="cerrarModalProveedor()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748B;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div style="font-size: 0.9rem; color: #1E293B; display: flex; flex-direction: column; gap: 8px;">
            <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                <p><strong>RIF / Cédula:</strong> <span id="modal-prov-rif"></span></p>
                <p><strong>Razón Social:</strong> <span id="modal-prov-razon"></span></p>
                <p><strong>Teléfono:</strong> <span id="modal-prov-telefono"></span></p>
                <p><strong>Correo Electrónico:</strong> <span id="modal-prov-email"></span></p>
                <p><strong>Dirección:</strong> <span id="modal-prov-direccion"></span></p>
                <p><strong>Estado:</strong> <span id="modal-prov-estado"></span></p>
            <?php else: ?>
                <p><strong>Razón Social:</strong> <span id="modal-prov-razon"></span></p>
                <p><strong>Teléfono:</strong> <span id="modal-prov-telefono"></span></p>
                <p><strong>Estado:</strong> <span id="modal-prov-estado"></span></p>
            <?php endif; ?>
        </div>
        <div style="margin-top: 1.5rem; text-align: right;">
            <button onclick="cerrarModalProveedor()" style="background: #0284c7; color: white; border: none; padding: 6px 15px; border-radius: 6px; font-size: 0.85rem; cursor: pointer;">Cerrar</button>
        </div>
    </div>
</div>

<!-- Script de Filtrado y Control del Modal de Proveedores -->
<script>
function verProveedor(rif, razon, telefono, email, direccion, estado) {
    const esAdmin = <?php echo (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador') ? 'true' : 'false'; ?>;
    
    if (esAdmin) {
        document.getElementById('modal-prov-rif').innerText = rif;
        document.getElementById('modal-prov-email').innerText = email;
        document.getElementById('modal-prov-direccion').innerText = direccion;
    }
    
    document.getElementById('modal-prov-razon').innerText = razon;
    document.getElementById('modal-prov-telefono').innerText = telefono;
    document.getElementById('modal-prov-estado').innerText = estado;
    document.getElementById('modalVisualizarProveedor').style.display = 'flex';
}

function cerrarModalProveedor() {
    document.getElementById('modalVisualizarProveedor').style.display = 'none';
}

function filtrarProveedores() {
    const texto = document.getElementById('inputBuscarProveedor').value.toLowerCase();
    const estadoFiltro = document.getElementById('filtroEstado').value;
    const filas = document.querySelectorAll('#tablaProveedores tbody tr');

    filas.forEach(fila => {
        if (fila.classList.contains('empty-table') || fila.cells.length < 3) return;
        
        const contenidoFila = fila.innerText.toLowerCase();
        const estadoFila = fila.getAttribute('data-estado');

        const coincideTexto = contenidoFila.includes(texto);
        const coincideEstado = (estadoFiltro === "" || estadoFila === estadoFiltro);

        if (coincideTexto && coincideEstado) {
            fila.style.display = '';
        } else {
            fila.style.display = 'none';
        }
    });
}
</script>