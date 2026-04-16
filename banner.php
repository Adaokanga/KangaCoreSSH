<?php
// KANGA CORE - Gerenciador de Banner SSH
error_reporting(0);

define('BANNER_FILE', '/etc/bannerssh');
define('DROPBEAR_DEFAULT', '/etc/default/dropbear');
define('SSHD_CONFIG', '/etc/ssh/sshd_config');

function banner_get_config() {
    $config = ['ssh_banner' => '', 'dropbear_banner' => ''];
    
    // Verifica SSH
    $sshd = @file_get_contents(SSHD_CONFIG);
    if ($sshd !== false) {
        preg_match('/^Banner\s+(.+)$/m', $sshd, $matches);
        if (!empty($matches[1])) {
            $config['ssh_banner'] = trim($matches[1]);
        }
    }
    
    // Verifica Dropbear
    if (shell_exec("netstat -nltp 2>/dev/null | grep -q 'dropbear'")) {
        $dropbear_conf = @file_get_contents(DROPBEAR_DEFAULT);
        if ($dropbear_conf !== false && preg_match('/^DROPBEAR_BANNER="(.+)"$/m', $dropbear_conf, $matches)) {
            $config['dropbear_banner'] = trim($matches[1]);
        }
    }
    
    // Se nenhum configurado, define padrão
    if (empty($config['ssh_banner'])) $config['ssh_banner'] = BANNER_FILE;
    if (empty($config['dropbear_banner']) && isset($dropbear_conf)) $config['dropbear_banner'] = BANNER_FILE;
    
    return $config;
}

function banner_ensure_config() {
    $config = banner_get_config();
    $banner_path = $config['ssh_banner'] ?: BANNER_FILE;
    
    // Configura SSH
    $sshd = @file_get_contents(SSHD_CONFIG);
    if ($sshd === false) return false;
    if (!preg_match('/^Banner\s+'.preg_quote($banner_path, '/').'$/m', $sshd)) {
        // Remove linhas Banner existentes e adiciona a nova
        $sshd = preg_replace('/^#?Banner\s+.*$/m', '', $sshd);
        $sshd .= "\nBanner $banner_path\n";
        file_put_contents(SSHD_CONFIG, $sshd);
    }
    
    // Configura Dropbear se estiver rodando
    if (shell_exec("netstat -nltp 2>/dev/null | grep -q 'dropbear'")) {
        $dropbear_conf = @file_get_contents(DROPBEAR_DEFAULT);
        if ($dropbear_conf !== false) {
            if (!preg_match('/^DROPBEAR_BANNER="/m', $dropbear_conf)) {
                file_put_contents(DROPBEAR_DEFAULT, "\nDROPBEAR_BANNER=\"$banner_path\"\n", FILE_APPEND);
            } elseif (!preg_match('/^DROPBEAR_BANNER="'.preg_quote($banner_path, '/').'"$/m', $dropbear_conf)) {
                $dropbear_conf = preg_replace('/^DROPBEAR_BANNER=.*$/m', "DROPBEAR_BANNER=\"$banner_path\"", $dropbear_conf);
                file_put_contents(DROPBEAR_DEFAULT, $dropbear_conf);
            }
        }
    }
    
    return $banner_path;
}

function banner_restart_services() {
    shell_exec("service ssh restart > /dev/null 2>&1 &");
    shell_exec("service dropbear restart > /dev/null 2>&1 &");
}

function banner_menu() {
    system('clear');
    echo "\033[38;5;39m╔════════════════════════════════════════════════════════════╗\033[0m\n";
    echo "\033[38;5;39m║\033[0m        \033[1;36mGERENCIADOR DE BANNER SSH - KANGA CORE\033[0m        \033[38;5;39m║\033[0m\n";
    echo "\033[38;5;39m╠════════════════════════════════════════════════════════════╣\033[0m\n";
    echo "\033[38;5;39m║\033[0m \033[1;33m🔗 Gerador de banner online:\033[0m                            \033[38;5;39m║\033[0m\n";
    echo "\033[38;5;39m║\033[0m    https://venhabrabo.github.io/criar_banner_rgba/         \033[38;5;39m║\033[0m\n";
    echo "\033[38;5;39m╠════════════════════════════════════════════════════════════╣\033[0m\n";
    
    // Mostra status atual
    $config = banner_get_config();
    $banner_file = $config['ssh_banner'] ?: BANNER_FILE;
    if (file_exists($banner_file) && filesize($banner_file) > 5) {
        echo "\033[38;5;39m║\033[0m \033[1;32m✔ Banner ativo em:\033[0m $banner_file" . str_repeat(' ', 48 - strlen($banner_file)) . "\033[38;5;39m║\033[0m\n";
    } else {
        echo "\033[38;5;39m║\033[0m \033[1;31m✘ Nenhum banner configurado\033[0m" . str_repeat(' ', 30) . "\033[38;5;39m║\033[0m\n";
    }
    echo "\033[38;5;39m╠════════════════════════════════════════════════════════════╣\033[0m\n";
    echo "\033[38;5;39m║\033[0m \033[1;33m1\033[0m - ADICIONAR / ALTERAR BANNER                           \033[38;5;39m║\033[0m\n";
    echo "\033[38;5;39m║\033[0m \033[1;33m2\033[0m - REMOVER BANNER                                        \033[38;5;39m║\033[0m\n";
    echo "\033[38;5;39m║\033[0m \033[1;33m0\033[0m - VOLTAR                                                \033[38;5;39m║\033[0m\n";
    echo "\033[38;5;39m╚════════════════════════════════════════════════════════════╝\033[0m\n";
    echo -ne "\033[1;33m > \033[0m";
    
    $opt = trim(fgets(STDIN));
    
    if ($opt == '1') {
        banner_add();
    } elseif ($opt == '2') {
        banner_remove();
    } elseif ($opt == '0') {
        return;
    } else {
        echo "\n\033[1;31mOpção inválida!\033[0m\n";
        sleep(1);
        banner_menu();
    }
}

