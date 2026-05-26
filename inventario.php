<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Agregar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria = $_POST['categoria'];

    $imagen = 'default.png';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $ext = strtolower(pathinfo($nombre_imagen, PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $permitidas)) {
            // Renombrar imagen con el nombre del producto
            $nombre_producto = strtolower(trim($nombre));
            $nombre_producto = preg_replace('/[^a-zA-Z0-9\s]/', '', $nombre_producto); // Quitar caracteres especiales
            $nombre_producto = str_replace(' ', '-', $nombre_producto); // Reemplazar espacios por guiones
            $imagen = $nombre_producto . '.' . $ext;

            move_uploaded_file($ruta_temp, 'imagenes/' . $imagen);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, imagen, categoria) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen, $categoria]);

    // Redirigir para limpiar el formulario
    header("Location: inventario.php");
    exit();
}

// Editar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria = $_POST['categoria'];

    $imagen = $_POST['imagen_actual'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temp = $_FILES['imagen']['tmp_name'];
        $ext = strtolower(pathinfo($nombre_imagen, PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $permitidas)) {
            // Eliminar imagen anterior
            if ($_POST['imagen_actual'] !== 'default.png') {
                unlink('imagenes/' . $_POST['imagen_actual']);
            }

            // Renombrar imagen con el nombre del producto
            $nombre_producto = strtolower(trim($nombre));
            $nombre_producto = preg_replace('/[^a-zA-Z0-9\s]/', '', $nombre_producto);
            $nombre_producto = str_replace(' ', '-', $nombre_producto);
            $imagen = $nombre_producto . '.' . $ext;

            move_uploaded_file($ruta_temp, 'imagenes/' . $imagen);
        }
    }

    $stmt = $pdo->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, imagen=?, categoria=? WHERE id=?");
    $stmt->execute([$nombre, $descripcion, $precio, $stock, $imagen, $categoria, $id]);

    // Redirigir para limpiar el formulario
    header("Location: inventario.php");
    exit();
}

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto['imagen'] !== 'default.png') {
        unlink('imagenes/' . $producto['imagen']);
    }

    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: inventario.php");
    exit();
}

// Cargar producto a editar
$productoEditar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $productoEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <h1>Inventario de Productos</h1>
        <a href="admin.php">Volver al panel</a>
        <a href="logout.php">Cerrar sesión</a>
    </header>

    <div class="container">
        <h2><?php echo $productoEditar ? 'Editar Producto' : 'Agregar Producto'; ?></h2>
        <form method="POST" enctype="multipart/form-data" id="formulario-inventario">
            <?php if ($productoEditar): ?>
                <input type="hidden" name="id" value="<?php echo $productoEditar['id']; ?>">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="imagen_actual" value="<?php echo $productoEditar['imagen']; ?>">
            <?php else: ?>
                <input type="hidden" name="accion" value="agregar">
            <?php endif; ?>

            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?php echo $productoEditar ? htmlspecialchars($productoEditar['nombre']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="descripcion"><?php echo $productoEditar ? htmlspecialchars($productoEditar['descripcion']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label>Precio:</label>
                <input type="number" step="0.01" name="precio" value="<?php echo $productoEditar ? $productoEditar['precio'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Stock:</label>
                <input type="number" name="stock" value="<?php echo $productoEditar ? $productoEditar['stock'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Categoría:</label>
                <input type="text" name="categoria" value="<?php echo $productoEditar ? htmlspecialchars($productoEditar['categoria']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Imagen:</label>
                <input type="file" name="imagen" accept="image/*">
                <?php if ($productoEditar && $productoEditar['imagen'] !== 'default.png'): ?>
                    <img src="imagenes/<?php echo $productoEditar['imagen']; ?>" width="100">
                <?php endif; ?>
            </div>
            <button type="submit"><?php echo $productoEditar ? 'Actualizar Producto' : 'Agregar Producto'; ?></button>
        </form>

        <h2>Lista de Productos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM productos");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td><img src='imagenes/{$row['imagen']}' width='50'></td>";
                    echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
                    echo "<td>{$row['precio']}</td>";
                    echo "<td>{$row['stock']}</td>";
                    echo "<td>" . htmlspecialchars($row['categoria']) . "</td>";
                    echo "<td class='acciones'>
        <a href='?editar={$row['id']}' class='btn-update'>Editar</a>
        <a href='javascript:void(0);' class='btn-delete' onclick='eliminarProducto({$row['id']})'>Eliminar</a>
      </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
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