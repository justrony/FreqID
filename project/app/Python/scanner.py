"""
scanner.py
Responsabilidade: captura de frames da câmera e extração de landmarks faciais
via MediaPipe FaceMesh.
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
            static_image_mode=False,
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
