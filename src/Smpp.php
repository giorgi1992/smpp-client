<?php

namespace Gk\Smpp;

class Smpp implements SmppInterface {
    public function sendOne($phone, $message)
    {
        return $phone.$message;
    }

    public function sendBulk(array $phones, $message)
    {
        return $phones.$message;
    }

}
