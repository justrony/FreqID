<?php

namespace App\Services\Biometric;

use Illuminate\Support\Facades\Http;

class PythonCameraDriver implements CaptureDriver
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly int    $timeoutSeconds = 10,
    ) {}

    /**
     * @throws CaptureException
     */
    public function capture(int $studentId): array
    {
        $user      = auth()->user();
        $tokenObj  = $user?->createToken('freqid-scanner-ephemeral');
        $plainText = $tokenObj?->plainTextToken;

        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->withToken($plainText)
                ->post("{$this->baseUrl}/register", [
                    'student_id'    => $studentId,
                    'teacher_token' => $plainText,
                ]);

            // Revoga imediatamente — token de uso único
            $tokenObj?->accessToken?->delete();

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
            $tokenObj?->accessToken?->delete(); // garante revogação mesmo em erro
            throw $e;
        } catch (\Throwable $e) {
            $tokenObj?->accessToken?->delete();
            throw new CaptureException(
                "Falha ao comunicar com o serviço de câmera: {$e->getMessage()}",
                previous: $e
            );
        }
    }
}
