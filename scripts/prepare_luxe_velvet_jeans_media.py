#!/usr/bin/env python3
"""Prepare the user-authorized product 22 catalog image for controlled web delivery."""

from pathlib import Path
from PIL import Image

SOURCE = Path(__file__).resolve().parents[1] / "storage/app/public/products/luxe-velvet-jeans-olive.jpg"
TARGET = SOURCE
TARGET_SIZE = (960, 1200)

with Image.open(SOURCE) as source:
    image = source.convert("RGB")
    image = image.resize(TARGET_SIZE, Image.Resampling.LANCZOS)
    image.save(TARGET, format="JPEG", quality=85, optimize=True, progressive=True)

print(f"Prepared {TARGET.relative_to(SOURCE.parents[3])} at {TARGET_SIZE[0]}x{TARGET_SIZE[1]} JPEG")
