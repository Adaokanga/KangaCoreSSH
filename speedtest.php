<?php
// /opt/KangaCore/speedtest.php

function speedtest()
{
    echo "\n\033[1;33mExecutando speedtest por favor aguarde...\033[0m\n";
    
    // O comando abaixo aceita as licenças automaticamente
    $gvo = shell_exec("speedtest --accept-license --accept-gdpr 2>&1");
    
    if (empty($gvo)) {
        echo "\033[1;31mErro: A ferramenta speedtest-cli não está instalada ou não retornou dados.\033[0m\n";
    } else {
        echo "\n\033[1;32mAqui está o resultado:\033[0m\n";
        echo "------------------------------------------------------------\n";
        echo $gvo;
        echo "------------------------------------------------------------\n";
    }
}