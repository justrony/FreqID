from pydantic import BaseModel, Field


class ScanRequest(BaseModel):
    class_id: int = Field(..., gt=0)


class RegisterRequest(BaseModel):
    student_id: int = Field(..., gt=0)
    teacher_token: str | None = Field(default=None)


class MatchFace(BaseModel):
    face_id: int
    embedding: list[float]


class MatchRequest(BaseModel):
    live_embedding: list[float]
    registered_faces: list[MatchFace]
