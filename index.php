<?php

require('models/blog.php');

$blog = new Blog();
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $blog->processForm();
}
$blog->saveToFile();

echo $blog->render();

$blog = null;
