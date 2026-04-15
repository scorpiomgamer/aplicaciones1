<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

require_admin(); // solo admin puede gestionar usuarios

// Token simple para evitar envíos accidentales (CSRF básico)
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = (string)$_SESSION['csrf'];

$mensajeOk = null;
$mensajeError = null;

// Acción: eliminar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $mensajeError = 'Token inválido, recarga la página.';
    } else {
        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        if ($idUsuario > 0) {
            try {
                $stmt = db()->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
                $stmt->execute([':id' => $idUsuario]);
                $mensajeOk = 'Usuario eliminado correctamente.';
            } catch (Throwable $e) {
                $mensajeError = 'No se pudo eliminar el usuario.';
            }
        } else {
            $mensajeError = 'ID de usuario inválido.';
        }
    }
}

// Acción: agregar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        $mensajeError = 'Token inválido, recarga la página.';
    } else {
        // Datos mínimos para insertar en la tabla usuarios (según pantera.sql)
        $nombres   = trim((string)($_POST['nombres'] ?? ''));
        $apellidos = trim((string)($_POST['apellidos'] ?? ''));
        $edad      = (int)($_POST['edad'] ?? 0);
        $direccion = trim((string)($_POST['direccion'] ?? ''));
        $email     = normalize_login_email((string)($_POST['email'] ?? ''));
        $telefono  = trim((string)($_POST['telefono'] ?? ''));
        $idTipoDoc = (int)($_POST['id_tipo_documento'] ?? 0);
        $contraseña = trim((string)($_POST['contraseña'] ?? ''));
        $numeroDoc = trim((string)($_POST['numero_documento'] ?? ''));
        $infoAdic  = trim((string)($_POST['informacion_adicional'] ?? ''));

        if ($nombres && $apellidos && $edad && $direccion && $email && $telefono && $idTipoDoc && $numeroDoc) {
            try {
                $stmt = db()->prepare(
                    "INSERT INTO usuarios
                        (nombres, apellidos, edad, direccion, email, telefono, id_tipo_documento, numero_documento, informacion_adicional)
                     VALUES
                        (:nombres, :apellidos, :edad, :direccion, :email, :telefono, :id_tipo_documento, :numero_documento, :informacion_adicional)"
                );
                $stmt->execute([
                    ':nombres' => $nombres,
                    ':apellidos' => $apellidos,
                    ':edad' => $edad,
                    ':direccion' => $direccion,
                    ':email' => $email,
                    ':telefono' => $telefono,
                    ':id_tipo_documento' => $idTipoDoc,
                    ':numero_documento' => $numeroDoc,
                    ':informacion_adicional' => $infoAdic,
                ]);
                $mensajeOk = 'Usuario creado correctamente.';
            } catch (Throwable $e) {
                $mensajeError = 'No se pudo crear el usuario (revisa si el email ya existe).';
            }
        } else {
            $mensajeError = 'Completa todos los campos requeridos para crear el usuario.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    if (!hash_equals((string)$csrf, (string)($_POST['csrf'] ?? ''))) {
        $mensajeError = 'Token inválido, recarga la página.';
    } else {
        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $nombres   = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $edad      = (int)($_POST['edad'] ?? 0);
        $direccion = trim($_POST['direccion'] ?? '');
        $email     = normalize_login_email((string)($_POST['email'] ?? ''));
        $telefono  = trim($_POST['telefono'] ?? '');
        $idTipoDoc = (int)($_POST['id_tipo_documento'] ?? 0);
        $numeroDoc = trim((string)($_POST['numero_documento'] ?? ''));
        $infoAdic  = trim((string)($_POST['informacion_adicional'] ?? ''));
        $activo    = isset($_POST['activo']) ? 1 : 0;

        if ($nombres !== '' && $apellidos !== '' && $edad > 0 && $direccion !== '' && $email !== '' && $telefono !== '' && $idTipoDoc > 0 && $numeroDoc !== '' && $idUsuario > 0) {
            try {
                $pdo = db();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $pdo->prepare(
                    "UPDATE usuarios
                     SET nombres = :nombres,
                      apellidos = :apellidos,
                      edad = :edad,
                      direccion = :direccion,
                      email = :email,
                      telefono = :telefono,
                      id_tipo_documento = :id_tipo_documento,
                      numero_documento = :numero_documento,
                      informacion_adicional = :informacion_adicional,
                      activo = :activo
                     WHERE id_usuario = :id_usuario"
                );
                $stmt->execute([
                    ':nombres' => $nombres,
                    ':apellidos' => $apellidos,
                    ':edad' => $edad,
                    ':direccion' => $direccion,
                    ':email' => $email,
                    ':telefono' => $telefono,
                    ':id_tipo_documento' => $idTipoDoc,
                    ':numero_documento' => $numeroDoc,
                    ':informacion_adicional' => $infoAdic,
                    ':activo' => $activo,
                    ':id_usuario' => $idUsuario,
                ]);
                $mensajeOk = 'Usuario editado correctamente.';
            } catch (Throwable $e) {
                error_log('DB error: '.$e->getMessage());
                $mensajeError = 'No se pudo editar el usuario.';
            }
        } else {
            $mensajeError = 'Completa todos los campos requeridos para editar el usuario.';
        }
    }
}

