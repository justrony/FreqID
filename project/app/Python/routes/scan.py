"""
routes/scan.py
Endpoint /scan — captura frame, extrai landmarks e registra presença via Laravel.
"""
import logging

import cv2
import numpy as np
from fastapi import APIRouter, HTTPException, Request

from ..config import LANDMARK_ROUND_DECIMALS, LARAVEL_FACE_SCAN_URL
from ..hashing import generate_landmark_hash
from ..http_client import LaravelRequestError, send_to_laravel
from ..scanner import FreqIDScanner
from ..schemas import ScanRequest

logger = logging.getLogger(__name__)
router = APIRouter(tags=["scan"])


@router.post("/scan")
def scan(req: ScanRequest, request: Request) -> dict:
    """
    Captura um frame, extrai landmarks, gera hash e envia ao Laravel.
    O Laravel identifica o aluno pelo hash e registra a presença na turma (class_id).
    """
    cap: cv2.VideoCapture | None = request.app.state.cap
    scanner: FreqIDScanner = request.app.state.scanner

    landmarks_array = _capture_landmarks(cap, scanner)
    landmark_hash = generate_landmark_hash(landmarks_array)
    landmarks_list: list = np.round(landmarks_array, decimals=LANDMARK_ROUND_DECIMALS).tolist()

    try:
        laravel_response = send_to_laravel(
            LARAVEL_FACE_SCAN_URL,
            {
                "landmark_hash": landmark_hash,
                "class_id": req.class_id,
                "landmarks": landmarks_list,
            },
        )
    except LaravelRequestError as exc:
        logger.error("Falha ao enviar scan para Laravel", extra={"class_id": req.class_id, "exc": str(exc)})
        raise HTTPException(status_code=502, detail="Erro de comunicação com o servidor") from exc

    logger.info("Presença registrada via scan", extra={"class_id": req.class_id, "hash": landmark_hash})
    return {
        "landmark_hash": landmark_hash,
        "landmarks_count": len(landmarks_list),
        "laravel_response": laravel_response,
    }


def _capture_landmarks(
    cap: cv2.VideoCapture | None,
    scanner: FreqIDScanner,
) -> np.ndarray:
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
