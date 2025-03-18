<?php

namespace CleanTalkCheck\CleanTalk;

class CleantalkVerdict
{
    public $allowed = true;
    public $comment = '';
    public $error = '';
    public $request_link = null;

    public function getJSON()
    {
        return json_encode($this);
    }

    public function getArray()
    {
        return json_decode($this->getJSON(), true);
    }
}