function banner_add() {
    system('clear');
    echo "\033[1;36m═══ ADICIONAR BANNER SSH ═══\033[0m\n\n";
    
    // Garante que a configuração existe
    $banner_file = banner_ensure_config();
    
    echo -ne "\033[1;32mMensagem a exibir no banner:\033[0m\n> ";
    $msg = trim(fgets(STDIN));
    if (empty($msg)) {
        echo "\n\033[1;31mCampo vazio ou inválido!\033[0m\n";
        sleep(2);
        banner_menu();
        return;
    }
    
    echo "\n\033[1;36mTamanho da fonte:\033[0m\n";
    echo " 1 - PEQUENA  (h6)\n";
    echo " 2 - MÉDIA    (h4)\n";
    echo " 3 - GRANDE   (h3)\n";
    echo " 4 - GIGANTE  (h1)\n";
    echo -ne "\033[1;32mOpção:\033[0m ";
    $size_opt = trim(fgets(STDIN));
    $sizes = ['1'=>'6', '2'=>'4', '3'=>'3', '4'=>'1', '01'=>'6', '02'=>'4', '03'=>'3', '04'=>'1'];
    $size = isset($sizes[$size_opt]) ? $sizes[$size_opt] : '3';
    
    echo "\n\033[1;36mCor do texto:\033[0m\n";
    echo " 01 - AZUL      06 - CYANO\n";
    echo " 02 - VERDE     07 - LARANJA\n";
    echo " 03 - VERMELHO  08 - ROXO\n";
    echo " 04 - AMARELO   09 - PRETO\n";
    echo " 05 - ROSA      10 - SEM COR\n";
    echo -ne "\033[1;32mOpção:\033[0m ";
    $cor_opt = trim(fgets(STDIN));
    
    $colors = [
        '1'=>'blue', '01'=>'blue',
        '2'=>'green', '02'=>'green',
        '3'=>'red', '03'=>'red',
        '4'=>'yellow', '04'=>'yellow',
        '5'=>'#F535AA', '05'=>'#F535AA',
        '6'=>'cyan', '06'=>'cyan',
        '7'=>'#FF7F00', '07'=>'#FF7F00',
        '8'=>'#9932CD', '08'=>'#9932CD',
        '9'=>'black', '09'=>'black',
        '10'=>null
    ];
    
    $color = isset($colors[$cor_opt]) ? $colors[$cor_opt] : null;
    
    // Monta HTML
    $html = "<h$size>";
    if ($color !== null) {
        $html .= "<font color='$color'>$msg</font>";
    } else {
        $html .= $msg;
    }
    $html .= "</h$size>\n";
    
    // Escreve no arquivo
    if (file_put_contents($banner_file, $html) === false) {
        echo "\n\033[1;31mErro ao escrever no arquivo $banner_file!\033[0m\n";
        sleep(2);
        banner_menu();
        return;
    }
    
    banner_restart_services();
    echo "\n\033[1;32m✔ Banner definido com sucesso!\033[0m\n";
    sleep(2);
    banner_menu();
}

function banner_remove() {
    $config = banner_get_config();
    $banner_file = $config['ssh_banner'] ?: BANNER_FILE;
    
    if (file_put_contents($banner_file, " ") === false) {
        echo "\n\033[1;31mErro ao limpar o banner!\033[0m\n";
    } else {
        banner_restart_services();
        echo "\n\033[1;32m✔ Banner removido com sucesso!\033[0m\n";
    }
    sleep(2);
    banner_menu();
}

// Se chamado diretamente via CLI com argumento "banner", executa o menu
if (isset($argv[1]) && $argv[1] == 'banner') {
    banner_menu();
}
?>