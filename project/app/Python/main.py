import logging
import logging.config
from contextlib import asynccontextmanager
from typing import AsyncGenerator

import cv2
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from .config import CAMERA_INDEX
from .deepface_engine import DeepFaceEngine
from .scanner import FreqIDScanner
from .routes import health, scan, register, stream, enroll

logging.config.dictConfig(
    {
        "version": 1,
        "disable_existing_loggers": False,
        "formatters": {
            "json_like": {
                "format": '{"time":"%(asctime)s","level":"%(levelname)s","logger":"%(name)s","msg":"%(message)s"}',
            }
        },
        "handlers": {
            "console": {
                "class": "logging.StreamHandler",
                "formatter": "json_like",
            }
        },
        "root": {"handlers": ["console"], "level": "INFO"},
    }
)

logger = logging.getLogger(__name__)


@asynccontextmanager
async def lifespan(application: FastAPI) -> AsyncGenerator[None, None]:
    logger.info("Iniciando câmera", extra={"index": CAMERA_INDEX})
    cap = cv2.VideoCapture(CAMERA_INDEX)
    if not cap.isOpened():
        logger.warning("Câmera não pôde ser aberta no índice %d", CAMERA_INDEX)

    application.state.cap = cap
    application.state.scanner = FreqIDScanner()

    deepface = DeepFaceEngine()
    deepface.warmup()
    application.state.deepface = deepface

    yield

    logger.info("Encerrando câmera")
    if cap is not None:
        cap.release()


app = FastAPI(
    title="FreqID Scanner",
    version="2.0.0",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(health.router)
app.include_router(stream.router)
app.include_router(scan.router)
app.include_router(register.router)
app.include_router(enroll.router)
