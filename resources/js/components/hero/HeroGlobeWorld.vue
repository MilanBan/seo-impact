<script setup lang="ts">
import { useLoop } from '@tresjs/core';
import { computed, onBeforeUnmount, onMounted, shallowRef } from 'vue';
import {
    BufferGeometry,
    Euler,
    QuadraticBezierCurve3,
    Vector3,
    type Group,
    type Mesh,
} from 'three';

const globe = shallowRef<Group>();
const dataPackets: Mesh[] = [];
const pointer = { x: 0, y: 0 };
let scrollDepth = 0;
let elapsed = 0;
const cameraPosition = new Vector3(0, 0, 4.7);
const lightPosition = new Vector3(2.5, 3, 4);
const globeRotation = new Euler(0.08, -1.05, 0);
const atmosphereScale = new Vector3(1.075, 1.075, 1.075);

const pointOnGlobe = (latitude: number, longitude: number, radius = 1.53) => {
    const phi = ((90 - latitude) * Math.PI) / 180;
    const theta = ((longitude + 180) * Math.PI) / 180;

    return new Vector3(
        -radius * Math.sin(phi) * Math.cos(theta),
        radius * Math.cos(phi),
        radius * Math.sin(phi) * Math.sin(theta),
    );
};

const arcCurves = [
    [44.82, 20.46, 52.52, 13.4],
    [44.82, 20.46, 51.51, -0.13],
    [44.82, 20.46, 25.2, 55.27],
    [44.82, 20.46, 40.71, -74.01],
].map(([fromLat, fromLng, toLat, toLng]) => {
    const start = pointOnGlobe(fromLat, fromLng);
    const end = pointOnGlobe(toLat, toLng);
    const control = start
        .clone()
        .add(end)
        .normalize()
        .multiplyScalar(start.distanceTo(end) > 2 ? 2.35 : 1.95);

    return new QuadraticBezierCurve3(start, control, end);
});

const arcGeometries = arcCurves.map((curve) =>
    new BufferGeometry().setFromPoints(curve.getPoints(42)),
);

const continentCoordinates = [
    [
        [36, -10],
        [43, -10],
        [51, -5],
        [58, 5],
        [71, 20],
        [66, 31],
        [58, 40],
        [48, 29],
        [41, 28],
        [36, 23],
        [41, 14],
        [36, -10],
    ],
    [
        [37, -17],
        [32, 10],
        [30, 32],
        [12, 44],
        [-12, 40],
        [-35, 18],
        [-29, 2],
        [-5, -17],
        [15, -17],
        [37, -17],
    ],
    [
        [71, 20],
        [68, 60],
        [72, 110],
        [55, 142],
        [35, 140],
        [20, 110],
        [8, 80],
        [25, 55],
        [40, 45],
        [58, 40],
        [71, 20],
    ],
    [
        [72, -165],
        [70, -105],
        [50, -55],
        [25, -80],
        [15, -100],
        [32, -118],
        [55, -138],
        [72, -165],
    ],
    [
        [12, -82],
        [5, -75],
        [-20, -70],
        [-55, -68],
        [-35, -50],
        [-5, -35],
        [10, -60],
        [12, -82],
    ],
    [
        [-12, 113],
        [-10, 142],
        [-25, 153],
        [-39, 146],
        [-36, 115],
        [-12, 113],
    ],
];

const continentGeometries = continentCoordinates.map((coordinates) =>
    new BufferGeometry().setFromPoints(
        coordinates.map(([lat, lng]) => pointOnGlobe(lat, lng, 1.54)),
    ),
);

const markerPositions = [
    [44.82, 20.46],
    [52.52, 13.4],
    [51.51, -0.13],
    [25.2, 55.27],
    [40.71, -74.01],
].map(([latitude, longitude]) => pointOnGlobe(latitude, longitude, 1.565));

const setDataPacket = (element: unknown, index: number) => {
    if (element) dataPackets[index] = element as Mesh;
};

const points = computed(() => {
    const values: number[] = [];
    for (let i = 0; i < 1100; i++) {
        const phi = Math.acos(-1 + (2 * i) / 1100);
        const theta = Math.sqrt(1100 * Math.PI) * phi;
        values.push(
            1.53 * Math.cos(theta) * Math.sin(phi),
            1.53 * Math.sin(theta) * Math.sin(phi),
            1.53 * Math.cos(phi),
        );
    }

    return new Float32Array(values);
});

