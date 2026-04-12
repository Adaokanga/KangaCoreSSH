<?php


function speedtest()
{
    echo "Executando speedtest por favor aguarde!\n";
    $gvo = shell_exec("speedtest --accept-license --accept-gdpr");
    echo "Aqui está o resultado\n";
    echo $gvo . "\n";
}