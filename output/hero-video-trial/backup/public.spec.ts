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
});

test('desktop hero keeps the detailed globe still while light layers animate', async ({
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
    expect(activeLightAnimations).toBeGreaterThanOrEqual(4);
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