const updatePointer = (event: PointerEvent) => {
    pointer.x = event.clientX / window.innerWidth - 0.5;
    pointer.y = event.clientY / window.innerHeight - 0.5;
};
const updateScroll = () => {
    scrollDepth = Math.min(window.scrollY, window.innerHeight) * -0.00018;
};

onMounted(() => {
    window.addEventListener('pointermove', updatePointer, { passive: true });
    window.addEventListener('scroll', updateScroll, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('pointermove', updatePointer);
    window.removeEventListener('scroll', updateScroll);
    arcGeometries.forEach((geometry) => geometry.dispose());
    continentGeometries.forEach((geometry) => geometry.dispose());
});

const { onBeforeRender } = useLoop();
onBeforeRender(({ delta }) => {
    if (!globe.value || document.hidden) return;
    elapsed += delta;
    globe.value.rotation.y += delta * 0.24;
    globe.value.rotation.x +=
        (pointer.y * 0.14 - globe.value.rotation.x) * 0.035;
    globe.value.rotation.z +=
        (-pointer.x * 0.11 - globe.value.rotation.z) * 0.035;
    globe.value.position.y += (scrollDepth - globe.value.position.y) * 0.035;
    dataPackets.forEach((packet, index) => {
        packet.position.copy(
            arcCurves[index].getPoint((elapsed * 0.22 + index * 0.19) % 1),
        );
        packet.scale.setScalar(0.8 + Math.sin(elapsed * 5 + index) * 0.25);
    });
});
</script>

<template>
    <TresPerspectiveCamera :position="cameraPosition" :fov="42" />
    <TresAmbientLight :intensity="0.9" color="#3a78ff" />
    <TresPointLight :position="lightPosition" :intensity="18" color="#5ebcff" />
    <TresGroup ref="globe" :rotation="globeRotation">
        <TresMesh>
            <TresSphereGeometry :args="[1.48, 48, 48]" />
            <TresMeshPhysicalMaterial
                color="#03143a"
                :roughness="0.72"
                :metalness="0.18"
                :transparent="true"
                :opacity="0.68"
            />
        </TresMesh>
        <TresMesh>
            <TresSphereGeometry :args="[1.505, 38, 38]" />
            <TresMeshBasicMaterial
                color="#2f74ff"
                wireframe
                :transparent="true"
                :opacity="0.46"
            />
        </TresMesh>
        <TresPoints>
            <TresBufferGeometry>
                <TresBufferAttribute
                    attach="attributes-position"
                    :array="points"
                    :item-size="3"
                />
            </TresBufferGeometry>
            <TresPointsMaterial
                color="#b6eaff"
                :size="0.02"
                :size-attenuation="true"
                :transparent="true"
                :opacity="0.95"
            />
        </TresPoints>
        <TresLine
            v-for="(geometry, index) in continentGeometries"
            :key="`continent-${index}`"
            :geometry="geometry"
        >
            <TresLineBasicMaterial
                color="#a9e6ff"
                :transparent="true"
                :opacity="1"
            />
        </TresLine>
        <TresLine
            v-for="(geometry, index) in arcGeometries"
            :key="index"
            :geometry="geometry"
        >
            <TresLineBasicMaterial
                color="#74bdff"
                :transparent="true"
                :opacity="0.85"
            />
        </TresLine>
        <TresMesh
            v-for="(position, index) in markerPositions"
            :key="`marker-${index}`"
            :position="position"
        >
            <TresSphereGeometry :args="[index === 0 ? 0.055 : 0.032, 12, 12]" />
            <TresMeshBasicMaterial color="#bcefff" />
        </TresMesh>
        <TresMesh
            v-for="(curve, index) in arcCurves"
            :key="`packet-${index}`"
            :ref="(element) => setDataPacket(element, index)"
            :position="curve.getPoint(index * 0.19)"
        >
            <TresSphereGeometry :args="[0.035, 10, 10]" />
            <TresMeshBasicMaterial color="#ffffff" />
        </TresMesh>
        <TresMesh :rotation="[1.22, 0.1, 0.65]">
            <TresTorusGeometry :args="[1.72, 0.008, 8, 160]" />
            <TresMeshBasicMaterial
                color="#5ab8ff"
                :transparent="true"
                :opacity="0.58"
            />
        </TresMesh>
        <TresMesh :scale="atmosphereScale">
            <TresSphereGeometry :args="[1.48, 32, 32]" />
            <TresMeshBasicMaterial
                color="#2780ff"
                :transparent="true"
                :opacity="0.09"
                :side="1"
            />
        </TresMesh>
    </TresGroup>
</template>
