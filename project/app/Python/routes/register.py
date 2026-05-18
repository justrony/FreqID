"""
routes/register.py
Endpoint /register — captura landmarks faciais e os retorna ao chamador (Laravel).

Responsabilidade do Python: capturar + processar.
Responsabilidade do Laravel: autenticar, autorizar e persistir no banco.
O Python NÃO chama o Laravel de volta — é o Laravel quem chama este endpoint e
usa a resposta para salvar no banco de dados.
"""
import logging

import cv2
import numpy as np
from fastapi import APIRouter, Request

from ..config import LANDMARK_ROUND_DECIMALS
from ..hashing import generate_landmark_hash
from ..scanner import FreqIDScanner
from ..schemas import RegisterRequest
from .scan import _capture_landmarks

logger = logging.getLogger(__name__)
router = APIRouter(tags=["register"])


@router.post("/register")
def register(req: RegisterRequest, request: Request) -> dict:
    """
    Captura um frame da câmera, extrai os landmarks faciais via MediaPipe,
    gera o hash SHA-256 e retorna os dados ao Laravel para persistência.

    O Laravel é responsável por:
    - Validar o teacher_token (Sanctum)
    - Verificar autorização do professor sobre o aluno
    - Salvar o FaceFeature no banco de dados
    """
    cap: cv2.VideoCapture | None = request.app.state.cap
    scanner: FreqIDScanner = request.app.state.scanner

    landmarks_array = _capture_landmarks(cap, scanner)
    landmark_hash = generate_landmark_hash(landmarks_array)
    landmarks_list: list = np.round(landmarks_array, decimals=LANDMARK_ROUND_DECIMALS).tolist()

    logger.info(
        "Landmarks capturados para registro",
        extra={"student_id": req.student_id, "hash": landmark_hash},
    )

    return {
        "student_id": req.student_id,
        "landmark_hash": landmark_hash,
        "landmarks_count": len(landmarks_list),
        "landmarks": landmarks_list,
    }
