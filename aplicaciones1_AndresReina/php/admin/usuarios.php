<?php
require_once '../config/db.php';

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Procesar formulario de agregar/editar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $rol = $_POST['rol'] ?? 'usuario';
    $usuario_id = intval($_POST['usuario_id'] ?? 0);

    if (empty($nombre) || empty($email)) {
        $error = 'El nombre y email son requeridos';
    } else {
        try {
            if ($usuario_id > 0) {
                // Actualizar usuario
                if (!empty($password)) {
                    // Encriptar contraseña si se proporciona
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, password = ?, telefono = ?, rol = ? WHERE id = ?");
                    $stmt->execute([$nombre, $apellido, $email, $password_hash, $telefono, $rol, $usuario_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, telefono = ?, rol = ? WHERE id = ?");
                    $stmt->execute([$nombre, $apellido, $email, $telefono, $rol, $usuario_id]);
                }
                $success = 'Usuario actualizado exitosamente';
            } else {
                // Crear usuario nuevo
                if (empty($password)) {
                    $error = 'La contraseña es requerida para nuevos usuarios';
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, telefono, rol, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
                    $stmt->execute([$nombre, $apellido, $email, $password_hash, $telefono, $rol]);
                    $success = 'Usuario creado exitosamente';
                }
            }
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $error = 'El email ya está en uso';
            } else {
                $error = 'Error al guardar el usuario: ' . $e->getMessage();
            }
        }
    }
}
// Eliminar usuario (eliminar completamente)
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $eliminar_id = intval($_GET['eliminar']);
    
    // No permitir eliminarse a sí mismo
    if ($eliminar_id == $_SESSION['usuario_id']) {
        $error = 'No puedes eliminar tu propia cuenta';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$eliminar_id]);
            $success = 'Usuario eliminado permanentemente';
        } catch (PDOException $e) {
            $error = 'Error al eliminar el usuario';
        }
    }
}
// Activar usuario
if (isset($_GET['activar']) && is_numeric($_GET['activar'])) {
    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET activo = 1 WHERE id = ?");
        $stmt->execute([$_GET['activar']]);
        $success = 'Usuario activado exitosamente';
    } catch (PDOException $e) {
        $error = 'Error al activar el usuario';
    }
}

// Obtener usuario para editar
$usuario_editar = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $usuario_editar = $stmt->fetch();
}

// Obtener todos los usuarios
$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY rol DESC, nombre ASC");
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios-Andres Reina</title>
    <link rel="icon" href="../../frontend/views/Carpintin-Don-Gusto/img/logo.jpg" type="image/jpg">
    <link rel="stylesheet" href="../../frontend/css/producto_estile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
