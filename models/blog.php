<?php

require('models/post.php');

class Blog
{
    private $posts = [];
    private $template = "";

    public function __construct(){
        $this->template = file_get_contents(dirname(__FILE__) . '/../templates/blog.html');
        $this->readFromFile();
    }

    public function __destruct(){
        $this->saveToFile();
    }

    public function render(){
        /*
            Render blog and posts using template
        */
        $payload = [
            'posts' => ""
        ];
        $this->sortPosts();
        foreach($this->posts as $post){
            $payload['posts'] .= $post->render();
        }

        $result = $this->template;
        foreach($payload as $brace => $replacement){
            $result = str_replace('{'.$brace.'}', $replacement, $result);
        }
        return $result;
    }

    private function sortPosts(){
        /*
            Sort post by date
        */
        $date = [];
        foreach($this->posts as $index => $post){
            $date[$index] = $post->date;
        }
        array_multisort($date, SORT_DESC, $this->posts);
    }

    public function processForm(){
        /*
            Create new post from POST data
            - no data verification
        */
        array_push(
            $this->posts,
            new Post(
                htmlspecialchars($_POST["email"]),
                htmlspecialchars($_POST["title"]),
                htmlspecialchars($_POST["body"]),
                $this->saveFile()
            )
        );
    }

    private function saveFile(){
        /*
         Safely save file and return filename
        */
        if (
            !isset($_FILES['image']['error']) ||
            is_array($_FILES['image']['error'])
        ) {
            return '';
        }
        if($_FILES['image']['size'] == 0){
            return "";
        }
        if ($_FILES['image']['size'] > 1000000) {
            throw new RuntimeException('Exceeded filesize limit.');
        }
        $path_parts = pathinfo($_FILES["image"]["name"]);
        $ex = $path_parts['extension'];
        $filename =  sha1($_FILES['image']['tmp_name']+time()).'.'.$ex;
        $path = dirname(__FILE__).'/../uploads/'.$filename;
        // if (!move_uploaded_file(
        //     $_FILES['image']['tmp_name'],
        //     $path
        // )) {
        //     throw new RuntimeException('Failed to move uploaded file.');
        // }
        $this->resizeImage($path);

        return $filename;
    }

    public function readFromFile(){
        /*
            Read posts data from disk
        */
        $json = json_decode(file_get_contents(dirname(__FILE__) . '/../data/data.php'), true);
        $this->posts = [];
        foreach($json as $post){
            $postObj = new Post();
            $postObj->fromArray($post);
            array_push($this->posts, $postObj);
        }
    }

    public function saveToFile(){
        /*
            Save posts data to disk
        */
        $json = [];
        foreach($this->posts as $post){
            array_push($json, $post->toArray());
        }

        file_put_contents(dirname(__FILE__) . '/../data/data.php', json_encode($json));

    }

    public function resizeImage($targetFile) {
        /*
            Resize image to be less than 300px x 300px
        */
        $maxDim = 300;
        list($width, $height, $type, $attr) = getimagesize( $_FILES['image']['tmp_name'] );
        if ( $width > $maxDim || $height > $maxDim ) {
            $target_filename = $_FILES['image']['tmp_name'];
            $fn = $_FILES['image']['tmp_name'];
            $size = getimagesize( $fn );
            $ratio = $size[0]/$size[1];
            if( $ratio > 1) {
                $width = $maxDim;
                $height = $maxDim/$ratio;
            } else {
                $width = $maxDim*$ratio;
                $height = $maxDim;
            }
            $src = imagecreatefromstring( file_get_contents( $fn ) );
            $dst = imagecreatetruecolor( $width, $height );
            imagecopyresampled( $dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1] );
            imagedestroy( $src );
            imagepng( $dst, $targetFile );
            imagedestroy( $dst );
        }
    }
}
