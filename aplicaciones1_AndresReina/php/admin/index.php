<?php
require_once '../config/db.php';

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Procesar formulario de agregar/editar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagen = trim($_POST['imagen'] ?? '');
    $categoria_id = intval($_POST['categoria'] ?? 1);
    $producto_id = intval($_POST['producto_id'] ?? 0);

    if (empty($nombre) || $precio <= 0) {
        $error = 'El nombre y precio son requeridos';
    } else {
        try {
            if ($producto_id > 0) {
                // Actualizar producto
                $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, categoria_id = ? WHERE id = ?");
                $stmt->execute([$nombre, $descripcion, $precio, $imagen, $categoria_id, $producto_id]);
                $success = 'Producto actualizado exitosamente';
            } else {
                // Crear producto
                $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, imagen, categoria_id, activo, destacado) VALUES (?, ?, ?, ?, ?, 1, 0)");
                $stmt->execute([$nombre, $descripcion, $precio, $imagen, $categoria_id]);
                $success = 'Producto creado exitosamente';
            }
        } catch (PDOException $e) {
            $error = 'Error al guardar el producto: ' . $e->getMessage();
        }
    }
}

// Eliminar producto
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$_GET['eliminar']]);
        $success = 'Producto eliminado exitosamente';
    } catch (PDOException $e) {
        $error = 'Error al eliminar el producto';
    }
}

// Obtener producto para editar
$producto_editar = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $producto_editar = $stmt->fetch();
}

// Obtener categorías
$stmt = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre");
$categorias = $stmt->fetchAll();

