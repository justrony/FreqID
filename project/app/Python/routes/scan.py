import logging

import cv2
import numpy as np
from fastapi import APIRouter, HTTPException, Request

from ..deepface_engine import DeepFaceEngine
from ..schemas import MatchRequest

logger = logging.getLogger(__name__)
router = APIRouter(tags=["scan"])


@router.post("/capture-hash")
def capture_embedding(request: Request) -> dict:
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
        logger.error("Erro no DeepFace.represent", extra={"error": str(e)})
        raise HTTPException(status_code=500, detail="Erro ao processar rosto")

    return {"embedding": embedding}


@router.post("/match")
def match_face(req: MatchRequest, request: Request) -> dict:
    deepface: DeepFaceEngine = request.app.state.deepface

    if not req.registered_faces:
        raise HTTPException(status_code=400, detail="Nenhum rosto registrado fornecido para comparação.")

    registered = [{"face_id": f.face_id, "embedding": f.embedding} for f in req.registered_faces]
    best_face_id, distance, verified = deepface.find_best_match(req.live_embedding, registered)

    return {
        "best_face_id": best_face_id,
        "distance": distance,
        "verified": verified,
    }
