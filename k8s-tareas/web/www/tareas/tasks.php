<?php
// Iniciar almacenamiento en búfer para evitar problemas con las cabeceras
ob_start();

// Incluir la conexión a la base de datos
include 'db.php';  // Asegúrate de que tienes la conexión a la base de datos en este archivo

// Configurar el tipo de contenido para JSON
header('Content-Type: application/json');

// Obtener todas las tareas
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tasks);
    exit;
}

// Agregar tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $title = $data['title'];
    $description = $data['description'];
    $priority = $data['priority'];

    // Validación básica
    if (!$title || !$priority) {
        echo json_encode(['message' => 'Datos incompletos']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO tasks (title, description, priority) VALUES (?, ?, ?)");
    $stmt->execute([$title, $description, $priority]);
    echo json_encode(["message" => "Tarea agregada exitosamente"]);
    exit;
}

// Eliminar tarea
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];

    // Eliminar tarea por ID
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["message" => "Tarea eliminada exitosamente"]);
    exit;
}

// Actualizar tarea (editar tarea)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    $id = $data['id'];
    $title = $data['title'];
    $description = $data['description'];
    $priority = $data['priority'];
    $completed = $data['completed'];

    // Validación
    if (!$id || !$title || !$priority) {
        echo json_encode(['message' => 'Datos incompletos']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, priority = ?, completed = ? WHERE id = ?");
    $stmt->execute([$title, $description, $priority, $completed, $id]);

    echo json_encode(["message" => "Tarea actualizada exitosamente"]);
    exit;
}

// Actualizar estado de completado
if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $completed = (int)$data['completed'];  // Asegúrate de que sea un número entero (0 o 1)

    if (!isset($id) || !isset($completed)) {
        echo json_encode(["message" => "ID o estado no válido"]);
        http_response_code(400);  // Bad request
        exit;
    }

    // Actualiza el estado de la tarea (completada o pendiente)
    $stmt = $pdo->prepare("UPDATE tasks SET completed = ? WHERE id = ?");
    $stmt->execute([$completed, $id]);

    // Verifica si la tarea fue actualizada
    if ($stmt->rowCount() > 0) {
        echo json_encode(["message" => "Estado de tarea actualizado exitosamente"]);
    } else {
        echo json_encode(["message" => "No se encontró la tarea"]);
        http_response_code(404);  // Not found
    }
    exit;
}

// Finalizar almacenamiento en búfer
ob_end_flush();
?>
