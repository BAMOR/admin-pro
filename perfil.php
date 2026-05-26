<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Actualizar datos del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];

    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=? WHERE id=?");
    $stmt->execute([$nombre, $email, $_SESSION['user_id']]);

    $_SESSION['nombre'] = $nombre; // Actualizar nombre en sesión
    
    // Guardar mensaje en sesión
    $_SESSION['mensaje_perfil'] = "Información actualizada correctamente.";
    
    header("Location: perfil.php");
    exit();
}

// Obtener historial de compras
$stmt = $pdo->prepare("SELECT * FROM ventas WHERE usuario_id = ? ORDER BY fecha DESC");
$stmt->execute([$_SESSION['user_id']]);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        .seguimiento {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
            margin-top: 10px;
        }
        .paso {
            padding: 5px 10px;
            border-radius: 4px;
            margin: 0 5px;
        }
        .activo {
            background-color: #4CAF50;
            color: white;
        }
        .inactivo {
            background-color: #ccc;
            color: #666;
        }
        .flecha {
            color: #666;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <header>
        <h1>Perfil de Usuario</h1>
        <div>
            <a href="index.php">Tienda</a>
            <a href="carrito.php">Carrito</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="container">
        <h2>Información de tu cuenta</h2>

        <form method="POST">
            <input type="hidden" name="accion" value="actualizar">
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
            </div>
            <button type="submit">Actualizar Información</button>
        </form>

        <h2>Historial de Compras</h2>
        <?php if (count($ventas) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>Total</th>
                        <th>Método de Pago</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Seguimiento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $venta): ?>
                        <tr>
                            <td><?php echo $venta['id']; ?></td>
                            <td>$<?php echo $venta['total']; ?></td>
                            <td><?php echo htmlspecialchars($venta['metodo_pago']); ?></td>
                            <td><?php echo $venta['fecha']; ?></td>
                            <td><?php echo htmlspecialchars($venta['estado']); ?></td>
                            <td>
                                <button onclick="mostrarSeguimiento(<?php echo $venta['id']; ?>)">Rastrear</button>
                            </td>
                        </tr>
                        <tr id="seguimiento-<?php echo $venta['id']; ?>" style="display:none;">
                            <td colspan="6">
                                <div class="seguimiento">
                                    <h4>Seguimiento del pedido #<?php echo $venta['id']; ?></h4>
                                    <?php
                                    $estados = [
                                        'Confirmado',
                                        'En empaque',
                                        'En transporte',
                                        'En camino',
                                        'Entregado'
                                    ];

                                    $estado_actual = array_search($venta['estado'], $estados);

                                    for ($i = 0; $i < count($estados); $i++) {
                                        $activo = $i <= $estado_actual ? 'activo' : 'inactivo';
                                        echo "<div class='paso $activo'>{$estados[$i]}</div>";
                                        if ($i < count($estados) - 1) {
                                            echo "<div class='flecha'>→</div>";
                                        }
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tienes compras registradas.</p>
        <?php endif; ?>
    </div>

    <script>
        function mostrarSeguimiento(id) {
            var elemento = document.getElementById('seguimiento-' + id);
            if (elemento.style.display === 'none') {
                elemento.style.display = 'table-row';
            } else {
                elemento.style.display = 'none';
            }
        }
        
        // Mostrar alerta si se actualizó el perfil
        <?php if (isset($_SESSION['mensaje_perfil'])): ?>
            Swal.fire({
                title: '¡Éxito!',
                text: '<?php echo $_SESSION['mensaje_perfil']; ?>',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
            <?php unset($_SESSION['mensaje_perfil']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
<?php include 'footer.php'; ?>