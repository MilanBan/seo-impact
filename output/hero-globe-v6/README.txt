V6 — KONTROLISANA ROTACIJA I PERIODIČNA ANIMACIJA

AKTUELNA ISPRAVKA: V6.1 ublažava atmosferski rub i uklanja donju liniju
sa tačkama. Novi fajl je hero-globe-v6-1-32s.mp4; vidi V6-1-changes.txt.
Podaci i merenja ispod odnose se na prvobitni V6. Za novu verziju koristi
hero-globe-v6-1-verification.json i hero-globe-v6-1-export-verification.json.
Aktuelni render.py sadrži ispravku; za novi izvoz dodaj --name hero-globe-v6-1
da ne prepišeš sačuvani originalni V6 video.

Glavni video: hero-globe-v6-32s.mp4
Format: 1920 x 1080, 30 fps, 32 sekunde, 960 kadrova, H.264, bez zvuka.
Izvoz koristi CRF 8 radi smanjenja razlike u kodiranju statične teksture
između početnog I-kadra i završnog kadra. Ovo je kvalitetna verzija za
pregled; eventualno smanjenje fajla za sajt zahteva novu proveru spoja.
Zemlja završava jedan pun obrt za 32 sekunde: 11,25 stepeni u sekundi.
Video je već renderovan pri ovoj brzini; nije potrebno usporavanje u CapCut-u.

Kako je napravljeno

render.py je samostalan analitički 3D renderer sfere sa ortografskom
projekcijom, bilinearnim uzorkovanjem kompletnih geografskih tekstura,
fiksnim osvetljenjem i odvojenim periodičnim svetlosnim efektima.
Kontinenti, noćna svetla i geografska mreža dele iste sferne koordinate.
Tlo je statična pozadinska slika. Pokretni podaci, snop i kompozitni odsjaji
izračunavaju se za svaki kadar; ne koriste se kadrovi Flow videa.

Zemljina fizička osa je nagnuta 23,4° u odnosu na normalu izabrane orbitalne
ravni. Pogled kamere je pod elevacijom od 20° prema toj ravni. Zato je
projekcija ose na ekran oko 24,73° ulevo od vertikale. Fizički i projektovani
ugao nisu ista veličina. Osa se ne menja tokom animacije.
Svetlosni snop ostaje vertikalan u kadru, odvojen od Zemljine ose.

Petlja

Svaki kadar se računa iz normalizovanog vremena n/960, n=0..959.
Kadar za fazu 1 predstavlja početak narednog ponavljanja i nije dodat
kao duplirani poslednji kadar. Time se izbegava pauza na spoju.
Sve brzine efekata odgovaraju celobrojnom broju ciklusa po punoj petlji.
Čestice se na krajevima svojih putanja glatko gase pre nevidljivog
ponovnog pojavljivanja. Svi slučajni položaji imaju fiksno seme 62026.

Provera

hero-globe-v6-verification.json beleži obliquitet, smer, trajanje i
poređenje stanja na granici petlje. Početak i matematički kraj ciklusa
moraju biti identični. Promena sa poslednjeg na prvi uzorkovani kadar
treba da bude uporediva sa promenom između susednih kadrova.
Završna MP4 provera dodatno proverava stvarni broj kadrova i kodirani spoj.
Provera je prošla: glavni MP4 ima tačno 960 kadrova i 32 sekunde, bez
zvučne trake. Izvorna scena ima razliku 0 između faza 0 i 1. Kodirana
promena na spoju Zemlje je oko 1,03 puta uobičajena susedna promena.
H.264 ipak unosi male razlike u teksturu: prosečna promena poda na spoju
je oko 0,30 nivoa na skali 0–255 u umanjenoj kontrolnoj slici. To nije
geometrijski skok ili promena smerova. Detalji su u fajlu
hero-globe-v6-export-verification.json.

Ovo je stilizovana vizuelizacija: tlo, svetlosni snop, noćna svetla,
atmosferski sjaj i kompozitni odsjaji služe vizuelnom izgledu i nisu
simulacija stvarnog Sunčevog osvetljenja ili fizičkog terena oko Zemlje.
Izgled nije identičan generisanoj V5 ilustraciji; geografija i animacija
su sada kontrolisane i ponovljive. Sajt i postojeći hero nisu menjani.

Izvori materijala

assets/background.png: pozadina dobijena imagegen doradom korisnikove
hero-globe-v5-loop.png, uklanjanjem Zemlje, snopa i vertikalnih efekata.
assets/earth-day.jpg:
https://raw.githubusercontent.com/mrdoob/three.js/r160/examples/textures/planets/earth_atmos_2048.jpg
assets/earth-night.png:
https://raw.githubusercontent.com/mrdoob/three.js/r160/examples/textures/planets/earth_lights_2048.png
Teksture su preuzete iz javnog Three.js r160 examples skupa; izvorni nazivi
su sačuvani u gornjim URL-ovima radi evidencije porekla.

Ponovno renderovanje

Potrebni su Python 3, NumPy, Pillow i ffmpeg na PATH-u.
Primer sa instaliranim zavisnostima:
python3 render.py --width 1920 --fps 30 --duration 32 --stills --video

U ovoj sesiji NumPy je izolovan u /tmp/seo-v6-pylibs; sistemski paketi
nisu menjani. Korišćena naredba:
PYTHONPATH=/tmp/seo-v6-pylibs python3 output/hero-globe-v6/render.py --width 1920 --stills --video

Sličice phase-0..3 predstavljaju faze na 0, 8, 16 i 24 sekunde.
hero-globe-v6-8s-preview.mp4 je ubrzani pregled celog obrta (720p).
hero-globe-v6-seam-check.mp4 prikazuje poslednje dve pa prve dve sekunde
glavnog videa. Pravi spoj petlje je na 2. sekundi tog kontrolnog klipa;
sam kontrolni klip nije namenjen neprekidnom ponavljanju.