// Obtener productos
$stmt = $pdo->query("
    SELECT p.*, c.nombre as categoria 
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    WHERE p.activo = 1 
    ORDER BY p.id DESC
");
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administracion</title>
    <link rel="icono" href="../../../frontend/views/Carpintin-Don-Gusto/img/logo.jpg" type="image/jpg">
    <link rel="stylesheet" href="../../../frontend/css/producto_estile.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
.admin-section {
    background: linear-gradient(135deg,#a5f8b7,#eafbea);
    border: 2px solid #50aa55;
    border-radius: 12px;
    padding: 35px;
    margin: 30px auto;
    max-width: 1400px;
    box-shadow: 0 8px 18px rgba(0,0,0,0.08);
}

.admin-section h2 {
    color: #2f8f2f;
    font-family: Arial, sans-serif;
    border-bottom: 3px solid #eb9525;
    padding-bottom: 12px;
    margin-bottom: 25px;
}

/* FORMULARIO */

form .row{
    margin-bottom:10px;
}

label{
    color:#8b2e2e;
    font-weight:bold;
}

.form-control{
    border:2px solid #3ef7ed;
    background:#FAF0E6;
    border-radius:8px;
    padding:10px;
}

.form-control:focus{
    border-color:#6cfc40;
    box-shadow:0 0 6px rgba(218,215,57,0.35);
}

/* BOTONES */

.btn-add{
    background:#60f485;
    color:#004d40;
    border:2px solid #e49964;
    padding:12px 30px;
    font-size:17px;
    border-radius:8px;
    display:block;
    margin:auto;
    transition:0.3s;
}

.btn-add:hover{
    background:#CD853F;
    color:white;
}

.btn-edit{
    background:#60f485;
    color:#004d40;
    border:1px solid #e49964;
    padding:7px 14px;
    border-radius:6px;
    text-decoration:none;
}

.btn-edit:hover{
    background:#c44e4e;
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
    background:#7c2119;
}

/* TABLA */

.table-container{
    overflow-x:auto;
    background:white;
    border-radius:10px;
    border:2px solid #968673;
}

.product-table{
    width:100%;
    border-collapse:collapse;
}

.product-table thead{
    background:#74dadd;
    color:white;
}

.product-table th{
    padding:15px;
    border-bottom:3px solid #6181ec;
}

.product-table td{
    padding:12px 15px;
    border-bottom:1px solid #5a401d;
}

.product-table tbody tr:hover{
    background:#d7f7da;
}

.product-table img{
    width:110px;
    height:85px;
    object-fit:cover;
    border-radius:6px;
    border:2px solid #84d8f1;
}

.nombre{
    font-weight:bold;
    color:#df823f;
}

.descripcion{
    font-size:13px;
    color:#555;
}

.precio{
    font-weight:bold;
    color:#dd9561;
    font-size:16px;
}

.categoria{
    background:#d4a373;
    color:white;
    padding:5px 12px;
    border-radius:15px;
    font-size:12px;
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
                <a class="navbar-brand" href="../../../frontend/views/Carpintin-Don-Gusto/index.html">
                    <img class="foto" src="../../../frontend/views/Carpintin-Don-Gusto/img/logo.jpg" alt="Logotipo" style="height: 50px;">
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
                        <li class="nav-item">
                            <a class="nav-link" href="../../../frontend/views/Carpintin-Don-Gusto/sobre-nosotros.html">Sobre Nosotros</a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center">
                        <span class="text-white me-3">Admin: <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                        <a href="logout.php" class="btn btn-outline-light btn-sm">Eliminar</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <br><br>
    
    <div class="container">
        <div class="admin-section">
            <h2><?php echo $producto_editar ? ' Editar Producto' : ' Agregar Nuevo Producto'; ?></h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
<form method="POST" action="">
<input type="hidden" name="producto_id" value="<?php echo $producto_editar['id'] ?? 0; ?>">

<div class="row">

<div class="col-md-6 mb-3">
<label>URL de Imagen</label>
<input type="text" class="form-control" name="imagen"
placeholder="img/producto.jpg"
value="<?php echo htmlspecialchars($producto_editar['imagen'] ?? ''); ?>">
</div>

<div class="col-md-6 mb-3">
<label>Nombre del Producto</label>
<input type="text" class="form-control" name="nombre"
required
value="<?php echo htmlspecialchars($producto_editar['nombre'] ?? ''); ?>">
</div>

</div>

<div class="row">

<div class="col-md-4 mb-3">
<label>Categoría</label>
<select class="form-control" name="categoria">
<?php foreach ($categorias as $cat): ?>
<option value="<?php echo $cat['id']; ?>"
<?php echo (isset($producto_editar['categoria_id']) && $producto_editar['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
<?php echo htmlspecialchars($cat['nombre']); ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-4 mb-3">
<label>Precio</label>
<input type="number"
class="form-control"
name="precio"
step="0.01"
required
value="<?php echo htmlspecialchars($producto_editar['precio'] ?? ''); ?>">
</div>

</div>

<div class="mb-4">
<label>Descripción</label>
<textarea class="form-control"
name="descripcion"
rows="4"><?php echo htmlspecialchars($producto_editar['descripcion'] ?? ''); ?></textarea>
</div>

<button type="submit" class="btn btn-add">
<?php echo $producto_editar ? 'Actualizar Producto' : 'Agregar Producto'; ?>
</button>

<?php if ($producto_editar): ?>
<a href="index.php" class="btn btn-secondary mt-3">Cancelar</a>
<?php endif; ?>

</form>
            </form>
        </div>
        
        <div class="admin-section">
            <h2> Productos del Sistema</h2>
            <div class="table-container">
                <table class="product-table" id="productosTable">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($productos) === 0): ?>
                            <tr>
                                <td colspan="6" class="empty-state">No hay productos aún</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos as $prod): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $imagen = $prod['imagen'] ?? '';
                                        if ($imagen && !str_starts_with($imagen, 'http') && !str_starts_with($imagen, '/')) {
                                            $imagen = '/carpinteria_proyecto-0.0.1/frontend/views/Carpintin-Don-Gusto/' . $imagen;
                                        }
                                        ?>
                                        <img src="<?php echo $imagen ? htmlspecialchars('/carpinteria_proyecto-0.0.1/frontend/views/Carpintin-Don-Gusto/' . $imagen) : '/carpinteria_proyecto-0.0.1/frontend/views/Carpintin-Don-Gusto/img/logo.jpg'; ?>" alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
                                    </td>
                                    <td class="nombre"><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                    <td class="descripcion" title="<?php echo htmlspecialchars($prod['descripcion'] ?? ''); ?>"><?php echo htmlspecialchars($prod['descripcion'] ?? 'Sin descripción'); ?></td>
                                    <td class="precio">$<?php echo number_format($prod['precio'], 2); ?></td>
                                    <td><span class="categoria"><?php echo htmlspecialchars($prod['categoria'] ?? 'General'); ?></span></td>
                                    <td class="actions">
                                        <a href="?editar=<?php echo $prod['id']; ?>" class="btn btn-edit">Editar</a>
                                        <a href="?eliminar=<?php echo $prod['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');">Eliminar</a>
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

