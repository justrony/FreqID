"""
routes/scan.py
Endpoints de captura facial:
  - POST /scan          : captura + envia ao Laravel (fluxo legado)
  - POST /capture-hash  : captura e retorna apenas o hash (novo fluxo via proxy Laravel)
"""
import logging

import cv2
import numpy as np
from fastapi import APIRouter, HTTPException, Request
from pydantic import BaseModel
from scipy.spatial import procrustes

from ..config import LANDMARK_ROUND_DECIMALS, LARAVEL_FACE_SCAN_URL
from ..hashing import generate_landmark_hash
from ..http_client import LaravelRequestError, send_to_laravel
from ..scanner import FreqIDScanner
from ..schemas import ScanRequest, MatchRequest

logger = logging.getLogger(__name__)
router = APIRouter(tags=["scan"])


@router.post("/scan")
def scan(req: ScanRequest, request: Request) -> dict:
    """
    Captura um frame, extrai landmarks, gera hash e envia ao Laravel.
    O Laravel identifica o aluno pelo hash e registra a presença na turma (class_id).
    Mantido para compatibilidade com o fluxo legado.
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


@router.post("/capture-hash")
def capture_hash(request: Request) -> dict:
    """
    Captura um frame da câmera, extrai landmarks e retorna os landmarks (além do hash).
    NÃO envia para o Laravel — o chamador (proxy Laravel) cuida disso.

    Usado pelo fluxo: Browser → Laravel POST /frequencia/capturar → Python POST /capture-hash
    """
    cap: cv2.VideoCapture | None = request.app.state.cap
    scanner: FreqIDScanner = request.app.state.scanner

    landmarks_array = _capture_landmarks(cap, scanner)
    landmark_hash = generate_landmark_hash(landmarks_array)
    landmarks_list: list = np.round(landmarks_array, decimals=LANDMARK_ROUND_DECIMALS).tolist()

    logger.info("Landmarks capturados via /capture-hash", extra={"hash": landmark_hash})
    return {
        "landmark_hash": landmark_hash,
        "landmarks_count": int(landmarks_array.shape[0]),
        "landmarks": landmarks_list,
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


@router.post("/match")
def match_face(req: MatchRequest) -> dict:
    """
    Compara os landmarks recém capturados com a lista de rostos salvos usando Análise de Procrustes.
    A Análise de Procrustes encontra a melhor transformação (translação, escala e rotação)
    para sobrepor as duas matrizes e retorna o erro (disparity).
    """
    if not req.registered_faces:
        raise HTTPException(status_code=400, detail="Nenhum rosto registrado fornecido para comparação.")

    live_matrix = np.array(req.live_landmarks)
    if live_matrix.shape != (478, 3):
        raise HTTPException(status_code=400, detail="live_landmarks deve ter shape (478, 3).")

    best_face_id = None
    min_disparity = float('inf')

    for face in req.registered_faces:
        face_matrix = np.array(face.landmarks)
        if face_matrix.shape != (478, 3):
            continue

        try:
            # procrustes retorna: mt1, mt2, disparity
            _, _, disparity = procrustes(live_matrix, face_matrix)
            
            if disparity < min_disparity:
                min_disparity = disparity
                best_face_id = face.face_id
        except Exception as e:
            logger.warning(f"Erro no procrustes para face_id {face.face_id}: {str(e)}")
            continue

    logger.info("Match via Procrustes", extra={"best_face_id": best_face_id, "disparity": min_disparity})

    return {
        "best_face_id": best_face_id,
        "disparity": min_disparity,
    }
