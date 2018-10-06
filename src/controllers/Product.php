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
     */
    static function Create($args): void {
        //checks if the required variables were given
        if(!($args['name'] && $args['price'] && $args['quantity'] && $args['image'] && $args['description'])) {
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
    static function display($name): void{
        $item = new ViewableProduct($name);
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
         $result = $db->query("SELECT * FROM products WHERE name = '" .$name. "';"); //fetch product by name from the db
        //checks if product with @param name exists
        echo $result->num_rows;
        echo "how many results:".mysqli_num_rows($result);//debugging
        if(mysqli_num_rows($result) < 0) {
                error("product does not exist");//dne, return error
            echo "FAIL";
            }
    }
}
?>
