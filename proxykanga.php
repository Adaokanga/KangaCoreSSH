<?php
require_once '/opt/KangaCore/config.php';




function dbkanga()
{
    $cpuInfo = shell_exec('cat /proc/cpuinfo | grep -m 1 "Serial"');
    $macInfo = shell_exec('cat /sys/class/net/$(ls /sys/class/net | head -n 1)/address');
    $macAddress = trim($macInfo);
    $machineInfo = $cpuInfo . $macAddress;
    return hash('sha256', $machineInfo);

}


function dbkanga2()
{
    $cpuInfo = shell_exec('cat /proc/cpuinfo | grep -m 1 "Serial"');
    $macInfo = shell_exec('cat /sys/class/net/$(ls /sys/class/net | head -n 1)/address');
    $macAddress = trim($macInfo);
    $machineInfo = $cpuInfo . $macAddress;
    echo hash('sha256', $machineInfo);

}


function createdbkanga()
{
    $hash = dbkanga();
    global $db_host, $db_port, $db_name, $db_user, $db_pass;


$conn = pg_connect("host=localhost dbname=kangacore user=$db_user password=$db_pass");

    if (!$conn) {
        echo "Failed to connect to PostgreSQL";
        exit;
    }

    $query = "CREATE TABLE IF NOT EXISTS proxydr4 (
                ID SERIAL PRIMARY KEY,
                gvo TEXT
              )";

    $result = pg_query($conn, $query);

    if (!$result) {
        echo "Error creating table: " . pg_last_error($conn);
    }
    $checkQuery = "SELECT 1 FROM proxydr4 WHERE ID = 1";
    $checkResult = pg_query($conn, $checkQuery);

    if (!$checkResult) {
        pg_close($conn);
        return;
    }

    $row = pg_fetch_assoc($checkResult);
    if (!$row) {
        $url = "https://raw.githubusercontent.com/Penguinehis/proxykanga/main/key";
        $content = file_get_contents($url);
        if ($content === false) {
            echo "OFF";
        } else {
            if (trim($content) === "mZyx3VuEclU4XWd8EFUnGpW9jQOiSqds5YtZfLyAMXNFucR5rF6FfTHoaYJ1hbYA6H7JObE1TfoWriTgfeTowljbF6lPJ9TS0Pe77FiIO4A3mJsa9VKHeoI5F8NGXv0Yoy7srN6WexkGkpDfciEBux5M9W50ucVgQsJKnYaZREuBYxHnq5wckoV0I4HCgQIPUULL95fwCuamu6DnsSr9EldgveWLf7VhkgxUjBdHYTCbAYcBLib9ISwPiD50BAYik82MA99ZbtLeyzTJN5CDFxDVPnNaBAOFAKeUXfIbft4w") {
                $insertQuery = "INSERT INTO proxydr4 (ID, gvo) VALUES (1, '$hash')";
                $insertResult = pg_query($conn, $insertQuery);
                if (!$insertResult) {
                    echo "Error inserting default row: " . pg_last_error($conn);
                }
            } else {
                echo "OFF";
            }
        }
    }

    pg_close($conn);
}


function kanga()
{
    $lima = dbkanga();
    $currentDateTime = date('Y-m-d H:i');
    $generatedHash = hash('sha256', $currentDateTime);

    /*if (kangaprhash($lima)) {*/
    echo $generatedHash;
    /*} else {
        echo "ERROR";
    }*/

}


function kangaprhash($hash)
{
    $kangaa = dbkanga();
    global $db_host, $db_port, $db_name, $db_user, $db_pass;


$conn = pg_connect("host=localhost dbname=kangacore user=$db_user password=$db_pass");
    if (!$conn) {
        die("Connection failed: " . pg_last_error());
    }
    $query = "SELECT gvo FROM proxydr4 WHERE ID = 1";
    $result = pg_query($conn, $query);
    if (!$result) {
        die("Query execution failed: " . pg_last_error());
    }
    while ($row = pg_fetch_assoc($result)) {
        $hash = $row['gvo'];
        if ($hash == $kangaa) {
            return true;
        } else {
            del232409875892309ete();
            return false;
        }
    }
    pg_close($conn);
}


function del232409875892309ete()
{
    $kangaa = dbkanga();
    global $db_host, $db_port, $db_name, $db_user, $db_pass;


$conn = pg_connect("host=localhost dbname=kangacore user=$db_user password=$db_pass");
    if (!$conn) {
        die("Connection failed: " . pg_last_error());
    }
    $query = "DELETE FROM proxydr4 WHERE ID = 1";
    $result = pg_query($conn, $query);
    if (!$result) {
        die("Query execution failed: " . pg_last_error());
    } else {
        createdbkanga();
    }
    pg_close($conn);
}


function kangaprhash2()
{
    $kangaa = dbkanga();
    global $db_host, $db_port, $db_name, $db_user, $db_pass;


$conn = pg_connect("host=localhost dbname=kangacore user=$db_user password=$db_pass");
    if (!$conn) {
        die("Connection failed: " . pg_last_error());
    }
    $query = "SELECT gvo FROM proxydr4 WHERE ID = 1";
    $result = pg_query($conn, $query);
    if (!$result) {
        die("Query execution failed: " . pg_last_error());
    }
    while ($row = pg_fetch_assoc($result)) {
        $hash = $row['gvo'];
        if ($hash == $kangaa) {
            echo $hash;
        } else {
            return false;
        }
    }
    pg_close($conn);
}


function pkanga($port)
{
    $onoff = shell_exec('screen -list | grep -q proxykanga && echo 1 || echo 0');
    if ($onoff == 1) {
        shell_exec('screen -X -S proxykanga quit');
        shell_exec("screen -dmS proxykanga bash -c 'while true; do ulimit -n 999999 && /opt/KangaCore/ProxyKanga -port $port; done'");
        echo "Proxy Kanga Online na Porta: $port\n";
    } else {
        deletecone("proxykanga");
        incone("proxykanga", $port, "null", "null", "null");
        shell_exec("screen -dmS proxykanga bash -c 'while true; do ulimit -n 999999 && /opt/KangaCore/ProxyKanga -port $port; done'");
        echo "Proxy Kanga Online na Porta: $port\n";
    }
}


function pkangaon()
{
    $onoff = shell_exec('screen -list | grep -q proxykanga && echo 1 || echo 0');
    if ($onoff == 1) {
        echo "ON";

    } else {
        echo "OFF";
    }
}


function pkangastop()
{
    deletecone("proxykanga");
    shell_exec("screen -X -S proxykanga quit");
    echo "Proxy Kanga OFF\n";

}