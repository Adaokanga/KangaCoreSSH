<?php
// KANGA CORE - Cérebro do Painel (Versão completa)
error_reporting(0);

// CARREGAMENTO DE TODOS OS MÓDULOS (58 arquivos originais)
require_once '/opt/KangaCore/config.php';
require "/opt/KangaCore/database.php";
require "/opt/KangaCore/sshmonitor.php";
require "/opt/KangaCore/criarusuario.php";
require "/opt/KangaCore/removeruser.php";
require "/opt/KangaCore/alterardata.php";
require "/opt/KangaCore/websocket.php";
require "/opt/KangaCore/badvpn.php";
require "/opt/KangaCore/alterarlimite.php";
require "/opt/KangaCore/alterarsenha.php";
require "/opt/KangaCore/stunnel.php";
require "/opt/KangaCore/infovps.php";
require "/opt/KangaCore/backup.php";
require "/opt/KangaCore/networkms.php";
require "/opt/KangaCore/openvpn.php";
require "/opt/KangaCore/network.php";
require "/opt/KangaCore/checkatt.php";
require "/opt/KangaCore/autostart.php";
require "/opt/KangaCore/checkusercontrol.php";
require "/opt/KangaCore/napster.php";
require "/opt/KangaCore/relatoriouser.php";
require "/opt/KangaCore/expirado.php";
require "/opt/KangaCore/userteste.php";
require "/opt/KangaCore/gbackup.php";
require "/opt/KangaCore/removertodos.php";
require "/opt/KangaCore/automenu.php";
require "/opt/KangaCore/proxykanga.php";   // Nome correto
require "/opt/KangaCore/speedtest.php";
require "/opt/KangaCore/statusvps.php";
require "/opt/KangaCore/xray.php";
require "/opt/KangaCore/dnstt.php";
require "/opt/KangaCore/limiterstart.php"; // Adicionado
require "/opt/KangaCore/bottg.php";        // Adicionado

// Definições da API (Opção 14)
define('API_PORT', '2083');
define('API_PATH', '/opt/KangaCore/kanga_api.php');

// --- FUNÇÕES VISUAIS ---
function drawHeader($subtitle = "HOME") {
    $version = trim(@file_get_contents("/opt/KangaCore/version.txt") ?: "1.0");
    $blue = "\033[38;5;39m"; $cyan = "\033[38;5;51m"; $reset = "\033[0m"; $green = "\033[38;5;46m";
    date_default_timezone_set('Africa/Luanda');
    $date = date('d/m/Y H:i:s');
    
    echo "{$blue}╔" . str_repeat("═", 60) . "╗{$reset}\n";
    echo "{$blue}║{$reset}" . str_pad("\033[1m{$cyan}KANGA CORE SSH v{$version}{$reset}", 74, " ", STR_PAD_BOTH) . "{$blue}║{$reset}\n";
    echo "{$blue}╠" . str_repeat("═", 60) . "╣{$reset}\n";
    echo "{$blue}║{$reset} Painel: {$green}" . str_pad($subtitle, 24) . "{$reset} Hora: " . str_pad($date, 20) . "{$blue}║{$reset}\n";
    echo "{$blue}╠" . str_repeat("═", 60) . "╣{$reset}\n";
}

function drawMenuLines($items) {
    $blue = "\033[38;5;39m"; $reset = "\033[0m"; $yellow = "\033[38;5;226m";
    foreach ($items as $index => $name) {
        $line = " $index - $name";
        echo "{$blue}║{$reset} {$yellow}$line" . str_repeat(" ", 60 - strlen($line) - 1) . "{$blue}║{$reset}\n";
    }
}

function drawFooter() {
    $blue = "\033[38;5;39m"; $reset = "\033[0m";
    echo "{$blue}╚" . str_repeat("═", 60) . "╝{$reset}\n";
}

