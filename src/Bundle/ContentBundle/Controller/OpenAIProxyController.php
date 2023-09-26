<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OpenAIProxyController
{
    public function openaiProxy(Request $request): JsonResponse
    {
        // Retrieve user input from the request
        $jsonContent = $request->getContent();
        $requestData = json_decode($jsonContent, true);

        // Retrieve the prompt from the JSON data
        $userInput = $requestData['prompt'];

        // Make a request to the OpenAI API
        $apiKey = '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';
        $organisation = 'org-JI8kEig0TfIuExyR18SAAm4Y';
        $openaiUrl = 'https://api.openai.com/v1/chat/completions';
        $content = 'Kan je mij een intro geven van het onderstaande artikel, het mag maximaal 200 karakters bevatten. De intro wordt gebruikt om lezers te motiveren het hele artikel te gaan lezen: ' . $userInput;

        $client = new Client();
        $response = $client->post($openaiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
                'OpenAI-Organization' => $organisation,
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
                'temperature' => 0.7, // Adjust as needed
            ],
        ]);

        // Get the response from OpenAI API
        $responseData = json_decode($response->getBody(), true);

        // Return the response to the client
        return new JsonResponse($responseData);
    }
}
