<?php
// /opt/KangaCore/speedtest.php

function speedtest()
{
    echo "\n\033[1;33mExecutando speedtest por favor aguarde...\033[0m\n";

    // Verificar qual binário está disponível
    $bin = trim(shell_exec("which speedtest 2>/dev/null"));

    if (empty($bin)) {
        echo "\033[1;31mErro: speedtest não encontrado no sistema.\033[0m\n";
        return;
    }

    // Verificar se é a versão da Ookla (aceita --accept-license) ou a versão Python antiga
    $help = shell_exec("$bin --help 2>&1");
    $isOokla = strpos($help, '--accept-license') !== false;
    $isPython = strpos($help, 'speedtest-cli') !== false;

    if ($isOokla) {
        // Versão oficial Ookla
        $output = shell_exec("$bin --accept-license --accept-gdpr 2>&1");
    } elseif ($isPython) {
        // Versão Python antiga (usa --simple para formato reduzido)
        $output = shell_exec("$bin --simple 2>&1");
    } else {
        // Tentativa genérica: executa sem argumentos especiais
        $output = shell_exec("$bin 2>&1");
    }

    if (empty($output)) {
        echo "\033[1;31mErro: Não foi possível obter resultado do speedtest.\033[0m\n";
    } else {
        echo "\n\033[1;32mAqui está o resultado:\033[0m\n";
        echo "------------------------------------------------------------\n";
        echo $output;
        echo "------------------------------------------------------------\n";
    }
}
?>