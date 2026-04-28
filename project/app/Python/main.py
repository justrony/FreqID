import cv2
import hashlib
import json
import numpy as np
import mediapipe as mp
import requests
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import StreamingResponse
from contextlib import asynccontextmanager
from pydantic import BaseModel


LARAVEL_URL = "http://localhost:8000/api/face-scan"
LARAVEL_REGISTER_URL = "http://localhost:8000/api/face-register"

# Drawing utilities para sobrepor os landmarks no frame ao vivo
mp_drawing = mp.solutions.drawing_utils
mp_drawing_styles = mp.solutions.drawing_styles


class FreqIDScanner:
    def __init__(self):
        self.mp_face_mesh = mp.solutions.face_mesh
        self.face_mesh = self.mp_face_mesh.FaceMesh(
            static_image_mode=False,
            max_num_faces=1,
            refine_landmarks=True,
            min_detection_confidence=0.5,
            min_tracking_confidence=0.5,
        )

    def extract_landmarks(self, frame):
        """Retorna (np.array de landmarks, raw_landmarks) ou (None, None)."""
        rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        results = self.face_mesh.process(rgb_frame)

        if results.multi_face_landmarks:
            for face_landmarks in results.multi_face_landmarks:
                landmarks = []
                for lm in face_landmarks.landmark:
                    landmarks.append([lm.x, lm.y, lm.z])
                return np.array(landmarks), face_landmarks

        return None, None

    def annotate_frame(self, frame, face_landmarks):
        """Desenha a malha facial (landmarks + contornos) sobre o frame."""
        mp_drawing.draw_landmarks(
            image=frame,
            landmark_list=face_landmarks,
            connections=mp.solutions.face_mesh.FACEMESH_TESSELATION,
            landmark_drawing_spec=None,
            connection_drawing_spec=mp_drawing_styles.get_default_face_mesh_tesselation_style(),
        )
        mp_drawing.draw_landmarks(
            image=frame,
            landmark_list=face_landmarks,
            connections=mp.solutions.face_mesh.FACEMESH_CONTOURS,
            landmark_drawing_spec=None,
            connection_drawing_spec=mp_drawing_styles.get_default_face_mesh_contours_style(),
        )
        return frame


def generate_landmark_hash(landmarks: np.ndarray) -> str:
    """Serializa os landmarks arredondados em JSON e retorna o SHA-256 hex."""
    rounded = np.round(landmarks, decimals=6).tolist()
    payload = json.dumps(rounded, sort_keys=True)
    return hashlib.sha256(payload.encode("utf-8")).hexdigest()


def send_to_laravel(
    url: str,
    payload: dict,
    bearer_token: str | None = None,
) -> dict | None:
    """Envia dados para um endpoint Laravel via POST."""
    headers = {"Accept": "application/json"}
    if bearer_token:
        headers["Authorization"] = f"Bearer {bearer_token}"
    try:
        response = requests.post(
            url,
            json=payload,
            headers=headers,
            timeout=5,
        )
        response.raise_for_status()
        return response.json()
    except requests.RequestException as exc:
        print(f"[FreqID] Erro ao enviar para Laravel: {exc}")
        return None


scanner = FreqIDScanner()
cap = None


@asynccontextmanager
async def lifespan(application: FastAPI):
    global cap
    cap = cv2.VideoCapture(0)
    yield
    if cap is not None:
        cap.release()


app = FastAPI(title="FreqID Scanner", version="1.0.0", lifespan=lifespan)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)


class ScanRequest(BaseModel):
    class_id: int


class RegisterRequest(BaseModel):
    student_id: int
    teacher_token: str


@app.get("/health")
def health():
    return {"status": "ok"}


