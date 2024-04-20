<?php
namespace App\Libs\PushNotification;

class Push {
    //notification title
    private $title;

    //notification message
    private $body;

    //notification image url
    private $icon;

    private $image;

    //notification image url
    private $click_action;

    //notification image url
    private $type;

    //initializing values in this constructor
    function __construct($title, $body, $icon, $image, $click_action, $type) {
        $this->title = $title;
        $this->body = $body;
        $this->icon = $icon;
        $this->image = $image;
        $this->click_action = $click_action;
        $this->type = $type;
    }

    //getting the push notification
    public function getPush() {
        $res = array(
        'title' => $this->title,
        'body' => $this->body,
        'icon' => $this->icon,
        'image' => $this->image,
        'click_action' => $this->click_action,
        'type' => $this->type
        );
        return $res;
    }

}