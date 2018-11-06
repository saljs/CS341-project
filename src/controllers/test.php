<?php
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
        $queryResult = $db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE 'sals5552_cart_" . getBusinessID() . "';"); //run a query
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
     * Returns the current business ID
     */
    static function business($args): void {
        success(getBusinessID()); //shorthand for HTTPResponse code 200
    }

    static function cattest($args): void {
        $string = "?name=50&code=50&typeRadio=percent&percent=50&tags-input=Microwave%2C324%2C1234&categories=Electronics&categories=Office&startdate=2018-05-05T05:05&enddate=2019-05-05T17:05";

        print_r(array_values($args));
        echo array_values($args);
    }
}

?>
