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
    protected $tables = array(); //database tables


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

    } // end of function

    /*
     * Connects to the database
     *
     *  @param array $connection
     *
     *  @return void
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
     * Save the insert query into the database
     *
     * @return bool
     */

    public function save()
    {
        $sql = $this->_query;
        $conn = $this->conn;

        //returns the stmt object
        $stmt = $this->makeQuery($sql, $conn);

        if (is_object($stmt))
        {
            //Save the insertId
            $this->_insert_id = $stmt->insert_id;


            if ($stmt->affected_rows)
            {
                $stmt->close();

                //return true if data was inserted
                return true;
            } else
            {
                //return false if data was not inserted
                $stmt->close();

                return false;
            }
        } else
        {
            return false;
        }

    } // end of function

    /*
     * Makes a query and retrieves information from the database as JSON
     *
     * @return string (JSON)
     */

    public function getJson()
    {
        $conn = $this->conn;
        $sql = $this->_query;// query
        //returns a statement object
        $stmt = $this->makeQuery($sql, $conn);

        $results = $this->bindResults($stmt);

        $results = json_encode($results);

        var_dump($results);

        return $results;
    } // end of function

    /*
     * Makes a query and retrieves information from the database as an Array
     *
     * @return array
     */

    public function getArray()
    {
        $conn = $this->conn;
        $sql = $this->_query;// query
        //returns a statement object
        $stmt = $this->makeQuery($sql, $conn);
        $results = $this->bindResults($stmt);

        var_dump($results);

        //returns results as an array
        return $results;

    } // end of function

    /*
     * Makes a query and retrieves information from the database as Object
     *
     * @return object
     */

    public function getObject()
    {
        $conn = $this->conn;
        $sql = $this->_query;// query

        //returns a statement object
        $stmt = $this->makeQuery($sql, $conn);

        $results = $this->bindResults($stmt);

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
     * This method binds the results
     *
     * @param object $stmt -  mysqli_stmt object
     *
     * @return array
     */

    protected function bindResults($stmt)
    {
        //initialize arrays
        $fields = array();
        $results = array();

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
        if (!call_user_func_array(array($stmt, 'bind_result'), $fields))
        {
            $this->_errors[] = 'bindResults() , Error binding results';
        }

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

    } // end of function

    /*
     * @param string $sql  = takes in a valid query created with the query class
     * @param object $conn = obj takes a mysqli_connect object
     *
     * @return array = returns an array with the results from the database
     */

    protected function makeQuery($sql, $conn)
    {
        //initiate statement
        $stmt = $conn->stmt_init();


        //Prepare the query
        if ($stmt->prepare($sql))
        {
            //function  to bind parameters
            $this->bindParameters($stmt);

            //bind Results
            if (!$stmt->execute())
            {
                $this->_errors[] = 'makeQuery() , execute failed to query database';
            }

            //returns the stmt object
            return $stmt;
        } else
        {
            return false;
        }
    } // end of function

    /*
     * Determines if it needs to bind the parameters or not
     *
     * @param $object $stmt - mysqli_stmt object
     *
     * @return bool
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
                if (!call_user_func_array(array($stmt, 'bind_param'), $bind_names))
                {
                    echo $this->_errors[] = 'Error binding parameters';

                    return false;
                } else
                {
                    // var_dump($bind_names);
                    return true;
                }

            }
        } else
        {
            return false; // returns nothing
        }

    }// end of function

    /*
     * Function destroys rows from the database
     *
     * @return bool
     */

    public function destroy()
    {

        //Connection
        $conn = $this->conn;

        //get the query
        $sql = $this->_query;

        $stmt = $this->makeQuery($sql, $conn);

        if (is_object($stmt))
        {

            if ($stmt->affected_rows)
            {
                echo 'deleted';

                return true;
            }
        } else
        {
            echo 'not deleted';

            return false;
        }
    } // end of function


    /*
     * Gets the number of rows retrieved from the database
     *
     * @return int
     */

    public function getCount()
    {
        $count = $this->_num_rows;

        return $count;
    } // end of function

    /*
     * Gets the number of rows retrieved from the database
     *
     * @return int
     */

    public function getQuery()
    {
        $query = $this->_query;

        return $query;
    } // end of function

    /*
     * Gets the number of parameters for the prepared statements
     *
     * @return str
     */

    public function getParams()
    {
        $query = $this->_parameters;

        return $query;
        // print_r($query);
    } // end of function

    /*
     * Gets columns for the prepared statements
     *
     * @return str
     */

    public function getColumns()
    {
        $query = $this->_columns;

        // print_r($query);
        return $query;
    } // end of function

    /*
     * Returns the last insert id
     *
     * @return int
     */

    public function getInsertId()
    {
        return $this->_insert_id;
    }

    /*
     *
     */
    public function getTables()
    {
        return $this->tables;
    } // end of function

    /*
     * Gets all the database tables
     *
     * @return array
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
        var_dump($dbTables);

        return $dbTables;

    } // end of function

    /*
     * returns errors in an array form
     *
     * @return array
     */

    public function getErrors()
    {
        $errors = $this->_errors;

        if (!empty($errors))
        {
            var_dump($errors);
        }

        return $errors;
    } // end of function

} // end of class
?>




