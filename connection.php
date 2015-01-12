<?php
include_once ( 'config.php' );

class connection
{
    public $rows;
    private $server = "127.0.0.1";
    private $db = 'ipohype';
    private $user = MYSQL_USER;
    private $pass = MYSQL_PASS;
    private $result;
    private $link;
    private $exectime; //execution time
    private $query;    
    
    function connection() // function to open a connection to SQL
    {
        $this->link = new mysqli($this->server, $this->user, $this->pass, $this->db);

        if($this->link->connect_errno > 0){
            die('Unable to connect to database');
        }
        else
        {
            $this->execute("SET NAMES 'utf8'");
        }
    }

    public function numFields() { return $this->link->field_count; }

    public function numRows() { return $this->link->num_rows; }
    
    public function displayErr() 
    {
        return $this->link->error;
    }
    
    function execute($query) // function to execute an SQL command
    {
        $this->query   = $query;

        $this->result  =  $this->link->query($query);

        return $this->result;
    }

    function fetch() { return $this->rows =  $this->result->fetch_assoc(); } // function to return all result rows

    public static function fetchOne( $sql ) // functino to return one result row at a time
    {
        $con = new connection();

        $con->execute( $sql );

        $con->fetch();

        return $con->rows;
    }
        
    function getId() // function to return the ID of a result row
    {
        return $this->link->insert_id;
    }
        
    function closeConn() // function to terminate connection to SQL
    {
        $this->link->close();
    }
}
    
?>
