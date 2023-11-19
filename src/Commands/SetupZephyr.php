<?php

namespace Wyxos\ZephyrUI\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetupZephyr extends Command
{
    protected $signature = 'zephyr:setup';
    protected $description = 'Setup Zephyr UI';

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
            $this->info('Authentication successful!');

            // Fetch and select project
            $projectToken = $this->selectProject($token);

            // Store the project token in the .env file
            if ($projectToken) {
                $this->storeTokenInEnv('ZEPHYR_TOKEN', $projectToken);
                $this->info('Project token stored successfully.');
            }
        } else {
            $this->error('Authentication failed. Please check your credentials and try again.');
        }
    }

    protected function selectProject($token)
    {
        $response = Http::withToken($token)->get('https://zephyr.test/api/workspace/projects');

        if ($response->successful()) {
            $projects = $response->json()['query']['items'];
            $projectNames = array_column($projects, 'name');

            // Add "New" option
            $projectNames[] = 'New';

            $selectedProjectName = $this->choice('Select a project:', $projectNames);

            if ($selectedProjectName === 'New') {
                // Prompt for new project name, default to app name
                $defaultName = config('app.name');
                $newProjectName = $this->ask('Enter the name of the new project', $defaultName);

                // Create a new project
                return $this->createNewProject($token, $newProjectName);
            }

            $selectedProjectId = $projects[array_search($selectedProjectName, $projectNames)]['id'];

            // Make an API call to generate a token for the selected project
            $tokenResponse = Http::withToken($token)->post("https://zephyr.test/api/workspace/projects/{$selectedProjectId}/token");

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

    protected function createNewProject($token, $projectName)
    {
        $response = Http::withToken($token)->post('https://zephyr.test/api/workspace/projects/store', [
            'name' => $projectName,
        ]);

        if ($response->successful()) {
            return $response->json()['token'];
        } else {
            $this->error('Failed to create new project.');
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
