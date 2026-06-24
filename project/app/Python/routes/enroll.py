import logging

import cv2
import numpy as np
from fastapi import APIRouter, HTTPException, Request, UploadFile, File

from ..deepface_engine import DeepFaceEngine

logger = logging.getLogger(__name__)
router = APIRouter(tags=["enroll"])

_ALLOWED_CONTENT_TYPES = {"image/jpeg", "image/png", "image/webp"}
_MAX_FILE_SIZE = 5 * 1024 * 1024


@router.post("/enroll-from-image")
async def enroll_from_image(
    request: Request,
    photo: UploadFile = File(...),
) -> dict:
    if photo.content_type not in _ALLOWED_CONTENT_TYPES:
        raise HTTPException(
            status_code=415,
            detail=f"Tipo de arquivo não suportado: {photo.content_type}. Use JPG, PNG ou WEBP.",
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
        raise HTTPException(status_code=422, detail="Não foi possível decodificar a imagem.")

    deepface: DeepFaceEngine = request.app.state.deepface

    try:
        embedding = deepface.represent(image)
    except ValueError as e:
        logger.error("Nenhum rosto detectado no enroll", extra={"error": str(e)})
        raise HTTPException(status_code=422, detail="Nenhum rosto detectado na imagem.")
    except Exception as e:
        logger.error("Erro no DeepFace (enroll)", extra={"error": str(e)})
        raise HTTPException(status_code=500, detail=f"Erro ao processar rosto: {str(e)}")

    return {"embedding": embedding}
