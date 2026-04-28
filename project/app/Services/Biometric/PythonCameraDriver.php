<?php

namespace App\Services\Biometric;

use Illuminate\Support\Facades\Http;

/**
 * GoF Bridge — ConcreteImplementor
 *
 * Driver que delega a captura ao serviço Python/FastAPI
 * rodando localmente com câmera real via OpenCV + MediaPipe.
 * O Python captura o frame, extrai landmarks e devolve o hash.
 */
class PythonCameraDriver implements CaptureDriver
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int    $timeoutSeconds = 10,
    ) {}

    /**
     * Chama POST /register no Python, que captura um frame da câmera,
     * extrai os landmarks e retorna o hash para ser persistido.
     *
     * @throws CaptureException
     */
    public function capture(int $studentId): array
    {
        try {
            $token = auth()->user()?->createToken('freqid-scanner')->plainTextToken;

            $response = Http::timeout($this->timeoutSeconds)
                ->withToken($token)
                ->post("{$this->baseUrl}/register", [
                    'student_id'    => $studentId,
                    'teacher_token' => $token,
                ]);

            if ($response->failed()) {
                throw new CaptureException(
                    "Python retornou erro {$response->status()}: " . $response->body()
                );
            }

            $data = $response->json();

            if (empty($data['landmark_hash'])) {
                throw new CaptureException('Resposta inválida do serviço de captura.');
            }

            return [
                'landmark_hash' => $data['landmark_hash'],
                'landmarks'     => $data['landmarks'] ?? [],
            ];
        } catch (CaptureException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new CaptureException(
                "Falha ao comunicar com o serviço de câmera: {$e->getMessage()}",
                previous: $e
            );
        }
    }
}
