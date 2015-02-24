<?php

/**
 * Created by PhpStorm.
 * User: luisbetancourt
 * Date: 2/4/15
 * Time: 12:15 PM
 * Ide:  PhpStorm
 */

namespace connector\classes;
use mysqli;

require_once('Query.php');

class Connection extends Query {

    protected $conn = ''; // connection object
    protected $tables = ''; //database tables


    /*
     * Constructor connects to the database
     */

    public function __construct()
    {

        //define connection
        $connection = array();

        //require the connection details file
        require_once('connector/config/config.php');

        //create connection
        $this->conn = $this->dbConnect($connection);

    }


    /*
     * --------------------------------------
     *
     * Methods
     *
     * ok  -- it works
     * !ok -- function does not work
     *
     * --------------------------------------
     */

    /*
     * Connects to the database
     *
     * ok
     */
    protected function dbConnect($connection)
    {
        $username = $connection['username'];
        $password = $connection['password'];
        $host = $connection['host'];
        $database = $connection['database'];

        $conn = new mysqli($host, $username, $password, $database);

        if ($conn)
        {
            return $conn;
        } else
        {
            return 'bad connection settings';
        }
    } // end of function


    /*
     * Gets all the database tables
     * @param($conn) = mysqli database connection
     *
     */

    protected function getDatabaseTables()
    {
        $conn = $this->conn;

// query to show all the tables
        $tables = 'SHOW TABLES';
        $results = $conn->query($tables);

        while ($row = $results->fetch_object())
        {
            $dbTables[] = current($row);

        }

        return $dbTables;

    }


    /* ------------------------------------------
     *
     * Getter Methods
     *
     * ------------------------------------------
     */
    public function getTables()
    {
        return $this->tables;
    }

    /*
     * Save
     * used for insert it saves a query into the database
     */
    public function save()
    {

    }

    /*
     * Makes a query and retrieves information from the database as JSON
     */
    public function getJson()
    {
        $conn = $this->conn;
        $sql = $this->_query;// query
        $results = $this->makeQuery($sql, $conn);

        $results = json_encode($results);

        var_dump($results);

    } // end of function

    /*
     * Makes a query and retrieves information from the database as an Array
     */
    public function getArray()
    {
        $conn = $this->conn;
        $sql = $this->_query;// query
        $results = $this->makeQuery($sql, $conn);

        var_dump($results);

    } // end of function

    /*
     * Makes a query and retrieves information from the database as Object
     */
    public function getObject()
    {
        $conn = $this->conn;
        $sql = $this->_query;// query
        $results = $this->makeQuery($sql, $conn);

        //initialize object
        $obj = new \stdClass();

        if (is_array($results) && !empty($results))
        {
            //create an object
            $obj = json_decode(json_encode($results));
        }

        var_dump($obj);

        return $obj;

    } // end of function


    /*
     * @param1 takes in a valid query created with the query class
     * @param2 obj takes a mysqli connection object
     *
     * @return array returns an array with the results from the database
     */

    protected function makeQuery($sql, $conn)
    {
        //initiate statement
        $stmt = $conn->stmt_init();

        //initialize the arrays
        $fields = array();
        $results = array();

        //Prepare the query
        if ($stmt->prepare($sql))
        {
            //function  to bind parameters
            $this->bindParameters($stmt);

            //bind Results
            $stmt->execute();

            //store the results
            $stmt->store_result();

            //store the num_rows in protected variable _num_rows
            $this->_num_rows = $stmt->num_rows;


            //get the meta and the column names from the database
            $meta = $stmt->result_metadata();

            //loop the fields to get the column names and save the value to the column name as a variable
            //ex $fields['order'] => [$order];
            while ($field = $meta->fetch_field())
            {
                $var = $field->name; // the field name
                $$var = null; //create a variable with the same field name
                $fields[$var] =  &$$var;
            }

            //bind results
            call_user_func_array(array($stmt, 'bind_result'), $fields);

            //return the statement as an array
            $i = 0;
            while ($stmt->fetch())
            {
                $results[$i] = array(); // initialize results array
                foreach ($fields as $key => $value)
                {
                    $results[$i][$key] = $value;
                }
                $i ++;
            }


            // close statement
            $stmt->close();

            return $results;
        }
    }

    /*
     * Determines if it needs to bind the parameters or not
     */
    protected function bindParameters($stmt)
    {

        //get the parameters from the database
        $params = $this->_parameters;

        if (count($params))
        {

            {
                $types = ''; // define types

                foreach ($params as $param)
                {

                    //integer
                    if (is_int($param))
                    {
                        $types .= 'i';
                    } //string
                    elseif (is_string($param))
                    {
                        $types .= 's';
                    } //float
                    elseif (is_float($param))
                    {
                        $types .= 'd';
                        //default: unknown
                    } else
                    {
                        $types .= 'b';
                    }

                }
                //store the types into an array
                $bind_names[] = $types;

                for ($i = 0; $i < count($params); $i ++)
                {
                    $bind_name = 'bind' . $i;
                    $$bind_name = $params[$i]; // creates a variable with the name of the parameter
                    $bind_names[] = &$$bind_name; // created an array of unique parameter names
                }

                //bind the parameters
                call_user_func_array(array($stmt, 'bind_param'), $bind_names);

                // var_dump($bind_names);
            }
        } else
        {
            return false; // returns nothing
        }

    }// end of function


    /*
     * Gets the number of rows retrieved from the database
     *
     * @return int
     */
    public function getCount()
    {
        $count = $this->_num_rows;

        return $count;
    }

    /*
     * Gets the number of rows retrieved from the database
     *
     * @return int
     */
    public function getQuery()
    {
        $query = $this->_query;

        return $query;
    }


} // end of class
?>




