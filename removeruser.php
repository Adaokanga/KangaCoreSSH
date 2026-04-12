<?php
require_once '/opt/KangaCore/config.php';

function deluser($USR_EX)
{
    shell_exec("usermod -p $(openssl passwd -1 'poneicavao2930') $USR_EX");
    shell_exec("kill -9 `ps -fu $USR_EX | awk '{print $2}' | grep -v PID`");
    shell_exec("userdel $USR_EX");
    shell_exec("php /opt/KangaCore/menu.php deleteData $USR_EX");
}


function delusernew($id)
{
    global $db_host, $db_port, $db_name, $db_user, $db_pass;


$conn = pg_connect("host=localhost dbname=kangacore user=$db_user password=$db_pass");

    $query = "SELECT usr FROM users where id = $1";

    $result = pg_query_params($conn, $query, array($id));

    while ($row = pg_fetch_assoc($result)) {
        $user = $row['usr'];
        shell_exec("php /opt/KangaCore/menu.php deluser $user");
    }

    pg_close($conn);
}