// --- FUNÇÕES DE SAÍDA DE TEXTO PARA O BASH (com novo visual) ---
function menu() {
    $users = retrieveDataAndCount();
    $online = onlines();
    drawHeader("MENU PRINCIPAL");
    echo "\033[38;5;39m║\033[0m" . str_pad("Usuários: $users | Onlines: $online", 60, " ", STR_PAD_BOTH) . "\033[38;5;39m║\033[0m\n";
    echo "\033[38;5;39m╠" . str_repeat("═", 60) . "╣\033[0m\n";
    drawMenuLines([
        "1" => "Gerenciar Usuários",
        "2" => "Protocolos de Conexão",
        "3" => "Ferramentas do Sistema",
        "0" => "Sair do Script"
    ]);
    drawFooter();
}

function menuusuario() {
    drawHeader("GERENCIAR USUÁRIOS");
    drawMenuLines([
        "1" => "Criar Usuário", "2" => "Gerar Teste", "3" => "Remover Usuário",
        "4" => "Monitor Online", "5" => "Alterar Validade", "6" => "Alterar Limite",
        "7" => "Alterar Senha", "8" => "Relatório", "9" => "Remover Expirados",
        "0" => "Voltar"
    ]);
    drawFooter();
}

function menuconnect() {
    drawHeader("PROTOCOLOS");
    drawMenuLines([
        "1" => "KANGA PROXY X", "2" => "Stunnel GVO", "3" => "OpenVPN",
        "4" => "Xray Core", "5" => "DNSTT (SlowDNS)", "6" => "Portas Ativas",
        "0" => "Voltar"
    ]);
    drawFooter();
}

function menuferramenta() {
    drawHeader("FERRAMENTAS");
    drawMenuLines([
        "1" => "Restaurar Backup", "2" => "BadVPN X", "3" => "Balanceamento de Rede",
        "4" => "CheckUser Mult App", "5" => "Gerar/Importar Backup KangaCoreSSH",
        "6" => "AutoMenu", "7" => "Speedtest", "8" => "Limitador (Alto Uso de CPU!)",
        "9" => "Atualizar", "10" => "INFO VPS", "11" => "Bot Telegram",
        "12" => "Remover todos os usuarios", "13" => "Remover Script",
        "14" => "Gerenciar Kanga API (Painel Remoto)",
        "0" => "Voltar"
    ]);
    drawFooter();
}

function menuxray() {
    drawHeader("XRAY CORE");
    drawMenuLines([
        "1" => "Criar Usuário", "2" => "Remover Usuário", "3" => "Listar Usuários",
        "4" => "Informação Xray", "5" => "Gerar Certificado TLS",
        "6" => "Instalar/Configurar Xray Core", "7" => "Remover Xray Core",
        "0" => "Voltar"
    ]);
    drawFooter();
}

