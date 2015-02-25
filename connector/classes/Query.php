<?php
/**
 * Created by PhpStorm.
 * User: luisbetancourt
 * Date: 2/17/15
 * Time: 10:45 PM
 * Ide:  PhpStorm
 */

/*
 * This class builds a query
 */

namespace connector\classes;

class Query {

    protected $_query = null; // query from user input
    protected $_table = null; // table that the user wants to attempt to interact with
    protected $_columns = array(); // columns from a select Query this is used for the prepared statements

    protected $_parameters = array(); //parameters for the bind_parameters() mysqli function
    protected $_num_rows = ''; //number of rows a query retrieved

    protected $_insert_id = ''; // insert id when a row is inserted
    protected $_errors = array(); // stores errors

    public function __construct()
    {

    } // end of function

    /*
     * Selects all rows from a table
     *
     * @return void
     */

    protected function all($table)
    {
        $sql = 'SELECT * FROM ' . $table;
        $this->_query = $sql;
        $this->_table = $table;

    } // end of function

    /*
     * Build the Select Query and returns it
     *
     * @param array $columns - columns that you want to select from the database
     *
     * @return string
     */

    protected function buildSelectQuery($columns)
    {
        return $selectedColumns = '`' . implode('` , `', $columns) . '`';
    } // end of function


    /*
     * The amount of columns for the Query is not known so call_user_func_array is used
     * to make the select method more flexible when querying the database.
     *
     * @param string $table
     *
     * @return void
     */

    protected function selectColumns($table, $columns)
    {

        // if the columns array is empty then get all the results
        $selectedColumns = call_user_func_array(array($this, 'buildSelectQuery'), array($columns));

        $sql = 'SELECT ' . $selectedColumns . ' FROM ' . '`' . $table . '`';

        // var_dump($sql);

        $this->_query = $sql;
        $this->_table = $table;

    } // end of function

    /*
     * Selects a table from a database if the $column array is not defined then
     * all the results from the table will be returned
     *
     * @param string $table  - table name
     * @param array  $columns -(optional)
     *
     * @return object
     */

    public function select($table, $columns = array())
    {
        // Cleans the sql so that you can keep querying the database
        $this->cleanUp();

        if (!empty($columns))
        {

            // Save the columns in as a property add check so that it is an array
            $this->_columns = $columns;

            // creates the query
            $this->selectColumns($table, $columns);

            return $this;

        } else
        {
            //creates the query
            $this->all($table); // returns all the results ex. SELECT * FROM Table
        }

        return $this;
    } // end of function


    /*
     *  Inserts in the database
     * @param string $table - table name
     * @param array $values - associative array where key => value is $column => 'insert value'
     *
     * @return object
     */

    public function insert($table, $values = array())
    {
        //clean up 
        $this->cleanUp();

        //save the table name
        $this->_table = $table;

        //Save the column names
        $keys = array_keys($values);


        $values = array_values($values);

        //Save the Column names for the prepared statements
        $this->_columns = $keys;

        //Save the parameters for the mysqli bind_params()
        $this->_parameters = $values;

        // make the values into a ? for the prepared statements

        //initialize the variable
        $preparedStatement = '';

        //loop the column values and foreach value insert into the array a ? for the prepared statement
        foreach ($values as $value)
        {
            $preparedStatement[] = '?';
        }

        // implode the array to get this format this format the column names for the insert query
        $implodedKeys = '`' . implode('`,`', $keys) . '`';

        // implode the array to get this format ?,?,?
        $implodedValues = implode(' ,', $preparedStatement);

        $sql = 'INSERT INTO ' . $table . ' ( ' . $implodedKeys . ' )' . ' VALUES ' . '( ' . $implodedValues . ' )';

        $this->_query = $sql;

        return $this;

    } // end of function

    /*
     * Deletes from a database
     * @param string $table - table name
     *
     * @return object
     */

    public function delete($table)
    {

        $sql = 'DELETE FROM ' . '`' . $table . '`';
        $this->_query = $sql;
        $this->_table = $table;

        return $this;
    } // end of function

    /*
     * Updates a table in the database
     * @param string $table - table name
     * @param array $values - values to update
     */

