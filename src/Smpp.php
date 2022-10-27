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
    protected $provider = 'default';

    /**
     * Transport
     */
    protected $transport;

    /**
     * Constructor
     */
    public function __construct(Repository $config)
    {
        if($config->get('smpp-client'))
        {
            $this->providers = $config->get('smpp-client.providers', []);
            $this->provider = $config->get('smpp-client.default_provider', 'example');

            if (array_key_exists($this->provider, $this->providers))
                $this->config = $this->providers[$this->provider];
        }
        else
        {
            print 'Config file config/smpp-client.php does not exists.';
            exit;
        }
    }

    /**
     * Send one SMS
     */
    public function sendOne($phone, $message)
    {
        $this->setup();

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
        return new SmppAddress('Sender', PhpSmpp::TON_ALPHANUMERIC);
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
        if(isset($this->config))
        {
            $config = $this->config;
            $transport = new SocketTransport([$config['host']], $config['port']);

            try
            {
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
            }
            catch (Exception $e) {
                print "Provider: {$this->provider}, Message: {$e->getMessage()}.";
                exit;
            }
        }
        else
        {
            print "Incorrect provider parameters.";
            exit;
        }
    }

}
