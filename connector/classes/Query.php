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
 * First - it Builds a query
 * Second - it prepares a statement
 */


namespace connector\classes;


class Query {

    protected $_query = null; // query from user input
    protected $_table = null; // table that the user wants to attempt to interact with
    protected $_columns = array(); // columns from a select Query this is used for the prepared statements

    protected $_parameters = array(); //parameters for the bind_parameters() mysqli function

    public function __construct()
    {

    }

    /*
     * Selects all rows from a table
     * @ void
     */
    protected function all($table)
    {
        $sql = 'SELECT * FROM ' . $table;
        $this->_query = $sql;
        $this->_table = $table;

    }

    /*
    * Build the Select Query and returns it
    */
    protected function buildSelectQuery($columns)
    {
        return $selectedColumns = '`' . implode('` , `', $columns) . '`';
    }


    /*
     * The amount of columns for the Query is not known so call_user_func_array is used
     * to make the select method more flexible when querying the database.
     *
     * @ void
     */
    protected function selectColumns($table, $columns)
    {

        // if the columns array is empty then get all the results
        $selectedColumns = call_user_func_array(array($this, 'buildSelectQuery'), array($columns));

        $sql = 'SELECT ' . $selectedColumns . ' FROM ' . '`' . $table . '`';

        var_dump($sql);

        $this->_query = $sql;
        $this->_table = $table;

    }

    /*
     * Selects a table from a database if the column array is not defined then
     * all the results from the table will be returned
     * Param1 = table name
     * Param 2 = columns (optional)
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
    }


    /*
     *  Inserts in the database
     * @param 1 = table name
     * @param 2 = array of values where key is the tale name and value is the the value to add
     */

    public function insert($table, $values = array())
    {
        $keys = array_keys($values);
        $values = array_values($values);

        $implodedKeys = '`' . implode('`,`', $keys) . '`';
        $implodedValues = '`' . implode('`,`', $values) . '`';

        $sql = 'INSERT INTO ' . $table . ' ' . $implodedKeys . ' VALUES ' . $implodedValues;

        $this->_query = $sql;
        $this->_table = $table;

        return $this;

    }

    /*
     * Deletes from a database
     * @param 1 - table name
     */

    public function delete($table)
    {
        $sql = 'DELETE FROM ' . '`' . $table . '`';
        $this->_query = $sql;
        $this->_table = $table;

        return $this;
    }

    /*
     * Updates a table in the database
     * @param 1 = table name
     * @param 2 = column values in an array where 'column name' =>'columns value'
     */

    public function update($table, $values = array())
    {
        $sql = 'UPDATE ' . $table . 'SET';

        $this->_table = $table;
        $this->_query = $sql;

        return $this;
    }

    /*
     * selects where to using table name and a
     * @param 1 = table name
     * @param 2 = arithmetic sign (+,-,<=,>=,!=)
     * @param 3 = what to look for in the table
     */
    public function where($column, $mathSign, $match)
    {

        //save the $match as a parameter
        $this->_parameters[] = $match;

        //get the table
        $table = $this->_table;
        $savedQuery = $this->_query;
        $sql = ' WHERE ' . $table . '.' . $column . ' ' . $mathSign . ' ?' .'';

        //save the new query
        $this->_query = $savedQuery . $sql;

        var_dump($this->_query);

        return $this;
    }
    /*
     * selects where to using table name and a
     * @param 1 = table name
     * @param 2 = arithmetic sign (+,-,<=,>=,!=)
     * @param 3 = what to look for in the table
     */
    public function andWhere($column, $mathSign, $match)
    {

        //save the $match as a parameter
        $this->_parameters[] = $match;

        //get the table
        $table = $this->_table;
        $savedQuery = $this->_query;

        $sql = ' AND ' . $table . '.' . $column . ' ' . $mathSign . ' ?' .'';

        //save the new query
        $this->_query = $savedQuery . $sql;

        var_dump($this->_query);

        return $this;
    }
    /*
     * selects where to using table name and a
     * @param 1 = table name
     * @param 2 = arithmetic sign (+,-,<=,>=,!=)
     * @param 3 = what to look for in the table
     */
    public function orWhere($column, $mathSign, $match)
    {

        //save the $match as a parameter
        $this->_parameters[] = $match;

        //get the table
        $table = $this->_table;
        $savedQuery = $this->_query;

        $sql = ' OR ' . $table . '.' . $column . ' ' . $mathSign . ' ?' .'';

        //save the new query
        $this->_query = $savedQuery . $sql;

        var_dump($this->_query);

        return $this;
    }

    /*
     * selects where to using table name and a
     * @param 1 = table name
     * @param 2 = arithmetic sign (+,-,<=,>=,!=)
     * @param 3 = what to look for in the table
     */
    public function orderBy($column, $order = 'DESC')
    {
        // get the Generated Query
        $savedQuery = $this->_query;

        $sql = 'ORDER BY ' . $column . ' ' . $order;

        //save the new query
        $this->_query = $savedQuery . $sql;

        var_dump($this->_query);

        return $this;
    }

    /*
     * Cleans all the variables so that you can keep querying the database
     */
    protected function cleanUp(){
        $this->_columns = array();
        $this->_parameters = array();
        $this->table = null;
        $this->query = null;
    }

}