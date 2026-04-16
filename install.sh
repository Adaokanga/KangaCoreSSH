#!/bin/bash
if grep -q 'NAME="Debian GNU/Linux"' /etc/os-release; then
    system="debian"
else
    system="ubuntu"
fi

if [ "$system" = "debian" ]; then
    apt-get install -y sudo
fi

sudo apt update
sudo apt upgrade -y
sudo apt install -y uuid-runtime
sudo apt install -y curl
sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common gnupg curl wget
if [ "$system" = "debian" ]; then
    repos=$(find /etc/apt/ -name '*.list' -exec cat {} + | grep  ^[[:space:]]*deb | grep -q "packages.sury.org/php" && echo 1 || echo 0)
    if [ "$repos" = "0" ]; then
        echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list
        curl -fsSL  https://packages.sury.org/php/apt.gpg | sudo gpg --dearmor -o /etc/apt/trusted.gpg.d/sury-keyring.gpg
        sudo apt update
    fi
else
    repos=$(find /etc/apt/ -name '*.list' -exec cat {} + | grep  ^[[:space:]]*deb | grep -q "/ondrej/php" && echo 1 || echo 0)
    if [ "$repos" = "0" ]; then
        sudo apt install lsb-release ca-certificates apt-transport-https software-properties-common -y
        sudo add-apt-repository ppa:ondrej/php -y
        sudo apt update
    fi
fi
sudo apt install -y php-cli php-curl php-sqlite3 php-pgsql git

if [ ! -e "/bin/php" ]; then
    sudo ln -s "$(command -v php)" /bin/php
fi

# === BACKUP EXISTING CONFIG.PHP BEFORE REMOVING FOLDER ===
CONFIG_BACKUP="/opt/KangaCore_config.php.bak"
if [ -f "/opt/KangaCore/config.php" ]; then
    cp /opt/KangaCore/config.php "$CONFIG_BACKUP"
    echo "Backup de config.php criado em $CONFIG_BACKUP"
fi
# =========================================================

cd /opt/
rm -rf KangaCore
cd "$HOME"

# Clona do seu novo repositório GitHub
git clone https://github.com/Adaokanga/KangaCoreSSH.git /opt/KangaCore

# Remove pastas desnecessárias baixadas do git (pois vamos baixar os binários específicos abaixo)
rm -rf /opt/KangaCore/aarch64
rm -rf /opt/KangaCore/x86_64
rm -rf /opt/KangaCore/install.sh

# Variavel com o link raw do seu GitHub
REPO_RAW="https://github.com/Adaokanga/KangaCoreSSH/raw/main"
ARCH=$(uname -m)

# Downloads dos binários com os nomes Kanganizados
curl -s -L -o /opt/KangaCore/menu $REPO_RAW/menu
curl -s -L -o /opt/KangaCore/kanga_go $REPO_RAW/$ARCH/kanga_go
curl -s -L -o /opt/KangaCore/dnstt-server $REPO_RAW/$ARCH/dnstt-server
curl -s -L -o /opt/KangaCore/badvpn-udpgw $REPO_RAW/$ARCH/badvpn-udpgw
curl -s -L -o /opt/KangaCore/libcrypto.so.3 $REPO_RAW/$ARCH/libcrypto.so.3
curl -s -L -o /opt/KangaCore/libssl.so.3 $REPO_RAW/$ARCH/libssl.so.3
curl -s -L -o /opt/KangaCore/ProxyKanga $REPO_RAW/$ARCH/ProxyKanga
curl -s -L -o /opt/KangaCore/dennybot $REPO_RAW/$ARCH/dennybot
# Download do módulo banner.php (comum a todas arquiteturas)
curl -s -L -o /opt/KangaCore/banner.php $REPO_RAW/banner.php

cd /opt/KangaCore
chmod +x *
cd "$HOME"

if [ -f "$CONFIG_BACKUP" ]; then
    cp "$CONFIG_BACKUP" /opt/KangaCore/config.php
    echo "config.php restaurado de $CONFIG_BACKUP"
