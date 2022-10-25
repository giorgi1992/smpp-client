<?php

namespace Gko\Smpp;

use GsmEncoder;
use Illuminate\Contracts\Config\Repository;
use SmppAddress;
use SmppClient;
use SMPP as PhpSmpp;
use SmppException;
use SocketTransport;
use SocketTransportException;

class Smpp implements SmppInterface
{

    protected $config;
    protected $providers;
    protected $smpp;
    protected $transport;

    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->providers = $config->get('smpp-client.providers', []);
    }

    /**
     * Send one SMS
     */
    public function sendOne($phone, $message)
    {
        $this->setup();

        $this->send(995558685848, 'Message');

        // $this->smpp->close();
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $phones, $message)
    {
        return [$phones, $message];
    }

    /**
     * SMPP send sms
     */
    protected function send($mobile, $message)
    {
        $message = $this->gsmEncoder($message);
        $sender = $this->sender();
        $recipient = $this->recipient($mobile);

        dd($this->smpp);
        $response = $this->smpp->sendSMS($sender, $recipient, $message, null, PhpSmpp::DATA_CODING_UCS2);

    }

    /**
     * SMPP setup
     */
    protected function setup()
    {
        if($this->providers) {
            foreach ($this->providers as $provider => $config) {
                $transport = new SocketTransport([$config['host']], $config['port']);

                try {
                    $transport->setRecvTimeout($config['timeout']);
                    $transport->debug = $config['debug'];

                    $smpp = new SmppClient($transport);
                    $smpp::$system_type = $config['system_type'];
                    $smpp::$sms_registered_delivery_flag = $config['sms_registered_delivery_flag'];
                    $smpp->debug = $config['debug'];

                    $transport->open();
                    $smpp->bindTransmitter($config['login'], $config['password']);

                    $this->smpp = $smpp;
                    $this->transport = $transport;
                } catch (SmppException | SocketTransportException $e) {
                    print "Provider: {$provider}, Message: {$e->getMessage()}";
                }
            }
        }
        else
            print "File config/smpp-client.php, provider does not exist";
    }

    /**
     * SMPP gsm encoder
     */
    protected function gsmEncoder($message)
    {
        return GsmEncoder::utf8_to_gsm0338($message);
    }

    /**
     * SMPP Sender
     */
    protected function sender()
    {
        foreach ($this->providers as $config)
            return new SmppAddress($config['sender'], $config['source_ton']);
    }

    /**
     * SMPP recipient
     */
    protected function recipient($mobile)
    {
        foreach ($this->providers as $config)
            return new SmppAddress($mobile, $config['destination_ton'], $config['destination_npi']);
    }

}
