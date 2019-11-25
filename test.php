<?php

$pkey = 'mje8eNA1zgPTSnbZ8OtAQqXdN8OV7dJMAiuywx6ogZtH6yREuWarlOrJFWC39OgOJirGqDZMUtQm6o/iS7ni00UfNWMUvu81eQRP9lepfkw045dX3JaxUsGvc3iatyEkKMdRSlK7Ur6ac52FQHigY1S289FAF2w1JQqg0Csl/CE=AQAB';
$t = openssl_get_privatekey($pkey);
var_dump($t);