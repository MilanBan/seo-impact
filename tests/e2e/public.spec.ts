import { expect, test } from '@playwright/test';

test('public pages are localized and keyboard accessible', async ({ page }) => {
    await page.goto('/en');
    await expect(page.getByRole('heading', { level: 1 })).toContainText(
        'SEO growth',
    );
    await page.getByRole('link', { name: 'DE' }).click();
    await expect(page).toHaveURL(/\/de$/);
    await expect(page.getByRole('heading', { level: 1 })).toContainText(
        'Mehr Sichtbarkeit',
    );
    await page.keyboard.press('Tab');
    await expect(page.locator(':focus')).toBeVisible();
});

test('contact form creates a lead', async ({ page }) => {
    await page.goto('/en/contact');
    await page.getByLabel('Your name').fill('Playwright Lead');
    await page.getByLabel('Work email').fill('playwright@example.com');
    await page
        .getByLabel('Tell us about the goal')
        .fill(
            'We want to build measurable growth across search and AI discovery.',
        );
    await page.getByRole('button', { name: /Send inquiry/ }).click();
    await expect(
        page.getByText('Thank you. We will be in touch shortly.'),
    ).toBeVisible();
});

test('mobile and reduced motion keep the static hero fallback', async ({
    page,
}) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/en');
    await expect(page.locator('.hero-poster img')).toBeVisible();
    await expect(page.locator('canvas.hero-canvas')).toHaveCount(0);
    await expect(page.locator('.hero-video')).toHaveCount(0);
});

