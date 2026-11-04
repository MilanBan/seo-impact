"""Deterministic orthographic 3D Earth + periodic composited light effects.

Dependencies: Python 3, NumPy, Pillow; ffmpeg for video export.
No accumulated simulation state: every frame is a pure function of loop phase.
"""
from pathlib import Path
import argparse
import json
import math
import subprocess
import time

import numpy as np
from PIL import Image, ImageDraw, ImageFilter

ROOT = Path(__file__).resolve().parent
TAU = 2 * math.pi
TILT = math.radians(23.4)
VIEW_LAT = math.radians(20)
START_LON = math.radians(20)
RS = getattr(Image, 'Resampling', Image)
TR = getattr(Image, 'Transpose', Image)
RESAMPLE = RS.LANCZOS


def smooth(x):
    x = np.clip(x, 0, 1)
    return x * x * (3 - 2 * x)


class Renderer:
    def __init__(self, width=1920):
        self.w, self.h = width, width * 9 // 16
        self.s = width / 1672
        self.sy = self.h / 941
        self.bg = np.asarray(Image.open(ROOT / 'assets/background.png')
                             .convert('RGB').resize((self.w, self.h), RESAMPLE),
                             dtype=np.float32)
        self.day = np.asarray(Image.open(ROOT / 'assets/earth-day.jpg').convert('RGB'), dtype=np.float32) / 255
        self.night = np.asarray(Image.open(ROOT / 'assets/earth-night.png').convert('RGB'), dtype=np.float32) / 255
        self.cx, self.cy, self.r = 1238*self.s, 648*self.sy, 255*self.s
        self.floor = int(700*self.sy)
        self.top = self.cy-self.r
        self.x0 = int(self.cx-self.r-14*self.s)
        self.x1 = int(self.cx+self.r+14*self.s)+1
        self.y0 = int(self.cy-self.r-14*self.s)
        self.y1 = self.floor+1
        yy, xx = np.mgrid[self.y0:self.y1, self.x0:self.x1].astype(np.float32)
        self.nx, self.ny = (xx-self.cx)/self.r, (self.cy-yy)/self.r
        self.rho = np.sqrt(self.nx**2+self.ny**2)
        self.nz = np.sqrt(np.maximum(0, 1-self.rho**2))
        # Tilt is physical obliquity relative to the orbital normal BEFORE
        # camera elevation. Camera-space transform = Rx(view_lat) Rz(tilt).
        # Its screen projection is slightly different from physical obliquity.
        c, s = math.cos(TILT), math.sin(TILT)
        vy = math.cos(VIEW_LAT)*self.ny+math.sin(VIEW_LAT)*self.nz
        self.local_z = -math.sin(VIEW_LAT)*self.ny+math.cos(VIEW_LAT)*self.nz
        vx = c*self.nx+s*vy
        self.local_y = -s*self.nx+c*vy
        self.latitude = np.arcsin(np.clip(self.local_y, -1, 1))
        self.view_lon = math.atan2(s*math.sin(VIEW_LAT), math.cos(VIEW_LAT))
        self.base_lon = np.arctan2(vx, self.local_z)-self.view_lon
        self.v = np.clip(0.5-self.latitude/math.pi, 0, 1)
        self.mask = smooth((1-self.rho)*self.r+0.5)
        self.floor_fade = smooth((self.floor-yy)/max(1, 7*self.s))
        self.mask *= self.floor_fade
        self.light = np.clip(-0.32*self.nx+0.62*self.ny+0.72*self.nz, 0, 1)
        self.lat_grid = np.exp(-(np.sin(self.latitude*12)/0.017)**2)
        # Broad, low-intensity scattering; no narrow emissive outline.
        self.atmosphere = np.exp(-((self.rho-0.992)/0.028)**2)
        self.haze = np.exp(-((self.rho-1.008)/0.045)**2)
        self.atmosphere_light = 0.2+0.8*np.clip(self.ny,0,1)
        self.axis = np.array([-math.sin(TILT), math.cos(TILT)*math.cos(VIEW_LAT),
                              math.cos(TILT)*math.sin(VIEW_LAT)])
        self.orbital_normal = np.array([0, math.cos(VIEW_LAT), math.sin(VIEW_LAT)])
        rng = np.random.default_rng(62026)
        # Explicitly seeded positions and integer cycle counts ensure periodicity.
        self.columns = []
        for i in range(43):
            x = float(rng.uniform(755, 1645))
            base = float(rng.uniform(665, 705))
            length = float(rng.uniform(65, 460))
            self.columns.append((x, base, length, float(rng.random()), int(rng.choice([8, 12, 16]))))
        self.particles = [(float(rng.uniform(850, 1610)), float(rng.uniform(570, 700)),
                           float(rng.uniform(90, 300)), float(rng.random()),
                           int(rng.choice([4, 8])), float(rng.uniform(0.55, 1.2)))
                          for _ in range(58)]
        # Cubic path follows the background plate's right-hand luminous S curve.
        curves = [((1395, 945),(1355, 875),(1190, 851),(1183, 799)),
                  ((1183, 799),(1172, 774),(1302, 774),(1298, 748)),
                  ((1298, 748),(1294, 719),(1050, 718),(939, 697)),
                  ((939, 697),(838, 679),(943, 674),(980, 669))]
        pts = []
        for control in curves:
            u = np.linspace(0, 1, 200, endpoint=False)[:, None]
            a,b,c,d = np.array(control, dtype=np.float32)
            pts.extend(((1-u)**3*a+3*(1-u)**2*u*b+3*(1-u)*u*u*c+u**3*d).tolist())
        self.path = np.asarray(pts)
        distances = np.r_[0, np.cumsum(np.linalg.norm(np.diff(self.path, axis=0), axis=1))]
        self.path_phase = distances/distances[-1]
        self.baked = self.static_lights()

    def pos(self, x, y):
        return x*self.s, y*self.sy

    def dot(self, draw, x, y, radius, color):
        x,y = self.pos(x,y)
        r = max(0.5, radius*self.s)
        draw.ellipse((x-r,y-r,x+r,y+r),fill=color)

    def static_lights(self):
        layer = Image.new('RGB', (self.w,self.h))
        draw = ImageDraw.Draw(layer)
        for x,y,length,phase,cycles in self.columns:
            draw.line([self.pos(x,y),self.pos(x,y-length)],fill=(3,19,43),width=max(1,round(self.s)))
            self.dot(draw,x,y,1.4,(30,115,200))
        return layer

    def sample(self, tex, u):
        th,tw=tex.shape[:2]
        tx=(u%1)*tw
        ty=self.v*(th-1)
        ix=np.floor(tx).astype(np.int32)%tw
        iy=np.floor(ty).astype(np.int32)
        jx=(ix+1)%tw
        jy=np.minimum(iy+1,th-1)
        fx=(tx-np.floor(tx))[...,None]
        fy=(ty-iy)[...,None]
        return ((tex[iy,ix]*(1-fx)+tex[iy,jx]*fx)*(1-fy)
                +(tex[jy,ix]*(1-fx)+tex[jy,jx]*fx)*fy)

    def sphere(self, phase):
        # Positive eastward rotation makes fixed landmarks move right initially.
        lon=self.base_lon+START_LON-TAU*phase
        u=0.5+lon/TAU
        day=self.sample(self.day,u)
        night=self.sample(self.night,u)
        luminance=day[...,0]*0.27+day[...,1]*0.55+day[...,2]*0.18
        shade=0.36+0.64*self.light
        col=luminance[...,None]*np.array([35,93,175],dtype=np.float32)*shade[...,None]
        ice=smooth((luminance-0.64)/0.32)
        col += ice[...,None]*np.array([50,75,92],dtype=np.float32)*shade[...,None]
        # Isolate city lights from the dark violet base of the night map.
        cities=np.clip((night[...,0]*0.5+night[...,1]*0.5-0.075)/0.925,0,1)**0.85
        col += cities[...,None]*np.array([235,217,184],dtype=np.float32)
        lon_grid=np.exp(-(np.sin(lon*12)/0.018)**2)
        grid=np.maximum(self.lat_grid,lon_grid)*(0.2+0.8*self.nz)
        col += grid[...,None]*np.array([2,12,23],dtype=np.float32)
        fresnel=(1-self.nz)**3
        col += fresnel[...,None]*np.array([2,12,28],dtype=np.float32)
        return col

    def column_layer(self, phase, foreground=False):
        layer = Image.new('RGB',(self.w,self.h))
        draw=ImageDraw.Draw(layer)
        columns=self.columns[::5] if foreground else self.columns
        for x,y,length,offset,cycles in columns:
            if foreground:
                length=min(length,105)
            for k in range(3):
                q=(phase*cycles+offset+k/3)%1
                gain=float(smooth(q/0.08)*smooth((1-q)/0.12))
                py=y-length*q
                color=tuple(int(v*gain) for v in (70,190,255))
                draw.line([self.pos(x,py+9),self.pos(x,py)],fill=tuple(int(v*.35) for v in color),width=max(1,round(self.s)))
                self.dot(draw,x,py,0.8,color)
        if not foreground:
            for x,y,length,offset,cycles,radius in self.particles:
                q=(phase*cycles+offset)%1
                gain=math.sin(math.pi*q)**2
                self.dot(draw,x,y-length*q,radius,tuple(int(v*gain) for v in (32,111,185)))
        return layer

    def foreground_layer(self, phase):
        layer=self.column_layer(phase, True)
        draw=ImageDraw.Draw(layer)
        # Main beam: screen-vertical, separate from tilted geographic north.
        x=self.cx
        draw.line([(x,0),(x,self.top)],fill=(7,35,85),width=max(1,round(7*self.s)))
        draw.line([(x,0),(x,self.top)],fill=(40,170,255),width=max(1,round(3*self.s)))
        draw.line([(x,0),(x,self.top)],fill=(180,245,255),width=max(1,round(self.s)))
        pulses=Image.new('RGB',(self.w,self.h))
        pulse_draw=ImageDraw.Draw(pulses)
        for k in range(3):
            q=(phase*8+k/3)%1
            py=q*self.top
            gain=float(smooth(q/0.035)*smooth((1-q)/0.035))
            for j in range(-9,10):
                g=math.exp(-(j/4)**2)*gain
                pulse_draw.line([(x-2*self.s,py+j*self.s),(x+2*self.s,py+j*self.s)],
                          fill=(int(200*g),int(245*g),int(255*g)),width=max(1,round(self.s)))
        self.dot(draw,self.cx/self.s,self.top/self.sy,3.5,(185,240,255))
        draw.line([(x-12*self.s,self.top),(x+12*self.s,self.top)],fill=(65,140,200),width=max(1,round(self.s)))
        # Cyclic packet train moves from the near foreground toward the globe.
        for k in range(6):
            q=(phase*4+k/6)%1
            gain=float(smooth(q/.04)*smooth((1-q)/.08))
            for tail in range(10):
                qp=q-tail*.002
                if not 0<=qp<=1:
                    continue
                px=np.interp(qp,self.path_phase,self.path[:,0])
                py=np.interp(qp,self.path_phase,self.path[:,1])
                g=gain*(1-tail/10)**1.8
                self.dot(draw,px,py,1.3,(int(110*g),int(200*g),int(255*g)))
        return Image.fromarray(np.minimum(np.asarray(layer,dtype=np.uint16)+np.asarray(pulses,dtype=np.uint16),255).astype(np.uint8))

    def add_glow(self, base, layer, strength=1):
        # Low-resolution glow keeps soft optical bloom stable and inexpensive.
        small=layer.resize((self.w//3,self.h//3),RS.BILINEAR)
        blur=small.filter(ImageFilter.GaussianBlur(2.5)).resize((self.w,self.h),RS.BILINEAR)
        return base+np.asarray(layer,dtype=np.float32)*strength+np.asarray(blur,dtype=np.float32)*2.5*strength

    def frame(self, phase):
        phase=float(phase%1)
        rear=self.column_layer(phase)
        rear=Image.fromarray(np.minimum(np.asarray(rear,dtype=np.uint16)+np.asarray(self.baked,dtype=np.uint16),255).astype(np.uint8))
        base=self.add_glow(self.bg.copy(),rear,.85)
        col=self.sphere(phase)
        area=base[self.y0:self.y1,self.x0:self.x1]
        a=self.mask[...,None]
        area[:]=area*(1-a)+col*a
        glow=((self.atmosphere*.25+self.haze*.13)*self.atmosphere_light*self.floor_fade)[...,None]*np.array([30,110,190],dtype=np.float32)
        area[:]+=glow
        # Restrained compressed reflection of the actual rotating surface.
        refl_img=Image.fromarray(np.clip(col*a,0,255).astype(np.uint8)).transpose(TR.FLIP_TOP_BOTTOM)
        rh=max(1,round(95*self.sy))
        refl_img=refl_img.resize((self.x1-self.x0,rh),RESAMPLE).filter(ImageFilter.GaussianBlur(2*self.s))
        refl=np.asarray(refl_img,dtype=np.float32)
        rh=min(rh,self.h-self.floor)
        weight=(1-np.arange(rh,dtype=np.float32)/rh)**2*.17
        base[self.floor:self.floor+rh,self.x0:self.x1]+=refl[:rh]*weight[:,None,None]
        fore=self.foreground_layer(phase)
        base=self.add_glow(base,fore,1.05)
        # Soft, periodic reflections of foreground lighting on a fixed floor.
        reflection=fore.crop((0,max(0,self.floor-round(180*self.sy)),self.w,self.floor))
        reflection=reflection.transpose(TR.FLIP_TOP_BOTTOM).resize((self.w,round(100*self.sy)),RESAMPLE)
        reflection=reflection.filter(ImageFilter.GaussianBlur(1.8*self.s))
        rr=np.asarray(reflection,dtype=np.float32)
        n=min(rr.shape[0],self.h-self.floor)
        base[self.floor:self.floor+n]+=rr[:n]*(0.23*(1-np.arange(n)/n)**2)[:,None,None]
        return np.clip(base,0,255).astype(np.uint8)

    def verify(self, fps, duration):
        start=self.frame(0)
        endpoint=self.frame(1)
        n=round(fps*duration)
        before=self.frame(1-1/n).astype(np.float32)
        after=self.frame(1/n).astype(np.float32)
        first=start.astype(np.float32)
        wrap=np.abs(first-before)
        following=np.abs(after-first)
        # A tracked front-face landmark moves right for positive eastward phase.
        def landmark(phase):
            lat,lon=math.radians(15),math.radians(20)
            a=lon-START_LON+self.view_lon+TAU*phase
            x,y,z=math.cos(lat)*math.sin(a),math.sin(lat),math.cos(lat)*math.cos(a)
            x,y=math.cos(TILT)*x-math.sin(TILT)*y,math.sin(TILT)*x+math.cos(TILT)*y
            y,z=math.cos(VIEW_LAT)*y-math.sin(VIEW_LAT)*z,math.sin(VIEW_LAT)*y+math.cos(VIEW_LAT)*z
            return (x,y,z)
        result={
            'renderer':'analytic 3D sphere, orthographic projection',
            'size':[self.w,self.h], 'fps':fps,'duration_seconds':duration,'frames':n,
            'axis_camera_space':self.axis.tolist(),
            'physical_obliquity_degrees':math.degrees(math.acos(float(np.dot(self.axis,self.orbital_normal)))),
            'orbital_normal_camera_space':self.orbital_normal.tolist(),
            'axis_projected_left_tilt_degrees':math.degrees(math.atan2(-self.axis[0],self.axis[1])),
            'camera_latitude_degrees':20,
            'earth_rotation_degrees':360,'degrees_per_second':360/duration,
            'initial_landmark_moves_right':landmark(.001)[0]>landmark(0)[0],
            'phase_zero_equals_phase_one':bool(np.array_equal(start,endpoint)),
            'endpoint_max_channel_difference':int(np.max(np.abs(start.astype(int)-endpoint.astype(int)))),
            'wrap_adjacent_mean_difference':float(wrap.mean()),
            'next_adjacent_mean_difference':float(following.mean()),
            'wrap_to_next_difference_ratio':float(wrap.mean()/following.mean()),
            'sampling':'frames n=0..N-1 at phase n/N; endpoint N is NOT duplicated',
            'effects':'fixed seed; integer phase cycles; faded particle recycling',
        }
        assert result['phase_zero_equals_phase_one']
        assert abs(result['physical_obliquity_degrees']-23.4)<1e-10
        assert result['initial_landmark_moves_right']
        assert .7<result['wrap_to_next_difference_ratio']<1.3
        return result


def main():
    ap=argparse.ArgumentParser()
    ap.add_argument('--width',type=int,default=1920)
    ap.add_argument('--fps',type=int,default=30)
    ap.add_argument('--duration',type=float,default=32)
    ap.add_argument('--stills',action='store_true')
    ap.add_argument('--video',action='store_true')
    ap.add_argument('--name',default='hero-globe-v6')
    args=ap.parse_args()
    r=Renderer(args.width)
    report=r.verify(args.fps,args.duration)
    (ROOT/f'{args.name}-verification.json').write_text(json.dumps(report,indent=2)+'\n')
    print(json.dumps(report,indent=2),flush=True)
    if args.stills:
        for i,phase in enumerate([0,.25,.5,.75]):
            Image.fromarray(r.frame(phase)).save(ROOT/f'{args.name}-phase-{i}.png')
    if args.video:
        dest=ROOT/f'{args.name}-{int(args.duration)}s.mp4'
        cmd=['ffmpeg','-hide_banner','-loglevel','warning','-y','-f','rawvideo','-pix_fmt','rgb24',
             '-s',f'{r.w}x{r.h}','-r',str(args.fps),'-i','-','-an','-c:v','libx264',
             '-preset','medium','-crf','8','-pix_fmt','yuv420p','-movflags','+faststart',
             '-threads','4',str(dest)]
        process=subprocess.Popen(cmd,stdin=subprocess.PIPE)
        start=time.monotonic()
        n=round(args.duration*args.fps)
        try:
            for i in range(n):
                process.stdin.write(r.frame(i/n).tobytes())
                if i%60==0:
                    print(f'{i}/{n} frames, {time.monotonic()-start:.1f}s elapsed',flush=True)
        finally:
            process.stdin.close()
        if process.wait()!=0:
            raise RuntimeError('ffmpeg export failed')
        print('Saved',dest,flush=True)


if __name__=='__main__':
    main()
