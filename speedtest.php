<?php
// KANGA CORE - Speedtest via Ookla CLI (Corrigido)

function speedtest() {
    // Verifica se o binário está instalado
    $speedtestBin = trim(shell_exec('which speedtest 2>/dev/null') ?: '');
    if (empty($speedtestBin)) {
        // Tenta fallback para speedtest-cli (versão Python)
        $speedtestBin = trim(shell_exec('which speedtest-cli 2>/dev/null') ?: '');
    }
    
    if (empty($speedtestBin)) {
        echo "\033[1;31m✘ Speedtest CLI não está instalado!\033[0m\n";
        echo "   Execute manualmente: apt install speedtest\n";
        return;
    }

    echo "\033[1;36m⏳ Executando speedtest, por favor aguarde...\033[0m\n";
    echo "   (Isso pode levar até 1 minuto)\n\n";

    // Aumenta o tempo limite do PHP para 120 segundos
    set_time_limit(120);

    // Comando com aceitação automática de licença e saída legível
    // Redireciona STDERR para STDOUT para capturar possíveis erros
    $cmd = escapeshellcmd($speedtestBin) . " --accept-license --accept-gdpr --format=human-readable 2>&1";
    
    // Executa e captura a saída completa
    $output = shell_exec($cmd);
    
    if ($output === null || trim($output) === '') {
        echo "\033[1;31m✘ Falha ao executar o speedtest.\033[0m\n";
        echo "   Verifique sua conexão com a internet.\n";
        return;
    }

    // Exibe resultado com formatação
    echo "\033[1;32m═══ RESULTADO DO SPEEDTEST ═══\033[0m\n\n";
    echo $output;
    echo "\n\033[1;32m══════════════════════════════\033[0m\n";
}

// Se chamado via CLI com argumento 'speedtest', executa a função
if (isset($argv[1]) && $argv[1] == 'speedtest') {
    speedtest();
}
?>