# ezQuery
class for making simple queries from a database

<h2>EZ Query</h2>

The purpouse of this class is to help you query a Mysql database easier by eliminating the repetitive task of connecting to the database preparing statements and executing the results, and parsing the data.

<h2>Select</h2>

To Select information from the database:

<pre>
$conn->select('table',array(
      'column1' => 'value1',
      'column2' => 'value 2'
      ));
</pre>
