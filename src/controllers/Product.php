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
     * @param category: category for item //need to add to table
     */
    static function Create($args): void {
        //checks if the required variables were given
        if(!($args['name'] && $args['price'] && $args['quantity'] && $args['image'] && $args['description'])) {//once add cat. to table, add check here
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
    /* 
     * Loads an existing Product
     * @param name: name of the product to query from db
     */
    static function load($args): void{
        $item = new ViewableProduct($args['name']);
        $item->display();
    }
     /* 
     * deletes an existing Product
     */
    static function delete(): void{
        echo "<h1>unimplimented, see functional req.s </h1>";
    }
     /* 
     * edits an existing Product
     */
    static function edit(): void{
        echo "<h1>unimplimented, see functional req.s </h1>";
    }


}
class ViewableProduct{
    public $name;
    public $price;
    public $quantity;
    public $image;
    public $description;
    public $category;
    
    function __construct($name){
        $db = $GLOBALS['database'];
        $q = "SELECT * FROM product WHERE name = '".$name."';";
        echo $q;
         $result = $db->query($q); //fetch product by name from the db
        //checks if product with @param name exists
        echo "<br> db query returned " .mysqli_num_rows($result). " results <br>";
        if(mysqli_num_rows($result) < 1) {//if product with this name gave a NO result
            error("product does not exist");//dne, return error
            echo "FAIL";
         }
        else{//there was a result
            //load all rows from query into this object
            $row = mysqli_fetch_assoc($result);
                $this->name = $row['name'];
                $this->price = $row['price'];
                $this->quantity = $row['quantity'];
                $this->image = $row['image'];
                $this->description = $row['description'];
               // $this->category = $row['name'];
            success();
        }
        print_r($this);
    }
    function diplay(){
        echo "<h1>unimplimented, creates user veiwable calling on product.html</h1>";
    }
}
?>
