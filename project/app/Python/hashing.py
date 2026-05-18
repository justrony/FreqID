"""
hashing.py
Responsabilidade: serialização e hashing determinístico de landmarks faciais.
"""
import hashlib
import json

import numpy as np

from .config import LANDMARK_ROUND_DECIMALS


def generate_landmark_hash(landmarks: np.ndarray) -> str:
    """
    Serializa os landmarks arredondados em JSON canônico e retorna o SHA-256 hex.

    A serialização é determinística graças a `sort_keys=True` e ao arredondamento
    fixo, garantindo que o mesmo rosto sempre produza o mesmo hash.
    """
    rounded: list[list[float]] = np.round(landmarks, decimals=LANDMARK_ROUND_DECIMALS).tolist()
    payload = json.dumps(rounded, sort_keys=True)
    return hashlib.sha256(payload.encode("utf-8")).hexdigest()
