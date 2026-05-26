<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Eliminar usuario
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];

    // No permitir eliminar admin
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $rol = $stmt->fetchColumn();

    if ($rol !== 'admin') {
        // Eliminar reseñas asociadas al usuario
        $stmt = $pdo->prepare("DELETE FROM reseñas WHERE usuario_id = ?");
        $stmt->execute([$id]);

        // Eliminar mensajes del chat asociados al usuario
        $stmt = $pdo->prepare("DELETE FROM chat WHERE usuario_id = ?");
        $stmt->execute([$id]);

        // Eliminar ventas asociadas al usuario
        $stmt = $pdo->prepare("DELETE FROM ventas WHERE usuario_id = ?");
        $stmt->execute([$id]);

        // Eliminar usuario
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
    }
}
// Cambiar rol
if (isset($_POST['cambiar_rol'])) {
    $id = $_POST['usuario_id'];
    $nuevo_rol = $_POST['rol'];

    $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->execute([$nuevo_rol, $id]);
}

$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY creado DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Panel Admin</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header>
        <h1>Panel de Usuarios</h1>
        <a href="admin.php">Volver al panel</a>
        <a href="logout.php">Cerrar sesión</a>
    </header>

    <div class="container">
        <h2>Lista de Usuarios</h2>

        <table border="1" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Fecha de registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="usuario_id" value="<?php echo $u['id']; ?>">
                                <select name="rol" onchange="this.form.submit()">
                                    <option value="cliente" <?php echo $u['rol'] === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                                    <option value="admin" <?php echo $u['rol'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <input type="hidden" name="cambiar_rol">
                            </form>
                        </td>
                        <td><?php echo $u['creado']; ?></td>
                        <td>
                            <?php if ($u['rol'] !== 'admin'): ?>
                                <a href="javascript:void(0);" class="btn-delete" onclick="eliminarUsuario(<?php echo $u['id']; ?>)">Eliminar</a>
                            <?php else: ?>
                                ---
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>
<script>
function eliminarUsuario(id) {
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