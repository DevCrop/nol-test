"""Local artifact check. QA-only dependency: fonttools + brotli, never deployed to PHP."""
from pathlib import Path
import struct
from fontTools.ttLib import TTFont
root=Path(__file__).resolve().parent.parent
count=0
for file in (root/'resource/vendor').rglob('*.woff2'):
    body=file.read_bytes()
    assert body[:4]==b'wOF2',str(file.relative_to(root))+' invalid signature'
    assert struct.unpack('>I',body[8:12])[0]==len(body),str(file.relative_to(root))+' wrong binary length'
    font=TTFont(file,checkChecksums=2,lazy=False)
    for tag in font.keys():
        if tag!='GlyphOrder':font[tag]
    assert font.getBestCmap() and font.getGlyphOrder(),str(file.relative_to(root))+' invalid glyph mapping'
    if 'glyf' in font:font['glyf'].ensureDecompiled()
    font.close();count+=1
print(str(count)+' WOFF2 files decoded / 0 FAIL')
