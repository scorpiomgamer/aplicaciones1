<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';

// Pequeña lógica para guardar en la tabla usuarios
$mensajeOk = null;
$mensajeError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres   = trim((string)($_POST['nombres'] ?? ''));
    $apellidos = trim((string)($_POST['apellidos'] ?? ''));
    $edad      = (int)($_POST['edad'] ?? 0);
    $direccion = trim((string)($_POST['direccion'] ?? ''));
    $email     = trim((string)($_POST['email'] ?? ''));
    $telefono  = trim((string)($_POST['telefono'] ?? ''));
    $documentoNombre = trim((string)($_POST['documento'] ?? ''));
    $numeroDocumento = trim((string)($_POST['numero_documento'] ?? ''));
    $infoAdicional   = trim((string)($_POST['info_adicional'] ?? ''));

    if ($nombres && $apellidos && $edad && $direccion && $email && $telefono && $documentoNombre && $numeroDocumento) {
        try {
            $pdo = db();

            // Buscar id_tipo_documento por nombre (coincide con datos del .sql)
            $sqlTipo = "SELECT id_tipo_documento FROM tipos_documento WHERE nombre = :nombre LIMIT 1";
            $stmtTipo = $pdo->prepare($sqlTipo);
            $stmtTipo->execute([':nombre' => $documentoNombre]);
            $filaTipo = $stmtTipo->fetch();

            if (!$filaTipo) {
                $mensajeError = 'Tipo de documento no válido en la base de datos.';
            } else {
                $idTipo = (int)$filaTipo['id_tipo_documento'];

                // Insertar en usuarios
                $sqlUsuario = "INSERT INTO usuarios
                    (nombres, apellidos, edad, direccion, email, telefono, id_tipo_documento, numero_documento, informacion_adicional)
                    VALUES (:nombres, :apellidos, :edad, :direccion, :email, :telefono, :id_tipo_documento, :numero_documento, :informacion_adicional)";

                $stmtUsuario = $pdo->prepare($sqlUsuario);
                $stmtUsuario->execute([
                    ':nombres'              => $nombres,
                    ':apellidos'            => $apellidos,
                    ':edad'                 => $edad,
                    ':direccion'            => $direccion,
                    ':email'                => $email,
                    ':telefono'             => $telefono,
                    ':id_tipo_documento'    => $idTipo,
                    ':numero_documento'     => $numeroDocumento,
                    ':informacion_adicional'=> $infoAdicional,
                ]);

                $mensajeOk = 'Registro guardado correctamente.';
            }
        } catch (Throwable $e) {
            $mensajeError = 'Error al guardar en la base de datos.';
        }
    } else {
        $mensajeError = 'Por favor completa todos los campos requeridos.';
    }
}

$title = 'Registro';
$cssFile = 'frontend/css/formulariocss.css';
require __DIR__ . '/includes/header.php';
?>

<main class="formulario py-4">
  <div class="container">
    <div class="card p-4 shadow-sm mx-auto" style="max-width: 780px;">
      <h2 class="mb-3">Registro</h2>
      <p class="text-muted">Formulario de registro de usuarios (se guarda en la base de datos).</p>

      <?php if ($mensajeOk): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensajeOk) ?></div>
      <?php endif; ?>

      <?php if ($mensajeError): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($mensajeError) ?></div>
      <?php endif; ?>

      <form class="formu" method="post" action="">
        <!-- Datos personales -->
        <div class="row mb-4">
          <div class="col">
            <div class="form-outline">
              <input required type="text" name="nombres" placeholder="Digite nombres" class="form-control" />
              <label class="form-label">Digite sus nombres</label>
            </div>
          </div>
          <div class="col">
            <div class="form-outline">
              <input required type="text" name="apellidos" placeholder="Digite apellidos" class="form-control" />
              <label class="form-label">Digite sus apellidos</label>
            </div>
          </div>
        </div>

        <div class="form-outline mb-4">
          <input type="number" name="edad" class="form-control" min="5" max="100" required placeholder="Digite edad" />
          <label class="form-label">Digite su edad</label>
        </div>

        <div class="form-outline mb-4">
          <input type="text" name="direccion" class="form-control" required placeholder="Dirección" />
          <label class="form-label">Dirección</label>
        </div>

        <div class="form-outline mb-4">
          <input type="email" name="email" class="form-control" required placeholder="Dirección de correo" />
          <label class="form-label">Email</label>
        </div>

        <div data-mdb-input-init class="form-outline mb-4">
          <input type="password" id="form6Example6" class="form-control" required="true" placeholder="Contraseña"/>
          <label class="form-label" for="form6Example6">Contraseña del usuario</label>
        </div>

        <div class="form-outline mb-4">
          <input type="text" name="telefono" class="form-control" required placeholder="Número de teléfono" />
          <label class="form-label">Número de celular</label>
        </div>

        <p class="mb-2">Tipo de documento</p>
        <select name="documento" class="form-select mb-3" required>
          <option value="Cedula ciudadana">Cédula ciudadana</option>
          <option value="Cedula extrangera">Cédula extranjera</option>
          <option value="Targeta de identidad">Tarjeta de identidad</option>
          <option value="Pasaporte">Pasaporte</option>
        </select>

        <div class="form-outline mb-4">
          <input type="text" name="numero_documento" class="form-control" required placeholder="Número de identificación" />
          <label class="form-label">Digite su número de identificación</label>
        </div>

        <div class="form-outline mb-4">
          <textarea class="form-control" name="info_adicional" rows="4"></textarea>
          <label class="form-label">Información adicional sobre el envío o producto</label>
        </div>

        <!-- Botón que envía los datos para guardar -->
        <button type="submit" class="btn btn-primary btn-block mb-2">Registrarse</button>
      </form>
    </div>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