// Listados para la vista
try {
    $tiposDocumento = db()->query("SELECT id_tipo_documento, nombre FROM tipos_documento WHERE activo = 1 ORDER BY nombre")->fetchAll();
} catch (Throwable $e) {
    $tiposDocumento = [];
}

try {
    $usuarios = db()->query(
        "SELECT u.id_usuario, u.nombres, u.apellidos, u.email, u.telefono, u.edad, u.direccion, u.numero_documento,
                u.id_tipo_documento, u.informacion_adicional, u.activo,
                td.nombre AS tipo_documento
         FROM usuarios u
         JOIN tipos_documento td ON td.id_tipo_documento = u.id_tipo_documento
         ORDER BY u.id_usuario DESC"
    )->fetchAll();
} catch (Throwable $e) {
    $usuarios = [];
    $mensajeError = $mensajeError ?? 'No se pudieron cargar los usuarios.';
}

$title = 'Gestión de usuarios';
$cssFile = 'frontend/css/formulariocss.css';
require __DIR__ . '/includes/header.php';
?>

<main class="container py-4">
  <h1 class="mb-3">Gestión de usuarios</h1>
  <p class="text-muted">Agregar, editar y eliminar usuarios registrados (solo administrador).</p>

  <?php if ($mensajeOk): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mensajeOk) ?></div>
  <?php endif; ?>

  <?php if ($mensajeError): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div>
  <?php endif; ?>

  <div class="card p-4 shadow-sm mb-4">
    <h2 class="h4 mb-3">Añadir usuario</h2>
    <form method="POST" class="row g-3">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

      <div class="col-md-6">
        <label class="form-label">Nombres</label>
        <input class="form-control" name="nombres" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Apellidos</label>
        <input class="form-control" name="apellidos" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Edad</label>
        <input class="form-control" name="edad" type="number" min="5" max="100" required>
      </div>
      <div class="col-md-9">
        <label class="form-label">Dirección</label>
        <input class="form-control" name="direccion" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" type="email" required>
        </div>

      <div class="col-md-6">
        <label class="form-label">Teléfono</label>
        <input class="form-control" name="telefono" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Tipo de documento</label>
        <select class="form-select" name="id_tipo_documento" required>
          <option value="">Seleccione...</option>
          <?php foreach ($tiposDocumento as $td): ?>
            <option value="<?= (int)$td['id_tipo_documento'] ?>"><?= htmlspecialchars((string)$td['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Número de documento</label>
        <input class="form-control" name="numero_documento" required>
      </div>
      <div class="col-12">
        <label class="form-label">Información adicional</label>
        <textarea class="form-control" name="informacion_adicional" rows="2"></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-primary" type="submit">Guardar usuario</button>
      </div>
    </form>
  </div>

  <div class="card p-4 shadow-sm">
    <h2 class="h4 mb-3">Usuarios registrados</h2>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Documento</th>
            <th>Teléfono</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$usuarios): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No hay usuarios todavía.</td></tr>
          <?php endif; ?>

          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td><?= (int)$u['id_usuario'] ?></td>
              <td><?= htmlspecialchars((string)$u['nombres'] . ' ' . (string)$u['apellidos']) ?></td>
              <td><?= htmlspecialchars((string)$u['email']) ?></td>
              <td><?= htmlspecialchars((string)$u['tipo_documento'] . ' - ' . (string)$u['numero_documento']) ?></td>
              <td><?= htmlspecialchars((string)$u['telefono']) ?></td>
              <td class="text-end">
                <div class="d-inline-flex flex-wrap gap-2 justify-content-end align-items-center">
                  <form method="POST" class="d-inline m-0" onsubmit="return confirm('¿Eliminar este usuario?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                    <button class="btn btn-sm btn-outline-danger btn-user-action" type="submit" title="Eliminar usuario" aria-label="Eliminar usuario">🗑️</button>
                  </form>
                  <button class="btn btn-sm btn-primary btn-user-action" type="button" data-bs-toggle="modal" data-bs-target="#modalEditUser-<?= (int)$u['id_usuario'] ?>" title="Editar usuario" aria-label="Editar usuario">✏️</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php foreach ($usuarios as $u): ?>
      <div class="modal fade" id="modalEditUser-<?= (int)$u['id_usuario'] ?>" tabindex="-1" aria-labelledby="modalEditUserLabel-<?= (int)$u['id_usuario'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h2 class="modal-title h5" id="modalEditUserLabel-<?= (int)$u['id_usuario'] ?>">Editar usuario #<?= (int)$u['id_usuario'] ?></h2>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <form method="POST" class="row g-3" id="formEditUser-<?= (int)$u['id_usuario'] ?>">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">

                <div class="col-md-6">
                  <label class="form-label">Nombres</label>
                  <input class="form-control" name="nombres" value="<?= htmlspecialchars((string)$u['nombres']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Apellidos</label>
                  <input class="form-control" name="apellidos" value="<?= htmlspecialchars((string)$u['apellidos']) ?>" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Edad</label>
                  <input class="form-control" name="edad" type="number" min="5" max="100" value="<?= (int)$u['edad'] ?>" required>
                </div>
                <div class="col-md-9">
                  <label class="form-label">Dirección</label>
                  <input class="form-control" name="direccion" value="<?= htmlspecialchars((string)$u['direccion']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input class="form-control" name="email" type="email" value="<?= htmlspecialchars((string)$u['email']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Teléfono</label>
                  <input class="form-control" name="telefono" value="<?= htmlspecialchars((string)$u['telefono']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tipo de documento</label>
                  <select class="form-select" name="id_tipo_documento" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($tiposDocumento as $td): ?>
                      <option value="<?= (int)$td['id_tipo_documento'] ?>" <?= (int)$u['id_tipo_documento'] === (int)$td['id_tipo_documento'] ? 'selected' : '' ?>><?= htmlspecialchars((string)$td['nombre']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Número de documento</label>
                  <input class="form-control" name="numero_documento" value="<?= htmlspecialchars((string)($u['numero_documento'] ?? '')) ?>" required>
                </div>
                <div class="col-12">
                  <label class="form-label">Información adicional</label>
                  <textarea class="form-control" name="informacion_adicional" rows="2"><?= htmlspecialchars((string)($u['informacion_adicional'] ?? '')) ?></textarea>
                </div>
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="activo" id="activo_<?= (int)$u['id_usuario'] ?>" value="1" <?= !empty($u['activo']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="activo_<?= (int)$u['id_usuario'] ?>">Usuario activo</label>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-dark" form="formEditUser-<?= (int)$u['id_usuario'] ?>">Guardar cambios</button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
