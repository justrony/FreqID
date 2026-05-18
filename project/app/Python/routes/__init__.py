"""routes package — re-exports all route modules."""
from . import health, register, scan, stream

__all__ = ["health", "register", "scan", "stream"]
