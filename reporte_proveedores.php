<?php
session_start();
require_once 'conexion.php';

date_default_timezone_set('America/Caracas');

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM proveedores ORDER BY id_proveedor ASC");
$proveedores = $stmt->fetchAll();

$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$fecha_formateada = date('j') . " de " . $meses[(int)date('n')] . " de " . date('Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Directorio de Proveedores - Multiservicios Learca, C.A.</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif, Arial;
            color: #000000;
            margin: 0;
            padding: 20px;
            background: #E2E8F0;
        }

        .sheet {
            width: 100%;
            min-height: 258mm; 
            background: #FFFFFF;
            padding: 20px 30px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .wrapper-preview {
            max-width: 816px;
            margin: 0 auto;
            background: #FFFFFF;
            border: 1px solid #475569;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .no-print {
            max-width: 816px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            font-family: Arial, sans-serif;
        }

        .btn-action {
            border: none;
            padding: 10px 20px;
            font-size: 0.95rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-download { background: #0E5AAC; color: white; }
        .btn-download:hover { background: #093b73; }
        .btn-back { background: #64748B; color: white; }
        .btn-back:hover { background: #475569; }

        .header-logo-container {
            margin-bottom: 2px;
        }

        .logo-img {
            height: 85px;
            width: auto;
            object-fit: contain;
        }

        .address-bar {
            font-size: 0.68rem;
            color: #000000;
            margin-top: 4px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .title-box {
            border: 2px solid #0E5AAC;
            background-color: #F8FAFC;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .title-box h2 {
            margin: 0;
            font-size: 1.15rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: bold;
            color: #0E5AAC;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #94A3B8;
            padding: 7px 8px;
            font-size: 0.85rem;
            text-align: left;
        }

        table.data-table th {
            background-color: #0E5AAC !important;
            color: #FFFFFF !important;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        table.data-table tr:nth-child(even) {
            background-color: #F8FAFC !important;
        }

        .date-right {
            text-align: right;
            font-size: 0.95rem;
            font-weight: bold;
            margin-top: 15px;
        }

        .footer-container {
            width: 100%;
            padding-top: 20px;
            padding-bottom: 5px;
        }

        .signature-section {
            width: 55%;
            margin: 0 auto;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000000;
            margin-bottom: 6px;
        }

        .signature-text {
            font-size: 0.9rem;
            font-weight: bold;
            line-height: 1.3;
        }

        @page {
            size: letter;
            margin: 10mm;
        }

        @media print {
            body { background: #FFFFFF; padding: 0; }
            .wrapper-preview { border: none; box-shadow: none; max-width: 100%; }
            .sheet { padding: 10px 20px; min-height: 258mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="descargarPDF()" class="btn-action btn-download">
            <i class="fa-solid fa-download"></i> Descargar Reporte PDF
        </button>
        <a href="dashboard.php?seccion=proveedores" class="btn-action btn-back">
            <i class="fa-solid fa-arrow-left"></i> Volver al Sistema
        </a>
    </div>

    <div class="wrapper-preview">
        <div class="sheet" id="reporte-pdf">
            
            <div class="top-content">
                <div class="header-logo-container">
                    <img src="assets/img/logo.png" alt="Logo" class="logo-img">
                </div>
                
                <div class="address-bar">
                    CALLE 5, CASA N°87-18, URBANIZACIÓN “FORTUNATO ORELLANA” - CABUDARE, ESTADO LARA, VENEZUELA
                </div>

                <div class="title-box">
                    <h2>DIRECTORIO OFICIAL DE PROVEEDORES Y COMPRAS</h2>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 6%;">N°</th>
                            <th style="width: 18%;">RIF / Cédula</th>
                            <th style="width: 32%;">Razón Social</th>
                            <th style="width: 18%;">Teléfono</th>
                            <th style="width: 26%;">Correo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($proveedores) > 0): ?>
                            <?php $i = 1; foreach ($proveedores as $p): ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($p['rif_cedula']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($p['razon_social']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['telefono']); ?></td>
                                    <td><?php echo htmlspecialchars($p['email']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;">No hay proveedores registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="date-right">
                    <?php echo $fecha_formateada; ?>
                </div>
            </div>

            <div class="footer-container">
                <div class="signature-section">
                    <div class="signature-line"></div>
                    <div class="signature-text">
                        Departamento Administrativo<br>
                        Multiservicios Learca, C.A.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function descargarPDF() {
            const elemento = document.getElementById('reporte-pdf');
            const opciones = {
                margin:      [10, 10, 10, 10],
                filename:    'Reporte_Proveedores_Multiservicios_Learca.pdf',
                image:       { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 3, useCORS: true, letterRendering: true },
                jsPDF:       { unit: 'mm', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().from(elemento).set(opciones).save();
        }
    </script>
</body>
</html>