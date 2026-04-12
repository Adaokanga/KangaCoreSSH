<?php


function dennybot($token, $tgid)
{
    $onoff = shell_exec('screen -list | grep -q botdenny && echo 1 || echo 0');
    if ($onoff == 1) {
        shell_exec('screen -X -S botdenny quit');
        shell_exec("screen -dmS botdenny bash -c 'while true; do ulimit -n 999999 && /opt/KangaCore/dennybot --token $token --id $tgid; done'");
        echo "BOT DENNY-A TELEGRAM ONLINE";
    } else {
        deletecone("botdenny");
        incone("botdenny", "null", $token, $tgid, "null");
        shell_exec("screen -dmS botdenny bash -c 'while true; do ulimit -n 999999 && /opt/KangaCore/dennybot --token $token --id $tgid; done'");
        echo "BOT DENNY-A TELEGRAM ONLINE";
    }
}


function dennyboton()
{
    $onoff = shell_exec('screen -list | grep -q botdenny && echo 1 || echo 0');
    if ($onoff == 1) {
        echo "ON";

    } else {
        echo "OFF";
    }
}


function dennybotstop()
{
    deletecone("botdenny");
    shell_exec("screen -X -S botdenny quit");
    echo "BOT DENNY-A TELEGRAM OFF";

}