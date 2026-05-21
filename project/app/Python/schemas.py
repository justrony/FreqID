"""
schemas.py
Contratos de entrada e saída da API (DTOs via Pydantic).
"""
from pydantic import BaseModel, Field


class ScanRequest(BaseModel):
    class_id: int = Field(..., gt=0, description="ID da turma para registrar presença")


class RegisterRequest(BaseModel):
    student_id: int = Field(..., gt=0, description="ID do aluno a ser cadastrado")
    teacher_token: str | None = Field(default=None, description="Bearer token do professor (ignorado pelo Python)")


class MatchFace(BaseModel):
    face_id: int = Field(..., description="ID da face salva no banco (FaceFeature ID)")
    landmarks: list[list[float]] = Field(..., description="Array de landmarks [x, y, z] salvo no banco")


class MatchRequest(BaseModel):
    live_landmarks: list[list[float]] = Field(..., description="Array de landmarks recém capturado da câmera")
    registered_faces: list[MatchFace] = Field(..., description="Lista de rostos salvos para comparação")
