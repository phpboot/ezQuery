# ezQuery
The purpouse of this class is to help you query a Mysql database easier by eliminating the repetitive task of connecting to the database, preparing statements, and parsing the data.

<h2>Configuration</h2>

In order to use the class you need to save the database information in the config.php file

<pre>
$connection = array(
    'username' => 'myUsername',
    'password' => 'myPassword',
    'host'     => 'localhost',
    'database' => 'mydatabase'
);
</pre>

<h2>Use</h2>
How to use the ezQuery
<ul>
    <li><a href="#select">Select</a></li>
    <li></li>
    <li></li>
</ul>

<h3 id="select">Select</h3>

To Select information from the database:

<pre>
$conn->select('table',array(
      'column1' => 'value1',
      'column2' => 'value 2'
      ));
</pre>

<h3>Insert</h3>

To insert information from the database:

<pre>
$conn->insert('table',array(
      'column1' => 'value1',
      'column2' => 'value 2'
      ))
      ->save();
</pre>


<h3>Delete</h3>

To Delete information from the database:

<pre>
$conn->delete('table')
      ->where('id','=', 1)
      ->destroy();
</pre>

<h3>Update</h3>

To Update information from the database:

<pre>
$conn->update('table', array(
    'column1' => 'value1',
    'column2'  => 'value2'
))->where('id', '=', '1')
  ->save();
</pre>

