<?php

namespace Gko\Smpp;

class Smpp implements SmppInterface {

    /**
     * Send a one SMS.
     */
    public function sendOne($phone, $message)
    {
        return $phone.$message;
    }

    /**
     * Send bulk SMS.
     */
    public function sendBulk(array $phones, $message)
    {
        return $phones.$message;
    }

}
