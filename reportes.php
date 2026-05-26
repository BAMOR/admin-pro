<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Total de ventas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM ventas");
$total_ventas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total de ingresos
$stmt = $pdo->query("SELECT SUM(total) as ingresos FROM ventas");
$ingresos = $stmt->fetch(PDO::FETCH_ASSOC)['ingresos'];

// Ventas por fecha
$stmt = $pdo->query("
    SELECT DATE(fecha) as dia, COUNT(*) as cantidad, SUM(total) as total_dia
    FROM ventas
    GROUP BY DATE(fecha)
    ORDER BY dia DESC
");
$ventas_fecha = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ventas por cliente
$stmt = $pdo->query("
    SELECT u.nombre, COUNT(v.id) as compras, SUM(v.total) as total_gastado
    FROM usuarios u
    JOIN ventas v ON u.id = v.usuario_id
    GROUP BY u.id
    ORDER BY total_gastado DESC
    LIMIT 5
");
$ventas_cliente = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Obtener meses e ingresos para el gráfico
$meses = [];
$ingresos_mes = [];
foreach ($ventas_mes as $v) {
    $meses[] = $v['mes'];
    $ingresos_mes[] = $v['total_mes'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Panel Admin</title>
    <link rel="stylesheet" href="styles/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .metrica {
            display: inline-block;
            width: 200px;
            padding: 15px;
            margin: 10px;
            background-color: #e7f3ff;
            border-radius: 4px;
            text-align: center;
        }
        .metrica h3 {
            margin: 0;
            font-size: 1.2em;
        }
        .metrica p {
            font-size: 1.5em;
            font-weight: bold;
            color: #4CAF50;
        }
        .grafico {
            margin: 20px 0;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <header>
        <h1>Reportes de Ventas</h1>
        <div>
            <a href="admin.php">Panel</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="container">
        <h2>Métricas Generales</h2>
        <div>
            <div class="metrica">
                <h3>Total de Ventas</h3>
                <p><?php echo $total_ventas; ?></p>
            </div>
            <div class="metrica">
                <h3>Ingresos Totales</h3>
                <p>$<?php echo number_format($ingresos, 2); ?></p>
            </div>
        </div>

        <!-- Botones de exportación -->
    <div style="margin: 20px 0;">
        <a href="exportar_pdf.php" class="btn">Exportar a PDF</a>
    </div>

        <h2>Ventas por Fecha</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas_fecha as $v): ?>
                    <tr>
                        <td><?php echo $v['dia']; ?></td>
                        <td><?php echo $v['cantidad']; ?></td>
                        <td>$<?php echo number_format($v['total_dia'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Clientes con Más Compras</h2>
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Compras</th>
                    <th>Total Gastado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas_cliente as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                        <td><?php echo $c['compras']; ?></td>
                        <td>$<?php echo number_format($c['total_gastado'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Gráfico de ingresos por mes -->
        

        <!-- Gráfico de ventas por cliente -->
        <div class="grafico">
            <h3>Ventas por Cliente</h3>
            <canvas id="graficoClientes"></canvas>
        </div>
    </div>

    <script>
       

        // Gráfico de ventas por cliente
        const ctx2 = document.getElementById('graficoClientes').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($ventas_cliente, 'nombre')); ?>,
                datasets: [{
                    label: 'Total gastado',
                    data: <?php echo json_encode(array_column($ventas_cliente, 'total_gastado')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>