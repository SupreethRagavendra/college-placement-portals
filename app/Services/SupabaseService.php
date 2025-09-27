<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    protected $client;
    protected $url;
    protected $anonKey;
    protected $serviceRoleKey;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false, // For development only
        ]);
        
        $this->url = config('supabase.url');
        $this->anonKey = config('supabase.anon_key');
        $this->serviceRoleKey = config('supabase.service_role_key');
    }

    /**
     * Build common headers for PostgREST requests
     */
    protected function buildHeaders(bool $useServiceRole = false, array $extra = []): array
    {
        $base = [
            'apikey' => $useServiceRole ? $this->serviceRoleKey : $this->anonKey,
            'Authorization' => 'Bearer ' . ($useServiceRole ? $this->serviceRoleKey : $this->anonKey),
        ];

        return array_merge($base, $extra);
    }

    /**
     * Select rows from a table using PostgREST query params
     * $filters example: ['category' => 'eq.Aptitude', 'id' => 'eq.10']
     */
    public function selectFrom(string $table, array $filters = [], array $options = []): array
    {
        $query = [];
        foreach ($filters as $key => $value) {
            $query[$key] = $value;
        }
        if (isset($options['select'])) {
            $query['select'] = $options['select'];
        }
        if (isset($options['order'])) {
            // order: ['column' => 'created_at', 'asc' => false]
            $order = $options['order'];
            $query['order'] = ($order['column'] ?? 'id') . '.' . ((isset($order['asc']) && $order['asc']) ? 'asc' : 'desc');
        }
        if (isset($options['limit'])) {
            $query['limit'] = (string)$options['limit'];
        }
        if (isset($options['offset'])) {
            $query['offset'] = (string)$options['offset'];
        }

        $headers = $this->buildHeaders(true, [
            'Content-Type' => 'application/json',
        ]);

        $response = $this->client->get($this->url . '/rest/v1/' . $table, [
            'headers' => $headers,
            'query' => $query,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Insert one row into a table
     */
    public function insertInto(string $table, array $data): array
    {
        $headers = $this->buildHeaders(true, [
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]);

        $response = $this->client->post($this->url . '/rest/v1/' . $table, [
            'headers' => $headers,
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Update a row by id
     */
    public function updateById(string $table, $id, array $data): array
    {
        $headers = $this->buildHeaders(true, [
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ]);

        $response = $this->client->patch($this->url . '/rest/v1/' . $table, [
            'headers' => $headers,
            'query' => [ 'id' => 'eq.' . $id ],
            'json' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Delete a row by id
     */
    public function deleteById(string $table, $id): void
    {
        $headers = $this->buildHeaders(true, [
            'Content-Type' => 'application/json',
        ]);

        $this->client->delete($this->url . '/rest/v1/' . $table, [
            'headers' => $headers,
            'query' => [ 'id' => 'eq.' . $id ],
        ]);
    }

    /**
     * Register a new user with Supabase Auth
     */
    public function signUp($email, $password, $userData = [])
    {
        try {
            $redirectUrl = config('supabase.redirect_url');
            
            Log::info('Supabase SignUp Request', [
                'email' => $email,
                'redirect_url' => $redirectUrl,
                'user_data' => $userData
            ]);

            $response = $this->client->post($this->url . '/auth/v1/signup', [
                'headers' => [
                    'apikey' => $this->anonKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email' => $email,
                    'password' => $password,
                    'data' => $userData,
                    'email_redirect_to' => $redirectUrl
                ]
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Supabase SignUp Response', [
                'status_code' => $response->getStatusCode(),
                'response_data' => $responseData
            ]);

            return $responseData;
        } catch (RequestException $e) {
            Log::error('Supabase SignUp Error: ' . $e->getMessage(), [
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body',
                'status_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'No status code'
            ]);
            throw new \Exception('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login user with Supabase Auth
     */
    public function signIn($email, $password)
    {
        try {
            $response = $this->client->post($this->url . '/auth/v1/token?grant_type=password', [
                'headers' => [
                    'apikey' => $this->anonKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email' => $email,
                    'password' => $password
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Supabase SignIn Error: ' . $e->getMessage());
            throw new \Exception('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Send password reset email
     */
    public function resetPasswordForEmail($email)
    {
        try {
            $response = $this->client->post($this->url . '/auth/v1/recover', [
                'headers' => [
                    'apikey' => $this->anonKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email' => $email
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Supabase Password Reset Error: ' . $e->getMessage());
            throw new \Exception('Password reset failed: ' . $e->getMessage());
        }
    }

    /**
     * Get user by access token
     */
    public function getUser($accessToken)
    {
        try {
            $response = $this->client->get($this->url . '/auth/v1/user', [
                'headers' => [
                    'apikey' => $this->anonKey,
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ]
            ]);

            $userData = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Supabase GetUser Response', [
                'status_code' => $response->getStatusCode(),
                'user_data' => $userData
            ]);

            return $userData;
        } catch (RequestException $e) {
            Log::error('Supabase GetUser Error: ' . $e->getMessage(), [
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body',
                'status_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'No status code'
            ]);
            throw new \Exception('Failed to get user: ' . $e->getMessage());
        }
    }

    /**
     * Update user metadata
     */
    public function updateUser($accessToken, $userData)
    {
        try {
            $response = $this->client->put($this->url . '/auth/v1/user', [
                'headers' => [
                    'apikey' => $this->anonKey,
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $userData
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Supabase UpdateUser Error: ' . $e->getMessage());
            throw new \Exception('Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Delete user (admin only)
     */
    public function deleteUser($userId)
    {
        try {
            $response = $this->client->delete($this->url . '/auth/v1/admin/users/' . $userId, [
                'headers' => [
                    'apikey' => $this->serviceRoleKey,
                    'Authorization' => 'Bearer ' . $this->serviceRoleKey,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Supabase DeleteUser Error: ' . $e->getMessage());
            throw new \Exception('Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Get all users (admin only)
     */
    public function getAllUsers()
    {
        try {
            $response = $this->client->get($this->url . '/auth/v1/admin/users', [
                'headers' => [
                    'apikey' => $this->serviceRoleKey,
                    'Authorization' => 'Bearer ' . $this->serviceRoleKey,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Supabase GetAllUsers Error: ' . $e->getMessage());
            throw new \Exception('Failed to get users: ' . $e->getMessage());
        }
    }

    /**
     * Update user password (admin only)
     */
    public function updateUserPassword($userId, $newPassword)
    {
        try {
            $response = $this->client->put($this->url . '/auth/v1/admin/users/' . $userId, [
                'headers' => [
                    'apikey' => $this->serviceRoleKey,
                    'Authorization' => 'Bearer ' . $this->serviceRoleKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'password' => $newPassword
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Supabase UpdateUserPassword Error: ' . $e->getMessage());
            throw new \Exception('Failed to update password: ' . $e->getMessage());
        }
    }

    /**
     * Call a Supabase Edge Function
     */
    public function callFunction(string $functionName, array $payload = [], bool $async = false)
    {
        try {
            $headers = [
                'apikey' => $this->anonKey,
                'Authorization' => 'Bearer ' . $this->anonKey,
                'Content-Type' => 'application/json',
            ];

            $options = [
                'headers' => $headers,
                'json' => $payload,
                'timeout' => $async ? 5 : 30, // Shorter timeout for async calls
            ];

            $url = $this->url . '/functions/v1/' . $functionName;

            if ($async) {
                // Return a promise for async calls
                return $this->client->postAsync($url, $options);
            } else {
                // Synchronous call
                $response = $this->client->post($url, $options);
                return json_decode($response->getBody()->getContents(), true);
            }
        } catch (RequestException $e) {
            Log::error('Supabase Edge Function Error: ' . $e->getMessage(), [
                'function_name' => $functionName,
                'payload' => $payload,
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body',
                'status_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'No status code'
            ]);
            
            if ($async) {
                // For async calls, log error but don't throw exception
                return null;
            }
            
            throw new \Exception('Edge function call failed: ' . $e->getMessage());
        }
    }

    /**
     * Send email notification asynchronously
     */
    public function sendStatusEmailAsync(string $studentEmail, string $studentName, string $status, ?string $rejectionReason = null): ?PromiseInterface
    {
        $payload = [
            'student_email' => $studentEmail,
            'student_name' => $studentName,
            'status' => $status,
            'college_name' => config('app.name', 'College Placement Portal')
        ];

        if ($rejectionReason && $status === 'rejected') {
            $payload['rejection_reason'] = $rejectionReason;
        }

        Log::info('Sending status email async', [
            'student_email' => $studentEmail,
            'status' => $status,
            'has_rejection_reason' => !empty($rejectionReason)
        ]);

        return $this->callFunction('send-status-email', $payload, true);
    }

    /**
     * Send email notification synchronously (for testing)
     */
    public function sendStatusEmail(string $studentEmail, string $studentName, string $status, ?string $rejectionReason = null): array
    {
        $payload = [
            'student_email' => $studentEmail,
            'student_name' => $studentName,
            'status' => $status,
            'college_name' => config('app.name', 'College Placement Portal')
        ];

        if ($rejectionReason && $status === 'rejected') {
            $payload['rejection_reason'] = $rejectionReason;
        }

        Log::info('Sending status email sync', [
            'student_email' => $studentEmail,
            'status' => $status,
            'has_rejection_reason' => !empty($rejectionReason)
        ]);

        return $this->callFunction('send-status-email', $payload, false);
    }
}
