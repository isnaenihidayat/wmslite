<?php

if (isset($_GET['type'])) {

    $type = $_GET['type'];

    if ($type == 'inbound') {

        $delivery = $_GET['param'];

        if (empty($delivery)) {
            die('Need param');
        }

        if (strlen($delivery) > 20) {
            die('Param too long');
        }

        $output = shell_exec('/usr/bin/php /var/www/html/wmslite/protected/yiic.php sync inbound --delivery=' . $delivery);
        echo "<pre>$output</pre>";
    } else if ($type == 'outbound') {

        $id = $_GET['param'];

        if (empty($id)) {
            die('Need param');
        }

        if (strlen($id) > 10) {
            die('Param too long');
        }

        $output = shell_exec('/usr/bin/php /var/www/html/wmslite/protected/yiic.php sync outbound --id=' . $id);
        echo "<pre>$output</pre>";
    } else if ($type == 'outboundDataOnly') {

        $id = $_GET['param'];

        if (empty($id)) {
            die('Need param');
        }

        if (strlen($id) > 10) {
            die('Param too long');
        }

        $output = shell_exec('/usr/bin/php /var/www/html/wmslite/protected/yiic.php sync outboundDataOnly --id=' . $id);
        echo "<pre>$output</pre>";
    } else {
        die('Nothing to do here, again');
    }
} else {
    die('Nothing to do here');
}
