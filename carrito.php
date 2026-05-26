<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

// Actualizar cantidad
if (isset($_POST['producto_id'])) {
    $id = $_POST['producto_id'];
    $cantidad = (int)$_POST['cantidad'];

    if ($cantidad <= 0) {
        unset($_SESSION['carrito'][$id]);
    } else {
        $_SESSION['carrito'][$id]['cantidad'] = $cantidad;
    }
}

// Eliminar del carrito
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    unset($_SESSION['carrito'][$id]);
}

// Calcular total
$total = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <h1>Carrito de Compras</h1>
        <a href="index.php">Volver a la tienda</a>
        <a href="logout.php">Cerrar sesión</a>
    </header>

    <div class="container">
        <h2>Productos en tu carrito</h2>

        <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
            <table border="1" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['carrito'] as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td>$<?php echo $item['precio']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="producto_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" min="0" style="width:60px;" onchange="this.form.submit()">
                                </form>
                            </td>
                            <td>$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                            <td><a href="javascript:void(0);" class="btn-delete" onclick="eliminarProducto(<?php echo $item['id']; ?>)">Eliminar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="checkout.php"><button>Proceder al pago</button></a>

            <h3>Total: $<?php echo number_format($total, 2); ?></h3>
        <?php else: ?>
            <p>Tu carrito está vacío.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>

<script>
function eliminarProducto(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'No podrás revertir esta acción',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?eliminar=' + id;
        }
    });
}
</script>