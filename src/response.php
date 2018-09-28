<?php

/*
 * Defines helper functions for HTTP responses.
 */

/*
 * A generic success message
 */
function success(): void {
    $output = new HTTPResponse();
    $payload->message = "Success";
    $output->setPayload($payload);
    $output->complete();
}

/*
 * A custom success message
 */
function success($message): void {
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