fi
# ==============================================

echo -n "/opt/KangaCore/menu" > /bin/menu
chmod +x /bin/menu

existing_cron=$(crontab -l 2>/dev/null | grep -F "*/5 * * * * find /run/user -maxdepth 1 -mindepth 1 -type d -exec mount -o remount,size=1M {} \;")
if [ -z "$existing_cron" ]; then
    (crontab -l 2>/dev/null; echo "*/5 * * * * find /run/user -maxdepth 1 -mindepth 1 -type d -exec mount -o remount,size=1M {} \;") | crontab -
fi

existing_crono=$(crontab -l 2>/dev/null | grep -F "@reboot sleep 30 && /usr/bin/php /opt/KangaCore/menu.php autostart")
if [ -z "$existing_crono" ]; then
    (crontab -l 2>/dev/null; echo "@reboot sleep 30 && /usr/bin/php /opt/KangaCore/menu.php autostart") | crontab -
fi

existing_lima=$(crontab -l 2>/dev/null | grep -F '@reboot sleep 30 && find /etc/KangaTeste -name "*.sh" -exec {} \;')
if [ -z "$existing_lima" ]; then
    (crontab -l 2>/dev/null; echo '@reboot sleep 30 && find /etc/KangaTeste -name "*.sh" -exec {} \;') | crontab -
fi

if dpkg -s libssl1.1 &>/dev/null; then
    echo "libssl1.1 is already installed."
else
    echo "deb http://security.ubuntu.com/ubuntu focal-security main" | tee /etc/apt/sources.list.d/focal-security.list
    apt-get update && apt-get install -y libssl1.1
fi

bash <(php /opt/KangaCore/postinstall.php installpostgre)

# Gerar DBS:
php /opt/KangaCore/menu.php createautostart
php /opt/KangaCore/menu.php createTable
php /opt/KangaCore/menu.php createdbkanga   # Mantido nome original para compatibilidade
php /opt/KangaCore/menu.php createv2table
php /opt/KangaCore/dbconvert.php convertdba
php /opt/KangaCore/dbconvert.php finishdba
php /opt/KangaCore/menu.php deletecone ws 
php /opt/KangaCore/menu.php createXrayTable

sed -i '/# HostKeyAlgorithms/ a\HostKeyAlgorithms +ssh-rsa' /etc/ssh/sshd_config
sed -i '/# PubkeyAcceptedKeyTypes/ a\PubkeyAcceptedKeyTypes +ssh-rsa' /etc/ssh/sshd_config

reposi2=$(find /etc/apt/ -name *.list | xargs cat | grep  ^[[:space:]]*deb | grep -q "ookla" && echo 1 || echo 0)
if [ "$reposi2" = "1" ]; then
    echo "OK"
else
    curl -s https://packagecloud.io/install/repositories/ookla/speedtest-cli/script.deb.sh | bash
    apt install -y speedtest
fi

install_netstat() {
    GREEN='\033[0;32m'
    RED='\033[0;31m'
    NC='\033[0m'
    if command -v netstat &> /dev/null; then
        echo -e "${GREEN}Netstat is already installed.${NC}"
    else
        echo "Netstat is not installed. Trying to install..."
        if [ -x "$(command -v apt)" ]; then
            apt update
            apt install -y net-tools
            echo -e "${GREEN}Netstat installation complete.${NC}"
        else
            echo -e "${RED}Unsupported system. Please install netstat manually.${NC}"
        fi
    fi
}
install_netstat

# continua o script
screen -X -S proxykanga quit
screen -X -S openvpn quit
screen -X -S badvpn quit
screen -X -S checkuser quit
screen -X -S napster quit
screen -X -S limiter quit
screen -X -S botdenny quit
screen -X -S kanga_api quit

php /opt/KangaCore/menu.php autostart

echo ""
echo ""
echo ""
echo "KANGA CORE instalado! Use o comando: menu"