// --- LÓGICA DA API KANGA (CORRIGIDA COM NOHUP - ESTÁVEL E FUNCIONAL) ---
function manageKangaAPI() {
    system('clear');
    drawHeader("KANGA API REMOTA");
    
    // Verificar se a API está rodando (processo escutando na porta)
    $portCheck = shell_exec("ss -tlnp 2>/dev/null | grep ':" . API_PORT . "' | grep php");
    $pid = shell_exec("pgrep -f 'kanga_api.php'");
    
    $status = (!empty($portCheck) || !empty($pid)) ? "\033[1;32mATIVO\033[0m" : "\033[1;31mINATIVO\033[0m";
    if (!empty($pid)) {
        $status = "\033[1;32mATIVO (PID: " . trim($pid) . ")\033[0m";
    }
    
    // Verificar persistência no crontab
    $cronCheck = shell_exec("crontab -l 2>/dev/null | grep 'kanga_api.php'");
    $persist = !empty($cronCheck) ? "\033[1;32m[PERSISTÊNCIA ON]\033[0m" : "\033[1;31m[PERSISTÊNCIA OFF]\033[0m";
    
    echo " Status atual: $status\n";
    echo " Persistência: $persist\n\n";
    echo " 1 - Ligar API (Porta ".API_PORT.")\n";
    echo " 2 - Desligar API\n";
    echo " 3 - Ver Endpoint / Link da API\n";
    echo " 0 - Voltar\n\n";
    echo " Opção: ";
    $opt = trim(fgets(STDIN));
    
    // Caminho absoluto do PHP (ajuste se necessário)
    $phpBin = trim(shell_exec("which php") ?: "/usr/bin/php");
    $command = "nohup $phpBin -S 0.0.0.0:" . API_PORT . " " . API_PATH . " > /dev/null 2>&1 &";
    
    if($opt == '1') {
        // --- LIGAR API ---
        if(empty($portCheck)) {
            // Mata qualquer processo residual
            shell_exec("pkill -f 'kanga_api.php' 2>/dev/null");
            sleep(1);
            
            // Inicia com nohup
            shell_exec($command);
            sleep(2); // Aguarda o servidor subir
            
            // Verifica se a porta está escutando
            $newCheck = shell_exec("ss -tlnp 2>/dev/null | grep ':" . API_PORT . "'");
            if(!empty($newCheck)) {
                echo "\n[✔] API iniciada com sucesso na porta " . API_PORT . "!";
            } else {
                echo "\n[!] API pode não ter iniciado corretamente. Verifique manualmente.";
            }
        } else {
            echo "\n[!] API já está rodando na porta " . API_PORT . ".";
        }
        
        // Adicionar persistência no crontab se não existir
        if(empty($cronCheck)) {
            $cronLine = "@reboot sleep 10 && $command";
            $currentCron = shell_exec("crontab -l 2>/dev/null");
            
            if(empty($currentCron)) {
                file_put_contents("/tmp/kanga_cron_tmp", $cronLine . PHP_EOL);
                shell_exec("crontab /tmp/kanga_cron_tmp");
                unlink("/tmp/kanga_cron_tmp");
            } else {
                file_put_contents("/tmp/kanga_cron_tmp", $currentCron . $cronLine . PHP_EOL);
                shell_exec("crontab /tmp/kanga_cron_tmp");
                unlink("/tmp/kanga_cron_tmp");
            }
            echo "\n[✔] Persistência ativada no crontab (@reboot)";
        } else {
            echo "\n[!] Persistência já configurada.";
        }
        sleep(2);
        
    } elseif($opt == '2') {
        // --- DESLIGAR API ---
        shell_exec("pkill -f 'kanga_api.php'");
        sleep(1);
        
        // Remove entradas do crontab
        if(!empty($cronCheck)) {
            shell_exec("crontab -l 2>/dev/null | grep -v 'kanga_api.php' | crontab -");
            echo "\n[✘] API encerrada e persistência removida.";
        } else {
            echo "\n[✘] API encerrada (persistência já estava OFF).";
        }
        sleep(2);
        
    } elseif($opt == '3') {
        // --- EXIBIR ENDPOINT ---
        $ip = trim(shell_exec("curl -s ifconfig.me 2>/dev/null || hostname -I | awk '{print $1}'"));
        if(empty($ip)) $ip = "SEU_IP";
        $endpoint = "http://$ip:" . API_PORT . "/kanga_api.php";
        
        echo "\n\033[1;36m🔗 ENDPOINT DA API:\033[0m\n";
        echo "   $endpoint\n\n";
        echo "\033[1;33m🔐 TOKEN DE AUTENTICAÇÃO:\033[0m\n";
        echo "   Bearer kanga_core_premium_token_2026\n\n";
        echo "\033[1;37m📌 Exemplo de requisição:\033[0m\n";
        echo "   curl -X POST $endpoint?action=list_online \\\n";
        echo "        -H 'Authorization: Bearer kanga_core_premium_token_2026'\n\n";
        echo "Pressione ENTER para voltar...";
        fgets(STDIN);
    }
}

// --- DESPACHANTE DE COMANDOS (mantido original) ---
if ($argc > 1) {
    $functionName = $argv[1];
    if (function_exists($functionName)) {
        $args = array_slice($argv, 2);
        call_user_func_array($functionName, $args);
    } else {
        echo "Função '$functionName' não encontrada no PHP.\n";
    }
}