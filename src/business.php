<?php

/*
 * Class that implements support for different
 * businesses
 */
class Business {
    
    static function getID(): int {
        $site = "debug";
        if($_SERVER['HTTP_ORIGIN']) {
            $site = preg_replace(array('/(^\w+:|^)\/\/', '/\b\/.*/'), "", $_SERVER['HTTP_ORIGIN']);
        }
        $db = $GLOBALS['database'];
        $ID = $db->query("SELECT id from businesses WHERE site = '" . $site . "';")->fetch_object()->id;
        return $ID;
    }
}

?>
