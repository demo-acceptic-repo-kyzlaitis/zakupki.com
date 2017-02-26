<?php
{
    $environment = 'dev';
    $envPath = realpath(__DIR__ . '/env-app/');

    //Check server hostname
    //$hostname = gethostname();
    $hostname = 'zakupki-dev';
    switch ($hostname) {
        case 'zakupki-test':
            $environment = 'test';

            break;
        case 'zakupki-stage':
            $environment = 'st';

            break;
        case 'zakupki-prod':
            // we are on production machine where also temp version reside

            // NOTE: we assume that any unknown environment is production, so no need to set it here implicitly
            $environment = 'prod';
            break;
        case 'zakupki-dev':
            $environment = 'dev';
            break;
        default;
            $environment = 'dev';
            break;
    }

    //Check site host
    if (isset($_SERVER['HTTP_HOST'])) {
        switch ($_SERVER['HTTP_HOST']) {
            case 'dev.zakupki.com.ua':
                $environment = 'dev';
                break;
            case 'ts.zakupki.com.ua':
                $environment = 'test';
                break;
            case 'st.zakupki.com.ua':
                $environment = 'st';
                break;
            case 'zakupki.com.ua':
                $environment = 'prod';
                break;
        }

    }

    if (!$environment) {
        throw new \Exception('Environment not set');
    }

    $envPath = $envPath.'/'.$environment;
    $envFiles = [];
    //Scan environments folder and load all configs
    foreach (scandir($envPath) as $filename) {
        if (in_array($filename, ['.', '..'])) {
            continue;
        }

        $envFiles[] = $filename;
    }

    if (!$envFiles) {
        throw new \Exception('Environment config files not found');
    }

    // Initialise Dotenv for manager configs
    foreach ($envFiles as $envFile) {
        \Dotenv::load($envPath, $envFile);
    }
}