test('desktop hero plays the globe video while cards and layout stay stable', async ({
    page,
    context,
}, testInfo) => {
    const pageErrors: string[] = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('/en');

    const heroVisual = page.locator('.hero-visual');
    const poster = page.locator('.hero-poster img');
    await expect(poster).toBeVisible();
    await expect(poster).toHaveAttribute(
        'src',
        '/images/brand/hero-globe-v3.png',
    );
    const cardRig = page.locator('.hero-card-rig');
    const cardStages = page.locator('.floating-card-stage');
    const rightCards = page.locator(
        '.floating-card-stage--growth .floating-card, .floating-card-stage--automations .floating-card, .floating-card-stage--software .floating-card',
    );
    await expect(cardRig).toHaveCount(1);
    await expect(page.locator('.floating-card')).toHaveCount(4);
    await expect(cardStages).toHaveCount(4);
    await expect(page.locator('.card-tether')).toHaveCount(0);
    expect(
        await cardRig.evaluate(
            (element) => getComputedStyle(element).perspective,
        ),
    ).not.toBe('none');
    expect(
        await page
            .locator('.floating-card')
            .evaluateAll((cards) =>
                cards.every((card) =>
                    getComputedStyle(card).transform.startsWith('matrix3d('),
                ),
            ),
    ).toBe(true);
    expect(
        await rightCards.evaluateAll(
            (cards) =>
                new Set(cards.map((card) => getComputedStyle(card).transform))
                    .size,
        ),
    ).toBe(1);
    expect(
        await cardStages.evaluateAll((stages) =>
            stages.every(
                (stage) =>
                    getComputedStyle(stage).animationName === 'card-rise' &&
                    getComputedStyle(stage).animationIterationCount === '1',
            ),
        ),
    ).toBe(true);
    await expect(heroVisual.locator('canvas')).toHaveCount(0);
    await expect(page.locator('.hero-data-packet')).toHaveCount(0);

    const video = page.locator('.hero-video');
    await expect(video).toHaveClass(/hero-video--ready/);
    await expect(video).toHaveAttribute(
        'src',
        '/videos/brand/hero-globe-loop-optimized-900p.mp4',
    );
    await expect
        .poll(() =>
            video.evaluate((element: HTMLVideoElement) => element.currentTime),
        )
        .toBeGreaterThan(0);
    expect(
        await video.evaluate((element: HTMLVideoElement) => ({
            muted: element.muted,
            loop: element.loop,
            inline: element.playsInline,
            duration: Math.round(element.duration * 100) / 100,
        })),
    ).toEqual({ muted: true, loop: true, inline: true, duration: 30.67 });

    const initialPosterState = await poster.evaluate((element) => ({
        objectPosition: getComputedStyle(element).objectPosition,
        transform: getComputedStyle(element).transform,
    }));
    expect(
        await poster.evaluate((element) => element.getAnimations().length),
    ).toBe(0);
    const activeLightAnimations = await heroVisual.evaluate((element) =>
        Array.from(
            element.querySelectorAll(
                '.hero-atmosphere, .hero-light-trails path, .hero-data-streams, .hero-beam',
            ),
        ).reduce(
            (total, layer) =>
                total +
                layer
                    .getAnimations()
                    .filter((animation) => animation.playState === 'running')
                    .length,
            0,
        ),
    );
    expect(activeLightAnimations).toBe(0);
    await page.waitForTimeout(1_500);

    const settledCardGeometry = await cardStages.evaluateAll((stages) =>
        stages.map((stage) => {
            const rect = stage.getBoundingClientRect();
            return {
                x: rect.x,
                y: rect.y,
                width: rect.width,
                height: rect.height,
                transform: getComputedStyle(stage).transform,
            };
        }),
    );

    const firstFrame = await page.screenshot({
        animations: 'allow',
        path: testInfo.outputPath('hero-animation-frame-1.png'),
    });
    await testInfo.attach('hero-animation-frame-1', {
        body: firstFrame,
        contentType: 'image/png',
    });

    await page.waitForTimeout(500);
    const laterCardGeometry = await cardStages.evaluateAll((stages) =>
        stages.map((stage) => {
            const rect = stage.getBoundingClientRect();
            return {
                x: rect.x,
                y: rect.y,
                width: rect.width,
                height: rect.height,
                transform: getComputedStyle(stage).transform,
            };
        }),
    );
    const laterPosterState = await poster.evaluate((element) => ({
        objectPosition: getComputedStyle(element).objectPosition,
        transform: getComputedStyle(element).transform,
    }));

    const laterPage = await context.newPage();
    laterPage.on('pageerror', (error) => pageErrors.push(error.message));
    await laterPage.setViewportSize({ width: 1440, height: 900 });
    await laterPage.goto('/en');
    await expect(laterPage.locator('.hero-video')).toHaveClass(
        /hero-video--ready/,
    );
    await laterPage.waitForTimeout(2_200);
    const secondFrame = await laterPage.screenshot({
        animations: 'allow',
        path: testInfo.outputPath('hero-animation-frame-2.png'),
    });
    await testInfo.attach('hero-animation-frame-2', {
        body: secondFrame,
        contentType: 'image/png',
    });
    await laterPage.close();

    const changedPixelRatio = await page.evaluate(
        async ({ before, after }) => {
            const decode = async (encoded: string) => {
                const response = await fetch(
                    `data:image/png;base64,${encoded}`,
                );
                return createImageBitmap(await response.blob());
            };
            const [beforeImage, afterImage] = await Promise.all([
                decode(before),
                decode(after),
            ]);
            const context = document
                .createElement('canvas')
                .getContext('2d', { willReadFrequently: true });
            if (!context) return 0;

            context.canvas.width = beforeImage.width;
            context.canvas.height = beforeImage.height;
            context.drawImage(beforeImage, 0, 0);
            const beforePixels = context.getImageData(
                0,
                0,
                beforeImage.width,
                beforeImage.height,
            ).data;
            context.clearRect(
                0,
                0,
                context.canvas.width,
                context.canvas.height,
            );
            context.drawImage(afterImage, 0, 0);
            const afterPixels = context.getImageData(
                0,
                0,
                afterImage.width,
                afterImage.height,
            ).data;

            let changed = 0;
            for (let index = 0; index < beforePixels.length; index += 4) {
                const distance =
                    Math.abs(beforePixels[index] - afterPixels[index]) +
                    Math.abs(beforePixels[index + 1] - afterPixels[index + 1]) +
                    Math.abs(beforePixels[index + 2] - afterPixels[index + 2]);
                if (distance > 24) changed++;
            }

            return changed / (beforePixels.length / 4);
        },
        {
            before: firstFrame.toString('base64'),
            after: secondFrame.toString('base64'),
        },
    );

    expect(secondFrame.equals(firstFrame)).toBeFalsy();
    expect(laterPosterState).toEqual(initialPosterState);
    expect(laterCardGeometry).toEqual(settledCardGeometry);
    expect(changedPixelRatio).toBeGreaterThan(0.001);
    expect(pageErrors).toEqual([]);
});

