<?php
// Localização: /opt/KangaCore/checkatt.php

function checkatt()
{
    // Caminho local do arquivo de versão
    $localVersionFile = "/opt/KangaCore/version.txt";
    
    // Lê a versão instalada na VPS
    $version = trim(@file_get_contents($localVersionFile) ?: "0.0");
    
    // URL RAW do seu repositório GitHub para verificar a versão mais recente
    $remoteVersionUrl = "https://raw.githubusercontent.com/Adaokanga/KangaCoreSSH/main/version.txt";
    
    // Captura a versão remota
    $version2 = trim(shell_exec("wget -qO- $remoteVersionUrl") ?: "0.0");

    if ($version === $version2) {
        return "Atualizado";
    } else {
        return "Novo Update Disponível!";
    }
}