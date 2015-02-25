# ezQuery
class for making simple queries from a database

<h2>EZ Query</h2>

The purpouse of this class is to help you query a Mysql database easier by eliminating the repetitive task of connecting to the database, preparing statements, and parsing the data.

<h2>configuration</h2>

In order to use the class you need to savethe database information in the config.php file

<pre>
$connection = array(
    'username' => 'myUsername',
    'password' => 'myPassword',
    'host'     => 'localhost',
    'database' => 'mydatabase'
);
</pre>

<h2>Select</h2>

To Select information from the database:

<pre>
$conn->select('table',array(
      'column1' => 'value1',
      'column2' => 'value 2'
      ));
</pre>

<h2>Insert</h2>

To insert information from the database:

<pre>
$conn->insert('table',array(
      'column1' => 'value1',
      'column2' => 'value 2'
      ))
      ->save();
</pre>


<h2>Delete</h2>

To Delete information from the database:

<pre>
$conn->delete('table')
      ->where('id','=', 1)
      ->destroy();
</pre>

<h2>Update</h2>

To Update information from the database:

<pre>
$conn->update('table', array(
    'column1' => 'value1',
    'column2'  => 'value2'
))->where('id', '=', '1')
  ->save();
</pre>

