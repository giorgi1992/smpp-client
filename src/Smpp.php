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
     * Transport
     */
    protected $transport;

    /**
     * SMPP
     */
    protected $smpp;

    /**
     * Providers, list
     */
    protected $providers;

    /**
     * Config, provider
     */
    protected $provider;

    /**
     * Config
     */
    protected $config;

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

        $this->transport = new SocketTransport([$this->config['host']], $this->config['port']);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if($this->smpp)
        {
            $this->smpp->close();
            $this->transport->close();
        }
    }

    /**
     * Send one SMS
     */
    public function sendOne($phone, $message)
    {
        $this->transmitter();

        return $this->send($this->sender(), $this->recipient($phone), $message);
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $phones, $message)
    {
        return 'sendBulk';
    }

    /**
     * Delivery report
     */
    public function deliveryReport()
    {
        return $this->receiver();
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
     * SMPP setup
     */
    protected function transmitter()
    {
        try
        {
            $this->transport->setRecvTimeout($this->config['timeout']);
            $smpp = new SmppClient($this->transport);
            $smpp::$system_type = $this->config['system_type'];
            $smpp::$sms_registered_delivery_flag = $this->config['sms_registered_delivery_flag'];
            $smpp->debug = $this->config['debug'];
            $this->transport->debug = $this->config['debug'];
            $this->transport->open();
            $smpp->bindTransmitter($this->config['login'], $this->config['password']);

            $this->smpp = $smpp;
        }
        catch (Exception $e) {
            exit("Provider: {$this->provider}, Message: {$e->getMessage()}.");
        }
    }

    /**
     * SMPP send sms
     */
    protected function send($sender, $recipient, $message)
    {
        return $this->smpp->sendSMS($sender, $recipient, $this->gsmEncoder($message), []);
    }

    /**
     * Delivery report
     */
    protected function receiver()
    {
        try
        {
            $this->transport->setRecvTimeout($this->config['timeout']);
            $smpp = new SmppClient($this->transport);
            $this->transport->open();
            $smpp->bindReceiver($this->config['login'], $this->config['password']);

            return $smpp->readSMS();
        }
        catch (Exception $e) {
            exit("Provider: {$this->provider}, Message: {$e->getMessage()}.");
        }
    }

}
