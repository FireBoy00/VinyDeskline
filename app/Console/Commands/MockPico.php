<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MockPico extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mock-pico {--temp=23.5} {--humid=52} {--light=610} {--loop} {--interval=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate a Pico W sending sensor data via MQTT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server   = 'broker.hivemq.com';
        $port     = 1883;
        $clientId = 'viny-deskline-mock-' . uniqid();

        $mqtt = new MqttClient($server, $port, $clientId);

        $this->info("Connecting to MQTT broker to send mock data...");

        try {
            $mqtt->connect();
            
            $doLoop = $this->option('loop');
            $sleepInterval = (int) $this->option('interval');

            do {
                $data = [
                    'temperature' => (float) $this->option('temp') + (rand(-10, 10) / 10),
                    'humidity'    => (float) $this->option('humid') + (rand(-20, 20) / 10),
                    'light'       => (float) $this->option('light') + rand(-50, 50),
                ];

                $message = json_encode($data);
                $topic = 'pico/sensors';

                $mqtt->publish($topic, $message, 0);
                $this->info("[" . now()->format('H:i:s') . "] ✓ Published to [$topic]: $message");
                
                if ($doLoop) {
                    sleep($sleepInterval);
                }
            } while ($doLoop);

            $mqtt->disconnect();
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
