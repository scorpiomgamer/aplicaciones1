<?php
declare(strict_types=1);

/**
 * Busca el perfil en `usuarios` por email de la cuenta; si no existe, crea uno mínimo
 * (por ejemplo administrador con sesión pero sin fila en usuarios).
 */
function checkout_ensure_usuario(PDO $pdo, string $email): ?array
{
    $email = strtolower(trim($email));
    if ($email === '') {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id_usuario, direccion FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return [
            'id_usuario' => (int)$row['id_usuario'],
            'direccion' => (string)($row['direccion'] ?? ''),
        ];
    }

    $idTipo = (int)$pdo->query('SELECT id_tipo_documento FROM tipos_documento WHERE activo = 1 ORDER BY id_tipo_documento ASC LIMIT 1')->fetchColumn();
    if ($idTipo < 1) {
        return null;
    }

    $numDoc = 'WEB-' . bin2hex(random_bytes(5));
    $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    $base = [
        ':nombres' => 'Cliente',
        ':apellidos' => 'Panthera',
        ':edad' => 18,
        ':direccion' => 'Por definir',
        ':email' => $email,
        ':telefono' => '0000000000',
        ':id_tipo_documento' => $idTipo,
        ':numero_documento' => $numDoc,
        ':informacion_adicional' => 'Perfil generado al confirmar pedido.',
    ];

    $attempts = [
        "INSERT INTO usuarios (nombres, apellidos, edad, direccion, email, telefono, id_tipo_documento, numero_documento, informacion_adicional, contraseña, activo)
         VALUES (:nombres, :apellidos, :edad, :direccion, :email, :telefono, :id_tipo_documento, :numero_documento, :informacion_adicional, :contraseña, 1)",
        "INSERT INTO usuarios (nombres, apellidos, edad, direccion, email, telefono, id_tipo_documento, numero_documento, informacion_adicional, activo)
         VALUES (:nombres, :apellidos, :edad, :direccion, :email, :telefono, :id_tipo_documento, :numero_documento, :informacion_adicional, 1)",
    ];

    $paramsWithPwd = $base + [':contraseña' => $hash];

    foreach ($attempts as $i => $sql) {
        try {
            $ins = $pdo->prepare($sql);
            $ins->execute($i === 0 ? $paramsWithPwd : $base);
            $id = (int)$pdo->lastInsertId();

            return ['id_usuario' => $id, 'direccion' => 'Por definir'];
        } catch (Throwable $e) {
            continue;
        }
    }

    // Email duplicado u otra carrera: volver a leer
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return [
            'id_usuario' => (int)$row['id_usuario'],
            'direccion' => (string)($row['direccion'] ?? ''),
        ];
    }

    return null;
}
