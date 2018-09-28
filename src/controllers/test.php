<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/response.php';

/*
 * Tests to make sure the server software 
 * is functioning correctly, as well as 
 * providing a simple example of how 
 * controllers should look and work
 */
class test {

    /* 
     * Echos a variable back to the user
     * @param args: The list of params the user provided
     */
    static function testFunc($args): void {
        //checks if the required variable was given
        if($args['var']) {
            $output = new HTTPResponse();
            $payload->message = $args['var'];
            $output->setPayload($payload);
            $output->complete(); //needs to be called to return value
        }
        else {
            $output = new HTTPResponse(400); //error response
            $payload->message = 'Error: must provide "var"';
            $output->setPayload($payload);
            $output->complete();
        }
    }

    /*
     * Dumps a list of tables in the database
     */
    static function dumpTables($args): void {
        $db = $GLOBALS['database']; //get the database connection from the list of global variables
        $queryResult = $db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE 'sals5552_cart';"); //run a query
        $output = new HTTPResponse();
        $payload->tables = array();
        while($row = $queryResult->fetch_assoc()) {
            //add each table name to the payload
            $payload->tables[] = $row['TABLE_NAME'];
        }
        $output->setPayload($payload);
        $output->complete();
    }

    /*
     * Returns the $_SERVER['HTTP_ORIGIN'] variable
     */
    static function origin($args): void {
        success($_SERVER['HTTP_ORIGIN']);
    }
}

?>
