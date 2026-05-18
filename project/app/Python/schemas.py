"""
schemas.py
Contratos de entrada e saída da API (DTOs via Pydantic).
"""
from pydantic import BaseModel, Field


class ScanRequest(BaseModel):
    class_id: int = Field(..., gt=0, description="ID da turma para registrar presença")


class RegisterRequest(BaseModel):
    student_id: int = Field(..., gt=0, description="ID do aluno a ser cadastrado")
    # teacher_token é enviado pelo Laravel mas autenticação/autorização são responsabilidade do Laravel.
    # O Python não valida nem usa o token — apenas captura e retorna os landmarks.
    teacher_token: str | None = Field(default=None, description="Bearer token do professor (ignorado pelo Python)")
