<?php

/*
 * Defines helper functions for HTTP responses.
 */

/*
 * A custom success message
 */
function success($message="Success"): void {
    $output = new HTTPResponse();
    $payload->message = $message;
    $output->setPayload($payload);
    $output->complete();
}

/*
 * A custom 400 error message
 */
function error($message): void {
    $output = new HTTPResponse(400);
    $payload->message = $message;
    $output->setPayload($payload);
    $output->complete();
}
