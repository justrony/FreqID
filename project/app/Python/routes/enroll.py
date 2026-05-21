"""
routes/enroll.py
Endpoint POST /enroll-from-image — recebe uma imagem upload e retorna os landmarks
faciais extraídos via MediaPipe PhotoScanner static_image_mode=True.

Responsabilidade do Python: decodificar a imagem e extrair landmarks.
Responsabilidade do Laravel: autenticar, autorizar e persistir no banco.
A imagem NÃO é salva em disco — processada em memória e descartada.
"""
import logging

import cv2
import numpy as np
from fastapi import APIRouter, HTTPException, UploadFile, File

from ..config import LANDMARK_ROUND_DECIMALS
from ..hashing import generate_landmark_hash
from ..scanner import PhotoScanner

logger = logging.getLogger(__name__)
router = APIRouter(tags=["enroll"])

_ALLOWED_CONTENT_TYPES = {"image/jpeg", "image/png", "image/webp"}

_MAX_FILE_SIZE = 5 * 1024 * 1024


@router.post("/enroll-from-image")
async def enroll_from_image(
    photo: UploadFile = File(..., description="Foto do aluno (JPG, PNG ou WEBP, máx. 5 MB)"),
) -> dict:
    """
    Processa uma imagem enviada pelo Laravel, extrai landmarks faciais via MediaPipe
    """

    if photo.content_type not in _ALLOWED_CONTENT_TYPES:
        raise HTTPException(
            status_code=415,
            detail=f"Tipo de arquivo não suportado: {photo.content_type}. "
                   "Use JPG, PNG ou WEBP.",
        )

    content = await photo.read()

    if len(content) > _MAX_FILE_SIZE:
        raise HTTPException(
            status_code=413,
            detail=f"Imagem muito grande ({len(content) // 1024} KB). Máximo: 5 MB.",
        )

    nparr = np.frombuffer(content, np.uint8)
    image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

    if image is None:
        raise HTTPException(
            status_code=422,
            detail="Não foi possível decodificar a imagem. Verifique se o arquivo está íntegro.",
        )

    # Extrai landmarks com PhotoScanner
    photo_scanner = PhotoScanner()
    landmarks_array = photo_scanner.extract_landmarks_from_image(image)

    if landmarks_array is None:
        raise HTTPException(
            status_code=404,
            detail="Nenhum rosto detectado na imagem. "
                   "Certifique-se de que o rosto do aluno está visível e bem iluminado.",
        )

    # Gera hash determinístico
    landmark_hash = generate_landmark_hash(landmarks_array)
    landmarks_list: list = np.round(landmarks_array, decimals=LANDMARK_ROUND_DECIMALS).tolist()

    logger.info(
        "Landmarks extraídos via upload de imagem",
        extra={"photo_name": photo.filename, "hash": landmark_hash},
    )

    return {
        "landmark_hash": landmark_hash,
        "landmarks_count": len(landmarks_list),
        "landmarks": landmarks_list,
    }
