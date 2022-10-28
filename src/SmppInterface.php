<?php

namespace Gko\Smpp;

interface SmppInterface
{

    /**
     * Send a one SMS.
     */
    public function sendOne($phone, $message);

    /**
     * Send bulk SMS.
     */
    public function sendBulk(array $phones, $message);

    /**
     * Delivery report.
     */
    public function deliveryReport();

}
