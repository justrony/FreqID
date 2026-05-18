"""
routes/health.py
Endpoint de health check — sem dependências externas.
"""
from fastapi import APIRouter

router = APIRouter(tags=["health"])


@router.get("/health")
def health() -> dict[str, str]:
    """Verifica se o serviço está operacional."""
    return {"status": "ok"}
