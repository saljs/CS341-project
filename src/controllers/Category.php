<?php

    class Category {

        static function Create($args): void {

            // Checks if the required variables were given
            if (!($args['category'])) {
                error("Missing required fields");
                return;
            }

            // Check if user has access
            try {
                $user = new SiteUser(null, $args['token']);
                if(!$user->isAuth() || $user->type != "admin") {
                    error("User doesn't have privileges to add items");
                    return;
                }
            }
            catch(Exception $e) {
                error($e->getMessage());
                return;
            }

            // Checks if the category exists
            $db = $GLOBALS['database'];
            $sql = "SELECT category FROM categories WHERE category='" . $args['category'] . "';";
            $result = $db->query($sql);
            if(!$result)
                error("There's an error in your SQL syntax: {$sql}");

            // If there is not an existing category with the name
            if(!mysqli_num_rows(result) > 0) {

                $sql = "INSERT INTO `categories`
                      (`category`) 
                      VALUES 
                      ('" . $args['category'] . "');";

                if(!$db->query($sql)) {
                    error("There's an error in your SQL syntax: {$sql}");
                    return;
                }

            }

            success();

        }

        // Replies a coma delimited string of all categories in the database
        static function GetAll($args): void {

            $db = $GLOBALS['database'];
            $sql = "SELECT category FROM categories";

            $result = $db->query($sql);
            if(!$result) {
                error("There's an error in your SQL syntax: {$sql}");
                return;
            }

            $categories = "";

            while($row = mysqli_fetch_assoc($result)) {

                $categories .= $row['category'] . ',';

            }

            rtrim($categories, ',');

            // make into json object?
            $output = new HTTPResponse();

            // Declare our return fields.
            $payload->categories = $categories;
            $output->setPayload($payload);
            $output->complete();

        }

        static function Delete($args): void {

            if(!($args['category'])) {

                error("Missing required fields");
                return;

            }

            // Checks if the category exists
            $db = $GLOBALS['database'];
            $sql = "SELECT category FROM categories WHERE category='" . $args['category'] . "';";
            $result = $db->query($sql);
            if(!$result) {
                error("There's an error in your SQL syntax: {$sql}");
                return;
            }

            // If there is not an existing category with the name
            if(mysqli_num_rows(result) > 0) {

                $sql = "DELETE FROM `categories` WHERE category='" . $args['category'] . "';";
                if(!$db->query($sql)) {
                    error("There's an error in your SQL syntax: {$sql}");
                    return;
                }

            }

            success();

        }

    }

?>