.admin-section{
background:linear-gradient(135deg,#FFF8DC,#fff4e0);
border:2px solid #D2B48C;
border-radius:12px;
padding:35px;
margin:30px auto;
max-width:1400px;
box-shadow:0 8px 18px rgba(0,0,0,0.08);
}

.admin-section h2{
color:#bfeb6f;
font-family:Arial,sans-serif;
border-bottom:3px solid #D2B48C;
padding-bottom:12px;
margin-bottom:25px;
}

/* FORMULARIO */

.form-group label{
color:#bfeb6f;
font-weight:bold;
font-size:15px;
}

.form-control{
border:2px solid #D2B48C;
background:#FAF0E6;
border-radius:8px;
padding:10px;
}

.form-control:focus{
border-color:#bfeb6f;
box-shadow:0 0 6px rgba(139,69,19,0.25);
}

/* BOTONES */

.btn-add{
background:#F4A460;
color:#2f4f2f;
border:2px solid #ce8856;
padding:12px 28px;
font-size:16px;
border-radius:8px;
transition:0.3s;
}

.btn-add:hover{
background:#78d7e4;
color:white;
}

.btn-edit{
background:#F4A460;
color:#2f4f2f;
border:1px solid #ce8856;
padding:7px 14px;
border-radius:6px;
text-decoration:none;
}

.btn-edit:hover{
background:#78d7e4;
color:white;
}

.btn-delete{
background:#c0392b;
color:white;
padding:7px 14px;
border-radius:6px;
text-decoration:none;
}

.btn-delete:hover{
background:#a93226;
}

.btn-activate{
background:#27ae60;
color:white;
padding:7px 14px;
border-radius:6px;
text-decoration:none;
}

.btn-activate:hover{
background:#1e8449;
}

/* TABLA */

.table-container{
overflow-x:auto;
background:white;
border-radius:10px;
border:2px solid #8cd292;
}

.user-table{
width:100%;
border-collapse:collapse;
}

.user-table thead{
background:#b7f791;
color:white;
}

.user-table th{
padding:15px;
border-bottom:3px solid #D2B48C;
}

.user-table td{
padding:12px 15px;
border-bottom:1px solid #D2B48C;
}

.user-table tbody tr:hover{
background:#FAF0E6;
}

.user-table .nombre{
font-weight:bold;
color:#71b4f1;
}

.user-table .email{
color:#555;
}

.user-table .telefono{
color:#555;
}

/* BADGES */

.rol-badge{
padding:5px 12px;
border-radius:15px;
font-size:12px;
font-weight:bold;
}

.rol-admin{
background:#9b59b6;
color:white;
}

.rol-usuario{
background:#3498db;
color:white;
}

.activo-badge{
background:#27ae60;
color:white;
padding:5px 12px;
border-radius:15px;
font-size:12px;
}

.inactivo-badge{
background:#e74c3c;
color:white;
padding:5px 12px;
border-radius:15px;
font-size:12px;
}

/* ACCIONES */

.actions{
white-space:nowrap;
}

/* MENSAJES */

.error{
color:#c0392b;
background:#fadbd8;
padding:12px;
border-radius:6px;
margin-bottom:20px;
border-left:5px solid #c0392b;
}

.success{
color:#1e8449;
background:#d5f5e3;
padding:12px;
border-radius:6px;
margin-bottom:20px;
border-left:5px solid #1e8449;
}

.empty-state{
text-align:center;
padding:40px;
color:#666;
}
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="../../frontend/views/Carpintin-Don-Gusto/index.html">
                    <img class="foto" src="../../frontend/views/Carpintin-Don-Gusto/img/logo.jpg" alt="Logotipo" style="height: 50px;">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mynavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-sm-0">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Productos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios.php">Usuarios</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <span class="text-white me-3">Admin: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                        <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <br><br>
    
    <div class="container">
        <div class="admin-section">
            <h2><?php echo $usuario_editar ? ' Editar Usuario' : ' Agregar Nuevo Usuario'; ?></h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
<form method="POST" action="">
<input type="hidden" name="usuario_id" value="<?php echo $usuario_editar['id'] ?? 0; ?>">

<div class="row">

<div class="col-md-6 mb-3">
<label for="nombre" class="form-label">Nombre</label>
<input type="text"
class="form-control"
id="nombre"
name="nombre"
required
value="<?php echo htmlspecialchars($usuario_editar['nombre'] ?? ''); ?>">
</div>

<div class="col-md-6 mb-3">
<label for="apellido" class="form-label">Apellido</label>
<input type="text"
class="form-control"
id="apellido"
name="apellido"
value="<?php echo htmlspecialchars($usuario_editar['apellido'] ?? ''); ?>">
</div>

</div>

<div class="row">

<div class="col-md-6 mb-3">
<label for="email" class="form-label">Email</label>
<input type="email"
class="form-control"
id="email"
name="email"
required
value="<?php echo htmlspecialchars($usuario_editar['email'] ?? ''); ?>">
</div>

<div class="col-md-6 mb-3">
<label for="telefono" class="form-label">Teléfono</label>
<input type="text"
class="form-control"
id="telefono"
name="telefono"
value="<?php echo htmlspecialchars($usuario_editar['telefono'] ?? ''); ?>">
</div>

</div>

<div class="row">

<div class="col-md-6 mb-3">
<label for="rol" class="form-label">Rol</label>
<select class="form-control" id="rol" name="rol">
<option value="usuario"
<?php echo (isset($usuario_editar['rol']) && $usuario_editar['rol'] === 'usuario') ? 'selected' : ''; ?>>
Usuario
</option>

<option value="admin"
<?php echo (isset($usuario_editar['rol']) && $usuario_editar['rol'] === 'admin') ? 'selected' : ''; ?>>
Administrador
</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label for="password" class="form-label">
<?php echo $usuario_editar ? 'Nueva Contraseña (dejar vacío para mantener)' : 'Contraseña'; ?>
</label>

<input type="password"
class="form-control"
id="password"
name="password"
<?php echo $usuario_editar ? '' : 'required'; ?>>
</div>

</div>

<div class="text-center mt-3">

<button type="submit" class="btn btn-add">
<?php echo $usuario_editar ? 'Actualizar Usuario' : 'Crear Usuario'; ?>
</button>

<?php if ($usuario_editar): ?>
<a href="usuarios.php" class="btn btn-secondary ms-2">Cancelar</a>
<?php endif; ?>

</div>

</form>
        </div>
        
        <div class="admin-section">
            <h2> Usuarios del Sistema</h2>
            <div class="table-container">
                <table class="user-table" id="usuariosTable">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($usuarios) === 0): ?>
                            <tr>
                                <td colspan="6" class="empty-state">No hay usuarios aún</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td class="nombre"><?php echo htmlspecialchars($user['nombre'] . ' ' . ($user['apellido'] ?? '')); ?></td>
                                    <td class="email"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="telefono"><?php echo htmlspecialchars($user['telefono'] ?? 'No especificado'); ?></td>
                                    <td>
                                        <span class="rol-badge <?php echo $user['rol'] === 'admin' ? 'rol-admin' : 'rol-usuario'; ?>">
                                            <?php echo $user['rol'] === 'admin' ? 'Admin' : 'Usuario'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['activo']): ?>
                                            <span class="activo-badge">Activo</span>
                                        <?php else: ?>
                                            <span class="inactivo-badge">eliminar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <a href="?editar=<?php echo $user['id']; ?>" class="btn btn-edit">Editar</a>
                                        <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                                            <?php if ($user['activo']): ?>
                                                <a href="?eliminar=<?php echo $user['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</a>
                                            <?php else: ?>
                                                <a href="?activar=<?php echo $user['id']; ?>" class="btn btn-activate">Activar</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

