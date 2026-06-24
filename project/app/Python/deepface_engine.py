import logging

import cv2
import numpy as np
from deepface import DeepFace
from scipy.spatial.distance import cosine

from .config import (
    DEEPFACE_DETECTOR_BACKEND,
    DEEPFACE_DISTANCE_METRIC,
    DEEPFACE_MATCH_THRESHOLD,
    DEEPFACE_MODEL_NAME,
)

logger = logging.getLogger(__name__)

_FALLBACK_DETECTORS = ["opencv", "ssd"]


class DeepFaceEngine:

    def __init__(self) -> None:
        self.model_name = DEEPFACE_MODEL_NAME
        self.detector_backend = DEEPFACE_DETECTOR_BACKEND
        self.distance_metric = DEEPFACE_DISTANCE_METRIC
        self.threshold = DEEPFACE_MATCH_THRESHOLD

    def warmup(self) -> None:
        dummy = np.zeros((224, 224, 3), dtype=np.uint8)
        try:
            DeepFace.represent(
                img_path=dummy,
                model_name=self.model_name,
                detector_backend=self.detector_backend,
                enforce_detection=False,
            )
            logger.info("DeepFace warmup concluído")
        except Exception:
            logger.warning("DeepFace warmup falhou")

    def _preprocess(self, image: np.ndarray) -> np.ndarray:
        h, w = image.shape[:2]
        max_dim = 1024
        if max(h, w) > max_dim:
            scale = max_dim / max(h, w)
            image = cv2.resize(image, (int(w * scale), int(h * scale)), interpolation=cv2.INTER_AREA)
        min_dim = 160
        if min(h, w) < min_dim:
            scale = min_dim / min(h, w)
            image = cv2.resize(image, (int(w * scale), int(h * scale)), interpolation=cv2.INTER_LINEAR)
        return image

    def represent(self, image: np.ndarray) -> list[float]:
        image = self._preprocess(image)

        detectors = [self.detector_backend] + _FALLBACK_DETECTORS
        last_error = None

        for detector in detectors:
            try:
                results = DeepFace.represent(
                    img_path=image,
                    model_name=self.model_name,
                    detector_backend=detector,
                    enforce_detection=True,
                )
                if results:
                    logger.info("Rosto detectado", extra={"detector": detector})
                    return results[0]["embedding"]
            except Exception as e:
                last_error = e
                logger.warning("Detector falhou", extra={"detector": detector, "error": str(e)})
                continue

        raise ValueError(f"Nenhum rosto detectado com nenhum detector: {last_error}")

    def find_best_match(
        self,
        live_embedding: list[float],
        registered_faces: list[dict],
    ) -> tuple[int | None, float, bool]:
        live_vec = np.array(live_embedding)
        best_face_id = None
        min_distance = float("inf")

        for face in registered_faces:
            face_vec = np.array(face["embedding"])
            distance = cosine(live_vec, face_vec)

            if distance < min_distance:
                min_distance = distance
                best_face_id = face["face_id"]

        verified = bool(min_distance <= self.threshold) if best_face_id else False
        return best_face_id, float(min_distance), verified
