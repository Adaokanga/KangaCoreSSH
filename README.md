Aqui está o README.md refatorado exatamente no mesmo estilo do arquivo original, apenas com os dados atualizados para o KANGA CORE SSH:

# 🛡️ KANGA CORE SSH

O **KANGA CORE** é um painel de gerenciamento SSH robusto e moderno, desenvolvido em **PHP** e **Bash**.  
Ele oferece controle total sobre usuários, limites, validades e monitoramento do sistema, ideal para servidores SSH de acesso remoto.

## 🚀 Instalação

Para instalar o KANGA CORE, utilize o comando abaixo:

```sh
wget https://raw.githubusercontent.com/Adaokanga/KangaCoreSSH/main/install.sh -O install.sh && chmod +x install.sh && ./install.sh
```

---

Documentação para integração:

Criar usuario:

Validade deve estar em Dias
Usuario não pode ser invalido para o sistema linux
Senha precisa ser valido para o sistema linux
Limite precisa ser um numero valido como 1~99999999

```sh
php /opt/KangaCore/menu.php criaruser $validade $usuario $senha $limite
```

Gerar teste:

Validade deve estar em minutos!

```sh
php /opt/KangaCore/menu.php gerarteste $validade
```

Deletar usuario:

Usuario deve ser valido!

```sh
php /opt/KangaCore/menu.php delusernew $usuario
```

Alterar validade:

Validade deve estar em dias!
Usuario deve ser valido!

```sh
php /opt/KangaCore/menu.php alterardatanew $usuario $validade
```

Alterar limite:

Usuario deve ser valido!
Limite precisa ser um numero valido como 1~99999999

```sh
php /opt/KangaCore/menu.php uplimitnew $usuario $limite
```

Verificar limite do usuario:

Usuario deve ser valido!

```sh
php /opt/KangaCore/menu.php printlim2new $usuario
```

Alterar senha:

Usuario deve ser valido!
Senha precisa ser valido para o sistema linux

```sh
php /opt/KangaCore/menu.php uppassnew $usuario $senha
```

Remover expirados:

```sh
php /opt/KangaCore/menu.php removeexpired
```

Gerar Backup KANGA CORE

O backup se encontra na pasta /root

Nome: kangacoressh.json

```sh
php /opt/KangaCore/menu.php createbackup
```

Restaurar backup usuarios:

```sh
php /opt/KangaCore/menu.php restorebackupuser
```

Restaurar backup conexão:

```sh
php /opt/KangaCore/menu.php restorebackupconnect
```

Relatorio de usuarios

este relatorio mostrara todos usuarios senhas e limites

```sh
php /opt/KangaCore/menu.php relatoriouser
```

Checkar % de uso de Ram

```sh
php /opt/KangaCore/menu.php ram
```

Checkar % de uso de CPU

```sh
php /opt/KangaCore/menu.php cpu
```

Usuarios criados

```sh
php /opt/KangaCore/menu.php retrieveDataAndCount
```

Usuarios Online

```sh
php /opt/KangaCore/menu.php onlines
```

Uso de rede

```sh
php /opt/KangaCore/menu.php network
```

Gerenciar Kanga API

```sh
php /opt/KangaCore/menu.php manageKangaAPI
```

Para suporte: https://t.me/+btRH6KWXgVQ0NGQ0
