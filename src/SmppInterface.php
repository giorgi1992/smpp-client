<?php

namespace Gk\Smpp;

interface SmppInterface
{
    /**
     * Send a one SMS.
     *
     * @param int $phone
     * @param string $message
     *
     * @return string
     */
    public function sendOne($phone, $message);

    /**
     * Send bulk SMS.
     *
     * @param array $phones
     * @param string $message
     *
     * @return array
     */
    public function sendBulk(array $phones, $message);
}
