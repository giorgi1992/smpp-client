<?php

namespace Gko\Smpp;

use Exception;
use GsmEncoder;
use Illuminate\Contracts\Config\Repository;
use SmppAddress;
use SmppClient;
use SMPP as PhpSmpp;
use SocketTransport;

class Smpp implements SmppInterface
{

    /**
     * SMPP
     */
    protected $smpp;

    /**
     * Config
     */
    protected $config;

    /**
     * Providers, list
     */
    protected $providers;

    /**
     * Config, provider
     */
    protected $provider;

    /**
     * Transport
     */
    protected $transport;

    /**
     * Constructor
     */
    public function __construct(Repository $config)
    {
        $config = $config->get('smpp-client', $config->get('smpp-client-default'));
        $this->providers = $config['providers'];
        $this->provider = $config['default_provider'];

        array_key_exists($this->provider, $this->providers)
            ? $this->config = $this->providers[$this->provider]
            : exit("Incorrect provider configuration.");
    }

    /**
     * Send one SMS
     */
    public function sendOne($phone, $message)
    {
        $this->setup();

        $this->delivery();

        return $this->send($this->sender(), $this->recipient($phone), $message);
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $phones, $message)
    {
        return [$phones, $message];
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
        return new SmppAddress($this->config['sender'], PhpSmpp::TON_ALPHANUMERIC);
    }

    /**
     * SMPP recipient
     */
    protected function recipient($mobile)
    {
        return new SmppAddress($mobile, PhpSmpp::TON_INTERNATIONAL, PhpSmpp::NPI_E164);
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
        $transport = new SocketTransport([$this->config['host']], $this->config['port']);

        try
        {
            $transport->setRecvTimeout($this->config['timeout']);

            $smpp = new SmppClient($transport);
            $smpp::$system_type = $this->config['system_type'];
            $smpp::$sms_registered_delivery_flag = $this->config['sms_registered_delivery_flag'];
            $smpp->debug = $this->config['debug'];

            $transport->debug = $this->config['debug'];
            $transport->open();

            $smpp->bindTransmitter($this->config['login'], $this->config['password']);

            $this->smpp = $smpp;
            $this->transport = $transport;
        }
        catch (Exception $e) {
            exit("Provider: {$this->provider}, Message: {$e->getMessage()}.");
        }
    }

    public function delivery()
    {
        $this->transport->open();
        $this->transport->bindReceiver($this->config['login'], $this->config['password']);

        $sms = $this->smpp->readSMS();
        dd($sms);
    }

}
