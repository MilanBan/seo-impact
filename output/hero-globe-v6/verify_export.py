"""Check the actual encoded video, including adjacent frames across its seam."""
from pathlib import Path
import json
import subprocess
import argparse
import numpy as np

root = Path(__file__).resolve().parent
parser=argparse.ArgumentParser()
parser.add_argument('--name',default='hero-globe-v6')
args=parser.parse_args()
video = root / f'{args.name}-32s.mp4'
metadata = json.loads(subprocess.check_output([
    'ffprobe','-v','error','-show_entries',
    'stream=codec_type,width,height,r_frame_rate,nb_frames,duration:format=duration,size',
    '-of','json',str(video)]))
streams = metadata['streams']
assert len(streams) == 1 and streams[0]['codec_type'] == 'video'
stream = streams[0]
assert (stream['width'],stream['height']) == (1920,1080)
assert stream['r_frame_rate'] == '30/1'
assert int(stream['nb_frames']) == 960
assert abs(float(stream['duration'])-32) < 0.0001

raw = subprocess.check_output([
    'ffmpeg','-v','error','-i',str(video),'-vf',
    "select='eq(n,0)+eq(n,1)+eq(n,958)+eq(n,959)',scale=640:360",
    '-vsync','0','-f','rawvideo','-pix_fmt','rgb24','-'])
frames=np.frombuffer(raw,dtype=np.uint8).reshape(4,360,640,3).astype(np.float32)
first,second,penultimate,last=frames
regions = {'whole_frame':(0,0,640,360),'earth':(375,151,575,268),
           'beam':(468,0,480,151),'floor':(290,269,640,360)}
result = {'metadata':metadata,'regions':{}}
for name,(x0,y0,x1,y1) in regions.items():
    def delta(a,b):
        return float(np.abs(a[y0:y1,x0:x1]-b[y0:y1,x0:x1]).mean())
    wrap=delta(last,first)
    ordinary=(delta(first,second)+delta(penultimate,last))/2
    result['regions'][name]={'wrap_adjacent_MAE':wrap,'neighbor_adjacent_MAE':ordinary,
                            'wrap_to_neighbor_ratio':wrap/max(ordinary,1e-9)}
    # Wide enough for I/P frame coding differences, tight enough for a reset.
    assert wrap < ordinary*1.65+0.1, (name,wrap,ordinary)
result['passed']=True
(root/f'{args.name}-export-verification.json').write_text(json.dumps(result,indent=2)+'\n')
print(json.dumps(result,indent=2))
