"""Correct cyclic lighting discontinuities without blending the globe texture.

Run with PYTHONPATH=/tmp/seo-v6-pylibs python3 <this file>.
The input remains intact. Corrections fade over 12 frames on each side of
both cuts. The normal local temporal difference is retained at each cut.
"""
from pathlib import Path
import json
import subprocess as sp
import numpy as np

ROOT = Path(__file__).resolve().parent
SOURCE = ROOT / 'joined-matched-loop.mp4'
TARGET = ROOT / 'joined-lighting-corrected-loop.mp4'
W, H, N, FPS, RAMP = 1280, 720, 368, 24, 12
SIZE = W * H * 3

def decoder(filters=None):
    cmd = ['ffmpeg', '-v', 'error', '-i', str(SOURCE)]
    if filters:
        cmd += ['-vf', filters, '-vsync', '0']
    return sp.Popen(cmd + ['-f', 'rawvideo', '-pix_fmt', 'rgb24', '-threads', '1', '-'], stdout=sp.PIPE)

def read_frame(pipe):
    data = pipe.read(SIZE)
    if not data:
        return None
    if len(data) != SIZE:
        raise RuntimeError('Incomplete decoded frame')
    return np.frombuffer(data, np.uint8).reshape(H, W, 3)

indices = [0, 1, 182, 183, 184, 185, 366, 367]
p = decoder('select=' + '+'.join(f'eq(n\\,{n})' for n in indices))
key = {n: read_frame(p.stdout).astype(np.float32) for n in indices}
assert p.wait() == 0

# A stationary geometric holdout protects the visible globe and its rim.
# Feathering occurs outside the sphere and beneath the ground occlusion.
y, x = np.mgrid[:H, :W]
distance = np.sqrt((x - 948.)**2 + (y - 499.)**2)
outside = np.clip((distance - 204.) / 12., 0, 1)
below_ground = np.clip((y - 537.) / 12., 0, 1)
mask = np.maximum(outside, below_ground).astype(np.float32)[..., None]
mask = mask * mask * (3 - 2 * mask)

corrections = {}
for cut in (0, 184):
    previous, before = (cut - 1) % N, (cut - 2) % N
    after = (cut + 1) % N
    expected_step = ((key[previous] - key[before]) + (key[after] - key[cut])) * .5
    corrections[cut] = (key[cut] - key[previous] - expected_step) * mask

encoder = sp.Popen(['ffmpeg', '-v', 'error', '-f', 'rawvideo', '-pix_fmt', 'rgb24',
    '-s', f'{W}x{H}', '-r', str(FPS), '-i', '-', '-an', '-c:v', 'libx264',
    '-crf', '12', '-preset', 'slow', '-pix_fmt', 'yuv420p', '-threads', '4',
    '-movflags', '+faststart', '-y', str(TARGET)], stdin=sp.PIPE)
source = decoder()
checked = {}
max_globe_change = 0
for n in range(N):
    original = read_frame(source.stdout)
    if original is None:
        raise RuntimeError(f'Missing frame {n}')
    result = original.astype(np.float32)
    for cut, correction in corrections.items():
        ahead = (n - cut) % N
        behind = (cut - 1 - n) % N
        if ahead < RAMP:
            weight = .5 * (1 + np.cos(np.pi * ahead / (RAMP - 1)))
            result -= .5 * weight * correction
        elif behind < RAMP:
            weight = .5 * (1 + np.cos(np.pi * behind / (RAMP - 1)))
            result += .5 * weight * correction
    result = np.rint(np.clip(result, 0, 255)).astype(np.uint8)
    max_globe_change = max(max_globe_change, int(np.abs(result.astype(np.int16) - original)[mask[..., 0] == 0].max()))
    if n in indices:
        checked[n] = result.astype(np.float32)
    encoder.stdin.write(result.tobytes())
    if n % 92 == 0:
        print(f'Processed {n}/{N}', flush=True)
assert read_frame(source.stdout) is None
assert source.wait() == 0
encoder.stdin.close()
assert encoder.wait() == 0

metrics = {'protected_globe_max_pixel_change_before_encoding': max_globe_change,
           'ramp_frames_each_side': RAMP, 'frames': N, 'fps': FPS, 'seams': {}}
regions = {'beam': (slice(0, 285), slice(930, 955)),
           'ground': (slice(550, 720), slice(580, 1280)),
           'globe': (slice(335, 495), slice(820, 1080))}
for cut in (0, 184):
    previous = (cut - 1) % N
    metrics['seams'][cut] = {}
    for name, roi in regions.items():
        metrics['seams'][cut][name] = {
            'before_MAE': float(np.abs(key[cut][roi] - key[previous][roi]).mean()),
            'after_MAE': float(np.abs(checked[cut][roi] - checked[previous][roi]).mean())}
(ROOT / 'lighting-correction-analysis.json').write_text(json.dumps(metrics, indent=2))
print(json.dumps(metrics, indent=2), flush=True)
