#!/bin/bash
# KANGA CORE - Instalador Oficial (Versão Corrigida e Completa)

set -e  # Interrompe o script em caso de erro

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}=== KANGA CORE INSTALLER ===${NC}"

# Identificar sistema operacional
if grep -q 'NAME="Debian GNU/Linux"' /etc/os-release; then
    SYSTEM="debian"
elif grep -q 'NAME="Ubuntu"' /etc/os-release; then
    SYSTEM="ubuntu"
else
    echo -e "${RED}Sistema não suportado. Use Debian ou Ubuntu.${NC}"
    exit 1
fi

# Verificar se é root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}Este script deve ser executado como root. Use sudo.${NC}"
   exit 1
fi

# Instalar dependências básicas
echo -e "${YELLOW}[1/7] Instalando dependências básicas...${NC}"
apt update
apt upgrade -y
apt install -y sudo curl wget git unzip screen net-tools software-properties-common lsb-release ca-certificates apt-transport-https gnupg uuid-runtime

# Instalar PHP e extensões
echo -e "${YELLOW}[2/7] Instalando PHP e extensões...${NC}"
if [ "$SYSTEM" = "debian" ]; then
    # Debian: repositório sury
    if ! grep -q "packages.sury.org/php" /etc/apt/sources.list.d/sury-php.list 2>/dev/null; then
        echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/sury-php.list
        curl -fsSL https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /etc/apt/trusted.gpg.d/sury-keyring.gpg
        apt update
    fi
else
    # Ubuntu: repositório ondrej
    if ! grep -q "ondrej/php" /etc/apt/sources.list.d/ondrej-ubuntu-php-*.list 2>/dev/null; then
        add-apt-repository ppa:ondrej/php -y
        apt update
    fi
fi

apt install -y php7.4-cli php7.4-curl php7.4-sqlite3 php7.4-pgsql
# Se preferir PHP 8.x, troque as linhas acima por:
# apt install -y php8.1-cli php8.1-curl php8.1-sqlite3 php8.1-pgsql

# Criar link simbólico /bin/php se não existir
if [ ! -e "/bin/php" ]; then
    ln -s "$(command -v php)" /bin/php
fi

# Verificar instalação do PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}Falha na instalação do PHP. Abortando.${NC}"
    exit 1
fi

# Instalar libssl1.1 (necessária para alguns binários)
echo -e "${YELLOW}[3/7] Instalando libssl1.1...${NC}"
if ! dpkg -s libssl1.1 &>/dev/null; then
    echo "deb http://security.ubuntu.com/ubuntu focal-security main" | tee /etc/apt/sources.list.d/focal-security.list
    apt update && apt install -y libssl1.1
fi

# Instalar PostgreSQL (opcional, mas alguns módulos podem precisar)
echo -e "${YELLOW}[4/7] Instalando PostgreSQL...${NC}"
apt install -y postgresql postgresql-contrib

# Instalar Speedtest
echo -e "${YELLOW}[5/7] Instalando Speedtest CLI...${NC}"
if ! command -v speedtest &> /dev/null; then
    curl -s https://packagecloud.io/install/repositories/ookla/speedtest-cli/script.deb.sh | bash
    apt-get install speedtest -y
fi

# Backup do config.php existente
echo -e "${YELLOW}[6/7] Preparando diretório de instalação...${NC}"
CONFIG_BACKUP="/opt/KangaCore_config.php.bak"
if [ -f "/opt/KangaCore/config.php" ]; then
    cp /opt/KangaCore/config.php "$CONFIG_BACKUP"
    echo -e "${GREEN}Backup de config.php criado em $CONFIG_BACKUP${NC}"
fi

# Remover instalação anterior e clonar repositório
rm -rf /opt/KangaCore
git clone https://github.com/Adaokanga/KangaCoreSSH.git /opt/KangaCore

# Remover pastas desnecessárias do clone
rm -rf /opt/KangaCore/aarch64 /opt/KangaCore/x86_64 /opt/KangaCore/install.sh

# Download dos binários específicos da arquitetura
REPO_RAW="https://github.com/Adaokanga/KangaCoreSSH/raw/main"
ARCH=$(uname -m)

