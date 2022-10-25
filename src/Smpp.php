<?php

namespace Gko\Smpp;

use Illuminate\Contracts\Config\Repository;
use SmppClient;
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
        return [$phone, $message];
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $phones, $message)
    {
        return [$phones, $message];
    }

    /**
     * SMPP setup
     */
    protected function setup()
    {
        if($this->providers)
            foreach ($this->providers as $provider => $config)
            {
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
                catch (SocketTransportException $e)
                {
                    if($config['debug'])
                        print $e;

                    print "Provider: {$provider}, Message: {$e->getMessage()}";
                }
            }

        print "File config/smpp-client.php, provider does not exist";
    }

}
