"""
scanner.py
Responsabilidade: extração de landmarks faciais via MediaPipe FaceMesh.

Dois scanners distintos:
  - FreqIDScanner   : modo contínuo (stream MJPEG / registro de frequência)
  - PhotoScanner    : modo estático (processamento de imagem enviada pelo usuário)
"""
import logging

import cv2
import mediapipe as mp
import numpy as np

from .config import (
    FACE_MESH_MAX_FACES,
    FACE_MESH_MIN_DETECTION_CONFIDENCE,
    FACE_MESH_MIN_TRACKING_CONFIDENCE,
)

logger = logging.getLogger(__name__)

mp_drawing = mp.solutions.drawing_utils
mp_drawing_styles = mp.solutions.drawing_styles


class FreqIDScanner:
    """Extrai landmarks faciais de frames OpenCV usando MediaPipe FaceMesh."""

    def __init__(self) -> None:
        self._mp_face_mesh = mp.solutions.face_mesh
        self._face_mesh = self._mp_face_mesh.FaceMesh(
            static_image_mode=False,         # modo vídeo — mantém tracking entre frames
            max_num_faces=FACE_MESH_MAX_FACES,
            refine_landmarks=True,
            min_detection_confidence=FACE_MESH_MIN_DETECTION_CONFIDENCE,
            min_tracking_confidence=FACE_MESH_MIN_TRACKING_CONFIDENCE,
        )

    def extract_landmarks(
        self,
        frame: np.ndarray,
    ) -> tuple[np.ndarray | None, object | None]:
        """Retorna (landmarks_array, raw_face_landmarks) ou (None, None)."""
        rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        results = self._face_mesh.process(rgb_frame)

        if results.multi_face_landmarks:
            for face_landmarks in results.multi_face_landmarks:
                points = [[lm.x, lm.y, lm.z] for lm in face_landmarks.landmark]
                return np.array(points), face_landmarks

        return None, None

    def annotate_frame(self, frame: np.ndarray, face_landmarks: object) -> np.ndarray:
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


class PhotoScanner:
    """
    Extrai landmarks faciais de uma imagem estática (upload) usando MediaPipe FaceMesh
    em modo estático (`static_image_mode=True`).

    Separado do FreqIDScanner para não interferir no tracking contínuo do stream ao vivo.
    """

    def __init__(self) -> None:
        self._mp_face_mesh = mp.solutions.face_mesh
        self._face_mesh = self._mp_face_mesh.FaceMesh(
            static_image_mode=True,           # modo imagem — sem tracking entre frames
            max_num_faces=FACE_MESH_MAX_FACES,
            refine_landmarks=True,
            min_detection_confidence=FACE_MESH_MIN_DETECTION_CONFIDENCE,
            min_tracking_confidence=FACE_MESH_MIN_TRACKING_CONFIDENCE,
        )

    def extract_landmarks_from_image(
        self,
        image: np.ndarray,
    ) -> np.ndarray | None:
        """
        Recebe um frame BGR (numpy array) decodificado a partir de um arquivo de imagem
        e retorna o array de landmarks (478 × 3) ou None se nenhum rosto for detectado.
        """
        rgb_image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        results = self._face_mesh.process(rgb_image)

        if results.multi_face_landmarks:
            face_landmarks = results.multi_face_landmarks[0]
            points = [[lm.x, lm.y, lm.z] for lm in face_landmarks.landmark]
            return np.array(points)

        return None
