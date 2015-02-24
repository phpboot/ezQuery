<?php
/**
 * Created by PhpStorm.
 * User: luisbetancourt
 * Date: 2/6/15
 * Time: 12:10 AM
 * Ide:  PhpStorm
 */
?>

<h2> call user funk</h2>

<?php

class car{


    public function color($color){
        print_r($color);
    }
}

$car = new car();

$array = array('red','blue','green','yellow');

call_user_func_array(array($car,'color'),array($array));
?>