echo -e "${YELLOW}Baixando binários para $ARCH...${NC}"
curl -s -L -o /opt/KangaCore/menu           "$REPO_RAW/menu"
curl -s -L -o /opt/KangaCore/kanga_go       "$REPO_RAW/$ARCH/kanga_go"
curl -s -L -o /opt/KangaCore/dnstt-server   "$REPO_RAW/$ARCH/dnstt-server"
curl -s -L -o /opt/KangaCore/badvpn-udpgw   "$REPO_RAW/$ARCH/badvpn-udpgw"
curl -s -L -o /opt/KangaCore/libcrypto.so.3 "$REPO_RAW/$ARCH/libcrypto.so.3"
curl -s -L -o /opt/KangaCore/libssl.so.3    "$REPO_RAW/$ARCH/libssl.so.3"
curl -s -L -o /opt/KangaCore/ProxyKanga     "$REPO_RAW/$ARCH/ProxyKanga"
curl -s -L -o /opt/KangaCore/dennybot       "$REPO_RAW/$ARCH/dennybot"
curl -s -L -o /opt/KangaCore/banner.php     "$REPO_RAW/banner.php"

# Ajustar permissões (CORRIGIDO)
echo -e "${YELLOW}[7/7] Ajustando permissões...${NC}"
cd /opt/KangaCore

# Tornar executáveis apenas os binários e scripts
chmod +x menu kanga_go dnstt-server badvpn-udpgw ProxyKanga dennybot
chmod 644 *.php *.txt *.json 2>/dev/null || true
chmod 755 .

# Garantir que banner.php tenha permissão de leitura
chmod 644 banner.php

# Restaurar config.php se existir backup
if [ -f "$CONFIG_BACKUP" ]; then
    cp "$CONFIG_BACKUP" /opt/KangaCore/config.php
    chmod 644 /opt/KangaCore/config.php
    echo -e "${GREEN}config.php restaurado de $CONFIG_BACKUP${NC}"
fi

cd "$HOME"

# Criar link do comando menu
echo -n "/opt/KangaCore/menu" > /bin/menu
chmod +x /bin/menu

# Configurar crontab
echo -e "${YELLOW}Configurando tarefas agendadas...${NC}"
# Limpeza de /run/user
if ! crontab -l 2>/dev/null | grep -q "find /run/user.*mount"; then
    (crontab -l 2>/dev/null; echo "*/5 * * * * find /run/user -maxdepth 1 -mindepth 1 -type d -exec mount -o remount,size=1M {} \;") | crontab -
fi

# Autostart na reinicialização
if ! crontab -l 2>/dev/null | grep -q "autostart"; then
    (crontab -l 2>/dev/null; echo "@reboot sleep 30 && /usr/bin/php /opt/KangaCore/menu.php autostart") | crontab -
fi

# Execução de scripts de teste
if ! crontab -l 2>/dev/null | grep -q "KangaTeste"; then
    (crontab -l 2>/dev/null; echo '@reboot sleep 30 && find /etc/KangaTeste -name "*.sh" -exec {} \;') | crontab -
fi

# Configurar SSH para aceitar RSA
echo -e "${YELLOW}Configurando SSH...${NC}"
if ! grep -q "HostKeyAlgorithms +ssh-rsa" /etc/ssh/sshd_config; then
    sed -i '/# HostKeyAlgorithms/ a\HostKeyAlgorithms +ssh-rsa' /etc/ssh/sshd_config
fi
if ! grep -q "PubkeyAcceptedKeyTypes +ssh-rsa" /etc/ssh/sshd_config; then
    sed -i '/# PubkeyAcceptedKeyTypes/ a\PubkeyAcceptedKeyTypes +ssh-rsa' /etc/ssh/sshd_config
fi

# Instalar PostgreSQL e criar bancos
echo -e "${YELLOW}Configurando bancos de dados...${NC}"
bash <(php /opt/KangaCore/postinstall.php installpostgre)

# Gerar tabelas
php /opt/KangaCore/menu.php createautostart
php /opt/KangaCore/menu.php createTable
php /opt/KangaCore/menu.php createdbkanga
php /opt/KangaCore/menu.php createv2table
php /opt/KangaCore/dbconvert.php convertdba
php /opt/KangaCore/dbconvert.php finishdba
php /opt/KangaCore/menu.php deletecone ws 
php /opt/KangaCore/menu.php createXrayTable

# Finalizar processos antigos
echo -e "${YELLOW}Parando processos antigos...${NC}"
screen -X -S proxykanga quit 2>/dev/null || true
screen -X -S openvpn quit 2>/dev/null || true
screen -X -S badvpn quit 2>/dev/null || true
screen -X -S checkuser quit 2>/dev/null || true
screen -X -S napster quit 2>/dev/null || true
screen -X -S limiter quit 2>/dev/null || true
screen -X -S botdenny quit 2>/dev/null || true
screen -X -S kanga_api quit 2>/dev/null || true

# Iniciar serviços
echo -e "${YELLOW}Iniciando serviços...${NC}"
php /opt/KangaCore/menu.php autostart

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  KANGA CORE instalado com sucesso!${NC}"
echo -e "${GREEN}  Digite: ${YELLOW}menu${GREEN} para acessar o painel.${NC}"
echo -e "${GREEN}========================================${NC}"