<?php


function limitador()
{
    $onoff = shell_exec('screen -list | grep -q limitador && echo 1 || echo 0');
    if ($onoff == 1) {
        shell_exec('screen -X -S limitador quit');
        shell_exec("screen -dmS limitador bash -c 'while true; do php /opt/KangaCore/limiter.php; done'");
        echo "Kanga Limiter Ativo\n";
    } else {
        deletecone("limiter");
        incone("limiter", "null", "null", "null", "null");
        shell_exec("screen -dmS limitador bash -c 'while true; do php /opt/KangaCore/limiter.php; done'");
        echo "Kanga Limiter Ativo\n";
    }
}


function limitadoron()
{
    $onoff = shell_exec('screen -list | grep -q limitador && echo 1 || echo 0');
    if ($onoff == 1) {
        echo "ON";

    } else {
        echo "OFF";
    }
}


function limitadorstop()
{
    deletecone("limitador");
    shell_exec("screen -X -S limitador quit");
    echo "Kanga limiter Desativado\n";

}