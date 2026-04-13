<?php
// KANGA CORE API - Central de Comando Remoto
header("Content-Type: application/json");

// 1. IMPORTAÇÃO DE TODOS OS MÓDULOS DE CONTROLE
require_once '/opt/KangaCore/config.php';
require_once '/opt/KangaCore/database.php';
require_once '/opt/KangaCore/criarusuario.php';   // Para criar SSH
require_once '/opt/KangaCore/removeruser.php';    // Para deletar
require_once '/opt/KangaCore/alterardata.php';    // Para validade
require_once '/opt/KangaCore/alterarlimite.php';  // Para limites
require_once '/opt/KangaCore/alterarsenha.php';   // Para senhas
require_once '/opt/KangaCore/sshmonitor.php';     // Para onlines reais
require_once '/opt/KangaCore/xray.php';           // Para V2ray/Vmess
require_once '/opt/KangaCore/expirado.php';       // Para limpeza automática

// Segurança
define('API_TOKEN', 'kanga_core_premium_token_2026');

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if ($authHeader !== 'Bearer ' . API_TOKEN) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Token invalido"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $input['action'] ?? '';

switch ($action) {

    // --- MONITORAMENTO ---
    case 'list_online':
        // Usa a lógica do sshmonitor.php para contar onlines reais
        ob_start(); 
        sshmonitor(); // Chama a função do seu arquivo sshmonitor.php
        $output = ob_get_clean();
        echo json_encode(["status" => "success", "monitor" => explode("\n", trim($output))]);
        break;

    // --- CRIAÇÃO ---
    case 'create_ssh':
        $user = $input['username'] ?? '';
        $pass = $input['password'] ?? '';
        $lim  = $input['limit'] ?? 1;
        $days = $input['days'] ?? 30;
        
        if (!empty($user) && !empty($pass)) {
            criaruser($days, $user, $pass, $lim); // Função do criarusuario.php
            echo json_encode(["status" => "success", "message" => "Usuario $user criado"]);
        }
        break;

    // --- EDIÇÃO DE USUÁRIO ---
    case 'change_pass':
        $user = $input['username'] ?? '';
        $pass = $input['password'] ?? '';
        if (function_exists('uppass')) {
            uppass($user, $pass); // Função do alterarsenha.php
            echo json_encode(["status" => "success", "message" => "Senha alterada"]);
        }
        break;

    case 'change_limit':
        $user = $input['username'] ?? '';
        $lim  = $input['limit'] ?? 1;
        uplimit($user, $lim); // Função do alterarlimite.php
        echo json_encode(["status" => "success", "message" => "Limite atualizado"]);
        break;

    case 'change_date':
        $user = $input['username'] ?? '';
        $days = $input['days'] ?? 30;
        alterardata($user, $days); // Função do alterardata.php
        echo json_encode(["status" => "success", "message" => "Validade estendida"]);
        break;

    // --- REMOÇÃO ---
    case 'delete_user':
        $user = $input['username'] ?? '';
        if (function_exists('removeruser')) {
            removeruser($user); // Função do removeruser.php
            echo json_encode(["status" => "success", "message" => "Usuario removido"]);
        }
        break;

    // --- XRAY (V2RAY) ---
    case 'create_xray':
        $days = $input['days'] ?? 30;
        $uuid = trim(shell_exec('uuidgen'));
        xrayAddUser($uuid, $days, "API_USER", "vmess"); // Função do xray.php
        echo json_encode(["status" => "success", "uuid" => $uuid]);
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Acao '$action' nao suportada"]);
        break;
}