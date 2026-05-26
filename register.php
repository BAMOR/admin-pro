<!DOCTYPE html>
<html lang="es">
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Mi Tortuga</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="register-container">
        <h2>Crear Cuenta</h2>
        <?php if (isset($_SESSION['mensaje'])): ?>
            <p style="color: green; font-weight: bold;">✅ <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red; font-weight: bold;">❌ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <form method="POST" action="register_process.php">
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Registrarse</button>
        </form>
        <a href="login.php">¿Ya tienes cuenta? Inicia sesión aquí</a>
    </div>
</body>
</html>