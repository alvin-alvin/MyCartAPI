<?php


class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function createPurchase($store_id,$item_list){
        $stmt = $this->conn->prepare("");
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getAllProductByStore($store_id){
        $stmt = $this->conn->prepare("SELECT * FROM tb_products WHERE store_id = '$store_id'");
        $stmt->execute();
        $products = $stmt->get_result();
        $stmt->close();
        return $products;
    }
}

?>