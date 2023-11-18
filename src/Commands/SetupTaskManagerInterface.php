<?php

namespace Wyxos\ZephyrUI\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetupTaskManagerInterface extends Command
{
    protected $signature = 'zephyr:setup';
    protected $description = 'Setup the Task Manager Interface';

    public function handle()
    {
        $email = $this->ask('Please enter your email');
        $password = $this->secret('Please enter your password');

        // Authenticate and get token
        $response = Http::post('https://zephyr.test/api/authenticate', [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response->successful()) {
            $token = $response->json()['token'];
            $this->info('Authentication successful! Token saved.');

            // Fetch and select project
            $projectToken = $this->selectProject($token);

            // Store the project token in the .env file
            if ($projectToken) {
                $this->storeTokenInEnv('PROJECT_TOKEN', $projectToken);
                $this->info('Project token stored successfully.');
            }
        } else {
            $this->error('Authentication failed. Please check your credentials and try again.');
        }
    }

    protected function selectProject($token)
    {
        $response = Http::withToken($token)->get('https://zephyr.test/api/projects');

        if ($response->successful()) {
            $projects = $response->json()['query']['items'];
            $projectNames = array_column($projects, 'name');
            $selectedProjectName = $this->choice('Select a project:', $projectNames);
            $selectedProjectId = $projects[array_search($selectedProjectName, $projectNames)]['id'];

            // Make an API call to generate a token for the selected project
            $tokenResponse = Http::withToken($token)->post("https://zephyr.test/api/projects/{$selectedProjectId}/token");

            if ($tokenResponse->successful()) {
                return $tokenResponse->json()['token'];
            } else {
                $this->error('Failed to generate project token.');
                return null;
            }
        } else {
            $this->error('Failed to fetch projects.');
            return null;
        }
    }

    protected function storeTokenInEnv($key, $value)
    {
        $envPath = app()->environmentFilePath();
        $envContent = file_get_contents($envPath);

        // Update or append the token value in the .env file
        $keyPattern = preg_quote("{$key}=", '/');
        if (preg_match("/^{$keyPattern}/m", $envContent)) {
            $envContent = preg_replace("/^{$keyPattern}.*/m", "{$key}={$value}", $envContent);
        } else {
            $envContent .= PHP_EOL . "{$key}={$value}" . PHP_EOL;
        }

        file_put_contents($envPath, $envContent);
    }
}