    public function update($table, $arrayValues = array())
    {
        //clean up
        $this->cleanUp();

        //save the table name
        $this->_table = $table;

        //Save the column names
        $keys = array_keys($arrayValues);

        //save the vaules
        $values = array_values($arrayValues);

        //Save the Column names for the prepared statements
        $this->_columns = $keys;

        //Save the parameters for the mysqli bind_params()
        $this->_parameters = $values;

        //initialize the variable
        $preparedStatement = '';

        //loop the column values and foreach value insert into the array a ? for the prepared statement
        foreach ($arrayValues as $key => $value)
        {
            $createdStatement[] = '`'.$key . '` '. ' = ' . ' ? ';
        }
        $preparedStatement =implode(',',$createdStatement);


        $sql = 'UPDATE ' . $table . ' SET ' . $preparedStatement ;

        $this->_query = $sql;

        if(!array($values)){
            $this->_errors[] = 'update(), 2nd parameter $value must be an array';
        }

        $this->_table = $table;
        $this->_query = $sql;

        return $this;
    } // end of function

    /*
     * Selects where to using table name and a
     *
     * @param string $column   - table name
     * @param string $mathSign - arithmetic sign (+,-,<=,>=,!=)
     * @param string $match    - what to look for in the table
     *
     * @return object
     */

    public function where($column, $mathSign, $match)
    {

        //save the $match as a parameter
        $this->_parameters[] = $match;

        $savedQuery = $this->_query;
        $sql = ' WHERE ' . $column . ' ' . $mathSign . ' ?' . '';

        //save the new query
        $this->_query = $savedQuery . $sql;

        //var_dump($this->_query);

        return $this;
    } // end of function

    /*
     * Selects where to using table name and a
     *
     * @param string $column   - table name
     * @param string $mathSign - arithmetic sign (+,-,<=,>=,!=)
     * @param string $match    - what to look for in the table
     *
     * @return object
     */

    public function andWhere($column, $mathSign, $match)
    {

        //save the $match as a parameter
        $this->_parameters[] = $match;

        //get the query
        $savedQuery = $this->_query;

        $sql = ' AND ' . $column . ' ' . $mathSign . ' ?' . '';

        //save the new query
        $this->_query = $savedQuery . $sql;

        // var_dump($this->_query);

        return $this;
    } // end of function

    /*
     * Selects where to using table name and a
     *
     * @param string $column   - table name
     * @param string $mathSign - arithmetic sign (+,-,<=,>=,!=)
     * @param string $match    - what to look for in the table
     *
     * @return object
     */

    public function orWhere($column, $mathSign, $match)
    {

        //save the $match as a parameter
        $this->_parameters[] = $match;

        $savedQuery = $this->_query;

        $sql = ' OR ' . $column . ' ' . $mathSign . ' ?' . '';

        //save the new query
        $this->_query = $savedQuery . $sql;

        //  var_dump($this->_query);

        return $this;
    } // end of function

    /*
     * Orders a database result
     * @param  str $column  - table name
     * @param  str $order   - orders results as descending or ascending
     *
     * @return object
     */

    public function orderBy($column, $order = 'DESC')
    {
        //check that only ASC or DESC values are inputted
        $orderValues = array('DESC', 'ASC');

        if (!in_array($orderValues, $order))
        {
            $order = 'DESC';
        }

        // get the Generated Query
        $savedQuery = $this->_query;

        $sql = 'ORDER BY ' . $column . ' ' . $order;

        //save the new query
        $this->_query = $savedQuery . $sql;

        var_dump($this->_query);

        return $this;
    } // end of function

    /*
     * limits the search results
     * @param int $limit - Number to start fetching records
     * @param int $start - Amount or records wanted
     *
     * @return object
     */

    public function limit($limit = 10, $start = 0)
    {
        // get the SQL
        $savedQuery = $this->_query;

        if (isset($start))
        {
            $sql = ' LIMIT ' . $start . ' , ' . $limit;
        } else
        {
            $sql = ' LIMIT ' . $limit;
        }
        //save the new SQL with the new Limit Query
        $this->_query = $savedQuery . $sql;

        return $this;
    } // end of function

    /*
     * Cleans all the variables so that you can keep querying the database
     *
     * @return void
     */

    protected function cleanUp()
    {
        $this->_columns = array();
        $this->_parameters = array();
        $this->_table = null;
        $this->_query = null;

    } // end of function

} // end of class
