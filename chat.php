<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Enviar mensaje
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat en Vivo - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
  
</head>
<body>
    <header>
        <h1>Chat en Vivo</h1>
        <div>
            <a href="index.php">Tienda</a>
            <a href="perfil.php">Perfil</a>
            <a href="faq.php">FAQ</a>
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
                <textarea name="mensaje" placeholder="Escribe tu mensaje..." required></textarea>
            </div>
            <button type="submit">Enviar</button>
        </form>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>