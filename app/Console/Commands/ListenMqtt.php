<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\SensorMetric;
use PhpMqtt\Client\Facades\MQTT;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class ListenMqtt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:listen-mqtt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen to MQTT messages from Pico W sensors and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Using the WebSocket address provided by the user
        $server   = 'broker.hivemq.com';
        $port     = 1883; // We use 1883 (TCP) for the PHP server as it is more stable than WS for background tasks
        $clientId = 'viny-deskline-server-' . uniqid();

        $mqtt = new MqttClient($server, $port, $clientId);

        $connectionSettings = (new ConnectionSettings)
            ->setKeepAliveInterval(60)
            ->setLastWillTopic('viny-deskline/status')
            ->setLastWillMessage('offline')
            ->setLastWillQualityOfService(1);

        $this->info("Connecting to MQTT broker at $server:$port...");

        try {
            $mqtt->connect($connectionSettings, true);
        } catch (\Exception $e) {
            $this->error("Could not connect to MQTT broker: " . $e->getMessage());
            return 1;
        }

        $this->info("Connected! Subscribing to 'pico/sensors'...");

        $lastSaved = 0;
        $interval = 60; // Save at most once per minute

        $mqtt->subscribe('pico/sensors', function ($topic, $message) use (&$lastSaved, $interval) {
            $this->info("Received message on topic [$topic]: $message");

            $now = time();
            if ($now - $lastSaved < $interval) {
                $this->line("Skipping save (throttled)");
                return;
            }

            try {
                $data = json_decode($message, true);
                if ($data && isset($data['temperature'], $data['humidity'], $data['light'])) {
                    SensorMetric::create([
                        'temperature' => $data['temperature'],
                        'humidity'    => $data['humidity'],
                        'light'       => $data['light'],
                        'recorded_at' => now(),
                    ]);
                    $lastSaved = $now;
                    $this->info("✓ Sensor metrics stored in DB");
                } else {
                    $this->warn("✗ Invalid data format received");
                }
            } catch (\Exception $e) {
                $this->error("Error saving metric: " . $e->getMessage());
            }
        }, 0);

        $mqtt->loop(true);

        return 0;
    }
}
