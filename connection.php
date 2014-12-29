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

    function dumptable($table)
    {   
        $result = mysql_query("SELECT * FROM $table");

        if (!$result) {
            die("Query to show fields from table failed");
        }

        $fields_num = mysql_num_fields($result);

        $html.= "<h1>Table: $table</h1>";
        
        $html.="<table border='1'><tr>";
        
        // printing table headers
        for($i=0; $i<$fields_num; $i++)
        {
            $field = mysql_fetch_field($result);
        
            $html.="<td>{$field->name}</td>";
        }

        $html.= "</tr>\n";
        // printing table rows
        
        while($row = mysql_fetch_row($result))
        {
            $html.= "<tr>";

            // $row is array... foreach( .. ) puts every element
            // of $row to $cell variable
            foreach($row as $cell)
                $html.= "<td>$cell</td>";

            $html.= "</tr>\n";
        }
                     
        return $html;
    }
    
    
    function connection()
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
    
    function echoquery()
    {
        echo $this->query;
    }
    
    function execute($query)
    {
        $this->query   = $query;

        $time_start    = microtime();

        $this->result  =  $this->link->query($query);

        $time_end      = microtime();

        $this->exectime= round(($time_end - $time_start),5);

        return $this->result;
    }

    function fetch() { return $this->rows =  $this->result->fetch_assoc(); }

    public static function fetchOne( $sql )
    {
        $con = new connection();

        $con->execute( $sql );

        $con->fetch();

        return $con->rows;
    }
        
    function getId()
    {
        return $this->link->insert_id;
    }
        
    function closeConn()
    {
        $this->link->close();
    }
}
    
    
?>
