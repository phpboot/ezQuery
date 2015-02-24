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
     * Accepts  the query and also the connection object
     *
     * OK
     */

    public function getObject()
    {
        $conn = $this->conn;// connection object
        $sql = $this->_query;// query


        $results = ''; // initialize result
        //mysql query

        if ($result = $conn->query($sql))
        {
            while ($obj = $result->fetch_object())
            {
                // new object array
                $results[] = $obj;
            }


            /* free result set */

            $result->close();
        }

        var_dump($results);

    } // end of function


    /*
     * get database query results as an array
     *
     *  ** Needs to get the table where the results are coming from
     */
    public function getArray()
    {
        $sql = $this->_query;// query
        $conn = $this->conn;
        $results = '';// initialize results

        //mysql query
        if ($result = $conn->query($sql))
        {
            while ($obj = $result->fetch_assoc())
            {
                // new object array
                $results[] = $obj;
            }

            /* free result set */
            $result->close();
        }

        var_dump($results);

    }

    /*
 * Gets executes the query and gets the results as Json
 */
    public function getJson()
    {
        $sql = $this->_query;// query

        $conn = $this->conn;

        $results = ''; // initialize result
        //mysql query
        if ($result = $conn->query($sql))
        {
            while ($obj = $result->fetch_object())
            {
                // new object array
                $results[] = $obj;
            }

            /* free result set */
            $result->close();
        }

        //convert to json
        var_dump(json_encode($results));
    }


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

    public function getDatabaseColumns()
    {
        $conn = $this->conn;

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
    public function getPreparedJson()
    {
        $conn = $this->conn;
        $sql = $this->_query;// query
        $results = $this->makeQuery($sql, $conn);

        $results = json_encode($results);

        var_dump($results);

    } // end of function

    /*
     * Makes a query and retrieves information from the database as JSON
     */
    public function getPreparedArray()
    {
        $conn = $this->conn;
        $sql = $this->_query;// query
        $results = $this->makeQuery($sql, $conn);

        var_dump($results);

    } // end of function


    /*
     * @param1 takes in a valid query created with the query class
     * @param2 obj takes a mysqli connection object
     *
     * @return array returns an array with the results from the database
     */

    protected function makeQuery($sql, $conn)
    {
        $conn = $this->conn;
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

    }


} // end of class
?>




