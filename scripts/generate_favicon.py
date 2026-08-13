from pathlib import Path

from PIL import Image, ImageDraw

ROOT = Path(__file__).resolve().parents[1]
PUBLIC = ROOT / "public"
PUBLIC.mkdir(parents=True, exist_ok=True)

# The mark intentionally uses no typography: it is a compact, recognizable R-shaped
# ribbon made from the active storefront colors (--c-dark #111 and --c-orange #e85d26).
SIZE = 256
BACKGROUND = (17, 17, 17, 255)
ACCENT = (232, 93, 38, 255)
PAPER = (255, 255, 255, 255)

image = Image.new("RGBA", (SIZE, SIZE), BACKGROUND)
draw = ImageDraw.Draw(image)

# Soft rounded outer field with a deliberate inset so the icon remains legible at 16px.
draw.rounded_rectangle((12, 12, 244, 244), radius=54, fill=BACKGROUND, outline=ACCENT, width=12)

# Stylized R: left stem, upper bowl, and an energetic diagonal leg. Rounded ends avoid
# fragile fine detail in browser tab and mobile shortcut sizes.
draw.rounded_rectangle((61, 57, 94, 201), radius=16, fill=PAPER)
draw.rounded_rectangle((84, 57, 172, 89), radius=16, fill=PAPER)
draw.rounded_rectangle((142, 74, 175, 132), radius=16, fill=PAPER)
draw.rounded_rectangle((84, 116, 164, 148), radius=16, fill=PAPER)
draw.polygon(((135, 133), (170, 132), (211, 201), (173, 201)), fill=ACCENT)
draw.rounded_rectangle((166, 171, 206, 204), radius=15, fill=ACCENT)

# Export a high-resolution source and standard browser/icon derivatives.
image.save(PUBLIC / "favicon.png", format="PNG", optimize=True)
image.resize((180, 180), Image.Resampling.LANCZOS).save(
    PUBLIC / "apple-touch-icon.png", format="PNG", optimize=True
)
image.save(
    PUBLIC / "favicon.ico",
    format="ICO",
    sizes=[(16, 16), (32, 32), (48, 48), (64, 64), (128, 128), (256, 256)],
)
