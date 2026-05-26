<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Enviar mensaje como admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensaje = $_POST['mensaje'];

    $stmt = $pdo->prepare("INSERT INTO chat (usuario_id, mensaje) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $mensaje]);
}

// Obtener mensajes
$stmt = $pdo->query("
    SELECT c.*, u.nombre, u.rol 
    FROM chat c 
    JOIN usuarios u ON c.usuario_id = u.id 
    ORDER BY c.enviado ASC
");
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Panel Admin</title>
    <link rel="stylesheet" href="styles/style.css">
  
</head>
<body>
    <header>
        <h1>Chat - Panel de Administrador</h1>
        <div>
            <a href="admin.php">Panel</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="container">
        <div class="chat-container">
            <?php foreach ($mensajes as $m): ?>
                <div class="mensaje <?php echo $m['rol']; ?>">
                    <strong><?php echo htmlspecialchars($m['nombre']); ?> (<?php echo $m['rol']; ?>):</strong><br>
                    <?php echo htmlspecialchars($m['mensaje']); ?><br>
                    <small><?php echo $m['enviado']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="POST">
            <div class="form-group">
                <textarea name="mensaje" placeholder="Escribe tu respuesta como admin..." required></textarea>
            </div>
            <button type="submit">Enviar como Admin</button>
        </form>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>