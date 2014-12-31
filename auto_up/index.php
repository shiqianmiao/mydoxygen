<?php
$output = array();
exec('sudo svn update /doxygen/app_serv/ --username miaosq --password 2259152202 --non-interactive --force', $output);
//exec('sudo rm -rf /doxygen/doc/app_serv/html', $output3);
//exec('sudo /usr/local/bin/doxygen /doxygen/app_serv.conf', $output2);
exec('sudo /home/miaosq/download/doxygen_app/bin/doxygen /doxygen/app_serv.conf', $output2);
echo 1;exit;

