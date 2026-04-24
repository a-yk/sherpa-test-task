<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FileDownloader
{
    const HTTP_STATUS_OK = 200;

    public function __construct(
        private HttpClientInterface $httpClient,
    ) {}

    public function downloadFile(string $url, string $savePath): bool
    {
        $response = $this->httpClient->request('GET', $url);

        if (self::HTTP_STATUS_OK !== $response->getStatusCode()) {
            return false;
        }

        $fileHandler = fopen($savePath, 'w');
        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }

        fclose($fileHandler);

        return true;
    }
}
