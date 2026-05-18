"""
streaming.py
Responsabilidade: geração do MJPEG stream com anotações do MediaPipe.
"""
import logging
from collections.abc import Generator
from typing import TYPE_CHECKING

import cv2
import numpy as np

from .config import MJPEG_JPEG_QUALITY
from .scanner import FreqIDScanner

if TYPE_CHECKING:
    pass

logger = logging.getLogger(__name__)

# Constantes visuais do overlay
_COLOR_FACE_DETECTED = (0, 200, 0)    # verde
_COLOR_NO_FACE = (0, 140, 255)        # laranja
_BORDER_THICKNESS = 3
_FONT = cv2.FONT_HERSHEY_SIMPLEX
_FONT_SCALE_LARGE = 0.8
_FONT_SCALE_SMALL = 0.7
_FONT_THICKNESS = 2


def generate_mjpeg(
    cap: cv2.VideoCapture,
    scanner: FreqIDScanner,
) -> Generator[bytes, None, None]:
    """
    Gerador para MJPEG stream.

    Lê frames da câmera continuamente, anota com landmarks do MediaPipe
    (se detectado) e yield cada frame como JPEG boundary.
    """
    while True:
        if cap is None or not cap.isOpened():
            logger.warning("Câmera não disponível — encerrando stream")
            break

        success, frame = cap.read()
        if not success:
            logger.warning("Falha ao ler frame da câmera — encerrando stream")
            break

        frame = _annotate_live_frame(frame, scanner)

        ok, buffer = cv2.imencode(
            ".jpg",
            frame,
            [cv2.IMWRITE_JPEG_QUALITY, MJPEG_JPEG_QUALITY],
        )
        if not ok:
            logger.warning("Falha ao codificar frame como JPEG — pulando frame")
            continue

        yield (
            b"--frame\r\n"
            b"Content-Type: image/jpeg\r\n\r\n"
            + buffer.tobytes()
            + b"\r\n"
        )


def _annotate_live_frame(frame: np.ndarray, scanner: FreqIDScanner) -> np.ndarray:
    """Aplica overlay visual de detecção de rosto sobre o frame."""
    _, face_landmarks = scanner.extract_landmarks(frame)
    h, w = frame.shape[:2]

    if face_landmarks is not None:
        frame = scanner.annotate_frame(frame, face_landmarks)
        cv2.rectangle(frame, (0, 0), (w - 1, h - 1), _COLOR_FACE_DETECTED, _BORDER_THICKNESS)
        cv2.putText(
            frame,
            "Rosto detectado",
            (10, 30),
            _FONT,
            _FONT_SCALE_LARGE,
            _COLOR_FACE_DETECTED,
            _FONT_THICKNESS,
            cv2.LINE_AA,
        )
    else:
        cv2.rectangle(frame, (0, 0), (w - 1, h - 1), _COLOR_NO_FACE, _BORDER_THICKNESS)
        cv2.putText(
            frame,
            "Posicione o rosto na camera",
            (10, 30),
            _FONT,
            _FONT_SCALE_SMALL,
            _COLOR_NO_FACE,
            _FONT_THICKNESS,
            cv2.LINE_AA,
        )

    return frame
