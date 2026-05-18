"""
main.py
Entrypoint do FreqID Python service.

Responsabilidade ÚNICA: montar a aplicação FastAPI, registrar middlewares,
configurar o ciclo de vida (câmera) e incluir os routers.
Toda lógica de negócio vive em módulos específicos.

Para iniciar:
    uvicorn app.Python.main:app --host 0.0.0.0 --port 8001
"""
import logging
import logging.config
from contextlib import asynccontextmanager
from typing import AsyncGenerator

import cv2
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from .config import CAMERA_INDEX
from .scanner import FreqIDScanner
from .routes import health, scan, register, stream

# ---------------------------------------------------------------------------
# Logging estruturado — zero prints em produção
# ---------------------------------------------------------------------------
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


# ---------------------------------------------------------------------------
# Lifespan: abre e fecha a câmera junto com o processo
# ---------------------------------------------------------------------------
@asynccontextmanager
async def lifespan(application: FastAPI) -> AsyncGenerator[None, None]:
    logger.info("Iniciando câmera", extra={"index": CAMERA_INDEX})
    cap = cv2.VideoCapture(CAMERA_INDEX)
    if not cap.isOpened():
        logger.warning("Câmera não pôde ser aberta no índice %d", CAMERA_INDEX)

    application.state.cap = cap
    application.state.scanner = FreqIDScanner()

    yield

    logger.info("Encerrando câmera")
    if cap is not None:
        cap.release()


# ---------------------------------------------------------------------------
# Aplicação
# ---------------------------------------------------------------------------
app = FastAPI(
    title="FreqID Scanner",
    version="1.0.0",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Permitindo todas as origens localmente (freqid.test, localhost, 127.0.0.1)
    allow_methods=["*"],
    allow_headers=["*"],
)

# Routers
app.include_router(health.router)
app.include_router(stream.router)
app.include_router(scan.router)
app.include_router(register.router)
