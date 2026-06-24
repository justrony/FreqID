import logging

import cv2
from fastapi import APIRouter, HTTPException, Request

from ..deepface_engine import DeepFaceEngine
from ..schemas import RegisterRequest

logger = logging.getLogger(__name__)
router = APIRouter(tags=["register"])


@router.post("/register")
def register(req: RegisterRequest, request: Request) -> dict:
    cap: cv2.VideoCapture | None = request.app.state.cap
    deepface: DeepFaceEngine = request.app.state.deepface

    if cap is None or not cap.isOpened():
        raise HTTPException(status_code=503, detail="Câmera não disponível")

    success, frame = cap.read()
    if not success:
        raise HTTPException(status_code=500, detail="Falha ao ler frame da câmera")

    try:
        embedding = deepface.represent(frame)
    except ValueError:
        raise HTTPException(status_code=404, detail="Nenhum rosto detectado no frame")
    except Exception as e:
        logger.error("Erro no DeepFace.represent (register)", extra={"error": str(e)})
        raise HTTPException(status_code=500, detail="Erro ao processar rosto")

    return {
        "student_id": req.student_id,
        "embedding": embedding,
    }
