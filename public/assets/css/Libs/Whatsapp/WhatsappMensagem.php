<?php

namespace App\Libs\Whatsapp;




class WhatsappMensagem{


    public $array = [];
 

    public $phone ; 
    public $message; 
    public $linkUrl; 
    public $image; 
    public $title; 
    public $delayMessage; 
    public $delayTyping;
    public $buttons = [];




    public function __construct($phone, $mensagem){
        $this->array['phone'] = $phone;
        $this->phone = $phone ;
        $this->array['message'] = $mensagem;

        $this->message = $mensagem; 
        return $this;

    }


   
    public function withLink($link){
        $this->linkUrl = $link ;
        $this->array['linkUrl'] = $link;
        return $this;
    }


    public function withImage($image){
        $this->array['image'] = $image;
        $this->image = $image ; 
        return $this;
    }

    public function withTitle($title){
        $this->array['title'] = $title;
        $this->title = $title; 
        return $this;
    }

    public function withDelay($delay){
        $this->array['delayMessage'] = $delay;
        $this->delayMessage = $delay ; 
        return $this;
    }

    public function withTyping($delay){
        $this->array['delayTyping'] = $delay;
        $this->delayTyping = $delay ;
        return $this;

    }


    public function getData(){
        return $this->array;
    }
    

    
    
    


}