"""
routes/stream.py
Endpoint MJPEG — retorna o feed da câmera com landmarks sobrepostos em tempo real.
"""
import logging

import cv2
from fastapi import APIRouter, HTTPException, Request
from fastapi.responses import StreamingResponse

from ..scanner import FreqIDScanner
from ..streaming import generate_mjpeg

logger = logging.getLogger(__name__)
router = APIRouter(tags=["stream"])


@router.get("/stream")
def stream(request: Request) -> StreamingResponse:
    """
    MJPEG stream — conecte diretamente num <img src="http://localhost:8001/stream">.
    Exibe o feed da câmera com landmarks do MediaPipe sobrepostos em tempo real.
    """
    cap: cv2.VideoCapture | None = request.app.state.cap
    scanner: FreqIDScanner = request.app.state.scanner

    if cap is None or not cap.isOpened():
        logger.error("Stream solicitado mas câmera não disponível")
        raise HTTPException(status_code=503, detail="Câmera não disponível")

    return StreamingResponse(
        generate_mjpeg(cap, scanner),
        media_type="multipart/x-mixed-replace; boundary=frame",
    )