test('hero video can pause and resume, and follows reduced motion changes', async ({
    page,
}) => {
    await page.goto('/en');
    const video = page.locator('.hero-video');
    await expect(video).toHaveClass(/hero-video--ready/);
    await page.getByRole('button', { name: 'Pause animation' }).click();
    await expect
        .poll(() =>
            video.evaluate((element: HTMLVideoElement) => element.paused),
        )
        .toBe(true);
    await page.getByRole('button', { name: 'Play animation' }).click();
    await expect
        .poll(() =>
            video.evaluate((element: HTMLVideoElement) => element.paused),
        )
        .toBe(false);
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await expect(video).toHaveCount(0);
    await expect(page.locator('.hero-poster img')).toBeVisible();
    await expect(page.locator('.hero-video-toggle')).toHaveCount(0);
});

test('data saver and offscreen playback conserve hero bandwidth', async ({
    browser,
}) => {
    const saverContext = await browser.newContext({
        viewport: { width: 1440, height: 900 },
    });
    await saverContext.addInitScript(() => {
        Object.defineProperty(navigator, 'connection', {
            configurable: true,
            value: {
                saveData: true,
                addEventListener() {},
                removeEventListener() {},
            },
        });
    });
    const saverPage = await saverContext.newPage();
    const videoRequests: string[] = [];
    saverPage.on('request', (request) => {
        if (request.url().includes('/videos/brand/')) {
            videoRequests.push(request.url());
        }
    });
    await saverPage.goto('/en');
    await expect(saverPage.locator('.hero-video')).toHaveCount(0);
    await expect(saverPage.locator('.hero-poster img')).toBeVisible();
    expect(videoRequests).toEqual([]);
    await saverContext.close();

    const page = await browser.newPage({
        viewport: { width: 1440, height: 900 },
    });
    await page.goto('/en');
    const video = page.locator('.hero-video');
    await expect(video).toHaveClass(/hero-video--ready/);
    await page.locator('.services-section').scrollIntoViewIfNeeded();
    await expect
        .poll(() =>
            video.evaluate((element: HTMLVideoElement) => element.paused),
        )
        .toBe(true);
    await page.locator('.hero-section').scrollIntoViewIfNeeded();
    await expect
        .poll(() =>
            video.evaluate((element: HTMLVideoElement) => element.paused),
        )
        .toBe(false);
    await page.close();
});

test('hero retains the original picture if the video cannot load', async ({
    page,
}) => {
    await page.route('**/videos/brand/*.mp4', (route) => route.abort());
    await page.goto('/en');
    await expect(page.locator('.hero-video')).toHaveCount(0);
    await expect(page.locator('.hero-poster img')).toBeVisible();
    await expect(page.locator('.hero-beam')).toHaveCount(0);
});

test('mobile hero uses a static fallback without overlap or overflow', async ({
    page,
}, testInfo) => {
    const videoRequests: string[] = [];
    page.on('request', (request) => {
        if (request.url().includes('/videos/brand/')) {
            videoRequests.push(request.url());
        }
    });

    for (const locale of ['en', 'sr', 'de']) {
        for (const width of [320, 360, 390]) {
            await page.setViewportSize({ width, height: 844 });
            await page.goto(`/${locale}`);
            await expect(page.locator('.hero-poster img')).toBeVisible();
            await expect(page.locator('.hero-video')).toHaveCount(0);
            await expect(page.locator('.hero-video-toggle')).toHaveCount(0);
            await expect(page.locator('.hero-atmosphere')).toHaveCount(0);

            const geometry = await page.evaluate(() => {
                const proof = document.querySelector('.proof-row');
                const stats = document.querySelector('.hero-stats');
                if (!proof || !stats) return null;

                const proofRect = proof.getBoundingClientRect();
                const statsRect = stats.getBoundingClientRect();
                return {
                    proofBottom: proofRect.bottom,
                    statsTop: statsRect.top,
                    scrollWidth: document.documentElement.scrollWidth,
                };
            });

            expect(geometry).not.toBeNull();
            expect(geometry!.statsTop).toBeGreaterThanOrEqual(
                geometry!.proofBottom,
            );
            expect(geometry!.scrollWidth).toBeLessThanOrEqual(width);
        }
    }

    expect(videoRequests).toEqual([]);
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/de');
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.evaluate(() => document.fonts.ready);
    await page.waitForTimeout(1_700);
    await page.screenshot({
        path: testInfo.outputPath('hero-static-mobile.png'),
    });
});
