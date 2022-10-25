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

    /**
     * Config
     */
    protected $config;

    /**
     * Providers
     */
    protected $providers;

    /**
     * SMPP
     */
    protected $smpp;

    /**
     * Transport
     */
    protected $transport;

    /**
     * Constructor
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->providers = $config->get('smpp-client.providers', []);
    }

    /**
     * Send one SMS
     */
    public function sendOne($mobile, $message)
    {
        $this->setup();

        return $this->send($this->sender(), $this->recipient($mobile), $message);
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $mobiles, $message)
    {
        return [$mobiles, $message];
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

    /**
     * SMPP send sms
     */
    protected function send($sender, $recipient, $message)
    {
        $message = $this->gsmEncoder($message);

        if(isset($this->smpp))
            return $this->smpp->sendSMS($sender, $recipient, $message, []);
    }

    /**
     * SMPP setup
     */
    protected function setup()
    {
        if(isset($this->providers))
        {
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
                    die;
                }
            }
        }
        else
        {
            print "File config/smpp-client.php, provider does not exist";
            die;
        }
    }

}
