<?php
require_once '/opt/KangaCore/config.php';

function createautostart()
{
    global $db_host, $db_port, $db_name, $db_user, $db_pass;


    $conn = pg_connect("host=localhost dbname=kangacore user=$db_user password=$db_pass");

    if (!$conn) {
        echo "Failed to connect to PostgreSQL";
        exit;
    }

    $query = "CREATE TABLE IF NOT EXISTS conestart (
                ID SERIAL PRIMARY KEY,
                cone TEXT,
                porta TEXT,
                banner TEXT,
                token TEXT,
                tipo TEXT
              )";

    $result = pg_query($conn, $query);

    if (!$result) {
        echo "Error creating table: " . pg_last_error($conn);
    }

    pg_close($conn);
}



function incone($cone, $porta, $banner, $token, $tipo)
{
    global $db_host, $db_port, $db_name, $db_user, $db_pass;


    $conn = pg_connect("host=localhost dbname=kangacore user=$db_user password=$db_pass");
    if (!$conn) {
        die("Connection failed: " . pg_last_error());
    }
    $query = "INSERT INTO conestart (cone, porta, banner, token, tipo) VALUES ($1, $2, $3, $4, $5)";
    $result = pg_prepare($conn, "", $query);
    if (!$result) {
        die("Statement preparation failed: " . pg_last_error());
    }
    $result = pg_execute($conn, "", array($cone, $porta, $banner, $token, $tipo));
    if (!$result) {
        die("Execution failed: " . pg_last_error());
    }
    pg_close($conn);
}


function autostart()
{
    global $db_host, $db_port, $db_name, $db_user, $db_pass;


    $conn = pg_connect("host=localhost dbname=kangacore user=$db_user password=$db_pass");
    if (!$conn) {
        die("Connection failed: " . pg_last_error());
    }
    $query = "SELECT * FROM conestart";
    $result = pg_query($conn, $query);
    if (!$result) {
        die("Query execution failed: " . pg_last_error());
    }
    while ($row = pg_fetch_assoc($result)) {
        $cone = $row['cone'];
        $porta = $row['porta'];
        $banner = $row['banner'];
        $token = $row['token'];
        $tipo = $row['tipo'];
        startsv($cone, $porta, $banner, $token, $tipo);
    }
}

function startsv($cone, $port, $banner, $token, $tipo)
{
    if ($cone == "ws") {
        shell_exec("/usr/bin/screen -dmS proxy bash -c 'while true; do ulimit -n 999999 && /opt/KangaCore/proxy --ulimit 999999 --port $port --response $banner; done'");
    } elseif ($cone == "open") {
        shell_exec("/usr/bin/screen -dmS openvpn bash -c 'while true; do ulimit -n 999999 && /opt/KangaCore/iptables.sh && cd /etc/openvpn && /usr/sbin/openvpn --config /etc/openvpn/server.conf; done'");
    } elseif ($cone == "badx") {
        shell_exec("/usr/bin/screen -dmS badvpn bash -c 'while true; do ulimit -n 999999 && /opt/KangaCore/badvpn-udpgw --listen-addr 127.0.0.1:7300 --max-clients 1024 --max-connections-for-client 2 --client-socket-sndbuf 10000; done'");
    } elseif ($cone == "netsta") {
        insertnet();
    } elseif ($cone == "checkuser") {
        shell_exec("/usr/bin/screen -dmS checkuser bash -c 'while true; do ulimit -n 999999 && php /opt/KangaCore/checkuser.php; done'");
    } elseif ($cone == "napster") {
        shell_exec("/usr/bin/screen -dmS napster bash -c 'while true; do ulimit -n 999999 && /opt/KangaCore/kanga_go -port :$port; done'");
    } elseif ($cone == "proxykanga") {
        shell_exec("/usr/bin/screen -dmS proxykanga bash -c 'while true; do ulimit -n 999999 && /opt/KangaCore/ProxyKanga -port $port; done'");
    } elseif ($cone == "limiter") {
        shell_exec("/usr/bin/screen -dmS limitador bash -c 'while true; do php /opt/KangaCore/limiter.php; done'");
    } elseif ($cone == "botdenny") {
        shell_exec("screen -dmS botdenny bash -c 'while true; do ulimit -n 999999 && /opt/KangaCore/dennybot --token $banner --id $token; done'");
    } elseif ($cone == "dnstt") {
        $bin      = '/opt/KangaCore/dnstt-server';
        $confDir  = '/opt/KangaCore/dnstt';
        $privFile = $confDir . '/server.key';
        if (!is_dir($confDir)) {
            mkdir($confDir, 0700, true);
        }
        if (!file_exists($privFile)) {
            return;
        }
        $cmd = "iptables -C INPUT -p udp --dport {$port} -j ACCEPT 2>/dev/null || iptables -I INPUT -p udp --dport {$port} -j ACCEPT";
        shell_exec($cmd);
        $cmd = "iptables -t nat -C PREROUTING -p udp --dport 53 -j REDIRECT --to-ports {$port} 2>/dev/null || iptables -t nat -I PREROUTING -p udp --dport 53 -j REDIRECT --to-ports {$port}";
        shell_exec($cmd);

        $cmd = "/usr/bin/screen -dmS dnstt bash -c '"
            . "while true; do "
            . "ulimit -n 999999 && "
            . escapeshellcmd($bin)
            . " -udp 0.0.0.0:" . $port
            . " -privkey-file " . escapeshellarg($privFile)
            . " " . escapeshellarg($banner)
            . " " . escapeshellarg($token)
            . "; "
            . "sleep 2; "
            . "done'";
        shell_exec($cmd);
    }
}

function deletecone($cone)
{
    global $db_host, $db_port, $db_name, $db_user, $db_pass;


    $conn = pg_connect("host=localhost dbname=kangacore user=$db_user password=$db_pass");
    if (!$conn) {
        die("Connection failed: " . pg_last_error());
    }
    $query = "DELETE FROM conestart WHERE cone = $1";
    $result = pg_prepare($conn, "", $query);
    if (!$result) {
        die("Statement preparation failed: " . pg_last_error());
    }
    $result = pg_execute($conn, "", array($cone));
    if (!$result) {
        die("Execution failed: " . pg_last_error());
    }
    pg_close($conn);
}
