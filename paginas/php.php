<?php
// Configuración de la base de datos
$host = 'localhost';
$db   = 'nombre_de_tu_base_de_datos';
$user = 'tu_usuario';
$pass = 'tu_contraseña';

$conn = new mysqli($host, $user, $pass, $db);

// Verifica si hay errores de conexión
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validar que no estén vacíos
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = 'Todos los campos son obligatorios.';
    }

    // Validar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        $errors[] = 'Las contraseñas no coinciden.';
    }

    // Validar si el correo ya existe
    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = 'El correo ya está registrado.';
        }
        $stmt->close();
    }

    // Si no hay errores, insertar el usuario
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $username, $email, $hashed_password);

        if ($stmt->execute()) {
            echo 'Registro exitoso. Ahora puedes iniciar sesión.';
        } else {
            echo 'Error en el registro: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Cerrar la conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
</head>
<body>
    <h2>Registro de Usuario</h2>
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="register.php" method="post">
        <label for="username">Nombre de Usuario:</label>
        <input type="text" id="username" name="username" required><br>

        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required><br>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required><br>

        <label for="confirm_password">Confirmar Contraseña:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br>

        <button type="submit">Registrar</button>
    </form>
</body>
</html>