def _generate_mjpeg():
    """
    Gerador para MJPEG stream.
    Lê frames da câmera continuamente, anota com os landmarks do MediaPipe
    (se detectado) e yield cada frame como JPEG boundary.
    """
    while True:
        if cap is None or not cap.isOpened():
            break

        success, frame = cap.read()
        if not success:
            break

        # Tentar detectar e anotar o rosto em tempo real
        _, face_landmarks = scanner.extract_landmarks(frame)
        if face_landmarks is not None:
            frame = scanner.annotate_frame(frame, face_landmarks)
            # Indicador visual: borda verde quando rosto detectado
            h, w = frame.shape[:2]
            cv2.rectangle(frame, (0, 0), (w - 1, h - 1), (0, 200, 0), 3)
            cv2.putText(
                frame, "Rosto detectado",
                (10, 30), cv2.FONT_HERSHEY_SIMPLEX,
                0.8, (0, 200, 0), 2, cv2.LINE_AA,
            )
        else:
            # Borda laranja quando sem rosto
            h, w = frame.shape[:2]
            cv2.rectangle(frame, (0, 0), (w - 1, h - 1), (0, 140, 255), 3)
            cv2.putText(
                frame, "Posicione o rosto na camera",
                (10, 30), cv2.FONT_HERSHEY_SIMPLEX,
                0.7, (0, 140, 255), 2, cv2.LINE_AA,
            )

        # Encode como JPEG e yield no formato multipart
        ok, buffer = cv2.imencode(".jpg", frame, [cv2.IMWRITE_JPEG_QUALITY, 80])
        if not ok:
            continue

        yield (
            b"--frame\r\n"
            b"Content-Type: image/jpeg\r\n\r\n"
            + buffer.tobytes()
            + b"\r\n"
        )


@app.get("/stream")
def stream():
    """
    Endpoint MJPEG — conecte diretamente num <img src="http://localhost:8001/stream">.
    Exibe o feed da câmera com landmarks do MediaPipe sobrepostos em tempo real.
    """
    return StreamingResponse(
        _generate_mjpeg(),
        media_type="multipart/x-mixed-replace; boundary=frame",
    )


@app.post("/scan")
def scan(req: ScanRequest):
    """
    Captura um frame, extrai landmarks, gera hash e envia ao Laravel.
    O Laravel identifica o aluno pelo hash e registra a presença na turma (class_id).
    """
    landmarks_array = _capture_landmarks()

    landmark_hash = generate_landmark_hash(landmarks_array)
    landmarks_list = np.round(landmarks_array, decimals=6).tolist()

    laravel_response = send_to_laravel(LARAVEL_URL, {
        "landmark_hash": landmark_hash,
        "class_id":      req.class_id,
        "landmarks":     landmarks_list,
    })

    return {
        "landmark_hash":  landmark_hash,
        "landmarks_count": len(landmarks_list),
        "laravel_response": laravel_response,
    }


@app.post("/register")
def register(req: RegisterRequest):
    """
    Captura um frame, extrai landmarks, gera hash e cadastra o rosto do aluno.
    teacher_token: Bearer token do professor autenticado no Laravel (via Sanctum).
    O Laravel valida se o professor tem acesso à escola/turma do aluno antes de cadastrar.
    """
    landmarks_array = _capture_landmarks()

    landmark_hash = generate_landmark_hash(landmarks_array)
    landmarks_list = np.round(landmarks_array, decimals=6).tolist()

    laravel_response = send_to_laravel(
        LARAVEL_REGISTER_URL,
        {
            "student_id":    req.student_id,
            "landmark_hash": landmark_hash,
            "landmarks":     landmarks_list,
        },
        bearer_token=req.teacher_token,
    )

    return {
        "student_id":      req.student_id,
        "landmark_hash":   landmark_hash,
        "landmarks_count": len(landmarks_list),
        "laravel_response": laravel_response,
    }


def _capture_landmarks() -> np.ndarray:
    """Captura frame da câmera e extrai landmarks. Levanta HTTPException em caso de falha."""
    if cap is None or not cap.isOpened():
        raise HTTPException(status_code=503, detail="Câmera não disponível")

    success, frame = cap.read()
    if not success:
        raise HTTPException(status_code=500, detail="Falha ao ler frame da câmera")

    landmarks_array, _ = scanner.extract_landmarks(frame)

    if landmarks_array is None:
        raise HTTPException(status_code=404, detail="Nenhum rosto detectado no frame")

    return landmarks_array
