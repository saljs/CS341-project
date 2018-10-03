<?php
/*
 * adds product to database and displays them
 */

class Product {

    /* 
     * Creates a new Product
     * @param name: name of the product
     * @param price: price of the product
     * @param quantity: number currently in stock
     * @param image: visual for product, a path in the directory
     */
    static function Create($args): void {
        //checks if the required variables were given
        if(!($args['name'] && $args['price'] && $args['quantity'] && $args['image'])) {
            error("Missing required fields");
            return;
        }

        //checks if product already exists with the same name
        $db = $GLOBALS['database'];
        $result = $db->query("SELECT id FROM products WHERE name = '" . $args['name'] . "';");
        if(mysqli_num_rows($result) > 0) {
            error("Product already exists");
            return;
        }
        success();
    }
    static function testFunc(): void {
        echo "hello world";
    }


}
class ViewableProduct{
    
}
?>
