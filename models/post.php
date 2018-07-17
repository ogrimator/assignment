<?php

class Post
{
    private $template;

    public function __construct($email="", $title="", $body="", $image=""){
        $this->email = $email;
        $this->title = $title;
        $this->body = $body;
        $this->date = time();
        $this->image = $image;
    }

    public function fromArray($json){
            $this->title = $json['title'];
            $this->image = $json['image'];
            $this->date = $json['date'];
            $this->body = $json['body'];
    }

    public function toArray(){
        $json = [
            'title' => $this->title,
            'image' => $this->image,
            'date'  => $this->date,
            'email' => $this->email,
            'body'  => $this->body
        ];
        return $json;
    }

    public function render(){
        /*
        Render post
        */
        $result = file_get_contents(dirname(__FILE__) . '/../templates/post.html');

        $payload = [
            'title' => $this->title,
            'image' => $this->image,
            'date'  => date("F j, Y, g:i a", $this->date),
            'body'  => $this->body
        ];
        foreach($payload as $brace => $replacement){
            $result = str_replace('{'.$brace.'}', $replacement, $result);
        }

        return $result;
    }
}
