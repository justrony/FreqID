<?php

namespace App\Services\Biometric;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PhotoUploadDriver
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int    $timeoutSeconds = 15,
    ) {}

    /**
     * Envia a foto ao Python e retorna os dados biométricos extraídos.
     *
     * @return array{ landmark_hash: string, landmarks: array<array<float>> }
     *
     * @throws CaptureException
     */
    public function captureFromPhoto(int $studentId, UploadedFile $photo): array
    {
        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->attach(
                    'photo',
                    $photo->getContent(),
                    $photo->getClientOriginalName(),
                    ['Content-Type' => $photo->getMimeType()],
                )
                ->post("{$this->baseUrl}/enroll-from-image");

            if ($response->failed()) {
                $detail = $response->json('detail', $response->body());
                throw new CaptureException(
                    "Python retornou erro {$response->status()}: {$detail}"
                );
            }

            $data = $response->json();

            if (empty($data['landmark_hash'])) {
                throw new CaptureException('Resposta inválida do serviço de processamento de imagem.');
            }

            Log::info('[PhotoUploadDriver] Landmarks extraídos via upload', [
                'student_id'       => $studentId,
                'landmark_hash'    => $data['landmark_hash'],
                'landmarks_count'  => $data['landmarks_count'] ?? null,
            ]);

            return [
                'landmark_hash' => $data['landmark_hash'],
                'landmarks'     => $data['landmarks'] ?? [],
            ];

        } catch (CaptureException $e) {
            throw $e;
        } catch (RequestException $e) {
            throw new CaptureException(
                "Falha ao comunicar com o serviço de imagem: {$e->getMessage()}",
                previous: $e
            );
        } catch (\Throwable $e) {
            throw new CaptureException(
                "Erro inesperado no processamento da imagem: {$e->getMessage()}",
                previous: $e
            );
        }
    }
}
