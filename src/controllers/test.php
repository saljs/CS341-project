<?php

/*
 * Tests to make sure the server software 
 * is functioning correctly, as well as 
 * providing a simple example of how 
 * controllers sould look and work
 */
class test {

    /* 
     * Echo's a varible back to the user
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
    static function dump($args): void {
        global $database;
        $queryResult = $databse->query("SHOW TABLES;");
        $output = new HTTPResponse();
        $payload->tables = var_dump($queryResult);
        $output->setPayload($payload);
        $output->complete();
    }
}

?>
