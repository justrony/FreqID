"""
http_client.py
Responsabilidade: comunicação HTTP com a API Laravel (integração externa).
Zero fire-and-forget — todo erro é logado com contexto e propagado como exceção
tipada para que as rotas possam reagir corretamente.
"""
import logging
from typing import Any

import requests

from .config import LARAVEL_REQUEST_TIMEOUT

logger = logging.getLogger(__name__)


class LaravelRequestError(RuntimeError):
    """Falha na comunicação com o backend Laravel."""


def send_to_laravel(
    url: str,
    payload: dict[str, Any],
    bearer_token: str | None = None,
) -> dict[str, Any]:
    """
    Envia dados para um endpoint Laravel via POST e retorna o JSON de resposta.

    Raises:
        LaravelRequestError: quando a request falha ou o status HTTP indica erro.
    """
    headers: dict[str, str] = {"Accept": "application/json"}
    if bearer_token:
        headers["Authorization"] = f"Bearer {bearer_token}"

    try:
        response = requests.post(
            url,
            json=payload,
            headers=headers,
            timeout=LARAVEL_REQUEST_TIMEOUT,
        )
        response.raise_for_status()
        return response.json()
    except requests.HTTPError as exc:
        logger.error(
            "Laravel retornou status de erro",
            extra={"url": url, "status_code": exc.response.status_code if exc.response else None},
        )
        raise LaravelRequestError(f"Laravel HTTP error: {exc}") from exc
    except requests.ConnectionError as exc:
        logger.error("Falha de conexão com Laravel", extra={"url": url})
        raise LaravelRequestError(f"Connection error: {exc}") from exc
    except requests.Timeout as exc:
        logger.error("Timeout na request para Laravel", extra={"url": url, "timeout": LARAVEL_REQUEST_TIMEOUT})
        raise LaravelRequestError(f"Request timed out after {LARAVEL_REQUEST_TIMEOUT}s") from exc
    except requests.RequestException as exc:
        logger.error("Erro inesperado na request para Laravel", extra={"url": url, "exc": str(exc)})
        raise LaravelRequestError(f"Unexpected request error: {exc}") from exc
