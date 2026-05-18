"""
config.py
Centraliza todas as configurações do FreqID Python service.
Valores devem vir de variáveis de ambiente em produção.
"""
import os

# ---------------------------------------------------------------------------
# Laravel integration
# ---------------------------------------------------------------------------
LARAVEL_BASE_URL: str = os.getenv("LARAVEL_BASE_URL", "http://localhost:8000")
LARAVEL_FACE_SCAN_URL: str = f"{LARAVEL_BASE_URL}/api/face-scan"
LARAVEL_FACE_REGISTER_URL: str = f"{LARAVEL_BASE_URL}/api/face-register"
LARAVEL_REQUEST_TIMEOUT: int = int(os.getenv("LARAVEL_REQUEST_TIMEOUT", "5"))

# ---------------------------------------------------------------------------
# Camera
# ---------------------------------------------------------------------------
CAMERA_INDEX: int = int(os.getenv("CAMERA_INDEX", "0"))

# ---------------------------------------------------------------------------
# MediaPipe face mesh
# ---------------------------------------------------------------------------
FACE_MESH_MAX_FACES: int = 1
FACE_MESH_MIN_DETECTION_CONFIDENCE: float = 0.5
FACE_MESH_MIN_TRACKING_CONFIDENCE: float = 0.5

# ---------------------------------------------------------------------------
# Stream
# ---------------------------------------------------------------------------
MJPEG_JPEG_QUALITY: int = int(os.getenv("MJPEG_JPEG_QUALITY", "80"))

# ---------------------------------------------------------------------------
# Landmark hashing
# ---------------------------------------------------------------------------
LANDMARK_ROUND_DECIMALS: int = 6
