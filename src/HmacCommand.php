<?php

namespace Amelia\Rememberable;

use Illuminate\Console\Command;

class HmacCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rememberable:hmac {--key=REMEMBERABLE_KEY}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the HMAC key for rememberable';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $key = $this->generateRandomKey();

        $this->setKeyInEnvironmentFile($key);

        $this->laravel['config']['rememberable.key'] = $key;

        $this->info("HMAC key [$key] set successfully.");
    }

    /**
     * Set the application key in the environment file.
     *
     * @param  string  $key
     * @return void
     */
    protected function setKeyInEnvironmentFile($key)
    {
        $name = $this->option('key');

        $file = file_get_contents($filename = $this->laravel->environmentFilePath());

        // we want to insert it, if we haven't already.
        if (! str_contains($file, $name)) {
            file_put_contents($filename, $file . "\n{$name}={$key}");

            return;
        }

        file_put_contents($filename, str_replace(
            $name . '=' . $this->laravel['config']['rememberable.key'],
            $name . '=' . $key,
            $file
        ));
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function generateRandomKey()
    {
        return base64_encode(random_bytes(32));
    }
}
