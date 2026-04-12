<?php
// /opt/KangaCore/kanga_api.php
header("Content-Type: application/json");

// Módulo de segurança - Defina um token forte
define('API_TOKEN', 'kanga_core_premium_token_2026');

// Autenticação via Header "Authorization: Bearer <token>"
$headers = getallheaders();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if ($authHeader !== 'Bearer ' . API_TOKEN) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Acesso Negado. Token invalido."]);
    exit;
}

// Receber dados da requisição POST
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

$action = isset($_GET['action']) ? $_GET['action'] : (isset($input['action']) ? $input['action'] : '');

switch ($action) {
    case 'create_ssh':
        $user = escapeshellarg($input['username']);
        $pass = escapeshellarg($input['password']);
        $limit = escapeshellarg($input['limit'] ?? 1);
        $days = escapeshellarg($input['days'] ?? 30);
        
        // Chamada direta para o script de criação do painel
        $cmd = "php /opt/KangaCore/criarusuario.php api $user $pass $limit $days";
        $output = shell_exec($cmd);
        
        echo json_encode(["status" => "success", "message" => "Usuario SSH criado.", "details" => trim($output)]);
        break;

    case 'create_xray':
        $uuid = escapeshellarg($input['uuid'] ?? shell_exec('cat /proc/sys/kernel/random/uuid'));
        $days = escapeshellarg($input['days'] ?? 30);
        
        $cmd = "php /opt/KangaCore/xray.php api_create $uuid $days";
        $output = shell_exec($cmd);
        
        echo json_encode(["status" => "success", "message" => "Usuario Xray criado.", "uuid" => trim($uuid)]);
        break;

    case 'list_online':
        // Usa o arquivo de banco de dados nativo
        require_once '/opt/KangaCore/database.php';
        $onlines = onlines(); // Função já existente no painel
        echo json_encode(["status" => "success", "online_count" => $onlines]);
        break;

    case 'activate_port':
        $port = escapeshellarg($input['port']);
        $service = escapeshellarg($input['service']); // ex: stunnel, ssh
        
        $cmd = "php /opt/KangaCore/network.php api_open_port $port $service";
        shell_exec($cmd);
        
        echo json_encode(["status" => "success", "message" => "Porta $port ativada para $service."]);
        break;

    default:
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Acao nao especificada ou invalida."]);
        break;
}
?>