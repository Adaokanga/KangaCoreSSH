#!/bin/bash
# KANGA CORE - Limpeza Final e Vinculação ao GitHub
TARGET="/storage/emulated/0/KANGA/opt/KangaCore"

echo "🚀 Iniciando Metamorfose Final: KANGA CORE SSH"

# 1. Renomeação de Arquivos e Binários
echo "[1/3] Ajustando nomes de arquivos e diretórios..."
[ -f "$TARGET/proxydragon.php" ] && mv "$TARGET/proxydragon.php" "$TARGET/proxykanga.php"

for arch in "aarch64" "x86_64"; do
    if [ -d "$TARGET/$arch" ]; then
        [ -f "$TARGET/$arch/ProxyKanga" ] && mv "$TARGET/$arch/ProxyKanga" "$TARGET/$arch/ProxyKanga"
        [ -f "$TARGET/$arch/dragon_go" ] && mv "$TARGET/$arch/dragon_go" "$TARGET/$arch/kanga_go"
        [ -f "$TARGET/$arch/ulekbot" ] && mv "$TARGET/$arch/ulekbot" "$TARGET/$arch/dennybot"
    fi
done

# 2. Substituição de Termos e Repositório
echo "[2/3] Executando o pente fino nos códigos (botdenny, gvo, GitHub)..."

# Definindo o novo repositório para substituição em massa
OLD_REPO="https://git.dr2.site/penguinehis/KangaCoreSSH-Beta"
NEW_REPO="https://github.com/Adaokanga/KangaCoreSSH"

# Mapa de trocas de strings
declare -A CHANGES=(
    ["KangaCore"]="KangaCore"
    ["dragoncore"]="kangacore"
    ["dragon"]="kanga"
    ["Kanga"]="Kanga"
    ["botulek"]="botdenny"
    ["cake"]="gvo"
    ["ulekbot"]="dennybot"
    ["ulek"]="denny"
    ["pdragon"]="pkanga"
    ["$OLD_REPO"]="$NEW_REPO"
)

# Loop de substituição em arquivos de texto/código
for old in "${!CHANGES[@]}"; do
    new="${CHANGES[$old]}"
    find "$TARGET" -type f \( -name "*.php" -o -name "*.sh" -o -name "*.txt" -o -name "menu" \) -exec sed -i "s|$old|$new|g" {} +
done

# 3. Finalização
echo "[3/3] Aplicando permissões de execução..."
chmod +x "$TARGET/menu"
chmod +x "$TARGET/install.sh"
for arch in "aarch64" "x86_64"; do
    [ -d "$TARGET/$arch" ] && chmod +x "$TARGET/$arch/"*
done

echo "✅ SUCESSO! Pasta limpa e vinculada ao repositório Adaokanga."