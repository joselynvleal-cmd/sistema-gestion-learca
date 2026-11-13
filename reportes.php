<?php
require_once 'conexion.php';

// Obtener estadísticas rápidas de la base de datos
$total_empleados = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$total_proveedores = $pdo->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
?>

<div class="welcome-container" style="padding-top: 0; margin-top: 5px; padding-bottom: 0.5rem; display: flex; flex-direction: column; gap: 1.5rem;">
    
    <!-- 1. Tarjetas Superiores de Estadísticas -->
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
        
        <!-- Tarjeta 1: Personal Registrado -->
        <div style="background: white; padding: 1.5rem; border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; gap: 1.2rem; z-index: 2;">
                <div style="width: 55px; height: 55px; background: linear-gradient(135deg, #2563EB, #3B82F6); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.4rem; box-shadow: 0 6px 15px rgba(37, 99, 235, 0.35);">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <h4 style="color: #64748B; font-size: 0.75rem; margin: 0 0 4px 0; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Personal Registrado</h4>
                    <span style="font-size: 2.2rem; font-weight: 900; color: #0F172A; line-height: 1;"><?php echo $total_empleados; ?></span>
                </div>
            </div>
            <!-- Gráfico de línea con degradado azul -->
            <div style="position: absolute; right: 0; bottom: 0; z-index: 1;">
                <svg width="220" height="75" viewBox="0 0 220 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M30 65C45 65 55 52 70 52C85 52 100 32 120 32C135 32 145 45 160 42C175 39 190 22 205 12V75H30V65Z" fill="url(#blue_fade_exact)"/>
                    <path d="M30 65C45 65 55 52 70 52C85 52 100 32 120 32C135 32 145 45 160 42C175 39 190 22 205 12" stroke="#3B82F6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="70" cy="52" r="3" fill="#3B82F6"/>
                    <circle cx="120" cy="32" r="3" fill="#3B82F6"/>
                    <circle cx="160" cy="42" r="3" fill="#3B82F6"/>
                    <circle cx="205" cy="12" r="3.5" fill="#2563EB"/>
                    <defs>
                        <linearGradient id="blue_fade_exact" x1="117" y1="12" x2="117" y2="75" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#3B82F6" stop-opacity="0.28"/>
                            <stop offset="1" stop-color="#93C5FD" stop-opacity="0.01"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- Tarjeta 2: Proveedores Activos -->
        <div style="background: white; padding: 1.5rem; border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; gap: 1.2rem; z-index: 2;">
                <div style="width: 55px; height: 55px; background: linear-gradient(135deg, #059669, #10B981); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.4rem; box-shadow: 0 6px 15px rgba(16, 185, 129, 0.35);">
                    <i class="fa-solid fa-truck-field"></i>
                </div>
                <div>
                    <h4 style="color: #64748B; font-size: 0.75rem; margin: 0 0 4px 0; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Proveedores Activos</h4>
                    <span style="font-size: 2.2rem; font-weight: 900; color: #0F172A; line-height: 1;"><?php echo $total_proveedores; ?></span>
                </div>
            </div>
            <!-- Gráfico de barras con tono verde y degradado vertical -->
            <div style="display: flex; align-items: flex-end; gap: 6px; height: 48px; padding-right: 10px; z-index: 2;">
                <div style="width: 10px; height: 12px; background: linear-gradient(to top, rgba(52, 211, 153, 0.25), #34D399); border-radius: 2px 2px 0 0;"></div>
                <div style="width: 10px; height: 18px; background: linear-gradient(to top, rgba(52, 211, 153, 0.25), #34D399); border-radius: 2px 2px 0 0;"></div>
                <div style="width: 10px; height: 24px; background: linear-gradient(to top, rgba(52, 211, 153, 0.25), #34D399); border-radius: 2px 2px 0 0;"></div>
                <div style="width: 10px; height: 30px; background: linear-gradient(to top, rgba(52, 211, 153, 0.25), #34D399); border-radius: 2px 2px 0 0;"></div>
                <div style="width: 10px; height: 36px; background: linear-gradient(to top, rgba(52, 211, 153, 0.25), #34D399); border-radius: 2px 2px 0 0;"></div>
                <div style="width: 10px; height: 42px; background: linear-gradient(to top, rgba(52, 211, 153, 0.25), #34D399); border-radius: 2px 2px 0 0;"></div>
                <div style="width: 10px; height: 48px; background: linear-gradient(to top, rgba(52, 211, 153, 0.25), #34D399); border-radius: 2px 2px 0 0;"></div>
            </div>
        </div>

    </div>

    <!-- 2. Sección Principal de Acceso a Reportes en PDF -->
    <div class="panel-card" style="background: white; padding: 1.8rem; border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 1.5rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 1rem;">
            <div style="width: 45px; height: 45px; background: #EFF6FF; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #0E5AAC; font-size: 1.3rem;">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
            <div>
                <h3 style="color: #0F172A; font-size: 1.15rem; margin: 0; font-weight: 700;">Generación y Visualización de Reportes Oficiales</h3>
                <p style="color: #64748B; font-size: 0.85rem; margin: 3px 0 0 0;">Selecciona el reporte institucional que deseas visualizar o descargar en PDF tamaño carta.</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            
            <!-- Tarjeta PDF Nómina -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; display: grid; grid-template-columns: 1fr 150px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                <!-- Columna Izquierda: Contenido y Botón -->
                <div style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; gap: 1.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                            <div style="width: 40px; height: 40px; background: #EFF6FF; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #2563EB; font-size: 1.1rem;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <h4 style="color: #0F172A; font-size: 1.1rem; margin: 0; font-weight: 700;">Reporte Oficial de Nómina</h4>
                        </div>
                        <p style="color: #64748B; font-size: 0.85rem; margin: 0; line-height: 1.5;">Listado completo del personal activo con sus cargos asignados y membrete oficial de la empresa.</p>
                    </div>
                    <div>
                        <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                            <a href="reporte_pdf.php" target="_blank" style="background: #0E5AAC; color: white; padding: 0.75rem 1.4rem; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 0.88rem; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(14, 90, 172, 0.25);">
                                <i class="fa-solid fa-eye"></i> Ver / Descargar PDF Nómina
                            </a>
                        <?php else: ?>
                            <span title="Acceso restringido solo para administradores" style="background: #94A3B8; color: #F1F5F9; padding: 0.75rem 1.4rem; border-radius: 10px; font-weight: 600; font-size: 0.88rem; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: not-allowed; opacity: 0.7;">
                                <i class="fa-solid fa-lock"></i> Ver / Descargar PDF Nómina
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Columna Derecha-->
                <div style="display: flex; align-items: center; justify-content: center; position: relative; background: #FFFFFF; padding: 1rem;">
                    <div style="position: absolute; left: 0; top: 12px; bottom: 12px; width: 1px; background: #E2E8F0;"></div>
                    <img src="assets/img/personal.png" alt="Reporte de Nómina" style="max-width: 90%; max-height: 150px; object-fit: contain;">
                </div>
            </div>

            <!-- Tarjeta PDF Proveedores -->
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 16px; display: grid; grid-template-columns: 1fr 150px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                <!-- Columna Izquierda -->
                <div style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; gap: 1.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                            <div style="width: 40px; height: 40px; background: #ECFDF5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #059669; font-size: 1.1rem;">
                                <i class="fa-solid fa-handshake"></i>
                            </div>
                            <h4 style="color: #0F172A; font-size: 1.1rem; margin: 0; font-weight: 700;">Directorio de Proveedores</h4>
                        </div>
                        <p style="color: #64748B; font-size: 0.85rem; margin: 0; line-height: 1.5;">Directorio corporativo detallado de proveedores y compras con datos de contacto y validación institucional.</p>
                    </div>
                    <div>
                        <?php if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'Administrador'): ?>
                            <a href="reporte_proveedores.php" target="_blank" style="background: #0E5AAC; color: white; padding: 0.75rem 1.4rem; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 0.88rem; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(14, 90, 172, 0.25);">
                                <i class="fa-solid fa-eye"></i> Ver / Descargar PDF Proveedores
                            </a>
                        <?php else: ?>
                            <span title="Acceso restringido solo para administradores" style="background: #94A3B8; color: #F1F5F9; padding: 0.75rem 1.4rem; border-radius: 10px; font-weight: 600; font-size: 0.88rem; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: not-allowed; opacity: 0.7;">
                                <i class="fa-solid fa-lock"></i> Ver / Descargar PDF Proveedores
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Columna Derecha -->
                <div style="display: flex; align-items: center; justify-content: center; position: relative; background: #FFFFFF; padding: 1rem;">
                    <div style="position: absolute; left: 0; top: 12px; bottom: 12px; width: 1px; background: #E2E8F0;"></div>
                    <img src="assets/img/proveedor.png" alt="Directorio de Proveedores" style="max-width: 150%; max-height: 90px; object-fit: contain;">
                </div>
            </div>

        </div>
    </div>
</div>