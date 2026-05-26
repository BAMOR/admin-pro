<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Mostrar mensaje de compra exitosa
if (isset($_SESSION['mensaje'])) {
    echo '<div class="alert alert-success"><strong>✅</strong> ' . $_SESSION['mensaje'] . '</div>';
    unset($_SESSION['mensaje']);
}

// Agregar reseña
if (isset($_POST['comentar'])) {
    $producto_id = $_POST['producto_id'];
    $comentario = $_POST['comentario'];
    $calificacion = $_POST['calificacion'];

    $stmt = $pdo->prepare("INSERT INTO reseñas (producto_id, usuario_id, comentario, calificacion) VALUES (?, ?, ?, ?)");
    $stmt->execute([$producto_id, $_SESSION['user_id'], $comentario, $calificacion]);
}

// Agregar al carrito
if (isset($_POST['agregar'])) {
    $producto_id = $_POST['producto_id'];
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        if (isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id]['cantidad']++;
        } else {
            $_SESSION['carrito'][$producto_id] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'cantidad' => 1
            ];
        }
    }
}

// Filtros
$filtro_nombre = '';
$filtro_categoria = '';
$filtro_precio_min = '';
$filtro_precio_max = '';
$filtro_popularidad = '';

if (isset($_GET['buscar'])) {
    $filtro_nombre = $_GET['buscar'];
}
if (isset($_GET['categoria'])) {
    $filtro_categoria = $_GET['categoria'];
}
if (isset($_GET['precio_min'])) {
    $filtro_precio_min = $_GET['precio_min'];
}
if (isset($_GET['precio_max'])) {
    $filtro_precio_max = $_GET['precio_max'];
}
if (isset($_GET['popularidad'])) {
    $filtro_popularidad = $_GET['popularidad'];
}

$sql = "SELECT p.*, (SELECT AVG(calificacion) FROM reseñas WHERE producto_id = p.id) AS promedio FROM productos p WHERE 1=1";

$params = [];

if ($filtro_nombre) {
    $sql .= " AND p.nombre LIKE ?";
    $params[] = "%$filtro_nombre%";
}

if ($filtro_categoria) {
    $sql .= " AND p.categoria = ?";
    $params[] = $filtro_categoria;
}

if ($filtro_precio_min) {
    $sql .= " AND p.precio >= ?";
    $params[] = $filtro_precio_min;
}

if ($filtro_precio_max) {
    $sql .= " AND p.precio <= ?";
    $params[] = $filtro_precio_max;
}

if ($filtro_popularidad) {
    $sql .= " HAVING promedio IS NOT NULL ORDER BY promedio DESC";
} else {
    $sql .= " ORDER BY p.nombre";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
    <h1>Bienvenido, <?php echo $_SESSION['nombre']; ?> (Cliente)</h1>
    <div>
        <a href="index.php">Inicio</a>
        <a href="perfil.php">Perfil</a>
        <a href="faq.php">FAQ</a>
        <a href="chat.php">Chat</a>
        <a href="carrito.php">Carrito</a>
        <a href="logout.php">Cerrar sesión</a>
    </div>
</header>

    <div class="container">
        <h2>Productos en venta</h2>

        <!-- Filtros -->
        <form method="GET">
            <div class="form-group">
                <input type="text" name="buscar" placeholder="Buscar producto..." value="<?php echo htmlspecialchars($filtro_nombre); ?>">
            </div>
            <div class="form-group">
                <select name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php
                    $stmt = $pdo->query("SELECT DISTINCT categoria FROM productos");
                    while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = $filtro_categoria === $cat['categoria'] ? 'selected' : '';
                        echo "<option value='{$cat['categoria']}' $selected>{$cat['categoria']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <input type="number" name="precio_min" placeholder="Precio mínimo" value="<?php echo $filtro_precio_min; ?>">
            </div>
            <div class="form-group">
                <input type="number" name="precio_max" placeholder="Precio máximo" value="<?php echo $filtro_precio_max; ?>">
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="popularidad" value="1" <?php echo $filtro_popularidad ? 'checked' : ''; ?>> Popularidad</label>
            </div>
            <button type="submit">Filtrar</button>
            <button type="button" onclick="location.href='index.php'" class="btn-reset">Restablecer filtros</button>
        </form>

        <div class="product-grid">
            <?php foreach ($productos as $p): ?>
                <div class="product-card">
                    <img src="imagenes/<?php echo $p['imagen']; ?>" alt="<?php echo htmlspecialchars($p['nombre']); ?>" style="width:100%; height:150px; object-fit:cover; border-radius:4px;">
                    <h3><?php echo htmlspecialchars($p['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($p['descripcion']); ?></p>
                    <p><strong>$<?php echo $p['precio']; ?></strong></p>
                    <p>Stock: <?php echo $p['stock']; ?></p>

                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="producto_id" value="<?php echo $p['id']; ?>">
                        <button type="submit" name="agregar">Agregar al carrito</button>
                    </form>

                    <!-- Mostrar reseñas -->
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM reseñas WHERE producto_id = ?");
                    $stmt->execute([$p['id']]);
                    $reseñas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div style="margin-top: 10px;">
                        <?php foreach ($reseñas as $r): ?>
                            <p><strong><?php echo str_repeat('⭐', $r['calificacion']); ?></strong> - <?php echo htmlspecialchars($r['comentario']); ?></p>
                        <?php endforeach; ?>
                    </div>

                    <!-- Formulario para dejar reseña -->
                    <div class="reseña-form">
                        <form method="POST" class="reseña-form-container">
                            <input type="hidden" name="producto_id" value="<?php echo $p['id']; ?>">
                            <div class="form-group reseña-textarea">
                                <textarea name="comentario" placeholder="Escribe tu comentario..." required></textarea>
                            </div>
                            <div class="form-group reseña-select">
                                <select name="calificacion" required>
                                    <option value="">Calificación</option>
                                    <option value="1">1 ⭐</option>
                                    <option value="2">2 ⭐</option>
                                    <option value="3">3 ⭐</option>
                                    <option value="4">4 ⭐</option>
                                    <option value="5">5 ⭐</option>
                                </select>
                            </div>
                            <button type="submit" name="comentar" class="btn btn-small">Enviar reseña</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>

<?php if (isset($_SESSION['mensaje_compra'])): ?>
    <script>
        Swal.fire({
            title: '¡Compra confirmada!',
            text: '<?php echo $_SESSION['mensaje_compra']; ?>',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    </script>
    <?php unset($_SESSION['mensaje_compra']); ?>
<?php endif; ?>