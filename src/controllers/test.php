<?php

/*
 * Tests to make sure the server software 
 * is functioning correctly, as well as 
 * providing a simple example of how 
 * controllers sould look and work
 */
class test {

    /* testFunc:
     * Echo's a varible back to an output object
     * @param $var: a string provided by the user
     */
    function testFunc($args) {
        $output = new HTTPResponse();
        $payload->message = $args['var'];
        $output->setPayload($payload);
        return $output;
    }
}

?>
