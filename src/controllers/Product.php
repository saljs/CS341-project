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
     * @param description: describes the product
     * @param category: category for item
     */
    static function Create($args): void {
        //checks if the required variables were given
        if(!($args['name'] && $args['price'] && $args['quantity'] && $args['image'] && $args['description'] && $args['category'])) {
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
    static function display($args): void{
        $item = new ViewableProduct($args['name']);
    }


}
class ViewableProduct{
    public $name;
    public $price;
    public $quantity;
    public $image;
    public $description;
    
    function __construct($name){
        $db = $GLOBALS['database'];
        $q = "SELECT * FROM product WHERE name = '".$name."';";
        echo $q;
         $result = $db->query($q); //fetch product by name from the db
        //checks if product with @param name exists
        echo "<br> db query returned " .mysqli_num_rows($result). " results";
        if(mysqli_num_rows($result) < 1) {
            error("product does not exist");//dne, return error
            echo "FAIL";
         }
        else{
            success();
        }
    }
}
?>
